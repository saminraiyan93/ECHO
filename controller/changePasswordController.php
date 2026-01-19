<?php
session_start();

header('Content-Type: application/json');

// Check login
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

require_once __DIR__ . '/../config/database.php';
$db = new Database();
$connection = $db->getConnection();

$userId = $_SESSION['user'];
$current = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
$new = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
$confirm = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

if(empty($current) || empty($new) || empty($confirm)){
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    $db->close();
    exit();
}

if(strlen($new) < 6){
    echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
    $db->close();
    exit();
}

if($new !== $confirm){
    echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match']);
    $db->close();
    exit();
}

// Fetch current password from DB
$sql = "SELECT password FROM user WHERE user_id = ? LIMIT 1";
$stmt = $connection->prepare($sql);
if(!$stmt){
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
    $db->close();
    exit();
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

if(!$result || $result->num_rows === 0){
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $stmt->close();
    $db->close();
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Note: existing app stores plain-text passwords; compare directly to remain compatible
if($row['password'] !== $current){
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    $db->close();
    exit();
}

// Update password
$updateSql = "UPDATE user SET password = ? WHERE user_id = ?";
$upStmt = $connection->prepare($updateSql);
if(!$upStmt){
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
    $db->close();
    exit();
}

$upStmt->bind_param('si', $new, $userId);
if($upStmt->execute()){
    // If remember password cookie exists, update it
    if(isset($_COOKIE['remember_password'])){
        setcookie('remember_password', $new, time()+86400*30, '/');
    }

    echo json_encode(['success' => true, 'message' => 'Password updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password: ' . $upStmt->error]);
}

$upStmt->close();
$db->close();

?>
