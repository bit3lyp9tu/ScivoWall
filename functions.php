<?php
	error_reporting(E_ALL);
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});

	ini_set('display_errors', 1);

	// error_log("functions.php");

	function get_get($name, $default=null) {
		if(isset($_GET[$name])) {
			return $_GET[$name];
		}
		return $default;
	}

	function isDocker() {
		return is_file("/.dockerenv");
	}

	function dier($msg) {
		$msg = print_r($msg, true);

		print_r($msg);

		exit(1);
	}
?>
