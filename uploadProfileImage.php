<?php
header("Access-Control-Allow-Origin: *");
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

// Vérifier si un fichier a été envoyé
if (!isset($_FILES['profileImage']) || !isset($_POST['cin'])) {
    http_response_code(400);
    echo json_encode(["message" => "Fichier ou CIN manquant."]);
    exit();
}

$cin = $_POST['cin'];
$uploadDir = __DIR__ . '/../../uploads/profiles/'; // Chemin relatif vers le dossier uploads/profiles
$fileName = uniqid() . '_' . basename($_FILES['profileImage']['name']); // Nom unique du fichier
$uploadFile = $uploadDir . $fileName;

// Vérifier si le dossier existe, sinon le créer
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Déplacer le fichier uploadé vers le dossier de destination
if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $uploadFile)) {
    // Mettre à jour la base de données avec le nom du fichier
    $stmt = $conn->prepare("UPDATE joueur SET profile_image = ? WHERE cin = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
        exit();
    }

    $stmt->bind_param("ss", $fileName, $cin); // 's' pour string
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Image de profil mise à jour avec succès.", "imagePath" => $fileName]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Erreur lors de la mise à jour de l'image de profil dans la base de données."]);
    }

    $stmt->close();
} else {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors du téléchargement de l'image."]);
}

$conn->close();
?>