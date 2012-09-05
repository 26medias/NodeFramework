<?php
	$p = params();
	
	initAdmin();
	
	$count = 0;
	function mapDir($dir) {
		/*global $count;
		$count++;
		if ($count > 30) {
			return $dir;
		}*/
		$buffer 	= array();
		$dirs 		= getDirAsArray($dir, array("..","."));
		foreach ($dirs as $d) {
			$buffer[$d] 	= mapDir($dir."/".$d);
			$buffer[$d]["__files__"] = getFileAsArray($dir."/".$d);
		}
		return $buffer;
	}
	
	//$sitemap = mapDir(".");
	
	//debug("sitemap", json_encode($sitemap));
	
	
	render(array(
		"require"	=> array("Uploadify","tree","jLib"),
		"dir"		=> $p["__here__"],
		"template"	=> $_CONF["template"]."/admin.html",
		"file"		=> "view/index.html",
		"data"		=> array(
			"sitemap"	=> $sitemap
		)
	));
	
?>