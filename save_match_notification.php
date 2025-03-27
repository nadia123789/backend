<?php
// save_match_notification.php

$host = 'localhost';
$dbname = 'matchit';
$username = 'root'; // Your database username
$password = ''; // Your database password

try {
    // Establishing a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Function to generate a random 4-digit code
function generateRandomCode($length = 4) {
    $characters = '0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Decode the input JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Extract data from the request
$team_name = $data['team_name'];
$terrain_name = $data['terrain_name'];
$date_time = $data['date_time'];
$adversaire_name = $data['adversaire_name'];

// Generate the random code
$confirmation_code = generateRandomCode();

// Fetch the team ID based on the team name
$query = "SELECT id_equipe FROM equipe WHERE nom_equipe = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$team_name]);
$team_id = $stmt->fetchColumn();

// Fetch the terrain ID based on the terrain name
$query = "SELECT id_terrain FROM terrain WHERE nom_terrain = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$terrain_name]);
$terrain_id = $stmt->fetchColumn();

// Fetch the adversaire ID based on the adversaire name
$query = "SELECT id_equipe FROM equipe WHERE nom_equipe = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$adversaire_name]);
$adversaire_id = $stmt->fetchColumn();

// Check if all necessary data (IDs) are available
if (!$team_id || !$terrain_id || !$adversaire_id) {
    echo json_encode(["status" => "error", "message" => "One or more entities (team, terrain, adversaire) not found."]);
    exit();
}

// Prepare SQL query to insert the match notification with the code
$sql = "INSERT INTO match_notifications (team_id, terrain_id, adversaire_id, date_time, confirmation_code) 
        VALUES (?, ?, ?, ?, ?)";

// Prepare the statement
$stmt = $pdo->prepare($sql);

// Execute the query with the data
if ($stmt->execute([$team_id, $terrain_id, $adversaire_id, $date_time, $confirmation_code])) {
    echo json_encode(["status" => "success", "message" => "Match notification saved.", "confirmation_code" => $confirmation_code]);
} else {
    echo json_encode(["status" => "error", "message" => "Error saving notification."]);
}
?>
