<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/vendor/autoload.php';

function respond(bool $ok, string $message, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Metodo non consentito.', 405);
}

if (!empty($_POST['website'] ?? '')) {
    respond(true, 'Richiesta inviata.');
}

$startedAt = (int) ($_POST['form_started_at'] ?? 0);
$now = time();
if ($startedAt <= 0 || ($now - $startedAt) < 3) {
    respond(true, 'Richiesta inviata.');
}

$clientIp = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateKey = preg_replace('/[^a-z0-9]/i', '', hash('sha256', $clientIp . '|byoursite-contact'));
$rateFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'byoursite_contact_' . $rateKey . '.json';
$windowSeconds = 15 * 60;
$maxRequests = 4;
$rateData = ['start' => $now, 'count' => 0];

if (is_file($rateFile)) {
    $decoded = json_decode((string) file_get_contents($rateFile), true);
    if (is_array($decoded) && isset($decoded['start'], $decoded['count'])) {
        $rateData = ['start' => (int) $decoded['start'], 'count' => (int) $decoded['count']];
    }
}

if (($now - $rateData['start']) > $windowSeconds) {
    $rateData = ['start' => $now, 'count' => 0];
}

$rateData['count']++;
file_put_contents($rateFile, json_encode($rateData), LOCK_EX);

if ($rateData['count'] > $maxRequests) {
    respond(false, 'Troppe richieste ravvicinate. Riprova tra qualche minuto.', 429);
}

$turnstileConfigPath = __DIR__ . '/turnstile-config.php';
$turnstileConfig = is_file($turnstileConfigPath) ? require $turnstileConfigPath : ['enabled' => false];

if (($turnstileConfig['enabled'] ?? false) === true) {
    $turnstileToken = trim((string) ($_POST['cf-turnstile-response'] ?? ''));
    $requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $requestHost = preg_replace('/:\d+$/', '', $requestHost) ?? $requestHost;
    $testHosts = $turnstileConfig['test_hosts'] ?? [];
    $isTestHost = is_array($testHosts) && in_array($requestHost, $testHosts, true);
    $turnstileSecret = trim((string) ($isTestHost ? ($turnstileConfig['test_secret_key'] ?? '') : ($turnstileConfig['secret_key'] ?? '')));

    if ($turnstileSecret === '') {
        respond(false, 'Verifica antibot non configurata. Scrivi a info@byoursite.com.', 503);
    }

    if ($turnstileToken === '') {
        respond(false, 'Completa la verifica antibot e riprova.', 422);
    }

    try {
        $client = new Client(['timeout' => 5]);
        $verification = $client->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'form_params' => [
                'secret' => $turnstileSecret,
                'response' => $turnstileToken,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ],
        ]);
        $verificationData = json_decode((string) $verification->getBody(), true);

        if (!is_array($verificationData) || ($verificationData['success'] ?? false) !== true) {
            respond(false, 'Verifica antibot non superata. Riprova tra qualche istante.', 422);
        }
    } catch (Throwable $exception) {
        error_log('BYOURSITE Turnstile error: ' . $exception->getMessage());
        respond(false, 'Verifica antibot non disponibile. Scrivi a info@byoursite.com.', 503);
    }
}

$configPath = __DIR__ . '/mail-config.php';
if (!is_file($configPath)) {
    respond(false, 'Modulo contatti non ancora configurato. Scrivi a info@byoursite.com.', 503);
}

$config = require $configPath;

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$contact = trim((string) ($_POST['contact'] ?? ''));
$topic = trim((string) ($_POST['topic'] ?? 'Richiesta progetto'));
$message = trim((string) ($_POST['message'] ?? ''));
$source = trim((string) ($_POST['source'] ?? 'contact-form'));

if ($name === '') {
    respond(false, 'Inserisci il tuo nome.', 422);
}

if ($email === '') {
    respond(false, 'Inserisci un indirizzo email per poterti rispondere.', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Indirizzo email non valido.', 422);
}

if ($contact !== '' && !preg_match('/^[+0-9 ()\/.-]{6,30}$/', $contact)) {
    respond(false, 'Inserisci un recapito valido oppure lascia il campo vuoto.', 422);
}

if ($message === '' || mb_strlen($message) < 8) {
    respond(false, 'Scrivi qualche dettaglio in piu sulla richiesta.', 422);
}

$allowedTopics = [
    'Sito vetrina',
    'Restyling sito',
    'Gestionale o area privata',
    'Automazione o integrazione API',
    'Consulenza tecnica',
    'Richiesta progetto',
];

if (!in_array($topic, $allowedTopics, true)) {
    $topic = 'Richiesta progetto';
}

$emailValue = $email !== '' ? $email : 'Non indicata';
$contactValue = $contact !== '' ? $contact : 'Non indicato';
$ipValue = (string) ($_SERVER['REMOTE_ADDR'] ?? 'n/d');
$userAgentValue = (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'n/d');
$submittedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Rome')))->format('d/m/Y H:i');

$escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$bodyLines = [
    'Nuova richiesta da BYOURSITE',
    'Data: ' . $submittedAt,
    '',
    'Nome: ' . $name,
    'Email: ' . $emailValue,
    'Recapito: ' . $contactValue,
    'Richiesta: ' . $topic,
    'Origine: ' . $source,
    '',
    'Messaggio:',
    $message,
    '',
    'IP: ' . $ipValue,
    'User agent: ' . $userAgentValue,
];

$htmlRows = [
    'Nome' => $name,
    'Email' => $emailValue,
    'Recapito' => $contactValue,
    'Richiesta' => $topic,
    'Data' => $submittedAt,
];

$htmlBody = '<!doctype html><html><body style="margin:0;padding:0;background:#f4f7fb;color:#101828;font-family:Arial,Helvetica,sans-serif;">';
$htmlBody .= '<div style="max-width:680px;margin:0 auto;padding:28px 18px;">';
$htmlBody .= '<div style="background:#050b16;color:#ffffff;border-radius:10px 10px 0 0;padding:24px 26px;border-bottom:3px solid #68ddf8;">';
$htmlBody .= '<div style="font-size:12px;letter-spacing:2px;text-transform:uppercase;color:#68ddf8;margin-bottom:8px;">BYOURSITE</div>';
$htmlBody .= '<h1 style="margin:0;font-size:24px;line-height:1.25;">Nuova richiesta dal sito</h1>';
$htmlBody .= '</div>';
$htmlBody .= '<div style="background:#ffffff;border:1px solid #d8e0ea;border-top:0;padding:24px 26px;">';
$htmlBody .= '<table role="presentation" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;margin-bottom:24px;">';
foreach ($htmlRows as $label => $value) {
    $htmlBody .= '<tr>';
    $htmlBody .= '<td style="width:140px;padding:10px 0;border-bottom:1px solid #eef2f7;color:#667085;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">' . $escape($label) . '</td>';
    $htmlBody .= '<td style="padding:10px 0;border-bottom:1px solid #eef2f7;font-size:15px;color:#101828;">' . $escape((string) $value) . '</td>';
    $htmlBody .= '</tr>';
}
$htmlBody .= '</table>';
$htmlBody .= '<div style="margin:0 0 10px;color:#667085;font-size:13px;text-transform:uppercase;letter-spacing:.5px;">Messaggio</div>';
$htmlBody .= '<div style="white-space:pre-wrap;background:#f8fafc;border:1px solid #e4eaf2;border-radius:8px;padding:16px 18px;font-size:16px;line-height:1.65;color:#101828;">' . nl2br($escape($message)) . '</div>';
$htmlBody .= '<div style="margin-top:24px;padding-top:16px;border-top:1px solid #eef2f7;color:#98a2b3;font-size:12px;line-height:1.5;">';
$htmlBody .= 'Origine: ' . $escape($source) . '<br>IP: ' . $escape($ipValue) . '<br>User agent: ' . $escape($userAgentValue);
$htmlBody .= '</div>';
$htmlBody .= '</div></div></body></html>';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = (string) $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = (string) $config['username'];
    $mail->Password = (string) $config['password'];
    $mail->Port = (int) $config['port'];

    if (($config['secure'] ?? 'tls') === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->CharSet = 'UTF-8';
    $mail->setFrom((string) $config['from_email'], (string) $config['from_name']);
    $mail->addAddress((string) $config['to_email'], (string) $config['to_name']);

    if ($email !== '') {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(true);
    $mail->Subject = '[BYOURSITE] ' . $topic . ' - ' . $name;
    $mail->Body = $htmlBody;
    $mail->AltBody = implode("\n", $bodyLines);

    $mail->send();
    respond(true, 'Richiesta inviata. Ti rispondero appena possibile.');
} catch (MailException $exception) {
    error_log('BYOURSITE contact error: ' . $exception->getMessage());
    respond(false, 'Invio non riuscito. Scrivi a info@byoursite.com.', 500);
}
