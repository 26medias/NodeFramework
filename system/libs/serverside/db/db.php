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
		$_DB = new sql_db($_CONF["vars"]["mysql"]["host"], $_CONF["vars"]["mysql"]["user"], $_CONF["vars"]["mysql"]["password"], $_CONF["vars"]["mysql"]["dbname"], false);
	}
	if(!$_DB->db_connect_id) {
	    echo("<br><br><center><b>Veuillez verifier les parametres de la connexion MySQL.</center></b>");
	}
}
useDataBase();

?>