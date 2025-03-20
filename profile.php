<?php
header("Content-Type: application/json; charset=UTF-8");

// Database connection
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "matchit"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to connect to the database."]);
    exit();
}

// Get the user's CIN from the request
$cin = $_GET['cin'] ?? null;

if (!$cin) {
    http_response_code(400);
    echo json_encode(["message" => "User CIN is missing."]);
    exit();
}

// Fetch user data from the database
$stmt = $conn->prepare("SELECT nom, prenom, sexe, telephone, email, cin, date_of_birth, profile_image FROM joueur WHERE cin = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Error preparing SQL query."]);
    exit();
}

$stmt->bind_param("s", $cin);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "User not found."]);
    exit();
}

// Bind the result variables
$stmt->bind_result($nom, $prenom, $sexe, $telephone, $email, $cin, $date_of_birth, $profile_image);
$stmt->fetch();

// Return the user's data
http_response_code(200);
echo json_encode([
    "nom" => $nom,
    "prenom" => $prenom,
    "sex" => $sexe,
    "telephone" => $telephone,
    "email" => $email,
    "cin" => $cin,
    "dateNaissance" => $date_of_birth,
    "profileImage" => $profile_image ? "http://localhost/backend-matchit/uploads/" . $profile_image : null,
]);

$stmt->close();
$conn->close();
?>
