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

// Récupérer les données du formulaire
$data = json_decode(file_get_contents("php://input"), true);

// Validation des données
if (empty($data['prenom']) || empty($data['nom']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Veuillez remplir tous les champs obligatoires."]);
    exit();
}

// Hash du mot de passe
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

// Insertion des données dans la base de données
$stmt = $conn->prepare("INSERT INTO joueur (prenom, nom, date_of_birth, sexe, cin, telephone, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $data['prenom'], $data['nom'], $data['date_of_birth'], $data['sexe'], $data['cin'], $data['telephone'], $data['email'], $hashed_password);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "Inscription réussie."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de l'inscription."]);
}

$stmt->close();
$conn->close();
?>