<?php
// Database connection settings
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

// Get the raw POST data
$data = json_decode(file_get_contents("php://input"));

// Check if the adversaire name is provided
if (isset($data->adversaire)) {
    $adversaire = $data->adversaire;

    // Prepare SQL statement to check if the team exists in the equipe table
    $query = "SELECT * FROM equipe WHERE nom_equipe = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$adversaire]); // Bind the adversaire parameter

    // Check if the adversaire exists
    if ($stmt->rowCount() > 0) {
        // If adversaire exists, return a success response
        echo json_encode(["exists" => true]);
    } else {
        // If adversaire does not exist, return a failure response
        echo json_encode(["exists" => false]);
    }
} else {
    // If no adversaire is provided, return an error response
    echo json_encode(["error" => "Nom de l’adversaire non fourni"]);
}

// Close the database connection
$pdo = null;
?>
