<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
        $_SESSION['restrictedMsg'] = 'You must login first';
        header('Location: ../view/login/login.php');
        exit();
    }

// Only process POST requests
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    require_once '../model/database.php';

    $db = new Database();
    $connection = $db->getConnection();
    
    // Get story ID from POST data
    $storyId = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;
    $userId = $_SESSION['user'];
    
    // Validate story ID
    if($storyId <= 0){
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid story ID']);
        $db->close();
        exit();
    }
    
    // Use prepared statement to prevent SQL injection
    $sql = "DELETE FROM story WHERE story_id = ? AND user_id = ?";
    $stmt = $connection->prepare($sql);
    
    if(!$stmt){
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
        $db->close();
        exit();
    }
    
    $stmt->bind_param('ii', $storyId, $userId);
    
    if($stmt->execute()){
        if($stmt->affected_rows > 0){
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Story deleted successfully']);
        } else {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Story not found or you do not have permission']);
        }
    } else {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
    $db->close();
    
} else {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
