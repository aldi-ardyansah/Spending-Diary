<?php

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: index.php");
        exit;
    }

    session_start();

    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $SESSION_Cookie = session_get_cookie_params();

        setcookie(
            session_name(),
            "",
            time() - 42000,
            $SESSION_Cookie["path"],
            $SESSION_Cookie["domain"],
            $SESSION_Cookie["secure"],
            $SESSION_Cookie["httponly"]
        );
    }

    session_unset();
    session_destroy();

    header("Location: index.php");
    exit;

?>