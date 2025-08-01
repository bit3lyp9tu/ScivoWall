<!DOCTYPE html>
<html lang='en'>
<?php

    error_log("login: " . __DIR__);

    $site_script="login.js";
    include(__DIR__ . "/../src/php/header.php");

    // jquery in lokale datei statt über code.jquery.com
?>
<body>
    <div>
        <div>
            <h1>Login</h1>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="pw" class="form-control" placeholder="Enter your Password...">
            <button id="login-btn">Submit</button>
        </div>
        <p id="login-response"></p>
    </div>
</body>
</html>
