<?php
	require_once "PHP Lib/api.php";
	
	$error = false;
	
	try
	{
		Auth::authenticate();
	}
	catch(Exception $ex)
	{
		$error = 'Authentication failed ' . $ex->getMessage();
	}
?>
<html>
<head>
<title>Testing Google login</title>
</head>
<body>
<?php
	if($error)
	{
		echo $error;
	}
	else
	{
		function printDataRow($row, $columnTag = '<td>', $columnCloseTag = '</td>')
		{
			echo '<tr>';
			echo $columnTag;
			echo $row->getRowName();
			echo $columnCloseTag;
			
			foreach($row->getRowValues() as $value)
			{
				echo $columnTag;
				echo $value;
				echo $columnCloseTag;
			}
			
			echo '</tr>';
		}
		
		//TrackedChannelDataHelper::addTrackedChannel('BlueXephos');
		//TrackedChannelDataHelper::addTrackedChannel('yogscast2');
		//TrackedChannelDataHelper::addTrackedChannel('yogscastlalna');
		//TrackedChannelDataHelper::addTrackedChannel('Mirosta1504');
		//ReportDataHelper::putNewReport(Report::CreateReport(Util::getUser()->getGoogleID(), Display::$Table, true), array(), array(ReportChannel::CreateReportChannel(2),ReportChannel::CreateReportChannel(3),ReportChannel::CreateReportChannel(4)), array(ReportStatistic::CreateReportStatistic(1),ReportStatistic::CreateReportStatistic(2)));
		$reports = ReportDataHelper::getVisibleReports(Util::getUser());
		$report = $reports[1];
		$data = $report->getReportData();
		
		echo $report->getReportName() . '<br>';
		echo '<table border="1" cellpadding="5">';
		$header = $data->getHeader();
		$sum = $data->getSum();
		
		if($header != null)
		{
			printDataRow($header, '<th>', '</th>');
		}
		
		foreach($data->getData() as $dataRow)
		{
			printDataRow($dataRow);
		}
		
		if($sum != null)
		{
			printDataRow($sum, '<td><b>','</b></td>');
		}
		echo '</table>';
	}
?>
</body>
</html>