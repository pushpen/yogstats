<?php
	require_once 'reportPermission.php';
	require_once 'reportChannel.php';
	require_once 'reportStatistic.php';
	require_once 'statistic.php';
	
	class Report
	{
		private $reportID;
		private $creatorID;
		private $display;
		private $hasSum;
		
		//Related Tables
		private $channels = null;
		private $statistics = null;
		
		public function __construct($reportID, $creatorID, $display, $hasSum)
		{
			$this->reportID = $reportID;
			$this->creatorID = $creatorID;
			$this->display = $display;
			$this->hasSum = $hasSum;
			
			/*$this->ownerIDs = array();
			$this->editIDs = array();
			$this->viewIDs = array();
			
			$this->channelIDs = array();
			
			$this->fields = array();*/
		}
		
		public static function CreateReport($creatorID, $display, $hasSum)
		{
			return new Report(-1, $creatorID, $display, $hasSum);
		}
		
		public function getReportID()
		{
			return $this->reportID;
		}
		
		public function getCreatorID()
		{
			return $this->creatorID:
		}
		
		public function getDisplay()
		{
			return $this->display;
		}
		
		public function getHasSum()
		{
			return $this->hasSum;
		}
	}
	
	abstract class Display
	{
		public static $Table = 0;
		public static $Graph = 1;
	}
	
	class ReportDataHelper
	{
		private static $tableName = 'Report';
		private static $columnNames = array('ReportID', 'CreatorID', 'Display', 'HasSum');
		
		private static function insertReport($report)
		{
			global $sqlSource;
			$sqlSource->insert(ReportDataHelper::$tableName, ReportDataHelper::getNewAssocArray($report));
			return $sqlSource->getLastID();
		}
		
		public static function putNewReport($report, $permissions, $channels, $statistics)
		{
			$reportID = ReportDataHelper::insertReport($report);
			if($reportID == 0)
			{
				throw new Exception('Report not created');
			}
			foreach($permissions as $permission)
			{
				$permission->setReportID($reportID);
				ReportPermissionDataHelper::putNewReportPermission($permission);
			}
			foreach($channels as $channel)
			{
				$channel->setReportID($reportID);
				ReportChannelDataHelper::putNewReportChannel($channel);
			}
			foreach($statistics as $statistic)
			{
				$statistic->setReportID($reportID);
				ReportStatisticDataHelper::putNewReportStatistic($statistic);
			}
		}
		
		public static function getReportByID($id)
		{
			global $sqlSource;
			$row = $sqlSource->query(array(ReportDataHelper::$tableName), ReportDataHelper::$columnNames, 'ReportID = ' . $id);
			return ReportDataHelper::getReportFromRow($row);
		}
		
		public function getReports()
		{
			global $sqlSource;
			$reports = array();
			$row = $sqlSource->query(array(ReportDataHelper::$tableName), ReportDataHelper::$columnNames, '1=1');
			
			while($row != null)
			{
				$reports[] = ReportDataHelper::getReportFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $reports;
		}
		
		public static function getVisibleReports($user)
		{
			if($user->canView())
			{
				return ReportDataHelper::getReports();
			}
			else
			{
				global $sqlSource;
				$reports = array();
				$rTbl = ReportDataHelper::$tableName;
				$rPTbl = ReportPermissionDataHelper::getTableName();
				$row = $sqlSource->query(array($rTbl, $rPTbl), ReportDataHelper::$columnNames, $rTbl . '.ReportID = ' . $rPTbl . '.ReportID AND (' . $rPTbl . '.GoogleID = ' . $user->getGoogleID() . ' OR ' . $rTbl . '.CreatorID = ' . $user->getGoogleID() .  ') AND ' . $rPTbl . '.Permissions & 1 = 1'); 
				
				while($row != null)
				{
					$reports[] = ReportDataHelper::getReportFromRow($row);
					$row = $sqlSource->nextResult();
				}
				
				return $row;
			}
		}
		
		private static function getNewAssocArray($report)
		{
			$retArray = array();
			$retArray['CreatorID'] = $report->getCreatorID();
			$retArray['Display'] = $report->getDisplay();
			$retArray['HasSum'] = $report->getHasSum();
			
			return $retArray;
		}
		
		private static function getReportFromRow($row)
		{
			if($row == null)
			{
				return null;
			}
			return new Report($row['ReportID'], $row['CreatorID'], $row['Display'], $row['HasSum']);
		}
	}
?>