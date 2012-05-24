<?php

function db_builder($options) {
	/*
		"tables"	=> array(
			"tasks"		=> "tasks",
			"tasks_tags"=> "tags",
		),
		"fields"	=> array(
			"tasks.id",
			"tasks.creation_date",
			"tasks.uid",
			"tasks.title",
			"tasks.text"
		),
		"where"		=> array(
			"tags.task_id=tasks.id"
		),
		"order"		=> array(
			"tasks.id desc"
		),
		"limit"		=> "0,20",
		"group"		=> "tasks.id"
	*/
	/* Options
		- tables
		- fields
		- where
		- order
		- limit
		- group
	*/
	$processed = array();
	$processed["fields"] = $options["fields"];
	$processed["tables"] = array();
	$processed["innerjoin"] = array();
	
	if (is_array($options["tables"])) {
		foreach ($options["tables"] as $table_name => $table_alias) {
			array_push($processed["tables"], $table_name." as ".$table_alias);
		}
	}
	//$processed["innerjoin"] = $options["innerjoin"];
	/*
	New syntax:
			"innerjoin"	=> array(
				"tables"	=> array(
					"tasks"		=> "t",
					"tag_ties"	=> "ties"
				),
				"on"	=> "t.id = ties.ref_id"
			),
	*/
	if (is_array($options["innerjoin"])) {
		$tmp = array();
		foreach ($options["innerjoin"] as $joinStructure) {
			foreach ($joinStructure["tables"] as $table_name => $table_alias) {
				array_push($tmp, $table_name." as ".$table_alias);
			}
			array_push($processed["innerjoin"],implode(" inner join ", $tmp)." on ".$joinStructure["on"]);
		}
	}
	if (count($processed["innerjoin"]) > 0) {
		array_push($processed["tables"], implode(",", $processed["innerjoin"]));
	}
	
	$processed["on"] 		= $options["on"];
	$processed["where"] 	= $options["where"];
	$processed["order"] 	= $options["order"];
	$processed["group"] 	= $options["group"];
	$processed["limit"] 	= $options["limit"];
	$processed["having"] 	= $options["having"];
	
	$query = "select ".implode(",", $processed["fields"])." from ".implode(",", $processed["tables"]);
	
	if (array_key_exists('on', $options)) {
		$query .= " on ".$processed["on"];
	}
	if (array_key_exists('where', $options)) {
		$query .= " where ".implode(" and ", $processed["where"]);
	}
	if (array_key_exists('group', $options)) {
		$query .= " group by ".$processed["group"];
	}
	if (array_key_exists('having', $options)) {
		$query .= " having ".$processed["having"];
	}
	if (array_key_exists('order', $options)) {
		$query .= " order by ".implode(",", $processed["order"]);
	}
	if (array_key_exists('limit', $options)) {
		$query .= " limit ".$processed["limit"];
	}
	
	return $query;
}

function db_clean($str) {
	return mysql_real_escape_string($str);
}

function db_query($sqlQuery) {
    $test = mysql_query($sqlQuery) or sqlDebug("$sqlQuery",mysql_error());
    return $test;
}

function db_large_query($sql) {
    $array = explode(";\r", $sql);
    foreach($array as $value) {
        db_query($value);
    }
    return true;
}

function db_valueOf($table, $q, $condition="", $debug=false) {
    $sql = "select $q from $table $condition";
    if ($debug)  echo $sql."<hr>";
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    $ligne = @mysql_fetch_array($test);
    return $ligne[$q];
}

function db_numberOf($table, $condition="") {
    $sql = "select * from $table $condition";
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    return @mysql_num_rows($test);
}

function db_array_valueOf($table, $condition="") {
    $tmp = array();
    $sql = "select * from $table $condition";
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    while ($ligne = @mysql_fetch_array($test)) {
        array_push($tmp, $ligne);
    }
    return $tmp;
}

function db_array_fieldOf($table) {
    $tmp = array();
    $sql = "SHOW FIELDS FROM $table";
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    while ($ligne = @mysql_fetch_array($test)) {
        array_push($tmp, $ligne['Field']);
    }
    return $tmp;
}

//--------------------- 
function db_pure_array_valueOf($sql) {
    $tmp = array();                          
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    while ($ligne = @mysql_fetch_array($test)) {
        array_push($tmp, $ligne);
    }
    return $tmp;
}
function db_pure_array_line_noindex_valueOf($sql) {
    $tmp = array();                          
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    $ligne = @mysql_fetch_array($test);
    $a = array();
    if (db_pure_numberOf($sql) >= 1) {
        foreach ($ligne as $var=>$val) {
            if (!is_integer($var)) {
                $a[$var] = $val; 
            }
        }
    }    
    return $a;
    
}

function db_pure_numberOf($sql) {           
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    return @mysql_num_rows($test);
}

function db_pure_valueOf($sql) {
    $test = mysql_query($sql) or sqlDebug("$sql",mysql_error());
    $ligne = @mysql_fetch_array($test);
    return $ligne[$q];
}
                       
//---------------------

function sqlDebug($sql,$message) {
    global $_SITE,$_SERVER;
	//echo "SQL ERROR:\"$message\" with query [$sql]<br />";
    if ($_SITE["sql_debug"] == true) {
        echo "<script>console.group('SQL ERROR');console.warn('".addslashes($sql)."');console.error('".addslashes($message)."');console.groupEnd();</script>";
    }                                                                                                        
    return true;    
}
//---------------------
function db_updateINT($table, $field, $value, $condition="", $insertOptions="") {
    $n = db_pure_numberOf("select * from $table $condition");
    if ($n) {
        $val = db_valueOf($table,$field,$condition);
        db_query("update $table set $field='".($val+$value)."' $condition");
    } else {
        if ($insertOptions != "") {
            foreach ($insertOptions as $name=>$qvalue) {
                $namestr .= ",$name";
                $valuestr .= ",'$qvalue'";
            }
        }
        db_query("insert into $table ($field"."$namestr) values ('$value'$valuestr);");
    }
    return $val+$value;
}

function db_getLastValue($table, $field, $condition="") {
    $val = db_valueOf($table,$field,$condition." order by id desc limit 0,1");
    return $val;
}

function createInsertQueryFromArray($table,$array,$return=false) {
    $fieldList = "";
    $valueList = "";
    foreach ($array as $field=>$value) {
        $fieldList .= $field.",";
        $valueList .= "'".db_clean($value)."',";
    }
    $fieldList = substr($fieldList,0,strlen($fieldList)-1);
    $valueList = substr($valueList,0,strlen($valueList)-1);
    
    $sqlQuery = "insert into ".$table." (".$fieldList.") values (".$valueList.");";
   // debug("db_insertArray",$sqlQuery);   
   	
   	if ($return) {
		return $sqlQuery;
	} else {
		db_query($sqlQuery);
		return db_valueOf($table,"LAST_INSERT_ID()","");
	}

    //echo " [".$sqlQuery."] ";
}

function db_getLineByArray($table,$array,$exceptions=null) {
    $fieldList = "";
    foreach ($array as $field=>$value) {
        if (!in_array($field,$exceptions))
            $fieldList .= $field."='".$value."' and ";
    }
    $fieldList = substr($fieldList,0,strlen($fieldList)-strlen(" and "));
    return db_pa("select * from ".$table." where ".$fieldList);
}

function createUpdateQueryFromArray($table,$conditions,$array,$createArray,$skipid=false) {
    
    if (!db_numberOf($table,$conditions)) {
        return createInsertQueryFromArray($table, $createArray);
    } else {
    
        $changeList = "";
        $props = db_getTableProps($table);
        
        foreach ($array as $field => $value) {
			$changeList .= "".$field."='".db_clean($value)."',";
        }
        $changeList = substr($changeList,0,strlen($changeList)-1);
        
        $sqlQuery = "update ".$table." set ".$changeList." ".$conditions.";";
        //debug("sqlQuery",$sqlQuery);
        //echo $sqlQuery."<br>";
        db_query($sqlQuery);
    }
	if (!$skipid) {
		return db_valueOf($table,"id",$conditions);
	} else {
		return true;
	}
}

function db_getColumns($table) {
	$query = "SHOW COLUMNS FROM $table";
	$fields_raw = db_pa($query);
	$fields = array();
	foreach ($fields_raw as $line) {
		array_push($fields, $line["Field"]);
	}
	return $fields;
}
function db_getTableProps($table) {
	$query = "SHOW COLUMNS FROM $table";
	$fields_raw = db_pa($query);
	$props = array();
	foreach ($fields_raw as $line) {
		//array_push($fields, $line["Field"]);
		$props[$line["Field"]] = $line;
	}
	return $props;
}

// correct a query for stupid tables using unix timestamps
// find all fields, detect the unix timestamp ones and decode them, before rewritting the right query.
function db_corrected($sql) {
	// find table
	$tmp = explode("from", $sql);
	$tmp2 = explode(" ",$tmp[1]);
	$table = $tmp2[1];
	$query = "SHOW COLUMNS FROM $table";
	$fields_raw = db_pa($query);
	
	$fields = array();
	foreach ($fields_raw as $line) {
		if ($line["Type"] == "timestamp") {
			array_push($fields, "UNIX_TIMESTAMP(".$line["Field"].") as ".$line["Field"]."");
		} else {
			array_push($fields, $line["Field"]);
		}
	}
	return str_replace("*",implode(",",$fields),$sql);
}

function db_getDistinctValues($table, $field, $condition="") {
	$values_raw = db_pa("select DISTINCT $field from $table ".$condition);
	$values = array();
	foreach ($values_raw as $line) {
		array_push($values, $line[$field]);
	}
	return $values;
}

function db_perfect($sql,$encodehtml=false) {
	$list = db_pa(db_corrected($sql));
	$buffer = array();
	foreach ($list as $line) {
		$tmp = array();
		foreach ($line as $field=>$value) {
			if (!is_int($field))
			//$val = utf8_encode($value);
			$val = ($value);
			if ($encodehtml) {
				$val = htmlentities($val);
			}
			$tmp[$field] = $val;
		}
		array_push($buffer, $tmp);
	}
	return $buffer;
}

function db_pa_utf8($sql,$double_decode=false) {
	$list = db_pure_array_valueOf($sql);
	$buffer = array();
	foreach ($list as $line) {
		$tmp = array();
		foreach ($line as $field => $value) {
			if (!is_int($field)) {
				$tmp[$field] = $value;
			}
		}
		array_push($buffer, $tmp);
	}
	return $buffer;
}
function db_line_utf8($sql) {
    $line = db_pure_array_line_noindex_valueOf($sql);
    $tmp = array();
	foreach ($line as $field => $value) {
		if (!is_int($value)) {
			$val = (($value));
		}
		$tmp[$field] = $val;
	}
	return $tmp;
}

/*******************************/
 // ALIAS

/*function db_pa($sql,$varvalConvert=false) {
    $return = db_pure_array_valueOf($sql);
    if ($varvalConvert) {
    	return db_varvalConvertion($return);
	} else {
		return $return;
	}
}*/
function db_pa($sql) {
	return db_pa_utf8($sql);
}
/*function db_line($sql) {
    return db_pure_array_line_noindex_valueOf($sql);
}*/
function db_line($sql) {
	return db_line_utf8($sql);
}
function db_insertArray($table,$array,$return=false) {
    return createInsertQueryFromArray($table,$array,$return);
}
function db_updateArray($table,$conditions,$array,$createArray) {
	//debug("db_updateArray",$conditions);
    return createUpdateQueryFromArray($table,$conditions,$array,$createArray);    
}

/**********************************/
 // data convertion
 function db_varvalConvertion($sqlResult) {
 	$buffer = array();
 	foreach ($sqlResult as $line) {
		$tmp = array();
		foreach ($line as $field=>$value) {
			if (!is_int($field))
			$tmp[$field] = utf8_encode($value);
		}
		array_push($buffer, $tmp);
	}
	return $buffer; 
 }
 function db_namedArray($sqlResult) {
 	$array = array();
 	foreach ($sqlResult as $name=>$value) {
 		$array[$name] = $value;
	}
	return $array; 
 }
?>