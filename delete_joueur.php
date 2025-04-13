<?php
header("Content-Type: application/json");
require_once 'config.php'; // Inclure votre configuration de connexion

$data = json_decode(file_get_contents("php://input"), true);
$cin = $conn->real_escape_string($data['cin']);

// Vérifier d'abord si le joueur est capitaine d'une équipe
$checkQuery = "SELECT id_equipe FROM equipe WHERE capitaine_id = '$cin'";
$result = $conn->query($checkQuery);

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Impossible de supprimer: ce joueur est capitaine d'une équipe"]);
    exit;
}

// Supprimer le joueur
$deleteQuery = "DELETE FROM joueur WHERE cin = '$cin'";

if ($conn->query($deleteQuery)) {
    echo json_encode(["success" => true, "message" => "Joueur supprimé avec succès"]);
} else {
    echo json_encode(["success" => false, "message" => "Erreur lors de la suppression: " . $conn->error]);
}

$conn->close();
?>