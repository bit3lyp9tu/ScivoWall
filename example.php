<?php
	$db_path = "/etc/dbpw";

	$password = null;

	if (file_exists($db_path)) {
		$password = file_get_contents($db_path);
		$password = chop($password);
	} else {
		die("$db_path not found!");
	}


	$servername = "localhost";
	$username = "poster_generator";

	// Create connection
	$conn = new mysqli($servername, $username, $password);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$user = $_GET["id"];

	$sql = "SELECT id, name FROM poster_generator.user where id = '".$conn->real_escape_string($user)."'";

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "id: " . $row["id"]. " - Name: " . $row["name"] . "\n";
		}
	} else {
		echo "0 results";
	}
?>
