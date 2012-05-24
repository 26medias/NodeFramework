<?php
	function facebook_init() {
		global $_CONF;
		$_GET["__shared__"]["facebook"] = array(
			"webservice"	=> $_CONF["libsettings"]["server"]["Facebook"]["webservice"],
			"code"	=> "
						<div id=\"fb-root\"></div>
						<script>
							window.fbAsyncInit = function() {
								FB.init({
									appId: '".$_CONF["libsettings"]["server"]["Facebook"]["appid"]."',
									status: true,
									cookie: true,
								 	xfbml: true,
								 	oauth: true
								 });
								 if (window.fbinit) {
								 	for (var i=0;i<window.fbinit.length;i++) {
								 		window.fbinit[i]();
								 	}
								 }
							};
							(function() {
								var e = document.createElement('script'); e.async = true;
								e.src = document.location.protocol +
								'//connect.facebook.net/".$_CONF["libsettings"]["server"]["Facebook"]["locale"]."/all.js';
								document.getElementById('fb-root').appendChild(e);
							}());
							$(function() {
								$(\".login\").bind('click', function() {
									var scope = this;
									$(\".login\").html(\"<a>Please wait...</a>\");
									FB.login(function(response) {
										console.log(\"FB.login \",response);
										$.ajax({
											url: 		\"".$_CONF["libsettings"]["server"]["Facebook"]["webservice"]."\",
											type:		\"POST\",
											data:		{
												token:		response.authResponse.accessToken,
												uid:		response.authResponse.userID,
												signed:		response.authResponse.signedRequest
											},
											success: 	function(data){
												document.location=document.location;
											}
										});
									}, {
										scope: '".$_CONF["libsettings"]["server"]["Facebook"]["perms"]."'
									});
								});
							});
							var facebook = {};
							window.fbinit = new Array();
							window.fbinit.push(function() {
								
							});
						</script>
			",
			"button"	=> "<div class=\"login\">Login</div>"
		);
	}
	
	function execute_fql($fql, $token=false, $uid=false) {
		$query 	= urlencode($fql);
		$url	= "https://api.facebook.com/method/fql.query?format=json&query=".$query."&access_token=".$token;
		$raw	= file_get($url);
		//debug($url, $raw);
		$raw	= preg_replace("#\":(\d+)#","\":\"$1\"",$raw);
		$array 	= json_decode($raw,true);
		return $array;
	}
	function execute_graph($url, $token=false, $uid=false) {
		$raw	= file_get($url."&access_token=".$token);
		$array 	= json_decode($raw,true);
		return $array;
	}
	
	facebook_init();
	
?>