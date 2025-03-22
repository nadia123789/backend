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

// Get and sanitize the user's CIN from the request
$cin = filter_input(INPUT_GET, 'cin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (!$cin) {
    http_response_code(400);
    echo json_encode(["message" => "User CIN is missing or invalid."]);
    exit();
}

// Fetch team name and logo from the database
$query = "
    SELECT e.nom_equipe, e.logo
    FROM equipe e
    INNER JOIN joueur j ON e.id_equipe = j.id_equipe
    WHERE j.cin = ?
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Error preparing SQL query."]);
    exit();
}

$stmt->bind_param("s", $cin); // Bind the cin to the query
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "User not found."]);
    exit();
}

// Bind the result variables
$stmt->bind_result($nom_equipe, $logo);
$stmt->fetch();

// Return the team name and logo
http_response_code(200);
echo json_encode([
    "teamName" => $nom_equipe,
    "teamLogo" =>$logo ? "http://localhost/backend-matchit/" . $logo  : null,
]);

$stmt->close();
$conn->close();
?>
