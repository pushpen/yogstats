<?php
	
	class UserDataHelper
	{
		public static function putNewUser($user)
		{
			
		}
		public static function getUserByID($userID)
		{
			
		}
		public static function getUserByHash($userIDHash)
		{
			
		}
	}
	
	include_once '..\google-api-php-client\src\contrib\Google_YouTubeService.php';
	
	//Interface
	class DataSource
	{
		protected $location;
		
		public function __construct($dataLocation)
		{
			$location = $dataLocation;
		}
		
		public function open(){};
		public function close(){};
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
			$authToken = $authenticationToken;
			$googleClient = $googleAPIClient;
			$youtubeDataAPI = new Google_YouTubeService($googleClient);
			$channelList = $youtubeDataAPI->channels;
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
			
			itemPointer = 0;
			$lastResult = json_decode($channelList->listChannels($fields['part'],$fields));
			$lastFields = $fields;
			
			if($lastResult->['pageInfo']->['totalResults'] == 0) return null;
			
			itemPointer++;
			return $lastResult->['items'][0];
		}
		
		public function nextResult()
		{
			if($itemPointer >= $lastResult->['pageInfo']->['totalResults'])
			{
				if(isset($lastResult->['nextPageToken']))
				{
					$lastFields['pageToken'] = $lastResult->['nextPageToken'];
					return query(array('channels'), $lastFields, '');
				}
				else
				{
					return null;
				}
			}
			
			$retRow = $lastResult->['items'][$itemPointer];
			$itemPointer++;
			return $retRow;
		}
		
		public function update($destination, $fields, $condition)
		{
			return new Exception('This data is read only');
		}
		
		public function insert()
	}
	
	class SQLDataSource extends DataSource
	{
		private $user;
		private $pass;
		private $dbname;
		private $con;
		private $result;
		
		//only called by helper function
		public function __construct($dataLocation, $username, $password, $databaseName)
		{
			parent::__construct($dataLocation);
			$user = $username;
			$pass = $password;
			$dbname = $databaseName;
		}
		
		public function open()
		{
			$con = sqli_connect($location, $user, $pass, $dbname);
			if(mysqli_connect_errno($con))
			{
				throw new Exception(mysqli_connect_error());
			}
			return true;
		}
		
		public function close()
		{
			mysqli_close($con);
		}
		
		private function keyImplode(
		
		private function keyValueImplode($seperator, $kvSeperator, $kvArray)
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
				
				$retStr = $retStr . $key . $kvSeperator . $value;
			}
		}
		
		//sources should be an array of tables to query
		//fields should be an array of field names to include in the response
		//conditions should be a string containing the SQL WHERE parameters
		public function query($sources, $fields, $conditions)
		{
			$result = mysqli_query($con, 'SELECT ' . implode(',',$fields) .
								' FROM ' . implode(',', $sources) . 
								' WHERE ' . $conditions);
			mysqli_fetch_array($result);
		}
		
		//Returns the next row of the last query
		public function nextResult()
		{
			return mysqli_fetch_row($result);
		}
		
		//destination is a string containing the table name to update
		//fields is an associative array of field names to values
		//conditions is a string containing the SQL WHERE parameters
		public function update($destination, $fields, $conditions)
		{
			$mysqli_query($con, 'UPDATE ' . $destination .
								' SET ' . keyValueImplode(', ', ' = ', $fields) .
								' WHERE ' . $conditions;
		}
	}
?>