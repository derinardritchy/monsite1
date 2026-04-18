<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

// =============================================
// 🔑 METE KLE API ANTHROPIC OU LA A
// =============================================
define('ANTHROPIC_API_KEY', 'sk-ant-METE-KLE-OU-LA-A');

header('Content-Type: application/json; charset=utf-8');

// Rate limit
if (!isset($_SESSION['ai_count'])) $_SESSION['ai_count'] = 0;
if ($_SESSION['ai_count'] > 30) {
    echo json_encode(['error' => 'Limit 30 mesaj atenn. Rafraîchi paj la.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['messages']) || !is_array($input['messages'])) {
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

$systemPrompt = "Tu es BiroTech AI, l'assistant intelligent de BiroTech créé par l'Ingénieur Derinard Ritchy. Tu es expert en bureautique: Microsoft Word, Excel, Publisher et Adobe Photoshop. Réponds dans la langue de l'utilisateur (français, créole haïtien ou anglais). Sois pédagogique, pratique, avec des exemples. Utilise des emojis. Pour les raccourcis: \`Ctrl+S\`. Réponses max 300 mots. Si hors bureautique, redirige poliment.";

$payload = json_encode([
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 800,
    'system'     => $systemPrompt,
    'messages'   => array_slice($input['messages'], -10) // garder derniers 10
]);

// =============================================
// FIX SSL — WAMP/XAMPP localhost pa gen certifika
// CURLOPT_SSL_VERIFYPEER => false règle erè sa a
// =============================================
$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . ANTHROPIC_API_KEY,
        'anthropic-version: 2023-06-01',
        'Content-Length: ' . strlen($payload)
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,  // FIX: WAMP localhost SSL
    CURLOPT_SSL_VERIFYHOST => false,  // FIX: WAMP localhost SSL
    CURLOPT_FOLLOWLOCATION => true,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['error' => '🌐 Erè rezo: ' . $curlError . '. Verifye koneksyon entènèt ou.']);
    exit;
}

if (!$response) {
    echo json_encode(['error' => '⏳ Pa gen repons. Eseye ankò.']);
    exit;
}

$data = json_decode($response, true);

if ($httpCode === 401) {
    echo json_encode(['error' => '🔑 Kle API pa valid. Admin dwe mete kle API Anthropic nan ai_proxy.php (liy 12).']);
    exit;
}
if ($httpCode === 429) {
    echo json_encode(['error' => '⏳ Twòp demann. Eseye nan 10 segond.']);
    exit;
}
if ($httpCode !== 200 || !$data) {
    echo json_encode(['error' => "Erè API (HTTP $httpCode). Eseye ankò."]);
    exit;
}

$_SESSION['ai_count']++;

if (isset($data['content'][0]['text'])) {
    echo json_encode(['reply' => $data['content'][0]['text']]);
} else {
    echo json_encode(['error' => 'Repons vid. Eseye ankò.']);
}
?>
