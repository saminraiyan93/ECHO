<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must login first';
    header('Location: ../view/login/login.php');
    exit();
}

require_once '../model/database.php';

$db = new Database();
$connection = $db->getConnection();

$user_id = $_SESSION['user'];

// Check if user is restricted or banned
$restrictionSql = "SELECT * FROM user_restriction 
                   WHERE user_id = ? AND (restriction_end_date > NOW() OR (restriction_type = 'permanent' AND restriction_end_date IS NULL))
                   LIMIT 1";
$restrictionStmt = $connection->prepare($restrictionSql);
$restrictionStmt->bind_param('i', $user_id);
$restrictionStmt->execute();
$restrictionResult = $restrictionStmt->get_result();

if($restrictionResult && $restrictionResult->num_rows > 0){
    $restriction = $restrictionResult->fetch_assoc();
    
    if($restriction['restriction_type'] === 'permanent'){
        $_SESSION['story_error'] = "❌ Your account is banned. You cannot post stories.";
    } else {
        $_SESSION['story_error'] = "⏱️ Your account is restricted. You cannot post stories until " . date('M d, Y H:i', strtotime($restriction['restriction_end_date']));
    }
    
    $restrictionStmt->close();
    $db->close();
    header('Location: ../view/dashboard/dashboard.php');
    exit();
}

$restrictionStmt->close();

// ✅ Only process POST requests
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // ✅ Get form data
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $contents = trim($_POST["contents"]);
    
    // ✅ Validate inputs
    if(empty($title) || empty($category) || empty($contents)){
        $_SESSION['story_error'] = "All fields are required!";
        $db->close();
        header('Location: ../view/dashboard/dashboard.php');
        exit();
    }

    // ✅ Use prepared statement to prevent SQL injection
    $sql = "INSERT INTO story (title, category, contents, user_id, createdAt, vote) 
            VALUES (?, ?, ?, ?, NOW(), 0)";
    
    $stmt = $connection->prepare($sql);
    
    if(!$stmt){
        $_SESSION['story_error'] = "Database error: " . $connection->error;
        $db->close();
        header('Location: ../view/dashboard/dashboard.php');
        exit();
    }
    
    $stmt->bind_param('sssi', $title, $category, $contents, $user_id);
    
    if($stmt->execute()){
        $_SESSION['story_success'] = "Story posted successfully! 🎉";
        $stmt->close();
        $db->close();
        header('Location: ../view/dashboard/dashboard.php');
        exit();
    } else {
        $_SESSION['story_error'] = "Failed to post story. Please try again: " . $stmt->error;
        $stmt->close();
        $db->close();
        header('Location: ../view/dashboard/dashboard.php');
        exit();
    }
    
} else {
    // ✅ If not POST, redirect to dashboard
    $db->close();
    header('Location: ../view/dashboard/dashboard.php');
    exit();
}

?>