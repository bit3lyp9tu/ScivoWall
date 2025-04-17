<?php
	$db_path = "/etc/dbpw";

	$password = null;
	$servername = "localhost";
	$username = "poster_generator";

	$port = 3306;

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		error_log("error_log: $db_path not found! Trying default-pw");
		$password = "password";

		$port = 3307;
	}

	// Create connection
	try {
		$GLOBALS["conn"] = new mysqli($servername, $username, $password, "", $port);

		// Check connection
		if ($GLOBALS["conn"]->connect_error) {
			error_log("Connection failed: " . $GLOBALS["conn"]->connect_error);

			exit(1);
		}
	} catch (\Throwable $e) {
		error_log("Error trying to initialize database connection: $e");
		exit(2);
	}
?>
