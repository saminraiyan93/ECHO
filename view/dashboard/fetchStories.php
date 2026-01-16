<?php
    require_once '../config/database.php';

        $db = new Database();
        $connection = $db->getConnection();

        $sql = 'SELECT title, contents, category FROM story where'
?>