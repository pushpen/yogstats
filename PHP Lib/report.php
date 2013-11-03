<?php
	/*require_once 'reportPermission.php';
	require_once 'reportChannel.php';
	require_once 'reportStatistic.php';
	*/require_once 'statistic.php';
	
	class Report
	{
		private $reportID;
		private $creatorID;
		private $display;
		private $hasSum;
		
		//Access Lists
		private $ownerIDs;
		private $editIDs;
		private $viewIDs;
		
		//Channels
		private $channelIDs;
		
		//Stats
		private $fields;
		
		public function __construct($creatorID, $reportID)
		{
			$this->ownerIDs = array();
			$this->editIDs = array();
			$this->viewIDs = array();
			
			$this->channelIDs = array();
			
			$this->fields = array();
		}
	}
	
	
?>