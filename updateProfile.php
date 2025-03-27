<?php
header("Content-Type: application/json; charset=UTF-8");

// Database connection
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "matchit"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to connect to the database."]);
    exit();
}

// Get the user's data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Validate input data
if (!isset($data['cin'])) {
    http_response_code(400);
    echo json_encode(["message" => "User CIN is missing."]);
    exit();
}

$cin = $data['cin'];
$nom = $data['nom'];
$prenom = $data['prenom'];
$date_of_birth = $data['dateNaissance'];
$sex = $data['sex'];
$telephone = $data['telephone'];
$email = $data['email'];

// Update user data in the database
$stmt = $conn->prepare("UPDATE joueur SET nom = ?, prenom = ?, sexe = ?, telephone = ?, email = ?, date_of_birth = ? WHERE cin = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Error preparing SQL query."]);
    exit();
}

$stmt->bind_param("sssssss", $nom, $prenom, $sex, $telephone, $email, $date_of_birth, $cin);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Informations mises à jour avec succès."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Error updating user data."]);
}

$stmt->close();
$conn->close();
?>
