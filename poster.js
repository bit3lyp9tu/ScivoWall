
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

function createArea(type, id, class_name, value) {

    const element = document.createElement(type);
    element.id = id;
    if (class_name != "") {
        //element.classList.add(class_name);
    }
    element.setAttribute("data-content", value);

    return element;
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

async function typeset(container, code) {
    // MathJax.startup.promise = MathJax.startup.promise
    //     .then(() => {
    //         container.innerHTML = code();
    //         return MathJax.typesetPromise([container]);
    //     })
    //     .catch((err) => console.log('Typeset failed: ' + err.message));
    // return MathJax.startup.promise;

    container.innerHTML = code();
    await MathJax.typesetPromise([container]);
}

async function show(response) {
    // document.getElementById("title").innerHTML = /*marked.marked*/(response.title);
    await typeset(document.getElementById("title"), () => marked.marked(response.title));
    document.getElementById("title").setAttribute("data-content", response.title);

    document.getElementById("authors").innerText = response.authors.toString(", ");

    const boxes = document.getElementById("boxes");

    for (const key in response.boxes) {
        if (response.boxes[key]) {
            const obj = createArea("div", "editBox-" + key, "box", response.boxes[key]);

            if (0) {
                obj.innerHTML = response.boxes[key];
            } else {
                // obj.setAttribute("data-original", toMarkdown(response.boxes[key]));
                // obj.innerText = toMarkdown(response.boxes[key]);
                await typeset(obj, () => marked.marked(response.boxes[key]));
            }

            boxes.appendChild(obj);
        }
    }

    var select = document.getElementById('view-mode');

    for (const key in response.vis_options) {
        var opt = document.createElement('option');
        opt.value = key;
        opt.innerHTML = response.vis_options[key];
        select.appendChild(opt);
    }
    select.value = response.visibility - 1;
}

//???????????
function selectElement(target_id, pointer) {
    const element = document.getElementById(target_id);

    if (pointer.children[0].id == target_id) {
        return pointer.children[0];
    }

    return null;
}

var selected_box = null;
var selected_title = null;
document.addEventListener("click", async function (event) {

    // Edit Title
    if (event.target.tagName === "DIV" && event.target.children[0].id.startsWith("title") && selected_title === null) { // if new editBox gets selected

        // change box to editable
        const element = createArea("textarea", event.target.children[0].id, "", event.target.children[0].getAttribute("data-content"));
        element.style.resize = "none"; //"vertical";
        element.value = event.target.children[0].getAttribute("data-content");
        event.target.children[0].parentNode.replaceChild(element, event.target.children[0]);
        element.style['pointer-events'] = 'auto';

        // remember box as previously selected
        selected_title = element;

    } else if (selected_title && event.target !== selected_title) {// if there was something once selected and if the new selected is different from the old

        // change old back to non-editable and save old edits
        const element = createArea("div", selected_title.id, "", selected_title.value);
        await typeset(element, () => marked.marked(selected_title.value));
        selected_title.parentNode.replaceChild(element, selected_title);
        element.style['pointer-events'] = 'none';

        // forget old selected
        selected_title = null;
    }

    // Edit Boxes
    if (event.target.tagName === "DIV" && event.target.id.startsWith("editBox") && selected_box === null) { // if new editBox gets selected

        // change box to editable
        const element = createArea("textarea", event.target.id, "", event.target.getAttribute("data-content"));
        element.rows = Math.round((event.target.innerText.match(/(<br>|\n)/g) || []).length * 1.5) + 1;
        element.style.resize = "none"; //"vertical";
        element.value = event.target.getAttribute("data-content");
        event.target.parentNode.replaceChild(element, event.target);

        //  remember box as previously selected
        selected_box = element;

    } else if (selected_box && event.target !== selected_box) {// if there was something once selected and if the new selected is different from the old

        // change old back to non-editable and save old edits
        const element = createArea("div", selected_box.id, "box", selected_box.value);
        await typeset(element, () => marked.marked(selected_box.value));
        selected_box.parentNode.replaceChild(element, selected_box);

        // forget old selected
        selected_box = null;
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

function prepareJSON(title, authors, content, visibility) {
    return result = {
        "title": title,
        "authors": authors,
        "content": content,
        "visibility": visibility
    };
}

document.getElementById("add-box").onclick = function () {
    const container = document.getElementById("boxes");

    const content = "Content";

    const box = createArea("div", "editBox-" + (container.children.length), "box", content);
    box.innerHTML = content;

    container.appendChild(box);
};

document.getElementById("save-content").onclick = function () {

    const content = [];
    const title = document.getElementById("title").getAttribute("data-content");//innerText;
    const authors = document.getElementById("authors").innerText.split(",");

    const container = document.getElementById("boxes");
    for (let i = 0; i < container.children.length; i++) {
        const element = container.children[i];

        // console.log(i + 1, element.innerHTML);
        content[i] = element.getAttribute("data-content");
    }

    const visibility = document.getElementById("view-mode").value;

    $.ajax({
        type: "POST",
        url: "poster_edit.php",
        data: {
            action: "content-upload",
            data: JSON.stringify(prepareJSON(title, authors, content, visibility))
        },
        success: function (response) {

            console.log(response);
        },
        error: function (err) {
            console.err(err);
        }
    });
};
