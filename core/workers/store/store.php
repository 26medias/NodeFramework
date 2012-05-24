<html lang="fr-FR">
<head>
	<title>Sitegen Store</title>
	<meta charset="UTF-8">
	<style type="text/css">
		body {
			margin:0;
			padding:0;
		}
	</style>
</head>
<body>
	
<?php
// load the libs
	require_once("core/compiled/php/common.php");
	
	echo '<iframe src="'.$_CONF["sid"]["store"].'initsession?token='.$_CONF["sid"]["token"].'&secret='.$_CONF["sid"]["secret"].'" style="width:100%; height:100%; border:0;"></iframe>';
?>

</body>
</html>