<?php

// Destroy session on logout
session_start();
session_unset();
session_destroy();

// Delete "Remember Me" cookies on logout
if(isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])){
    setcookie('remember_email', '', time()-3600, '/');
    setcookie('remember_password', '', time()-3600, '/');
}

header('Location: ../view/login/login.php');
exit();

?>