<?php
	$p = params();
	
	$data = array(
		"email"		=> $p["email"],
		"password"	=> $p["password"],
		"call"		=> $p["call"]
	);
	foreach ($p["data"] as $dataVal => $dataVal) {
		$data[$dataVal] = $dataVal;
	}
	
	
	$raw = file_get($p["url"], $data);
	
	echo $raw;
	
?>