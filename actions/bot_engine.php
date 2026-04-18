<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// 1. Récupération du message
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => "Akwaba ! Je n'ai pas bien saisi votre message."]);
    exit;
}

// 2. Instructions de personnalité
$instruction = "Tu es NexoBot, l'assistant IA de la plateforme Nexora en Côte d'Ivoire.
Ton rôle : Aider les utilisateurs (prestataires et clients).
Ton style : Professionnel, chaleureux, avec quelques expressions ivoiriennes.
Paiements : Orange Money, MTN MoMo, Moov Money, Wave.";

// 3. Appel API OpenAI
$url = "https://api.openai.com/v1/chat/completions";

$payload = [
    "model" => "gpt-4o-mini", // Modèle très économique et rapide
    "messages" => [
        ["role" => "system", "content" => $instruction],
        ["role" => "user", "content" => $userMessage]
    ],
    "temperature" => 0.7
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . OPENAI_API_KEY
]);

// Sécurité pour XAMPP/WAMP
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    echo json_encode(['reply' => "Erreur de connexion : " . $err]);
    exit;
}

$resData = json_decode($response, true);

// 4. Traitement de la réponse
if (isset($resData['choices'][0]['message']['content'])) {
    $reply = $resData['choices'][0]['message']['content'];
} else {
    // Si OpenAI renvoie une erreur (ex: plus de crédit)
    $errorMsg = $resData['error']['message'] ?? "L'IA ne répond pas. Code: " . $httpCode;
    $reply = "Désolé, petit souci technique : " . $errorMsg;
}

echo json_encode(['reply' => $reply]);