<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    echo "<p>PHP script is running!</p>";


	$password = "password";
	$servername = "localhost";
	$username = "poster_generator";
	$port = 3307;

    $GLOBALS["conn"] = new mysqli($servername, $username, $password, "", $port);
    $result = mysqli_query($$GLOBALS["conn"], "SELECT name FROM user;");
    print_r($result);
?>
<!DOCTYPE html>
<html lang='en'>
    <body>
        <p>TestTest</p>
    </body>
</html>
