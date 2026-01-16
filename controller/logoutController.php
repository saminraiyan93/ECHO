<?php

// Destroy session on logout
session_start();
session_unset();
session_destroy();

header('Location: ../view/login/login.php');
exit();

?>