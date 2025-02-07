
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
        if (response.boxes[key]) {
            const obj = document.createElement("div");
            obj.classList.add("box");
            obj.id = "editBox-" + key;
            if (0) {
                obj.innerHTML = response.boxes[key];
            } else {
                // obj.setAttribute("data-original", toMarkdown(response.boxes[key]));
                // obj.innerText = toMarkdown(response.boxes[key]);
                typeset(obj, () => response.boxes[key]);
            }

            boxes.appendChild(obj);
        }
    }
}

var selected = null;
document.addEventListener("click", function (event) {
    if (event.target.tagName === "DIV" && event.target.id.startsWith("editBox")) {
        if (selected && selected !== event.target) {
            const originalDiv = document.createElement("div");
            originalDiv.id = selected.id;
            originalDiv.classList.add("box");

            if (selected.tagName === "TEXTAREA") {
                originalDiv.innerText = selected.value; // Preserve the edited text
            } else {
                originalDiv.innerText = selected.innerText; // Preserve original text for div
            }

            selected.parentNode.replaceChild(originalDiv, selected);
            selected = null;
        }

        const textarea = document.createElement("textarea");
        textarea.id = event.target.id;
        textarea.value = event.target.innerText;
        textarea.rows = Math.round((event.target.innerText.match(/(<br>|\n)/g) || []).length * 1.5) + 1;
        textarea.style.resize = "none"; //"vertical";

        event.target.parentNode.replaceChild(textarea, event.target);
        selected = textarea;
    } else if (selected && !event.target.isEqualNode(selected)) {
        const originalDiv = document.createElement("div");
        originalDiv.id = selected.id;
        originalDiv.classList.add("box");

        if (selected.tagName === "TEXTAREA") {
            originalDiv.innerText = selected.value;

            // const value = selected.value;
            // typeset(originalDiv, () => value);
            // typeset(originalDiv, () => value)
            //     .then(() => {
            //         selected.parentNode.replaceChild(originalDiv, selected);
            //     });
        }

        selected.parentNode.replaceChild(originalDiv, selected);
        selected = null;
    }
});

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

function prepareJSON(title, authors, content) {
    return result = {
        "title": title,
        "authors": authors,
        "content": content
    };
}

document.getElementById("add-box").onclick = function () {
    const container = document.getElementById("boxes");

    const box = document.createElement("div");
    box.classList.add("box");
    box.id = "editBox-" + (container.children.length);
    box.innerHTML = "Content";

    container.appendChild(box);
};

document.getElementById("save-content").onclick = function () {

    const content = [];
    const title = document.getElementById("title").innerText;
    const authors = document.getElementById("authors").innerText.split(",");

    const container = document.getElementById("boxes");
    for (let i = 0; i < container.children.length; i++) {
        const element = container.children[i];

        // console.log(i + 1, element.innerHTML);
        content[i] = element.innerHTML;
    }

    $.ajax({
        type: "POST",
        url: "poster_edit.php",
        data: {
            action: "content-upload",
            data: JSON.stringify(prepareJSON(title, authors, content))
        },
        success: function (response) {

            console.log(response);
        },
        error: function (err) {
            console.err(err);
        }
    });
};
