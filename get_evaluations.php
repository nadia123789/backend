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

// Récupérer toutes les évaluations avec les commentaires et les scores
$query = "SELECT confirmation_code, score_equipe1, score_equipe2, note, commentaire, date_evaluation FROM evaluations ORDER BY date_evaluation DESC";
$result = $conn->query($query);

$evaluations = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $evaluations[] = $row;
    }
    echo json_encode(["evaluations" => $evaluations], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["message" => "Aucune évaluation trouvée."]);
}

// Fermer la connexion
$conn->close();
?>
