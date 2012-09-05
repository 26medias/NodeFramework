<?php
	$p = params();
	
	tpl_setPageTitle("Homepage");
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/main.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			
		)
	));
	
?>
