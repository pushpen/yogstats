<?php
	require_once 'PHP Lib/api.php';
	
	Auth::startSession();
	
	function splitQueryParameters($query)
	{
		$exploded = explode('&', $query);
		$parameters = array();
		for($i = 0; $i < sizeof($exploded); $i++)
		{
			$kvPair = explode('=', $exploded[$i]);
			
			if(sizeof($kvPair) >= 2) 
			{
				$parameters[$kvPair[0]] = $kvPair[1];
			}
		}
		return $parameters;
	}
	
	$error = '';
	
	$encodedGet = splitQueryParameters($_SERVER['QUERY_STRING']);
	$decodedState = splitQueryParameters(urldecode($encodedGet['state']));
		
	if($_SESSION['authState'] != $decodedState['security_token'])
	{
		echo 'Error: invalid security token';
	}
	else if(!isset($_GET['error']) && isset($_GET['code']))
	{
		$_SESSION['authCode'] = $_GET['code'];
		$error = Auth::authenticate();
		try
		{
			
		}
		catch(Exception $ex)
		{
			$error = $ex->getMessage();
		}
		if($error == 1)
		{
			header('Location: ' . $decodedState['from']);
			exit;
		}
	}
	else
	{
		echo $_GET['error'];
	}
	
	?>

<html>
<head>
<title>Testing google login stage 2</title>
</head>
<body>
	<?php
		echo 'Error: ' . $error;
	?>
</body>
</html>