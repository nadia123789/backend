<?php
// Include the database connection file
$host = 'localhost';
$dbname = 'matchit';
$username = 'root'; // Change with your database username
$password = ''; // Change with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Define the base URL for images
$base_url = "http://localhost/backend-matchit/"; 

// Query to fetch terrain name, image, and localisation from the terrain table
$query = "SELECT nom_terrain, image_terrain, localisation FROM terrain"; 
$stmt = $pdo->prepare($query); // Prepare the query
$stmt->execute(); // Execute the query

// Fetch all results as an associative array
$terrains = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Modify the image path to include the full URL
foreach ($terrains as &$terrain) {
    $terrain['image_terrain'] = $terrain['image_terrain'] ? $base_url . $terrain['image_terrain'] : null;
}

// Return the terrain data as JSON
echo json_encode($terrains); 
?>
