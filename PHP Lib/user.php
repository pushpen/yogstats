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

?>