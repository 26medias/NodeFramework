<?php
	
	class gdgram {
		public function __construct() {
			$this->layers 		= array();
			$this->canvas		= array(
				"width"		=> 200,
				"height"	=> 200
			);
			$this->exportFormat	= "PNG";
		}
		public function canvasSize($w,$h) {
			$this->canvas["width"] 	= $w;
			$this->canvas["height"] = $h;
		}
		
		public function newLayer($name, $width=false, $height=false) {
			if (!$width) { 	$width 	=  $this->canvas["width"]; }
			if (!$height) { $height =  $this->canvas["height"]; }
			$ress = $this->createTransparentRessource($width, $height);
			array_push($this->layers, array(
				"name"		=> $name,
				"ress"		=> $ress,
				"width"		=> $width,
				"height"	=> $height
			));
			//return count($this->layers)-1;
			return array(
				"name"		=> $name,
				"ress"		=> $ress,
				"width"		=> $width,
				"height"	=> $height
			);
		}
		
		
		public function fill($ress, $rgba) {
			$color = imagecolorallocatealpha($ress["ress"], $rgba["r"], $rgba["g"], $rgba["b"], $rgba["a"]);
			imagefill($ress["ress"], 0, 0, $color);
		}
		
		public function replace($ress, $x, $y, $rgba_to) {
			$buffer		= $this->duplicate($ress);
			imagetruecolortopalette($buffer["ress"],false, 255);
			$color		= imagecolorat($buffer["ress"], $x, $y);
			imagecolorset($buffer["ress"], $color, $rgba_to["r"], $rgba_to["g"], $rgba_to["b"]);
			return $buffer;
		}
		
		public function transparent($ress, $x, $y) {
			$buffer		= $this->duplicate($ress);
			imagetruecolortopalette($buffer["ress"],false, 255);
			$color		= imagecolorat($buffer["ress"], $x, $y);
			imagecolortransparent($buffer["ress"], $color);
			return $buffer;
		}
		
		function opacity($ress, $opacity) {
			$buffer		= $this->duplicate($ress);
			$img 		= &$buffer["ress"];
			if( !isset( $opacity ) ){
				return false;
			}
			$opacity /= 100;
		
			//get image width and height
			$w = imagesx( $img );
			$h = imagesy( $img );
		
			//turn alpha blending off
			imagealphablending( $img, false );
		
			//find the most opaque pixel in the image (the one with the smallest alpha value)
			$minalpha = 127;
			for( $x = 0; $x < $w; $x++ ) {
				for( $y = 0; $y < $h; $y++ ) {
					$alpha = ( imagecolorat( $img, $x, $y ) >> 24 ) & 0xFF;
					if( $alpha < $minalpha )
					{ $minalpha = $alpha; }
				}
			}
			//loop through image pixels and modify alpha for each
			for( $x = 0; $x < $w; $x++ ) {
				for( $y = 0; $y < $h; $y++ ){
					//get current alpha value (represents the TANSPARENCY!)
					$colorxy = imagecolorat( $img, $x, $y );
					$alpha = ( $colorxy >> 24 ) & 0xFF;
					//calculate new alpha
					if( $minalpha !== 127 ){
						$alpha = 127 + 127 * $opacity * ( $alpha - 127 ) / ( 127 - $minalpha );
					}
					else{
						$alpha += 127 * $opacity;
					}
					//get the color index with new alpha
					$alphacolorxy = imagecolorallocatealpha( $img, ( $colorxy >> 16 ) & 0xFF, ( $colorxy >> 8 ) & 0xFF, $colorxy & 0xFF, $alpha );
					//set pixel with the new color + opacity
					if( !imagesetpixel( $img, $x, $y, $alphacolorxy ) ){
						return false;
					}
				}
			}
			return $buffer;
		}
		
		public function file_get($url) {
			if (strpos($url,"://")===false) {
				return file_get_contents($url);
			} else {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				$output = curl_exec($ch);
				curl_close($ch);
				return $output;
			}
		}
		
		public function loadString($str) {
			
		    $ress = imagecreatefromstring($str);
		    imagealphablending($ress, true);
			imagesavealpha($ress, true);
			
			return array(
				"ress"		=> $ress,
				"width"		=> imagesx($ress),
				"height"	=> imagesy($ress)
			);
		}
		
		public function loadImage($filename) {
			$info = pathinfo($filename);
			switch($info["extension"]) {
			  case "jpg":
			  case "jpeg":
			    $ress = imagecreatefromjpeg($filename);
			    break;
			  case "gif":
			    $ress = imagecreatefromgif($filename);
			    break;
			  case "png":
			  default:
			    $ress = imagecreatefrompng($filename);
			    imagealphablending($ress, true);
				imagesavealpha($ress, true);
			    break;
			}
			list($width, $height, $type, $attr) = getimagesize($filename);
			
			return array(
				"ress"		=> $ress,
				"width"		=> $width,
				"height"	=> $height
			);
		}
		
		public function fit($ress, $mw, $mh, $scale=true, $resize=false) {
			$ress_ratio 	= $ress["width"] / $ress["height"];
			$box_ratio		= $mw / $mh;
			$diff = array(
				"width"			=> $ress["width"] / $mw,
				"height"		=> $ress["height"] / $mh
			);
			$diff_ratio		= $diff["width"] / $diff["height"];
			
			if ($ress_ratio > $box_ratio) {
				$scale_ratio	= $ress["width"] / $mw;
				$nw				= $mw;
				$nh				= $ress["height"] / $scale_ratio;
				$nx				= 0;
				$ny				= ($mh-$nh)/2;
			} else {
				$scale_ratio	= $ress["height"] / $mh;
				$nw				= $ress["width"] / $scale_ratio;
				$nh				= $mh;
				if (!$scale) {
					if ($nw > $ress["width"]) {
						$nw = $ress["width"];
					}
					if ($nh > $ress["height"]) {
						$nh = $ress["height"];
					}
				}
				$nx				= ($mw-$nw)/2;
				$ny				= 0;
			}
			// create the box
			if (!$resize) {
				$buffer 		= $this->createTransparentRessource($mw, $mh);
				imagealphablending($buffer, true);
				imagecopyresampled($buffer, $ress["ress"], $nx, $ny, 0, 0, $nw, $nh, $ress["width"], $ress["height"]);
				imagesavealpha($buffer,true);
				return array(
					"ress"		=> $buffer,
					"width"		=> $mw,
					"height"	=> $mh
				);
			} else {
				$buffer 		= $this->createTransparentRessource($nw, $nh);
				imagealphablending($buffer, true);
				imagecopyresampled($buffer, $ress["ress"], 0, 0, 0, 0, $nw, $nh, $ress["width"], $ress["height"]);
				imagesavealpha($buffer,true);
				return array(
					"ress"		=> $buffer,
					"width"		=> $nw,
					"height"	=> $nh
				);
			}
		}
		
		public function duplicate($ress) {
			$buffer 	= $this->createTransparentRessource($ress["width"], $ress["height"]);
			imagealphablending($buffer, true);
			imagecopy($buffer, $ress["ress"], 0, 0, 0, 0, $ress["width"], $ress["height"]);
			imagesavealpha($buffer,true);
			return array(
				"ress"		=> $buffer,
				"width"		=> $ress["width"],
				"height"	=> $ress["height"]
			);
		}
		
		public function applyFilter($ress, $filterName, $options=array()) {
			// create a copy
			$buffer		= $this->duplicate($ress);
			switch ($filterName) {
				case "grayscale":
				imagefilter($buffer["ress"], IMG_FILTER_GRAYSCALE);
				break;
				case "brightness":
				imagefilter($buffer["ress"], IMG_FILTER_BRIGHTNESS, $options["level"]);
				break;
				case "contrast":
				imagefilter($buffer["ress"], IMG_FILTER_CONTRAST, $options["level"]);
				break;
				case "smooth":
				imagefilter($buffer["ress"], IMG_FILTER_SMOOTH, $options["level"]);
				break;
				case "colorize":
				imagefilter($buffer["ress"], IMG_FILTER_COLORIZE, $options["r"], $options["g"], $options["b"], $options["a"]);
				break;
				case "negative":
				imagefilter($buffer["ress"], IMG_FILTER_NEGATE);
				break;
				case "edge":
				imagefilter($buffer["ress"], IMG_FILTER_EDGEDETECT);
				break;
				case "emboss":
				imagefilter($buffer["ress"], IMG_FILTER_EMBOSS);
				break;
				case "blur":
				imagefilter($buffer["ress"], IMG_FILTER_GAUSSIAN_BLUR);
				break;
				case "sketch":
				imagefilter($buffer["ress"], IMG_FILTER_MEAN_REMOVAL);
				break;
				case "pixelate":
				imagefilter($buffer["ress"], IMG_FILTER_PIXELATE, $options["size"], $options["advanced"]);
				break;
			}
			return $buffer;
		}
		
		public function generateQRCode($url, $width, $height, $margin=2, $eclevel='L') {
			$ggurl = "http://chart.apis.google.com/chart?chs=".$width."x".$height."&cht=qr&chld=".$eclevel."|".$margin."&chl=".urlencode($url);
			//$ress = $this->createTransparentRessource($width, $height);
			$ress = $this->loadString($this->file_get($ggurl));
			return $ress;
		}
		
		public function copy($ress, $layer, $x=0, $y=0) {
			imagealphablending($layer["ress"], true);
			imagecopy($layer["ress"], $ress["ress"], $x,$y,0,0,$ress["width"],$ress["height"]);
			imagesavealpha($layer["ress"],true);
		}
		
		private function createTransparentRessource($width=false, $height=false) {
			$ress = imagecreatetruecolor($width, $height);
			imagealphablending($ress, true);
			imagesavealpha($ress,true);
			$col = imagecolorallocatealpha($ress,255,255,255,127);
			imagefill($ress, 0, 0, $col);
			return $ress;
		}
		
		public function raster($filename=false) {
			$raster = $this->createTransparentRessource($this->canvas["width"], $this->canvas["height"]);
			foreach ($this->layers as $layer) {
				$coord = array();
				$coord["x"] 	= ($this->canvas["width"]-$layer["width"])/2;
				$coord["y"] 	= ($this->canvas["height"]-$layer["height"])/2;
				imagealphablending($raster, true);
				imagecopy($raster, $layer["ress"], $coord["x"],$coord["y"],0,0,$layer["width"],$layer["height"]);
				imagesavealpha($raster,true);
			}
			if ($filename) {
				imagepng($raster, $filename, 9, PNG_ALL_FILTERS);
			}
			return array(
				"ress"		=> $raster,
				"width"		=> $this->canvas["width"],
				"height"	=> $this->canvas["height"]
			);
		}
		
		public function export($ress, $filename) {
			imagepng($ress["ress"], $filename, 9, PNG_ALL_FILTERS);
		}
	}
?>
