<?php

            require_once '../config/database.php';

            $db = new Database();
            $connection = $db->getConnection();

            $error = [];
            if($_SERVER["REQUEST_METHOD"] == "POST"){
                $email = $_POST["email"];
                $password = $_POST["password"];

                if(empty($email)){
                    $error["email"] = "email can't be empty <br>";
                }

                if(empty($password)){
                    $error["password"] = "Please Enter your Password";
                }

                

                // if no error found
                if(empty($error)){
                    $sql = "SELECT * FROM USER WHERE user_email = '$email' and password = '$password'";
                    
                    $result = $connection->query($sql); 

                    if($result && $result->num_rows == 1){
 
                        // successful login
                        header("Location: dashboard.php");
                        exit;
                    } else{
                        $error["login"] = "Invalid email or password";
                    }
                }
            }

        ?>