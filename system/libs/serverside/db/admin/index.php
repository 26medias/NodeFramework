<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "mysql";
	
	if (isset($p["host"])) {
		setVar($namespace, "host", 		$p["host"]);
		setVar($namespace, "user",		$p["user"]);
		setVar($namespace, "password",	$p["password"]);
		setVar($namespace, "dbname",	$p["dbname"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"updated"	=> isset($p["host"])
		)
	));
	
?>