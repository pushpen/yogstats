<?php
	
	class ReportPermission
	{
		private $reportPermissionID;
		private $reportID;
		private $googleID;
		private $permissions;
		
		public function __construct($reportPermissionID, $reportID, $googleID, $permissions)
		{
			$this->reportPermissionID = $reportPermissionID;
			$this->reportID = $reportID;
			$this->googleID = $googleID;
			$this->permissions = $permissions;
		}
		
		public static function CreatePermission($googleID, $permissions)
		{
			return new ReportPermission(-1, -1, $googleID, $permissions);
		}
		
		public function getReportPermissionID()
		{
			return $this->reportPermissionID;
		}
		
		public function getReportID()
		{
			return $this->reportID;
		}
		
		public function setReportID($reportID)
		{
			$this->reportID = $reportID;
		}
		
		public function getGoogleID()
		{
			return $this->googleID;
		}
		
		public function getPermissions()
		{
			return $this->permissions;
		}
	}
	
	abstract class Permission
	{
		public static $View = 1;
		public static $Edit = 3;
		public static $Owner = 7;
	}
	
	class ReportPermissionDataHelper
	{
		private static $tableName = 'ReportPermission';
		private static $columnNames = array('ReportPermissionID', 'ReportID', 'GoogleID', 'Permissions');
		
		public static function putNewReportPermission($reportPermission)
		{
			global $sqlSource;
			$sqlSource->insert(ReportPermissionDataHelper::$tableName, ReportPermissionDataHelper::getNewAssocArray($reportPermission));
		}
		
		public static function getReportPermissionByID($id)
		{
			global $sqlSource;
			$row = $sqlSource->query(array(ReportPermissionDataHelper::$tableName), ReportPermissionDataHelper::$columnNames, 'ReportPermissionID = ' . $id);
			return ReportPermissionDataHelper::getReportPermissionFromRow($row);
		}
		
		public static function getReportPermissionForUser($reportID, $googleID)
		{
			global $sqlSource;
			$permissions = array();
			$row = $sqlSource->query(array(ReportPermissionDataHelper::$tableName), ReportPermissionDataHelper::$columnNames, 'ReportID = ' . $reportID . ' AND GoogleID = ' . SQLDataSource::sqlValFormat($googleID));
			
			while($row != null)
			{
				$permissions[] = ReportPermissionDataHelper::getReportPermissionFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $permissions;
		}
		
		public static function getReportPermissions($reportID)
		{
			global $sqlSource;
			$permissions = array();
			$row = $sqlSource->query(array(ReportPermissionDataHelper::$tableName), ReportPermissionDataHelper::$columnNames, 'ReportID = ' . $reportID);
			
			while($row != null)
			{
				$permissions[] = ReportPermissionDataHelper::getReportPermissionFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $permissions;
		}
		
		public static function getTableName()
		{
			return ReportPermissionDataHelper::$tableName;
		}
		
		private static function getNewAssocArray($reportPermission)
		{
			$retArray = array();
			$retArray['ReportID'] = $reportPermission->getReportID();
			$retArray['GoogleID'] = $reportPermission->getGoogleID();
			$retArray['Permissions'] = $reportPermission->getPermissions();
			
			return $retArray;
		}
		
		private static function getReportPermissionFromRow($row)
		{
			if($row == null)
			{
				return null;
			}
			return new ReportPermission($row['ReportPermissionID'], $row['ReportID'], $row['GoogleID'], $row['Permissions']);
		}
	}
?>