<?php
header("Content-Type: application/json; charset=UTF-8");

// Connexion à la base de données
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$password = ""; // Remplacez par votre mot de passe MySQL
$dbname = "matchit"; // Remplacez par le nom de votre base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Échec de la connexion à la base de données."]);
    exit();
}

// Récupérer le CIN de l'utilisateur depuis la requête
$cin = $_GET['cin'] ?? null;

if (!$cin) {
    http_response_code(401);
    echo json_encode(["message" => "CIN de l'utilisateur manquant."]);
    exit();
}

// Récupérer les informations de l'utilisateur
$stmt = $conn->prepare("SELECT nom FROM joueur WHERE cin = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
    exit();
}

$stmt->bind_param("s", $cin); // 's' pour string
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Utilisateur non trouvé."]);
    exit();
}

$stmt->bind_result($nom);
$stmt->fetch();

// Renvoyer les informations de l'utilisateur
http_response_code(200);
echo json_encode([
    "nom" => $nom,
   
  
]);

$stmt->close();
$conn->close();
?>