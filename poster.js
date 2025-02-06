
function url_to_json() {
    const result = {};

    if (window.location.href.includes('?')) {
        const suffix = window.location.href.split("?")[1].split("&");

        for (let param of suffix) {
            const [key, value] = param.split("=");

            if (key && value) {
                result[key] = decodeURIComponent(value);
            }
        }
    }
    return result;
}

async function request(data) {
    return new Promise((resolve, reject) => {
        const key = data.hasOwnProperty('id') ? 'id' : '';
        const value = data.id || '';

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
                resolve(response);
            },
            error: function (error) {
                reject(error);
            }
        });
    });
}

// function typeset(code) {
//     MathJax.startup.promise = MathJax.startup.promise
//         .then(() => MathJax.typesetPromise(code()))
//         .catch((err) => console.log('Typeset failed: ' + err.message));
//     return MathJax.startup.promise;
// }

function typeset(container, code) {
    MathJax.startup.promise = MathJax.startup.promise
        .then(() => {
            container.innerHTML = code();
            return MathJax.typesetPromise([container]);
        })
        .catch((err) => console.log('Typeset failed: ' + err.message));
    return MathJax.startup.promise;
}

function show(response) {
    document.getElementById("title").innerText = response.title;
    document.getElementById("authors").innerText = response.authors.toString(", ");

    const boxes = document.getElementById("boxes");

    for (const key in response.boxes) {
        const obj = document.createElement("div");
        obj.classList.add("box");
        if (0) {
            obj.innerHTML = response.boxes[key];
        } else {
            typeset(obj, () => response.boxes[key]);
        }

        boxes.appendChild(obj);
    }

    // const container = document.getElementById('math-container');


}

window.onload = async function () {
    const data = url_to_json();

    let response = {};
    try {
        response = await request(data);
        console.log(response);
    } catch (error) {
        console.error("content head request failed " + error);
    }

    if (response.status != 'error') {

        show(response);

    } else {
        toastr["warning"]("Not Logged in");
    }

};

document.getElementById("save-content").onclick = function () {
    console.log("save");

}
