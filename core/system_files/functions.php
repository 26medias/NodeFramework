<?php
	function params() {
		global $_GET, $_POST;
		$output = array();
		foreach ($_GET["__qs__"] as $key => $value) {
			$output[$key] = $value;
		}
		foreach ($_POST as $key => $value) {
			$output[$key] = $value;
		}
		foreach ($_GET as $key => $value) {
			$output[$key] = $value;
		}
		return $output;
	}
	
	function debug($label, $data) {
		echo "<div style=\"margin-left: 40px;\"><u><h3>".$label."</h3></u><pre style=\"border-left:2px solid #000000;margin:10px;padding:4px;\">".print_r($data, true)."</pre></div>";
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
					$bufferjs .= JSMin::minify(file_get($file));
				} else {
					$bufferjs .= JSMin::minify(file_get("http://".$expl[1]));
				}
			}
			foreach ($_CLIENTSIDELIBS[$_ID]["files"]["css"] as $file) {
				$expl = explode("http://",$file);
				if (count($expl) === 1) {
					$buffercss .= CssMin::minify(system_updateCssPaths($file, $PUBLISH_IMAGES, $PUBLISH_IMAGES_REL));
				} else {
					$buffercss .= CssMin::minify(system_updateCssPaths("http://".$expl[1], $PUBLISH_IMAGES, $PUBLISH_IMAGES_REL));
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
			$this->content = preg_replace_callback("#url\(\s*[\"\']([a-zA-Z0-9_\/\-\.\s\/]+)[\"\']\s*\)#", array("self","makeCleanURL"), $this->content);
			$this->content = preg_replace_callback("#url\(\s*([a-zA-Z0-9_\/\-\.\s\/]+)\s*\)#", array("self","makeCleanURL"), $this->content);
			$this->content = preg_replace_callback("#src\s*\=\s*[\"\']([a-zA-Z0-9_\/\-\.\s\/]+)[\"\']#", array("self","makeCleanSRC"), $this->content);
		}
		public function makeCleanURL($input) {
			copy($this->getDirname($this->filename)."/".$input[1],$this->copydir.md5($input[1]).".".$this->getExt($input[1]));
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
	
	
	function system_registerServersideLib($id) {
		$here							= "core/compiled/php/";
		$cache_serverside_libs			= $here."cache_serverside_libs.json";
		$cache_serverside_includes		= $here."common.php";
		$folder_libs_serverside			= "system/libs/serverside/";
		
		$serversideLibs 				= json_decode(file_get($cache_serverside_libs),true);
		
		
		
		array_push($serversideLibs["libs"], $id);
		
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
	
	function system_setTemplate($id) {
		global $_CONF;
		$here							= "core/compiled/php/";
		$conf_file						= $here."conf.php";
		$cache_settings					= $here."cache_settings.json";
		$_CONF["template"]				= $id;
		$settings						= json_decode(file_get($cache_settings),true);
		$settings["template"]			= $id;
		
		file_put_contents($conf_file, 		"<?php\n\t\$_CONF=".array_to_phpArray($_CONF).";\n?>");
		file_put_contents($cache_settings, 	json_encode($settings));
	}
	
	
	function system_uncompressPackedFile($source) {
		global $_CONF;
		
		$time			= time();
		$tempPath 		= 'misc/temp/store/'.$time."/";
		$tempFilename 	= $tempPath."temp.app";
		mkdir($tempPath);
		
		copy_remote($source, $tempFilename, "wb");
		
		$fileInfo = pathinfo($tempFilename);
		
		$zipFile = $tempFilename;
		
		$zip = new PclZip($zipFile);
		if ($zip->extract(PCLZIP_OPT_PATH, $tempPath) == 0) {
			die("Error : ".$zip->errorInfo(true));
		}
		
		return $tempPath;
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
			// template to activate
			if (isset($installConf["activate"]["template"])) {
				system_setTemplate($installConf["activate"]["template"]);
			}
		}
		
		return $return;
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