<?php
	require_once __DIR__ . '/reportPermission.php';
	require_once __DIR__ . '/reportChannel.php';
	require_once __DIR__ . '/reportStatistic.php';
	require_once __DIR__ . '/statistic.php';
	
	class Report
	{
		private $reportID;
		private $creatorID;
		private $reportName;
		private $hasAllChannels;
		private $display;
		private $hasSum;
		
		//Related Tables
		private $channels = null;
		private $statistics = null;
		
		public function __construct($reportID, $creatorID, $reportName, $hasAllChannels, $display, $hasSum)
		{
			$this->reportID = $reportID;
			$this->creatorID = $creatorID;
			$this->reportName = $reportName;
			$this->hasAllChannels = $hasAllChannels;
			$this->display = $display;
			$this->hasSum = $hasSum;
			
			/*$this->ownerIDs = array();
			$this->editIDs = array();
			$this->viewIDs = array();
			
			$this->channelIDs = array();
			
			$this->fields = array();*/
		}
		
		public static function CreateReport($creatorID, $reportName, $hasAllChannels, $display, $hasSum)
		{
			return new Report(-1, $creatorID, $reportName, $hasAllChannels, $display, $hasSum);
		}
		
		public function getReportData($headerRow = true)
		{
			$data = array();
			$this->getChannels();
			$this->getStatistics();
			
			$resolvedStats = array();
			$resolvedChannels = array();
			
			foreach($this->statistics as $statistic)
			{
				$resolvedStats[] = StatisticDataHelper::getStatisticByID($statistic->getStatisticID());
			}
			
			if($this->hasAllChannels)
			{
				$resolvedChannels = TrackedChannelDataHelper::getTrackedChannels();
			}
			else
			{
				foreach($this->channels as $channel)
				{
					$resolvedChannels[] = TrackedChannelDataHelper::getTrackedChannelByID($channel->getTrackedID());
				}
			}
			
			$tableHeader = null;
			
			if($headerRow)
			{
				$headerNames = array();
				
				foreach($resolvedStats as $statistic)
				{
					$headerNames[] = $statistic->getStatisticName();
				}
				
				$tableHeader = new ReportDataRow('Channel Name', $headerNames);
			}
			global $ytDataSource;
			$querySources = array($ytDataSource);
			$queryFields = $this->constructQueryFields($resolvedStats);
			
			foreach($resolvedChannels as $channel)
			{
				$queryResults = $this->executeQueries($queryFields, $querySources, array(array('channels')), array(array('id' => $channel->getChannelID())));	
				$values = $this->fetchValues($resolvedStats, $queryResults);
				$data[] = new ReportDataRow($channel->getChannelName(), $values);
			}
			
			$sum = null;
			
			if($this->hasSum)
			{
				$values = array();
				
				foreach($data as $row)
				{
					for($i = 0; $i < sizeof($row->getRowValues()); $i++)
					{
						if(is_numeric($row->getRowValues()[$i]))
						{
							if(!isset($values[$i]))
							{
								$values[$i] = 0;
							}
							$values[$i] += $row->getRowValues()[$i];
						}	
						else
						{
							$values[$i] = '';
						}
					}
				}
				
				$sum = new ReportDataRow('Sum', $values);
			}
			
			return new ReportData($tableHeader, $data, $sum);
		}
		
		private function constructQueryFields($statistics)
		{
			$fields = array();
			
			foreach($statistics as $statistic)
			{
				if(!isset($fields[$statistic->getAPISource()]))
				{
					$fields[$statistic->getAPISource()] = array();
				}
				if(!isset($fields[$statistic->getAPISource()][$statistic->getFieldName()]))
				{
					$fields[$statistic->getAPISource()][$statistic->getFieldName()] = array();
				}
				
				$fields[$statistic->getAPISource()][$statistic->getFieldName()][$statistic->getFieldValue()] = true;
			}
			
			foreach($fields as $sourceKey => $queryFields)
			{
				foreach($queryFields as $fKey => $fieldValueArray)
				{
					$fieldValueString = '';
					$seperator = '';
					foreach($fieldValueArray as $key => $value)
					{
						$fieldValueString .= $seperator . $key;
						$seperator = ',';
					}
					
					$fields[$sourceKey][$fKey] = $fieldValueString;
				}
			}
			
			return $fields;
		}
		
		private function executeQueries($fields, $sources, $sourceNames, $channelFields)
		{
			$queryResults = array();
			
			foreach($fields as $source => $queryFields)
			{
				foreach($channelFields[$source] as $key => $value)
				{
					$queryFields[$key] = $value;
				}
				$queryResults[$source] = $sources[$source]->query($sourceNames[$source], $queryFields, '');
			}
			
			return $queryResults;
		}
		
		private function fetchValues($statistics, $results)
		{
			$values = array();
			$i = 0;
			
			foreach($statistics as $statistic)
			{
				$values[$i] = $statistic->getData($results[$statistic->getAPISource()]);
				$i++;
			}
			return $values;
		}
		
		public function getReportID()
		{
			return $this->reportID;
		}
		
		public function setReportID($reportID)
		{
			$this->reportID = $reportID;
		}
		
		public function getCreatorID()
		{
			return $this->creatorID;
		}
		
		public function getReportName()
		{
			return $this->reportName;
		}
		
		public function getHasAllChannels()
		{
			return $this->hasAllChannels;
		}
		
		public function getDisplay()
		{
			return $this->display;
		}
		
		public function getHasSum()
		{
			return $this->hasSum;
		}
		
		public function getChannels()
		{
			if($this->channels === null)
			{
				if(!$this->hasAllChannels)
				{
					$this->channels = ReportChannelDataHelper::getReportChannels($this->reportID);
				}
				else
				{
					$this->channels = array();
				}
			}
			return $this->channels;
		}
		
		public function getStatistics()
		{
			if($this->statistics === null)
			{
				$this->statistics = ReportStatisticDataHelper::getReportStatistics($this->reportID);
			}
			return $this->statistics;
		}
	}
	
	class ReportData
	{
		private $header;
		private $data;
		private $sum;
		
		public function __construct($header, $data, $sum)
		{
			$this->header = $header;
			$this->data = $data;
			$this->sum = $sum;
		}
		
		public function getHeader()
		{
			return $this->header;
		}
		
		public function getData()
		{
			return $this->data;
		}
		
		public function getSum()
		{
			return $this->sum;
		}
	}
	
	class ReportDataRow
	{
		private $rowName;
		private $rowValues;
		
		public function __construct($rowName, $rowValues)
		{
			$this->rowName = $rowName;
			$this->rowValues = $rowValues;
		}
		
		public function getRowName()
		{
			return $this->rowName;
		}
		
		public function getRowValues()
		{
			return $this->rowValues;
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
		private static $columnNames = array('ReportID', 'CreatorID', 'ReportName', 'HasAllChannels', 'Display', 'HasSum');
		
		private static function insertReport($report)
		{
			global $sqlSource;
			$sqlSource->insert(ReportDataHelper::$tableName, ReportDataHelper::getNewAssocArray($report));
			return $sqlSource->getLastID();
		}
		
		public static function putNewReport($report, $permissions, $channels, $statistics)
		{
			$reportID = ReportDataHelper::insertReport($report);
			$report->setReportID($reportID);
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
		
		private static function addTableNameToColumns($columns, $tableName)
		{
			$retArray = array();
			foreach($columns as $column)
			{
				$retArray[] = $tableName . '.' . $column;
			}
			
			return $retArray;
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
				$row = $sqlSource->query(array($rTbl, $rPTbl), ReportDataHelper::addTableNameToColumns(ReportDataHelper::$columnNames, ReportDataHelper::$tableName), '(' . $rTbl . '.ReportID = ' . $rPTbl . '.ReportID AND ' . $rPTbl . '.GoogleID = ' . SQLDataSource::sqlValFormat($user->getGoogleID()) .  ' AND (' . $rPTbl . '.Permissions & 1 = 1))'); 
				
				while($row != null)
				{
					$reports[$row['ReportID']] = ReportDataHelper::getReportFromRow($row);
					$row = $sqlSource->nextResult();
				}
				
				$row = $sqlSource->query(array($rTbl), ReportDataHelper::$columnNames, 'CreatorID = ' . SQLDataSource::sqlValFormat($user->getGoogleID()));
				
				while($row != null)
				{
					$reports[$row['ReportID']] = ReportDataHelper::getReportFromRow($row);
					$row = $sqlSource->nextResult();
				}
				
				$retReports = array();
				
				foreach($reports as $key => $value)
				{
					$retReports[] = $value;
				}
				
				return $retReports;
			}
		}
		
		private static function getNewAssocArray($report)
		{
			$retArray = array();
			$retArray['CreatorID'] = $report->getCreatorID();
			$retArray['ReportName'] = $report->getReportName();
			$retArray['HasAllChannels'] = $report->getHasAllChannels();
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
			return new Report($row['ReportID'], $row['CreatorID'], $row['ReportName'], $row['HasAllChannels'], $row['Display'], $row['HasSum']);
		}
	}
?>