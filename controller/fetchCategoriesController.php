<?php
    require_once '../model/database.php';

    $db = new Database();
    $connection = $db->getConnection();
    $response = [];

    $sql = "SELECT category_name FROM category ORDER BY category_name ASC";
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
        $response['message'] = 'Failed to fetch categories';
    }

    $db->close();
    header('Content-Type: application/json');
    echo json_encode($response);
?>
