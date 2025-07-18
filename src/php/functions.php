<?php
	error_reporting(E_ALL);
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});

	ini_set('display_errors', 1);

	// error_log("functions.php");

	function isDocker() {
		return is_file("/.dockerenv");
	}

	function dier($msg) {
		$msg = print_r($msg, true);

		print("<pre>");
		print_r($msg);
		print("</pre>");

		exit(1);
	}
?>
