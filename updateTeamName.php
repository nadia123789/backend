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

// Get and sanitize the input data
$cin = filter_input(INPUT_POST, 'cin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$newTeamName = filter_input(INPUT_POST, 'newTeamName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$cin || !$newTeamName) {
    http_response_code(400);
    echo json_encode(["message" => "CIN or team name is missing or invalid."]);
    exit();
}

// Update team name in the database
$query = "
    UPDATE equipe e
    INNER JOIN joueur j ON e.id_equipe = j.id_equipe
    SET e.nom_equipe = ?
    WHERE j.cin = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Error preparing SQL query."]);
    exit();
}

$stmt->bind_param("ss", $newTeamName, $cin); // Bind parameters for the query
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Team name updated successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to update team name."]);
}

$stmt->close();
$conn->close();
?>
