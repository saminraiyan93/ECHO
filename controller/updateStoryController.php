<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Only process POST requests
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    require_once __DIR__ . '/../config/database.php';
    
    $db = new Database();
    $connection = $db->getConnection();
    
    // Get data from POST
    $storyId = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $contents = isset($_POST['contents']) ? trim($_POST['contents']) : '';
    $userId = $_SESSION['user'];
    
    // Validate
    if($storyId <= 0){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid story ID']);
        $db->close();
        exit();
    }
    
    if(empty($title) || empty($category) || empty($contents)){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        $db->close();
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $sql = "UPDATE story 
            SET title = ?, 
                category = ?, 
                contents = ?, 
                updatedAt = NOW() 
            WHERE story_id = ? AND user_id = ?";
    
    $stmt = $connection->prepare($sql);
    
    if(!$stmt){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
        $db->close();
        exit();
    }
    
    $stmt->bind_param('sssii', $title, $category, $contents, $storyId, $userId);
    
    if($stmt->execute()){
        if($stmt->affected_rows > 0){
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Story updated successfully']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No changes made or story not found']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
    $db->close();
    
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
