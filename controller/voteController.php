<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){
    echo json_encode(['status' => 'error', 'message' => 'You must login first']);
    exit();
}

require_once '../model/database.php';

$db = new Database();
$connection = $db->getConnection();

$response = ['status' => 'error', 'message' => ''];

$user_id = $_SESSION['user'];

// Check if user is restricted or banned
$restrictionSql = "SELECT * FROM user_restriction 
                   WHERE user_id = ? AND (restriction_end_date > NOW() OR (restriction_type = 'permanent' AND restriction_end_date IS NULL))
                   LIMIT 1";
$restrictionStmt = $connection->prepare($restrictionSql);
$restrictionStmt->bind_param('i', $user_id);
$restrictionStmt->execute();
$restrictionResult = $restrictionStmt->get_result();

if($restrictionResult && $restrictionResult->num_rows > 0){
    $restriction = $restrictionResult->fetch_assoc();
    
    if($restriction['restriction_type'] === 'permanent'){
        $response['message'] = "❌ Your account is banned. You cannot vote on stories.";
    } else {
        $response['message'] = "⏱️ Your account is restricted. You cannot vote until " . date('M d, Y H:i', strtotime($restriction['restriction_end_date']));
    }
    
    echo json_encode($response);
    $restrictionStmt->close();
    $db->close();
    exit();
}

$restrictionStmt->close();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(!isset($data['story_id'])){
        $response['message'] = 'Story ID is required';
        echo json_encode($response);
        exit();
    }

    $story_id = intval($data['story_id']);

    // Check if user has already voted on this story
    $checkVote = "SELECT * FROM vote WHERE user_id = ? AND story_id = ?";
    $stmt = $connection->prepare($checkVote);
    $stmt->bind_param("ii", $user_id, $story_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        // User has already voted, so remove the vote (toggle off)
        $deleteVote = "DELETE FROM vote WHERE user_id = ? AND story_id = ?";
        $stmt = $connection->prepare($deleteVote);
        $stmt->bind_param("ii", $user_id, $story_id);
        
        if($stmt->execute()){
            // Update vote count in story table
            $updateStory = "UPDATE story SET vote = (SELECT COUNT(*) FROM vote WHERE story_id = ?) WHERE story_id = ?";
            $stmt = $connection->prepare($updateStory);
            $stmt->bind_param("ii", $story_id, $story_id);
            $stmt->execute();

            $response['status'] = 'success';
            $response['message'] = 'Vote removed';
            $response['voted'] = false;

            // Get updated vote count
            $getVotes = "SELECT vote FROM story WHERE story_id = ?";
            $stmt = $connection->prepare($getVotes);
            $stmt->bind_param("i", $story_id);
            $stmt->execute();
            $voteResult = $stmt->get_result();
            $voteRow = $voteResult->fetch_assoc();
            $response['voteCount'] = intval($voteRow['vote']);
        } else {
            $response['message'] = 'Error removing vote';
        }
    } else {
        // User hasn't voted, so add a vote (toggle on)
        $addVote = "INSERT INTO vote (type, user_id, story_id) VALUES (1, ?, ?)";
        $stmt = $connection->prepare($addVote);
        $stmt->bind_param("ii", $user_id, $story_id);
        
        if($stmt->execute()){
            // Update vote count in story table
            $updateStory = "UPDATE story SET vote = (SELECT COUNT(*) FROM vote WHERE story_id = ?) WHERE story_id = ?";
            $stmt = $connection->prepare($updateStory);
            $stmt->bind_param("ii", $story_id, $story_id);
            $stmt->execute();

            $response['status'] = 'success';
            $response['message'] = 'Vote added';
            $response['voted'] = true;

            // Get updated vote count
            $getVotes = "SELECT vote FROM story WHERE story_id = ?";
            $stmt = $connection->prepare($getVotes);
            $stmt->bind_param("i", $story_id);
            $stmt->execute();
            $voteResult = $stmt->get_result();
            $voteRow = $voteResult->fetch_assoc();
            $response['voteCount'] = intval($voteRow['vote']);
        } else {
            $response['message'] = 'Error adding vote';
        }
    }

    $db->close();
} else {
    $response['message'] = 'Invalid request method';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
