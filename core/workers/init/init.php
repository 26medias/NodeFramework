<?php
	
	require_once("core/system_files/functions.php");
	require_once("core/compiled/php/conf.php");
	
	$p								= params();
	
	$secret							= $p["secret"];
	$rsid_script					= "http://store.likiweb.com/scripts/rsid/".$secret;
	$here							= "core/compiled/php/";
	
	$folder_conf					= "system/conf/";
	
	$file_sid						= $folder_conf."sid.conf";
	
	$cache_serverside_libs			= $here."cache_serverside_libs.json";
	$cache_clientside_libs			= $here."cache_clientside_libs.json";
	$cache_settings					= $here."cache_settings.json";
	$cache_libsettings				= $here."cache_libsettings.json";
	
	$cache_clientside_includes		= $here."settings.js";
	$cache_serverside_includes		= $here."common.php";
	$cache_conf_includes			= $here."conf.php";
	$cache_conf_clientsideincludes	= $here."clientsideincludes.php";
	
	
	
	// check if the init was already done
	$raw_file_sid					= file_get($file_sid);
	$_SID 							= json_decode($raw_file_sid,true);
	
	if ($_SID["secret"] != "") {
		die("Unauthorized update");
	}
	
	// Get the Site's Identity from the centralized DB
	$_RSID 							= json_decode(file_get($rsid_script),true);
	$_SID							= $_RSID;
	
	// Save Site's ID
	file_put_contents($file_sid, json_encode($_SID));
	
	
	
	$_CONF = array(
		"template"		=> "",
		"settings"		=> array(
			"mode_debug"	=> "on",
			"base"			=> $_SID["path"]
		),
		"sid"			=> $_SID
	);
	
	system_saveConf();
	
	$required_libs = array("Twig","Templates","zip");
	foreach ($required_libs as $libname) {
		system_registerServersideLib($libname);
	}
	
	system_activateTheme("base");
?>