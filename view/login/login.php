<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DEBUG OUTPUT
echo "<!-- DEBUG: POST data: " . print_r($_POST, true) . " -->";
echo "<!-- DEBUG: REQUEST_METHOD: " . $_SERVER["REQUEST_METHOD"] . " -->";

//Redirect to dashboard if already logged in
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true){
    header('Location: ../dashboard/dashboard.php');
    exit();
}

//Redirect to admin dashboard if admin already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true){
    header('Location: ../admin/admin.php');
    exit();
}

// Initialize error array if not set
if(!isset($error)){
    $error = [];
}

$restrictedMsg = '';    
// Get restricted msg if exits
if(isset($_SESSION['restrictedMsg'])){
    $restrictedMsg = $_SESSION['restrictedMsg'];    // Step 1: Copy to variable
    unset($_SESSION['restrictedMsg']);          // Step 2: Delete from session
}

// cookie -- remember me func.
// check if email and password cookie exists and auto-fill
$remembered_email = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
$remembered_pass = isset($_COOKIE['remember_password']) ? $_COOKIE['remember_password'] : '';

?>

<html>
    <body>

        <h1>Welcome Back, Login to continue the journey🧑‍🚀</h1>

        <!-- Display restricted access message -->
        <?php if(!empty($restrictedMsg)){ ?>
            <p style="color: orange; font-weight: bold; background: #fff3cd; padding: 10px; border-radius: 5px;">
                ⚠️ <?php echo htmlspecialchars($restrictedMsg); ?>
            </p>
        <?php } ?>
        
        <!-- Display login error if exists -->
        <?php
            if (!empty($error["login"])) {
                echo "<p style='color:red; font-weight: bold; background: #ffebee; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($error["login"]) . "</p>";
            }
        ?>

        <!-- Info: Both User and Admin Login -->
        <p style="color: #666; font-size: 13px; background: #f0f0f0; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            💡 <strong>Note:</strong> Use this form for both user and admin login. Your credentials will be verified against the appropriate account type.
        </p>

        <form action="/ECHO/controller/loginValidation.php"  method="POST">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your Email"
            value="<?php echo htmlspecialchars($remembered_email); ?>"   
            >
            <?php
                if(isset($error["email"])){
                    echo "<span style='color:red'>" . $error["email"] . "</span>";
                }
            ?>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password"
            value = "<?php echo htmlspecialchars($remembered_pass); // auto-fill if cookie exits, else empty string ?>"
            >
            <?php
                if(isset($error["password"])){
                    echo "<span style='color:red'>" . $error["password"] . "</span>";
                }
            ?>
            <br>
            <!-- Add Remember me func  -->
            <input type="checkbox" id="remember_me" name="remember_me">
            <label for="remember_me">Remember Me</label>
            <br><br>
            <input type="submit">

        </form>

    </body>
</html>

