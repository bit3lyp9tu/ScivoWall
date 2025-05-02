<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    echo "<p>PHP script is running!</p>";

    $servername = "127.0.0.1";
    $username = "poster_generator";
    $password = "password";
    $database = "poster_generator";
    $port = 3307;

    $conn = new mysqli($servername, $username, $password, $database, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT name FROM user;";
    $result = $conn->query($sql);

    if ($result === true) {
        print_r($result);
    } else {
        try {
            print_r($result->fetch_all());
        } catch (Throwable $th) {
            print_r([]);
        }
    }
?>
<!DOCTYPE html>
<html lang='en'>
    <body>
        <p>TestTest</p>
    </body>
</html>
