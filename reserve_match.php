<?php
// Database connection
$host = 'localhost';
$dbname = 'matchit';
$username = 'root'; // Your database username
$password = ''; // Your database password

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!$data || empty($data['cin']) || empty($data['nom_equipe_adversaire']) || empty($data['nom_terrain']) || empty($data['datetime'])) {
    echo json_encode(['success' => false, 'message' => 'Please provide all fields.']);
    exit;
}

$cin = $data['cin'];  // CIN of the user
$nom_equipe_adversaire = $data['nom_equipe_adversaire'];
$nom_terrain = $data['nom_terrain'];
$datetime = $data['datetime'];

// Fetch the team name (nom_equipe) using CIN
$sql = "SELECT nom_equipe FROM equipe WHERE capitaine_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cin); // Use CIN to find the team
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'User team not found.']);
    exit;
}

$row = $result->fetch_assoc();
$nom_equipe = $row['nom_equipe'];  // Get the team name for this CIN

// Check if adversaire exists
$sql = "SELECT * FROM equipe WHERE nom_equipe = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nom_equipe_adversaire);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Adversaire team not found.']);
    exit;
}

// Generate a random 4-digit confirmation code
$confirmation_code = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

// Insert into reservation table
$sql = "INSERT INTO reservation (nom_equipe, nom_equipe_adversaire, nom_terrain, datetime, confirmation_code) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $nom_equipe, $nom_equipe_adversaire, $nom_terrain, $datetime, $confirmation_code);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reservation successful!', 'confirmation_code' => $confirmation_code]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
