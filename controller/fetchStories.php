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

        $user_id = $_SESSION['user'];
        $sql = "SELECT story.* , user.user_name FROM story INNER JOIN user ON story.user_id = user.user_id ORDER BY story.createdAt DESC ";

        $result = $connection->query($sql);

        $stories = [];

        if($result && $result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $story_id = $row['story_id'];

                // Check if current user has voted on this story
                $voteCheck = "SELECT * FROM vote WHERE user_id = ? AND story_id = ?";
                $stmt = $connection->prepare($voteCheck);
                $stmt->bind_param("ii", $user_id, $story_id);
                $stmt->execute();
                $voteResult = $stmt->get_result();
                $hasVoted = $voteResult->num_rows > 0;

                $stories[] = [
                    'story_id' => $row['story_id'],
                    'title' => $row['title'],
                    'contents' => $row['contents'],
                    'category' => $row['category'],
                    'vote' => $row['vote'],
                    'user_name' => $row['user_name'],
                    'createdAt' => $row['createdAt'],
                    'hasVoted' => $hasVoted
                ];

            }
        }

        // return json response
        header('Content-Type: application/json');
        echo json_encode($stories);

        $db->close();

?>