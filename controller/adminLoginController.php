<?php
    session_start();

    require_once '../model/database.php';

    $error = [];
    $admin_user = null;

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';

        if(empty($email)){
            $error["email"] = "Email can't be empty";
        }

        if(empty($password)){
            $error["password"] = "Please enter your password";
        }

        // If no errors, validate credentials
        if(empty($error)){
            $db = new Database();
            $connection = $db->getConnection();

            // Use prepared statement to prevent SQL injection
            $sql = "SELECT * FROM admin WHERE admin_email = ? AND password = ?";
            $stmt = $connection->prepare($sql);
            
            if(!$stmt){
                $error["login"] = "Database error: " . $connection->error;
            } else {
                $stmt->bind_param('ss', $email, $password);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result && $result->num_rows == 1){
                    // Successful admin login
                    $admin = $result->fetch_assoc();
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_name'] = $admin['admin_name'];
                    $_SESSION['admin_email'] = $admin['admin_email'];

                    // Redirect to admin dashboard
                    $stmt->close();
                    $db->close();
                    header("Location: ../view/admin/admin.php");
                    exit();
                } else {
                    $error["login"] = "Invalid email or password";
                }
                
                $stmt->close();
            }
            
            $db->close();
        }
    }

    // Include the admin login view
    include '../view/admin/adminLogin.php';
?>
