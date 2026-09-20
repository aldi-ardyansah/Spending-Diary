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

	$Current_Month = date("m");
	$Current_Year = date("Y");
	$Selected_Month = $_GET["Month"] ?? $Current_Month;
	$Selected_Year = $_GET["Year"] ?? $Current_Year;

	if (!is_numeric($Selected_Month) || $Selected_Month < 1 || $Selected_Month > 12) $Selected_Month = $Current_Month;
	if (!is_numeric($Selected_Year) || $Selected_Year < 2000 || $Selected_Year > 2100) $Selected_Year = $Current_Year;

	$Selected_Month = str_pad($Selected_Month, 2, "0", STR_PAD_LEFT);
	$Selected_Year = (int) $Selected_Year;
	$Selected_Period = "$Selected_Year-$Selected_Month";

	$Previous_Date = new DateTime("$Selected_Period-01");
	$Previous_Date->modify("-1 month");
	$Previous_Month = $Previous_Date->format("m");
	$Previous_Year = $Previous_Date->format("Y");
	$Previous_Period = "$Previous_Year-$Previous_Month";

	$Month_Names = [
		"01" => "January", "02" => "February", "03" => "March", "04" => "April",
		"05" => "May", "06" => "June", "07" => "July", "08" => "August",
		"09" => "September", "10" => "October", "11" => "November", "12" => "December"
	];

	$Selected_Month_Name = $Month_Names[$Selected_Month];
	$Previous_Month_Name = $Month_Names[$Previous_Month];

	function GetSpendingData($Connection, $Username, $Period, $Query, $First_Row = false) {
		$SQL_Query = mysqli_prepare($Connection, $Query);
		mysqli_stmt_bind_param($SQL_Query, "ss", $Username, $Period);
		mysqli_stmt_execute($SQL_Query);
		$SQL_Result = mysqli_stmt_get_result($SQL_Query);
		$Data = [];
		while ($SQL_Fetch = mysqli_fetch_array($SQL_Result)) $Data[] = $SQL_Fetch;
		return $First_Row ? ($Data[0] ?? null) : $Data;
	}

	$SQL_Fetch = GetSpendingData($sql_connection, $Session_Username, $Selected_Period, "SELECT COUNT(*) AS Total_Records, COALESCE(SUM(CAST(Amount AS DECIMAL(20,2))), 0) AS Total_Spending, COALESCE(AVG(CAST(Amount AS DECIMAL(20,2))), 0) AS Average_Spending FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ?", true);
	$Total_Records = $SQL_Fetch["Total_Records"] ?? 0;
	$Total_Spending = $SQL_Fetch["Total_Spending"] ?? 0;
	$Average_Spending = $SQL_Fetch["Average_Spending"] ?? 0;

	$Previous_Data = GetSpendingData($sql_connection, $Session_Username, $Previous_Period, "SELECT COUNT(*) AS Total_Records, COALESCE(SUM(CAST(Amount AS DECIMAL(20,2))), 0) AS Total_Spending FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ?", true);
	$Previous_Total_Records = $Previous_Data["Total_Records"] ?? 0;
	$Previous_Total_Spending = $Previous_Data["Total_Spending"] ?? 0;

	$Spending_Difference = $Total_Spending - $Previous_Total_Spending;
	$Spending_Change_Percentage = $Previous_Total_Spending > 0 ? ($Spending_Difference / $Previous_Total_Spending) * 100 : ($Total_Spending > 0 ? 100 : 0);
	$Transaction_Difference = $Total_Records - $Previous_Total_Records;
	$Transaction_Change_Percentage = $Previous_Total_Records > 0 ? ($Transaction_Difference / $Previous_Total_Records) * 100 : ($Total_Records > 0 ? 100 : 0);

	$Category_Data = GetSpendingData($sql_connection, $Session_Username, $Selected_Period, "SELECT Category, COUNT(*) AS Total_Records, COALESCE(SUM(CAST(Amount AS DECIMAL(20,2))), 0) AS Total_Amount FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ? GROUP BY Category ORDER BY Total_Amount DESC");
	$Top_Category = $Category_Data[0]["Category"] ?? "No Data";
	$Top_Category_Amount = $Category_Data[0]["Total_Amount"] ?? 0;
	$Top_Category_Percentage = $Total_Spending > 0 ? ($Top_Category_Amount / $Total_Spending) * 100 : 0;
	$Highest_Spending = $Top_Category_Amount;

	$Daily_Data = GetSpendingData($sql_connection, $Session_Username, $Selected_Period, "SELECT Date, COUNT(*) AS Total_Records, COALESCE(SUM(CAST(Amount AS DECIMAL(20,2))), 0) AS Total_Amount FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ? GROUP BY Date ORDER BY Date");
	$Highest_Day_Data = GetSpendingData($sql_connection, $Session_Username, $Selected_Period, "SELECT Date, COUNT(*) AS Total_Records, COALESCE(SUM(CAST(Amount AS DECIMAL(20,2))), 0) AS Total_Amount FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ? GROUP BY Date ORDER BY Total_Amount DESC, Date DESC", true);
	$Highest_Day_Date = $Highest_Day_Data["Date"] ?? null;
	$Highest_Day_Amount = $Highest_Day_Data["Total_Amount"] ?? 0;
	$Highest_Day_Records = $Highest_Day_Data["Total_Records"] ?? 0;
	$Days_With_Spending = count($Daily_Data);
	$Average_Daily_Spending = $Days_With_Spending > 0 ? $Total_Spending / $Days_With_Spending : 0;

	$Largest_Transactions = GetSpendingData($sql_connection, $Session_Username, $Selected_Period, "SELECT ID, Date, Description, Category, Amount, Notes FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ? ORDER BY CAST(Amount AS DECIMAL(20,2)) DESC, Date DESC, ID DESC LIMIT 5");
	$Latest_Transactions = GetSpendingData($sql_connection, $Session_Username, $Selected_Period, "SELECT ID, Date, Description, Category, Amount, Notes FROM tb_b_spending WHERE Username = ? AND DATE_FORMAT(Date, '%Y-%m') = ? ORDER BY Date DESC, ID DESC LIMIT 5");

	$Categories = [
		"Food & Drinks", "Groceries", "Shopping", "Clothing", "Footwear", "Accessories",
		"Electronics", "Computer & Accessories", "Audio", "Mobile Phone", "Camera",
		"Home Appliances", "Furniture", "Home Supplies", "Kitchen Supplies",
		"Bathroom Supplies", "Personal Care", "Stationery", "Books & Learning Materials",
		"Toys & Games", "Gaming", "Sports Equipment", "Hobby Supplies", "Automotive Parts",
		"Motorcycle Parts", "Tools", "Other Goods", "Transportation", "Fuel", "Parking",
		"Toll", "Vehicle Maintenance", "Vehicle Tax", "Bills & Utilities",
		"Phone & Internet", "Subscription", "Software & Apps", "Services", "Insurance",
		"Education", "Course & Training", "Entertainment", "Travel", "Recreation",
		"Events", "Health", "Household", "Gifts & Donations", "Tax & Administration",
		"Bank & Payment Fees", "Baby & Kids", "Pets", "Other"
	];

	$Chart_Daily_Labels = [];
	$Chart_Daily_Data = [];
	foreach ($Daily_Data as $Daily) {
		$Chart_Daily_Labels[] = date("d M", strtotime($Daily["Date"]));
		$Chart_Daily_Data[] = (float) $Daily["Total_Amount"];
	}

	$Chart_Category_Labels = [];
	$Chart_Category_Data = [];
	foreach ($Category_Data as $Category) {
		$Chart_Category_Labels[] = $Category["Category"];
		$Chart_Category_Data[] = (float) $Category["Total_Amount"];
	}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Spending Diary</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/dashboard.css" rel="stylesheet">
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
					<li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
					<li class="nav-item"><a class="nav-link" href="spending.php">Spending</a></li>
					<li class="nav-item"><a class="nav-link" href="account.php">Account</a></li>
					<li class="nav-item mobile-actions">
						<button class="btn btn-outline-light theme-toggle" id="ThemeToggle" type="button" aria-label="Toggle dark mode" title="Toggle dark mode"><span id="ThemeIcon">☾</span></button>
						<form action="index_logout.php" method="post">
							<button class="btn btn-light logout-button" type="submit">Logout</button>
						</form>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<main>
		<div class="container py-5">
			<div class="card dashboard-card">
				<div class="card-body p-4 p-md-5">
					<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
						<div>
							<h2 class="page-title mb-2">Dashboard</h2>
							<p class="text-secondary mb-0">Welcome back, <?= htmlspecialchars($Session_Name) ?>.</p>
						</div>
						<div class="d-flex flex-column flex-sm-row gap-2">
							<button class="btn btn-indigo" type="button" data-bs-toggle="modal" data-bs-target="#QuickAddSpending">+ Add Spending</button>
							<a class="btn btn-outline-secondary" href="spending.php">Manage Spending</a>
						</div>
					</div>

					<?php if ($Spending_Success === "Add"): ?>
						<div class="alert alert-success mb-4" role="alert">Spending added successfully!</div>
					<?php elseif ($Spending_Success === "Edit"): ?>
						<div class="alert alert-success mb-4" role="alert">Spending updated successfully!</div>
					<?php elseif ($Spending_Success === "Delete"): ?>
						<div class="alert alert-success mb-4" role="alert">Spending deleted successfully!</div>
					<?php endif; ?>

					<?php if ($Spending_Failed === "Data"): ?>
						<div class="alert alert-danger mb-4" role="alert">Failed to process spending data!</div>
					<?php endif; ?>

					<form action="dashboard.php" method="get" class="mb-4">
						<div class="row g-3 align-items-end">
							<div class="col-12 col-md-5">
								<label for="Month" class="form-label">Month</label>
								<select class="form-select" id="Month" name="Month" required>
									<?php foreach ($Month_Names as $Month_Number => $Month_Name): ?>
										<option value="<?= $Month_Number ?>" <?= $Selected_Month === $Month_Number ? "selected" : "" ?>><?= $Month_Name ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="col-12 col-md-5">
								<label for="Year" class="form-label">Year</label>
								<select class="form-select" id="Year" name="Year" required>
									<?php for ($Year = $Current_Year; $Year >= 2000; $Year--): ?>
										<option value="<?= $Year ?>" <?= $Selected_Year == $Year ? "selected" : "" ?>><?= $Year ?></option>
									<?php endfor; ?>
								</select>
							</div>
							<div class="col-12 col-md-2">
								<button class="btn btn-indigo w-100" type="submit">Apply</button>
							</div>
						</div>
					</form>

					<div class="mb-4">
						<h5 class="section-title mb-1"><?= $Selected_Month_Name ?> <?= $Selected_Year ?></h5>
						<p class="text-secondary mb-0">Spending analysis for the selected period.</p>
					</div>

					<div class="row g-4 mb-4">
						<?php
						$Summary_Cards = [
							["Total Spending", "Rp " . number_format($Total_Spending, 0, ",", "."), "$Total_Records transactions"],
							["Average Transaction", "Rp " . number_format($Average_Spending, 0, ",", "."), "Per transaction"],
							["Highest Spending", "Rp " . number_format($Highest_Spending, 0, ",", "."), htmlspecialchars($Top_Category)],
							["Top Category", htmlspecialchars($Top_Category), "Rp " . number_format($Top_Category_Amount, 0, ",", ".")]
						];
						foreach ($Summary_Cards as $Card):
						?>
							<div class="col-12 col-md-6 col-xl-3">
								<div class="card summary-card h-100 bg-light">
									<div class="card-body p-4">
										<div class="summary-title mb-2"><?= $Card[0] ?></div>
										<div class="stat-value"><?= $Card[1] ?></div>
										<small class="text-secondary"><?= $Card[2] ?></small>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="row g-4 mb-4">
						<div class="col-12 col-xl-8">
							<div class="card section-card h-100">
								<div class="card-body p-4">
									<h5 class="section-title mb-1">Daily Spending</h5>
									<p class="text-secondary mb-4">Daily spending during <?= $Selected_Month_Name ?> <?= $Selected_Year ?>.</p>
									<div style="height: 320px;">
										<canvas id="DailySpendingChart"></canvas>
									</div>
								</div>
							</div>
						</div>

						<div class="col-12 col-xl-4">
							<div class="card section-card h-100">
								<div class="card-body p-4">
									<h5 class="section-title mb-1">Month Comparison</h5>
									<p class="text-secondary mb-4">Current month vs previous month.</p>
									<div style="height: 320px;">
										<canvas id="MonthComparisonChart"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row g-4 mb-4">
						<div class="col-12">
							<div class="card section-card">
								<div class="card-body p-4">
									<h5 class="section-title mb-1">Spending by Category</h5>
									<p class="text-secondary mb-4">Category spending for <?= $Selected_Month_Name ?> <?= $Selected_Year ?>.</p>
									<div style="height: 500px;">
										<canvas id="CategorySpendingChart"></canvas>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row g-4 mb-4">
						<div class="col-12 col-lg-6">
							<div class="card section-card h-100">
								<div class="card-body p-4">
									<h5 class="section-title mb-4">Daily Summary</h5>
									<div class="row g-4">
										<div class="col-12 col-md-6">
											<div class="text-secondary mb-1">Average per Spending Day</div>
											<h4 class="mb-0">Rp <?= number_format($Average_Daily_Spending, 0, ",", ".") ?></h4>
										</div>
										<div class="col-12 col-md-6">
											<div class="text-secondary mb-1">Spending Days</div>
											<h4 class="mb-0"><?= $Days_With_Spending ?> days</h4>
										</div>
									</div>
									<hr>
									<div class="text-secondary mb-1">Highest Spending Day</div>
									<?php if ($Highest_Day_Date): ?>
										<h5 class="mb-1"><?= date("d F Y", strtotime($Highest_Day_Date)) ?></h5>
										<p class="mb-0">Rp <?= number_format($Highest_Day_Amount, 0, ",", ".") ?> <span class="text-secondary">· <?= $Highest_Day_Records ?> transactions</span></p>
									<?php else: ?>
										<p class="text-secondary mb-0">No spending records for this period.</p>
									<?php endif; ?>
								</div>
							</div>
						</div>

						<div class="col-12 col-lg-6">
							<div class="card section-card h-100">
								<div class="card-body p-4">
									<h5 class="section-title mb-4">Month Comparison Details</h5>
									<div class="d-flex justify-content-between mb-3">
										<span class="text-secondary"><?= $Selected_Month_Name ?> <?= $Selected_Year ?></span>
										<strong>Rp <?= number_format($Total_Spending, 0, ",", ".") ?></strong>
									</div>
									<div class="d-flex justify-content-between mb-3">
										<span class="text-secondary"><?= $Previous_Month_Name ?> <?= $Previous_Year ?></span>
										<strong>Rp <?= number_format($Previous_Total_Spending, 0, ",", ".") ?></strong>
									</div>
									<hr>
									<div class="d-flex justify-content-between mb-3">
										<span class="text-secondary">Difference</span>
										<strong>Rp <?= number_format(abs($Spending_Difference), 0, ",", ".") ?></strong>
									</div>
									<div class="d-flex justify-content-between mb-3">
										<span class="text-secondary">Spending Change</span>
										<strong><?= $Spending_Difference > 0 ? "+" : "" ?><?= number_format($Spending_Change_Percentage, 1, ",", ".") ?>%</strong>
									</div>
									<div class="d-flex justify-content-between">
										<span class="text-secondary">Transaction Change</span>
										<strong><?= $Transaction_Difference > 0 ? "+" : "" ?><?= number_format($Transaction_Change_Percentage, 1, ",", ".") ?>%</strong>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="row g-4 mb-4">
						<?php foreach (["Largest Transactions" => $Largest_Transactions, "Latest Transactions" => $Latest_Transactions] as $Title => $Transactions): ?>
							<div class="col-12 col-xl-6">
								<div class="card section-card h-100">
									<div class="card-body p-4">
										<h5 class="section-title mb-4"><?= $Title ?></h5>
										<div class="table-responsive">
											<table class="table table-hover align-middle mb-0">
												<thead>
													<tr>
														<th>Date</th>
														<th>Description</th>
														<th>Category</th>
														<th class="text-end">Amount</th>
													</tr>
												</thead>
												<tbody>
													<?php if ($Transactions): ?>
														<?php foreach ($Transactions as $Transaction): ?>
															<tr>
																<td><?= htmlspecialchars($Transaction["Date"]) ?></td>
																<td><?= htmlspecialchars($Transaction["Description"]) ?></td>
																<td><?= htmlspecialchars($Transaction["Category"]) ?></td>
																<td class="text-end">Rp <?= number_format((float) $Transaction["Amount"], 0, ",", ".") ?></td>
															</tr>
														<?php endforeach; ?>
													<?php else: ?>
														<tr><td colspan="4" class="text-center text-secondary py-4">No spending records.</td></tr>
													<?php endif; ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="text-center pt-2">
						<a class="btn btn-indigo" href="spending.php">View All Spending</a>
					</div>
				</div>
			</div>
		</div>
	</main>

	<div class="modal fade" id="QuickAddSpending" tabindex="-1" aria-labelledby="QuickAddSpendingLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-indigo" id="QuickAddSpendingLabel">Add Spending</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form action="spending_action.php" method="post">
					<div class="modal-body">
						<input type="hidden" name="Action" value="Add">
						<input type="hidden" name="Redirect" value="dashboard.php">
						<div class="mb-3">
							<label for="QuickAddDate" class="form-label">Date</label>
							<input type="date" class="form-control" id="QuickAddDate" name="Date" value="<?= date("Y-m-d") ?>" required>
						</div>
						<div class="mb-3">
							<label for="QuickAddDescription" class="form-label">Description</label>
							<input type="text" class="form-control" id="QuickAddDescription" name="Description" required>
						</div>
						<div class="mb-3">
							<label for="QuickAddCategory" class="form-label">Category</label>
							<select class="form-select" id="QuickAddCategory" name="Category" required>
								<option value="" selected disabled>Select category</option>
								<?php foreach ($Categories as $Category): ?>
									<option value="<?= htmlspecialchars($Category) ?>"><?= htmlspecialchars($Category) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="mb-3">
							<label for="QuickAddAmount" class="form-label">Amount</label>
							<input type="number" class="form-control" id="QuickAddAmount" name="Amount" min="0" step="1" required>
						</div>
						<div>
							<label for="QuickAddNotes" class="form-label">Notes</label>
							<textarea class="form-control" id="QuickAddNotes" name="Notes" rows="3"></textarea>
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

	<script src="js/bootstrap.bundle.min.js"></script>
	<script src="js/light-dark-theme.js"></script>
    <script src="js/chart.umd.min.js"></script>
	<script>
		const ChartLabels = <?= json_encode($Chart_Daily_Labels) ?>;
		const ChartDailyData = <?= json_encode($Chart_Daily_Data) ?>;
		const ChartCategoryLabels = <?= json_encode($Chart_Category_Labels) ?>;
		const ChartCategoryData = <?= json_encode($Chart_Category_Data) ?>;
		const ChartTextColor = getComputedStyle(document.documentElement).getPropertyValue("--bs-body-color").trim() || "#212529";
		const ChartGridColor = getComputedStyle(document.documentElement).getPropertyValue("--bs-border-color").trim() || "#dee2e6";

		new Chart(document.getElementById("DailySpendingChart"), {
			type: "line",
			data: {
				labels: ChartLabels,
				datasets: [{
					label: "Spending",
					data: ChartDailyData,
					borderColor: "#653795",
					backgroundColor: "rgba(101,55,149,0.12)",
					fill: true,
					tension: 0.35,
					pointRadius: 4,
					pointHoverRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: context => "Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y)
						}
					}
				},
				scales: {
					x: {
						ticks: { color: ChartTextColor },
						grid: { color: ChartGridColor }
					},
					y: {
						beginAtZero: true,
						ticks: {
							color: ChartTextColor,
							callback: value => "Rp " + new Intl.NumberFormat("id-ID").format(value)
						},
						grid: { color: ChartGridColor }
					}
				}
			}
		});

		new Chart(document.getElementById("CategorySpendingChart"), {
			type: "bar",
			data: {
				labels: ChartCategoryLabels,
				datasets: [{
					label: "Spending",
					data: ChartCategoryData,
					backgroundColor: "#653795",
					borderRadius: 6
				}]
			},
			options: {
				indexAxis: "y",
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: context => "Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.x)
						}
					}
				},
				scales: {
					x: {
						beginAtZero: true,
						ticks: {
							color: ChartTextColor,
							callback: value => "Rp " + new Intl.NumberFormat("id-ID").format(value)
						},
						grid: { color: ChartGridColor }
					},
					y: {
						ticks: { color: ChartTextColor },
						grid: { display: false }
					}
				}
			}
		});

		new Chart(document.getElementById("MonthComparisonChart"), {
			type: "bar",
			data: {
				labels: [<?= json_encode($Previous_Month_Name) ?>, <?= json_encode($Selected_Month_Name) ?>],
				datasets: [{
					label: "Spending",
					data: [<?= (float) $Previous_Total_Spending ?>, <?= (float) $Total_Spending ?>],
					backgroundColor: ["#8b8b8b", "#653795"],
					borderRadius: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: { display: false },
					tooltip: {
						callbacks: {
							label: context => "Rp " + new Intl.NumberFormat("id-ID").format(context.parsed.y)
						}
					}
				},
				scales: {
					x: {
						ticks: { color: ChartTextColor },
						grid: { display: false }
					},
					y: {
						beginAtZero: true,
						ticks: {
							color: ChartTextColor,
							callback: value => "Rp " + new Intl.NumberFormat("id-ID").format(value)
						},
						grid: { color: ChartGridColor }
					}
				}
			}
		});
	</script>
</body>
</html>