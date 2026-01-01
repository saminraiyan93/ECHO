<html>
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

        <h1>Welcome, Sign-Up to begin your journey🚀</h1>
        <?php if (!empty($success)) { ?>
            <p style="color: green; font-weight: bold;">
                <?php echo $success; ?>
                <br>
                <a href="login.php">Click here to login</a>
            </p>
        <?php } ?>
        <form action="<?php echo $_SERVER['PHP_SELF']?>" method="POST">
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