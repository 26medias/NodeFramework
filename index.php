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
	$_GET["__shared__"]["vars"] = $_CONF["vars"];
	
	switch ($_GET["mode"]) {
		case "web":
		default:
			require_once("core/compiled/php/common.php"); // only include the libs if we're on web mode. Else that could break the workers.
			$_GET["__here__"] = "apps/".$_GET["app"]."/";
			require_once("apps/".$_GET["app"]."/".$_GET["module"].".php");
		break;
		case "admin":
			require_once("core/compiled/php/common.php");
			if ($_GET["admcore"]) {
				$_GET["__here__"] = "system/admin/";
				require_once("system/admin/".$_GET["module"].".php");
			} else {
				switch ($_GET["admtype"]) {
					default:
					case "apps":
						$_GET["__here__"] = "apps/".$_GET["app"]."/admin/";
						require_once("apps/".$_GET["app"]."/admin/".$_GET["module"].".php");
					break;
					case "themes":
						$_GET["__here__"] = "templates/".$_GET["theme"]."/admin/";
						require_once("templates/".$_GET["theme"]."/admin/".$_GET["module"].".php");
					break;
				}
				
			}
		break;
		case "worker":
			$_GET["__here__"] = "core/workers/".$_GET["worker"]."/";
			require_once("core/workers/".$_GET["worker"]."/".$_GET["service"].".php");
		break;
	}
?>