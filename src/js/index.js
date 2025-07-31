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

var intervalId = null;
var counter = 0;
function showPoster(selector, index) {
    var posters = document.querySelectorAll(selector);
    var l = index % posters.length;

    for (var i = 0; i < posters.length; i++) {
        const element = posters[i];

        if (i != l) {
            element.classList.add("hide");
        }
    }
    posters[l].classList.remove("hide");
}
function showPosterAll() {
    const selector = "div.poster-slide>ul>iframe";

    showPoster(selector, counter);
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

    counter += value % document.querySelectorAll(selector).length;
    showPoster(selector, counter);
    console.info("Counter shifted by ", value);
}
function setCounter(value) {
    const selector = "div.poster-slide>ul>iframe";

    counter = value % document.querySelectorAll(selector).length;
    showPoster(selector, counter);
    console.info("Set Counter to ", counter);
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

        document.getElementById("spinner").style.display = "none";

        intervalId = setInterval(showPosterAll, 2000);
    } else {

        const elem = document.createElement("h2");
        elem.innerText = "There are no public posters available at the moment.";
        cont.appendChild(elem);
    }

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

