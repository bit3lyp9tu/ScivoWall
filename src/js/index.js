// TODO: change carousel to https://www.jqueryscript.net/demo/image-cover-flow-flipster/
async function fetchAvailablePosters() {
    return await $.ajax({
        type: "POST",
        url: "/api/post_traffic.php",
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

    $('.my-flipster').flipster({
        itemContainer: 'ul',
        // [string|object]
        // Selector for the container of the flippin' items.

        itemSelector: 'div',
        // [string|object]
        // Selector for children of `itemContainer` to flip

        start: 'center',
        // ['center'|number]
        // Zero based index of the starting item, or use 'center' to start in the middle

        fadeIn: 100,
        // [milliseconds]
        // Speed of the fade in animation after items have been setup

        loop: true,
        // [true|false]
        // Loop around when the start or end is reached

        autoplay: 300,
        // [false|milliseconds]
        // If a positive number, Flipster will automatically advance to next item after that number of milliseconds

        pauseOnHover: true,
        // [true|false]
        // If true, autoplay advancement will pause when Flipster is hovered

        style: 'coverflow',
        // [coverflow|carousel|flat|...]
        // Adds a class (e.g. flipster--coverflow) to the flipster element to switch between display styles
        // Create your own theme in CSS and use this setting to have Flipster add the custom class

        spacing: -0.2,
        // [number]
        // Space between items relative to each item's width. 0 for no spacing, negative values to overlap

        click: true,
        // [true|false]
        // Clicking an item switches to that item

        keyboard: true,
        // [true|false]
        // Enable left/right arrow navigation

        scrollwheel: true,
        // [true|false]
        // Enable mousewheel/trackpad navigation; up/left = previous, down/right = next

        touch: true,
        // [true|false]
        // Enable swipe navigation for touch devices

        nav: false,
        // [true|false|'before'|'after']
        // If not false, Flipster will build an unordered list of the items
        // Values true or 'before' will insert the navigation before the items, 'after' will append the navigation after the items

        buttons: true,
        // [true|false|'custom']
        // If true, Flipster will insert Previous / Next buttons with SVG arrows
        // If 'custom', Flipster will not insert the arrows and will instead use the values of `buttonPrev` and `buttonNext`

        buttonPrev: 'Previous',
        // [text|html]
        // Changes the text for the Previous button

        buttonNext: 'Next',
        // [text|html]
        // Changes the text for the Next button

        onItemSwitch: false
        // [function]
        // Callback function when items are switched
        // Arguments received: [currentItem, previousItem]
    });

    // const content = await JSON.parse(await fetchAvailablePosters());

    // console.log("len", content.poster_id.length);

    // document.getElementById("posters").setAttribute("data-carousel-3d", "");

    // const cont = document.getElementById("posters");
    // for (const elem in content.poster_id) {
    //     // const a = document.createElement("DIV");


    //     const b = document.createElement("IFRAME");
    //     b.id = "iframe-" + elem;

    //     b.setAttribute("src", "poster.php?id=" + content.poster_id[elem] + "&mode=public");
    //     b.setAttribute("width", 600);
    //     b.setAttribute("height", 600);

    //     // a.appendChild(b);
    //     cont.appendChild(b);

    //     // console.log(content.title[elem]);
    // }

    // if (cont.children.length >= 1) {
    //     document.getElementById("iframe-0").setAttribute("selected", "");
    // }

    // await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.waitforimages/2.4.0/jquery.waitforimages.js');
    // await loadScript('https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js');

    // await loadScript('./bower_components/modernizr/modernizr.js');
    // await loadScript('./bower_components/carousel-3d/dist/jquery.carousel-3d.js');

    // await loadStyle("./bower_components/carousel-3d/dist/styles/jquery.carousel-3d.default.css");
    // await loadStyle("./src/css/style_index.css");

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
$(document).on("click", "#login-btn", function () {
    let username = document.getElementById("name");
    let password = document.getElementById("pw");

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
});
$(document).on("click", "#register-btn", function () {
    let username = document.getElementById("username");
    let password = document.getElementById("password");
    let password2 = document.getElementById("password2");

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
});
