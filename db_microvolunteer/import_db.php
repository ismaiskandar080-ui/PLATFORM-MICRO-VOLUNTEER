<?php
require_once 'c:/xampp/htdocs/db_microvolunteer/config/db.php';
$sql = file_get_contents('c:/xampp/htdocs/db_microvolunteer/microvolunteer.sql');
$sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
$sql = preg_replace('/USE [^;]+;/i', '', $sql);
if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "SQL import successful!\n";
} else {
    echo "Error during SQL import: " . $conn->error . "\n";
}
?>
