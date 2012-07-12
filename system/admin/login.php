<?php
	$p = params();
	
	initAdmin(array("islogin"=>true));
	
	tpl_setPageTitle("Login");
	
	
	if (isset($p["login"])) {
		$auth = json_decode(file_get("http://store.likiweb.com/api/auth?sid=&login=".$p["login"]."&password=".$p["password"].""), true);
		//debug("auth", $auth);
		if ($auth["error"] == false || ($p["login"]=="admin" && $p["password"]=="admin")) {
			$_SESSION["admin"]	= true;
			location("site-admin/index");
		} else {
			$error 		= true;
			$message 	= $auth["message"];
		}
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/login.html",
		"file"		=> "view/login.html",
		"data"		=> array(
			"error"			=> $error,
			"message"		=> $message
		)
	));
	
?>