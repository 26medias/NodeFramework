<?php
	
	$loc							= "core/compiled/clientside/";
	
	$cache_conf_clientsideincludes	= "core/compiled/php/clientsideincludes.php";
	
	$files = getFileAsArray($loc, array("js","css"));
	
	// erase the compiled files
	foreach ($files as $file) {
		unlink($loc.$file);
	}
	
	// erase the lib list
	file_put_contents($cache_conf_clientsideincludes,"<?php\n\t\$_CLIENTSIDELIBS = array();\n?>");
	
	// erase the images
	$dirs = getDirAsArray($loc."images/", array(".",".."));
	foreach ($dirs as $dir) {
		$images = getFileAsArray($loc."images/".$dir);
		foreach ($images as $image) {
			unlink($loc."images/".$dir."/".$image);
		}
		rmdir($loc."images/".$dir);
	}
	
?>