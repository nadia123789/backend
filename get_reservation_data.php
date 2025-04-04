<?php
// Database connection
$host = 'localhost';
$dbname = 'matchit';
$username = 'root'; // Your database username
$password = ''; // Your database password

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the CIN from the client (via GET or POST, assuming it's passed)
$cin = isset($_GET['cin']) ? $_GET['cin'] : null;

if (!$cin) {
    echo json_encode(['error' => 'CIN parameter is missing']);
    exit;
}

// Query to get the team name (nom_equipe) from the CIN
$sql = "SELECT nom_equipe FROM equipe WHERE capitaine_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cin);
$stmt->execute();
$result = $stmt->get_result();

// Check if the team exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nom_equipe = $row['nom_equipe']; // Get the team name for this CIN

    // Query to fetch all reservation data for this team or its opponent
    $sql = "SELECT * FROM reservation 
            WHERE (nom_equipe = ? OR nom_equipe_adversaire = ?) 
            ORDER BY created_at DESC"; // Get all reservations, not just confirmed
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nom_equipe, $nom_equipe);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the reservation exists
    if ($result->num_rows > 0) {
        $notifications = [];
        
        // Fetch all reservations and prepare both types of notifications
        while ($reservation = $result->fetch_assoc()) {
            // Always return confirmation notification (confirmation code)
            $notifications[] = [
                'type' => 'confirmation',
                'message' => "Votre code de confirmation est : " . $reservation['confirmation_code'],
                'date' => (new DateTime($reservation['created_at']))->format('c'), // Convert to ISO format
                'time' => date("H:i", strtotime($reservation['created_at'])),
            ];

            // Add match details notification if confirmed
            if ($reservation['is_confirmed'] == 1) {
                $notifications[] = [
                    'type' => 'match_details',
                    'message' => "Match: " . $reservation['nom_equipe'] . " vs " . $reservation['nom_equipe_adversaire'] . "\n" .
                                 "Lieu: " . $reservation['nom_terrain'] . "\n" .
                                 "Date et Heure: " . (new DateTime($reservation['datetime']))->format('Y-m-d H:i:s'),
                    'date' => (new DateTime($reservation['created_at']))->format('c'), // Convert to ISO format
                    'time' => date("H:i", strtotime($reservation['created_at'])),
                ];
            }
        }

        // Return all notifications as JSON
        echo json_encode($notifications);
    } else {
        echo json_encode(['error' => 'No reservation found for this team']);
    }
} else {
    echo json_encode(['error' => 'Team not found for this CIN']);
}

$stmt->close();
$conn->close();
?>
