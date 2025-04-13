<?php
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "matchit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$cin = $_GET['cin'] ?? null;
if (!$cin) {
    http_response_code(400);
    echo json_encode(["error" => "Player CIN is required"]);
    exit();
}

// 1. Trouver toutes les équipes du joueur (capitaine ou joueur)
$teamQuery = $conn->prepare("
    SELECT e.id_equipe, e.nom_equipe, e.logo 
    FROM equipe e
    WHERE e.capitaine_id = ? 
    OR ? IN (player_1_cin, player_2_cin, player_3_cin, player_4_cin, 
             player_5_cin, player_6_cin, player_7_cin, player_8_cin, player_9_cin)
");
$teamQuery->bind_param("s", $cin, $cin);
$teamQuery->execute();
$teamResult = $teamQuery->get_result();

if ($teamResult->num_rows === 0) {
    http_response_code(200);
    echo json_encode(null);
    exit();
}

$teams = [];
while ($team = $teamResult->fetch_assoc()) {
    $teams[] = $team['nom_equipe'];
}

// 2. Trouver le prochain match confirmé pour ces équipes
$currentDate = date('Y-m-d H:i:s');
$placeholders = implode(',', array_fill(0, count($teams), '?'));

$query = $conn->prepare("
    SELECT 
        r.*,
        home.logo as home_logo,
        away.logo as away_logo
    FROM reservation r
    LEFT JOIN equipe home ON r.nom_equipe = home.nom_equipe
    LEFT JOIN equipe away ON r.nom_equipe_adversaire = away.nom_equipe
    WHERE (r.nom_equipe IN ($placeholders) OR r.nom_equipe_adversaire IN ($placeholders))
    AND r.datetime > ?
    AND r.is_confirmed = 1
    ORDER BY r.datetime ASC
    LIMIT 1
");

// Bind parameters
$types = str_repeat('s', count($teams)) . 's'; // types for teams + current date
$params = array_merge($teams, $teams, [$currentDate]); // teams for both home and away
array_unshift($params, $types);
call_user_func_array([$query, 'bind_param'], $params);

$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    http_response_code(200);
    echo json_encode(null);
    exit();
}

$matchData = $result->fetch_assoc();

// Déterminer quelle équipe est à domicile
$isHomeTeam = in_array($matchData['nom_equipe'], $teams);

$response = [
    'nom_equipe' => $matchData['nom_equipe'],
    'nom_equipe_adversaire' => $matchData['nom_equipe_adversaire'],
    'datetime' => $matchData['datetime'],
    'nom_terrain' => $matchData['nom_terrain'],
    'confirmation_code' => $matchData['confirmation_code'],
    'home_logo' => $isHomeTeam ? $matchData['home_logo'] : $matchData['away_logo'],
    'away_logo' => $isHomeTeam ? $matchData['away_logo'] : $matchData['home_logo']
];

// Si le joueur est dans l'équipe adverse, inverser les noms
if (!$isHomeTeam) {
    $response['nom_equipe'] = $matchData['nom_equipe_adversaire'];
    $response['nom_equipe_adversaire'] = $matchData['nom_equipe'];
}

http_response_code(200);
echo json_encode($response);

$teamQuery->close();
$query->close();
$conn->close();
?>