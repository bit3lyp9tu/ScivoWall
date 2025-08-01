$(document).ready(function () {
    document.getElementById("register-btn").onclick = function () {

        const username = document.getElementById("username");
        const password = document.getElementById("password");
        const password2 = document.getElementById("password2");

        if (username.value == "" || password.value == "" || password2.value == "" || password.value != password2.value) {
            toastr["warning"]("Ensure you input a value in all fields!");
        } else {
            $.ajax({
                type: "POST",
                url: "/api/post_traffic.php",
                data: {
                    action: 'register',
                    name: username.value,
                    pw: password.value
                },
                success: function (response) {
                    toastr["warning"](response);
                    window.location.href = "login.php";
                },
                error: function () {
                    toastr["error"]("An error occurred");
                }
            });
        }
    }
});
