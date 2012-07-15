<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "mapr";
	
	if (isset($p["address"])) {
		setVar($namespace, "address", $p["address"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"updated"	=> isset($p["address"])
		)
	));
	
?>