// document.addEventListener("pageshow", () => {
window.onload = function () {
    const key = window.location.href.split("?")[1].split("&")[0].split("=")[0];
    const value = window.location.href.split("?")[1].split("&")[0].split("=")[1];

    var content = "";

    $.ajax({
        type: "POST",
        url: "poster_edit.php",
        data: {
            action: "get-content",
            key: key,
            value: value
        },
        dataType: 'json',
        success: function (response) {

            if (response.status != 'error') {

                console.log(response);
                console.log(response.title);
                console.log(response.authors);

                document.getElementById("title").innerText = response.title;
                document.getElementById("authors").innerText = response.authors.toString(", ");

                const boxes = document.getElementById("boxes");

                for (const key in response.boxes) {
                    const obj = document.createElement("p");
                    obj.innerText = response.boxes[key];

                    boxes.appendChild(obj);
                }

            } else {

                toastr["warning"]("Not Logged in");
            }

        },
        error: function () {

            console.error("content head request failed ");
        }
    });


};
