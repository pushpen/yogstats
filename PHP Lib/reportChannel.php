<?php
	
	class ReportChannel
	{
		private $reportChannelID;
		private $reportID;
		private $trackedID;
		
		public function __construct($reportChannelID, $reportID, $trackedID)
		{
			$this->reportChannelID = $reportChannelID;
			$this->reportID = $reportID;
			$this->trackedID = $trackedID;
		}
		
		public static function CreateReportChannel($trackedID)
		{
			return new ReportChannel(-1, -1, $trackedID);
		}
		
		public function getReportChannelID()
		{
			return $this->reportChannelID;
		}
		
		public function getReportID()
		{
			return $this->reportID;
		}
		
		public function setReportID($reportID)
		{
			$this->reportID = $reportID;
		}
		
		public function getTrackedID()
		{
			return $this->trackedID;
		}
	}
	
	class ReportChannelDataHelper
	{
		private static $tableName = 'ReportChannel';
		private static $columnNames = array('ReportChannelID', 'ReportID', 'TrackedID');
		
		public static function putNewReportChannel($reportChannel)
		{
			global $sqlSource;
			$sqlSource->insert(ReportChannelDataHelper::$tableName, ReportChannelDataHelper::getNewAssocArray($reportChannel));
		}
		
		public static function getReportChannelByID($id)
		{
			global $sqlSource;
			$row = $sqlSource->query(array(ReportChannelDataHelper::$tableName), ReportChannelDataHelper::$columnNames, 'ReportChannelID = ' . $id);
			return ReportChannelDataHelper::getReportChannelFromRow($row);
		}
		
		public static function getReportChannels($reportID)
		{
			global $sqlSource;
			$channels = array();
			$row = $sqlSource->query(array(ReportChannelDataHelper::$tableName), ReportChannelDataHelper::$columnNames, 'ReportID = ' . $reportID);
			
			while($row != null)
			{
				$channels[] = ReportChannelDataHelper::getReportChannelFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $channels;
		}
		
		private static function getNewAssocArray($reportChannel)
		{
			$retArray = array();
			$retArray['ReportID'] = $reportChannel->getReportID();
			$retArray['TrackedID'] = $reportChannel->getTrackedID();
			
			return $retArray;
		}
		
		private static function getReportChannelFromRow($row)
		{
			if($row == null)
			{
				return null;
			}
			return new ReportChannel($row['ReportChannelID'], $row['ReportID'], $row['TrackedID']);
		}
	}
?>