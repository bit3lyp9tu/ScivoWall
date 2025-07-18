<!DOCTYPE html>
<html lang='en'>
<?php

    $site_script="login.js";
    include(__DIR__ . "/../src/php/header.php");

    // include_once(__DIR__ . "/../src/php/account_management.php");

    // jquery in lokale datei statt über code.jquery.com
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
