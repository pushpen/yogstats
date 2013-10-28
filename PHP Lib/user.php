<?php
	
	class User
	{
		private $GoogleID;
		private $Email;
		private $Hash;
		private $AuthToken;
		
		public function __construct($id, $email, $hash, $token)
		{
			$GoogleID = $id;
			$Email = $email;
			$Hash = $hash;
			$AuthToken = $token;
		}
		
		public static function CreateFromAuthToken($token, $email, $id)
		{
			$salt = md5(rand());
			return new User($id, $email, hash('SHA256', $salt . $GoogleID), $token);
		}
		
		public function getGoogleID()
		{
			return $GoogleID;
		}
		
		public function getEmail()
		{
			return $Email;
		}
		
		public function getHash()
		{
			return $Hash;
		}
		
		public function getAuthToken()
		{
			return $AuthToken;
		}
		
		public function setAuthToken($token)
		{
			$AuthToken = $token;
		}
	}

?>