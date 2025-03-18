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

// Récupérer le CIN de l'utilisateur depuis la requête
$cin = $_POST['cin'] ?? null;

if (!$cin) {
    http_response_code(401);
    echo json_encode(["message" => "CIN de l'utilisateur manquant."]);
    exit();
}

// Vérifier si un fichier a été téléchargé
if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Créer le dossier s'il n'existe pas
    }
    $uploadFile = $uploadDir . basename($_FILES['profileImage']['name']);

    if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadFile)) {
        // Mettre à jour le champ profileImage dans la base de données
        $stmt = $conn->prepare("UPDATE joueur SET profileImage = ? WHERE cin = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
            exit();
        }

        $stmt->bind_param("ss", $uploadFile, $cin);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Image de profil mise à jour avec succès.", "profileImage" => $uploadFile]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Échec de la mise à jour de l'image de profil."]);
        }

        $stmt->close();
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Échec du téléchargement de l'image."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Aucun fichier téléchargé ou erreur lors du téléchargement."]);
}

$conn->close();
?>