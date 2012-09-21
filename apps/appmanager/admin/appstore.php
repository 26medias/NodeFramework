<?php
	$p = params();
	
	initAdmin();
	
	$file_sid = "system/conf/sid.conf";
	if (file_exists($file_sid)) {
		$raw_file_sid					= file_get($file_sid);
		$_SID 							= json_decode($raw_file_sid,true);
		$sid = $_SID["sid"];
	} else {
		$sid = 0;
	}
	$namespace 	= "appstore";
	if (isset($p["updated"])) {
		setVar($namespace, "email", 	$p["email"]);
		setVar($namespace, "password", 	$p["password"]);
		setVar($namespace, "api", 		$p["api"]);
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/appstore.html",
		"data"		=> array(
			"sid"		=> $sid,
			"token"		=> $_SID["token"],
			"secret"	=> $_SID["secret"]
		)
	));
	
?>