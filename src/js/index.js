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

function mod(n, m) {
    return ((n % m) + m) % m;
}

function index_converter(i, l) {
    return i < 0 ? mod((i + l * (mod(-i, l))), l) : i % l;
}

var intervalId = null;
var counter = 0;
function showPoster(selector, index) {
    var posters = document.querySelectorAll(selector);
    var l = index_converter(index, posters.length);

    for (var i = 0; i < posters.length; i++) {
        const element = posters[i];

        if (i != l) {
            element.classList.add("hide");
        }
    }
    console.log(l, index, posters[l], posters);

    posters[l].classList.remove("hide");

}
function showPosterAll() {
    const selector = "div.poster-slide>ul>iframe";
    const length = document.querySelectorAll(selector).length;

    showPoster(selector, counter);
    document.getElementById("counter").innerText = (index_converter(counter, length) + 1) + "/" + length;
    counter++;
}

function restart(time) {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }
    console.info("Set interval to", time);
    intervalId = setInterval(showPosterAll, time);
}
function stop() {
    if (intervalId !== null) {
        clearInterval(intervalId);
        intervalId = null;
        console.info("Stopped interval");
    } else {
        console.info("No interval running");
    }
}
function shiftCounter(value) {
    const selector = "div.poster-slide>ul>iframe";
    const length = document.querySelectorAll(selector).length;

    counter += value % length;
    showPoster(selector, counter);
    document.getElementById("counter").innerText = (index_converter(counter, length) + 1) + "/" + length;
    console.info("Counter shifted by ", value);
}
function setCounter(value) {
    const selector = "div.poster-slide>ul>iframe";
    const length = document.querySelectorAll(selector).length;

    counter = value % length;
    showPoster(selector, counter);
    document.getElementById("counter").innerText = (index_converter(counter, length) + 1) + "/" + length;
    console.info("Set Counter to ", counter);
}

var idleTime = 0;
$(document).ready(function () {
    var idleInterval = setInterval(timerIncrement, 60 * 1000);

    $(this).mousemove(function (e) {
        idleTime = 0;
    });
    $(this).keydown(function (e) {
        idleTime = 0;
    });
});

function timerIncrement() {
    idleTime = idleTime + 1;
    if (idleTime > 60) {
        window.location.reload();
        console.warn("Idle time exceeded, reloading...");
    }
    if (idleTime > 5) {
        restart(2000);
        console.warn("Manual time limit of 5min exceeded, Restarting sequence...");
    }
}

window.onload = async function () {

    //TODO:   [BUG]   accessing index-view works,
    //              but going to poster after and back to index
    //              resets the session validation and public content is deactivated???

    const content = await JSON.parse(await fetchAvailablePosters());

    const cont = document.getElementById("posters");

    console.log(content.poster_id.length);

    if (content.poster_id.length > 0) {
        for (const elem in content.poster_id) {
            const ul = document.createElement("UL");
            const b = document.createElement("IFRAME");
            b.id = "iframe-" + elem;
            // console.log(content.title[elem]);
            b.setAttribute("src", "poster.php?id=" + content.poster_id[elem] + "&mode=public");

            ul.appendChild(b)
            cont.appendChild(ul);
        }

        intervalId = setInterval(showPosterAll, 2000);
    } else {

        document.getElementsByClassName("slider-controls")[0].style.display = "none";

        const elem = document.createElement("h2");
        elem.innerText = "There are no public posters available at the moment.";
        cont.appendChild(elem);
    }

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

document.addEventListener("keydown", (event) => {

    if (document.querySelectorAll("div.poster-slide>ul>iframe").length > 0) {
        if (event.key === "ArrowRight") {
            shiftCounter(1);
        }
        if (event.key === "ArrowLeft") {
            shiftCounter(-1);
        }
        if (event.code === "Space") {
            if (intervalId === null) {
                console.info("restart poster slider");
                restart(2000);
            } else {
                console.info("stop poster slider");
                stop();
            }
        }
    }

});
