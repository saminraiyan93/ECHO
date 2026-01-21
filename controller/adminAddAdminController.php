<?php
    session_start();

    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    require_once '../model/database.php';

    $db = new Database();
    $connection = $db->getConnection();
    $response = [];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        
        if($action === 'add'){
            $admin_name = isset($_POST['admin_name']) ? trim($_POST['admin_name']) : '';
            $admin_email = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if(empty($admin_name)){
                $response['success'] = false;
                $response['message'] = 'Admin name is required';
            }
            elseif(empty($admin_email)){
                $response['success'] = false;
                $response['message'] = 'Admin email is required';
            }
            elseif(empty($password)){
                $response['success'] = false;
                $response['message'] = 'Password is required';
            }
            elseif(strlen($password) < 6){
                $response['success'] = false;
                $response['message'] = 'Password must be at least 6 characters';
            }
            else {
                // Check if email already exists
                $checkSql = "SELECT admin_id FROM admin WHERE admin_email = ?";
                $checkStmt = $connection->prepare($checkSql);
                $checkStmt->bind_param('s', $admin_email);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();

                if($checkResult && $checkResult->num_rows > 0){
                    $response['success'] = false;
                    $response['message'] = 'Email already exists';
                } else {
                    // Insert new admin
                    $sql = "INSERT INTO admin (admin_name, admin_email, password) VALUES (?, ?, ?)";
                    $stmt = $connection->prepare($sql);

                    if($stmt){
                        $stmt->bind_param('sss', $admin_name, $admin_email, $password);
                        if($stmt->execute()){
                            $response['success'] = true;
                            $response['message'] = 'Admin added successfully';
                        } else {
                            $response['success'] = false;
                            $response['message'] = 'Failed to add admin: ' . $connection->error;
                        }
                        $stmt->close();
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Database error: ' . $connection->error;
                    }
                }
                $checkStmt->close();
            }
        }
        elseif($action === 'fetch'){
            // Fetch all admins
            $sql = "SELECT admin_id, admin_name, admin_email FROM admin ORDER BY admin_id";
            $result = $connection->query($sql);

            if($result){
                $admins = [];
                while($row = $result->fetch_assoc()){
                    $admins[] = $row;
                }
                $response['success'] = true;
                $response['admins'] = $admins;
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to fetch admins';
            }
        }
        elseif($action === 'delete'){
            $admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : 0;

            // Prevent deleting the current logged-in admin
            if($admin_id == $_SESSION['admin_id']){
                $response['success'] = false;
                $response['message'] = 'Cannot delete your own account';
            }
            elseif($admin_id <= 0){
                $response['success'] = false;
                $response['message'] = 'Invalid admin ID';
            }
            else {
                $sql = "DELETE FROM admin WHERE admin_id = ?";
                $stmt = $connection->prepare($sql);

                if($stmt){
                    $stmt->bind_param('i', $admin_id);
                    if($stmt->execute()){
                        $response['success'] = true;
                        $response['message'] = 'Admin deleted successfully';
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Failed to delete admin';
                    }
                    $stmt->close();
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Database error';
                }
            }
        }
        else {
            $response['success'] = false;
            $response['message'] = 'Invalid action';
        }
    } else {
        $response['success'] = false;
        $response['message'] = 'Invalid request method';
    }

    $db->close();
    header('Content-Type: application/json');
    echo json_encode($response);
?>
