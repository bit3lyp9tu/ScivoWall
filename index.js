async function fetchAvailablePosters() {
    return await $.ajax({
        type: "POST",
        url: "poster_edit.php",
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

    const content = await JSON.parse(await fetchAvailablePosters());

    const cont = document.getElementById("posters");
    for (const elem in content.poster_id) {
        const a = document.createElement("DIV");
        const b = document.createElement("IFRAME");

        b.setAttribute("src", "poster.php?id=" + content.poster_id[elem] + "&mode=public");
        b.setAttribute("with", 300);
        b.setAttribute("height", 600);

        a.appendChild(b);
        cont.appendChild(a);

        // console.log(content.title[elem]);
    }
}
