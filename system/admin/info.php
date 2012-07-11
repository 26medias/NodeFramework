<?php
	$p = params();
	
	initAdmin();
	
	tpl_setPageTitle("Configuration");
	
	if (isset($_GET["app"])) {
		$objectinfo = json_decode(file_get("apps/".$_GET["app"]."/app.id"),true);
	} elseif (isset($_GET["theme"])) {
		$objectinfo = json_decode(file_get("templates/".$_GET["theme"]."/theme.id"),true);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/info.html",
		"data"		=> array(
			"objectinfo"	=> $objectinfo
		)
	));
	
?>