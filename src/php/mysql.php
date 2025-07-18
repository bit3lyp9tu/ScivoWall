<?php
	$db_path = "/etc/dbpw";

	$servername = "localhost";
	$port = 3800;

	$database = "poster_generator";

	$username = "poster_generator";
	$password = "password";

	if (getenv("GITHUB_ACTIONS")) {
		$servername = "127.0.0.1";
	}

	if (file_exists('/.dockerenv') || getenv('IS_DOCKER') === 'true') {
		$servername = "dockerdb";
		$port = 3306;
	}

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		error_log("error_log: $db_path not found! Trying default-pw");
	}

	// Create connection
	try {
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
