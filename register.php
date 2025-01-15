<?php
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

<script>
// todo: toastr
    let registerForm = document.getElementById("form");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        let username = document.getElementById("username");
        let password = document.getElementById("password");
        let password2 = document.getElementById("password2");

        if (username.value == "" || password.value == "" || password2.value == "" || password.value != password2.value) {
            alert("Ensure you input a value in all fields!");
        } else {
            $.ajax({
                type: "POST",
                url: "account_management.php",
                data: {
                    action: 'register',
                    name: username.value,
                    pw: password.value
                },
                success: function(response) {
                    $('#register-response').html(response);
                },
                error: function() {
                    alert("An error occurred");
                }
            });
        }
    });

</script>
