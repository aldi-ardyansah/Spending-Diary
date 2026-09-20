<?php
    session_start();
    $Register_Failed = $_SESSION["Register_Failed"] ?? null;
    $Register_Data = $_SESSION["Register_Data"] ?? [];
    unset($_SESSION["Register_Failed"], $_SESSION["Register_Data"]);
?>
<!doctype html>
<html lang="en">
<head>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/register.css" rel="stylesheet">
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Spending Diary - Register</title>
	<link rel="icon" type="image/x-icon" href="assets/logo.svg">
</head>
<body>
	<button class="btn btn-light auth-theme-toggle theme-toggle" id="ThemeToggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode">
		<span id="ThemeIcon">☾</span>
	</button>
	<div class="container auth-container d-flex align-items-center justify-content-center">
		<div class="card auth-card p-4">
			<div class="text-center">
				<img src="assets/logo.svg" class="mb-4 auth-logo" alt="Spending Diary">
				<h2 class="text-indigo mb-2">Create Account</h2>
				<p class="text-secondary mb-4">Create your Spending Diary account.</p>
			</div>
			<form action="register_action.php" method="post" onsubmit="return validatePassword()">
				<div class="mb-3">
					<label for="Username" class="form-label">Username</label>
					<input type="text" class="form-control <?= $Register_Failed === "Username" ? 'is-invalid' : '' ?>" id="Username" name="Username" value="<?= htmlspecialchars($Register_Data["Username"] ?? "") ?>" required autofocus>
					<?php if ($Register_Failed === "Username"): ?>
						<div class="invalid-feedback">Username already exists!</div>
					<?php endif; ?>
				</div>
				<div class="mb-3">
					<label for="Password" class="form-label">Password</label>
					<input type="password" class="form-control <?= $Register_Failed === "Password" ? 'is-invalid' : '' ?>" id="Password" name="Password" required>
					<?php if ($Register_Failed === "Password"): ?>
						<div class="invalid-feedback">Passwords do not match!</div>
					<?php endif; ?>
				</div>
				<div class="mb-3">
					<label for="Password_Confirmation" class="form-label">Confirm Password</label>
					<input type="password" class="form-control <?= $Register_Failed === "Password" ? 'is-invalid' : '' ?>" id="Password_Confirmation" name="Password_Confirmation" required>
				</div>
				<div id="Password_Error" class="text-danger mb-4 d-none">Passwords do not match!</div>
				<div class="mb-4">
					<label for="Name" class="form-label">Name</label>
					<input type="text" class="form-control" id="Name" name="Name" value="<?= htmlspecialchars($Register_Data["Name"] ?? "") ?>" required>
				</div>
				<div class="d-grid">
					<button class="btn btn-indigo" type="submit">Register</button>
				</div>
			</form>
			<div class="text-center mt-4">
				<span class="text-secondary">Already have an account?</span>
				<a href="index.php" class="auth-link text-decoration-none fw-semibold">Login</a>
			</div>
		</div>
	</div>
	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/light-dark-theme.js"></script>
	<script>
		function validatePassword() {
			const Password = document.getElementById("Password").value;
			const Password_Confirmation = document.getElementById("Password_Confirmation").value;
			const Password_Error = document.getElementById("Password_Error");
			if (Password !== Password_Confirmation) {
				Password_Error.classList.remove("d-none");
				return false;
			}
			Password_Error.classList.add("d-none");
			return true;
		}
	</script>
</body>
</html>