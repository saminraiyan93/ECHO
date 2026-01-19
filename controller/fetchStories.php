<?php
    session_start();

    // Check if user is logged in
    if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
        $_SESSION['restrictedMsg'] = 'You must login first';
        header('Location: ../view/login/login.php');
        exit();
    }

    require_once '../config/database.php';

        $db = new Database();
        $connection = $db->getConnection();

        $sql = "SELECT story.* , user.user_name FROM story INNER JOIN user ON story.user_id = user.user_id ORDER BY story.createdAt DESC ";

        $result = $connection->query($sql);

        $stories = [];

        if($result && $result->num_rows > 0){
            while($row = $result->fetch_assoc()){

                $stories[] = [
                    'story_id' => $row['story_id'],
                    'title' => $row['title'],
                    'contents' => $row['contents'],
                    'category' => $row['category'],
                    'vote' => $row['vote'],
                    'user_name' => $row['user_name'],
                    'createdAt' => $row['createdAt']
                ];

            }
        }

        // return json response
        header('Content-Type: application/json');
        echo json_encode($stories);

        $db->close();

?>