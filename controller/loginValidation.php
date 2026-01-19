<?php
    session_start(); 

    require_once '../config/database.php';

    $db = new Database();
    $connection = $db->getConnection();

    $error = [];
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';

        if(empty($email)){
            $error["email"] = "email can't be empty <br>";
        }

        if(empty($password)){
            $error["password"] = "Please Enter your Password";
        }

        // if no error found
        if(empty($error)){
            // Use prepared statement to prevent SQL injection
            $sql = "SELECT * FROM user WHERE user_email = ? AND password = ?";
            $stmt = $connection->prepare($sql);
            
            if(!$stmt){
                $error["login"] = "Database error: " . $connection->error;
            } else {
                $stmt->bind_param('ss', $email, $password);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result && $result->num_rows == 1){
                    // successful login
                    $user = $result->fetch_assoc();
                    $_SESSION['user'] = $user['user_id'];
                    $_SESSION['user_name'] = $user['user_name'];
                    $_SESSION['user_email'] = $user['user_email'];
                    $_SESSION['logged_in'] = true;

                    // Set cookie implementation
                    if(isset($_POST['remember_me'])){
                        // cookie expires in 30 days
                        setcookie("remember_email", $email, time()+86400*30, '/');  // email cookie
                        setcookie("remember_password", $password, time()+86400*30, '/');  // remember password cookie
                    } 
                    else{
                        // If not checked, delete existing cookie
                        if(isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])){
                            setcookie('remember_email', '', time()-3600, '/');
                            setcookie('remember_password', '', time()-3600, '/');
                        }
                    }

                    // redirect to dashboard
                    $stmt->close();
                    $db->close();
                    header("Location: ../view/dashboard/dashboard.php");
                    exit();
                } else{
                    $error["login"] = "Invalid email or password";
                }
                
                $stmt->close();
            }
        }
    }
    
    $db->close();

    // Include the view at the end
    include '../view/login/login.php';
