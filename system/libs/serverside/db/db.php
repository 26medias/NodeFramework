<?php
/************************************************************************/
/* OSYWES: Advanced Content Management System                           */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004 by Jean-Michel WYTTENBACH                         */
/* http://www.webjamy.com                                               */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/*                                                                      */
/************************************************************************/

function useDataBase() {
	global $_DB,$_CONF;
	if (empty($_DB)) {
		$_DB = new sql_db($_CONF["libsettings"]["server"]["MySQL"]["server"], $_CONF["libsettings"]["server"]["MySQL"]["login"], $_CONF["libsettings"]["server"]["MySQL"]["password"], $_CONF["libsettings"]["server"]["MySQL"]["dbname"], false);
	}
	if(!$_DB->db_connect_id) {
	    die("<br><br><center><b>There seems to be a problem with the MySQL server, sorry for the inconvenience.<br><br>We should be back shortly.</center></b>");
	}
}
useDataBase();

?>