<?php
	function formatHash($hash)
	{
		$retStr = '';
		
		for($i = 0; $i < strlen($hash); $i++)
		{
			$retStr = $retStr . bin2hex(substr($hash, $i, 1));
		}
		return $retStr;
	}
	
	function formatHash2($hash)
	{
		$retStr = '';
		
		for($i = 0; $i < strlen($hash); $i++)
		{
			$retStr = $retStr . ord(substr($hash, $i, 1)) . ',';
		}
		return $retStr;
	}
	setcookie('cook', 'haha', time() + 60*60*24, '/', '', true);
	$hash = hash('SHA256', md5(rand()), false);
	echo $hash . ' ' . strlen($hash) . '<br>';
	$hash = hash('SHA256', md5(rand()), true);
	echo formatHash($hash) . ' ' . strlen( formatHash($hash)) .  '<br>';
	echo formatHash2($hash) .  '<br>';
?>