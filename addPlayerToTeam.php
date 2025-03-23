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

// Handle POST request to add player to team
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the incoming JSON data
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input.']);
        exit;
    }

    // Validate input data
    if (!isset($inputData['playerCin']) || !isset($inputData['captainCin'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing playerCin or captainCin in request.']);
        exit;
    }

    $playerCin = $inputData['playerCin'];
    $captainCin = $inputData['captainCin'];

    // Step 1: Check if the player CIN exists in the joueur table
    $checkPlayerQuery = $conn->prepare("SELECT * FROM joueur WHERE cin = ?");
    $checkPlayerQuery->bind_param("s", $playerCin);
    $checkPlayerQuery->execute();
    $checkPlayerResult = $checkPlayerQuery->get_result();

    if ($checkPlayerResult->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Player does not exist in the joueur table.']);
        exit;
    }

    // Step 2: Check if the captainCin exists in the equipe table
    $checkTeamQuery = $conn->prepare("SELECT * FROM equipe WHERE capitaine_id = ?");
    $checkTeamQuery->bind_param("s", $captainCin);
    $checkTeamQuery->execute();
    $checkTeamResult = $checkTeamQuery->get_result();

    if ($checkTeamResult->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'No team found with the provided captain CIN.']);
        exit;
    }

    $teamData = $checkTeamResult->fetch_assoc();
    $teamIdFromRequest = $teamData['id_equipe']; // Get the team ID from the equipe table

    // Step 3: Check if the player already belongs to a team
    $checkPlayerEquipeQuery = $conn->prepare("SELECT * FROM joueur WHERE cin = ? AND id_equipe IS NOT NULL");
    $checkPlayerEquipeQuery->bind_param("s", $playerCin);
    $checkPlayerEquipeQuery->execute();
    $checkPlayerEquipeResult = $checkPlayerEquipeQuery->get_result();

    if ($checkPlayerEquipeResult->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Player is already assigned to a team.']);
        exit;
    }

    // Step 4: Retrieve team data to check available slots
    $checkTeamQuery = $conn->prepare("SELECT * FROM equipe WHERE id_equipe = ?");
    $checkTeamQuery->bind_param("s", $teamIdFromRequest);
    $checkTeamQuery->execute();
    $teamResult = $checkTeamQuery->get_result();
    $teamData = $teamResult->fetch_assoc();

    // Check which player slot is available
    $availableSlot = null;
    for ($i = 1; $i <= 9; $i++) {
        if ($teamData["player_" . $i . "_cin"] === null) {
            $availableSlot = $i;
            break;
        }
    }

    // If no slot is available (team is full)
    if ($availableSlot === null) {
        echo json_encode(['status' => 'error', 'message' => 'No available slot in the team.']);
        exit;
    }

    // Step 5: Update the team with the player CIN in the available slot
    $updateTeamQuery = $conn->prepare("UPDATE equipe SET player_{$availableSlot}_cin = ? WHERE id_equipe = ?");
    $updateTeamQuery->bind_param("ss", $playerCin, $teamIdFromRequest);

    if ($updateTeamQuery->execute()) {
        // Step 6: Update the player's id_equipe to link them to the team
        $updatePlayerEquipeQuery = $conn->prepare("UPDATE joueur SET id_equipe = ? WHERE cin = ?");
        $updatePlayerEquipeQuery->bind_param("ss", $teamIdFromRequest, $playerCin);

        if ($updatePlayerEquipeQuery->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Player added to the team successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error updating player in joueur table']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error adding player to equipe table']);
    }
} 
// Handle GET request to retrieve team players by teamName
// Handle GET request to retrieve team players by teamName
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Ensure teamName is provided in the query parameters
    if (!isset($_GET['teamName'])) {
        echo json_encode([
            "status" => "error",
            "message" => "Missing teamName parameter."
        ]);
        exit();
    }

    $teamName = $_GET['teamName'];

    // Update the SQL query to use the correct column name for team name
    $sql = $conn->prepare("SELECT id_equipe FROM equipe WHERE nom_equipe = ?"); // Corrected column name
    $sql->bind_param("s", $teamName);
    $sql->execute();
    $result = $sql->get_result();

    if ($result && $result->num_rows > 0) {
        $team = $result->fetch_assoc();
        $teamId = $team['id_equipe'];

        // Query to fetch players for the team
        $playersQuery = $conn->prepare("SELECT * FROM joueur WHERE id_equipe = ?");
        $playersQuery->bind_param("s", $teamId);
        $playersQuery->execute();
        $playersResult = $playersQuery->get_result();

        $players = [];
        while ($player = $playersResult->fetch_assoc()) {
            $players[] = $player;
        }

        echo json_encode([
            "status" => "success",
            "players" => $players
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Team not found."
        ]);
    }
}

else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
