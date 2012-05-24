<?php
	switch ($_GET["error"]) {
		case "404":
			echo "Page not found.";
		break;
		case "403":
			echo "Acces forbidden!";
		break;
		default:
			echo "general error.";
		break;
	}
?>