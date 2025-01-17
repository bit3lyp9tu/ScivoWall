$(document).ready(function () {
    let registerForm = document.getElementById("login-form");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        let username = document.getElementById("name");
        let password = document.getElementById("pw");

        if (username.value == "" || password.value == "") {
            toastr["warning"]("Ensure you input a value in both fields!");
        } else {
            $.ajax({
                type: "POST",
                url: "account_management.php",
                data: {
                    action: 'login',
                    name: username.value,
                    pw: password.value
                },
                success: function (response) {
                    toastr["warning"](response);
                },
                error: function () {
                    toastr["error"]("An error occurred");
                }
            });
        }
    });
});
