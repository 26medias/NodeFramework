<?php
	$p = params();
	
	initAdmin();
	
	
	
	render(array(
		"require"	=> array("Uploadify","tree","jLib"),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/install.html",
		"data"		=> array(
			
		)
	));
	
?>