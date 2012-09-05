<?php
	$p = params();
	
	$installed 	= system_install($p["pack"]);
	
	echo json_encode($installed);
	
?>