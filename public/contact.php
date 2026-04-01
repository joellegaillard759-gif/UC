<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://www.urbenconstruction.ch');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Honeypot — si ce champ est rempli, c'est un bot
if (!empty($_POST['website'])) {
    // Fausse réponse de succès pour ne pas alerter le bot
    echo json_encode(['success' => true]);
    exit;
}

// Validation des champs requis
$nom     = trim($_POST['nom'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($nom) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Champs obligatoires manquants']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Adresse email invalide']);
    exit;
}

// Champs optionnels
$telephone = trim($_POST['telephone'] ?? '—');
$commune   = trim($_POST['commune'] ?? '—');
$service   = trim($_POST['service'] ?? '—');

// Adresse de destination (jamais exposée dans le HTML)
$to      = "\x69\x6e\x66\x6f\x40\x75\x72\x62\x65\x6e\x63\x6f\x6e\x73\x74\x72\x75\x63\x74\x69\x6f\x6e\x2e\x63\x68";
$subject = 'Nouvelle demande de contact — Urben Construction';

$body  = "Nom : $nom\n";
$body .= "Email : $email\n";
$body .= "Téléphone : $telephone\n";
$body .= "Commune : $commune\n";
$body .= "Service : $service\n";
$body .= "\nMessage :\n$message\n";

$headers  = "From: noreply@urbenconstruction.ch\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$sent = mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "L'envoi a échoué, veuillez nous appeler directement."]);
}
