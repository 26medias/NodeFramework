<?php
	
	header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	session_start();
	
	/* Load the core php functions */
	require_once("core/system_files/functions.php");
	require_once("core/compiled/php/conf.php");
	
	
	// check if already installed
	$folder_conf					= "system/conf/";
	$file_sid						= $folder_conf."sid.conf";
	
	
	if (isset($_GET["secret"])) {
		
		$password = $_CONF["vars"]["site"]["password"];
		include("system/admin/view/credentials.html");
		//location("site-admin");
	} else {
	
		if (file_exists($file_sid)) {
			$raw_file_sid					= file_get($file_sid);
			$_SID 							= json_decode($raw_file_sid,true);
			
			if ($_SID["secret"] != "") {
				die("Node already installed. Delete system/conf/sid.conf to allow for a new install.");
			}
		}
	
		// set the base path
		$paths = explode("/",$_SERVER["REQUEST_URI"]);
		$realpath = array();
		for ($i=1;$i<count($paths)-1;$i++) {
			array_push($realpath, $paths[$i]);
		}
		$realpath = implode("/", $realpath);
		$base = "http://".$_SERVER["SERVER_NAME"]."/".$realpath."/";
		if (!isset($_CONF["settings"])) {
			$_CONF["settings"] = array();
		}
		$_CONF["settings"]["base"] = $base;
		$_CONF["original_template"] = false;
		system_saveConf();
		
		// register the default serverside libs
		$required_libs = array("Twig","Templates","zip");
		foreach ($required_libs as $libname) {
			system_registerServersideLib($libname);
		}
		
		// install the template
		system_activateTheme("base");
		system_activateAdminTheme("adm-base");
		
		
		// Create secret and token keys
		$secret		= md5(time()."|secret|".$base);
		$token		= md5(time()."|token|".$base);
		$appstore	= "http://127.0.0.1/_deprecated/contracts/29 - sitegen/store2/";
		$password	= substr(create_token(), 0, 8);
		
		// save username and password for admin access
		setVar("site", "login", 	"admin");
		setVar("site", "password", 	$password);
		
		file_put_contents($file_sid, json_encode(array(
			"sid"		=> 0,
			"installed"	=> false,
			"token"		=> $token,
			"secret"	=> $secret,
			"store"		=> $appstore
		)));
		
		location("install/".$secret);
	}
?>