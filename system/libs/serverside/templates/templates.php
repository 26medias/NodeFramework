<?php

	function tpl_setPageTitle($string, $showTitle=false) {
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["hastitle"] 	= true;
		$_GET["__shared__"]["template"]["showTitle"] 	= $showTitle;
		$_GET["__shared__"]["template"]["title"] 		= $string;
	}
	
	function tpl_setMainPage() {
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["isMain"] 	= true;
	}
	function tpl_isMultipage() {
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["multipage"] 	= true;
	}
	
	function tpl_setPageDescription($string) {
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["hasdescription"] 	= true;
		$_GET["__shared__"]["template"]["description"] 		= $string;
	}
	
	function tpl_useSlider($name) { 
		global $_CONF;
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["hasSlider"] 	= true;
		$_GET["__shared__"]["template"]["slider"] 		= $_CONF["template"]."/sliders/".$name.".html";
	}
	
	function tpl_useBanner($name) { 
		global $_CONF;
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["hasBanner"] 	= true;
		$_GET["__shared__"]["template"]["banner"] 		= $_CONF["template"]."/banners/".$name.".html";
	}
	
?>