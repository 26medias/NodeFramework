<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "mapr";
	
	if (isset($p["var1"])) {
		setVar($namespace, "var1", $p["var1"]);
		setVar($namespace, "var2", $p["var2"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"updated"	=> isset($p["var1"])
		)
	));
	
?>