<?php
	
	$p								= params();
	
	$secret							= $p["secret"];
	$rsid_script					= "http://www.likiweb.com/scripts/rsid/".$secret;
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
		//die("Unauthorized update");
	}
	
	// Get the Site's Identity from the centralized DB
	$_RSID 							= json_decode(file_get($rsid_script),true);
	$_SID							= $_RSID;
	
	// Save Site's ID
	file_put_contents($file_sid, json_encode($_SID));
	
	
	
	// Get the current settings
	$settings 						= json_decode(file_get($cache_settings),true);
	
	// Update the data
	$settings["selected"]					= "dev";
	$settings["settings"]["dev"]["base"]	= $_SID["path"];
	
	// save the settings
	file_put_contents($cache_settings, json_encode($settings));
	
	
	// create the CONF file
	$_CONF = array(
		"libsettings"	=> array(),
		"template"		=> "",
		"settings"		=> array(
			"conf"			=> "dev",
			"mode_debug"	=> "on",
			"base"			=> $_SID["path"]
		),
		"sid"			=> $_SID
	);
	
	//debug("_CONF", $_CONF);
	
	file_put_contents($cache_conf_includes, 		"<?php\n\t\$_CONF=".array_to_phpArray($_CONF).";\n?>");
	
	system_registerServersideLib("Twig");
	system_registerServersideLib("zip");
	system_setTemplate("gray");
?>