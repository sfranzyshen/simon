<?php
	if (file_exists(__DIR__ . '/public' . $_SERVER['REQUEST_URI'])) {
		return false; 
	} else {
		include_once "public/index.php";
	}
?>
