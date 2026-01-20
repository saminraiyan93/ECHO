<?php
    session_start();

    // Check if admin is logged in
    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    require_once '../model/database.php';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $story_id = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;

        $db = new Database();
        $connection = $db->getConnection();
        $response = [];

        if($story_id <= 0){
            $response['success'] = false;
            $response['message'] = 'Invalid story ID';
        } else {
            // Delete story and its associated comments (cascaded by foreign key)
            $sql = "DELETE FROM story WHERE story_id = ?";
            $stmt = $connection->prepare($sql);
            
            if($stmt){
                $stmt->bind_param('i', $story_id);
                if($stmt->execute()){
                    $response['success'] = true;
                    $response['message'] = 'Story deleted successfully';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Failed to delete story: ' . $connection->error;
                }
                $stmt->close();
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: ' . $connection->error;
            }
        }

        $db->close();
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
?>
