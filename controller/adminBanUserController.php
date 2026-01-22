<?php
    session_start();

    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    require_once '../model/database.php';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $restriction_days = isset($_POST['restriction_days']) ? intval($_POST['restriction_days']) : 0;
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

        $db = new Database();
        $connection = $db->getConnection();
        $response = [];

        if($user_id <= 0){
            $response['success'] = false;
            $response['message'] = 'Invalid user ID';
        }
        elseif($action === 'ban'){
            $restriction_type = 'permanent';
            $restriction_end_date = NULL;
            $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : NULL;

            $sql = "INSERT INTO user_restriction (user_id, restriction_type, restriction_reason, restriction_end_date, admin_id) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $connection->prepare($sql);
            
            if($stmt){
                $stmt->bind_param('isssi', $user_id, $restriction_type, $reason, $restriction_end_date, $admin_id);
                if($stmt->execute()){
                    $updateUserSql = "UPDATE user SET status = 'banned' WHERE user_id = ?";
                    $updateStmt = $connection->prepare($updateUserSql);
                    $updateStmt->bind_param('i', $user_id);
                    $updateStmt->execute();
                    $updateStmt->close();

                    $response['success'] = true;
                    $response['message'] = 'User banned permanently';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Failed to ban user: ' . $connection->error;
                }
                $stmt->close();
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: ' . $connection->error;
            }
        }
        elseif($action === 'restrict'){
            if($restriction_days <= 0){
                $response['success'] = false;
                $response['message'] = 'Please specify restriction days (greater than 0)';
            } else {
                $restriction_type = 'temporary';
                $restriction_end_date = date('Y-m-d H:i:s', strtotime("+$restriction_days days"));
                $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : NULL;

                $checkSql = "SELECT * FROM user_restriction WHERE user_id = ? AND restriction_end_date > NOW()";
                $checkStmt = $connection->prepare($checkSql);
                $checkStmt->bind_param('i', $user_id);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if($checkResult->num_rows > 0){
                    $response['success'] = false;
                    $response['message'] = 'User already has an active restriction';
                } else {
                    $sql = "INSERT INTO user_restriction (user_id, restriction_type, restriction_reason, restriction_end_date, admin_id) 
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $connection->prepare($sql);
                    
                    if($stmt){
                        $stmt->bind_param('isssi', $user_id, $restriction_type, $reason, $restriction_end_date, $admin_id);
                        if($stmt->execute()){
                            $updateUserSql = "UPDATE user SET status = 'restricted' WHERE user_id = ?";
                            $updateStmt = $connection->prepare($updateUserSql);
                            $updateStmt->bind_param('i', $user_id);
                            $updateStmt->execute();
                            $updateStmt->close();

                            $response['success'] = true;
                            $response['message'] = "User restricted for $restriction_days days until $restriction_end_date";
                        } else {
                            $response['success'] = false;
                            $response['message'] = 'Failed to restrict user: ' . $connection->error;
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
        elseif($action === 'remove_restriction'){
            $sql = "UPDATE user_restriction SET restriction_end_date = NOW() WHERE user_id = ? AND (restriction_end_date > NOW() OR restriction_type = 'permanent' OR restriction_end_date IS NULL)";
            $stmt = $connection->prepare($sql);
            
            if($stmt){
                $stmt->bind_param('i', $user_id);
                if($stmt->execute()){
                    $updateUserSql = "UPDATE user SET status = 'active' WHERE user_id = ?";
                    $updateStmt = $connection->prepare($updateUserSql);
                    $updateStmt->bind_param('i', $user_id);
                    $updateStmt->execute();
                    $updateStmt->close();

                    $response['success'] = true;
                    $response['message'] = 'Restriction removed';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Failed to remove restriction: ' . $connection->error;
                }
                $stmt->close();
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: ' . $connection->error;
            }
        }
        else {
            $response['success'] = false;
            $response['message'] = 'Invalid action';
        }

        $db->close();
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
?>
