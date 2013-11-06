<?php
	require_once __DIR__ . '/../../PHP Lib/api.php';
	Auth::authenticate();
	
	function printChannels($channels)
	{
		foreach($channels as $channel)
		{
			echo '<tr>';
			echo '<td>' . htmlentities($channel->getChannelName()) . '</td>';
			echo '<td>' . ($channel->getAuthToken() != null ? 'Yes':'No') . '</td>';
			echo '<td><button onclick="removeChannel(' . $channel->getTrackedID() . ',this)">Remove</button></td>';
			echo '</tr>';
		}
	}
	
	$removeError = '';
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['formID']))
	{
		switch($_POST['formID'])
		{
			case 'removeChannel':
				try
				{
					//TODO: Need to add permissions checks!
					$removeID = $_POST['removeID'];
					$valid = Util::ValidateNumber($removeID, 1);
					if($valid === 0)
					{
						TrackedChannelDataHelper::removeTrackedChannel(Util::SQLSanitise($removeID, SanitiseType::$Int));
					}
					else
					{
						$removeError = 'Failed to remove, invalid removeID';
					}
				}
				catch(Exception $ex)
				{
					switch($ex->getCode())
					{
						default:
						$removeError = $ex->getCode() . ' ' . $ex->getMessage();
					}
				}
			break;
		}
	}
	
	$trackedChannels = TrackedChannelDataHelper::getTrackedChannels();
?>
<script type="text/javascript" src="../../JS Lib/tools.js"> </script>
<script type="text/javascript">
	function removeChannel(id, button)
	{
		//TODO: Add double check, make sure we don't accidentally remove a channel
		var channelIDField = addHiddenField(id, "removeID", button.form);
		postFormButton(button);
		button.form.removeChild(channelIDField);
	}
</script>
<section class="channel-manager">
	<h2>Channel Manager</h2>
	<p>Click a channel's button to have it be removed from future reports.</p>
	<?php if($removeError != '') echo '<p class="error-text">' . $removeError . '</p>'; ?>
	<form id="removeChannel" method="POST">
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Auth status<br/>(<abbr title="Channels must authenticate with our application to be included in advanced reports and graphs. If they do not authenticate, their Views and Subscribers will still be shown, but advanced reports may not be accurate.">what does this mean?</abbr>)</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php printChannels($trackedChannels); ?>
				<!-- example <tr>
					<td>bluexephos</td>
					<td>Yes</td>
					<td><button>Remove</buttom></td>
				</tr> 
				<tr>
					<td>yogscast2</td>
					<td>Yes</td>
					<td><button>Remove</button></td>
				</tr>
				<tr>
					<td>yogscastsips</td>
					<td>Yes</td>
					<td><button>Remove</buttom></td>
				</tr>
				<tr>
					<td>yogscastlalna</td>
					<td>Yes</td>
					<td><button>Remove</buttom></td>
				</tr>
				<tr>
					<td>yogscastsjin</td>
					<td>No</td>
					<td><button>Remove</buttom></td>
				</tr>
				<tr>
					<td>yogscastrythian</td>
					<td>No</td>
					<td><button>Remove</buttom></td>
				</tr>
				<tr>
					<td>haatfilms</td>
					<td>Yes</td>
					<td><button>Remove</buttom></td>
				</tr>
				<tr>
					<td>yogscastkim</td>
					<td>Yes</td>
					<td><button>Remove</buttom></td>
				</tr>
				<tr>
					<td>thestrippin</td>
					<td>No</td>
					<td><button>Remove</buttom></td>
				</tr>
				-->
				
			</tbody>
		</table>
	</form>
</section>