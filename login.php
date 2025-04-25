<!DOCTYPE html>
<html lang='en'>
<?php
    // ob_start();

    // error_reporting(E_ALL);
    // ini_set('display_errors', 1);

    $site_script="login.js";
    // var_dump($site_script); // To see if it's getting processed

    include(__DIR__ . "/" . "header.html");
    include(__DIR__ . "/" . "account_management.php");

    // jquery in lokale datei statt über code.jquery.com

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
