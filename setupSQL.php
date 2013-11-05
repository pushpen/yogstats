<?php
	require_once 'PHP Lib/config.php';
	
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
	
	function createTableList($dbName, $tables)
	{
		foreach($tables as $key => $table)
		{
			$tables[$key] = $dbName . '.' . $table;
		}
		
		return $tables;
	}
	
	$database = SQLConfig::$DBName;
	$location = SQLConfig::$SqlLocation;
	$rootUser = 'root';
	$pass = '';
	$shouldDrop = false;
	$con = mysqli_connect($location, $rootUser, $pass);
	
	$userExists = mysqli_fetch_array(query('SELECT EXISTS(SELECT 1 FROM mysql.user WHERE user = "' . SQLConfig::$SqlUser . '")'));
	
	if(!$userExists[0])
	{
		query('CREATE USER "' . SQLConfig::$SqlUser . '"@"localhost" IDENTIFIED BY "' . SQLConfig::$SqlPass . '"');
		echo 'User ' . SQLConfig::$SqlUser . ' created <br>';
	}
	query('CREATE DATABASE IF NOT EXISTS ' . $database);
	echo 'Database ' . $database . ' created <br>';
	query('GRANT ALL ON ' . $database . '.* TO "' . SQLConfig::$SqlUser . '"@"localhost"');
	echo 'All permissions for ' . $database . ' given to ' . SQLConfig::$SqlUser . '<br>';
	
	if($shouldDrop)
	{
		$tableList = createTableList(SQLConfig::$DBName, array('ReportChannel', 'ReportPermission', 'ReportStatistic'));
		query('DROP TABLE IF EXISTS ' . implode(', ', $tableList));
		$tableList = createTableList(SQLConfig::$DBName, array('Report', 'Statistic', 'TrackedChannel'));
		query('DROP TABLE IF EXISTS ' . implode(', ', $tableList));
		$tableList = createTableList(SQLConfig::$DBName, array('User'));
		query('DROP TABLE IF EXISTS ' . implode(', ', $tableList));
		echo 'Dropped existing datatables <br>';
	}
	
	mysqli_close($con);
	$con = mysqli_connect($location, SQLConfig::$SqlUser, SQLConfig::$SqlPass, $database);
	echo 'Reconnected as ' . SQLConfig::$SqlUser . '<br>';
	
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
	query('CREATE TABLE IF NOT EXISTS Report (' . 
		  'ReportID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		  'CreatorID VARCHAR(32) NOT NULL,' .
		  'ReportName VARCHAR(64),' .
		  'Display TINYINT,' .
		  'HasSum BIT,' .
		  'FOREIGN KEY (CreatorID) REFERENCES User (GoogleID) ON UPDATE CASCADE ON DELETE NO ACTION' .
		  ')'
		 );
	echo 'Datatable Report created <br>';
	query('CREATE TABLE IF NOT EXISTS ReportPermission (' . 
		  'ReportPermissionID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		  'ReportID INT NOT NULL,' .
		  'GoogleID VARCHAR(32) NOT NULL,' .
		  'Permissions TINYINT,' .
		  'FOREIGN KEY (ReportID) REFERENCES Report(ReportID) ON UPDATE CASCADE ON DELETE CASCADE,'.
		  'FOREIGN KEY (GoogleID) REFERENCES User(GoogleID) ON UPDATE CASCADE ON DELETE NO ACTION' .
		  ')'
		 );
	echo 'Datatable ReportPermission created <br>';
	query('CREATE TABLE IF NOT EXISTS ReportChannel (' . 
		  'ReportChannelID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		  'ReportID INT NOT NULL,' .
		  'TrackedID INT NOT NULL,' .
		  'FOREIGN KEY (ReportID) REFERENCES Report(ReportID) ON UPDATE CASCADE ON DELETE CASCADE,' .
		  'FOREIGN KEY (TrackedID) REFERENCES TrackedChannel(TrackedID) ON UPDATE CASCADE ON DELETE CASCADE' .
		  ')'
		 );
	echo 'Datatable ReportChannel created <br>';
	query('CREATE TABLE IF NOT EXISTS ReportStatistic (' . 
		  'ReportStatisticID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,' .
		  'ReportID INT NOT NULL,' .
		  'StatisticID INT NOT NULL,' .
		  'FOREIGN KEY (ReportID) REFERENCES Report(ReportID) ON UPDATE CASCADE ON DELETE CASCADE,' .
		  'FOREIGN KEY (StatisticID) REFERENCES Statistic(StatisticID) ON UPDATE CASCADE ON DELETE CASCADE' .
		  ')'
		 );
	echo 'Datatable ReportStatistic created <br>';
	
	try
	{
		query('INSERT INTO Statistic (StatisticID,StatisticName,FieldName,FieldValue,ResponseLocation,APISource) VALUES (1,"Total Views","part","statistics","statistics.viewCount",0)');
	}
	catch(Exception $ex)
	{
		//echo $ex->getMessage() . '<br>';
	}
	try
	{
		query('INSERT INTO Statistic (StatisticID,StatisticName,FieldName,FieldValue,ResponseLocation,APISource) VALUES (2,"Total Subscribers","part","statistics","statistics.subscriberCount",0)');
	}
	catch(Exception $ex)
	{
		//echo $ex->getMessage() . '<br>';
	}
	echo 'Statistic types added <br>';
?>