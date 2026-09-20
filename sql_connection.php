<?php

    if (basename($_SERVER["SCRIPT_FILENAME"]) === basename(__FILE__)) {
        header("Location: index.php");
        exit;
    }

    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "spending-diary";

    $sql_connection = mysqli_connect(
        $host,
        $username,
        $password,
        $database
    );

    if (!$sql_connection) {
        header("Location: index.php");
        exit;
    }

    mysqli_set_charset($sql_connection, "utf8mb4");

?>