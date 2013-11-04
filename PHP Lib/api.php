<?php
	require_once 'google-api-php-client/src/Google_Client.php';
	require_once 'google-api-php-client/src/contrib/Google_Oauth2Service.php';
	require_once 'PHP Lib/config.php';
	require_once 'PHP Lib/data.php';
	require_once 'PHP Lib/user.php';
	require_once 'PHP Lib/trackedChannel.php';
	require_once 'PHP Lib/report.php';
	
	class Util
	{
		public static function getUser()
		{
			if(!Auth::hasUser())
			{
				return null;
			}
			return $_SESSION['authUser'];
		}
	}
	
	class Auth
	{
		private static $redirectUri = 'https://localhost/yogstats/authCode.php';
		private static $domain = '';
		private static $serverRoot = 'https://localhost';
		
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
			if(!Auth::hasSession()) session_start();
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
				header('Location: ' . Auth::$serverRoot . $_SERVER['REQUEST_URI']);
				exit;
			}
			Auth::startSession();
			
			if(!Auth::hasUser())
			{
				echo 'No User';
				//Have a cookie, lookup existing user data
				$userFound = false;
				if(isset($_COOKIE['preUser']))
				{
					global $sqlSource;
					$user = UserDataHelper::getUserByHash($sqlSource->sanitise($_COOKIE['preUser']));
					
					if($user != null)
					{
						$userFound = true;
						$_SESSION['authAccessToken'] = $user->getAccessToken();
						$_SESSION['authUser'] = $user;
					}
				}
				//No valid user found
				if(!$userFound)
				{
					//Doesn't have access token
					if(!Auth::hasAccessCode())
					{
						//Doesn't have auth code
						if(!Auth::hasCode())
						{	
							$authState = md5(rand());
							$_SESSION['authState'] = $authState;
							header('Location: ' . Auth::createAuthCodeURI(Auth::$redirectUri, implode(' ', $permissions), 'https://localhost' . $_SERVER['REQUEST_URI']));
							exit;
						}
						/*$ch = curl_init('https://accounts.google.com/o/oauth2/token');
						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						//curl_setopt($ch, CURLOPT_CAINFO, getcwd() . '\certificates\googleAccounts.crt');
						
						$postFields = array('code' => $_SESSION['authCode'], 'client_id' => Auth::$clientID, 
											'client_secret' => Auth::$clientSecret, 
											'redirect_uri' => 'http://localhost/yogstats/authCode.php', 'grant_type' => 'authorization_code');
						
						curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
						$postVal = curl_exec($ch);
						echo 'curl done ' . curl_error($ch) . '<br>';
						curl_close($ch);
						echo $postVal;
						*/
						
						//Create google client and oauth service, authenticate
						$GLOBALS['gClient'] = new Google_Client();
						$GLOBALS['gClient']->setClientId(AuthConfig::$ClientID);
						$GLOBALS['gClient']->setClientSecret(AuthConfig::$ClientSecret);
						$GLOBALS['gClient']->setRedirectUri(Auth::$redirectUri);
						$GLOBALS['gOAuth'] = new Google_Oauth2Service($GLOBALS['gClient']);
						$GLOBALS['gClient']->authenticate($_SESSION['authCode']);
						$_SESSION['authAccessToken'] = $GLOBALS['gClient']->getAccessToken();
					}
					
				}
				
				Auth::recreateGoogleClient();
				
				if(Auth::needsRefresh())
				{
					$GLOBALS['gClient']->refreshToken(json_decode($GLOBALS['gClient']->getAccessToken())->{'refresh_token'});
					$_SESSION['authAccessToken'] = $GLOBALS['gClient']->getAccessToken();
				}
				
				if(!$userFound)
				{
					$userInfo = Auth::getUserData();
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
					setcookie('preUser', $user->getHash(), time() + 60*60*24, '/', Auth::$domain, true);
				}
			}
			else
			{
				Auth::recreateGoogleClient();
				
				if(Auth::needsRefresh())
				{
					$GLOBALS['gClient']->refreshToken(json_decode($GLOBALS['gClient']->getAccessToken())->{'refresh_token'});
					$_SESSION['authAccessToken'] = $GLOBALS['gClient']->getAccessToken();
					$_SESSION['authUser']->setAuthToken($_SESSION['authAccessToken']);
					UserDataHelper::updateUser($_SESSION['authUser']);
				}
				
				if(!isset($_COOKIE['preUser']))
				{
					$canSet = setcookie('preUser', $_SESSION['authUser']->getHash(), time() + 60*60*24, '/', Auth::$domain, true);
					echo 'Cookie was sent: ' . $canSet;
				}
			}
			return true;
		}
		
		private static function recreateGoogleClient()
		{
			if(!isset($GLOBALS['gClient']))
			{			
				$GLOBALS['gClient'] = new Google_Client();
				$GLOBALS['gClient']->setClientId(AuthConfig::$ClientID);
				$GLOBALS['gClient']->setClientSecret(AuthConfig::$ClientSecret);
				$GLOBALS['gClient']->setRedirectUri(Auth::$redirectUri);
				$GLOBALS['gClient']->setAccessToken($_SESSION['authAccessToken']);
				$GLOBALS['gOAuth'] = new Google_Oauth2Service($GLOBALS['gClient']);
			}
		}
		
		private static function getUserData()
		{
			$service = $GLOBALS['gOAuth'];
			$info = $service->userinfo->get();
			return $info;
		}
		
		public static function createAuthCodeURI($redirectURI, $permissions, $fromUrl)
		{
			return 'https://accounts.google.com/o/oauth2/auth?client_id=' . 
			AuthConfig::$ClientID . 
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
			Auth::authenticate();
			$info = Auth::getUserData();
			return $info['email'];
		}
	}
?>