<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "aboutr";
	
	if (isset($p["var3"])) {
		setVar($namespace, "var3", $p["var3"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/test.html",
		"data"		=> array(
			"updated"	=> isset($p["var3"])
		)
	));
	
?>