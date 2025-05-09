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
    // echo date_default_timezone_get();

    // $local_tz = new DateTimeZone('Europe/London');
    // $local = new DateTime('now', $local_tz);

    // $user_tz = new DateTimeZone(timezone_name_from_abbr(date_default_timezone_get()));
    // $user = new DateTime('now', $user_tz);

    // $local_offset = $local->getOffset() / 3600;
    // $user_offset = $user->getOffset() / 3600;

    // $diff = $local_offset - $user_offset;

    // print_r($diff);

    // echo new DateTime('now', new DateTimeZone('Europe/London'))->getOffset() / 3600 - new DateTime('now', new DateTimeZone(timezone_name_from_abbr(date_default_timezone_get())))->getOffset() / 3600;

    // php -r "echo new DateTime('now', new DateTimeZone('Europe/London'))->getOffset() / 3600 - new DateTime('now', new DateTimeZone(timezone_name_from_abbr(date_default_timezone_get())))->getOffset() / 3600;"
?>
<!DOCTYPE html>
<html lang='en'>
    <body>
        <p>TestTest</p>
    </body>
</html>
