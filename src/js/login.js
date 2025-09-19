function login() {
    const username = document.getElementById("name");
    const password = document.getElementById("pw");

    if (username.value == "" || password.value == "") {
        toastr["warning"]("Ensure you input a value in both fields!");
    } else {
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
    }
}

$(document).ready(function () {
    document.getElementById("login-btn").onclick = login;
});

$(this).keydown(function (e) {
    if (e.code == "Enter") {
        login();
    }
});
