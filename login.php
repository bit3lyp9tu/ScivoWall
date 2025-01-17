<?php
    $site_script="login.js";

    include("account_management.php");
    include("header.html");

    // jquery in lokale datei statt über code.jquery.com
?>
<!DOCTYPE html>
<html lang='en'>
<body>
    <div>
        <form action="" id="login-form">
            <h1>Login</h1>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="pw" class="form-control" placeholder="Enter your Password...">
            <button type="submit">Submit</button>
        </form>
        <p id="login-response"></p>
    </div>
</body>
</html>
