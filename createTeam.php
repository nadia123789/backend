<?php
// Database connection
$host = 'localhost';
$dbname = 'matchit';
$username = 'root'; // Change with your database username
$password = ''; // Change with your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Ensure request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the CIN (captain's ID) from the request
    $captainId = $_POST['captain_id'] ?? null;
    $teamName = $_POST['teamName'] ?? null;

    if (!$captainId || !$teamName) {
        echo json_encode(['success' => false, 'message' => 'Missing data.']);
        exit();
    }

    // Check if the user is already a captain
    $stmt = $pdo->prepare("SELECT * FROM equipe WHERE capitaine_id = :captain_id");
    $stmt->execute(['captain_id' => $captainId]);
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already a captain and cannot create another team.']);
        exit();
    }

    // Handle the file upload
    if (isset($_FILES['teamImage']) && $_FILES['teamImage']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $fileTmpName = $_FILES['teamImage']['tmp_name'];
        $fileName = basename($_FILES['teamImage']['name']);
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($fileTmpName, $filePath)) {
            // Prepare the SQL insert statement
            $stmt = $pdo->prepare("INSERT INTO equipe (nom_equipe, logo, capitaine_id) VALUES (:teamName, :logo, :captainId)");
            $stmt->execute([
                'teamName' => $teamName,
                'logo' => $filePath,
                'captainId' => $captainId
            ]);

            // Mettre à jour la colonne 'capitain' du joueur qui a créé l'équipe
            $updateStmt = $pdo->prepare("UPDATE joueur SET capitain = 1 WHERE cin = :captainId");
            $updateStmt->execute(['captainId' => $captainId]);

            echo json_encode(['success' => true, 'message' => 'Team created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No image uploaded or error uploading file.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
