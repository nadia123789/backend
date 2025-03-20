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

// Get the user's CIN and profile image from the request
$cin = $_POST['cin'] ?? null;
$profileImage = $_FILES['profileImage'] ?? null;

if (!$cin || !$profileImage) {
    http_response_code(400);
    echo json_encode(["message" => "Missing data."]);
    exit();
}

// Move the uploaded file to the uploads folder
$uploadDir = "uploads/";
$uploadFile = $uploadDir . basename($profileImage['name']);

if (move_uploaded_file($profileImage['tmp_name'], $uploadFile)) {
    // Update the image path in the database
    $stmt = $conn->prepare("UPDATE joueur SET profile_image = ? WHERE cin = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["message" => "Error preparing SQL query."]);
        exit();
    }

    $stmt->bind_param("ss", $uploadFile, $cin);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        http_response_code(200);
        echo json_encode([
            "message" => "Profile image updated successfully!",
            "imagePath" => basename($profileImage['name'])
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error updating profile image."]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["message" => "Error moving uploaded file."]);
}

$conn->close();
?>
