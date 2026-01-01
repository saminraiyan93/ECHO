<html>
    <body>

        <h1>Welcome Back, Login to continue the journey🧑‍🚀</h1>
        <?php
            if (isset($error["login"])) {
                echo "<p style='color:red'>{$error["login"]}</p>";
            }
        ?>
        <form action="loginValidation.php"  method = "POST">

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