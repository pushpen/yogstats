<?php
	require_once __DIR__ . '/../../PHP Lib/api.php';
	
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
	$createError = '';
	if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['formID']))
	{
		switch($_POST['formID'])
		{
			case 'createChannel':
				try
				{
					//TODO: Need to add permissions checks!
					TrackedChannelDataHelper::addTrackedChannel($_POST['channelName']);
				}
				catch(Exception $ex)
				{
					switch($ex->getCode())
					{
						case 1:
							$createError = $ex->getMessage();
						break;
						case 1062:
							$createError = $_POST['channelName'] . ' already in tracked channels';
						break;
						default:
						$createError = $ex->getCode() . ' ' . $ex->getMessage();
					}
				}
			break;
		}
	}
?>
<script type="text/javascript" src="../../JS Lib/tools.js"> </script>
<?php if($authError != '') echo '<p class="error-text">' . $authError . '</p>'; ?>
<section class="channel-add">
	<h2>Add a channel</h2>
	<p>Add a channel here to have it's statistics be included in future reports.</p>
	<form id = 'createChannel' onsubmit="postForm(this);" method="POST">
		<label for="channel-name">YouTube Name:</label>
		<input type="text" name = "channelName" id="channel-name" />
		<div class="help-text"><strong>Without</strong> http://youtube.com/</div>
		<input type="button" onclick = "postFormButton(this);" value="Add channel" />
		<?php if($createError != '') echo '<p class="error-text">' . $createError . '</p>'; ?>
	</form>
</section>