<?php
	$p = params();
	
	initAdmin();
	
	$namespace 	= "site";
	
	if (isset($p["updated"])) {
		setVar($namespace, "login", 	$p["login"]);
		setVar($namespace, "password", 	$p["password"]);
		setVar($namespace, "name", 		$p["name"]);
	}
	
	
	render(array(
		"require"	=> array("Uploadify"),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"updated"	=> isset($p["updated"])
		)
	));
	
?>