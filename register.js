$(document).ready(function () {
    let registerForm = document.getElementById("form");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        let username = document.getElementById("username");
        let password = document.getElementById("password");
        let password2 = document.getElementById("password2");

        if (username.value == "" || password.value == "" || password2.value == "" || password.value != password2.value) {
            toastr["warning"]("Ensure you input a value in all fields!");
        } else {
            $.ajax({
                type: "POST",
                url: "account_management.php",
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
    });
});
