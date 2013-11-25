<?php
	require_once __DIR__ . '/../google-api-php-client/src/Google_Client.php';
	require_once __DIR__ . '/../google-api-php-client/src/contrib/Google_Oauth2Service.php';
	require_once __DIR__ . '/config.php';
	require_once __DIR__ . '/data.php';
	require_once __DIR__ . '/user.php';
	require_once __DIR__ . '/trackedChannel.php';
	require_once __DIR__ . '/report.php';
	
	abstract class SanitiseType
	{
		public static $String = 0;
		public static $Int = 1;
		public static $Float = 2;
	}
	
	class Util
	{
		private static $SQLKeywords = array(
		'ACCESSIBLE','ACTION','ADD','ALL','ALTER','ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 
		'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BIT', 'BLOB', 'BOTH', 'BY', 
		'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE','CROSS','CURRENT_DATA','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR',
		'DATABASE', 'DATABASES', 'DATE', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE','DEFAULT','DELAYED','DELETE','DESC','DESCRIBE','DETERMINISTIC','DISTINCT','DISTINCTROW', 'DIV', 'DOUBLE','DROP','DUAL',
		'EACH', 'ELSE', 'ELSEIF', 'ENCLOSED', 'ENUM', 'ESCAPED', 'EXISTS', 'EXIT', 'EXPLAIN',
		'FALSE', 'FETCH', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 
		'GRANT', 'GROUP', 'GENERAL',
		'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND','HOUR_MINUTE', 'HOUR_SECOND',
		'IF', 'IGNORE', 'IGNORE_SERVER_IDS', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE','INSERT','INT','INT1','INT2','INT3','INT4','INT8','INTEGER','INTERVAL','INTO','IS','ITERATE',
		'JOIN',
		'KEY', 'KEYS', 'KILL',
		'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP','LOW_PRIORITY', 
		'MASTER_SSL_VERIFY_SERVER_CERT', 'MASTER_HEARTBEAT_PERIOD','MATCH' ,'MAXVALUE', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES',
		'NATURAL', 'NO', 'NOT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMERIC',
		'ON', 'OPTIMIZE', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 
		'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE',
		'RANGE', 'READ', 'READS', 'READ_WRITE', 'REAL', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME' ,'REPEAT', 'REPLACE', 'REQUIRE', 'RESIGNAL', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE',
		'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SIGNAL', 'SLOW', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SSL', 'STARTING', 'STRAIGHT_JOIN',
		'TABLE', 'TERMINATED', 'TEXT', 'THEN', 'TIME', 'TIMESTAMP', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE',
		'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 
		'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING',
		'WHEN', 'WHERE', 'WHILE', 'WITH', 'WRITE',
		'XOR',
		'YEAR_MONTH',
		'ZEROFILL'
		);
		
		public static function getUser()
		{
			if(!Auth::hasUser())
			{
				return null;
			}
			return $_SESSION['authUser'];
		}
		
		public static function ValidateNumber($value, $min = null, $max = PHP_INT_MAX)
		{
			if($min == null)
			{
				$min = PHP_INT_MAX + 1;
			}
			if(!is_numeric($value)) 
			{
				return false;
			}
			if($value < $min)
			{
				return -1;
			}
			if($value > $max)
			{
				return 1;
			}
			return 0;
		}
		
		public static function GetValidateErrorMessage($err, $min = Util::MIN, $max = PHP_INT_MAX)
		{
			if($err === false)
			{
				return 'Please enter a valid number';
			}
			if($err < 0)
			{
				return 'Please enter a number greater than or equal to ' . $min;
			}
			if($err > 0)
			{
				return 'Please enter a number less than or equal to ' . $max;
			}
			return '';
		}
		
		public static function SQLSanitise($value, $type)
		{
			global $sqlDataSource;
			switch($type) 
			{
				//String
				case 0:
					return $sqlDataSource->sanitise($value);
				//Integer
				case 1:
					return intval($value);
				case 2:
					return floatval($value);
				default:
					throw new Exception('Unknown type ' . $type);
			}
		}
		
		private static function SQLStrip($value)
		{
			$isString = true;
			$isBracket = 0;
			
			$currentWord = '';
			$returnString = '';
			
			for($i = 0; $i < strlen($value); $i++)
			{
				
			}
		}
		
		public static function FormatNumber($value, $decimals)
		{
			if(!is_numeric($value) || $value <= 9999) return $value;
			else return number_format($value, $decimals, '.', ',');
		}
	}
	
	class Auth
	{
		
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
			if(AuthConfig::$UseSSL && (!isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS']))
			{
				header('Location: ' . AuthConfig::$ServerRoot . $_SERVER['REQUEST_URI']);
				exit;
			}
			
			Auth::startSession();
			
			if(!Auth::hasUser())
			{
				//echo 'No User';
				//Have a cookie, lookup existing user data
				$userFound = false;
				if(isset($_COOKIE['preUser']))
				{
					global $sqlSource;
					$user = UserDataHelper::getUserByHash($sqlSource->sanitise($_COOKIE['preUser']));
					
					if($user != null)
					{
						$userFound = true;
						$_SESSION['authAccessToken'] = $user->getAuthToken();
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
							header('Location: ' . Auth::createAuthCodeURI(AuthConfig::$RedirectUri, implode(' ', $permissions), AuthConfig::$ServerRoot . $_SERVER['REQUEST_URI']));
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
						$GLOBALS['gClient']->setRedirectUri(AuthConfig::$RedirectUri);
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
					setcookie('preUser', $user->getHash(), time() + 60*60*24, '/', AuthConfig::$Domain, true);
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
					$canSet = setcookie('preUser', $_SESSION['authUser']->getHash(), time() + 60*60*24, '/', AuthConfig::$Domain, true);
					//echo 'Cookie was sent: ' . $canSet;
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
				$GLOBALS['gClient']->setRedirectUri(AuthConfig::$RedirectUri);
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