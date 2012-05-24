<?php
	// load the libs
	require_once("core/compiled/php/conf.php");
	require_once("core/compiled/php/common.php");
	require_once("system/libs/serverside/zip/zip.php");
	
	
	
	
	echo $_CONF["settings"]["base"].$installConf["installScript"];
	//header("Location: ".$_CONF["settings"]["base"].$installConf["installScript"]);
?>