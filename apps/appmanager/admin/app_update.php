<?php
	$p = params();
	
	initAdmin();
	
	//call the webservice
	$call = $_CONF["vars"]["appstore"]["api"]."?email=".urlencode($_CONF["vars"]["appstore"]["email"])."&password=".urlencode($_CONF["vars"]["appstore"]["password"])."&call=addfile&pack=".urlencode($_CONF["settings"]["base"].$p["pack"])."&project=".$p["project"];
	
	$return = file_get($call);
	
	echo $return;
?>