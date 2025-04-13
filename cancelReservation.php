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
if (!$data || empty($data['code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Code de réservation requis.']);
    exit;
}

$reservationCode = $data['code'];

// Fetch the reservation from the database using the code
$sql = "SELECT * FROM reservation WHERE confirmation_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reservationCode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Code de réservation invalide.']);
    exit;
}

// If the reservation exists, cancel it
$sql = "DELETE FROM reservation WHERE confirmation_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reservationCode);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Réservation annulée avec succès.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'annulation.']);
}

$stmt->close();
$conn->close();
?>
