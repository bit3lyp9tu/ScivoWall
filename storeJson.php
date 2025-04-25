<?php
	include_once(__DIR__ . "/" . "functions.php");

	$json = $_POST['json'];

	if(!$json) {
		die("Needs to get JSON as POST parameter");
	}

	$subfolder = 'json';
	if(isDocker()) {
		$subfolder = "/poster_generator_json/";
	}

	$hash = md5($json);

	if (!file_exists($subfolder)) {
		mkdir($subfolder, 0755, true);
	}

	$fileName = $subfolder . '/' . $hash . '.json';
	try {
		$written_length = file_put_contents($fileName, $json);

		echo $hash;
	} catch (\Throwable $e) {
		print("Error: ".$e);
	}
?>
