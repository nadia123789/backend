<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "matchit"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Échec de la connexion à la base de données."]);
    exit();
}

// Get user CIN from the request
$cin = filter_input(INPUT_GET, 'cin', FILTER_SANITIZE_STRING);
if (!$cin) {
    http_response_code(401);
    echo json_encode(["message" => "CIN de l'utilisateur manquant."]);
    exit();
}

// Fetch user data, including profile image
$stmt = $conn->prepare("SELECT nom, prenom, email, telephone, cin, sexe, date_of_birth, profile_image FROM joueur WHERE cin = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $cin); // 's' for string
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Utilisateur non trouvé."]);
    exit();
}

$stmt->bind_result($nom, $prenom, $email, $telephone, $cin, $sexe, $date_of_birth, $profile_image);
$stmt->fetch();

// Return user data, including profile image path
http_response_code(200);
echo json_encode([
    "nom" => $nom,
    "prenom" => $prenom,
    "email" => $email,
    "telephone" => $telephone,
    "cin" => $cin,
    "sex" => $sexe,
    "dateNaissance" => $date_of_birth,
    "profileImage" => $profile_image ? "/backend-matchit/uploads/profiles/" . basename($profile_image) : "/path-to-placeholder-image.png",
], JSON_UNESCAPED_SLASHES);

$stmt->close();
$conn->close();
?>