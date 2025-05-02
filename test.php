<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    echo "<p>PHP script is running!</p>";

    $servername = "localhost";
    $port = 3800;

    $database = "poster_generator";

    $username = "poster_generator";
    $password = "password";

    if (getenv("GITHUB_ACTIONS")) {
        $servername = "127.0.0.1";
    }

    $conn = new mysqli($servername, $username, $password, $database, $port);

    if ($conn->connect_error) {
        print_r("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT name FROM poster_generator.user;";
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
