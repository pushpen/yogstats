<?php
	require_once '../../PHP Lib/api.php';
	$authError = '';
	try
	{
		Auth::authenticate();
	}
	catch(Exception $ex)
	{
		switch($ex->getCode())
		{
			default:
				$authError = $ex->getCode() . ' ' . $ex-getMessage();
		}
	}
	
	function printReportList($reports)
	{
		foreach($reports as $report)
		{
			echo '<li><a href=report-view.php?ReportID=' . urlencode($report->getReportID()) . '>' . htmlentities($report->getReportName()) . '</a></li>';
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
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Yogstats Beta</title>
		<link href="https://dl.dropboxusercontent.com/u/16697297/work/yogscast/stats_compiled.css" rel="stylesheet" type="text/css"/>
		<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700">
		<!--- scripts here eventually when we need them, namely: jQuery, Google Visualization Plugin, etc. -->
		<script type="text/javascript" src="../../JS Lib/tools.js"> </script>
	</head>
	<body>
		<header>
			<a href="dashboard.php"><h1>Yogstats</h1></a>
			<nav>
				<ul>
					<li><a href="dashboard.php">Dashboard</a></li>
					<li><a href="reports.php">Reports</a></li>
					<li><a href="channel-manager.php">Channel Manager</a></li>
					<li class="logout"><a href="#">Log Out</a></li>
				</ul>
			<nav>
		</header>
		<aside>
			<h2>Reports</h2>
			<?php 
				if($reportsError != '')
				{
					echo '<p class="error-text">' . htmlentities($reportsError) . '</p>';
					echo '<!--';
				}
			?>
			<ul>
				<?php if($reportsError == '') printReportList($reports) ?>
				<!--
				<li>Individual views:
					<ul>
						<li><a href="#">bluexephos</a></li>
						<li><a href="#">yogscast2</a></li>
						<li><a href="#">yogscastsips</a></li>
						<li><a href="#">yogscastlalna</a></li>
						<li><a href="#">yogscastsjin</a></li>
						<li><a href="#">yogscastrythian</a></li>
						<li>...</li>
					</ul>
				</li>-->
				<li class="button"><a href="#">Add a report</a></li>
			</ul> 
			<?php 
				if($reportsError != '') echo '-->';
			?>
		</aside>
		<main>
		<?php if($authError != '') echo '<p class="error-text">' . $authError . '</p>'; ?>