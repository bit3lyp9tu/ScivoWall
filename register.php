<!DOCTYPE html>
<html lang='en'>
<?php
    include(__DIR__ . "/" . "account_management.php");
    include(__DIR__ . "/" . "header.html");
?>
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
