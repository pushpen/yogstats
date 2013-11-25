<?php 
	require 'header.php';

	function printReports($reports)
	{
		foreach($reports as $report)
		{
			echo '<tr><td><a href=report-view.php?ReportID=' . urlencode($report->getReportID()) . '>' . htmlentities($report->getReportName()) . '</a></td></tr>';
		}
	}

	$reportsError = '';

	try
	{
		$reports = ReportDataHelper::getVisibleReports(Util::getUser());
		
		if(sizeof($reports) === 0) $reportsError = 'There are no reports to show';
	}
	catch(Exception $ex)
	{
		$reportsError = $ex->getCode() . ' ' . $ex->getMessage();
	}
	
?>
<section class="reports">
	<h2>Reports</h2>
	<?php 
		if($reportsError != '')
		{
			echo '<p class="error-text">' . htmlentities($reportsError) . '</p>';
			echo '<!--';
		}
	?>
	<table>
		<thead>
			<tr>
				<th>Report Name</th>
			</tr>
		</thead>
		<tbody>
		<?php if($reportsError == '') printReports($reports) ?>
		</tbody>
	</table>
	<?php 
		if($reportsError != '') echo '-->';
	?>
</section>
<?php 
require 'footer.php';
?>