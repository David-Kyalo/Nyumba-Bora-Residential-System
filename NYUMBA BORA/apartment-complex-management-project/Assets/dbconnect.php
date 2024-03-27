<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname =  "apartments";
    $conn = mysqli_connect("localhost", "root", "", "apartments");
     
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    echo "Connected successfully";