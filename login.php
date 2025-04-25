<!DOCTYPE html>
<html lang='en'>
<?php
    ob_start();

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // $site_script="login.js";
    // var_dump($site_script); // To see if it's getting processed
    echo "<p>Still processing!</p>";

    // include("account_management.php");
    // include("header.html");
    // echo realpath("account_management.php");
    // echo "file 2";
    echo realpath(__DIR__ . "/" . "header.html");

    // jquery in lokale datei statt über code.jquery.com

    echo "<p>PHP script is running!</p>";

    print_r(__DIR__);
    $directory = __DIR__;
    if (is_dir($directory)) {
        $files = scandir($directory);
        echo "Dir exists";
    } else {
        echo "Directory does not exist: " . $directory;
    }
    // print_r(scandir("/scientific_poster_generator/"));

    // ob_end_flush();
?>
<body>
    <div>
        <form action="" id="login-form">
            <h1>Login</h1>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="pw" class="form-control" placeholder="Enter your Password...">
            <button id="login" type="submit">Submit</button>
        </form>
        <p id="login-response"></p>
    </div>
</body>
</html>
