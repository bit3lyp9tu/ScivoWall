<?php
    $site_script = "register.js";

    include("account_management.php");
    include("header.html");
?>
<!DOCTYPE html>
<html lang='en'>
<body>
    <div>
        <form action="" id="form">
            <h1>Register</h1>
            <input type="text" id="username" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="password" class="form-control" placeholder="Enter your Password...">
            <input type="password" id="password2" class="form-control" placeholder="Repeat your Password...">
            <button type="submit">Submit</button>
        </form>
        <p id="register-response"></p>
    </div>
</body>
</html>
