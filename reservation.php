<?php
header("Content-Type: application/json; charset=UTF-8");

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "matchit";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur de connexion à la base de données."]);
    exit();
}

$teamId = $_GET['teamId'] ?? '';

if (empty($teamId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Team ID is required']);
    exit;
}

// Get team name first
$stmt = $pdo->prepare("SELECT nom_equipe FROM equipe WHERE id_equipe = ?");
$stmt->execute([$teamId]);
$team = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    http_response_code(404);
    echo json_encode(['error' => 'Team not found']);
    exit;
}

// Get all reservations for this team
$stmt = $pdo->prepare("SELECT * FROM reservation WHERE nom_equipe = ? OR nom_equipe_adversaire = ? ORDER BY datetime ASC");
$stmt->execute([$team['nom_equipe'], $team['nom_equipe']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($reservations);
?>