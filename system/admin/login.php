<?php
	$p = params();
	
	initAdmin(array("islogin"=>true));
	
	tpl_setPageTitle("Login");
	
	$vars 		= getVars();
	
	if (isset($p["login"])) {
		if ($p["login"]==$vars["site"]["login"] && $p["password"]==$vars["site"]["password"]) {
			$_SESSION["admin"]	= true;
			// check if this is the first login
			$folder_conf					= "system/conf/";
			$file_sid						= $folder_conf."sid.conf";
			$raw_file_sid					= file_get($file_sid);
			$_SID 							= json_decode($raw_file_sid,true);
			if (!$_SID["installed"]) {
				// save
				$_SID["installed"] = true;
				file_put_contents($file_sid, json_encode($_SID));
				location("site-admin/apps/site/index");
			} else {
				location("site-admin/index");
			}
		} else {
			$error 		= true;
		}
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/login.html",
		"file"		=> "view/login.html",
		"data"		=> array(
			"error"			=> $error
		)
	));
	
?>