<?php
	function isRunningInDocker(): bool {
		if (is_file('/.dockerenv')) {
			return true;
		}
		if (is_file('/proc/1/cgroup')) {
			$cgroup = file_get_contents('/proc/1/cgroup');
			return strpos($cgroup, 'docker') !== false || strpos($cgroup, 'containerd') !== false;
		}
		return false;
	}

	$db_path = "/etc/dbpw";

	$servername = getenv('DB_HOST');
	$username = getenv('DB_USER');
	$password = getenv('DB_PASS');
	$database = getenv('DB_NAME');
	$port = (int) getenv('DB_PORT');

	if (isRunningInDocker()) {
		$servername = "dockerdb";
		$username = "poster_generator";
		$database = "poster_generator";
		$password = "password";
		$port = 3306;
	} else if (getenv("GITHUB_ACTIONS")) {
		$servername = getenv('DB_HOST');
	}

	if (file_exists($db_path)) {
		$password = trim(file_get_contents($db_path));
	} else {
		if (!isRunningInDocker()) {
			error_log("error_log: $db_path not found! Trying default-pw");
		}
	}

	mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

	try {
		$GLOBALS["conn"] = @new mysqli($servername, $username, $password, $database, $port);
	} catch (\Throwable $e) {
		include $_SERVER['DOCUMENT_ROOT'] . "/pages/404.php";
		exit;
	}
?>
