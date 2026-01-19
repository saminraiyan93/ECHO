<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    $_SESSION['restrictedMsg'] = 'You must login first';
    header('Location: ../view/login/login.php');
    exit();
}

// ✅ Only process POST requests
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    require_once '../config/database.php';

    $db = new Database();
    $connection = $db->getConnection();
    
    // ✅ Get form data
    $title = trim($_POST["title"]);
    $category = trim($_POST["category"]);
    $contents = trim($_POST["contents"]);
    $user_id = $_SESSION['user']; // Get logged-in user ID
    
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