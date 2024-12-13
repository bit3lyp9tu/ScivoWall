function get_current_page_json() {
    var json = {
        maintitle: $("#maintitle").text(),	//Name of Project
        mainsubtitle: $("#mainsubtitle").text(),	//Authors
        boxes: []
    };

    for (var elem of $(".box")) {
        var data_original = elem.getAttribute("data-original");
        json["boxes"].push(filter_html(data_original));
    }

    return json;
}

async function load_from_json(json) {
    $("#maintitle").text(json.maintitle)
    $("#mainsubtitle").text(json.mainsubtitle)

    $(".container").html("")

    for (var i = 0; i < json.boxes.length; i++) {
        await add_box(json.boxes[i], 0);
    }

    $(".MathJax").css("pointer-events", "none");
}

function save_current_json() {
    var stringified = JSON.stringify(get_current_page_json());
    var md5_stringified = md5(stringified);

    if (last_saved_hash === null || last_saved_hash != md5_stringified) {
        $.ajax({
            type: "POST",
            url: "storeJson.php",
            data: {
                json: stringified
            },
            success: function (response) {
                log(response);
                history.pushState({}, null, "index.php?id=" + response);
            }
        });
        last_saved_hash = md5_stringified;
    }
}


// import sample_data from 'testing/sample-data.json';
function fetchJSONSampleData() {
    fetch("testing/sample-data.json")
        .then((res) => {
            if (!res.ok) {
                throw new Error
                    (`HTTP error! Status: ${res.status}`);
            }
            return res.json();
        })
        .then((data) =>
            console.log(data))
        .catch((error) =>
            console.error("Unable to fetch data:", error));
}
// fetchJSONSampleData();
