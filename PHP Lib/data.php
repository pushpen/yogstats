<?php
	include_once 'user.php';
	
	$userSource = new SQLDataSource(DataSource::$sqlLocation, DataSource::$sqlUser, DataSource::$sqlPass, 'User');
	
	class UserDataHelper
	{
		private static $tableName = 'User';
		private static $columnNames = array('GoogleID', 'Email', 'Hash', 'AuthToken');
		
		public static function putNewUser($user)
		{
			global $userSource;
			$userSource->open();
			$userSource->insert(UserDataHelper::$tableName, UserDataHelper::getAssocArray($user));
		}
		public static function getUserByID($userID)
		{
			global $userSource;
			$userSource->open();
			$result = $userSource->query(UserDataHelper::$tableName, UserDataHelper::$columnNames, 'GoogleID = ' . $userID);
			return getUserFromRow($result);
		}
		public static function getUserByHash($hash)
		{
			global $userSource;
			$userSource->open();
			$result = $userSource->query(UserDataHelper::$tableName, UserDataHelper::$columnNames, 'Hash = ' . $hash);
			return getUserFromRow($result);
		}
		public static function updateUser($user)
		{
			global $userSource;
			$userSource->open();
			$userSource->update(UserDataHelper::$tableName, UserDataHelper::getAssocArray($user), 'GoogleID = ' . $user->getGoogleID());
		}
		
		private static function getUserFromRow($row)
		{
			if($row == null)
			{
				return null;
			}
			
			$user = new User($row['GoogleID'], $row['Email'], $row['Hash'], $row['AuthToken']);
			return $user;
		}
		
		private static function getAssocArray($user)
		{
			$retArray = array();
			$retArray['GoogleID'] = $user->getGoogleID();
			$retArray['Email'] = $user->getEmail();
			$retArray['Hash'] = $user->getHash();
			$retArray['AuthToken'] = $user->getAuthToken();
			
			return $retArray;
		}
	}
	
	require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
	
	//Interface
	class DataSource
	{
		public static $sqlLocation = 'localhost';
		public static $sqlUser = '';
		public static $sqlPass = '';
		protected $location;
		
		public function __construct($dataLocation)
		{
			$this->location = $dataLocation;
		}
		
		public function open(){}
		public function close(){}
		//Returns the first row from the query
		public function query($sources, $fields, $conditions){}
		public function update($destination, $fields, $conditions){}
		public function insert($destination, $fields) {}
		//Returns the next row from the last query or NULL if none is available
		public function nextResult(){}
	}
	
	class YoutubeDataAPIDataSource extends DataSource
	{
		private $authToken;
		private $googleClient;
		private $youtubeDataAPI;
		private $channelList;
		private $lastResult;
		private $lastFields;
		private $itemPointer = 0;
		
		public function __construct($authenticationToken, $googleAPIClient)
		{
			parent::__construct(null);
			$this->authToken = $authenticationToken;
			$this->googleClient = $googleAPIClient;
			$this->youtubeDataAPI = new Google_YouTubeService($this->googleClient);
			$this->channelList = $youtubeDataAPI->channels;
		}
		
		//sources should be an empty array or the singleton 'channels'
		//fields should be an associative array between the parameters of the channel list call and their values
		//conditions should be the empty string
		public function query($sources, $fields, $conditions)
		{
			if(sizeof($sources) > 1 || (sizeof($sources) == 1 && $sources[0] != 'channels')) 
			{
				throw new Exception('Invalid source, only a single source of type channel is currently supported');
			}
			if($conditions != '')
			{
				throw new Exception('Conditions not currently supported');
			}
			
			$this->itemPointer = 0;
			$this->lastResult = json_decode($this->channelList->listChannels($fields['part'],$fields));
			$this->lastFields = $fields;
			
			if($this->lastResult->{'pageInfo'}->{'totalResults'} == 0)
			{
				return null;
			}
			
			$this->itemPointer++;
			return $this->lastResult->{'items'}[0];
		}
		
		public function nextResult()
		{
			if($this->itemPointer >= $this->lastResult->{'pageInfo'}->{'totalResults'})
			{
				if(isset($this->lastResult->{'nextPageToken'}))
				{
					$this->lastFields['pageToken'] = $this->lastResult->{'nextPageToken'};
					return query(array('channels'), $this->lastFields, '');
				}
				else
				{
					return null;
				}
			}
			
			$retRow = $this->lastResult->{'items'}[$this->itemPointer];
			$this->itemPointer++;
			return $retRow;
		}
		
		public function update($destination, $fields, $condition)
		{
			return new Exception('This data is read only');
		}
		
		public function insert($destination, $fields)
		{
			return new Exception('This data is read only');
		}
	}
	
	class SQLDataSource extends DataSource
	{
		private $user;
		private $pass;
		private $dbname;
		private $con;
		private $result;
		private $connected;
		
		//only called by helper function
		public function __construct($dataLocation, $username, $password, $databaseName)
		{
			parent::__construct($dataLocation);
			$this->user = $username;
			$this->pass = $password;
			$this->dbname = $databaseName;
			$this->connected = false;
		}
		
		public function open()
		{
			if($this->connected)
			{
				return true;
			}
			
			$this->con = mysqli_connect($this->location, $this->user, $this->pass, $this->dbname);
			if(mysqli_connect_errno($this->con))
			{
				throw new Exception(mysqli_connect_error());
			}
			
			$this->connected = true;
			return true;
		}
		
		public function close()
		{
			mysqli_close($this->con);
			$this->connected = false;
		}
		
		private function sqlImplode($seperator, $array)
		{
			$first = true;
			$retStr = '';
			
			foreach($kvArray as $value)
			{
				if(!$first)
				{
					$retStr = $retStr . $seperator;
				}
				else
				{
					$first = false;
				}
				
				$retStr = $retStr . sqlVarFormat($value);
			}
		}
		
		private function sqlKeyImplode($seperator, $kvArray)
		{
			$first = true;
			$retStr = '';
			
			foreach($kvArray as $key => $value)
			{
				if(!$first)
				{
					$retStr = $retStr . $seperator;
				}
				else
				{
					$first = false;
				}
				
				$retStr = $retStr . $key;
			}
		}
		
		private function sqlKeyValueImplode($seperator, $kvSeperator, $kvArray)
		{
			$first = true;
			$retStr = '';
			
			foreach($kvArray as $key => $value)
			{
				if(!$first)
				{
					$retStr = $retStr . $seperator;
				}
				else
				{
					$first = false;
				}
				
				$retStr = $retStr . $key . $kvSeperator . sqlValFormat($value);
			}
		}
		
		private function sqlValFormat($var)
		{
			if(is_string($var))
			{
				return '"' . $var . '"';
			}
			return $var;
		}
		
		//sources should be an array of tables to query
		//fields should be an array of field names to include in the response
		//conditions should be a string containing the SQL WHERE parameters
		public function query($sources, $fields, $conditions)
		{
			$this->result = mysqli_query($this->con, 'SELECT ' . implode(',',$fields) .
								' FROM ' . implode(',', $sources) . 
								' WHERE ' . $conditions);
			return mysqli_fetch_array($result);
		}
		
		//Returns the next row of the last query
		public function nextResult()
		{
			return mysqli_fetch_array($this->result);
		}
		
		//destination is a string containing the table name to update
		//fields is an associative array of field names to values
		//conditions is a string containing the SQL WHERE parameters
		public function update($destination, $fields, $conditions)
		{
			$mysqli_query($this->con, 'UPDATE ' . $destination .
								' SET ' . sqlKeyValueImplode(', ', ' = ', $fields) .
								' WHERE ' . $conditions);
		}
		
		public function insert($destination, $fields)
		{
			$mysqli_query($this->con, 'INSERT INTO ' . $destination . ' (' . sqlKeyImplode(', ', $fields) 
								. ') VALUES (' . sqlImplode(', ', $fields) . ')');
		}
	}
?>