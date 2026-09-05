<?php
return [
    'provider' => 'gemini',
    'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
    'gemini_model' => getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash',
    'max_output_tokens' => 260,
    'temperature' => 0.35,
];
