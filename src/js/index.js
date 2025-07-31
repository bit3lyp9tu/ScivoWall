var sleeper = 2000;

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

window.onload = async function () {

    //TODO:   [BUG]   accessing index-view works,
    //              but going to poster after and back to index
    //              resets the session validation and public content is deactivated???

    const content = await JSON.parse(await fetchAvailablePosters());

    console.log("len", content.poster_id.length);

    document.getElementById("posters").setAttribute("data-carousel-3d", "");

    const cont = document.getElementById("posters");
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

    while (true) {
        for (var i = 0; i < document.querySelectorAll("div.poster-slide>ul>iframe").length; i++) {

            showPoster("div.poster-slide>ul>iframe", i);
            await new Promise(r => setTimeout(r, sleeper));
        }
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

