<?php
	$p = params();
	
	initAdmin();
	
	$namespace 	= "appstore";
	if (isset($p["updated"])) {
		setVar($namespace, "email", 	$p["email"]);
		setVar($namespace, "password", 	$p["password"]);
		setVar($namespace, "api", 		$p["api"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/appstore.html",
		"data"		=> array(
			
		)
	));
	
?>