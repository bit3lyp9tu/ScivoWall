<?php
	error_reporting(E_ALL);
	set_error_handler(function ($severity, $message, $file, $line) {
		throw new \ErrorException($message, $severity, $severity, $file, $line);
	});

	ini_set('display_errors', 1);

	function get_get($name, $default=null) {
		if(isset($_GET[$name])) {
			return $_GET[$name];
		}
		return $default;
	}

	function isDocker() {
		return is_file("/.dockerenv");
	}

	function test_read_json($name) {
		// Read the JSON file
		$json = file_get_contents($name);

		// Check if the file was read successfully
		if ($json === false) {
			die('Error reading the JSON file');
		}

		// Decode the JSON file
		$json_data = json_decode($json, true);

		// Check if the JSON was decoded successfully
		if ($json_data === null) {
			die('Error decoding the JSON file');
		}

		// Display data
		echo "<pre>";
		print_r($json_data);
		echo "</pre>";
	}

	function test_write_json() {
		$array = Array (
			"0" => Array (
				"id" => "000000000",
				"name" => "Bobby",
				"Subject" => "Java"
			),
			"1" => Array (
				 "id" => "7021",
				"name" => "ojaswi",
				"Subject" => "sql"
			)
		 );

		$json = json_encode($array);

		file_put_contents("json/write_test.json", $json);
	}
?>
