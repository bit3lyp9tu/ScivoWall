<?php

$servername = "localhost";
$username = "root";
$password = "holm";
$dbname = "poster_generator";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}


try {

	// sql to create table
	$sql = "CREATE TABLE if not exists images (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(500) NOT NULL,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	)";

	if ($conn->query("create database poster_generator if not exists") === TRUE) {
		echo "Table images created successfully";
	} else {
		echo "Error creating table: " . $conn->error;
	}

	if ($conn->query("use poster_generator") === TRUE) {
		echo "Table images created successfully";
	} else {
		echo "Error creating table: " . $conn->error;
	}

	if ($conn->query($sql) === TRUE) {
		echo "Table images created successfully";
	}
} catch (\Throwable $e) {}

// Check if the image was uploaded
if(isset($_FILES['image'])) {
	$errors = array();
	$file_name = $_FILES['image']['name'];
	$file_size = $_FILES['image']['size'];
	$file_tmp = $_FILES['image']['tmp_name'];
	$file_type = $_FILES['image']['type'];
	$file_ext = strtolower(end(explode('.', $_FILES['image']['name'])));

	$extensions = array("jpeg","jpg","png", "gif");

	// Check if the uploaded file is an image
	if(in_array($file_ext, $extensions) === false){
		$errors[] = "Extension not allowed, please choose a JPEG or PNG file.";
	}

	// Check if the size of the file is below a certain limit
	if($file_size > 2097152) {
		$errors[] = 'File size must be exactly 2 MB or less';
	}

	// If there are no errors, move the uploaded file to a designated folder
	if(empty($errors) == true) {
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
		$hash_file_name = md5(file_get_contents($file_tmp)).".".$ext;
		move_uploaded_file($file_tmp, "images/".$hash_file_name);

		$conn = new mysqli($servername, $username, $password, $dbname);

		// Check the connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}

		// Insert the image name into the database
		$sql = "INSERT INTO images (name) VALUES ('".$hash_file_name."')";
		if ($conn->query($sql) === TRUE) {
			echo "{\"filePath\": \"images/$hash_file_name\"}";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	} else {
		print_r($errors);
	}
}

$conn->close();
?>
