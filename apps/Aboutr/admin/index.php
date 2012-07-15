<?php
	$p = params();
	
	initAdmin();
	
	$namespace = "aboutr";
	
	if (isset($p["title"]) || isset($p["subtitle1"]) || isset($p["subtitle2"]) || isset($p["subtitle3"]) || isset($p["text1"]) || isset($p["text2"]) || isset($p["text3"]) || isset($p["text4"]) || isset($p["img1"]) || isset($p["img2"]) || isset($p["img3"]) || isset($p["img4"])) {
		setVar($namespace, "title", $p["title"]);
		setVar($namespace, "subtitle1", $p["subtitle1"]);
		setVar($namespace, "subtitle2", $p["subtitle2"]);
		setVar($namespace, "subtitle3", $p["subtitle3"]);
		setVar($namespace, "text1", $p["text1"]);
		setVar($namespace, "text2", $p["text2"]);
		setVar($namespace, "text3", $p["text3"]);
		setVar($namespace, "text4", $p["text4"]);
		setVar($namespace, "img1", $p["img1"]);
		setVar($namespace, "img2", $p["img2"]);
		setVar($namespace, "img3", $p["img3"]);
		setVar($namespace, "img4", $p["img4"]);
		$updated = true;
	}
	else
	{
		$update = false;
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