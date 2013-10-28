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
		
		public static function UserFromAuthToken($token)
		{
			
		}
	}

?>