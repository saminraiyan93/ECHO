<?php
    session_start();
    
    // Destroy the admin session
    $_SESSION['admin_logged_in'] = false;
    $_SESSION['admin_id'] = null;
    $_SESSION['admin_name'] = null;
    $_SESSION['admin_email'] = null;
    $_SESSION['user_type'] = null;
    
    session_destroy();
    
    // Redirect to login
    header('Location: ../view/login/login.php');
    exit();
?>
