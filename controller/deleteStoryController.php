<?php
session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
        $_SESSION['restrictedMsg'] = 'You must login first';
        header('Location: ../view/login/login.php');
        exit();
    }

require_once '../model/database.php';

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
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => "❌ Your account is banned. You cannot delete stories."]);
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
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => "⏱️ Your account is restricted. You cannot delete stories until " . date('M d, Y H:i', strtotime($restriction['restriction_end_date']))]);
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
    $userId = $_SESSION['user'];
    
    if($storyId <= 0){
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid story ID']);
        $db->close();
        exit();
    }
    
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
