<?php
	include_once("mysql.php");

	// Create connection
	$conn = new mysqli($servername, $username, $password);

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	$user = $_GET["id"];

	$sql = "SELECT id, name FROM user where id = '".$conn->real_escape_string($user)."'";

	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "id: " . $row["id"]. " - Name: " . $row["name"] . "\n";
		}
	} else {
		echo "0 results";
	}
?>
