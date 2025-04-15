<?php
	$db_path = "/etc/dbpw";

	$password = null;

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		$password = "testpw";
		error_log("$db_path not found!");
	}


	$servername = "localhost";
	$username = "poster_generator";

	// Create connection
	$GLOBALS["conn"] = new mysqli($servername, $username, $password);

	// Check connection
	if ($GLOBALS["conn"]->connect_error) {
		die("Connection failed: " . $GLOBALS["conn"]->connect_error);
	}
?>
