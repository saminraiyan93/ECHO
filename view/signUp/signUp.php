<?php
    

    // to be implemented
    // check if already logged in 
    // if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true){
    //     header()
    // }
?>

<html>
    <body>
        <h1>Welcome, Sign-Up to begin your journey🚀</h1>
        <?php if (!empty($_SESSION['signup_success'])) { ?>
            <p style="color: green; font-weight: bold;">
                <?php echo $_SESSION['signup_success']; ?>
                <br>
                <a href="../view/login/login.php">Click here to login</a>
            </p>
        <?php } ?>
        <form action="../../controller/signUpController.php" method="POST">
            <label for="name">Name: </label>
            <input type="text" id="name" name="name" placeholder="Enter your Name" >
            <?php
                if(isset($error["name"])){
                    echo "<span style='color:red'>" . $error["name"] . "</span>";
                }
            ?>
            <br>
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
            <input type="submit">

        </form>

        

    </body>
</html>