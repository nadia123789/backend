<?php
header("Content-Type: application/json; charset=UTF-8");

// Connexion à la base de données
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$password = ""; // Remplacez par votre mot de passe MySQL
$dbname = "matchit"; // Remplacez par le nom de votre base de données

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Échec de la connexion à la base de données."]);
    exit();
}

// Récupérer l'ID de l'utilisateur et l'image
$user_id = $_POST['user_id'] ?? null;
$profileImage = $_FILES['profileImage'] ?? null;

if (!$user_id || !$profileImage) {
    http_response_code(400);
    echo json_encode(["message" => "Données manquantes."]);
    exit();
}

// Déplacer l'image téléchargée vers un dossier sur le serveur
$uploadDir = "uploads/";
$uploadFile = $uploadDir . basename($profileImage['name']);

if (move_uploaded_file($profileImage['tmp_name'], $uploadFile)) {
    // Mettre à jour le chemin de l'image dans la base de données
    $stmt = $conn->prepare("UPDATE joueur SET profile_image = ? WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
        exit();
    }

    $stmt->bind_param("si", $uploadFile, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Image de profil mise à jour avec succès!"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la mise à jour de l'image de profil."]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors du téléchargement de l'image."]);
}

$conn->close();
?>