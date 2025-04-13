<?php
header("Content-Type: application/json");

// Connexion à la base de données
$host = "localhost";
$user = "root";
$password = "";
$database = "matchit";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Échec de la connexion à la base de données."]));
}

$query = "SELECT cin, nom, prenom, email, telephone, capitain FROM joueur";
$result = $conn->query($query);

$joueurs = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $joueurs[] = $row;
    }
}

echo json_encode(["joueurs" => $joueurs]);

$conn->close();
?>