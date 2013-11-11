<?php
	require_once __DIR__ . '/../../PHP Lib/api.php';
	Auth::authenticate();
	
	function printReportData($reportData)
	{
		echo '<table>';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . htmlentities($reportData->getHeader()->getRowName()) . '</th>';
		
		foreach($reportData->getHeader()->getRowValues() as $rowVal)
		{
			echo '<th>' . htmlentities($rowVal) . '</th>';
		}
		
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		
		foreach($reportData->getData() as $dataRow)
		{
			echo '<tr>';
			echo '<td class="value">' . htmlentities($dataRow->getRowName()) . '</td>';
			
			foreach($dataRow->getRowValues() as $rowVal)
			{
				echo '<td class="value">' . htmlentities($rowVal) . '</td>';
			}
			echo '</tr>';
		}
		
		if($reportData->getSum() != null)
		{
			echo '<tr>';
			echo '<td class="sum">' . htmlentities($reportData->getSum()->getRowName()) . '</td>';
			
			foreach($reportData->getSum()->getRowValues() as $rowVal)
			{
				echo '<td class="value">' . htmlentities($rowVal) . '</td>';
			}
			echo '</tr>';
		}
		
		echo '</tbody>';
		echo '</table>';
	}
	
	$reportError = '';
	if(isset($_GET['ReportID']))
	{
		$reportID = $_GET['ReportID'];
		$valid = Util::ValidateNumber($reportID, 1);
		if($valid === 0)
		{
			//TODO: Permissions checks
			try
			{
				$report = ReportDataHelper::getReportByID(Util::SQLSanitise($reportID, SanitiseType::$Int));
				if($report == null)
				{
					$reportError = 'Invalid report ID';
				}
			}
			catch(Exception $ex)
			{
				switch($ex->getCode())
				{
					default:
					echo 'Error <br>';
					$reportError = $ex->getCode() . ' ' . $ex->getMessage();
				}
			}
		}
		else
		{
			$reportError = 'Invalid report ID';
		}
	}
	else
	{
		$reportError = 'Invalid report ID';
	}
?>
<section class="report-view">
	<?php 
	if($reportError != '') echo '<p class="error-text">' . htmlentities($reportError) . '</p>';
	else
	{
		echo '<h2>'. htmlentities($report->getReportName()) . '</h2>';
		printReportData($report->getReportData(true));
		/*
		Example
		<h2>Report Name</h2>
		<table>
			<thead>
				<tr>
					<th>Channel Name</th>
					<th>Total Views</th>
					<th>Total Subscribers</th>
				</tr>
				-->
			</thead>
			<tbody> 
				<tr>
					<td class="value">BlueXephos</td>
					<td class="value">12331322</td>
					<td class="value">4233121</td>
				</tr> 
				<tr>
					<td class="name">Sum</td>
					<td class="value">12331322</td>
					<td class="value">4233121</td>
				</tr>
			</tbody>
		</table>*/
	}
	?>
</section>