<?php
// Assuming you're using mysqli to connect to the database
$host = 'localhost';
$dbname = 'matchit';
$username = 'root'; // Your database username
$password = ''; // Your database password

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$nom_terrain = $_POST['nom_terrain'];
$datetime = $_POST['datetime'];

$sql = "SELECT * FROM reservation WHERE nom_terrain = ? AND datetime = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nom_terrain, $datetime);
$stmt->execute();
$result = $stmt->get_result();

// If there's any reservation at the given time
if ($result->num_rows > 0) {
    echo json_encode(['available' => false]); // Time is unavailable
} else {
    echo json_encode(['available' => true]); // Time is available
}

$stmt->close();
$conn->close();
?>
