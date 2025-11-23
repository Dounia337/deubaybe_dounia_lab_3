<?php
// Database Connection Information
$server = "localhost";
$user = "root";
$password = "";
$database = "webtech_2025A_deubaybe.dounia";
$port = 3306;

// Create a connection
$connection = new mysqli($server, $user, $password, $database, $port);

// Check if connection established
if($connection->connect_error){
    die("Connection Failed: " . $connection->connect_error);
}
?>