<?php
	$p = params();
	
	initAdmin(array("islogin"=>true));
	
	tpl_setPageTitle("Login");
	
	$vars 		= getVars();
	
	if (isset($p["login"])) {
		if (($p["login"]==$vars["site"]["login"] && $p["password"]==$vars["site"]["password"]) || !isset($vars["site"]["login"])) {
			$_SESSION["admin"]	= true;
			location("site-admin/index");
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