<?php
	require 'header.php';
	
	try
	{
		$report = Report::CreateReport(null, "Dashboard", true, Display::$Table, true);//ReportDataHelper::getReportByID(SQLConfig::$DashboardReportID);
		$report->setStatistics(array(ReportStatistic::CreateReportStatistic(2), ReportStatistic::CreateReportStatistic(1)));
		$reportData = $report->getReportData(false);
		$dashboardRowValues = $reportData->getSum()->getRowValues();
	}
	catch(Exception $ex)
	{
		$dashboardError = $ex->getCode() . ' ' . $ex->getMessage();
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
				<div class="value"><?php echo htmlentities(Util::FormatNumber($dashboardRowValues[0], 0)); ?></div>
				<div class="key">subscribers</div>
			</div>
			<div class="right">
				<div class="value"><?php echo htmlentities(Util::FormatNumber($dashboardRowValues[1], 0)); ?></div>
				<div class="key">views</div>
			</div>
		</div>
	</div>
</section>
<section class="reports">
<div class="reports-helptext">
		To view a specific report, choose a channel name from the column down the left side of the page.
</div>
</section>
<?php 
	if(isset($dashboardError))
	{
		echo '-->';
	}
?>
<div style="clear:both"></div>
<?php require 'footer.php'; ?>