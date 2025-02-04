document.addEventListener("pageshow", () => {
    const data = window.location.href.substring("?")[1].substring("&")[0];
    console.log(data);

    console.log("pageshow");
    $.ajax({
        type: "GET",
        url: "poster_edit.php",
        data: {
            // action: "content-head"
            id: 112
        },
        success: function (response) {

            console.log(response);
        },
        error: function () {

            console.error("content head request failed");
        }
    });
});
