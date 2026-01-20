<?php
    session_start();

    // Check if admin is logged in
    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    require_once '../model/database.php';

    $db = new Database();
    $connection = $db->getConnection();

    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $response = [];

    if($action === 'add'){
        $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

        if(empty($category_name)){
            $response['success'] = false;
            $response['message'] = 'Category name cannot be empty';
        } else {
            // Check if category already exists
            $checkSql = "SELECT COUNT(*) as count FROM category WHERE category_name = ?";
            $checkStmt = $connection->prepare($checkSql);
            $checkStmt->bind_param('s', $category_name);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $row = $checkResult->fetch_assoc();

            if($row['count'] > 0){
                $response['success'] = false;
                $response['message'] = 'Category already exists';
            } else {
                // Insert new category into database
                $insertSql = "INSERT INTO category (category_name) VALUES (?)";
                $insertStmt = $connection->prepare($insertSql);
                
                if($insertStmt){
                    $insertStmt->bind_param('s', $category_name);
                    if($insertStmt->execute()){
                        $response['success'] = true;
                        $response['message'] = 'Category added successfully';
                        $response['category_id'] = $connection->insert_id;
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Failed to add category: ' . $connection->error;
                    }
                    $insertStmt->close();
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Database error: ' . $connection->error;
                }
            }
            
            $checkStmt->close();
        }
    }
    elseif($action === 'delete'){
        $category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

        if(empty($category_name)){
            $response['success'] = false;
            $response['message'] = 'Category name cannot be empty';
        } else {
            // Check if any stories use this category
            $checkSql = "SELECT COUNT(*) as count FROM story WHERE category = ?";
            $checkStmt = $connection->prepare($checkSql);
            $checkStmt->bind_param('s', $category_name);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $row = $checkResult->fetch_assoc();

            if($row['count'] > 0){
                $response['success'] = false;
                $response['message'] = 'Cannot delete category. ' . $row['count'] . ' story(ies) are using this category. Delete those stories first.';
            } else {
                // Delete the category from database
                $deleteSql = "DELETE FROM category WHERE category_name = ?";
                $deleteStmt = $connection->prepare($deleteSql);
                
                if($deleteStmt){
                    $deleteStmt->bind_param('s', $category_name);
                    if($deleteStmt->execute()){
                        $response['success'] = true;
                        $response['message'] = 'Category deleted successfully';
                    } else {
                        $response['success'] = false;
                        $response['message'] = 'Failed to delete category: ' . $connection->error;
                    }
                    $deleteStmt->close();
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Database error: ' . $connection->error;
                }
            }
            $checkStmt->close();
        }
    }
    elseif($action === 'fetch'){
        // Get all categories from category table
        $sql = "SELECT category_id, category_name FROM category ORDER BY category_name ASC";
        $result = $connection->query($sql);

        if($result){
            $categories = [];
            while($row = $result->fetch_assoc()){
                $categories[] = $row['category_name'];
            }
            $response['success'] = true;
            $response['categories'] = $categories;
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to fetch categories: ' . $connection->error;
        }
    }
    else {
        $response['success'] = false;
        $response['message'] = 'Invalid action';
    }

    $db->close();
    header('Content-Type: application/json');
    echo json_encode($response);
?>
