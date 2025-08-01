<!DOCTYPE html>
<html lang='en'>
<?php

    $site_script="register.js";
    include(__DIR__ . "/../src/php/header.php");

    // include_once(__DIR__ . "/../src/php/account_management.php");
?>
<body>
    <div>
        <div>
            <h1>Register</h1>
            <input type="text" id="username" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="password" class="form-control" placeholder="Enter your Password...">
            <input type="password" id="password2" class="form-control" placeholder="Repeat your Password...">
            <button id="register-btn">Submit</button>
        </div>
        <p id="register-response"></p>
    </div>
</body>
</html>
