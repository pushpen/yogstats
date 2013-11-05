<?php
	
	class User
	{
		private $GoogleID;
		private $Email;
		private $Hash;
		private $AuthToken;
		private $Permissions;
		
		public function __construct($id, $email, $hash, $token, $permissionLevel)
		{
			$this->GoogleID = $id;
			if(strlen($this->GoogleID) > 32)
			{
				throw new Exception('Google ID cannot be > 32 chars, actual length ' . strlen($this->GoogleID));
			}
			$this->Email = $email;
			$this->Hash = $hash;
			if(strlen($hash) != 64)
			{
				throw new Exception('Hash must be 64 chars');
			}
			$this->AuthToken = $token;
			$this->Permissions = $permissionLevel;
		}
		
		public static function CreateFromAuthToken($token, $email, $id, $permissionLevel)
		{
			$salt = md5(rand());
			return new User($id, $email, hash('SHA256', $salt . $id), $token, $permissionLevel);
		}
		
		public function canView()
		{
			return $this->Permissions & 1 == 1;
		}
		
		public function canEdit()
		{
			return $this->Permissions & 2 == 2;
		}
		
		public function isOwner()
		{
			return $this->Permissions & 4 == 4;
		}
		
		public function getGoogleID()
		{
			return $this->GoogleID;
		}
		
		public function getEmail()
		{
			return $this->Email;
		}
		
		public function getHash()
		{
			return $this->Hash;
		}
		
		public function getAuthToken()
		{
			return $this->AuthToken;
		}
		
		public function setAuthToken($token)
		{
			$this->AuthToken = $token;
		}
		
		public function getPermissions()
		{
			return $this->Permissions;
		}
	}

	class UserDataHelper
	{
		private static $tableName = 'User';
		private static $columnNames = array('GoogleID', 'Email', 'Hash', 'AuthToken', 'Permissions');
		
		public static function putNewUser($user)
		{
			global $sqlSource;
			$sqlSource->open();
			$sqlSource->insert(UserDataHelper::$tableName, UserDataHelper::getAssocArray($user));
		}
		public static function getUserByID($userID)
		{
			global $sqlSource;
			$sqlSource->open();
			$result = $sqlSource->query(array(UserDataHelper::$tableName), UserDataHelper::$columnNames, 'GoogleID = ' . SQLDataSource::sqlValFormat($userID));
			return UserDataHelper::getUserFromRow($result);
		}
		public static function getUserByHash($hash)
		{
			global $sqlSource;
			$sqlSource->open();
			$result = $sqlSource->query(array(UserDataHelper::$tableName), UserDataHelper::$columnNames, 'Hash = ' . SQLDataSource::sqlValFormat($hash));
			return UserDataHelper::getUserFromRow($result);
		}
		
		public static function getUsers()
		{
			global $sqlSource;
			$users = array();
			$row = $sqlSource->query(array(UserDataHelper::$tableName), UserDataHelper::$columnNames, '1=1');
			
			while($row != null)
			{
				$users[] = UserDataHelper::getUserFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $users;
		}
		
		public static function getOtherUsers($user)
		{
			global $sqlSource;
			$users = array();
			$row = $sqlSource->query(array(UserDataHelper::$tableName), UserDataHelper::$columnNames, 'GoogleID != ' . SQLDataSource::sqlValFormat($user->getGoogleID()));
			
			while($row != null)
			{
				$users[] = UserDataHelper::getUserFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $users;
		}
		
		public static function updateUser($user)
		{
			global $sqlSource;
			$sqlSource->open();
			$sqlSource->update(UserDataHelper::$tableName, UserDataHelper::getAssocArray($user), 'GoogleID = ' . SQLDataSource::sqlValFormat($user->getGoogleID()));
		}
		
		private static function getUserFromRow($row)
		{
			if($row == null)
			{
				return null;
			}
			$user = new User($row['GoogleID'], $row['Email'], $row['Hash'], $row['AuthToken'], $row['Permissions']);
			return $user;
		}
		
		private static function getAssocArray($user)
		{
			$retArray = array();
			$retArray['GoogleID'] = $user->getGoogleID();
			$retArray['Email'] = $user->getEmail();
			$retArray['Hash'] = $user->getHash();
			$retArray['AuthToken'] = $user->getAuthToken();
			$retArray['Permissions'] = $user->getPermissions();
			
			return $retArray;
		}
	}
?>