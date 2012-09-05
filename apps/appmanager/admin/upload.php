<?php
	$p = params();
	
	//initAdmin();
	
	if (!empty($_FILES)) {
		$info = pathinfo($_FILES['Filedata']['name']);
		if (in_array($info["extension"], array('zip','app'))) {
			$temp_path	= "misc/storage/temp/";
			$filename	= time()."_".md5(generateGUID()).".zip";
			
			makePath($temp_path);
			
			move_uploaded_file($_FILES['Filedata']['tmp_name'],$temp_path.$filename);
			
			//verify pack
			$validator = system_verifyPack($temp_path.$filename);
			
			if ($validator["error"]) {
				if ($validator["invalid"]) {
					echo json_encode(array(
						"error"		=> true,
						"invalid"	=> true
					));
				} else {
					echo json_encode(array(
						"error"		=> true,
						"messages"	=> $validator["messages"]
					));
				}
			} else {
				echo json_encode(array(
					"error"		=> false,
					"pack"		=> $temp_path.$filename,
					"filename"	=> $filename,
					"meta"		=> $validator["meta"],
					"meta_encode"	=> base64_encode(json_encode($validator["meta"]))
				));
			}
			
		} else {
			echo json_encode(array(
				"error"		=> true,
				"message"	=> "Format de fichier incompatible"
			));
		}
		
	}
?>