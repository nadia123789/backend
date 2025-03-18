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

// Récupérer l'ID de l'utilisateur depuis la requête
$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["message" => "ID de l'utilisateur manquant."]);
    exit();
}

// Récupérer les informations de l'utilisateur
$stmt = $conn->prepare("SELECT nom, prenom, sexe, telephone, email, cin, date_of_birth, profile_image FROM joueur WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Utilisateur non trouvé."]);
    exit();
}

// Bind the result variables
$stmt->bind_result($nom, $prenom, $sexe, $telephone, $email, $cin, $date_of_birth, $profile_image);
$stmt->fetch();

// Renvoyer les informations de l'utilisateur
http_response_code(200);
echo json_encode([
    "nom" => $nom,
    "prenom" => $prenom,
    "sex" => $sexe,
    "telephone" => $telephone,
    "email" => $email,
    "cin" => $cin,
    "dateNaissance" => $date_of_birth,
    "profileImage" => $profile_image,
]);

$stmt->close();
$conn->close();
?>