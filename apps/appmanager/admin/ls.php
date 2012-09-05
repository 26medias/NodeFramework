<?php
	$p = params();
	
	initAdmin();
	
	$dir = $p["dir"];
	$dirs 		= getDirAsArray($dir, array("..","."));
	$files 		= getFileAsArray($dir);
	
	echo json_encode(array(
		"path"	=> $dir,
		"dirs"	=> $dirs,
		"files"	=> $files
	));
?>