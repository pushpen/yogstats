<?php
	require_once "PHP Lib/api.php";
	
	$error = false;
	
	try
	{
		API::authenticate();
	}
	catch(Exception $ex)
	{
		$error = 'Authentication failed';
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
		echo API::getEmailAddress();
	}
?>
</body>
</html>