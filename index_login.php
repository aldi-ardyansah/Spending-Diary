<?php

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.php");
        exit;
    }

    session_start();

    require_once("sql_connection.php");

    $POST_Username = trim($_POST["Username"] ?? "");
    $POST_Password = $_POST["Password"] ?? "";

    if ($POST_Username === "" || $POST_Password === "") {
        $_SESSION["Login_Failed"] = "Username";
        $_SESSION["Login_Username"] = $POST_Username;

        header("Location: index.php");
        exit;
    }

    $SQL_Query = mysqli_prepare(
        $sql_connection,
        "SELECT * FROM tb_a_accounts WHERE Username = ?"
    );

    mysqli_stmt_bind_param(
        $SQL_Query,
        "s",
        $POST_Username
    );

    mysqli_stmt_execute($SQL_Query);

    $SQL_Result = mysqli_stmt_get_result($SQL_Query);

    if (mysqli_num_rows($SQL_Result) > 0) {

        $SQL_Fetch = mysqli_fetch_array($SQL_Result);

        if (password_verify($POST_Password, $SQL_Fetch["Password"])) {

            session_regenerate_id(true);

            $_SESSION["Username"] = $SQL_Fetch["Username"];
            $_SESSION["Name"] = $SQL_Fetch["Name"];

            header("Location: dashboard.php");
            exit;

        } else {

            $_SESSION["Login_Failed"] = "Password";
            $_SESSION["Login_Username"] = $POST_Username;

            header("Location: index.php");
            exit;
        }

    } else {

        $_SESSION["Login_Failed"] = "Username";
        $_SESSION["Login_Username"] = $POST_Username;

        header("Location: index.php");
        exit;
    }

?>