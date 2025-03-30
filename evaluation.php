<?php
header("Content-Type: application/json");

// Connexion à la base de données
$host = "localhost";  // Change si nécessaire
$user = "root";       // Change si nécessaire
$password = "";       // Change si nécessaire
$database = "matchit"; // Change si nécessaire

$conn = new mysqli($host, $user, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die(json_encode(["message" => "Échec de la connexion à la base de données."]));
}

// Lire les données envoyées en JSON
$data = json_decode(file_get_contents("php://input"), true);

// Vérifier que toutes les données sont présentes
if (!isset($data["confirmation_code"], $data["note"], $data["commentaire"], $data["score_equipes"])) {
    echo json_encode(["message" => "Données incomplètes."]);
    exit;
}

$confirmation_code = $conn->real_escape_string($data["confirmation_code"]);
$note = (int)$data["note"];
$commentaire = $conn->real_escape_string($data["commentaire"]);
$score_equipe1 = (int)$data["score_equipes"]["equipe1"];
$score_equipe2 = (int)$data["score_equipes"]["equipe2"];

// Vérifier si le code de confirmation existe dans la base
$checkQuery = "SELECT confirmation_code FROM reservation WHERE confirmation_code = '$confirmation_code'";
$result = $conn->query($checkQuery);

if ($result->num_rows == 0) {
    echo json_encode(["message" => "Code de confirmation invalide."]);
    exit;
}

// Insérer l'évaluation avec les nouveaux champs
$insertQuery = "INSERT INTO evaluations (confirmation_code, score_equipe1, score_equipe2, note, commentaire, date_evaluation) 
                VALUES ('$confirmation_code', '$score_equipe1', '$score_equipe2', '$note', '$commentaire', NOW())";

if ($conn->query($insertQuery)) {
    echo json_encode(["message" => "Évaluation enregistrée avec succès."]);
} else {
    echo json_encode(["message" => "Erreur lors de l'enregistrement de l'évaluation."]);
}

// Fermer la connexion
$conn->close();
?>
