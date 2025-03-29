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

// Get the CIN and entered code from the request body
$data = json_decode(file_get_contents('php://input'), true);
$cin = isset($data['cin']) ? $data['cin'] : null;
$entered_code = isset($data['entered_code']) ? $data['entered_code'] : null;

if (!$cin || !$entered_code) {
    echo json_encode(['error' => 'CIN or code is missing']);
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

    // Query to fetch the latest reservation data for this team (nom_equipe)
    $sql = "SELECT * FROM reservation WHERE nom_equipe = ? ORDER BY created_at DESC LIMIT 1"; // Get the latest reservation
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nom_equipe);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the reservation exists
    if ($result->num_rows > 0) {
        $reservation = $result->fetch_assoc();
        $confirmation_code = $reservation['confirmation_code'];

        // Verify the entered code with the stored confirmation code
        if ($entered_code === $confirmation_code) {
            // Update the reservation to set is_confirmed to 1
            $update_sql = "UPDATE reservation SET is_confirmed = 1 WHERE nom_equipe = ? AND confirmation_code = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $nom_equipe, $confirmation_code);
            $update_stmt->execute();

            // Check if the update was successful
            if ($update_stmt->affected_rows > 0) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Failed to update confirmation status']);
            }

            $update_stmt->close();
        } else {
            echo json_encode(['error' => 'Incorrect confirmation code']);
        }
    } else {
        echo json_encode(['error' => 'No reservation found for this team']);
    }
} else {
    echo json_encode(['error' => 'Team not found for this CIN']);
}

$stmt->close();
$conn->close();
?>
