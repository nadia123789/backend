<?php
header("Content-Type: application/json; charset=UTF-8");

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "matchit";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur de connexion à la base de données."]);
    exit();
}

// Récupérer toutes les équipes avec les infos du capitaine
$sql = "SELECT 
            e.id_equipe, 
            e.nom_equipe, 
            e.logo, 
            e.capitaine_id,
            j.prenom AS capitaine_prenom,
            j.nom AS capitaine_nom,
            j.telephone AS capitaine_telephone,
            j.email AS capitaine_email
        FROM equipe e
        JOIN joueur j ON e.capitaine_id = j.cin";
$result = $conn->query($sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["message" => "Erreur lors de l'exécution de la requête: " . $conn->error]);
    exit();
}

$teams = [];

while ($row = $result->fetch_assoc()) {
    $teams[] = [
        'id_equipe' => $row['id_equipe'],
        'nom_equipe' => $row['nom_equipe'],
        'logo' => $row['logo'],
        'capitaine_id' => $row['capitaine_id'],
        'capitaine' => [
            'prenom' => $row['capitaine_prenom'],
            'nom' => $row['capitaine_nom'],
            'telephone' => $row['capitaine_telephone'],
            'email' => $row['capitaine_email']
        ]
    ];
}

// Envoyer les données au format JSON
echo json_encode($teams);

// Fermer la connexion
$conn->close();
?>