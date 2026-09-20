<?php
	session_start();
	if (!isset($_SESSION["Username"]) || !isset($_SESSION["Name"])) {
		header("Location: index.php");
		exit;
	}
	require_once("sql_connection.php");
	$Session_Username = $_SESSION["Username"];
	$Session_Name = $_SESSION["Name"];
	$Spending_Success = $_SESSION["Spending_Success"] ?? null;
	$Spending_Failed = $_SESSION["Spending_Failed"] ?? null;
	unset($_SESSION["Spending_Success"], $_SESSION["Spending_Failed"]);

	$Categories = ["Food & Drinks", "Groceries", "Shopping", "Clothing", "Footwear", "Accessories", "Electronics", "Computer & Accessories", "Audio", "Mobile Phone", "Camera", "Home Appliances", "Furniture", "Home Supplies", "Kitchen Supplies", "Bathroom Supplies", "Personal Care", "Stationery", "Books & Learning Materials", "Toys & Games", "Gaming", "Sports Equipment", "Hobby Supplies", "Automotive Parts", "Motorcycle Parts", "Tools", "Other Goods", "Transportation", "Fuel", "Parking", "Toll", "Vehicle Maintenance", "Vehicle Tax", "Bills & Utilities", "Phone & Internet", "Subscription", "Software & Apps", "Services", "Insurance", "Education", "Course & Training", "Entertainment", "Travel", "Recreation", "Events", "Health", "Household", "Gifts & Donations", "Tax & Administration", "Bank & Payment Fees", "Baby & Kids", "Pets", "Other"];

	$SQL_Query = mysqli_prepare($sql_connection, "SELECT * FROM tb_b_spending WHERE Username = ? ORDER BY Date DESC, ID DESC");
	mysqli_stmt_bind_param($SQL_Query, "s", $Session_Username);
	mysqli_stmt_execute($SQL_Query);
	$SQL_Result = mysqli_stmt_get_result($SQL_Query);
	$Total_Spending = 0;
	$Total_Records = mysqli_num_rows($SQL_Result);
	while ($SQL_Fetch = mysqli_fetch_array($SQL_Result)) $Total_Spending += $SQL_Fetch["Amount"];
	mysqli_stmt_execute($SQL_Query);
	$SQL_Result = mysqli_stmt_get_result($SQL_Query);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Spending Diary</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/spending.css" rel="stylesheet">
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
					<li class="nav-item"><a class="nav-link active" aria-current="page" href="spending.php">Spending</a></li>
					<li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
					<li class="nav-item account-actions-item">
						<div class="account-actions">
							<button class="btn btn-outline-light theme-toggle" id="ThemeToggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode"><span id="ThemeIcon">☾</span></button>
							<form action="index_logout.php" method="post" class="logout-form">
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
				<div class="col-12">
					<div class="card spending-card">
						<div class="card-body p-4 p-md-5">
							<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
								<div>
									<h2 class="page-title mb-2">Spending</h2>
									<p class="text-secondary mb-0">Manage your spending records.</p>
								</div>
								<div><button class="btn btn-indigo" type="button" data-bs-toggle="modal" data-bs-target="#AddSpending">+ Add Spending</button></div>
							</div>

							<?php if ($Spending_Success === "Add"): ?>
								<div class="alert alert-success mb-4" role="alert">Spending added successfully!</div>
							<?php elseif ($Spending_Success === "Edit"): ?>
								<div class="alert alert-success mb-4" role="alert">Spending updated successfully!</div>
							<?php elseif ($Spending_Success === "Delete"): ?>
								<div class="alert alert-success mb-4" role="alert">Spending deleted successfully!</div>
							<?php endif; ?>
							<?php if ($Spending_Failed): ?>
								<div class="alert alert-danger mb-4" role="alert">Failed to process spending data.</div>
							<?php endif; ?>

							<div class="row g-4 mb-4">
								<div class="col-12 col-md-6">
									<div class="card summary-card bg-light h-100">
										<div class="card-body p-4">
											<div class="summary-title mb-2">Total Spending</div>
											<div class="stat-value">Rp <?= number_format($Total_Spending, 0, ",", ".") ?></div>
										</div>
									</div>
								</div>
								<div class="col-12 col-md-6">
									<div class="card summary-card bg-light h-100">
										<div class="card-body p-4">
											<div class="summary-title mb-2">Total Records</div>
											<div class="stat-value"><?= $Total_Records ?></div>
										</div>
									</div>
								</div>
							</div>

							<div class="table-responsive">
								<table class="table table-hover align-middle mb-0">
									<thead>
										<tr>
											<th>Date</th>
											<th>Description</th>
											<th>Category</th>
											<th class="text-end">Amount</th>
											<th>Notes</th>
											<th class="text-center action-column">Action</th>
										</tr>
									</thead>
									<tbody>
										<?php if ($Total_Records > 0): ?>
											<?php while ($SQL_Fetch = mysqli_fetch_array($SQL_Result)): ?>
												<tr>
													<td><?= htmlspecialchars($SQL_Fetch["Date"]) ?></td>
													<td><?= htmlspecialchars($SQL_Fetch["Description"]) ?></td>
													<td><?= htmlspecialchars($SQL_Fetch["Category"]) ?></td>
													<td class="text-end amount">Rp <?= number_format($SQL_Fetch["Amount"], 0, ",", ".") ?></td>
													<td class="notes-cell"><?= htmlspecialchars($SQL_Fetch["Notes"] ?? "") ?></td>
													<td class="text-center action-column">
														<div class="d-flex justify-content-center gap-2">
															<button class="btn btn-sm btn-outline-primary EditSpending" type="button" data-bs-toggle="modal" data-bs-target="#EditSpending" data-id="<?= htmlspecialchars($SQL_Fetch["ID"]) ?>" data-date="<?= htmlspecialchars($SQL_Fetch["Date"]) ?>" data-description="<?= htmlspecialchars($SQL_Fetch["Description"]) ?>" data-category="<?= htmlspecialchars($SQL_Fetch["Category"]) ?>" data-amount="<?= htmlspecialchars($SQL_Fetch["Amount"]) ?>" data-notes="<?= htmlspecialchars($SQL_Fetch["Notes"] ?? "") ?>">Edit</button>
															<form action="spending_action.php" method="post" class="d-inline">
																<input type="hidden" name="Action" value="Delete">
																<input type="hidden" name="ID" value="<?= htmlspecialchars($SQL_Fetch["ID"]) ?>">
																<input type="hidden" name="Redirect" value="spending.php">
																<button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Are you sure you want to delete this spending record?');">Delete</button>
															</form>
														</div>
													</td>
												</tr>
											<?php endwhile; ?>
										<?php else: ?>
											<tr><td colspan="6" class="text-center text-secondary py-5">No spending records found.</td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</main>

	<div class="modal fade" id="AddSpending" tabindex="-1" aria-labelledby="AddSpendingLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-indigo" id="AddSpendingLabel">Add Spending</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="spending_action.php" method="post">
					<div class="modal-body">
						<input type="hidden" name="Action" value="Add">
						<input type="hidden" name="Redirect" value="spending.php">
						<div class="mb-3">
							<label for="Add_Date" class="form-label">Date</label>
							<input type="date" class="form-control" id="Add_Date" name="Date" value="<?= date("Y-m-d") ?>" required>
						</div>
						<div class="mb-3">
							<label for="Add_Description" class="form-label">Description</label>
							<input type="text" class="form-control" id="Add_Description" name="Description" required>
						</div>
						<div class="mb-3">
							<label for="Add_Category" class="form-label">Category</label>
							<select class="form-select" id="Add_Category" name="Category" required>
								<option value="" selected disabled>Select category</option>
								<?php foreach ($Categories as $Category): ?>
									<option value="<?= htmlspecialchars($Category) ?>"><?= htmlspecialchars($Category) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="mb-3">
							<label for="Add_Amount" class="form-label">Amount</label>
							<input type="number" class="form-control" id="Add_Amount" name="Amount" min="0" step="1" required>
						</div>
						<div class="mb-3">
							<label for="Add_Notes" class="form-label">Notes</label>
							<textarea class="form-control" id="Add_Notes" name="Notes" rows="3"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
						<button class="btn btn-indigo" type="submit">Save Spending</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="EditSpending" tabindex="-1" aria-labelledby="EditSpendingLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-indigo" id="EditSpendingLabel">Edit Spending</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="spending_action.php" method="post">
					<div class="modal-body">
						<input type="hidden" name="Action" value="Edit">
						<input type="hidden" name="Redirect" value="spending.php">
						<input type="hidden" name="ID" id="Edit_ID">
						<div class="mb-3">
							<label for="Edit_Date" class="form-label">Date</label>
							<input type="date" class="form-control" id="Edit_Date" name="Date" required>
						</div>
						<div class="mb-3">
							<label for="Edit_Description" class="form-label">Description</label>
							<input type="text" class="form-control" id="Edit_Description" name="Description" required>
						</div>
						<div class="mb-3">
							<label for="Edit_Category" class="form-label">Category</label>
							<select class="form-select" id="Edit_Category" name="Category" required>
								<option value="" disabled>Select category</option>
								<?php foreach ($Categories as $Category): ?>
									<option value="<?= htmlspecialchars($Category) ?>"><?= htmlspecialchars($Category) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="mb-3">
							<label for="Edit_Amount" class="form-label">Amount</label>
							<input type="number" class="form-control" id="Edit_Amount" name="Amount" min="0" step="1" required>
						</div>
						<div class="mb-3">
							<label for="Edit_Notes" class="form-label">Notes</label>
							<textarea class="form-control" id="Edit_Notes" name="Notes" rows="3"></textarea>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
						<button class="btn btn-indigo" type="submit">Save Changes</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/light-dark-theme.js"></script>
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll(".EditSpending").forEach(function (Button) {
				Button.addEventListener("click", function () {
					document.getElementById("Edit_ID").value = this.getAttribute("data-id");
					document.getElementById("Edit_Date").value = this.getAttribute("data-date");
					document.getElementById("Edit_Description").value = this.getAttribute("data-description");
					document.getElementById("Edit_Category").value = this.getAttribute("data-category");
					document.getElementById("Edit_Amount").value = this.getAttribute("data-amount");
					document.getElementById("Edit_Notes").value = this.getAttribute("data-notes") || "";
				});
			});
		});
	</script>
</body>
</html>