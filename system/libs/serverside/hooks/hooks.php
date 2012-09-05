<?php
	$hook_list = array();
	
	function hook_create($event, $data, $return=false) {
		global $hook_list;
		if (!array_key_exists($event, $hook_list)) {
			return false;
		}
		foreach ($hook_list[$event] as $callback) {
			$buffer = call_user_func($callback, $data);
			if ($return) {
				return $buffer;
			}
		}
		return true;
		
	}
	function hook_subscribe($event, $callback) {
		global $hook_list;
		if (!array_key_exists($event, $hook_list)) {
			$hook_list[$event] = array();
		}
		array_push($hook_list[$event], $callback);
		return true;
	}
?>
