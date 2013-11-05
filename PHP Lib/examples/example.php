<?php
	//Must be done before all html markup and in all php files viewable by a user
	//Include the back-end stuff
	require_once __DIR__ . '/../api.php';
	//Authenticate the user, will throw exception if cannot authenticate
	Auth::authenticate();
?>

<html>
<head>
<title>Examples</title>
</head>
<body>
<?php
	//Example 1: Sanitise user input
	$someUserInput = '1 abc';
	$sanitisedInputInt = Util::SQLSanitise($someUserInput, SanitiseType::$Int);
	$someUserInput = '1.21 abc';
	$sanitisedInputFloat = Util::SQLSanitise($someUserInput, SanitiseType::$Float);
	$someUserInput = 'ad" AND 1 = 1';
	$sanitisedInputString = Util::SQLSanitise($someUserInput, SanitiseType::$String);
	
	//Example 2: Add tracked channel - $channelName not used in SQL, so no sanitisation required
	$channelName = 'BlueXephos';
	TrackedChannelDataHelper::addTrackedChannel($channelName);
	
	//Example 3: Current User details
	$user = Util::getUser();
	$user->getGoogleID(); //Unique ID
	$user->getEmail();//Email address of user
	
	//Example 4: Lists of users
	$users = UserDataHelper::getUsers(); //Array of all users on the site
	$otherUsers = UserDataHelper::getUsers(Util::getUser()); //Array of all users except the one logged in
	
	//Example 5: List of tracked channels
	$trackedChannels = TrackedChannelDataHelper::getTrackedChannels();
	
	//Example 6: List of statistics
	$availableStats = StatisticDataHelper::getStatistics();
	
	//Example 7: Add report
	//The most complex part, you first need to build up three arrays
	
		//7a: Permissions list
		$permissions = array();
		//Get a user to give permissions to (currently only supports users already registered on the site)
		$user = UserDataHelper::getUserByID('116428524452295004944');
		//Add permission to array
		$permissions[] = ReportPermission::CreatePermission($user->getGoogleID(), Permission::$view);
		
		//7b: Channel list
		$channels = array();
		//Get the ID of the first tracked channel
		$trackedChannelID = $trackedChannels[0]->getTrackedID();
		//Add channel to array
		$channels[] = ReportChannel::CreateChannel($trackedChannelID);
		
		//7c: Statistics list
		$statistics = array();
		//Get the ID of the first statistic
		$statID = $availableStats[0]->getStatisticID();
		//Add statistic to array
		$statistics[] = ReportStatistic::CreateStatistic($statID);
		
		//7d: Create the report
		//Current user's ID
		$creatorID = Util::getUser()->getGoogleID();
		//What type of report should be displayed
		$displayType = Display::$table;
		//Does it have a sum field?
		$hasSum = true;
		//Create the report object
		$report = Report::CreateReport($creatorID, $displayType, $hasSum);
		
		//7e: Commit to the database
		ReportDataHelper::putNewReport($report, $permissions, $channels, $statistics);
	
	//Example 8: Validating numbers
	$userNumberString = '1322';
	//Ensures string is a number between 0 and 10 (inclusive)
	$valid = Util::ValidateNumber($userNumberString, 0, 10);
	//Check for an error (make sure to use ===)
	if($valid !=== 0)
	{
		//The error message, make sure to use the min and max
		//Or look at this function to help you write your own
		$errorMessage = Util::GetValidateErrorMessage($valid, 0, 10);
	}
?>
</body>
</html>