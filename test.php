<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    echo "<p>PHP script is running!</p>";

	$servername = "127.0.0.1";
	$username = "poster_generator";
	$password = "password";
	$port = 3307;

    $GLOBALS["conn"] = new mysqli($servername, $username, $password, "", $port);

    $sql = "SELECT name FROM poster_generator.user;";
    $result = $GLOBALS["conn"]->query($sql);

    if ($result === True) {
        print_r($result);
    }else{
        try {
            print_r($result->fetch_all());
        } catch (Throwable $th) {
            print_r(array());
        }
    }
?>
<!DOCTYPE html>
<html lang='en'>
    <body>
        <p>TestTest</p>
    </body>
</html>
