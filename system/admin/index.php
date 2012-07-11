<?php
	$p = params();
	
	initAdmin();
	
	tpl_setPageTitle("Configuration");
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			
		)
	));
	
?>