<?php
	$p = params();
	
	initAdmin();
	
	tpl_setPageTitle("Configuration");
	
	$isActive = false;
	
	if (isset($p["activate_lib"])) {
		system_registerServersideLib($p["objectid"]);
	}
	if (isset($p["deactivate_lib"])) {
		system_unregisterServersideLib($p["objectid"]);
	}
	
	switch($p["admtype"]) {
		case "apps":
		$objectinfo = json_decode(file_get("apps/".$_GET["app"]."/app.id"),true);
		break;
		case "themes":
		$objectinfo = json_decode(file_get("templates/".$_GET["theme"]."/theme.id"),true);
		break;
		case "libs":
		$objectinfo 	= json_decode(file_get("system/libs/serverside/".$_GET["lib"]."/lib.conf"),true);
		$serversideLibs = system_getServersideLibList();
		$isActive 		= in_array($objectinfo["lib"]["id"], $serversideLibs);
		break;
		case "clibs":
		$objectinfo 	= json_decode(file_get("system/libs/clientside/".$_GET["clib"]."/lib.conf"),true);
		break;
	}
	
	//debug("objectinfo", $objectinfo);
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/info.html",
		"data"		=> array(
			"objectinfo"		=> $objectinfo,
			"isServersideLib"	=> isset($_GET["lib"]),
			"objtype"			=> $p["admtype"],
			"isActive"			=> $isActive
		)
	));
	
?>