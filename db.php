<?php

    $host = "localhost";
    $db   = "root";
    $pass = "";
    $dbname = "mes_mini";

    $conn = new mysqli($host, $db, $pass, $dbname);

    if($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>