<?php
	
	$p								= params();
	
	$here							= "core/compiled/php/";
	
	$folder_templates				= "templates/";
	$folder_libs_serverside			= "system/libs/serverside/";
	$folder_libs_clientside			= "system/libs/clientside/";
	$folder_conf					= "system/conf/";
	
	$file_conf						= $folder_conf."lwf.conf";
	$file_sid						= $folder_conf."sid.conf";
	
	$cache_serverside_libs			= $here."cache_serverside_libs.json";
	$cache_clientside_libs			= $here."cache_clientside_libs.json";
	$cache_settings					= $here."cache_settings.json";
	$cache_libsettings				= $here."cache_libsettings.json";
	
	$cache_clientside_includes		= $here."settings.js";
	$cache_serverside_includes		= $here."common.php";
	$cache_conf_includes			= $here."conf.php";
	$cache_conf_clientsideincludes	= $here."clientsideincludes.php";
	
	// parse the conf file
	$conf							= json_decode(file_get($file_conf),true);
	
	// Load the full list of libs
		$libs_serverside 			= system_listLibs($folder_libs_serverside);
		$libs_clientside 			= system_listLibs($folder_libs_clientside);
		$template_list				= system_listTemplates($folder_templates);
		
	// check if the form has been submitted
		if (is_array($p["serversidelibs"])) {
			
			$temp_serverside_libs 	= array();
			$temp_serverside_files 	= array();
			$temp_serverside_ids 	= array("libs"=>array());
			$temp_php_buffer		= "";
			
			foreach ($p["serversidelibs"] as $submitted_lib_name => $state) {
				foreach ($libs_serverside as $lib_array) {
					if ($lib_array["id"] == $submitted_lib_name) {
						array_push($temp_serverside_libs, $lib_array);
					}
				}
			}
			
			// list the files needed
			$temp_serverside_libs = system_prioritizeExtLibs($temp_serverside_libs);
			foreach($temp_serverside_libs as $libArray) {
				$temp_php_buffer .= "// lib :: ".$libArray["id"]." (/".$libArray["path"].")\n";
				array_push($temp_serverside_ids["libs"], $libArray["id"]);
				foreach ($libArray["include"] as $fileArray) {
					array_push($temp_serverside_files, $fileArray["file"]);
					$temp_php_buffer .= "\trequire_once(\"".$fileArray["file"]."\");\n";
				}
			}
			
			// save the require_once file
			file_put_contents($cache_serverside_includes, "<?php\n".$temp_php_buffer."?>");
			
			// save the selected libs
			file_put_contents($cache_serverside_libs, json_encode($temp_serverside_ids));
		} else {
			// no lib selected.
			// empty the common file
			/*file_put_contents($cache_serverside_includes, "<?php\n?>");*/
		}
		if (is_array($p["clientsidelibs"])) {
			
			$temp_clientside_libs 	= array();
			$temp_clientside_files 	= array();
			$temp_clientside_ids 	= array("libs"=>array());
			$temp_js_buffer		= "";
			
			foreach ($p["clientsidelibs"] as $submitted_lib_name => $state) {
				foreach ($libs_clientside as $lib_array) {
					if ($lib_array["id"] == $submitted_lib_name) {
						array_push($temp_clientside_libs, $lib_array);
					}
				}
			}
			
			// list the files needed
			$temp_clientside_libs = system_prioritizeExtLibs($temp_clientside_libs);
			foreach($temp_clientside_libs as $libArray) {
				$temp_js_buffer .= "// lib :: ".$libArray["id"]." (/".$libArray["path"].")\n";
				array_push($temp_clientside_ids["libs"], $libArray["id"]);
				foreach ($libArray["include"] as $fileArray) {
					array_push($temp_clientside_files, $fileArray["file"]);
					$temp_js_buffer .= "\trequire_once(\"".$fileArray["file"]."\");\n";
				}
			}
			
			// save the selected libs
			file_put_contents($cache_clientside_libs, json_encode($temp_clientside_ids));
			
			
		} else {
			// no lib selected.
			// empty the common file
			file_put_contents($cache_clientside_includes, "var __settings__ = {};");
		}
		
		
		
		$_sides = array("client","server");
		foreach ($_sides as $_side) {
			if (is_array($p["libsettings"]) && is_array($p["libsettings"][$_side])) {
				// get the content of the libsettings
				$current_libsettings	= json_decode(file_get($cache_libsettings),true);
				if (!is_array($current_libsettings["settings"])) {
					$current_libsettings["settings"] = array();
				}
				$current_libsettings["settings"][$_side][$p["settings"]["conf"]] = $p["libsettings"][$_side];
				file_put_contents($cache_libsettings,json_encode($current_libsettings));
				
				// push the lib settings to the main conf object
				$conf["conf"][$p["settings"]["conf"]]["libsettings"] = $p["libsettings"];
			}
		}
		
			// save the client-side settings
			file_put_contents($cache_clientside_includes, "var __settings__ = ".json_encode($current_libsettings["settings"]["client"][$p["settings"]["conf"]]).";");
		
	// load the settings cache
		if (!file_exists($cache_settings)) {
			$current_settings		= "";
			file_put_contents($cache_settings,json_encode(array(
				"settings" 	=> "",
				"selected"	=> "",
				"template"	=> "default"
			)));
		} else {
			$current_settings		= json_decode(file_get($cache_settings),true);
		}
		
		
		if (isset($p["template"])) {
			$current_settings["template"]	 = $p["template"];
			// save the conf profile to use
			$conf["conf"][$p["settings"]["conf"]]["template"] = $p["template"];
		} else {
			
		}
		
		
		if (is_array($p["settings"])) {
			
			$current_settings["settings"][$p["settings"]["conf"]] = $p["settings"];
			$current_settings["selected"]	= $p["settings"]["conf"];
			
			file_put_contents($cache_settings,json_encode($current_settings));
			
			// push the lib settings to the main conf object
			$conf["conf"][$p["settings"]["conf"]]["settings"] = $p["settings"];
			
			// save the site ID
			$siteid					= json_decode(file_get($file_sid),true);
			$conf["conf"][$p["settings"]["conf"]]["sid"] = $siteid;
			file_put_contents($cache_conf_includes,"<?php\n\t\$_CONF=".array_to_phpArray($conf["conf"][$p["settings"]["conf"]]).";\n?>");
		}
		
	
	// load the active libs
		if (!file_exists($cache_serverside_libs)) {
			$serverside_libs	= array();
			file_put_contents($cache_serverside_libs,json_encode($serverside_libs));
		} else {
			$serverside_libs	= json_decode(file_get($cache_serverside_libs),true);
		}
		if (!file_exists($cache_clientside_libs)) {
			$clientside_libs	= array();
			file_put_contents($cache_clientside_libs,json_encode($clientside_libs));
		} else {
			$clientside_libs	= json_decode(file_get($cache_clientside_libs),true);
		}
		
		
	// load the libsettings cache
		if (!file_exists($cache_libsettings)) {
			$current_libsettings	= "";
			file_put_contents($cache_libsettings,json_encode(array("settings"=>array("server"=>array(),"client"=>array()))));
		} else {
			$current_libsettings	= json_decode(file_get($cache_libsettings),true);
		}
		
	// load the libsettings cache
		if (!file_exists($cache_conf_clientsideincludes)) {
			file_put_contents($cache_conf_clientsideincludes,"<?php\n\t\$_CLIENTSIDELIBS = array();\n?>");
		}
		
?>
<html>
	<head>
		<title>LWF CONF</title>
		<style type="text/css">
			table.light {
				width:				100%;
				border-collapse: 	separate;
			    border-spacing: 	0;
			    margin-bottom: 		20px;
			    vertical-align: 	middle;
			    border:								1px solid #788794;
				-moz-box-shadow: 					0px 0px 4px #5a656f;
				-webkit-box-shadow: 				0px 0px 4px #5a656f;
				box-shadow: 						0px 0px 4px #5a656f;
			}
			table.light > thead {
				
			}
			table.light > thead > tr > th, table.light > tfoot > tr > th {
				text-shadow: 						#3a4147 0px 1px 2px;
				border-top:							1px solid #9da7b0;
				border-bottom:						1px solid #6c7983;
				padding:							5px;
				color:								#d8d9d7;
				font-weight:						bold;
				font-size:							14px;
				text-align:							left;
				background: #86939e;
				background: -moz-linear-gradient(top, #86939e 0%, #788590 100%);
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#86939e), color-stop(100%,#788590));
				background: -webkit-linear-gradient(top, #86939e 0%,#788590 100%);
				background: -o-linear-gradient(top, #86939e 0%,#788590 100%);
				background: -ms-linear-gradient(top, #86939e 0%,#788590 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#86939e', endColorstr='#788590',GradientType=0 );
				background: linear-gradient(top, #86939e 0%,#788590 100%);
			}
			
			table.light > tbody {
				
			}
			
			table.light > tbody > tr > td {
				background-color: 					#7e8c99;
				color:								#d6d7d8;
				font-size:							11px;
				padding:							5px;
				text-align:							left;
			}
			table.light > tbody > tr:nth-child(odd) > td {
				border-top:							1px solid #8e9ba5;
				border-bottom:						1px solid #6c7983;
				padding:							5px;
				color:								#d6d7d8;
				font-size:							11px;
				background: 						#7e8c99;
			}
			table.light > tbody > tr:nth-child(even) > td {
				border-top:							1px solid #8e9ba5;
				border-bottom:						1px solid #6c7983;
				padding:							5px;
				color:								#d6d7d8;
				font-size:							11px;
				background: 						#71818e;
			}
			
			table.light > tfoot {
				
			}
			button, .button {
				vertical-align:			text-bottom;
				padding:				10px 20px;
				font-weight:			bold;
				color:					#d6d7d8;
				text-shadow: 			#000000 0px 1px 2px;
				-moz-box-shadow: 		0px 0px 1px #1d2023;
				-webkit-box-shadow: 	0px 0px 1px #1d2023;
				box-shadow: 			0px 0px 1px #1d2023;
				border:					1px solid #5c6268;
				-webkit-border-radius: 	5px;
				-moz-border-radius: 	5px;
				border-radius: 			5px;
				display:				inline-block;
				behavior: url(border-radius.htc);
				background: #7e8891;
				background: -moz-linear-gradient(top, #7e8891 0%, #6b757f 11%, #49525a 100%);
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#7e8891), color-stop(11%,#6b757f), color-stop(100%,#49525a));
				background: -webkit-linear-gradient(top, #7e8891 0%,#6b757f 11%,#49525a 100%);
				background: -o-linear-gradient(top, #7e8891 0%,#6b757f 11%,#49525a 100%);
				background: -ms-linear-gradient(top, #7e8891 0%,#6b757f 11%,#49525a 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#7e8891', endColorstr='#49525a',GradientType=0 );
				background: linear-gradient(top, #7e8891 0%,#6b757f 11%,#49525a 100%);
			}
			button.small, .button.small, a.button.small {
				padding:				5px 10px;
				font-size:				9px;
			}
		</style>
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
		<script type="text/javascript">
			var libsettings 		= <?php echo json_encode($current_libsettings); ?>;
			var current_settings 	= <?php echo json_encode($current_settings); ?>;
			libsettings 			= libsettings["settings"];
			current_settings 		= current_settings["settings"];
			console.log(libsettings);
			$(function() {
				$("#conf_dropdown").change(function() {
					var x, a, i, j, sides=['client','server'];
					for (a=0;a<sides.length;a++) {
						if (libsettings[sides[a]][$(this).val()] && libsettings[sides[a]][$(this).val()]) {
							for (i in libsettings[sides[a]][$(this).val()]) {
								for (j in libsettings[sides[a]][$(this).val()][i]) {
									$("#"+i+"_"+j).val(libsettings[sides[a]][$(this).val()][i][j]);
								}
							}
						}
					}
					if (current_settings[$(this).val()]) {
						for (x in current_settings[$(this).val()]) {
							if (x != "conf") {
								$("#settings_"+x).val(current_settings[$(this).val()][x]);
							}
						}
					}
				});
			});
		</script>
	</head>
	<body>
		<form action="" method="post">
			<table class="light">
				<thead>
					<tr>
						<th colspan="2">Options</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td width="225">Settings to use</td>
						<td>
							<select name="settings[conf]" id="conf_dropdown">
								<?php
									foreach ($conf["conf"] as $conf_name) {
								?>
									<option value="<?php echo $conf_name; ?>" <?php if (is_array($current_settings) && is_array($current_settings["settings"]) && $conf_name==$current_settings["selected"]) {echo "selected";} ?>><?php echo $conf_name; ?></option>
								<?php
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td width="225">Mode Debug</td>
						<td>
							<select name="settings[mode_debug]" id="settings_mode_debug">
								<option value="off" <?php if (is_array($current_settings) && is_array($current_settings["settings"]) && is_array($current_settings["settings"][$current_settings["selected"]]) && $current_settings["settings"][$current_settings["selected"]]["mode_debug"]=="off") {echo "selected";} ?>>Turned off</option>
								<option value="on" <?php if (is_array($current_settings) && is_array($current_settings["settings"]) && is_array($current_settings["settings"][$current_settings["selected"]]) && $current_settings["settings"][$current_settings["selected"]]["mode_debug"]=="on") {echo "selected";} ?>>Turned on</option>
							</select>
						</td>
					</tr>
					<tr>
						<td width="225">Base url</td>
						<td>
							<input type="text" id="settings_base" name="settings[base]" value="<?php if (is_array($current_settings) && is_array($current_settings["settings"]) && is_array($current_settings["settings"][$current_settings["selected"]]) ) { echo $current_settings["settings"][$current_settings["selected"]]["base"]; } ?>">
						</td>
					</tr>
				</tbody>
			</table>
			
			<table class="light">
				<thead>
					<tr>
						<th width="25">&nbsp;</th>
						<th width="200">Library</th>
						<th>Description</th>
						<th width="450">Settings</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($libs_serverside as $lib) {
					?>
					<tr>
						<td>
							<input type="checkbox" name="serversidelibs[<?php echo $lib["id"]; ?>]" <?php if (in_array($lib["id"],$serverside_libs["libs"])) {echo "checked";} ?> />
						</td>
						<td valign="middle">
							<b><?php echo $lib["id"]."<br />(".$lib["version"].")"; ?></b>
						</td>
						<td>
							<?php
								echo $lib["description"];
							?>
							<ul>
							<?php
								foreach ($lib["include"] as $file) {
									echo "<li><b><u>".$file["file"]."</u></b><br />";
									echo	$file["description"]."</li>";
								}
							?>
							</ul>
						</td>
						<td>
							<?php
								if (is_array($lib["settings"])) {
							?>
							<table class="light">
								<tbody>
									<?php
										foreach ($lib["settings"] as $setting_label	=> $setting_value) {
									?>
									<tr>
										<td>
											<?php
												echo $setting_label;
											?>
										</td>
										<td>
											<?php
												if (is_array($setting_value)) {
											?>
											<select id="<?php echo $lib["id"]."_".$setting_label; ?>" name="libsettings[server][<?php echo $lib["id"]; ?>][<?php echo $setting_label; ?>]">
												<?php
													foreach ($setting_value as $opt_label) {
												?>
													<option value="<?php echo $opt_label; ?>" <?php if (is_array($current_libsettings) && is_array($current_libsettings["settings"]) && is_array($current_libsettings["settings"]["server"][$current_settings["selected"]]) && is_array($current_libsettings["settings"]["server"][$current_settings["selected"]][$lib["id"]])) { if ($current_libsettings["settings"]["server"][$current_settings["selected"]][$lib["id"]][$setting_label] == $opt_label) { echo "selected";} } ?>><?php echo $opt_label; ?></option>
												<?php
													}
												?>
											</select>
											<?php
												} else {
											?>
											<input type="text" id="<?php echo $lib["id"]."_".$setting_label; ?>" name="libsettings[server][<?php echo $lib["id"]; ?>][<?php echo $setting_label; ?>]" value="<?php if (is_array($current_libsettings) && is_array($current_libsettings["settings"]) && is_array($current_libsettings["settings"]["server"][$current_settings["selected"]]) && is_array($current_libsettings["settings"]["server"][$current_settings["selected"]][$lib["id"]])) { echo $current_libsettings["settings"]["server"][$current_settings["selected"]][$lib["id"]][$setting_label]; } ?>" />
											<?php
												}
											?>
										</td>
									</tr>
									<?php
										}
									?>
								</tbody>
							</table>
							<?php
								} else {
									echo "&nbsp;";
								}
							?>
						</td>
					</tr>
					<?php
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4">
							<input type="submit" class="button small" value="Update" />
						</th>
					</tr>
				</tfoot>
			</table>
			
			<table class="light">
				<thead>
					<tr>
						<th width="25">&nbsp;</th>
						<th width="200">Library</th>
						<th>Description</th>
						<th width="450">Settings</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($libs_clientside as $lib) {
					?>
					<tr>
						<td>
							<input type="checkbox" name="clientsidelibs[<?php echo $lib["id"]; ?>]" <?php if (in_array($lib["id"],$clientside_libs["libs"])) {echo "checked";} ?> />
						</td>
						<td valign="middle">
							<b><?php echo $lib["id"]."<br />(".$lib["version"].")"; ?></b>
						</td>
						<td>
							<?php
								echo $lib["description"];
							?>
							<ul>
							<?php
								foreach ($lib["include"] as $file) {
									echo "<li><b><u>".$file["file"]."</u></b><br />";
									echo	$file["description"]."</li>";
								}
							?>
							</ul>
						</td>
						<td>
							<?php
								if (is_array($lib["settings"])) {
							?>
							<table class="light">
								<tbody>
									<?php
										foreach ($lib["settings"] as $setting_label	=> $setting_value) {
									?>
									<tr>
										<td>
											<?php
												echo $setting_label;
											?>
										</td>
										<td>
											<input type="text" id="<?php echo $lib["id"]."_".$setting_label; ?>" name="libsettings[client][<?php echo $lib["id"]; ?>][<?php echo $setting_label; ?>]" value="<?php if (is_array($current_libsettings) && is_array($current_libsettings["settings"]) && is_array($current_libsettings["settings"]["client"][$current_settings["selected"]]) && is_array($current_libsettings["settings"]["client"][$current_settings["selected"]][$lib["id"]])) { echo $current_libsettings["settings"]["client"][$current_settings["selected"]][$lib["id"]][$setting_label]; } ?>" />
										</td>
									</tr>
									<?php
										}
									?>
								</tbody>
							</table>
							<?php
								} else {
									echo "&nbsp;";
								}
							?>
						</td>
					</tr>
					<?php
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4">
							<input type="submit" class="button small" value="Update" />
						</th>
					</tr>
				</tfoot>
			</table>
			
			<table class="light">
				<thead>
					<tr>
						<th width="25">&nbsp;</th>
						<th>Template name</th>
					</tr>
				</thead>
				<tbody>
					<?php
						foreach ($template_list as $template) {
					?>
					<tr>
						<td>
							<input type="radio" name="template" value="<?php echo $template; ?>" id="<?php echo $template; ?>" <?php if($current_settings["template"]==$template) { echo "checked"; } ?> />
						</td>
						<td>
							<label for="<?php echo $template; ?>"><?php echo $template; ?></label>
						</td>
					</tr>
					<?php
						}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="4">
							<input type="submit" class="button small" value="Update" />
						</th>
					</tr>
				</tfoot>
			</table>
		</form>
	</body>
</html>
