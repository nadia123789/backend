<?php
// Database connection
$host = 'localhost';
$dbname = 'matchit';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer les données
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['nom_equipe']) || empty($data['nom_equipe_adversaire']) || empty($data['code'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields.']);
    exit;
}

$nom_equipe = $data['nom_equipe'];
$nom_equipe_adversaire = $data['nom_equipe_adversaire'];
$code = $data['code'];

// Vérifier le code
$sql = "SELECT * FROM reservation WHERE nom_equipe = ? AND nom_equipe_adversaire = ? AND confirmation_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nom_equipe, $nom_equipe_adversaire, $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid confirmation code.']);
    exit;
}

// Confirmer la réservation
$sql = "UPDATE reservation SET confirmed = TRUE WHERE nom_equipe = ? AND nom_equipe_adversaire = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nom_equipe, $nom_equipe_adversaire);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reservation confirmed!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error confirming reservation.']);
}

$stmt->close();
$conn->close();
?>
