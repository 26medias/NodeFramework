<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "contactr";
	
	if (isset($p["title"]) || isset($p["text"]) || isset($p["email"]) || isset($p["address"])) {
		setVar($namespace, "title", $p["title"]);
		setVar($namespace, "text", $p["text"]);
		setVar($namespace, "email", $p["email"]);
		setVar($namespace, "address", $p["address"]);
		$updated = true;
	}
	else
	{
		$updated = false;
	}
	
	render(array(
		"require"	=> array(),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"updated"	=> $updated
		)
	));
	
?>