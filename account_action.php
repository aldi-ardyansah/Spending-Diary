<?php

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: account.php");
        exit;
    }

    session_start();

    if (!isset($_SESSION["Username"]) || !isset($_SESSION["Name"])) {
        header("Location: index.php");
        exit;
    }

    require_once("sql_connection.php");

    $Session_Username = $_SESSION["Username"];

    $POST_Name = trim($_POST["Name"] ?? "");
    $POST_Username = trim($_POST["Username"] ?? "");
    $POST_Current_Password = $_POST["Current_Password"] ?? "";
    $POST_New_Password = $_POST["New_Password"] ?? "";
    $POST_New_Password_Confirmation = $_POST["New_Password_Confirmation"] ?? "";

    if ($POST_Name === "" || $POST_Username === "") {
        $_SESSION["Account_Failed"] = "Data";

        header("Location: account.php");
        exit;
    }

    if ($POST_Username !== $Session_Username) {

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
            $_SESSION["Account_Failed"] = "Username";

            header("Location: account.php");
            exit;
        }
    }

    if ($POST_Current_Password !== "" || $POST_New_Password !== "" || $POST_New_Password_Confirmation !== "") {

        if ($POST_Current_Password === "") {
            $_SESSION["Account_Failed"] = "Password";

            header("Location: account.php");
            exit;
        }

        if ($POST_New_Password !== $POST_New_Password_Confirmation) {
            $_SESSION["Account_Failed"] = "Password_Confirmation";

            header("Location: account.php");
            exit;
        }

        $SQL_Query = mysqli_prepare(
            $sql_connection,
            "SELECT Password FROM tb_a_accounts WHERE Username = ?"
        );

        mysqli_stmt_bind_param(
            $SQL_Query,
            "s",
            $Session_Username
        );

        mysqli_stmt_execute($SQL_Query);

        $SQL_Result = mysqli_stmt_get_result($SQL_Query);
        $SQL_Fetch = mysqli_fetch_array($SQL_Result);

        if (!password_verify($POST_Current_Password, $SQL_Fetch["Password"])) {
            $_SESSION["Account_Failed"] = "Password";

            header("Location: account.php");
            exit;
        }

        $POST_Password_Hash = password_hash($POST_New_Password, PASSWORD_DEFAULT);

        $SQL_Query = mysqli_prepare(
            $sql_connection,
            "UPDATE tb_a_accounts SET Name = ?, Username = ?, Password = ? WHERE Username = ?"
        );

        mysqli_stmt_bind_param(
            $SQL_Query,
            "ssss",
            $POST_Name,
            $POST_Username,
            $POST_Password_Hash,
            $Session_Username
        );

    } else {

        $SQL_Query = mysqli_prepare(
            $sql_connection,
            "UPDATE tb_a_accounts SET Name = ?, Username = ? WHERE Username = ?"
        );

        mysqli_stmt_bind_param(
            $SQL_Query,
            "sss",
            $POST_Name,
            $POST_Username,
            $Session_Username
        );
    }

    if (!mysqli_stmt_execute($SQL_Query)) {
        $_SESSION["Account_Failed"] = "Data";

        header("Location: account.php");
        exit;
    }

    $_SESSION["Username"] = $POST_Username;
    $_SESSION["Name"] = $POST_Name;
    $_SESSION["Account_Success"] = true;

    header("Location: account.php");
    exit;

?>