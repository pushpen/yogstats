<?php
	require_once __DIR__ . '/../../PHP Lib/api.php';
	Auth::authenticate();
	
	try
	{
		$report = Report::CreateReport(null, "Dashboard", true, Display::$Table, true);//ReportDataHelper::getReportByID(SQLConfig::$DashboardReportID);
		$report->setStatistics(array(ReportStatistic::CreateReportStatistic(2), ReportStatistic::CreateReportStatistic(1)));
		$reportData = $report->getReportData(false);
		$dashboardRowValues = $reportData->getSum()->getRowValues();
	}
	catch(Exception $ex)
	{
		$dashboardError = $ex.getCode() . ' ' . $ex->getMessage();
	}
?>

<?php 
	if(isset($dashboardError))
	{
		echo '<p class="error-text">' . $dashboardError . '</p>';
		echo '<!--';
	}
?> 
<section class="dashboard">
	<h2>Dashboard</h2>
	<div class="summary">
		Across all channels, Yogscast has accumulated...
		<div class="readout">
			<div class="left">
				<div class="value"><?php echo htmlentities($dashboardRowValues[0]); ?></div>
				<div class="key">subscribers</div>
			</div>
			<div class="right">
				<div class="value"><?php echo htmlentities($dashboardRowValues[1]); ?></div>
				<div class="key">views</div>
			</div>
		</div>
	</div>
	<div class="reports-helptext">
		To view data pertaining to each channel specifically, choose a channel name from the column down the left side of the page.
	</div>
</section>
<?php 
	if(isset($dashboardError))
	{
		echo '-->';
	}
?>
<div style="clear:both"></div>