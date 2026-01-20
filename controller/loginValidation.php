<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    session_start(); 

    require_once '../model/database.php';

    $db = new Database();
    $connection = $db->getConnection();

    $error = [];
    
    // DEBUG: Check if form was submitted
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        error_log("Login form submitted - Email: " . (isset($_POST["email"]) ? $_POST["email"] : "not set"));
        
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
            // First check if it's an admin account
            $adminSql = "SELECT * FROM admin WHERE admin_email = ? AND password = ?";
            $adminStmt = $connection->prepare($adminSql);
            
            if($adminStmt){
                $adminStmt->bind_param('ss', $email, $password);
                $adminStmt->execute();
                $adminResult = $adminStmt->get_result();
                
                if($adminResult && $adminResult->num_rows == 1){
                    // Successful admin login
                    $admin = $adminResult->fetch_assoc();
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_name'] = $admin['admin_name'];
                    $_SESSION['admin_email'] = $admin['admin_email'];
                    $_SESSION['user_type'] = 'admin';

                    // Set cookie implementation
                    if(isset($_POST['remember_me'])){
                        setcookie("remember_email", $email, time()+86400*30, '/');
                        setcookie("remember_password", $password, time()+86400*30, '/');
                    } 
                    else{
                        if(isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])){
                            setcookie('remember_email', '', time()-3600, '/');
                            setcookie('remember_password', '', time()-3600, '/');
                        }
                    }

                    // Redirect to admin dashboard
                    $adminStmt->close();
                    $db->close();
                    session_write_close();  // Ensure session is written before redirect
                    header("Location: ../view/admin/admin.php");
                    exit();
                }
                $adminStmt->close();
            }

            // Check if it's a regular user account
            $userSql = "SELECT * FROM user WHERE user_email = ? AND password = ?";
            $userStmt = $connection->prepare($userSql);
            
            if(!$userStmt){
                $error["login"] = "Database error: " . $connection->error;
            } else {
                $userStmt->bind_param('ss', $email, $password);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                
                if($userResult && $userResult->num_rows == 1){
                    // Fetch user record
                    $user = $userResult->fetch_assoc();
                    $userStatus = isset($user['status']) ? $user['status'] : 'active';

                    // Check user status and handle accordingly
                    if($userStatus === 'banned'){
                        // User is banned - deny login
                        $error["login"] = "❌ Your account has been banned. You no longer have access to ECHO.";
                    } 
                    else {
                        // User is active or restricted - allow login (dashboard will show restriction banner if needed)
                        $clearSql = "UPDATE user_restriction SET restriction_end_date = NOW() WHERE user_id = ? AND (restriction_end_date > NOW() OR restriction_type = 'permanent' OR restriction_end_date IS NULL)";
                        $clearStmt = $connection->prepare($clearSql);
                        if($clearStmt){
                            $clearStmt->bind_param('i', $user['user_id']);
                            $clearStmt->execute();
                            $clearStmt->close();
                        }

                        // Successful user login
                        $_SESSION['user'] = $user['user_id'];
                        $_SESSION['user_name'] = $user['user_name'];
                        $_SESSION['user_email'] = $user['user_email'];
                        $_SESSION['logged_in'] = true;
                        $_SESSION['user_type'] = 'user';

                        // Set cookie implementation
                        if(isset($_POST['remember_me'])){
                            setcookie("remember_email", $email, time()+86400*30, '/');
                            setcookie("remember_password", $password, time()+86400*30, '/');
                        } 
                        else{
                            if(isset($_COOKIE['remember_email']) && isset($_COOKIE['remember_password'])){
                                setcookie('remember_email', '', time()-3600, '/');
                                setcookie('remember_password', '', time()-3600, '/');
                            }
                        }

                        // Redirect to dashboard
                        $userStmt->close();
                        $db->close();
                        session_write_close();  // Ensure session is written before redirect
                        header("Location: ../view/dashboard/dashboard.php");
                        exit();
                    }
                } else{
                    $error["login"] = "Invalid email or password";
                }
                
                $userStmt->close();
            }
        }
    }
    
    $db->close();

    // Include the view at the end
    include '../view/login/login.php';
