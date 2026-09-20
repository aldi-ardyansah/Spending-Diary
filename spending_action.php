<?php

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: spending.php");
        exit;
    }

    session_start();

    if (!isset($_SESSION["Username"]) || !isset($_SESSION["Name"])) {
        header("Location: index.php");
        exit;
    }

    require_once("sql_connection.php");

    $Session_Username = $_SESSION["Username"];

    $POST_Action = $_POST["Action"] ?? "";
    $POST_ID = $_POST["ID"] ?? "";
    $POST_Date = $_POST["Date"] ?? "";
    $POST_Description = trim($_POST["Description"] ?? "");
    $POST_Category = trim($_POST["Category"] ?? "");
    $POST_Amount = $_POST["Amount"] ?? "";
    $POST_Notes = trim($_POST["Notes"] ?? "");
    $POST_Redirect = $_POST["Redirect"] ?? "spending.php";

    if (!in_array($POST_Redirect, ["spending.php", "dashboard.php"], true)) {
        $POST_Redirect = "spending.php";
    }

    if ($POST_Action === "Add") {

        if (
            $POST_Date === "" ||
            $POST_Description === "" ||
            $POST_Category === "" ||
            $POST_Amount === ""
        ) {
            $_SESSION["Spending_Failed"] = "Data";

            header("Location: " . $POST_Redirect);
            exit;
        }

        $SQL_Query = mysqli_prepare(
            $sql_connection,
            "INSERT INTO tb_b_spending (Username, Date, Description, Category, Amount, Notes) VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $SQL_Query,
            "ssssds",
            $Session_Username,
            $POST_Date,
            $POST_Description,
            $POST_Category,
            $POST_Amount,
            $POST_Notes
        );

        if (!mysqli_stmt_execute($SQL_Query)) {
            $_SESSION["Spending_Failed"] = "Data";

            header("Location: " . $POST_Redirect);
            exit;
        }

        $_SESSION["Spending_Success"] = "Add";

        header("Location: " . $POST_Redirect);
        exit;
    }

    if ($POST_Action === "Edit") {

        if (
            $POST_ID === "" ||
            $POST_Date === "" ||
            $POST_Description === "" ||
            $POST_Category === "" ||
            $POST_Amount === ""
        ) {
            $_SESSION["Spending_Failed"] = "Data";

            header("Location: spending.php");
            exit;
        }

        $SQL_Query = mysqli_prepare(
            $sql_connection,
            "UPDATE tb_b_spending
             SET Date = ?, Description = ?, Category = ?, Amount = ?, Notes = ?
             WHERE ID = ? AND Username = ?"
        );

        mysqli_stmt_bind_param(
            $SQL_Query,
            "sssdsss",
            $POST_Date,
            $POST_Description,
            $POST_Category,
            $POST_Amount,
            $POST_Notes,
            $POST_ID,
            $Session_Username
        );

        if (!mysqli_stmt_execute($SQL_Query)) {
            $_SESSION["Spending_Failed"] = "Data";

            header("Location: spending.php");
            exit;
        }

        $_SESSION["Spending_Success"] = "Edit";

        header("Location: spending.php");
        exit;
    }

    if ($POST_Action === "Delete") {

        if ($POST_ID === "") {
            $_SESSION["Spending_Failed"] = "Data";

            header("Location: spending.php");
            exit;
        }

        $SQL_Query = mysqli_prepare(
            $sql_connection,
            "DELETE FROM tb_b_spending WHERE ID = ? AND Username = ?"
        );

        mysqli_stmt_bind_param(
            $SQL_Query,
            "ss",
            $POST_ID,
            $Session_Username
        );

        if (!mysqli_stmt_execute($SQL_Query)) {
            $_SESSION["Spending_Failed"] = "Data";

            header("Location: spending.php");
            exit;
        }

        $_SESSION["Spending_Success"] = "Delete";

        header("Location: spending.php");
        exit;
    }

    $_SESSION["Spending_Failed"] = "Data";

    header("Location: spending.php");
    exit;

?>