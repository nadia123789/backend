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
    echo json_encode(["status" => "error", "message" => "Failed to connect to the database."]);
    exit();
}

// Handle POST request to remove player from team
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the incoming JSON data
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input.']);
        exit;
    }

    // Validate input data
    if (!isset($inputData['playerCin'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing playerCin in request.']);
        exit;
    }

    $playerCin = $inputData['playerCin'];

    // Step 1: Retrieve player data by CIN
    $checkPlayerQuery = $conn->prepare("SELECT * FROM joueur WHERE cin = ?");
    $checkPlayerQuery->bind_param("s", $playerCin);
    $checkPlayerQuery->execute();
    $checkPlayerResult = $checkPlayerQuery->get_result();

    if ($checkPlayerResult->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Player does not exist in the joueur table.']);
        exit;
    }

    // Step 2: Get the team ID from the player
    $playerData = $checkPlayerResult->fetch_assoc();
    $teamId = $playerData['id_equipe'];

    if ($teamId == null) {
        echo json_encode(['status' => 'error', 'message' => 'Player is not assigned to a team.']);
        exit;
    }

    // Step 3: Remove player from the team by clearing the player's CIN slot
    $clearPlayerSlotQuery = $conn->prepare("UPDATE equipe SET 
        player_1_cin = CASE WHEN player_1_cin = ? THEN NULL ELSE player_1_cin END,
        player_2_cin = CASE WHEN player_2_cin = ? THEN NULL ELSE player_2_cin END,
        player_3_cin = CASE WHEN player_3_cin = ? THEN NULL ELSE player_3_cin END,
        player_4_cin = CASE WHEN player_4_cin = ? THEN NULL ELSE player_4_cin END,
        player_5_cin = CASE WHEN player_5_cin = ? THEN NULL ELSE player_5_cin END,
        player_6_cin = CASE WHEN player_6_cin = ? THEN NULL ELSE player_6_cin END,
        player_7_cin = CASE WHEN player_7_cin = ? THEN NULL ELSE player_7_cin END,
        player_8_cin = CASE WHEN player_8_cin = ? THEN NULL ELSE player_8_cin END,
        player_9_cin = CASE WHEN player_9_cin = ? THEN NULL ELSE player_9_cin END
        WHERE id_equipe = ?");
    
    $clearPlayerSlotQuery->bind_param("ssssssssss", 
        $playerCin, $playerCin, $playerCin, $playerCin, $playerCin, 
        $playerCin, $playerCin, $playerCin, $playerCin, $teamId);

    if ($clearPlayerSlotQuery->execute()) {
        // Step 4: Update player's team ID to NULL in the joueur table
        $removePlayerFromTeamQuery = $conn->prepare("UPDATE joueur SET id_equipe = NULL WHERE cin = ?");
        $removePlayerFromTeamQuery->bind_param("s", $playerCin);

        if ($removePlayerFromTeamQuery->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Player removed from team successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error removing player from joueur table']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error removing player from team in equipe table']);
    }
}
?>
