// TODO: change carousel to https://www.jqueryscript.net/demo/image-cover-flow-flipster/
async function fetchAvailablePosters() {
    return await $.ajax({
        type: "POST",
        url: "post_traffic.php",
        data: {
            action: "fetch-available-posters"
        },
        success: function (response) {
            return response;
        },
        error: function (err) {
            console.log(err);
            return -1;
        }
    });
}

window.onload = async function () {

    //TODO:   [BUG]   accessing index-view works,
    //              but going to poster after and back to index
    //              resets the session validation and public content is deactivated???

    const content = await JSON.parse(await fetchAvailablePosters());

    console.log("len", content.poster_id.length);

    document.getElementById("posters").setAttribute("data-carousel-3d", "");

    const cont = document.getElementById("posters");
    for (const elem in content.poster_id) {
        // const a = document.createElement("DIV");


        const b = document.createElement("IFRAME");
        b.id = "iframe-" + elem;

        b.setAttribute("src", "poster.php?id=" + content.poster_id[elem] + "&mode=public");
        b.setAttribute("width", 600);
        b.setAttribute("height", 600);

        // a.appendChild(b);
        cont.appendChild(b);

        // console.log(content.title[elem]);
    }

    if (cont.children.length >= 1) {
        document.getElementById("iframe-0").setAttribute("selected", "");
    }

    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.waitforimages/2.4.0/jquery.waitforimages.js');
    await loadScript('https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js');

    await loadScript('./bower_components/modernizr/modernizr.js');
    await loadScript('./bower_components/carousel-3d/dist/jquery.carousel-3d.js');

    await loadStyle("./bower_components/carousel-3d/dist/styles/jquery.carousel-3d.default.css");
    await loadStyle('./style_index.css');

    document.getElementById("spinner").style.display = "none";
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src;

        s.onload = resolve;
        s.onerror = reject;

        document.head.appendChild(s);
    });
}
function loadStyle(href) {
    return new Promise((resolve, reject) => {
        style = document.createElement("link");
        style.setAttribute("rel", "stylesheet");
        style.setAttribute("href", href);

        style.onload = resolve;
        style.onerror = reject;

        document.head.appendChild(style);
    });
}

document.getElementById("login-btn").addEventListener("click", function () {
    let username = document.getElementById("name");
    let password = document.getElementById("pw");

    if (username.value == "" || password.value == "") {
        toastr["warning"]("Ensure you input a value in both fields!");
    } else {
        $.ajax({
            type: "POST",
            url: "post_traffic.php",
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
});

document.getElementById("register-btn").addEventListener("click", function () {
    let username = document.getElementById("username");
    let password = document.getElementById("password");
    let password2 = document.getElementById("password2");

    if (username.value == "" || password.value == "" || password2.value == "" || password.value != password2.value) {
        toastr["warning"]("Ensure you input a value in all fields!");
    } else {
        $.ajax({
            type: "POST",
            url: "post_traffic.php",
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
