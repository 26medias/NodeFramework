<?php
/************************************************************************/
/*			BLACKWIZZARD.NET SDK FOR CMSPHP			*/
/*					-------------------		*/
/*   copyright		: (C) 2005 Julien Loutre AKA blackwizzard	*/
/*   contact		: blackwizzard@gmail.com / blackwizzard.net	*/
/*					-------------------		*/
/*									*/
/************************************************************************/

function saveThumb($inputFile, $outputFile, $squareLimit=200, $JPEGquality=100, $white=false) {
	// $inputFile: original filename
	// $outputFile: output filename
	// $squareLimit:max size of the picture's sides, in pixel.
	// $JPEGquality:quality of the output JPEG file
	// file extention detection updated by Bricomix.
	
	$ext = strtolower(substr($inputFile, strrpos($inputFile, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($inputFile);
		break;
		case "gif":
		$img_in = imagecreatefromgif($inputFile);
		break;
		case "png":
		$img_in = imagecreatefrompng($inputFile);
		break;
		case "swf":
		copy($inputFile, $outputFile);
		return $outputFile;
		break;
		default:
	}
	list($width, $height, $type, $attr) = getimagesize($inputFile);
	if ($squareLimit < $width || $squareLimit < $height) {
		$ratio = $width/$height;
		if ($ratio>=1) {
			$nw = $squareLimit;
			$nh = $squareLimit/$ratio;
		} else {
			$nw = $squareLimit*$ratio;
			$nh = $squareLimit;
		}
	} else {
		$nw = $width;
		$nh = $height;
	}

	$img_out = imagecreatetruecolor($nw, $nh);
	imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, imagesx($img_out), imagesy($img_out), imagesx($img_in), imagesy($img_in));

	$t = imagejpeg($img_out, $outputFile, $JPEGquality);
	imagedestroy($img_out);
	imagedestroy($img_in);
	if ($white == true) {
		applyWhiteBG($squareLimit, $outputFile, $outputFile);
	}
	return $outputFile;
}

function saveThumb2($inputFile, $outputFile, $wLimit=200, $hLimit=200, $JPEGquality=100, $color="0,0,0", $overlay_source=false, $overlayBack=false) {
	
	$ow = $wLimit;
	$oh = $hLimit;
	
	$ext = strtolower(substr($inputFile, strrpos($inputFile, '.') + 1));
	switch($ext) {
	  case "jpg":
	  case "jpeg":
	    $img_in = imagecreatefromjpeg($inputFile);
	    break;
	  case "gif":
	    $img_in = imagecreatefromgif($inputFile);
	    break;
	  case "png":
	    $img_in = imagecreatefrompng($inputFile);
	    imagealphablending($img_in, false);
		imagesavealpha($img_in, true);
	    break;
	  case "swf":
		copy($inputFile, $outputFile);
		return $outputFile;
		break;
	  default:
	}
	list($width, $height, $type, $attr) = getimagesize($inputFile);
	
	if ($wLimit < $width || $hLimit < $height) {
		$ratio = $width/$height;
		if ($ratio>=1) { // width > height
			$nw = $wLimit;
			$scaleRatio = $width/$wLimit;
			$nh = $height/$scaleRatio;
		} else { // height > width
			$nh = $hLimit;
			$scaleRatio = $height/$hLimit;
			$nw = $width/$scaleRatio;
		}
	} else {
		$nw 		= $width;
		$nh 		= $height;
	}
	//imagepng($img_in, $outputFile, 9, PNG_ALL_FILTERS);
	//return false;
	$coord = array();
	$coord["x"] 	= ($ow-$nw)/2;
	$coord["y"] 	= ($oh-$nh)/2;
	
	$img_out 		= imagecreatetruecolor($nw, $nh);
	imagealphablending($img_out, false);
	
	imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, $nw, $nh, imagesx($img_in), imagesy($img_in));
	imagesavealpha($img_out,true);
	//imagepng($img_out, $outputFile, 9, PNG_ALL_FILTERS);
	//return false;
	
	if ($color == false || $color == "false") {
		$img_bg = imagecreatetruecolor($ow, $oh);
		imagealphablending($img_bg, false);
		imagecopy($img_bg, $img_out, $coord["x"],$coord["y"],0,0,$nw,$nh);
		imagesavealpha($img_bg,true);
		imagepng($img_bg, $outputFile, 9, PNG_ALL_FILTERS);
		return true;
	}
	
	$img_bg = imagecreatetruecolor($ow, $oh);
	$colorArrray 	= explode(",",$color);
	$bgcolor 		= imagecolorallocate($img_bg, $colorArrray[0], $colorArrray[1], $colorArrray[2]);
	imagefill($img_bg,0,0,$bgcolor);

	
	if (!$overlayBack) {
		imagecopy($img_bg, $img_out, $coord["x"],$coord["y"],0,0,$nw,$nh);
	}
	
	if ($overlay_source != false) {
		// create overlay
		$overlay_ress 	= imagecreatefrompng($overlay_source);
	    imagealphablending($overlay_ress, false);
		imagesavealpha($overlay_ress, true);
		$img_overlay 	= imagecreatetruecolor($ow, $oh);
		$overlay_w 		= 10;
		$overlay_h 		= 10;
		$overlay_nx 	= ceil($ow/$overlay_w);
		$overlay_ny 	= ceil($oh/$overlay_h);
		for ($px=0; $px < $overlay_nx; $px++) {
			for ($py=0; $py < $overlay_ny; $py++) {
				imagecopy($img_bg, $overlay_ress, $px*$overlay_w,$py*$overlay_h,0,0,$overlay_w,$overlay_h);
			}
		}
	}
	
	if ($overlayBack) {
		imagecopy($img_bg, $img_out, $coord["x"],$coord["y"],0,0,$nw,$nh);
	}
	
	imagepng($img_bg, $outputFile, 9, PNG_ALL_FILTERS);
	//imagejpeg($img_bg, $outputFile, 100);
	
}

function crop($inputFile, $outputFile, $w, $h, $x, $y, $JPEGquality=100) {

	$ext = strtolower(substr($inputFile, strrpos($inputFile, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($inputFile);
		break;
		case "gif":
		$img_in = imagecreatefromgif($inputFile);
		break;
		case "png":
		$img_in = imagecreatefrompng($inputFile);
		break;
		default:
	}
	list($width, $height, $type, $attr) = getimagesize($inputFile);

	$img_out = imagecreatetruecolor($w, $h);
	imagecopyresampled($img_out, $img_in, 0, 0, $x, $y, $w, $h, $w, $h);

	$t = imagejpeg($img_out, $outputFile, $JPEGquality);
	imagedestroy($img_out);
	imagedestroy($img_in);

	return $outputFile;
}
function applyBG($color, $wSize, $hSize, $source, $export) {
	$ext = strtolower(substr($source, strrpos($source, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($source);
		break;
		case "gif":
		$img_in = imagecreatefromgif($source);
		break;
		case "png":
		$img_in = imagecreatefrompng($source);
		break;
		default:
		return $source;
	}
	list($width, $height, $type, $attr) = getimagesize($source);
	$img_bg = imagecreatetruecolor($wSize, $hSize);
	$colorArrray = explode(",",$color);
	$bgcolor = imagecolorallocate($img_bg, $colorArrray[0], $colorArrray[1], $colorArrray[2]);
	imagefill($img_bg,0,0,$bgcolor);

	$coord = array();
	$coord["x"] = ($wSize-$width)/2;
	$coord["y"] = ($hSize-$height)/2;

	imagecopy($img_bg, $img_in, $coord["x"],$coord["y"],0,0,$width,$height);

	$t = imagejpeg($img_bg, $export, 100);
	return $t;
}
function applyWhiteBG($size, $source, $export) {
	$ext = strtolower(substr($source, strrpos($source, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($source);
		break;
		case "gif":
		$img_in = imagecreatefromgif($source);
		break;
		case "png":
		$img_in = imagecreatefrompng($source);
		break;
		default:
		return $source;
	}
	list($width, $height, $type, $attr) = getimagesize($source);
	$img_bg = imagecreatetruecolor($size, $size);
	$bgcolor = imagecolorallocate($img_bg, 255, 255, 255);
	imagefill($img_bg,0,0,$bgcolor);

	$coord = array();
	$coord["x"] = ($size-$width)/2;
	$coord["y"] = ($size-$height)/2;

	imagecopy($img_bg, $img_in, $coord["x"],$coord["y"],0,0,$width,$height);

	$t = imagejpeg($img_bg, $export, 100);
	return $t;
}

function getImageProps($filename) {
	$ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
	switch($ext) {
		default:
		return array("error"=>$ext);
		break;
		case "jpg":
		case "jpeg":
		$filetype = "image";
		break;
		case "gif":
		$filetype = "image";
		break;
		case "png":
		$filetype = "image";
		break;
		case "swf":
		$filetype = "flash";
		break;
		case "flv":
		$filetype = "video";
		break;
		case "pdf":
		$filetype = "pdf";
		break;
	}
	if ($filetype == "flash") {
		$swf = new swfheader(false);
		$swf->loadswf($filename);
		$width = $swf->width;
		$height = $swf->height;
	} else {
		list($width, $height, $type, $attr) = getimagesize($filename);
	}
	return array(
	"width"=>$width,
	"height"=>$height,
	"ext"=>$ext,
	"size"=>formatBytes(filesize($filename)),
	"filetype"=>$filetype
	);
}

function formatBytes($b,$p = null) {
	/**
	*
	* @author Martin Sweeny
	* @version 2010.0617
	*
	* returns formatted number of bytes.
	* two parameters: the bytes and the precision (optional).
	* if no precision is set, function will determine clean
	* result automatically.
	*
	**/
	$units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
	$c=0;
	if(!$p && $p !== 0) {
		foreach($units as $k => $u) {
			if(($b / pow(1024,$k)) >= 1) {
				$r["bytes"] = $b / pow(1024,$k);
				$r["units"] = $u;
				$c++;
			}
		}
		return number_format($r["bytes"],2) . " " . $r["units"];
	} else {
		return number_format($b / pow(1024,$p)) . " " . $units[$p];
	}
}

function MakeProfileIcon($inputFile, $outputFile, $squareLimit=60, $JPEGquality=80) {
	global $_FOLDERS, $_TEMPLATE;
	// $inputFile: original filename
	// $outputFile: output filename
	// $squareLimit:max size of the picture's sides, in pixel.
	// $JPEGquality:quality of the output JPEG file
	// file extention detection updated by Bricomix.


	$ext = strtolower(substr($inputFile, strrpos($inputFile, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($inputFile);
		break;
		case "gif":
		$img_in = imagecreatefromgif($inputFile);
		break;
		case "png":
		$img_in = imagecreatefrompng($inputFile);
		break;
		default:

	}
	list($width, $height, $type, $attr) = getimagesize($inputFile);
	if ($squareLimit < $width) {
		$ratio = $width/$height;
		if ($ratio>=1) {
			$nw = $squareLimit;
			$nh = $squareLimit/$ratio;
		} else {
			$nw = $squareLimit*$ratio;
			$nh = $squareLimit;
		}
	} else {
		$nw = $width;
		$nh = $height;
	}
	$img_out = imagecreatetruecolor($nw, $nh);
	imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, imagesx($img_out), imagesy($img_out), imagesx($img_in), imagesy($img_in));

	$img_out2 = imagecreatetruecolor($squareLimit, $squareLimit);
	imagecolorallocate($img_out2, 0, 0, 0);


	$free_w = $squareLimit-$nw;
	$free_h = $squareLimit-$nh;
	$dec_w = $free_w/2;
	$dec_h = $free_h/2;

	imagecopy($img_out2, $img_out,$dec_w,$dec_h,0,0,$squareLimit,$squareLimit);



	$img_mask = imagecreatefrompng($_FOLDERS["template"].$_TEMPLATE["name"]."/images/avatar_mask.png");
	imagecopy($img_out2, $img_mask,0,0,0,0,$squareLimit,$squareLimit);

	$t = imagejpeg($img_out2, WCFolder($outputFile,1), $JPEGquality);
	imagedestroy($img_out);
	imagedestroy($img_out2);
	imagedestroy($img_in);
	imagedestroy($img_mask);
	return $outputFile;
}

function applySignature($signature, $source) {
	$ext = strtolower(substr($source, strrpos($source, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($source);
		break;
		case "gif":
		$img_in = imagecreatefromgif($source);
		break;
		case "png":
		$img_in = imagecreatefrompng($source);
		break;
		default:
		return $source;
	}
	list($width, $height, $type, $attr) = getimagesize($source);
	/*$img_out = imagecreatetruecolor($width, $height);*/
	$img_mask = imagecreatefrompng($signature);
	imagecopy($img_in, $img_mask,0,0,-25,-25,$width,$height);

	$t = imagejpeg($img_in, $source, 100);
	return $t;
	/*
	$ext = strtolower(substr($source, strrpos($source, '.') + 1));
	switch($ext) {
	case "jpg":
	case "jpeg":
	$img_in = imagecreatefromjpeg($source);
	break;
	case "gif":
	$img_in = imagecreatefromgif($source);
	break;
	case "png":
	$img_in = imagecreatefrompng($source);
	break;
	default:
	return $source;
	}
	list($width, $height, $type, $attr) = getimagesize($source);
	/*$img_out = imagecreatetruecolor($width, $height);
	$img_out = imagecreatetruecolor($width, $height);
	imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, imagesx($img_out), imagesy($img_out), imagesx($img_in), imagesy($img_in));

	$img_out2 = imagecreatetruecolor($width, $height);
	imagecolorallocate($img_out2, 0, 0, 0);

	imagecopy($img_out2, $img_out,$width,$height,0,0,$width,$height);

	$img_mask = imagecreatefrompng($signature);
	imagecopy($img_out2, $img_mask,0,0,0,0,$width,$height);

	$t = imagejpeg($img_in, $source, 100);
	return $source;
	*/
}

function saveAsJPG($inputFile, $outputFile, $JPEGquality=100) {
	// $inputFile: original filename
	// $outputFile: output filename
	// $JPEGquality:quality of the output JPEG file
	// file extention detection updated by Bricomix.
	$ext = strtolower(substr($inputFile, strrpos($inputFile, '.') + 1));
	switch($ext) {
		case "jpg":
		case "jpeg":
		$img_in = imagecreatefromjpeg($inputFile);
		break;
		case "gif":
		$img_in = imagecreatefromgif($inputFile);
		break;
		case "png":
		$img_in = imagecreatefrompng($inputFile);
		break;
		default:

	}

	$nw = 200;
	$nh = 200;
	$img_out = imagecreatetruecolor($nw, $nh);
	imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, imagesx($img_out), imagesy($img_out), imagesx($img_in), imagesy($img_in));

	$t = imagejpeg($img_out, $outputFile, $JPEGquality);
	imagedestroy($img_out);
	imagedestroy($img_in);
	return $t;
}

function createthumb($name,$filename, $thumb_x, $thumb_y, $caption)
{
	if (!file_exists($filename)){
		$img_in = imagecreatefromjpeg($name);
		$img_out = imagecreatetruecolor($thumb_x, $thumb_y);
		imagecopyresampled($img_out, $img_in, 0, 0, 0, 0, imagesx($img_out), imagesy($img_out), imagesx($img_in), imagesy($img_in));
		$white = imagecolorallocate($img_out, 255, 255, 255);
		$black = imagecolorallocate($img_out, 0, 0, 0);
		imagestring($img_out,1,3,3,$caption,$black);
		imagestring($img_out,1,2,2,$caption,$white);
		$t = imagejpeg($img_out, $filename, 100);
		imagedestroy($img_out);
		imagedestroy($img_in);
	}

}

function copyright($filename, $caption) {
	$img_out = imagecreatefromjpeg($filename);
	$white = imagecolorallocate($img_out, 255, 255, 255);
	$black = imagecolorallocate($img_out, 0, 0, 0);
	imagestring($img_out,1,3,3,$caption,$black);
	imagestring($img_out,1,2,2,$caption,$white);
	$t = imagejpeg($img_out);
	return $t;
}

function uploadpicture($field) {
	global $_FOLDERS;

	if (!empty($_FILES[$field]["name"])) {
		if (in_array(strtolower(STRING_get_file_ext($_FILES[$field]["name"])),array("jpg","jpeg","gif","png"))) {
			$uniqid = makeRandomNumber(10);
			// ICON
			$tempName = $_FOLDERS["temp"].$uniqid.".".STRING_get_file_ext($_FILES[$field]["name"]);
			move_uploaded_file($_FILES[$field]["tmp_name"],$tempName);
		} else{
			return false;
		}
	} else{
		return false;
	}
	return $tempName;
}

function createUploadDir($fromname) {
	global $_FOLDERS;
	$uniqid = makeRandomNumber(10);
	$purename = aphanumeric_field($fromname,false);
	$uploadDir = $_FOLDERS["upload"].substr($purename,0,1)."/".substr($purename,1,1)."/".substr($purename,2,1);
	WCFolder($uploadDir);
	return $uploadDir."/".$uniqid.".jpg";
}

function uploadAndResize($field, $tempFolder, $upFolder, $squareLimit=200, $JPEGquality=100, $leavetemp=false) {
	if (!empty($_FILES[$field]["name"])) {
		$uniqid = makeRandomNumber(10);
		// ICON
		$tempName = $tempFolder.$uniqid.".".STRING_get_file_ext($_FILES["picture"]["name"]);
		$finalName = $upFolder.$uniqid.".jpg";
		copy($_FILES[$field]["tmp_name"],$tempName);
		saveThumb($tempName,$finalName,$squareLimit,$JPEGquality);
		if (!$leavetemp) {
			unlink($tempName);
		}
	} else {
		return false;
	}
	return $finalName;
}


function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
	if(!isset($pct)){
		return false;
	}
	$pct /= 100;
	// Get image width and height
	$w = imagesx( $src_im );
	$h = imagesy( $src_im );
	// Turn alpha blending off
	imagealphablending( $src_im, false );
	// Find the most opaque pixel in the image (the one with the smallest alpha value)
	$minalpha = 127;
	for( $x = 0; $x < $w; $x++ )
	for( $y = 0; $y < $h; $y++ ){
		$alpha = ( imagecolorat( $src_im, $x, $y ) >> 24 ) & 0xFF;
		if( $alpha < $minalpha ){
			$minalpha = $alpha;
		}
	}
	//loop through image pixels and modify alpha for each
	for( $x = 0; $x < $w; $x++ ){
		for( $y = 0; $y < $h; $y++ ){
			//get current alpha value (represents the TANSPARENCY!)
			$colorxy = imagecolorat( $src_im, $x, $y );
			$alpha = ( $colorxy >> 24 ) & 0xFF;
			//calculate new alpha
			if( $minalpha !== 127 ){
				$alpha = 127 + 127 * $pct * ( $alpha - 127 ) / ( 127 - $minalpha );
			} else {
				$alpha += 127 * $pct;
			}
			//get the color index with new alpha
			$alphacolorxy = imagecolorallocatealpha( $src_im, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
			//set pixel with the new color + opacity
			if( !imagesetpixel( $src_im, $x, $y, $alphacolorxy ) ){
				return false;
			}
		}
	}
	// The image copy
	imagecopy($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
}
function openGDLink($src) {
	if (!file_exists($src)) {
		debug("MISSING", $src);
	}
	$gdlink = imagecreatefrompng($src);
	imagealphablending($gdlink, false);
	imagesavealpha($gdlink, true);
	list($width, $height, $type, $attr) = getimagesize($src);
	return array(
	"link"=> $gdlink,
	"w"=> $width,
	"h"=> $height
	);
}
?>