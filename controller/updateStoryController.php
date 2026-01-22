<?php
session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

require_once __DIR__ . '/../model/database.php';

$db = new Database();
$connection = $db->getConnection();

$user_id = $_SESSION['user'];

$statusSql = "SELECT status FROM user WHERE user_id = ? LIMIT 1";
$statusStmt = $connection->prepare($statusSql);
$statusStmt->bind_param('i', $user_id);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();

if($statusResult && $statusResult->num_rows > 0){
    $userStatus = $statusResult->fetch_assoc();
    
    if($userStatus['status'] === 'banned'){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "❌ Your account is banned. You cannot update stories."]);
        $statusStmt->close();
        $db->close();
        exit();
    }
    
    if($userStatus['status'] === 'restricted'){
        $restrictionSql = "SELECT restriction_end_date FROM user_restriction WHERE user_id = ? LIMIT 1";
        $restrictionStmt = $connection->prepare($restrictionSql);
        $restrictionStmt->bind_param('i', $user_id);
        $restrictionStmt->execute();
        $restrictionResult = $restrictionStmt->get_result();
        
        if($restrictionResult && $restrictionResult->num_rows > 0){
            $restriction = $restrictionResult->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => "⏱️ Your account is restricted. You cannot update stories until " . date('M d, Y H:i', strtotime($restriction['restriction_end_date']))]);
            $restrictionStmt->close();
            $statusStmt->close();
            $db->close();
            exit();
        }
        $restrictionStmt->close();
    }
}
$statusStmt->close();

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    $storyId = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
    $contents = isset($_POST['contents']) ? trim($_POST['contents']) : '';
    $userId = $_SESSION['user'];
    
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
