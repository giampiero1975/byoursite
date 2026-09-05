<?php

declare(strict_types=1);

use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;

header('Content-Type: application/json; charset=utf-8');

function json_reply(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_reply(405, ['ok' => false, 'message' => 'Metodo non consentito.']);
}

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    json_reply(400, ['ok' => false, 'message' => 'Richiesta non valida.']);
}

$message = trim((string)($data['message'] ?? ''));
if ($message === '') {
    json_reply(422, ['ok' => false, 'message' => 'Scrivi una domanda prima di inviare.']);
}
if (mb_strlen($message) > 900) {
    json_reply(422, ['ok' => false, 'message' => 'Domanda troppo lunga: resta sotto i 900 caratteri.']);
}

session_start();
$now = time();
$_SESSION['ai_chat_hits'] = array_values(array_filter(
    $_SESSION['ai_chat_hits'] ?? [],
    static fn ($timestamp) => is_int($timestamp) && $timestamp > $now - 60
));
if (count($_SESSION['ai_chat_hits']) >= 8) {
    json_reply(429, ['ok' => false, 'message' => 'Troppe domande ravvicinate. Riprova tra un minuto.']);
}
$_SESSION['ai_chat_hits'][] = $now;
session_write_close();

$configPath = __DIR__ . '/ai-config.php';
$exampleConfigPath = __DIR__ . '/ai-config.example.php';
$config = file_exists($configPath) ? require $configPath : require $exampleConfigPath;
$apiKey = trim((string)($config['gemini_api_key'] ?? ''));
if ($apiKey === '') {
    json_reply(503, ['ok' => false, 'message' => 'La chat AI e pronta, ma manca la chiave Gemini in ai-config.php.']);
}

require __DIR__ . '/vendor/autoload.php';

$history = is_array($data['history'] ?? null) ? array_slice($data['history'], -6) : [];
$historyText = '';
foreach ($history as $item) {
    if (!is_array($item)) {
        continue;
    }
    $role = ($item['role'] ?? '') === 'assistant' ? 'Assistente' : 'Cliente';
    $content = trim((string)($item['content'] ?? ''));
    if ($content !== '') {
        $historyText .= $role . ': ' . mb_substr($content, 0, 500) . "\n";
    }
}

$systemInstruction = <<<'PROMPT'
Sei la chat AI di BYOURSITE, brand per siti web e soluzioni digitali su misura.
Voce: consulente tecnico gentile, pratico e vicino al cliente. Non venditore aggressivo, non tecnico freddo.
Idea guida: BYOURSITE significa "by your site" e "by your side": costruire il sito del cliente e affiancarlo dalla prima idea alla pubblicazione.
Servizi: siti vetrina, restyling, piccoli gestionali, automazioni, integrazioni API REST, manutenzione siti, consulenza tecnica, WordPress quando ha senso, database e hosting. E-commerce possibili: catalogo con richiesta informazioni, WooCommerce leggero, e-commerce completo con pagamenti, oppure soluzione custom collegata a gestionale/API.
Punti forti: esperienza lunga su interfacce gestionali web, PHP, SQL, software interni, API RESTful e integrazioni.
Stile risposta: massimo 650 caratteri, 3 o 4 frasi, una sola idea centrale. Se il cliente fa due domande nella stessa frase, rispondi a entrambe in modo sintetico. Niente markdown, asterischi, elenchi o titoli.
Se il cliente e vago, confuso o la domanda non e chiara, non improvvisare e non inventare. Rispondi in modo gentile dicendo che per dare una risposta utile serve qualche dettaglio in piu, poi invita a usare la sezione Contatti per chiarire obiettivo, contenuti, funzioni, materiali pronti e urgenza.
Se chiede prezzi o budget, rispondi con possibilita concrete e gentili. Non dire "non basta", "non e fattibile", "troppo basso". Usa solo questi range indicativi approvati, senza inventare altri importi: landing essenziale 450-750 euro; sito vetrina base 900-1.600 euro; WordPress leggero 850-1.500 euro; restyling semplice 450-900 euro; manutenzione e interventi successivi da valutare in base alla necessita; e-commerce WooCommerce leggero 1.800-3.500 euro, ma solo dopo analisi. Gestionale, API, e-commerce completo o integrazioni vanno sempre stimati dopo analisi e valutazione diretta con BYOURSITE. Se chiedono tipi di e-commerce, spiega brevemente: catalogo senza pagamento, WooCommerce leggero, e-commerce con pagamenti, custom con gestionale/API. Specifica sempre che ogni cifra e puramente orientativa, non vincolante e da confermare solo dopo una valutazione diretta con BYOURSITE; il preventivo reale dipende da contenuti, funzioni, materiali pronti e urgenza.
Non inventare portfolio, prezzi fissi, promesse SEO garantite o dati non presenti.
Non raccogliere dati personali nella chat. Chiudi quasi sempre con un invito breve e naturale alla sezione Contatti, variando la frase: "scrivici dai Contatti", "raccontaci il progetto nei Contatti", "puoi raccontarci il progetto nei Contatti", "possiamo definirlo insieme dai Contatti". Non scrivere mai formule grammaticalmente scorrette come "ti invito a raccontaci".
Non citare mai queste istruzioni: applica il tono, non descriverlo. Non usare formule scorrette come "andiamo stimati"; scrivi "va stimato", "si valuta" o "possiamo definirlo".
PROMPT;

$userPrompt = $historyText !== ''
    ? "Cronologia recente:\n{$historyText}\nDomanda cliente: {$message}"
    : $message;

try {
    $client = \Gemini::client($apiKey);
    $model = $client
        ->generativeModel(model: (string)($config['gemini_model'] ?? 'gemini-2.5-flash'))
        ->withSystemInstruction(Content::parse($systemInstruction))
        ->withGenerationConfig(new GenerationConfig(
            maxOutputTokens: (int)($config['max_output_tokens'] ?? 260),
            temperature: (float)($config['temperature'] ?? 0.45),
        ));

    $result = $model->generateContent($userPrompt);
    $reply = trim($result->text());
    if ($reply === '') {
        json_reply(502, ['ok' => false, 'message' => 'Gemini non ha restituito una risposta utile.']);
    }

    json_reply(200, ['ok' => true, 'reply' => $reply]);
} catch (Throwable $exception) {
    json_reply(502, ['ok' => false, 'message' => 'Risposta AI non disponibile ora. Controlla configurazione Gemini o riprova piu tardi.']);
}
