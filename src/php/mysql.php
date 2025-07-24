<?php
	$db_path = "/etc/dbpw";

	$servername = (getenv('DB_HOST') !== null) ? getenv('DB_HOST') : "localhost";
	$username = (getenv('DB_USER') !== null) ? getenv('DB_USER') : "poster_generator";
	$password = (getenv('DB_PASS') !== null) ? getenv('DB_PASS') : "password";
	$database = (getenv('DB_NAME') !== null) ? getenv('DB_NAME') : "poster_generator";
	$port = (int) (getenv('DB_PORT') !== null) ? getenv('DB_PORT') : 3800;

	if (getenv("GITHUB_ACTIONS")) {
		$servername = (getenv('DB_HOST') !== null) ? getenv('DB_HOST') : "127.0.0.1";
	}

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		error_log("error_log: $db_path not found! Trying default-pw");
	}

	// Create connection
	try {
		error_log("servername: " . $servername . " username: " . $username . " pw: " . $password . " db: " . $database . " port: " . $port);
		$GLOBALS["conn"] = new mysqli($servername, $username, $password, $database, $port);

		// Check connection
		if ($GLOBALS["conn"]->connect_error) {
			error_log("Connection failed: " . $GLOBALS["conn"]->connect_error);

			exit(1);
		}
	} catch (\Throwable $e) {
		echo "<pre>";
		if (preg_match('/Class "mysqli" not found/', $e)) {
			echo "The Module mysqli not found. Try installing it with\nsudo apt-get install php-mysqli";
		} else {
			echo "Error trying to initialize database connection: $e";
		}
		echo "</pre>";
		exit(2);
	}
?>
