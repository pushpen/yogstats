<?php
	class Statistic
	{
		private $statisticID;
		private $statisticName;
		private $fieldName;
		private $fieldValue;
		private $responseLocation;
		private $APISource;
		
		public function __construct($statisticID, $statisticName, $fieldName, $fieldValue, $responseLocation, $APISource)
		{
			$this->statisticID = $statisticID;
			$this->statisticName = $statisticName;
			$this->fieldName = $fieldName;
			$this->fieldValue = $fieldValue;
			$this->responseLocation = $responseLocation;
			$this->APISource = $APISource;
		}
		
		public function getStatisticID()
		{
			return $this->statisticID;
		}
		
		public function getStatisticName()
		{
			return $this->statisticName;
		}
		
		public function getFieldName()
		{
			return $this->fieldName;
		}
		
		public function getFieldValue()
		{
			return $this->fieldValue;
		}
		
		public function getResponseLocation()
		{
			return $this->responseLocation;
		}
		
		public function getAPISource()
		{
			return $this->APISource;
		}
	}
	
	class StatisticDataHelper
	{
		private static $tableName = 'Statistic';
		private static $columnNames = array('StatisticID', 'StatisticName', 'FieldName', 'FieldValue', 'ResponseLocation', 'APISource');
	
		public static function getStatisticFromID($id)
		{
			global $sqlSource;
			$row = $sqlSource->query(array(StatisticDataHelper::$tableName), StatisticDataHelper::$columnNames, 'WHERE StatisticID = ' . $id);
			return getStatisticFromRow($row);
		}
		
		public static function getStatisticFromRow($row)
		{
			return new Statistic($row['StatisticID'], $row['StatisticName'], $row['FieldName'], $row['FieldValue'], $row['ResponseLocation'], $row['APISource']);
		}
		
		public static function getStatistics()
		{
			global $sqlSource;
			$statistics = array();
			$row = $sqlSource->query(array(StatisticDataHelper::$tableName), StatisticDataHelper::$columnNames, '1=1');
			
			while($row != null)
			{
				$statistics[] = StatisticDataHelper::getStatisticFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $statistics;
		}
	}
?>