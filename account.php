<?php
	session_start();
	if (!isset($_SESSION["Username"]) || !isset($_SESSION["Name"])) {
		header("Location: index.php");
		exit;
	}
	require_once("sql_connection.php");
	$Session_Username = $_SESSION["Username"];
	$Session_Name = $_SESSION["Name"];
	$Account_Success = $_SESSION["Account_Success"] ?? null;
	$Account_Failed = $_SESSION["Account_Failed"] ?? null;
	unset($_SESSION["Account_Success"], $_SESSION["Account_Failed"]);

	$SQL_Query = mysqli_prepare($sql_connection, "SELECT * FROM tb_a_accounts WHERE Username = ?");
	mysqli_stmt_bind_param($SQL_Query, "s", $Session_Username);
	mysqli_stmt_execute($SQL_Query);
	$SQL_Result = mysqli_stmt_get_result($SQL_Query);
	$SQL_Fetch = mysqli_fetch_array($SQL_Result);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Spending Diary</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/account.css" rel="stylesheet">
	<link rel="icon" type="image/x-icon" href="assets/logo.svg">
</head>
<body>
	<nav class="navbar navbar-expand-lg">
		<div class="container">
			<a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
				<img src="assets/logo.svg" class="navbar-logo" alt="Spending Diary">
				<span>Spending Diary</span>
			</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#Navbar" aria-controls="Navbar" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="Navbar">
				<ul class="navbar-nav ms-auto">
					<li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
					<li class="nav-item"><a class="nav-link" href="spending.php">Spending</a></li>
					<li class="nav-item"><a class="nav-link active" aria-current="page" href="account.php">Account</a></li>
					<li class="nav-item account-actions-item">
						<div class="account-actions">
							<button class="btn btn-outline-light theme-toggle" id="ThemeToggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode"><span id="ThemeIcon">☾</span></button>
							<form action="index_logout.php" method="post">
								<button class="btn btn-light logout-button" type="submit">Logout</button>
							</form>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<main>
		<div class="container py-5">
			<div class="row justify-content-center">
				<div class="col-12 col-lg-8">
					<div class="card account-card">
						<div class="card-body p-4 p-md-5">
							<div class="mb-4">
								<h2 class="page-title mb-2">Account</h2>
								<p class="text-secondary mb-0">Manage your account information.</p>
							</div>

							<?php if ($Account_Success === "Data"): ?>
								<div class="alert alert-success mb-4" role="alert">Account updated successfully!</div>
							<?php endif; ?>
							<?php if ($Account_Failed === "Data"): ?>
								<div class="alert alert-danger mb-4" role="alert">Failed to update account!</div>
							<?php elseif ($Account_Failed === "Password"): ?>
								<div class="alert alert-danger mb-4" role="alert">Wrong current password!</div>
							<?php elseif ($Account_Failed === "Username"): ?>
								<div class="alert alert-danger mb-4" role="alert">Username already exists!</div>
							<?php endif; ?>

							<form action="account_action.php" method="post">
								<div class="mb-4">
									<label for="Name" class="form-label">Name</label>
									<input type="text" class="form-control" id="Name" name="Name" value="<?= htmlspecialchars($SQL_Fetch["Name"]) ?>" required>
								</div>
								<div class="mb-4">
									<label for="Username" class="form-label">Username</label>
									<input type="text" class="form-control" id="Username" name="Username" value="<?= htmlspecialchars($SQL_Fetch["Username"]) ?>" required>
								</div>
								<hr class="my-4">
								<h5 class="text-indigo mb-3">Change Password</h5>
								<div class="mb-4">
									<label for="Current_Password" class="form-label">Current Password</label>
									<input type="password" class="form-control" id="Current_Password" name="Current_Password">
								</div>
								<div class="mb-4">
									<label for="New_Password" class="form-label">New Password</label>
									<input type="password" class="form-control" id="New_Password" name="New_Password">
								</div>
								<div class="mb-4">
									<label for="New_Password_Confirmation" class="form-label">Confirm New Password</label>
									<input type="password" class="form-control" id="New_Password_Confirmation" name="New_Password_Confirmation">
								</div>
								<div class="d-flex justify-content-end gap-2">
									<a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
									<button class="btn btn-indigo" type="submit">Save Changes</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/light-dark-theme.js"></script>
</body>
</html>