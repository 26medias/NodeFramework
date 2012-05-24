<?php
// load the libs
	require_once("core/compiled/php/common.php");
	
	$p = params();
	
	
	$file = $p["file"]."?token=".$_CONF["sid"]["token"]."&secret=".$_CONF["sid"]["secret"];
	
	$installed = system_install($file);
	
	if ($installed["error"] == true) {
		// error
	} else {
		// success
		render(array(
			"require"	=> array(),
			"dir"		=> $p["__here__"],
			"template"	=> $_CONF["template"]."/main.html",
			"file"		=> "view/install.html",
			"data"		=> array(
				"test"		=> array("hello"=>"world"),
				"install"	=> $installed["conf"]
			)
		));
	}
?>