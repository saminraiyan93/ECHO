<html>
    <head> <link rel="stylesheet" href="signup.css"> </head>

    <body>
        <?php   // VALIDATION LOGIC --> to be moved to a separate file later on

            $error = [];

            require_once '../config/database.php';

            $db = new Database();
            $connection = $db->getConnection();

            $success = '';

            // Auto Increment User Id
            function generateUserId($connection){
                $sql = "SELECT user_id FROM user ORDER BY user_id DESC LIMIT 1";
                $result = $connection->query($sql);

                if ($result->num_rows == 0) {
                    // no user exists yet
                    return 1;
                } else {
                    $row = $result->fetch_assoc();
                    return $row["user_id"] + 1;
                }
            }

            
            
            // Validation
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                    $name = $_POST["name"];
                    $userId = generateUserId($connection);

                if(empty($name)){
                    $error["name"] = "name can't be empty <br>";
                }

                else if(strlen($name)<3){
                    $error["name"] = "name must be 3 character long <br>";
                }

                $email = $_POST["email"];

                if(empty($email)){
                    $error["email"] = "email can't be empty <br>";
                }

                $password = $_POST["password"];

                if(empty($password)){
                    $error["password"] = "Please Enter your Password";
                }

                else if(strlen($password) < 6){
                    $error["password"] = "Password must be atleast 6 characters long.";
                }

                // if no error found
                if(empty($error)){
                    $sql = "INSERT INTO user (user_id, user_name, user_email, password, status) VALUES ('$userId','$name', '$email', '$password', 'active')";

                    if($connection->query($sql)){
                        $success = "Account created successfully! You can now login";

                        // clear fields
                        $name = $email = '';
                        
                    } else{
                        $error["db"] = "Something went wrong while saving. Please try again.";
                    }

                }

            }

        ?>


        <div class="signup-wrapper">

    <!-- LEFT SIDE -->
    <div class="signup-left">
        <h2>Join Echo Today</h2>
        <p>
            Create your account and start your journey with a modern,
            secure and simple platform built for you.
        </p>
    </div>

    <!-- RIGHT SIDE -->
    <div class="signup-right">

        <h1>Create an account</h1>
        <p class="subtitle">It’s quick and easy.</p>

        <?php if (!empty($success)) { ?>
            <p style="color: green; font-weight: 600;">
                <?php echo $success; ?><br>
                <a href="login.php">Click here to login</a>
            </p>
        <?php } ?>

        <form action="<?php echo $_SERVER["PHP_SELF"] ?>" method="POST">

            <div class="input-group">
                <label>Name</label>
                <input type="text" name="name" placeholder="Enter your name">
                <?php if(isset($error["name"])) echo "<span>".$error["name"]."</span>"; ?>
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email">
                <?php if(isset($error["email"])) echo "<span>".$error["email"]."</span>"; ?>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create password">
                <?php if(isset($error["password"])) echo "<span>".$error["password"]."</span>"; ?>
            </div>

            <input type="submit" value="Create Account">

        </form>

        <div class="form-footer">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

    </div>

</div>

        

    </body>
</html>