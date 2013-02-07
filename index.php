<?php
	define('_VALIDATE','1');
	// Core Include
	require_once 'library/jmtfw.php';
	
	// create framework object and call dispatch method
	$framework = new JMTFW();
	$framework->dispatch();
?>