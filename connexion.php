<?php
header("Content-Type: application/json; charset=UTF-8");

// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Update if necessary
$dbname = "matchit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

// Get the data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Validate input data
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["message" => "Please provide both email and password"]);
    exit();
}

$email = $data['email'];
$password_input = $data['password']; // The input password provided by the user

// Check if the user exists in the 'gestionnaire' table
$stmt = $conn->prepare("SELECT idGestionnaire, password, nom, prenom FROM gestionnaire WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($idGestionnaire, $stored_password, $nom, $prenom);
    $stmt->fetch();

    // Compare plain-text password for 'gestionnaire'
    if ($password_input === $stored_password) {
        $token = bin2hex(random_bytes(16)); // Generate a token
        http_response_code(200);
        echo json_encode([
            "message" => "Login successful as Gestionnaire",
            "token" => $token,
            "idGestionnaire" => $idGestionnaire,
            "role" => "gestionnaire",
            "nom" => $nom,
            "prenom" => $prenom
        ]);
        exit();
    }
}

// If not found in gestionnaire, check the 'joueur' table
$stmtJoueur = $conn->prepare("SELECT cin, password FROM joueur WHERE email = ?");
$stmtJoueur->bind_param("s", $email);
$stmtJoueur->execute();
$stmtJoueur->store_result();

if ($stmtJoueur->num_rows > 0) {
    $stmtJoueur->bind_result($cin, $stored_password_joueur);
    $stmtJoueur->fetch();

    // Use password_verify for 'joueur' as the password is hashed
    if (password_verify($password_input, $stored_password_joueur)) {
        $token = bin2hex(random_bytes(16)); // Generate a token
        http_response_code(200);
        echo json_encode([
            "message" => "Login successful as Joueur",
            "token" => $token,
            "cin" => $cin,
            "role" => "joueur"
        ]);
        exit();
    }
}

// If no match is found in either table
http_response_code(401);
echo json_encode(["message" => "Invalid email or password"]);

$stmt->close();
$stmtJoueur->close();
$conn->close();
?>
