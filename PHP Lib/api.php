<?php
	require_once 'google-api-php-client/src/Google_Client.php';
	require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
	require_once 'data.php';
	
	class API
	{
		private static $clientID = '696232355614.apps.googleusercontent.com';
		private static $clientSecret = '9hPHiYyVKNiMDNXkTNuayD6Q';
		private static $redirectUri = $serverRoot . '/yogstats/authCode.php';
		private static $domain = 'localhost';
		private static $serverRoot = 'https://' . $domain;
		public static function hasSession()
		{
			return session_id() != '' && isset($_SESSION);
		}
		
		public static function hasCode()
		{
			return isset($_SESSION['authState']) && isset ($_SESSION['authCode']);
		}
		
		public static function hasAccessCode()
		{
			return isset($_SESSION['authAccessToken']);
		}
		
		public static function needsRefresh()
		{
			return !isset($GLOBALS['gClient']) || $GLOBALS['gClient']->isAccessTokenExpired();
		}
		
		public static function startSession()
		{
			if(!API::hasSession()) session_start();
		}
		
		public static function hasUser()
		{
			return isset($_SESSION['authUser']);
		}
		
		//Permissions array optional, passing with no parameters will run default auth method
		public static function authenticate($permissions = array('openid', 'email'))
		{
			if(!$_SERVER['HTTPS'])
			{
				header('Location: ' . API::$serverRoot . $_SERVER['REQUEST_URI']);
				exit;
			}
			API::startSession();
			
			if(!API::hasUser())
			{
				//Have a cookie, lookup existing user data
				$userFound = false;
				if(isset($_COOKIE['preUser']))
				{
					$user = UserDataHelper::getUserByHash($_COOKIE['preUser']);
					
					if($user != null)
					{
						$userFound = true;
						$_SESSION['authAccessToken'] = $user->getAccessToken();
						$_SESSION['authUser'] = $user;
					}
				}
				if(!$userFound)
				{
					if(!API::hasAccessCode())
					{
						if(!API::hasCode())
						{	
							$authState = md5(rand());
							$_SESSION['authState'] = $authState;
							header('Location: ' . API::createAuthCodeURI(API::$redirectUri, implode(' ', $permissions), 'https://localhost' . $_SERVER['REQUEST_URI']));
							exit;
						}
						/*$ch = curl_init('https://accounts.google.com/o/oauth2/token');
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						//curl_setopt($ch, CURLOPT_CAINFO, getcwd() . '\certificates\googleAccounts.crt');
						
						$postFields = array('code' => $_SESSION['authCode'], 'client_id' => API::$clientID, 
											'client_secret' => API::$clientSecret, 
											'redirect_uri' => 'http://localhost/yogstats/authCode.php', 'grant_type' => 'authorization_code');
						
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
						$postVal = curl_exec($ch);
						echo 'curl done ' . curl_error($ch) . '<br>';
						curl_close($ch);
						echo $postVal;
						*/
						$GLOBALS['gClient'] = new Google_Client();
						$GLOBALS['gClient']->setClientId(API::$clientID);
						$GLOBALS['gClient']->setClientSecret(API::$clientSecret);
						$GLOBALS['gClient']->setRedirectUri(API::$redirectUri);
						$GLOBALS['gClient']->authenticate($_SESSION['authCode']);
						$_SESSION['authAccessToken'] = $GLOBALS['gClient']->getAccessToken();
					}
					
					$userInfo = API::getUserData();
					$user = UserDataHelper::getUserByID($userInfo['id']);
					
					if($user == null)
					{
						$user = User::CreateFromAuthToken($_SESSION['authAccessToken'], $userInfo['email'], $userInfo['id']);
						UserDataHelper::putNewUser($user);
					}
					else
					{
						$user->setAuthToken($_SESSION['authAccessToken']);
						UserDataHelper::updateUser($user);
					}
					
					$_SESSION['authUser'] = $user;
					setcookie('preUser', $user->getHash(), time() + 60*60*24, '/', $domain, true);
				}
				
				if(!isset($GLOBALS['gClient']))
				{
					$GLOBALS['gClient'] = new Google_Client();
					$GLOBALS['gClient']->setClientId(API::$clientID);
					$GLOBALS['gClient']->setClientSecret(API::$clientSecret);
					$GLOBALS['gClient']->setRedirectUri(API::$redirectUri);
					$GLOBALS['gClient']->setAccessToken($_SESSION['authAccessToken']);
				}
				
				if(API::needsRefresh())
				{
					$GLOBALS['gClient']->refreshToken(json_decode($GLOBALS['gClient']->getAccessToken())->{'refresh_token'});
					$_SESSION['authAccessToken'] = $GLOBALS['gClient']->getAccessToken();
				}
			}
			return true;
		}
		
		private static function getUserData()
		{
			$service = new Google_Oauth2Service($GLOBALS['gClient']);
			$info = $service->userinfo->get();
			return $info;
		}
		
		public static function createAuthCodeURI($redirectURI, $permissions, $fromUrl)
		{
			return 'https://accounts.google.com/o/oauth2/auth?client_id=' . 
			API::$clientID . 
			'&response_type=code&access_type=offline&prompt=consent' . 
			'&scope=' .
			urlencode($permissions) .
			'&redirect_uri=' . 
			$redirectURI .
			'&state=' . 
			urlencode('security_token=' . $_SESSION['authState'] . '&from=' . $fromUrl);
		}
		
		public static function getEmailAddress()
		{
			API::authenticate();
			$service = new Google_Oauth2Service($GLOBALS['gClient']);
			$info = $service->userinfo->get();
			return $info['email'];
		}
	}
?>