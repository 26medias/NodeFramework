<?php
	
	header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	session_start();
	
	/* Load the core php functions */
	require_once("core/system_files/functions.php");
	require_once("core/compiled/php/conf.php");
	
	// set the base path
	$paths = explode("/",$_SERVER["REQUEST_URI"]);
	$realpath = array();
	for ($i=1;$i<count($paths)-1;$i++) {
		array_push($realpath, $paths[$i]);
	}
	$realpath = implode("/", $realpath);
	$base = "http://".$_SERVER["SERVER_NAME"]."/".$realpath."/";
	$_CONF["settings"]["base"] = $base;
	system_saveConf();
	
	// register the default serverside libs
	$required_libs = array("Twig","Templates","zip");
	foreach ($required_libs as $libname) {
		system_registerServersideLib($libname);
	}
	location("site-admin");
?>