<?php
    session_start();

    $registerSuccess = $_SESSION['Register_Success'] ?? false;
    $loginFailed = $_SESSION['Login_Failed'] ?? null;
    $loginUsername = $_SESSION['Login_Username'] ?? '';

    unset($_SESSION['Register_Success'], $_SESSION['Login_Failed'], $_SESSION['Login_Username']);

    $alerts = [
        'Username' => 'Username does not exist!',
        'Password' => 'Wrong password!'
    ];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spending Diary</title>
    <link rel="icon" href="assets/logo.svg">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<button class="btn btn-light auth-theme-toggle theme-toggle" id="ThemeToggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode"><span id="ThemeIcon">☾</span></button>
<main class="container auth-container d-flex align-items-center justify-content-center">
    <div class="card auth-card p-4">
        <header class="text-center">
            <img src="assets/logo.svg" class="mb-4 auth-logo" alt="Spending Diary">
            <h2 class="text-indigo mb-2">Spending Diary</h2>
            <p class="text-secondary mb-4">Manage your spending with ease.</p>
        </header>
        <?php if ($registerSuccess): ?>
            <div class="alert alert-success mb-4" role="alert">Account registered successfully!</div>
        <?php elseif ($loginFailed !== null && isset($alerts[$loginFailed])): ?>
            <div class="alert alert-danger mb-4" role="alert"><?= htmlspecialchars($alerts[$loginFailed]) ?></div>
        <?php endif; ?>
        <form action="index_login.php" method="post">
            <div class="mb-3">
                <label for="Username" class="form-label">Username</label>
                <input type="text" class="form-control" id="Username" name="Username" value="<?= htmlspecialchars($loginUsername) ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label for="Password" class="form-label">Password</label>
                <input type="password" class="form-control" id="Password" name="Password" required>
            </div>
            <div class="d-grid">
                <button class="btn btn-indigo" type="submit">Login</button>
            </div>
        </form>
        <div class="text-center mt-4">
            <span class="text-secondary">Don't have an account?</span>
            <a href="register.php" class="auth-link text-decoration-none fw-semibold">Register</a>
        </div>
    </div>
</main>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/light-dark-theme.js"></script>
</body>
</html>