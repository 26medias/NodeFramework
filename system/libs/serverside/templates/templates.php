<?php

	function tpl_setPageTitle($string) { // array
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["hastitle"] = true;
		$_GET["__shared__"]["template"]["title"] = $string;
	}
	function tpl_setPageDescription($string) { // array
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["hasdescription"] = true;
		$_GET["__shared__"]["template"]["description"] = $string;
	}
	
	
	function tpl_setCurrentPage($id) { // array
		if (!$_GET["__shared__"]["template"]) {
			$_GET["__shared__"]["template"] = array();
		}
		$_GET["__shared__"]["template"]["currentpage"] = $id;
	}
	
?>