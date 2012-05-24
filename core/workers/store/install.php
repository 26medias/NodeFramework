<?php
	// load the libs
	require_once("core/compiled/php/conf.php");
	require_once("core/compiled/php/common.php");
	require_once("system/libs/serverside/zip/zip.php");
	
	
	$tempPath = system_uncompressPackedFile("helloworld.zip");
	
	if ($tempPath === false) {
		debug("the file is not a .zip nor a .app");
	}
	
	$installConf = json_decode(file_get($tempPath."install.conf"),true);
	
	//debug("conf",$installConf);
	
	if (isset($installConf["copy"])) {
		// things to copy
		if (isset($installConf["copy"]["directories"])) {
			foreach($installConf["copy"]["directories"] as $copyDetails) {
				copy_directory($tempPath.$copyDetails[0], $copyDetails[1]);
			}
		}
		if (isset($installConf["copy"]["files"])) {
			foreach($installConf["copy"]["files"] as $copyDetails) {
				copy($tempPath.$copyDetails[0], $copyDetails[1]);
			}
		}
	}
	
	if (isset($installConf["register"])) {
		// libs to register
		if (isset($installConf["register"]["serverside"])) {
			foreach($installConf["register"]["serverside"] as $registerDetails) {
				system_registerServersideLib($registerDetails);
			}
		}
		if (isset($installConf["register"]["clientside"])) {
			foreach($installConf["register"]["clientside"] as $registerDetails) {
				system_registerClientsideLib($registerDetails);
			}
		}
	}
	//header("Location: ".$_CONF["settings"]["base"].$installConf["installScript"]);
?>