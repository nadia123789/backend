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

if (isset($data['nom_terrain']) && isset($data['disponibilite'])) {
    $nom_terrain = $data['nom_terrain'];
    $disponibilite = $data['disponibilite'];

    // Update the disponibilite based on terrain name
    $stmt = $conn->prepare("UPDATE terrain SET disponibilite = ? WHERE nom_terrain = ?");
    $stmt->bind_param("is", $disponibilite, $nom_terrain);

    if ($stmt->execute()) {
        echo "Disponibilité updated successfully!";
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
