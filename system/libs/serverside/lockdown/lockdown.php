<?php
	function lockdown() {
		global $_CONF;
		if ($_SESSION["login"] != $_CONF["libsettings"]["server"]["Lockdown"]["login"] && $_SESSION["password"] != $_CONF["libsettings"]["server"]["Lockdown"]["password"]) {
			header("Location: ".$_CONF["libsettings"]["server"]["Lockdown"]["redirect"]);
			die();
		}
	}
?>