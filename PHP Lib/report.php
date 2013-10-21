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
			$ownerIDs = array();
			$editIDs = array();
			$viewIDs = array();
			
			$channelIDs = array();
			
			$fields = array();
		}
	}	
?>