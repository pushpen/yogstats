<?php
	function query($query)
	{
		global $con;
		$res = mysqli_query($con, $query); 
		if(mysqli_errno($con)) 
		{
			throw new Exception('SQL Error: ' . mysqli_error($con));
		}
		return $res;
	}
	
	$database = 'YogstatsTest';
	$location = 'localhost';
	$rootUser = 'root';
	$pass = '';
	$con = mysqli_connect($location, $rootUser, $pass);
	
	$userExists = mysqli_fetch_array(query('SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = "yogAdmin")'));
	
	if(!$userExists[0])
	{
		query('CREATE USER "yogAdmin"@"localhost" IDENTIFIED BY "EuRMd8EWDLAB"');
		echo 'User yogAdmin created <br>';
	}
	query('CREATE DATABASE IF NOT EXISTS ' . $database);
	echo 'Database ' . $database . ' created <br>';
	query('GRANT ALL ON ' . $database . '.* TO "yogAdmin"@"localhost"');
	echo 'All permissions for ' . $database . ' given to yogAdmin <br>';
	mysqli_close($con);
	$con = mysqli_connect($location, 'yogAdmin', 'EuRMd8EWDLAB', $database);
	echo 'Reconnected as yogAdmin <br>';
	
	query('CREATE TABLE IF NOT EXISTS User (' . 
		  'GoogleID VARCHAR(32) NOT NULL PRIMARY KEY,' .
		  'Email VARCHAR(32) NOT NULL,' .
		  'Hash CHAR(64) NOT NULL UNIQUE,' .
		  'AuthToken TEXT,' .
		  'Permissions TINYINT' .
		  ')'
		 );
	echo 'Datatable User created <br>';
	
	query('CREATE TABLE IF NOT EXISTS TrackedChannel (' . 
		  'TrackedID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		  'ChannelID VARCHAR(32) NOT NULL UNIQUE,' .
		  'ChannelName VARCHAR(64),' .
		  'AuthToken TEXT' .
		  ')'
		 );
	echo 'Datatable TrackedChannel created <br>';
	
	query('CREATE TABLE IF NOT EXISTS Statistic (' . 
		  'StatisticID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		  'StatisticName VARCHAR(64),' .
		  'FieldName VARCHAR(64),' .
		  'FieldValue VARCHAR(64),' .
		  'ResponseLocation VARCHAR(64),' .
		  'APISource TINYINT' .
		  ')'
		 );
	echo 'Datatable Statistic created <br>';
?>