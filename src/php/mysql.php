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

	if (getenv("GITHUB_ACTIONS")) {
		$servername = getenv('DB_HOST');
	}
	
	if(isRunningInDocker()) {
		$servername = "dockerdb";
	} else {
		echo "\n=== Docker Environment Check ===\n";
		echo "Hostname: " . gethostname() . "\n";
		echo "PHP SAPI: " . php_sapi_name() . "\n";
		echo "User: " . get_current_user() . "\n";
		echo "UID: " . posix_getuid() . " | GID: " . posix_getgid() . "\n";
		echo "Running as root? " . (posix_getuid() === 0 ? 'Yes' : 'No') . "\n";

		// Optional: Show network info
		echo "IP addresses:\n";
		$ips = gethostbynamel(gethostname());
		if ($ips) {
			foreach ($ips as $ip) {
				echo " - $ip\n";
			}
		} else {
			echo " - Could not resolve host IPs\n";
		}

		// Show some ENV variables
		echo "ENV Variables:\n";
		foreach (['HOSTNAME', 'IN_DOCKER', 'DOCKER', 'CONTAINER'] as $var) {
			echo " - $var = " . getenv($var) . "\n";
		}

		echo "\nChecking for Docker...\n";
		$inDocker = isRunningInDocker();

		echo "\nResult: ";
		if ($inDocker) {
			echo "✅ RUNNING INSIDE DOCKER\n";
		} else {
			echo "❌ IS NOT RUNNING IN DOCKER!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!111111111!1111111!!!!!!!111!!!1111111!!!!11\n";
		}

		echo "\n===============================\n";
	}

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		error_log("error_log: $db_path not found! Trying default-pw");
	}

	// Create connection
	try {
		print_r("servername:\t" . $servername . "\nusername:\t" . $username . "\npw:\t\t" . $password . "\ndb:\t\t" . $database . "\nport:\t\t" . $port . "\n");
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
