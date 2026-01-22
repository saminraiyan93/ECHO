<?php  

session_start();

$error = [];

require_once '../model/database.php';

$db = new Database();
$connection = $db->getConnection();

$success = '';

function generateUserId($connection){
    $sql = "SELECT user_id FROM user ORDER BY user_id DESC LIMIT 1";
    $result = $connection->query($sql);

    if ($result->num_rows == 0) {
        return 1;
    } else {
        $row = $result->fetch_assoc();
        return $row["user_id"] + 1;
    }
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = isset($_POST["name"]) ? trim($_POST["name"]) : '';
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : '';
    $password = isset($_POST["password"]) ? trim($_POST["password"]) : '';
    
    if(empty($name)){
        $error["name"] = "name can't be empty <br>";
    }
    else if(strlen($name)<3){
        $error["name"] = "name must be 3 character long <br>";
    }

    if(empty($email)){
        $error["email"] = "email can't be empty <br>";
    }

    if(empty($password)){
        $error["password"] = "Please Enter your Password";
    }
    else if(strlen($password) < 6){
        $error["password"] = "Password must be atleast 6 characters long.";
    }

    if(!empty($error)){
        $_SESSION['signup_errors'] = $error;
        $connection->close();
        session_write_close();
        header("Location: ../view/signUp/signUp.php");
        exit();
    }

    {
        $userId = generateUserId($connection);
        
        $sql = "INSERT INTO user (user_id, user_name, user_email, password, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = $connection->prepare($sql);
        
        if(!$stmt){
            $error["db"] = "Database error: " . $connection->error;
        } else {
            $stmt->bind_param('isss', $userId, $name, $email, $password);
            $result = $stmt->execute();
            
            if($result){
                $_SESSION['signup_success'] = "Account created successfully! You can now login";
                $stmt->close();
                $connection->close();
                session_write_close();
                header("Location: ../view/signUp/signUp.php");
                exit();
            } else{
                $error["db"] = "Something went wrong while saving. Please try again: " . $stmt->error;
            }
            
            $stmt->close();
        }
    }
}

include '../view/signUp/signUp.php';

?>
