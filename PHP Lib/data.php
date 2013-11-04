<?php
	require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';
	
	//Interface
	abstract class DataSource
	{
		public static $sqlLocation = 'localhost';
		public static $sqlUser = 'yogAdmin';
		public static $sqlPass = 'EuRMd8EWDLAB';
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
		public function sanitise($string){}
	}
	
	$ytDataSource = new YoutubeDataAPIDataSource();
	
	class YoutubeDataAPIDataSource extends DataSource
	{
		private $lastResult;
		private $lastFields;
		private $itemPointer = 0;
		private static $apiKey = 'AIzaSyDwWbRNMAvyEzc7kAEn40YEPtbjM_aXVOk';
		
		public function __construct()
		{
			parent::__construct(null);
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
			$this->lastResult = $this->listChannels($fields);
			$this->lastFields = $fields;
			
			if($this->lastResult->{'pageInfo'}->{'totalResults'} == 0)
			{
				return null;
			}
			
			$this->itemPointer++;
			return $this->lastResult->{'items'}[0];
		}
		
		private function listChannels($fields)
		{
			$url =  'https://www.googleapis.com/youtube/v3/channels';
			$seperator = '?';
			
			$fields['key'] = YoutubeDataAPIDataSource::$apiKey;
			
			foreach($fields as $key => $value)
			{
				$url .= $seperator;
				$url .= $key . '=' . urlencode($value);
				$seperator = '&';
			}
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			$response = curl_exec($ch);
			
			if(curl_errno($ch)) 
			{
				throw new Exception('Youtube Data API threw exception: ' . curl_error($ch));
			}
			curl_close($ch);
			
			$response = json_decode($response);
			if(isset($response->{'error'}))
			{
				throw new Exception ('Youtube Data API threw exception: ' . $response->{'error'}->{'message'});
			}
			return $response;
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
		
		public function sanitise($string)
		{
			return $string;
		}
	}
	
	$sqlSource = new SQLDataSource(DataSource::$sqlLocation, DataSource::$sqlUser, DataSource::$sqlPass, 'Yogstats');
	$sqlSource->open();
	
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
		
		public static function sqlImplode($seperator, $array)
		{
			$first = true;
			$retStr = '';
			
			foreach($array as $value)
			{
				if(!$first)
				{
					$retStr = $retStr . $seperator;
				}
				else
				{
					$first = false;
				}
				
				$retStr = $retStr . SQLDataSource::sqlValFormat($value);
			}
			
			return $retStr;
		}
		
		public static function sqlKeyImplode($seperator, $kvArray)
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
			
			return $retStr;
		}
		
		public static function sqlKeyValueImplode($seperator, $kvSeperator, $kvArray)
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
				
				$retStr = $retStr . $key . $kvSeperator . SQLDataSource::sqlValFormat($value);
			}
			
			return $retStr;
		}
		
		public static function sqlValFormat($var)
		{
			if(is_string($var))
			{
				$var = str_replace("'", '\'', $var);
				$var = str_replace('"', '\"', $var);
				$var = str_replace("\b", '\b', $var);
				$var = str_replace("\n", '\n', $var);
				$var = str_replace("\r", '\r', $var);
				$var = str_replace("\t", '\t', $var);
				$var = str_replace('%', '\%', $var);
				$var = str_replace('_', '\_', $var);
				return '"' . $var . '"';
			}
			if(is_float($var))
			{
				$var = number_format($var,0,'.','');
			}
			if($var === null)
			{
				$var = 'NULL';
			}
			return $var;
		}
		
		//sources should be an array of tables to query
		//fields should be an array of field names to include in the response
		//conditions should be a string containing the SQL WHERE parameters
		public function query($sources, $fields, $conditions)
		{
			$query = 'SELECT ' . implode(',',$fields) .
								' FROM ' . implode(',', $sources) . 
								' WHERE ' . $conditions;
			//echo $query . '<br>';
			$this->result = mysqli_query($this->con, $query);
			if(!$this->result) throw new Exception('Query threw exception: ' . mysqli_error($this->con));
			return mysqli_fetch_array($this->result);
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
			$query = 'UPDATE ' . $destination .
								' SET ' . SQLDataSource::sqlKeyValueImplode(', ', ' = ', $fields) .
								' WHERE ' . $conditions;
			//echo $query . '<br>';
			$qResult = mysqli_query($this->con, $query);
			if(!$qResult) throw new Exception('Update threw exception: ' . mysqli_error($this->con));
		}
		
		public function insert($destination, $fields)
		{
			$query = 'INSERT INTO ' . $destination . ' (' . SQLDataSource::sqlKeyImplode(', ', $fields) 
								. ') VALUES (' . SQLDataSource::sqlImplode(', ', $fields) . ')';
			//echo $query . '<br>';
			$qResult = mysqli_query($this->con, $query);
			if(!$qResult) throw new Exception('Insert threw exception: ' . mysqli_error($this->con));
		}
		
		public function sanitise($string)
		{
			return mysqli_real_escape_string($this->con, $string);
		}
		
		public function getLastID()
		{
			return mysqli_insert_id($this->con);
		}
	}
?>