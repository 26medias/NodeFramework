<?php
	
	Twig_Autoloader::register();
	
	
	/*function render_cached($dir, $file, $id=null) {
		$tpl = new RainTPL();
		$tpl->configure("tpl_dir", 	$dir);
		if	($cache = $tpl->cache($file, 84600, $id)) {
			echo "(cached)".$cache;
			return true;
		} else {
			return false;
		}
	}*/
	
	function render($options) {
		global $_CONF;
		
		array_push($options["require"], "template-".$_CONF["template"]);
		
		if (!isset($options["data"])) {
			$options["data"] = array();
		}
		foreach ($options["exclude"] as $excludeName) {
			foreach ($options["require"] as $req_index => $requireName) {
				if ($requireName == $excludeName) {
					array_splice($options["require"], $req_index, 1);
				}
			}
		}
		
		$options["data"]["conf"]	= $_CONF;
		$options["data"]["shared"]	= $_GET["__shared__"];
		
		//debug("options", $options);
		
		if (isset($options["template"])) {
			
			$options["return"] = true; // force return so we can include in the main template
			return __render(array(
				"dir"		=> "templates/",
				"file"		=> $options["template"],
				"data"		=> array(
					"conf"			=> $_CONF,
					"output"		=> __render($options),
					"shared"		=> $_GET["__shared__"],
					"includes"		=> clientside_include($options["require"]),
					"__child_data"	=> $options["data"]
				)
			));
		} else {
			return __render($options);
		}
	}
	
	function __render($options) {
		$loader = new Twig_Loader_Filesystem($options["dir"]);
		
		if ($_CONF["libsettings"]["server"]["Twig"]["cache"] == "on") {
			$twig = new Twig_Environment($loader, array(
				"cache" => "core/compiled/serverside/",
			));
		} else {
			$twig = new Twig_Environment($loader, array());
		}
		//$twig->addExtension(new Twig_Extension_Debug());
		
		
		$lexer = new Twig_Lexer($twig, array(
			'tag_comment'  => array('{*', '*}'),
			'tag_block'    => array('{{', '}}'),
			'tag_variable' => array('{$', '}'),
		));
		$twig->setLexer($lexer);
		
		if ($options["return"] == true) {
			return $twig->render($options["file"], $options["data"]);
		} else {
			echo $twig->render($options["file"], $options["data"]);
		}
	}
?>