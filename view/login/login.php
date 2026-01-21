<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true){
    header('Location: ../dashboard/dashboard.php');
    exit();
}

if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header('Location: ../admin/admin.php');
    exit();
}

if(!isset($error)){
    $error = [];
}

if(isset($_SESSION['login_error'])){
    $error["login"] = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if(isset($_SESSION['login_errors'])){
    $error = array_merge($error, $_SESSION['login_errors']);
    unset($_SESSION['login_errors']);
}

$restrictedMsg = '';    
if(isset($_SESSION['restrictedMsg'])){
    $restrictedMsg = $_SESSION['restrictedMsg'];    
    unset($_SESSION['restrictedMsg']);          
}

$remembered_email = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
$remembered_pass = isset($_COOKIE['remember_password']) ? $_COOKIE['remember_password'] : '';

?>

<html>
    <head>
        <link rel="stylesheet" href="login.css">
    </head>
    <body>
        <div class="login-wrapper">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Login to continue your journey with Echo.</p>
            </div>

            <div class="form-container">
                <form action="/ECHO/controller/loginValidation.php"  method="POST">

                    <h1>Login</h1>

                    <!-- Display restricted access message -->
                    <?php if(!empty($restrictedMsg)){ ?>
                        <p class="warning"><?php echo htmlspecialchars($restrictedMsg); ?></p>
                    <?php } ?>
                    
                    <!-- Display login error if exists -->
                    <?php
                        if (!empty($error["login"])) {
                            echo "<p class='error'>" . htmlspecialchars($error["login"]) . "</p>";
                        }
                    ?>

                    <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your Email"
            value="<?php echo htmlspecialchars($remembered_email); ?>"   
            >
            <?php
                if(isset($error["email"])){
                    echo "<p class='field-error'>" . htmlspecialchars($error["email"]) . "</p>";
                }
            ?>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password"
            value = "<?php echo htmlspecialchars($remembered_pass); // auto-fill if cookie exits, else empty string ?>"
            >
            <?php
                if(isset($error["password"])){
                    echo "<p class='field-error'>" . htmlspecialchars($error["password"]) . "</p>";
                }
            ?>
            <br>
            <!-- Add Remember me func  -->
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Remember Me</label>
            <br><br>
                    <input type="submit">

                </form>

                <div class="form-footer">
                    New user?
                    <a href="../../view/signUp/signUp.php">SignUp</a>
                </div>
            </div>
        </div>

