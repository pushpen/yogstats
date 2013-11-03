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
		$stats = StatisticDataHelper::getStatistics();
		foreach($stats as $value)
		{
			var_dump($value);
			echo '<br>';
		}
	}
?>
</body>
</html>