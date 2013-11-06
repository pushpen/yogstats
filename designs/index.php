<?php 
	require_once __DIR__ . '/../PHP Lib/api.php';
	Auth::authenticate();
	
	function writeTrackedChannels($channels)
	{
		foreach($channels as $channel)
		{
			echo '<tr>';
			echo '<td>' . $channel->getChannelName() . '</td>';
			echo '<td>' . (($channel->getAuthToken() != null) ? ('Yes'):('No')) . '</td>';
			echo '<td><button>Remove</buttom></td>';
			echo '</tr>';
		}
	}
	$channelError = '';
	
	if($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		if(isset($_POST['formID']))
		{
			$formID = $_POST['formID'];
			switch($formID)
			{
				case 'createChannelForm':
					try
					{
						TrackedChannelDataHelper::addTrackedChannel($_POST['channel']);
					}
					catch(Exception $ex)
					{
						switch($ex->getCode())
						{
							case 1062:
								$channelError = 'Channel already added! <br>';
							break;
							default:
								$channelError = $ex->getCode() . ' ' . $ex->getMessage() . '<br>';
						}
					}
				break;
			}
		}
	}
	
	$report = ReportDataHelper::getReportByID(4);
	$reportData = $report->getReportData(false);
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Yogstats Beta</title>
		<link rel="stylesheet" href="css/style.css" />
		<!--- scripts here eventually when we need them, namely: jQuery, Google Visualization Plugin, etc. -->
		<script type="text/javascript" src="//use.typekit.net/pqg7zey.js"></script>
		<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
		<script type="text/javascript">
			function postForm(formID)
			{
				document.getElementById(formID).submit();
			}
			
			function removeChannel(id)
			{
				document.getElementById('').
			}
		</script>
	</head>
	<body>
		<header>
			<h1>Yogstats</h1>
			<nav>
				<ul>
					<li><a href="#">Reports</a></li>
					<li><a href="#">Channel Manager</a></li>
					<li><a href="#">Self-destruct Button</a></li>
					<li class="logout"><a href="#">Log Out</a></li>
				</ul>
			<nav>
		</header>
		<aside>
			<h2>Reports</h2>
			<ul>
				<li><a href="#">Dashboard</a></li>
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
				</li>
				<li class="button"><a href="#">Add a report</a></li>
			</ul> 
		</aside>
		<main>
			<h2>Dashboard</h2>
			<section class="summary">
				Across all channels, Yogscast has accumulated...
				<?php 
					foreach($_POST as $postKey => $postVal)
					{
						echo $postKey . ' => ' . $postVal . '<br>';
					}
				?>
				<div class="readout">
					<div class="left">
						<div class="value"><?php echo $reportData->getSum()->getRowValues()[1] ?></div>
						<div class="key">subscribers</div>
					</div>
					<div class="right">
						<div class="value"><?php echo $reportData->getSum()->getRowValues()[1] ?></div>
						<div class="key">views</div>
					</div>
				</div>
			</section>
			<section class="reports">
				To view data pertaining to each channel specifically, choose a channel name from the column down the left side of the page.
			</section>
			<div style="clear:both"></div>
			<section class="add chanel">
				<h2>Add a channel</h2>
				<p>Add a channel here to have it's statistics be included in future reports.</p>
				<form id = "createChannelForm" method = "POST">
					<label for="f-name">YouTube Name:</label>
					<input type="text" id="f-name" name ="channel" />
					<input type="hidden" name="formID" value="createChannelForm" />
					<div class="help-text"><strong>Without</strong> http://youtube.com/</div>
					<input type="button" onclick="postForm('createChannelForm')" value="Add channel" />
				</form>
				<p style='color:red;'><b><?php echo $channelError ?></b></p>
			</section>
			<div style="clear:both"></div>
			<section class="edit chanel">
				<h2>Channel Manager</h2>
				<p>Click a channel's button to have it be removed from future reports.</p>
				<form id = "removeChannelForm" method="POST">
				<table>
					<thead>
						<tr>
							<th>Name</th>
							<th>Auth status<br/>(<abbr title="Channels must authenticate with our application to be included in advanced reports and graphs. If they do not authenticate, their Views and Subscribers will still be shown, but advanced reports may not be accurate.">what does this mean?</abbr>)</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						
						writeTrackedChannels(TrackedChannelDataHelper::getTrackedChannels());
						
						?>
					</tbody>
				</table>
				</form>
			</section>
		</main>
		<footer>
			Built by <a href="#">Tom</a> and <a href="#">Ed</a>
		</footer>
	</body>
</html>
