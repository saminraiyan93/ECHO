<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if(!isset($error)){
        $error = [];
    }
    
    if(isset($_SESSION['signup_errors'])){
        $error = array_merge($error, $_SESSION['signup_errors']);
        unset($_SESSION['signup_errors']);
    }
?>

<html>
    <head>
        <link rel="stylesheet" href="signUp.css">
    </head>
    <body>
        <div class="signup-wrapper">
            <div class="signup-header">
                <h2>Join Echo Today</h2>
                <p>Create your account and start your journey with a modern, secure and simple platform built for you.</p>
            </div>

            <div class="form-container">
                <form action="../../controller/signUpController.php" method="POST">
                    <h1>Create an account</h1>
                    <p class="subtitle">It's quick and easy.</p>
                    
                    <?php if (!empty($_SESSION['signup_success'])) { ?>
                        <p class="success">
                            <?php echo htmlspecialchars($_SESSION['signup_success']); 
                            unset($_SESSION['signup_success']); ?>
                            <br><br>
                            <a href="../../view/login/login.php" style="color: #166534; text-decoration: none; font-weight: bold;">Click here to login</a>
                        </p>
                    <?php } ?>

                    <label for="name">Name: </label>
            <input type="text" id="name" name="name" placeholder="Enter your Name" >
            <?php
                if(isset($error["name"])){
                    echo "<p class='field-error'>" . htmlspecialchars($error["name"]) . "</p>";
                }
            ?>
            <br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your Email" >
            <?php
                if(isset($error["email"])){
                    echo "<p class='field-error'>" . htmlspecialchars($error["email"]) . "</p>";
                }
            ?>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" >
            <?php
                if(isset($error["password"])){
                    echo "<p class='field-error'>" . htmlspecialchars($error["password"]) . "</p>";
                }
            ?>
            <br>
            <input type="submit" value="Create Account">

                </form>

                <div class="form-footer">
                    Already have an account?
                    <a href="../../view/login/login.php">Login</a>
                </div>
            </div>
        </div>