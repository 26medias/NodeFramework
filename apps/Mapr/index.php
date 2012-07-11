<?php
	$p = params();
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/main.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"address"	=> $_CONF["libsettings"]["server"]["Mapr"]["adresse"]
		)
	));
	
?>