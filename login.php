<?php
    include("account_management.php");
    // include("test.php");
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>Login</title>
    <meta charset='utf-8'>
    <link rel='stylesheet' type='text/css' href=style.css>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="cryptography.js"></script>
</head>

<body>
    <!-- <div>
        <h2>Login</h2>
        <form action="">
            <label for="name">Username:</label>
            <input type="text" id="name" name="name"><br><br>
            <label for="pw">Password:</label>
            <input type="password" id="pw" name="pw"><br><br>
            <input type="submit" value="Submit">
        </form>
    </div> -->
    <div>

        <form action="" id="registerForm">
            <h1>Login</h1>
            <input type="text" id="username" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="password" class="form-control" placeholder="Enter your Password...">
            <input type="password" id="password2" class="form-control" placeholder="Repeat your Password...">
            <button type="submit">Submit</button>
        </form>

    </div>

    <div>
        <p id="response"></p>
    </div>
</body>
</html>

<script>
    let registerForm = document.getElementById("registerForm");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        let username = document.getElementById("username");
        let password = document.getElementById("password");
        let password2 = document.getElementById("password2");

        if (username.value == "" || password.value == "" || password2.value == "" || password.value != password2.value) {
            alert("Ensure you input a value in both fields!");
        } else {
            var salt = generate_salt();//"841ae4f433bc273d9f2151dd9bbe5da";
            var pepper = "a2d47c981889513c5e2ddbca71f414";
            var hash = md5(password.value + ":" + salt + ":" + pepper);

            console.log(username.value, password.value, salt, pepper, hash);

            $.ajax({
            type: "POST",
            url: "account_management.php",
            data: {
                action: 'register',
                name: username.value,
                pw: password.value
            },
            success: function(response) {
                $('#response').html(response);
            },
            error: function() {
                alert("An error occurred");
            }
        });
        }
    });
</script>
