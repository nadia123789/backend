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

$query = "SELECT * FROM evaluations";
$result = $conn->query($query);

$evaluations = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $evaluations[] = $row;
    }
}

echo json_encode(["evaluations" => $evaluations]);

$conn->close();
?>