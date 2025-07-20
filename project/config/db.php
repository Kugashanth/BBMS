<?php
    $host = "localhost";
    $user = "root";  
    $password = "";  
    $dbname = "1_blood_bank";
    $port = 3306;

    $conn = new mysqli($host, $user, $password, $dbname, $port);
    // $conn = new mysqli($host, $user, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
