<html>
   <head> <link rel="stylesheet" href="login.css"> </head>

    <body>

       
        <?php
            if (isset($error["login"])) {
                echo "<p style='color:red'>{$error["login"]}</p>";
            }
        ?>
        <form action="loginValidation.php"  method = "POST">

          <div class="login-wrapper">

    <div class="login-left">
        <h2>Welcome Back</h2>
        <p>Login to continue your journey with Echo.</p>
    </div>

    <div class="login-right">

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

          <div class="form-footer">
            New user?
            <a href="signUp.php">SignUp</a>
        </div>
    
    </div>

</div>


        </form>

        

    </body>
</html>