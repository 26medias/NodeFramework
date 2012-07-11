<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "theme_base";
	
	if (isset($p["title"])) {
		setVar($namespace, "title", 		$p["title"]);
		setVar($namespace, "copyright",		$p["copyright"]);
		setVar($namespace, "description",	$p["description"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"updated"	=> isset($p["title"])
		)
	));
	
?>