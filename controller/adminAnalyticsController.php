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

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $response = [];

    if($action === 'stats'){
        // Get total users and total stories
        $totalUsersSql = "SELECT COUNT(*) as total FROM user";
        $totalStoriesSql = "SELECT COUNT(*) as total FROM story";
        $restrictedUsersSql = "SELECT COUNT(DISTINCT user_id) as total FROM user WHERE status = 'restricted'";
        $bannedUsersSql = "SELECT COUNT(DISTINCT user_id) as total FROM user WHERE status = 'banned'";
        $totalCategoriesSql = "SELECT COUNT(*) as total FROM category";

        $totalUsersResult = $connection->query($totalUsersSql);
        $totalStoriesResult = $connection->query($totalStoriesSql);
        $restrictedUsersResult = $connection->query($restrictedUsersSql);
        $bannedUsersResult = $connection->query($bannedUsersSql);
        $totalCategoriesResult = $connection->query($totalCategoriesSql);

        if($totalUsersResult && $totalStoriesResult && $restrictedUsersResult && $bannedUsersResult && $totalCategoriesResult){
            $totalUsers = $totalUsersResult->fetch_assoc()['total'];
            $totalStories = $totalStoriesResult->fetch_assoc()['total'];
            $restrictedUsers = $restrictedUsersResult->fetch_assoc()['total'];
            $bannedUsers = $bannedUsersResult->fetch_assoc()['total'];
            $totalCategories = $totalCategoriesResult->fetch_assoc()['total'];

            $response['success'] = true;
            $response['stats'] = [
                'total_users' => $totalUsers,
                'total_stories' => $totalStories,
                'restricted_users' => $restrictedUsers,
                'banned_users' => $bannedUsers,
                'total_categories' => $totalCategories
            ];
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to fetch statistics';
        }
    }
    elseif($action === 'users'){
        // Get all users with their restriction status
        $sql = "SELECT u.user_id, u.user_name, u.user_email, u.status, 
                       ur.restriction_type, ur.restriction_end_date
                FROM user u
                LEFT JOIN user_restriction ur ON u.user_id = ur.user_id 
                  AND ur.restriction_end_date > NOW()
                ORDER BY u.user_id DESC";
        
        $result = $connection->query($sql);

        if($result){
            $users = [];
            while($row = $result->fetch_assoc()){
                $users[] = $row;
            }
            $response['success'] = true;
            $response['users'] = $users;
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to fetch users: ' . $connection->error;
        }
    }
    elseif($action === 'stories'){
        // Get all stories with user information
        $sql = "SELECT s.story_id, s.title, s.contents, s.category, s.vote, s.createdAt, 
                       u.user_id, u.user_name 
                FROM story s 
                INNER JOIN user u ON s.user_id = u.user_id 
                ORDER BY s.createdAt DESC";
        
        $result = $connection->query($sql);

        if($result){
            $stories = [];
            while($row = $result->fetch_assoc()){
                $stories[] = $row;
            }
            $response['success'] = true;
            $response['stories'] = $stories;
        } else {
            $response['success'] = false;
            $response['message'] = 'Failed to fetch stories: ' . $connection->error;
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
