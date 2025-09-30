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

var timer = 1000 * 20;
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

    if (length > 0) {
        showPoster(selector, counter);
        document.getElementById("counter").innerText = (index_converter(counter, length) + 1) + "/" + length;
        counter++;
    }
}

function untoggleAll() {
    const selector = ".slider-controls>*.toggled";
    document.querySelectorAll(selector).forEach(element => {
        element.classList.remove("toggled");
    });
}

function restart(time = timer) {
    if (intervalId !== null) {
        clearInterval(intervalId);
    }

    untoggleAll();
    document.querySelector(".slider-controls>input[value='Play']").classList.add("toggled");

    console.info("Set interval to", time);
    intervalId = setInterval(showPosterAll, time);
}
function stop() {
    if (intervalId !== null) {
        untoggleAll();
        document.querySelector(".slider-controls>input[value='Stop']").classList.add("toggled");

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
        console.warn("Idle time exceeded, reloading...");
        window.location.reload();
    }
    if (idleTime > 5 && intervalId === null) {
        console.warn("Manual time limit of 5min [Stop] exceeded, Restarting sequence...");
        restart();
    }
}

var startX = 0;
var endX = 0;

window.addEventListener("load", async function () {
    var loaded_iframes = 0;

    //TODO:   [BUG]   accessing index-view works,
    //              but going to poster after and back to index
    //              resets the session validation and public content is deactivated???

    const content = await JSON.parse(await fetchAvailablePosters());

    const cont = document.getElementById("posters");

    console.log(content.poster_id.length);

    if (content.poster_id.length > 0) {
        if (content.poster_id.length == 1) {
            document.getElementsByClassName("slider-controls")[0].style.display = "none";
        }

        content.poster_id.forEach((posterId, index) => {
            const ul = document.createElement("UL");
            const iframe = document.createElement("IFRAME");
            iframe.id = "iframe-" + index;
            iframe.src = "poster.php?id=" + posterId + "&mode=public";

            if (index != 0) {
                iframe.classList.add("hide");
            }

            iframe.addEventListener("load", () => {
                loaded_iframes++;

                console.info("loading poster...", index, content.poster_id.length, loaded_iframes);

                if (content.poster_id.length == loaded_iframes) {
                    console.log("All posters loaded", content.poster_id.length, loaded_iframes);
                    document.getElementById("spinner").style.display = "none";
                }
            });

            ul.appendChild(iframe);
            cont.appendChild(ul);
        });

        if (content.poster_id.length != 1) {
            setCounter(0);
            restart();
        }

    } else {
        document.getElementsByClassName("slider-controls")[0].style.display = "none";

        const elem = document.createElement("h2");
        elem.innerText = "There are no public posters available at the moment.";
        cont.appendChild(elem);

        document.getElementById("spinner").style.display = "none";
    }
});

function handleGesture() {

    if (!document.querySelector(".poster-slide>h2")) {
        if (endX < startX - 50) {
            console.log("swipeleft");
            shiftCounter(1);
        }
        if (endX > startX + 50) {
            console.log("swiperight");
            shiftCounter(-1);
        }
    }
}

document.addEventListener("pointerdown", function (e) {
    startX = e.clientX;
}, false);

document.addEventListener("pointerup", function (e) {
    endX = e.clientX;
    handleGesture();
}, false);

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
                restart();
            } else {
                console.info("stop poster slider");
                stop();
            }
        }
    }
});
