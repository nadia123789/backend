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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $nom_terrain = $_POST['nom_terrain'];
    $localisation = $_POST['localisation'];
    $disponibilite = $_POST['disponibilite'];

    // Handle file upload
    if (isset($_FILES['image_terrain']) && $_FILES['image_terrain']['error'] == 0) {
        $imageTmpPath = $_FILES['image_terrain']['tmp_name'];
        $imageName = basename($_FILES['image_terrain']['name']);
        $imageSize = $_FILES['image_terrain']['size'];

        // Define upload directory
        $uploadDir = 'uploads/';
        $targetPath = $uploadDir . $imageName;

        // Check if file is an image (optional validation)
        $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($imageTmpPath, $targetPath)) {
                // File uploaded successfully

                // Insert data into the database (including image path)
                $stmt = $conn->prepare("INSERT INTO terrain (nom_terrain, localisation, disponibilite, image_terrain) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssis", $nom_terrain, $localisation, $disponibilite, $targetPath);

                if ($stmt->execute()) {
                    echo "Terrain added successfully!";
                } else {
                    echo "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Invalid image format. Only JPG, JPEG, PNG, GIF are allowed.";
        }
    } else {
        echo "No file uploaded or there was an error uploading the file.";
    }
}

// Close the connection
$conn->close();
?>
