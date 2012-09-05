<?php
	function params() {
		global $_GET, $_POST;
		$output = array();
		foreach ($_GET["__qs__"] as $key => $value) {
			$output[$key] = stripslashes($value);
		}
		foreach ($_POST as $key => $value) {
			$output[$key] = stripslashes($value);
		}
		foreach ($_GET as $key => $value) {
			$output[$key] = stripslashes($value);
		}
		return $output;
	}
	
	
	function create_token() {
		return md5(generateGUID());
	}
	
	function generateGUID() {
		return mt_rand(100000,900000);
	}

	function makePath($path) {
		if (!is_dir($path)) {
			$parts = explode("/",$path);
			$buffer = ".";
			foreach($parts as $part) {
				$buffer = $buffer."/".$part;
				if (!is_dir($buffer)) {
					$mk = mkdir($buffer);
				}
			}
		}
	}
	
	
	function share() {
		$_argv = func_get_args();
		$_argc = func_num_args();
		if (is_array($_argv[0])) {
			foreach ($_argv[0] as $var => $val) {
				$_GET["__shared__"][$var] = $val;
			}
		} else {
			$_GET["__shared__"][$_argv[0]] = $_argv[1];
		}
		
	}
		
	
	function debug($label, $data) {
		echo "<div style=\"margin-left: 40px;\"><u><h3>".$label."</h3></u><pre style=\"border-left:2px solid #000000;margin:10px;padding:4px;\">".print_r($data, true)."</pre></div>";
	}
	
	
	function location($path) {
		global $_CONF;
		header("Location: ".$_CONF["settings"]["base"].$path);
	}
	
	function clientside_include() {
		global $_CLIENTSIDELIBS, $_CONF;
		
		$cache_conf_clientsideincludes	= "core/compiled/php/clientsideincludes.php";
		
		$cache_dir 						= "core/compiled/clientside/";
		
		
		$_argv = func_get_args();
		$_argc = func_num_args();
		
		if (is_array($_argv[0])) {
			$_argv = $_argv[0];
		}
		
		$alphaLibs = $_argv;
		sort($alphaLibs, SORT_STRING);
		$_ID = md5(implode("_",$alphaLibs));
		
		
		$PUBLISH_IMAGES 				= "core/compiled/clientside/images/".$_ID."/";
		$PUBLISH_IMAGES_REL 			= "images/".$_ID."/";
		
		if (!is_dir($PUBLISH_IMAGES)) {
			mkdir($PUBLISH_IMAGES, 0777, true);
		}
		
		if (is_array($_CLIENTSIDELIBS[$_ID])) {
			// lib group already created
			if ($_CONF["settings"]["mode_debug"] == "off") {
				return $_CLIENTSIDELIBS[$_ID]["compiled"];
			} else {
				return $_CLIENTSIDELIBS[$_ID]["files"];
			}
		} else {
			$libs = system_getFullLibs($_argv);
			
			require_once("core/system_files/jsmin.php");
			require_once("core/system_files/cssmin.php");
			
			// change everything
			$_CLIENTSIDELIBS[$_ID] = array(
				"files"		=> array(
					"js"		=> array(),
					"css"		=> array()
				),
				"compiled"=> array(
					"js"		=> array(),
					"css"		=> array()
				)
			);
			foreach ($libs as $libArray) {
				foreach ($libArray["include"] as $fileArray) {
					if (!is_array($_CLIENTSIDELIBS[$_ID]["files"][$fileArray["type"]])) {
						$_CLIENTSIDELIBS[$_ID]["files"][$fileArray["type"]] = array();
					}
					$expl = explode("http://",$fileArray["file"]);
						if (count($expl) === 1) {
							array_push($_CLIENTSIDELIBS[$_ID]["files"][$fileArray["type"]], $fileArray["file"]);
						} else {
							array_push($_CLIENTSIDELIBS[$_ID]["files"][$fileArray["type"]], "http://".$expl[1]);
						}
				}
			}
			
			$bufferjs 	= "";
			$buffercss 	= "";
			
			// compress data
			foreach ($_CLIENTSIDELIBS[$_ID]["files"]["js"] as $file) {
				$expl = explode("http://",$file);
				if (count($expl) === 1) {
					$bufferjs .= "/**".$file."**/\n".JSMin::minify(file_get($file)).";;\n\n\n";
				} else {
					$bufferjs .= "/**".$file."**/\n".JSMin::minify(file_get("http://".$expl[1])).";;\n\n\n";
				}
			}
			foreach ($_CLIENTSIDELIBS[$_ID]["files"]["css"] as $file) {
				$expl = explode("http://",$file);
				if (count($expl) === 1) {
					$buffercss .= "/**".$file."**/\n".CssMin::minify(system_updateCssPaths($file, $PUBLISH_IMAGES, $PUBLISH_IMAGES_REL))."\n\n\n";
				} else {
					$buffercss .= "/**".$file."**/\n".CssMin::minify(system_updateCssPaths("http://".$expl[1], $PUBLISH_IMAGES, $PUBLISH_IMAGES_REL))."\n\n\n";
				}
			}
			
			$_CLIENTSIDELIBS[$_ID]["compiled"]["js"]	= array($cache_dir.$_ID.".js");
			$_CLIENTSIDELIBS[$_ID]["compiled"]["css"]	= array($cache_dir.$_ID.".css");
			
			file_put_contents($cache_dir.$_ID.".js", $bufferjs);
			file_put_contents($cache_dir.$_ID.".css", $buffercss);
			
			file_put_contents($cache_conf_clientsideincludes,"<?php\n\t\$_CLIENTSIDELIBS = ".array_to_phpArray($_CLIENTSIDELIBS).";\n?>");
		}
		
		if ($_CONF["settings"]["mode_debug"] == "off") {
			return $_CLIENTSIDELIBS[$_ID]["compiled"];
		} else {
			return $_CLIENTSIDELIBS[$_ID]["files"];
		}
	}
	
	
	function array_to_phpArray($a=false) 
	{
		if (is_null($a)) return 'null';
		if ($a === false) return 'false';
		if ($a === true) return 'true';
		if (is_scalar($a))
		{
			if (is_float($a))
			{
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}
	
			if (is_string($a))
			{
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else
			return $a;
		}
		$isList = true;
		for ($i = 0, reset($a); $i < count($a); $i++, next($a))
		{
			if (key($a) !== $i)
			{
				$isList = false;
				break;
			}
		}
		
		$result = array();
		if ($isList)
		{
			foreach ($a as $v) $result[] = array_to_phpArray($v);
			return 'array(' . join(',', $result) . ')';
		}
		else
		{
			foreach ($a as $k => $v) $result[] = array_to_phpArray($k).'=>'.array_to_phpArray($v);
			return 'array(' . join(',', $result) . ')';
		}
	}
	
	
	function file_get($url) {
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
	
	/**
	 * list the folders in a folder
	 * @param $dir
	 * @param $exclude
	 * @return array of folders
	 */
	function getDirAsArray($dir, $exclude=array()) {
		$output = array();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if (!in_array($file, $exclude) && is_dir($dir."/".$file)) {
					array_push($output, $file);
				}
			}
			closedir($handle);
		}
		return $output;
	}
	
	/**
	 * list the files in a folder
	 * @param $dir
	 * @param $exclude
	 * @return array of folders
	 */
	function getFileAsArray($dir, $ext=array()) {
		$output = array();
		if ($handle = opendir($dir)) {
			while (false !== ($file = readdir($handle))) {
				if (!is_dir($dir."/".$file)) {
					$path_parts = pathinfo($dir."/".$file);
					if (in_array($path_parts["extension"],$ext) || count($ext) == 0) {
						array_push($output, $file);
					}
				}
			}
			closedir($handle);
		}
		return $output;
	}
	
	
	
	
	function system_listTemplates($basedir) {
		$templates = getDirAsArray($basedir,array(".",".."));
		return $templates;
	}
	
	function system_listLibs($basedir) {
		$data = array();
		
		$phplibs = getDirAsArray($basedir,array(".",".."));
		
		$phplibsInfo = array();
		
		// get the list of libs by looking into the lib directory
		foreach($phplibs as $lib) {
			$info = system_getExtLibInfo($basedir,$lib);
			if ($info) {
				array_push($phplibsInfo,$info);
			}
		}
		// change the loading priorities by checking the dependencies
		$phplibsInfo = system_prioritizeExtLibs($phplibsInfo);
		
		// generate the array of files
		foreach($phplibsInfo as $lib) {
			// update the file path
			foreach ($lib["include"] as $fileIdx => $fileDescription) {
				//debug($basedir.$lib["path"]."/".$lib["files"][$fileIdx]["file"]);
				$lib["include"][$fileIdx]["file"] = $basedir.$lib["path"]."/".$lib["include"][$fileIdx]["file"];
			}
			array_push($data,$lib);
		}
		return $data;
	}
	
	/**
	 * Get the info about an ext lib
	 * @param $basedir
	 * @param $lib
	 * @return unknown_type
	 */
	function system_getExtLibInfo($basedir,$lib) {
		$confFile = $basedir.$lib."/lib.conf";
		$conf = false;
		// get the conf file as an array
		if (file_exists($confFile)) {
			$conf = json_decode(file_get($confFile),true);
		}
		// if the conf file exists
		if ($conf) {
			// register path name
			$conf["lib"]["path"] = $lib;
			return $conf["lib"];
		}
		return false;
	}
	
	/**
	 * reorganize the loading order of the libs, to take the priorities in consideration
	 * @param $libsArray
	 * @return unknown_type
	 */
	function system_prioritizeExtLibs($libsArray) {
		$changed = false;
		foreach ($libsArray as $libindex=>$lib) {
			if (isset($lib["require"]) && count($lib["require"]) > 0) {
				$minimalPosition = 0;
				foreach ($lib["require"] as $requiredLibName) {
					$libPosition = system_getLibPosition($libsArray, $requiredLibName);
					if ($libPosition === false) {
						//throw new Exception("Missing dependency - JS ExtLib [".$requiredLibName."]");
					}
					$minimalPosition = max($libPosition, $minimalPosition);
				}
				if ($libindex < $minimalPosition) {
					$changed = true;
					// remove the lib from the array
					array_splice($libsArray, $libindex, 1);
					
					// push back the lib at the right place
					array_splice($libsArray, $minimalPosition, 0, array($lib));
					return system_prioritizeExtLibs($libsArray);
				}
			}
		}
		return $libsArray;
	}
	
	
	function system_getLibRequirement($libsArray, $lib, $required=array()) {
		$new = array();
		
		array_push($required, $lib);
		
		$thislib = system_getLibById($libsArray, $lib);
		
		foreach ($thislib["require"] as $reqName) {
			if (!in_array($reqName, $required)) {
				array_push($new, $reqName);
				array_push($required, $reqName);
			}
		}
		
		foreach ($new as $newLib) {
			$required = system_getLibRequirement($libsArray, $newLib, $required);
		}
		return array_unique($required);
	}
	
	
	/**
	 * Find the index of a given libName in a libArray
	 * @param libsArray
	 * @param libName
	 * @return index of the lib
	 */
	function system_getLibPosition($libsArray, $libName) {
		foreach ($libsArray as $libindex => $lib) {
			if ($lib["id"] == $libName) {
				return $libindex;
			}
		}
		return false;
	}
	function system_getLibById($libsArray, $id) {
		
		foreach ($libsArray as $lib) {
			if ($lib["id"] == $id) {
				return $lib;
			}
		}
		return false;
	}
	
	
	function system_getFullLibs($libs) {
		$folder_libs_clientside		= "system/libs/clientside/";
		$libsArray 					= system_listLibs($folder_libs_clientside);
		$libsBuffer 				= array();
		
		$required 					= array();
		
		foreach ($libs as $libId) {
			$required = array_unique(array_merge($required, system_getLibRequirement($libsArray,$libId)));
		}
		//return $required;
		//return $libsArray;
		
		foreach ($required as $libName) {
			array_push($libsBuffer, system_getLibById($libsArray, $libName));
		}
		
		return system_prioritizeExtLibs($libsBuffer);
		//debug("test 1 :: jQuery",system_getFullLibs(array("jQuery")));
		//debug("test 2 :: Attachment",system_getFullLibs(array("Attachment")));
		//debug("test 3 :: Tasks",system_getFullLibs(array("Tasks")));
		//debug("test 4 :: Attachment,Tags",system_getFullLibs(array("Attachment","Tags")));
	}
	
	/* CSS RELOCATOR */
	class css_relocator {
		public function __construct($filename, $copydir, $to) {
			$this->filename 	= $filename;
			$this->to 			= $to;
			$this->copydir 		= $copydir;
			$this->content 		= file_get($filename);
			//debug("filename",$this->filename);
			$this->update();
		}
		public function getBasename($path) {
			$from_info 	= pathinfo($path);
			return $from_info["basename"];
		}
		public function getExt($path) {
			$from_info 	= pathinfo($path);
			return $from_info["extension"];
		}
		public function getFilename($path) {
			$from_info 	= pathinfo($path);
			return $from_info["filename"];
		}
		public function getDirname($path) {
			$from_info 	= pathinfo($path);
			return $from_info["dirname"];
		}
		public function update() {
			$this->content = preg_replace_callback("#url\(\s*[\"\']*([a-zA-Z0-9_:\/\-\.\s\/]+)[\"\']*\s*\)#", array("self","makeCleanURL"), $this->content);
			//$this->content = preg_replace_callback("#url\(\s*([a-zA-Z0-9_\/\-\.\s\/]+)\s*\)#", array("self","makeCleanURL"), $this->content);
			$this->content = preg_replace_callback("#src\s*\=\s*[\"\']([a-zA-Z0-9_\/\-\.\s\/]+)[\"\']#", array("self","makeCleanSRC"), $this->content);
		}
		public function makeCleanURL($input) {
			//debug("strpos -> ".$input[1], strpos($input[1], "://"));
			if (strpos($input[1], "://") !== false) {
				file_put_contents($this->copydir.md5($input[1]).".".$this->getExt($input[1]),file_get($input[1]));
			} else {
				copy($this->getDirname($this->filename)."/".$input[1],$this->copydir.md5($input[1]).".".$this->getExt($input[1]));
			}
			return "url('".$this->to.md5($input[1]).".".$this->getExt($input[1])."')";
		}
		public function makeCleanSRC($input) {
			return "src='".$this->to.md5($input[1]).".".$this->getExt($input[1])."'";
		}
		public function output() {
			return $this->content;
		}
	}
	
	function system_updateCssPaths($content, $copydir, $to) {
		$cssReloc	= new css_relocator($content, $copydir, $to);
		return $cssReloc->output();
	}
	
	
	function copy_directory( $source, $destination ) {
		if ( is_dir( $source ) ) {
			@mkdir( $destination );
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) {
				if ( $readdirectory == '.' || $readdirectory == '..' ) {
					continue;
				}
				$PathDir = $source . '/' . $readdirectory;
				if ( is_dir( $PathDir ) ) {
					copy_directory( $PathDir, $destination . '/' . $readdirectory );
					continue;
				}
				copy( $PathDir, $destination . '/' . $readdirectory );
			}
	
			$directory->close();
		}else {
			copy( $source, $destination );
		}
	}
	
	function copy_remote($from, $to, $wt="w") {
		if (strpos($from,"://")===false) {
			copy($from, $to);
		} else {
			// get the args
			$url = parse_url($from);
			parse_str($url["query"], $__qs);
			$u = explode("?",$from);
			
			$newurl = $u[0];
			
			$newurl = str_replace(" ","%20",$newurl);
			
			$fp 	= fopen($to, $wt);
			
			$ch 	= curl_init($newurl);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, count($__qs));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $__qs);
			$data 	= curl_exec($ch);
			
			curl_close($ch);
			fclose($fp);
		}
	}
	
	
	function system_registerServersideLib($id) {
		$here							= "core/compiled/php/";
		$cache_serverside_libs			= $here."cache_serverside_libs.json";
		$cache_serverside_includes		= $here."common.php";
		$folder_libs_serverside			= "system/libs/serverside/";
		
		$serversideLibs 				= json_decode(file_get($cache_serverside_libs),true);
		
		
		
		array_push($serversideLibs["libs"], $id);
		
		//debug("serversideLibs", $serversideLibs["libs"]);
		
		$serversideLibs["libs"] 	= array_unique($serversideLibs["libs"]);
		
		$libs_serverside 			= system_listLibs($folder_libs_serverside);
		
		//debug("libs_serverside", $libs_serverside);
		
		$libs = array();
		foreach ($serversideLibs["libs"] as $libName) {
			foreach ($libs_serverside as $libArray) {
				if ($libArray["id"] == $libName) {
					array_push($libs, $libArray);
				}
			}
		}
		
		
		$libs 	= system_prioritizeExtLibs($libs);
		$temp_php_buffer		= "";
		
		//debug("libs", $libs);
		
		foreach($libs as $libArray) {
			$temp_php_buffer .= "// lib :: ".$libArray["id"]." (/".$libArray["path"].")\n";
			foreach ($libArray["include"] as $fileArray) {
				$temp_php_buffer .= "\trequire_once(\"".$fileArray["file"]."\");\n";
			}
		}
		file_put_contents($cache_serverside_libs, json_encode($serversideLibs));
		file_put_contents($cache_serverside_includes, "<?php\n".$temp_php_buffer."?>");
	}
	
	
	function system_unregisterServersideLib($id) {
		$here							= "core/compiled/php/";
		$cache_serverside_libs			= $here."cache_serverside_libs.json";
		$cache_serverside_includes		= $here."common.php";
		$folder_libs_serverside			= "system/libs/serverside/";
		
		$serversideLibs 				= json_decode(file_get($cache_serverside_libs),true);
		
		$newArray = array();
		foreach ($serversideLibs["libs"] as $libname) {
			if ($libname != $id) {
				array_push($newArray, $libname);
			}
		}
		
		$serversideLibs["libs"]		= $newArray;
		
		$serversideLibs["libs"] 	= array_unique($serversideLibs["libs"]);
		
		$libs_serverside 			= system_listLibs($folder_libs_serverside);
		//debug("libs_serverside", $libs_serverside);
		$libs = array();
		foreach ($serversideLibs["libs"] as $libName) {
			foreach ($libs_serverside as $libArray) {
				if ($libArray["id"] == $libName) {
					array_push($libs, $libArray);
				}
			}
		}
		
		
		$libs 	= system_prioritizeExtLibs($libs);
		$temp_php_buffer		= "";
		
		//debug("libs", $libs);
		
		foreach($libs as $libArray) {
			$temp_php_buffer .= "// lib :: ".$libArray["id"]." (/".$libArray["path"].")\n";
			foreach ($libArray["include"] as $fileArray) {
				$temp_php_buffer .= "\trequire_once(\"".$fileArray["file"]."\");\n";
			}
		}
		file_put_contents($cache_serverside_libs, json_encode($serversideLibs));
		file_put_contents($cache_serverside_includes, "<?php\n".$temp_php_buffer."?>");
	}
	
	function system_registerClientsideLib($id) {
		$here							= "core/compiled/php/";
		$cache_clientside_libs			= $here."cache_clientside_libs.json";
		$clientsideLibs 				= json_decode(file_get($cache_clientside_libs),true);
		
		array_push($clientsideLibs["libs"], $id);
		
		$clientsideLibs["libs"] 	= array_unique($clientsideLibs["libs"]);
		file_put_contents($cache_clientside_libs, json_encode($clientsideLibs));
	}
	
	
	function system_uncompressPackedFile($source) {
		global $_CONF;
		
		$time			= time();
		$tempPath 		= 'misc/temp/store/'.$time."/";
		$tempFilename 	= $tempPath."temp.app";
		makePath($tempPath);
		
		copy_remote($source, $tempFilename, "wb");
		
		$fileInfo = pathinfo($tempFilename);
		
		$zipFile = $tempFilename;
		
		$zip = new PclZip($zipFile);
		if ($zip->extract(PCLZIP_OPT_PATH, $tempPath) == 0) {
			die("Error : ".$zip->errorInfo(true));
		}
		
		return $tempPath;
	}
	
	function system_uncompressLocalPackedFile($source) {
		global $_CONF;
		
		$info = pathinfo($source);
		$path = $info["dirname"]."/".$info["filename"]."/";
		mkdir($path);
		
		
		$zip = new PclZip($source);
		if ($zip->extract(PCLZIP_OPT_PATH, $path) == 0) {
			die("Error : ".$zip->errorInfo(true));
		}
		
		return $path;
	}
	
	function system_resetCache() {
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
	}
	
	function system_saveConf() {
		global $_CONF;
		$confFile = "core/compiled/php/conf.php";
		// reset theme swap
		if (isset($_CONF["original_template"]) && $_CONF["original_template"] != false && $_CONF["original_template"] != "") {
			$_CONF["template"] 			= $_CONF["original_template"];
			$_CONF["original_template"] = false;
			$tpl_swap = true;
		}
		file_put_contents($confFile,"<?php\n\t\$_CONF=".array_to_phpArray($_CONF).";\n?>");
		if ($tpl_swap) {
			$_CONF["original_template"] = $_CONF["template"];
			$_CONF["template"] 			= $_CONF["admtemplate"];
		}
	}
	
	function system_activateTheme($name) {
		global $_CONF;
		$_CONF["template"] = $name;
		system_saveConf();
	}
	function system_activateAdminTheme($name) {
		global $_CONF;
		$_CONF["admtemplate"] = $name;
		system_saveConf();
	}
	
	function system_install($filename) {
		$tempPath = system_uncompressPackedFile($filename);
		
		$return 			= array();
		$return["error"] 	= false;
		
		if ($tempPath === false) {
			$return["error"] = true;
			return $return;
		}
		
		$installConf = json_decode(file_get($tempPath."install.conf"),true);
		
		$return["conf"] = $installConf;
		
		if (isset($installConf["copy"])) {
			// things to copy
			if (isset($installConf["copy"]["directories"])) {
				foreach($installConf["copy"]["directories"] as $copyDetails) {
					copy_directory($tempPath.$copyDetails[0], $copyDetails[1]);
				}
			}
			if (isset($installConf["copy"]["files"])) {
				foreach($installConf["copy"]["files"] as $copyDetails) {
					copy($tempPath.$copyDetails[0], $copyDetails[1]);
				}
			}
		}
		
		if (isset($installConf["register"])) {
			// libs to register
			if (isset($installConf["register"]["serverside"])) {
				foreach($installConf["register"]["serverside"] as $registerDetails) {
					system_registerServersideLib($registerDetails);
				}
			}
			if (isset($installConf["register"]["clientside"])) {
				foreach($installConf["register"]["clientside"] as $registerDetails) {
					system_registerClientsideLib($registerDetails);
				}
			}
		}
		if (isset($installConf["activate"])) {
			if (isset($installConf["activate"]["template"])) {
				system_activateTheme($installConf["activate"]["template"]);
			}
		}
		
		
		
		return $return;
	}
	
	function system_verifyPack($filename) {
		$tempPath = system_uncompressLocalPackedFile($filename);
		
		$return 			= array();
		$return["error"] 	= false;
		
		if ($tempPath === false) {
			$return["error"] = true;
			return $return;
		}
		
		$descriptorExists = file_exists($tempPath."install.conf");
		if (!$descriptorExists) {
			return array(
				"error"		=> true,
				"invalid"	=> true,
				"messages"	=> "install.conf non existant"	
			);
		}
		
		$installConf = json_decode(file_get($tempPath."install.conf"),true);
		
		$validator = system_packDescriptorValidator($installConf);
		if ($validator["error"]) {
			$return["error"] = true;
			$return["messages"] = $validator["messages"];
		} else {
			$return["meta"] = $installConf["meta"];
		}
		
		return $return;
	}
	
	function system_packDescriptorValidator($data) { // data:Array -> json_decode(install.conf)
		$messages 	= array();
		$return 	= array();
		if (!isset($installConf["meta"])) {
			array_push($message, "meta");
		}
		if (!isset($installConf["copy"])) {
			array_push($message, "copy");
		}
		if (!isset($installConf["meta"]["name"])) {
			array_push($message, "meta.name");
		}
		if (!isset($installConf["meta"]["author"])) {
			array_push($message, "meta.author");
		}
		if (!isset($installConf["meta"]["version"])) {
			array_push($message, "meta.version");
		}
		if (!isset($installConf["meta"]["contact"])) {
			array_push($message, "meta.contact");
		}
		if (count($messages) == 0) {
			$return["error"] = false;
		} else {
			$return["error"] 	= true;
			$return["messages"] = $messages;
		}
	}
	
	function system_getServersideLibList() {
		$here							= "core/compiled/php/";
		$cache_serverside_libs			= $here."cache_serverside_libs.json";
		$serversideLibs 				= json_decode(file_get($cache_serverside_libs),true);
		return $serversideLibs["libs"];
	}
	
	function getIP() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else {
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	
	function initAdmin($options) {
		global $_CONF;
		if (!$_SESSION["admin"] && !isset($options["islogin"])) {
			location("site-admin/login");
		}
		
		// list apps
		$apps 		= array();
		$apps_dir 	= getDirAsArray("apps", array("..","."));
		foreach ($apps_dir as $appdir) {
			$fileappid 	= "apps/".$appdir."/app.id";
			$fileadmin 	= "apps/".$appdir."/admin.conf";
			if (file_exists($fileappid) && file_exists($fileadmin)) {
				$appid 		= json_decode(file_get($fileappid), true);
				$appadmin 	= json_decode(file_get($fileadmin), true);
				$apps[$appid["name"]] = array(
					"id"	=> $appdir,
					"info"	=> $appid,
					"admin"	=> $appadmin,
					"pages"	=> count($appadmin)
				);
			}
		}
		
		// list themes
		$themes 		= array();
		$themes_dir 	= getDirAsArray("templates", array("..","."));
		foreach ($themes_dir as $themedir) {
			$fileappid 	= "templates/".$themedir."/theme.id";
			$fileadmin 	= "templates/".$themedir."/admin.conf";
			if (file_exists($fileappid)) {
				$themeid 		= json_decode(file_get($fileappid), true);
				if ($themeid["admintpl"]) {
					continue;
				}
				$themes[$themeid["name"]] = array(
					"id"	=> $themedir,
					"info"	=> $themeid
				);
				if (file_exists($fileadmin)) {
					$themeadmin 	= json_decode(file_get($fileadmin), true);
					$themes[$themeid["name"]]["admin"]	= $themeadmin;
					$themes[$themeid["name"]]["pages"]	= count($themeadmin);
				}
				
			}
		}
		
		// list admin themes
		$admthemes 		= array();
		$admthemes_dir 	= getDirAsArray("templates", array("..","."));
		foreach ($admthemes_dir as $themedir) {
			$fileappid 	= "templates/".$themedir."/theme.id";
			$fileadmin 	= "templates/".$themedir."/admin.conf";
			if (file_exists($fileappid)) {
				$themeid 		= json_decode(file_get($fileappid), true);
				if (!$themeid["admintpl"]) {
					continue;
				}
				$admthemes[$themeid["name"]] = array(
					"id"	=> $themedir,
					"info"	=> $themeid
				);
				if (file_exists($fileadmin)) {
					$themeadmin 	= json_decode(file_get($fileadmin), true);
					$admthemes[$themeid["name"]]["admin"]	= $themeadmin;
					$admthemes[$themeid["name"]]["pages"]	= count($themeadmin);
				}
				
			}
		}
		
		// list serverside Libs
		$libs 		= array();
		$libs_dir 	= getDirAsArray("system/libs/serverside", array("..","."));
		foreach ($libs_dir as $libdir) {
			$filelibid 	= "system/libs/serverside/".$libdir."/lib.conf";
			$fileadmin 		= "system/libs/serverside/".$libdir."/admin.conf";
			if (file_exists($fileappid) /*&& file_exists($fileadmin)*/) {
				$libid 		= json_decode(file_get($filelibid), true);
				$libs[$libid["lib"]["id"]] = array(
					"id"	=> $libdir,
					"info"	=> $libid,
					"pages"	=> count($libadmin)
				);
				if (file_exists($fileadmin)) {
					$libadmin 	= json_decode(file_get($fileadmin), true);
					$libs[$libid["lib"]["id"]]["admin"] = $libadmin;
				}
			}
		}
		
		// list clientside Libs
		$clibs 		= array();
		$libs_dir 	= getDirAsArray("system/libs/clientside", array("..","."));
		foreach ($libs_dir as $libdir) {
			$filelibid 	= "system/libs/clientside/".$libdir."/lib.conf";
			$fileadmin 		= "system/libs/clientside/".$libdir."/admin.conf";
			if (file_exists($fileappid) /*&& file_exists($fileadmin)*/) {
				$libid 		= json_decode(file_get($filelibid), true);
				$clibs[$libid["lib"]["id"]] = array(
					"id"	=> $libdir,
					"info"	=> $libid,
					"pages"	=> count($libadmin)
				);
				if (file_exists($fileadmin)) {
					$libadmin 	= json_decode(file_get($fileadmin), true);
					$clibs[$libid["lib"]["id"]]["admin"] = $libadmin;
				}
			}
		}
		
		// correct icon path
		foreach ($apps as $idx => $app) {
			if (isset($apps[$idx]["info"]["icon"])) {
				$apps[$idx]["info"]["icon"] = "apps/".$apps[$idx]["id"]."/".$apps[$idx]["info"]["icon"];
			} else {
				$apps[$idx]["info"]["icon"] = "system/misc/defaulticon.png";
			}
			foreach ($app["admin"] as $idx2 => $page) {
				$apps[$idx]["admin"][$idx2]["icon"] = "apps/".$apps[$idx]["id"]."/admin/".$apps[$idx]["admin"][$idx2]["icon"];
			}
		}
		foreach ($themes as $idx => $theme) {
			if (isset($themes[$idx]["info"]["icon"])) {
				$themes[$idx]["info"]["icon"] = "templates/".$themes[$idx]["id"]."/".$themes[$idx]["info"]["icon"];
			} else {
				$themes[$idx]["info"]["icon"] = "system/misc/defaulticon.png";
			}
			foreach ($theme["admin"] as $idx2 => $page) {
				$themes[$idx]["admin"][$idx2]["icon"] = "templates/".$themes[$idx]["id"]."/admin/".$themes[$idx]["admin"][$idx2]["icon"];
			}
		}
		foreach ($admthemes as $idx => $theme) {
			if (isset($admthemes[$idx]["info"]["icon"])) {
				$admthemes[$idx]["info"]["icon"] = "templates/".$admthemes[$idx]["id"]."/".$admthemes[$idx]["info"]["icon"];
			} else {
				$admthemes[$idx]["info"]["icon"] = "system/misc/defaulticon.png";
			}
			foreach ($theme["admin"] as $idx2 => $page) {
				$admthemes[$idx]["admin"][$idx2]["icon"] = "templates/".$admthemes[$idx]["id"]."/admin/".$admthemes[$idx]["admin"][$idx2]["icon"];
			}
		}
		foreach ($libs as $idx => $lib) {
			if (isset($libs[$idx]["info"]["icon"])) {
				$libs[$idx]["info"]["icon"] = "system/libs/serverside/".$libs[$idx]["id"]."/".$libs[$idx]["info"]["icon"];
			} else {
				$libs[$idx]["info"]["icon"] = "system/misc/defaulticon.png";
			}
			foreach ($lib["admin"] as $idx2 => $page) {
				$libs[$idx]["admin"][$idx2]["icon"] = "system/libs/serverside/".$libs[$idx]["id"]."/admin/".$libs[$idx]["admin"][$idx2]["icon"];
			}
		}
		foreach ($clibs as $idx => $lib) {
			if (isset($clibs[$idx]["info"]["icon"])) {
				$clibs[$idx]["info"]["icon"] = "system/libs/clientside/".$clibs[$idx]["id"]."/".$clibs[$idx]["info"]["icon"];
			} else {
				$clibs[$idx]["info"]["icon"] = "system/misc/defaulticon.png";
			}
			foreach ($lib["admin"] as $idx2 => $page) {
				$clibs[$idx]["admin"][$idx2]["icon"] = "system/libs/clientside/".$clibs[$idx]["id"]."/admin/".$clibs[$idx]["admin"][$idx2]["icon"];
			}
		}
		
		$_GET["__shared__"]["admin"] = array(
			"apps" 		=> $apps,
			"themes" 	=> $themes,
			"admthemes" 	=> $admthemes,
			"libs" 		=> $libs,
			"clibs" 	=> $clibs
		);
		
		// swpat the themes
		$_CONF["original_template"] = $_CONF["template"];
		$_CONF["template"]			= $_CONF["admtemplate"];
		//debug("admin", $_GET["__shared__"]["admin"]);
	}
	
	function getVars() {
		global $_CONF;
		return $_CONF["vars"];
	}
	
	function saveVars() {
		global $_CONF;
		$_CONF["vars"] = $_GET["__shared__"]["vars"];
		system_saveConf();
	}
	
    /**
     * setVar(namespace, var, val)
     * setVar(namespace, array("var" => "val"))
     * 
     */
	function setVar() {
		
		$_argv = func_get_args();
		$_argc = func_num_args();
		
		// load vars
		$vars = getVars();
		if (!$_GET["__shared__"]["vars"]) {
			$_GET["__shared__"]["vars"] = array();
		}
		foreach ($vars as $varNamespace => $varArray) {
			$_GET["__shared__"]["vars"][$varNamespace] = $varArray;
		}
		
		// Register namespace if not existing
		if (!$_GET["__shared__"]["vars"][$_argv[0]]) {
			$_GET["__shared__"]["vars"][$_argv[0]] = array();
		}
		if ($_argc == 2) {
			if (is_array($_argv[1])) {
				foreach ($_argv[1] as $var => $val) {
					$_GET["__shared__"]["vars"][$_argv[0]][$var] = $val;
				}
			}
		} elseif ($_argc == 3) {
			$_GET["__shared__"]["vars"][$_argv[0]][$_argv[1]] = $_argv[2];
		}
		
		// save vars
		
		saveVars();
	}
	
	
	function system_getPages() {
		$pages 		= array();
		$apps_dir 	= getDirAsArray("apps", array("..","."));
		foreach ($apps_dir as $appdir) {
			$fileappid 	= "apps/".$appdir."/app.id";
			if (file_exists($fileappid)) {
				$appid 		= json_decode(file_get($fileappid), true);
				foreach ($appid["pages"] as $pageData) {
					array_push($pages, $pageData);
				}
			}
		}
		return $pages;
	}
	
	
	
class Rijndael
{
    private $key,$iv_size,$iv;

    /**
     * constructor
     * @param $key (string:'TheKey')
     * @return void
     */
    function __construct($key='TheKey'){
        $this->iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $this->iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
        $this->key = trim($key);
    }

    public function encrypt($string){
        $string=trim($string);
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->key, $string, MCRYPT_MODE_ECB, $this->iv));
    }

    public function decrypt($string){
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$this->key,base64_decode($string),MCRYPT_MODE_ECB,$this->iv));
    }
} 
?>