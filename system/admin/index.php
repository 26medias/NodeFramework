<?php
	$p = params();
	
	initAdmin();
	
	tpl_setPageTitle("Configuration");
	
	if (isset($p["formsubmit"])) {
		if (isset($p["baseurl"])) {
			$_CONF["settings"]["base"] = $p["baseurl"];
			system_saveConf();
		}
	}
	
	if (isset($p["theme-id"])) {
		system_activateTheme($p["theme-id"]);
	}
	
	if (isset($p["reset-cache"])) {
		system_resetCache();
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"current_theme"	=> $_CONF["template"],
			"updated"		=> isset($p["formsubmit"])
		)
	));
	
?>