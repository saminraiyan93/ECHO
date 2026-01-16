<?php
session_start();

// Redirect to dashboard if already logged in
if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true){
    header('Location: ../dashboard/dashboard.php');
    exit();
}

$restrictedMsg = '';    
// Get restricted msg if exits
if(isset($_SESSION['restrictedMsg'])){
    $restrictedMsg = $_SESSION['restrictedMsg'];    // Step 1: Copy to variable
    unset($_SESSION['restrictedMsg']);          // Step 2: Delete from session
}

// cookie -- remember me func.

?>

<html>
    <body>

        <h1>Welcome Back, Login to continue the journey🧑‍🚀</h1>

        <!-- Display restricted access message -->
        <?php if(!empty($restrictedMsg)){ ?>
            <p style="color: orange; font-weight: bold; background: #fff3cd; padding: 10px; border-radius: 5px;">
                ⚠️ <?php echo $restrictedMsg; ?>
            </p>
        <?php } ?>
        <!-- Display login error if exists -->
        <?php
            if (isset($error["login"])) {
                echo "<p style='color:red'>{$error["login"]}</p>";
            }
        ?>
        <form action="../../controller/loginValidation.php"  method = "POST">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your Email" >
            <?php
                if(isset($error["email"])){
                    echo "<span style='color:red'>" . $error["email"] . "</span>";
                }
            ?>
            <br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" >
            <?php
                if(isset($error["password"])){
                    echo "<span style='color:red'>" . $error["password"] . "</span>";
                }
            ?>
            <br>
            <!-- Add Remember me func  -->
            <input type="submit">

        </form>

    </body>
</html>