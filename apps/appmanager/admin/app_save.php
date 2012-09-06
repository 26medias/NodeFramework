<?php
	$p = params();
	
	initAdmin();
	
	//call the webservice
	$call = $_CONF["vars"]["appstore"]["api"]."?email=".urlencode($_CONF["vars"]["appstore"]["email"])."&password=".urlencode($_CONF["vars"]["appstore"]["password"])."&call=savefile&type=".urlencode($p["type"])."&name=".urlencode($p["name"])."&pack=".urlencode($_CONF["settings"]["base"].$p["pack"]);
	
	$return = file_get($call);
	
	echo $return;
?>