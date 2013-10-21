<?php
	require_once 'google-api-php-client/src/Google_Client.php';
	//require_once 'google-api-php-client/src/contrib/
	
	function hasSession()
	{
		return isset(session_id() != '' && isset($_SESSION));
	}
	
	function hasAuthed()
	{
		return isset($_SESSION['authState']);
	}
	
	function authenticate()
	{
		if(!hasSession()) session_start();
		if(!hasAuthed())
		{
			$authState = md5(rand());
			$_SESSION['authState'] = $authState;
		}
	}
	
	function createAuthURI()
	{
		return "https://accounts.google.com/o/oauth2/auth?client_id=" + 
		$clientID + 
		"&response_type=code&scope=openid%20email&redirect_uri=" + 
		$redirectURI +
		"&state=security_token" + 
		htmlentities($_SESSION['authState']) + 
		%3D138r5719ru3e1%26url%3Dhttps://oa2cb.example.com/myHome&login_hint=jsmith@example.com";
	}
?>