<?php
	
	class TrackedChannel
	{
		private $trackedID;
		private $channelID;
		private $channelName;
		private $authToken;
		
		public function __construct($trackedID, $channelID, $channelName, $authToken)
		{
			$this->trackedID = $trackedID;
			$this->channelID = $channelID;
			$this->channelName = $channelName;
			$this->authToken = $authToken;
		}
		
		public static function CreateFromChannelName($channelName)
		{
			global $ytDataSource;
			$channelData = $ytDataSource->query(array('channels'), array('part' => 'id,snippet', 'forUsername' => $channelName), '');
			if($channelData === null) throw new Exception('Could not find channel', 1);
			
			$channelID = $channelData->{'id'};
			$channelName = $channelData->{'snippet'}->{'title'};
			
			return new TrackedChannel(-1, $channelID, $channelName, null);
		}
		
		public function getTrackedID()
		{
			return $this->trackedID;
		}
		
		public function getChannelID()
		{
			return $this->channelID;
		}
		
		public function getChannelName()
		{
			return $this->channelName;
		}
		
		public function getAuthToken()
		{
			return $this->authToken;
		}
	}
	
	class TrackedChannelDataHelper
	{
		private static $tableName = 'TrackedChannel';
		private static $columnNames = array('TrackedID', 'ChannelID', 'ChannelName', 'AuthToken');
		
		public static function addTrackedChannel($channelName)
		{
			global $sqlSource;
			
			$newChannel = TrackedChannel::CreateFromChannelName($channelName);
			$sqlSource->insert(TrackedChannelDataHelper::$tableName, TrackedChannelDataHelper::getNewAssocArray($newChannel));
			
		}
		
		public static function getTrackedChannelByID($id)
		{
			global $sqlSource;
			$row = $sqlSource->query(array(TrackedChannelDataHelper::$tableName), TrackedChannelDataHelper::$columnNames, 'WHERE TrackedID = ' . SQLDataSource::sqlValFormat($id));
			return TrackedChannelDataHelper::getTrackedChannelFromRow($row);
		}
		
		public static function getTrackedChannels()
		{
			global $sqlSource;
			$channels = array();
			$row = $sqlSource->query(array(TrackedChannelDataHelper::$tableName), TrackedChannelDataHelper::$columnNames, '1=1');
			
			while($row != null)
			{
				$channels[] = TrackedChannelDataHelper::getTrackedChannelFromRow($row);
				$row = $sqlSource->nextResult();
			}
			
			return $channels;
		}
		
		private static function getTrackedChannelFromRow($row)
		{
			return new TrackedChannel($row['TrackedID'], $row['ChannelID'], $row['ChannelName'], $row['AuthToken']);
		}
		
		private static function getAssocArray($trackedChannel)
		{
			$retArray = array();
			$retArray['TrackedID'] = $trackedChannel->getTrackedID();
			$retArray['ChannelID'] = $trackedChannel->getChannelID();
			$retArray['ChannelName'] = $trackedChannel->getChannelName();
			$retArray['AuthToken'] = $trackedChannel->getAuthToken();
			
			return $retArray;
		}
		
		private static function getNewAssocArray($trackedChannel)
		{
			$retArray = array();
			$retArray['ChannelID'] = $trackedChannel->getChannelID();
			$retArray['ChannelName'] = $trackedChannel->getChannelName();
			$retArray['AuthToken'] = $trackedChannel->getAuthToken();
			
			return $retArray;
		}
	}
?>