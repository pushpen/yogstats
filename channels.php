<?php
	//Must be done before all html markup and in all php files viewable by a user
	//Include the back-end stuff
	require_once __DIR__ . '/PHP Lib/api.php';
	//Authenticate the user, will throw exception if cannot authenticate
	Auth::authenticate();
?>

<?php
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
?>