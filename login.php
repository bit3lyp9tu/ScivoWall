<?php
    include("account_management.php");

    // session_start();
    // echo "Die Session-ID ist :" . session_id();
    // $sid=session_id(); //erstellt eine Variable mit der Session-ID
//
// boilerplate code: <!DOCTYPE html> ... auslagern in ne eigene datei, kann auch .html sein und
// dann include("header.html")
//
// jquery in lokale datei statt über code.jquery.com
//
// script in eigene datei, außerdem den ganzen script-block in $(document).ready(function () { ... hier den ganzen alten kram ... })
//
// https://codeseven.github.io/toastr/ statt alert
//
// serverseitig: pw komplexität checken
//
// weiterleitung per action an liste eigener projekte
//
// toastr statt alert -> https://codeseven.github.io/toastr/
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>Login</title>
    <meta charset='utf-8'>
    <link rel='stylesheet' type='text/css' href=style.css>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div>
        <form action="" id="form">
            <h1>Login</h1>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username...">
            <input type="password" id="pw" class="form-control" placeholder="Enter your Password...">
            <button type="submit">Submit</button>
        </form>
        <p id="login-response"></p>
    </div>
</body>
</html>

<script>
    let registerForm = document.getElementById("form");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        let username = document.getElementById("name");
        let password = document.getElementById("pw");

        if (username.value == "" || password.value == "") {
            alert("Ensure you input a value in both fields!");
        } else {
            $.ajax({
                type: "POST",
                url: "account_management.php",
                data: {
                    action: 'login',
                    name: username.value,
                    pw: password.value
                },
                success: function(response) {
			// todo: alle error messages durchgehen und mit toastr anzeigen
                    $('#login-response').html(response);
                },
                error: function() {
                    alert("An error occurred");
                }
            });
        }
    });

</script>
