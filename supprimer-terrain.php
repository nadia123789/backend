<?php

// Database connection (Replace with your database credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "matchit"; // Update with your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted via POST (JSON data)
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['nom_terrain'])) {
    $nom_terrain = $data['nom_terrain'];

    // Delete the terrain by name
    $stmt = $conn->prepare("DELETE FROM terrain WHERE nom_terrain = ?");
    $stmt->bind_param("s", $nom_terrain);

    if ($stmt->execute()) {
        echo "Terrain deleted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid data received.";
}

// Close the connection
$conn->close();
?>
