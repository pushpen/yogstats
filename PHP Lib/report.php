<?php
	class Report
	{
		private $creatorID;
		private $reportID;
		
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
			$this->$ownerIDs = array();
			$this->$editIDs = array();
			$this->$viewIDs = array();
			
			$this->$channelIDs = array();
			
			$this->$fields = array();
		}
	}	
?>