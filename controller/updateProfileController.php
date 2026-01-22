<?php
session_start();

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    require_once __DIR__ . '/../model/database.php';
    
    $db = new Database();
    $connection = $db->getConnection();
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $userId = $_SESSION['user'];
    
    if(empty($username) || empty($email)){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        $db->close();
        exit();
    }
    
    $checkSql = "SELECT user_id FROM user WHERE user_email = ? AND user_id != ?";
    $checkStmt = $connection->prepare($checkSql);
    
    if(!$checkStmt){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
        $db->close();
        exit();
    }
    
    $checkStmt->bind_param('si', $email, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if($checkResult && $checkResult->num_rows > 0){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email already in use by another account']);
        $checkStmt->close();
        $db->close();
        exit();
    }
    
    $checkStmt->close();
    
    $sql = "UPDATE user 
            SET user_name = ?, 
                user_email = ? 
            WHERE user_id = ?";
    
    $stmt = $connection->prepare($sql);
    
    if(!$stmt){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
        $db->close();
        exit();
    }
    
    $stmt->bind_param('ssi', $username, $email, $userId);
    
    if($stmt->execute()){
        $_SESSION['user_name'] = $username;
        $_SESSION['user_email'] = $email;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
    $db->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
