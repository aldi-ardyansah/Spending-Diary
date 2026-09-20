<?php

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: register.php");
        exit;
    }

    session_start();

    require_once("sql_connection.php");

    $POST_Username = trim($_POST["Username"] ?? "");
    $POST_Password = $_POST["Password"] ?? "";
    $POST_Password_Confirmation = $_POST["Password_Confirmation"] ?? "";
    $POST_Name = trim($_POST["Name"] ?? "");

    $_SESSION["Register_Data"] = [
        "Username" => $POST_Username,
        "Name" => $POST_Name
    ];

    if ($POST_Username === "" || $POST_Password === "" || $POST_Password_Confirmation === "" || $POST_Name === "") {
        $_SESSION["Register_Failed"] = "Data";

        header("Location: register.php");
        exit;
    }

    if ($POST_Password !== $POST_Password_Confirmation) {
        $_SESSION["Register_Failed"] = "Password";

        header("Location: register.php");
        exit;
    }

    $SQL_Query = mysqli_prepare(
        $sql_connection,
        "SELECT Username FROM tb_a_accounts WHERE Username = ?"
    );

    mysqli_stmt_bind_param(
        $SQL_Query,
        "s",
        $POST_Username
    );

    mysqli_stmt_execute($SQL_Query);

    $SQL_Result = mysqli_stmt_get_result($SQL_Query);

    if (mysqli_num_rows($SQL_Result) > 0) {
        $_SESSION["Register_Failed"] = "Username";

        header("Location: register.php");
        exit;
    }

    $POST_Password_Hash = password_hash($POST_Password, PASSWORD_DEFAULT);

    $SQL_Query = mysqli_prepare(
        $sql_connection,
        "INSERT INTO tb_a_accounts (Name, Username, Password) VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $SQL_Query,
        "sss",
        $POST_Name,
        $POST_Username,
        $POST_Password_Hash
    );

    if (!mysqli_stmt_execute($SQL_Query)) {
        $_SESSION["Register_Failed"] = "Data";

        header("Location: register.php");
        exit;
    }

    unset($_SESSION["Register_Data"]);

    $_SESSION["Register_Success"] = true;

    header("Location: index.php");
    exit;

?>