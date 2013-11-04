<?php
	
	class ReportStatistic
	{
		private $reportStatisticID;
		private $reportID;
		private $statisticID;
		
		public function __construct($reportStatisticID, $reportID, $statisticID)
		{
			$this->reportStatisticID = $reportStatisticID;
			$this->reportID = $reportID;
			$this->statisticID = $statisticID;
		}
		
		public static function CreateReportStatistic($statisticID)
		{
			return new ReportStatistic(-1, -1, $statisticID);
		}
		
		public function getReportStatisticID()
		{
			return $this->reportStatisticID;
		}
		
		public function getReportID()
		{
			return $this->reportID;
		}
		
		public function setReportID($reportID)
		{
			$this->reportID = $reportID;
		}
		
		public function getStatisticID()
		{
			return $this->statisticID;
		}
	}
	
	class ReportStatisticDataHelper
	{
		private static $tableName = 'ReportStatistic';
		private static $columnNames = array('ReportStatisticID', 'ReportID', 'StatisticID');
		
		public static function putNewReportStatistic($reportStatistic)
		{
			global $sqlSource;
			$sqlSource->insert(ReportStatisticDataHelper::$tableName, ReportStatisticDataHelper::getNewAssocArray($reportStatistic));
		}
		
		public static function getReportStatisticByID($id)
		{
			global $sqlSource;
			$row = $sqlSource->query(array(ReportStatisticDataHelper::$tableName), ReportStatisticDataHelper::$columnNames, 'ReportStatisticID = ' . $id);
			return ReportStatisticDataHelper::getReportStatisticFromRow($row);
		}
		
		public static function getReportStatistics($reportID)
		{
			global $sqlSource;
			$statistics = array();
			$row = $sqlSource->query(array(ReportStatisticDataHelper::$tableName), ReportStatisticDataHelper::$columnNames, 'ReportID = ' . $reportID);
			
			while($row != null)
			{
				$statistics[] = ReportStatisticDataHelper::getReportStatisticFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $statistics;
		}
		
		private static function getNewAssocArray($reportStatistic)
		{
			$retArray = array();
			$retArray['ReportID'] = $reportStatistic->getReportID();
			$retArray['StatisticID'] = $reportStatistic->getStatisticID();
			
			return $retArray;
		}
		
		private static function getReportStatisticFromRow($row)
		{
			if($row == null)
			{
				return null;
			}
			return new ReportStatistic($row['ReportStatisticID'], $row['ReportID'], $row['StatisticID']);
		}
	}
?>