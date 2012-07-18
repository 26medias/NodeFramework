<?php
// load the libs
	require_once("core/compiled/php/common.php");
	
	$p = params();
	
	/* 
	worker/install/remote?
	file=http%3A%2F%2F127.0.0.1%2F_deprecated%2Fcontracts%2F29+-+sitegen%2Fstore%2Fdownload/15/abd41beee3e510c15ba5bbbf9c3fabe0
	&transaction_code=abd41beee3e510c15ba5bbbf9c3fabe0
	&fv=15
	*/
	
	$file 		= $p["file"]."?token=".$_CONF["sid"]["token"]."&secret=".$_CONF["sid"]["secret"];
	//$IDdata		= json_decode(file_get($_CONF["sid"]["store"]."download/id/".$p["fv"]),true);
	//$IDdata		= json_decode(file_get("../store/id.id"),true);
	
	$installed 	= system_install($file);
	
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
92578350?>