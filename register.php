<?php
header("Content-Type: application/json; charset=UTF-8");

// Répondre à la requête OPTIONS (pré-vol)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// Récupération des données du formulaire
$data = json_decode(file_get_contents("php://input"), true);

// Vérification des données reçues
if (
    empty($data['prenom']) ||
    empty($data['nom']) ||
    empty($data['date_of_birth']) ||
    empty($data['sexe']) ||
    empty($data['cin']) ||
    empty($data['telephone']) ||
    empty($data['email']) ||
    empty($data['password'])
) {
    http_response_code(400);
    echo json_encode(["message" => "Tous les champs sont obligatoires."]);
    exit();
}

$prenom = $data['prenom'];
$nom = $data['nom'];
$date_of_birth = $data['date_of_birth'];
$sexe = $data['sexe'];
$cin = $data['cin'];
$telephone = $data['telephone'];
$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_BCRYPT); // Hashage du mot de passe

// Vérification si l'email existe déjà
$stmt = $conn->prepare("SELECT id FROM joueur WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(400);
    echo json_encode(["message" => "Email déjà utilisé."]);
    $stmt->close();
    $conn->close();
    exit();
}

// Insertion des données dans la table `joueur`
$stmt = $conn->prepare("INSERT INTO joueur (prenom, nom, date_of_birth, sexe, cin, telephone, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de la préparation de la requête SQL."]);
    exit();
}

$stmt->bind_param("ssssssss", $prenom, $nom, $date_of_birth, $sexe, $cin, $telephone, $email, $password);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["message" => "Joueur enregistré avec succès."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de l'enregistrement du joueur."]);
}

$stmt->close();
$conn->close();
?>