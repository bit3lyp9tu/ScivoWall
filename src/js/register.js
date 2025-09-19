function register() {
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
                console.debug(response);

                if (response == "1") {

                    $.ajax({
                        type: "POST",
                        url: "/api/post_traffic.php",
                        data: {
                            action: 'login',
                            name: username.value,
                            pw: password.value
                        },
                        success: function (response) {
                            if (response == "Correct Password") {
                                toastr["success"]("Logged in");
                                window.location.href = "projects.php";
                            } else {
                                toastr["warning"](response);
                            }
                        },
                        error: function () {
                            toastr["error"]("An error occurred...");
                        }
                    });

                } else {
                    toastr["warning"](response);
                    toastr["warning"]("Please try again");
                }
            },
            error: function () {
                toastr["error"]("An error occurred");
            }
        });
    }
}

$(document).ready(function () {
    document.getElementById("register-btn").onclick = register;
});

$(this).keydown(function (e) {
    if (e.code == "Enter") {
        register();
    }
});
