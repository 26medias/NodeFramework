<?php
	
	header('P3P: CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	
	error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
	session_start();
	
	/* Load the core php functions */
	require_once("core/system_files/functions.php");
	require_once("core/compiled/php/conf.php");
	require_once("core/compiled/php/clientsideincludes.php");
	
	// parse query string
	$__urlinfo 	= parse_url($_SERVER["REQUEST_URI"]);
	parse_str($__urlinfo["query"], $__qs);
	
	$_GET["__qs__"] 	= $__qs;
	$_GET["__shared__"] = array();
	
	switch ($_GET["mode"]) {
		case "web":
		default:
			require_once("core/compiled/php/common.php"); // only include the libs if we're on web mode. Else that could break the workers.
			$_GET["__here__"] = "apps/".$_GET["app"]."/";
			require_once("apps/".$_GET["app"]."/".$_GET["module"].".php");
		break;
		case "worker":
			$_GET["__here__"] = "core/workers/".$_GET["worker"]."/";
			require_once("core/workers/".$_GET["worker"]."/".$_GET["service"].".php");
		break;
	}
?>