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

// Récupération des données du formulaire
$data = json_decode(file_get_contents("php://input"), true);

// Vérification des données reçues
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Veuillez remplir tous les champs."]);
    exit();
}

$email = $data['email'];
$password = $data['password'];

// Vérification des informations d'identification
$stmt = $conn->prepare("SELECT id, password FROM joueur WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["message" => "Email ou mot de passe incorrect."]);
    exit();
}

$stmt->bind_result($id, $hashed_password);
$stmt->fetch();

// Vérification du mot de passe
if (!password_verify($password, $hashed_password)) {
    http_response_code(401);
    echo json_encode(["message" => "Email ou mot de passe incorrect."]);
    exit();
}

// Connexion réussie
http_response_code(200);
echo json_encode([
    "message" => "Connexion réussie.",
    "user_id" => $id, // Renvoyer l'ID de l'utilisateur
]);

$stmt->close();
$conn->close();
?>