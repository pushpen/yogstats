<?php
	
	class User
	{
		private $GoogleID;
		private $Email;
		private $Hash;
		private $AuthToken;
		
		public function __construct($id, $email, $hash, $token)
		{
			$this->GoogleID = $id;
			$this->Email = $email;
			$this->Hash = $hash;
			$this->AuthToken = $token;
		}
		
		public static function CreateFromAuthToken($token, $email, $id)
		{
			$salt = md5(rand());
			return new User($id, $email, hash('SHA256', $salt . $GoogleID), $token);
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
	}

?>