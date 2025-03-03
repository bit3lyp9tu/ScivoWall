
var author_names = [];

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

function prepareJSON(title, authors, content, visibility) {
    return result = {
        "title": title,
        "authors": authors,
        "content": content,
        "visibility": visibility
    };
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

async function hasValidUserSession() {
    return await $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "has-valid-user-session"
        },
        success: function (response) {
            return response;
        },
        error: function (err) {
            console.error(err);
            return false;
        }
    });
}
async function imageUpload(data, poster_id) {
    await $.ajax({
        type: "POST",
        url: "poster_edit.php",
        data: {
            action: "image-upload",
            data: data,
            id: poster_id
        },
        success: function (response) {
            console.log(response);
        },
        error: function (error) {
            console.error("fehler", error);
        }
    });
}

function converter(element, attribute, value) {
    switch (prefix) {
        case 'width':
            element.style.width = value;
            break;
        case 'height':
            element.style.height = value;
            break;
        case 'scale':

            break;
        default:
            console.error("Attribute [" + attribute + "] not found!\n");
            break;
    }
}
function style_parser(element, style) {
    const styles = style.replace(" ", "").split(",");

    for (let i = 0; i < styles.length; i++) {
        const elem = styles[i];
        console.log(elem);
        // converter(element, elem.split("=")[0], elem.split("=")[1]);
    }
}

async function getLoadedImg(poster_id, img_name, style) {
    const resp = await $.ajax({
        type: "POST",
        url: "poster_edit.php",
        data: {
            action: "get-image",
            //id: pk_id,
            poster_id: poster_id,
            name: img_name
        },
        success: function (response) {
            return response;
        },
        error: function (error) {
            return error;
        }
    });
    // console.log("data: ", JSON.parse(resp).data);

    const container = document.createElement("div");
    const img = document.createElement("img");
    img.src = JSON.parse(resp).data;
    img.style.width = '100%';
    img.style.objectFit = 'cover';

    container.appendChild(img);
    return container;
}

async function upload(id, data) {
    return await $.ajax({
        type: "POST",
        url: "poster_edit.php",
        data: {
            action: "content-upload",
            id: id,
            data: data
        },
        success: function (response) {
            return response;
        },
        error: function (err) {
            console.error(err);
            return err;
        }
    });
}

async function isEditView() {
    const data = url_to_json();
    const isValid = await hasValidUserSession();

    console.log(data["mode"], isValid);

    return (data["mode"] != null && data["mode"] == 'private' && isValid == 1);
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

async function typeset(container, code) {
    container.innerHTML = code();
    await MathJax.typesetPromise([container]);
}

async function show(response) {

    //TODO: load interactive elements only if mode=private + session-id valid

    // document.getElementById("title").innerHTML = /*marked.marked*/(response.title);
    await typeset(document.getElementById("title"), () => marked.marked(response.title));
    document.getElementById("title").setAttribute("data-content", response.title);

    // document.getElementById("authors").value = response.authors != null ? response.authors.toString(", ") : "";
    filloutAuthors(response.authors);

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
    if (select != null) {

        for (const key in response.vis_options) {
            var opt = document.createElement('option');
            opt.value = key;
            opt.innerHTML = response.vis_options[key];
            select.appendChild(opt);
        }
        select.value = response.visibility - 1;
    }
}

var selected_box = null;
var selected_title = null;
document.addEventListener("click", async function (event) {

    const url = url_to_json();

    //TODO: check if session-id valid
    if (url["mode"] != null && url["mode"] == 'private') {
        // Edit Title
        if (event.target.tagName === "DIV" && !event.target.id.startsWith("editBox") && event.target.children[0].id.startsWith("title") && selected_title === null) { // if new editBox gets selected

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

            loadImages();

            // forget old selected
            selected_box = null;
        }
    }
});

//TODO: check for invalid names during image upload
function imgDragDrop() {
    const url = url_to_json();

    // const dropZone = document.getElementById('drop-zone');
    const boxes = document.getElementById('boxes');

    if (boxes.length == 0) {
        console.error("No boxes-div found");
        return;
    }
    if (boxes.children.length == 0) {
        console.error("boxes has no children");
        return;
    }

    for (let i = 0; i < boxes.children.length; i++) {
        if (boxes.children[i].id.startsWith("editBox-" + i)) {

            const zone = boxes.children[i];

            zone.addEventListener('dragover', function (event) {
                event.preventDefault();
                colorBoxes('dashed');
            });

            zone.addEventListener('dragend', function (event) {
                colorBoxes('none');
            });

            zone.addEventListener('drop', async function (event) {
                event.preventDefault();

                colorBoxes('none');

                console.log("id", event.target.id);

                if (event.dataTransfer.files.length > 0) {
                    const file = event.dataTransfer.files[0];
                    const reader = new FileReader();

                    reader.onload = async function (e) {
                        console.log("Save Image...");
                        const data = {
                            "name": file.name,
                            "type": file.type,
                            "size": file.size,
                            "last_modified": 0,//file.lastModified,
                            "webkit_relative_path": file.webkitRelativePath,
                            "data": e.target.result
                        };
                        // console.log(data);

                        await imageUpload(data, url["id"]);
                    };

                    reader.readAsDataURL(file); // Read the file as base64

                    //TODO: modify editBox and insert \includegraphics{name}
                    insertImageMark(file.name, i);
                }
            });
        }
    }
}

function insertImageMark(name, index) {
    const imgMark = '<p placeholder="image">\includegraphics{' + name + "}</p>";

    const elem = document.getElementById("editBox-" + index);
    elem.innerHTML += imgMark;
    elem.setAttribute("data-content", elem.getAttribute("data-content") + imgMark);
}

function colorBoxes(type) {
    const boxes = document.getElementById("boxes");
    for (let i = 0; i < boxes.children.length; i++) {
        boxes.children[i].style.border = type;  //dashed or none
        boxes.children[i].style.borderColor = 'rgb(40, 176, 255)';
    }
}

function createEditMenu() {
    const parent = document.getElementById("edit-options");

    const link_container = document.createElement("dic");
    const link = document.createElement("a");
    link.href = "login.php";
    link_container.appendChild(link);

    const add_btn = document.createElement("button");
    add_btn.id = "add-box";
    add_btn.innerText = "Add Box";

    const save_btn = document.createElement("button");
    save_btn.id = "save-content";
    save_btn.innerText = "Save";

    const select_view_mode = document.createElement("select");
    select_view_mode.id = "view-mode";
    select_view_mode.name = "";

    parent.appendChild(link_container);
    parent.appendChild(add_btn);
    parent.appendChild(save_btn);
    parent.appendChild(select_view_mode);
}

window.onload = async function () {
    const data = url_to_json();
    let response = {};

    const state = await isEditView();
    if (state) {
        console.log("logged in");
        createEditMenu();
    } else {
        console.log("logged out");
    }

    try {
        response = await request(data);

        console.log(response);
    } catch (error) {
        console.error("content head request failed " + error);
    }

    if (response.status != 'error') {
        await show(response);
        loadImages();
        imgDragDrop();
        buttonEvents();

    } else {
        toastr["warning"]("Not Logged in");
    }

    // start with single textfield
    // once filed with content,
    //      insert new button field
    //      should button pressed - field gets removed

    // if (document.getElementById("typeahead").value) {
    //     console.log("content");

    //     const input = document.createElement("input");
    //     input.type = "search";
    //     input.id = "typeahead";
    //     // input.class = "tt-input";
    //     input.autocomplete = "on";
    //     input.placeholder = "...";

    //     const btn = document.createElement("button");
    //     btn.id = "remove-element";
    //     // btn.onclick = remove();
    //     btn.innerText = "X";

    //     const container = document.createElement("div");
    //     container.style.display = "flex";
    //     container.appendChild(input);
    //     container.appendChild(btn);

    //     document.getElementById("typeahead-container").appendChild(container);
    // }

    // function remove() {
    //     console.log("remove item", this.closest("div"));
    //     this.closest("div").remove();
    // }

    const inputElement = document.getElementById("typeahead");

    const instance = typeahead({
        input: inputElement,
        source: {
            local: author_names,
        }
    });
};

function author_item(value) {
    const p = document.createElement("p");
    p.innerText = value

    const btn = document.createElement("button");
    btn.id = "remove-element";
    btn.classList.add("author-item-btn");
    btn.innerText = "X";
    btn.onclick = function () {
        this.closest("div").remove();
    };

    const item = document.createElement("div");
    item.classList.add("author-item");
    item.appendChild(p);
    item.appendChild(btn);

    return item;
}

document.addEventListener("focusin", function (event) {
    // const inputElement = document.getElementById("typeahead");

    // const instance = typeahead({
    //     input: inputElement,
    //     source: {
    //         local: author_names,
    //     }
    // });
})

document.addEventListener("focusout", function (event) {
    if (event.target.id == "typeahead") {
        if (event.target.value != "") {
            // convert target into item

            const field = event.target;

            author_names.push(event.target.value);

            const new_elem = author_item(event.target.value)

            insertElementAtIndex(document.getElementById("typeahead-container"), new_elem, -1);
            event.target.value = "";

            // const input = document.getElementsByClassName("typeahead-standalone")
            // input.after(new_elem);
        }
    }
});

function addElementBeforeLast(parent_id, newElement, suffix) {
    const container = document.getElementById(parent_id);

    container.insertBefore(newElement, container.firstChild);
    // container.appendChild(suffix);
}

function insertElementAtIndex(container, newElement, index) {
    const children = Array.from(container.children);

    if (index <= children.length) {

        var i = 0;

        if (index < 0) {
            i = children.length + index;
        } else {
            i = index;
        }
        console.log(index, i);


        const referenceNode = children[i] || null;
        container.insertBefore(newElement, referenceNode);
    } else {
        console.error('Invalid index');
    }
}

function filloutAuthors(list) {
    for (let i = 0; i < list.length; i++) {
        author_names.push(list[i]);
        insertElementAtIndex(document.getElementById("typeahead-container"), author_item(list[i]), i);
    }
}

function getAuthorItems() {
    var list = [];
    const parent = document.getElementById("typeahead-container");

    for (let i = 0; i < parent.children.length; i++) {
        if (!parent.children[i].classList.contains("typeahead-standalone")) {
            list.push(parent.children[i].querySelector('p').innerText);
        }
    }
    return list;
}

function buttonEvents() {

    if (!document.getElementById("save-content")) {
        return;
    }
    document.getElementById("save-content").onclick = async function () {
        console.log("save");

        const header = url_to_json();

        const content = [];
        const title = document.getElementById("title").getAttribute("data-content");//innerText;
        const authors = getAuthorItems();

        const container = document.getElementById("boxes");
        for (let i = 0; i < container.children.length; i++) {
            const element = container.children[i];

            content[i] = element.getAttribute("data-content");
        }
        const visibility = document.getElementById("view-mode").value;
        const response = await upload(header.id, JSON.stringify(prepareJSON(title, authors, content, visibility)));

        if (response == -1) {

        } else if (response != "ERROR") {
            console.log(response);
        } else {
            console.error(response);
            toastr["error"]("An error occurred");
        }
    };
    if (!document.getElementById("add-box")) {
        return;
    }
    document.getElementById("add-box").onclick = function () {

        const container = document.getElementById("boxes");

        const content = "Content";

        const box = createArea("div", "editBox-" + (container.children.length), "box", content);
        box.innerHTML = content;

        container.appendChild(box);
    };
    if (!document.getElementById("img-load")) {
        return;
    }
    document.getElementById("img-load").onclick = async function () {
        loadImages();
    }
}

//TODO make load on page reload
async function loadImages() {
    const url = url_to_json();

    const boxes = document.getElementById("boxes");

    for (let i = 0; i < boxes.children.length; i++) {
        //TODO: check for invalid names during image upload

        const word = boxes.children[i].innerHTML.match(/\<p placeholder\=\"image\"\>includegraphics(\[.*\])?\{(\w|\s|-|_)+\.(png|jpg|gif)\}\<\/p\>/);
        if (word) {
            const name = word[0].slice(word[0].indexOf('{') + 1, word[0].indexOf('}'));
            const settings = word[0].includes('[') && word[0].includes(']') ? word[0].slice(word[0].indexOf('[') + 1, word[0].indexOf(']')) : "";

            // console.log(settings);
            style_parser(null, settings);

            const box_images = boxes.children[i].querySelectorAll("p[placeholder]");
            for (let j = 0; j < box_images.length; j++) {
                if (box_images[j].getAttribute("placeholder") == "image") {

                    boxes.children[i].replaceChild(await getLoadedImg(url["id"], name), box_images[j], settings);
                }
            }
        }
    }
}
