<?php
	function isRunningInDocker(): bool {
		// Check /.dockerenv
		if (is_file('/.dockerenv')) {
			return true;
		}

		// Check cgroup info
		if (is_file('/proc/1/cgroup')) {
			$cgroup = file_get_contents('/proc/1/cgroup');
			if (strpos($cgroup, 'docker') !== false || strpos($cgroup, 'containerd') !== false) {
				return true;
			}
		}

		return false;
	}

	$db_path = "/etc/dbpw";

	$servername = getenv('DB_HOST');
	$username = getenv('DB_USER');
	$password = getenv('DB_PASS');
	$database = getenv('DB_NAME');
	$port = (int) getenv('DB_PORT');

	if(isRunningInDocker()) {
		echo "is running in docker\n";
		$servername = "dockerdb";
		$username = "poster_generator";
		$database = "poster_generator";
		$password = "password";
		$port = 3306;
	} else if (getenv("GITHUB_ACTIONS")) {
		echo "is running in github actions\n";
		$servername = getenv('DB_HOST');
	}
	

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		error_log("error_log: $db_path not found! Trying default-pw");
	}

	// Create connection
	try {
		#print_r("servername:\t" . $servername . "\nusername:\t" . $username . "\npw:\t\t" . $password . "\ndb:\t\t" . $database . "\nport:\t\t" . $port . "\n");
		// error_log("servername:\t" . $servername . "\nusername:\t" . $username . "\npw:\t\t" . $password . "\ndb:\t\t" . $database . "\nport:\t\t" . $port . "\n");
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
