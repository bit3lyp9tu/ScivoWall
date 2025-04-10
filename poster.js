
var author_names = [];

var log = console.log;

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
            console.error("Error", error);
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
    console.log(JSON.parse(resp));

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

function edit_box_if_no_other_was_selected(_target) {
    // change box to editable
    const element = createArea("textarea", _target.id, "", _target.getAttribute("data-content"));
    element.rows = Math.round((_target.innerText.match(/(<br>|\n)/g) || []).length * 1.5) + 1;
    element.style.resize = "none"; //"vertical";
    element.value = _target.getAttribute("data-content");

    element.querySelectorAll('p[placeholder="plotly"]').forEach(e => {
        e.remove();
    });

    _target.parentNode.replaceChild(element, _target);

    //  remember box as previously selected
    selected_box = element;
}

async function unedit_box() {
    //console.log("TESTETSTSTTSTTS", selected_box.value.replaceAll("\n", "").replace(/(?<=\>).*(?=\<\/p\>)/g, ``));
    // selected_box = selected_box.value.replaceAll("\n", "").replace(/(?<=\>).*(?=\<\/p\>)/g, ``);
    // if (element.querySelector("p[placeholder='plotly']")) {
    // }

    // change old back to non-editable and save old edits
    if (selected_box) {
        const element = createArea("div", selected_box.id, "box", selected_box.value);

        await typeset(element, () => marked.marked(selected_box.value));
        if (selected_box && selected_box.parentNode) {
            selected_box.parentNode.replaceChild(element, selected_box);
        }

        loadImages();

        loadPlots();

        // forget old selected
        selected_box = null;
    }

    initEditBoxes();
}

async function edit_box(_target) {
    if (_target.tagName === "DIV" && _target.id.startsWith("editBox") && selected_box === null) { // if new editBox gets selected
        edit_box_if_no_other_was_selected(_target)
    } else if (selected_box && _target !== selected_box) {// if there was something once selected and if the new selected is different from the old
        unedit_box();
    }
}

function initUneditHandler() {
    document.addEventListener('click', function (event) {
        // Prüfe, ob der Klick *innerhalb* einer editBox war
        const isInsideEditBox = event.target.closest('[id^="editBox-"]');

        // Wenn NICHT innerhalb einer editBox → unedit_box() aufrufen
        if (!isInsideEditBox) {
            unedit_box();
        }
    });
}

async function initEditBoxes() {
    if (await isEditView()) {
        const editBoxes = Array.from(document.querySelectorAll('[id^="editBox-"]'));

        editBoxes.forEach(box => {
            // Prüfen, ob der Listener bereits gesetzt wurde
            if (!box._hasEditBoxClickListener) {
                box.addEventListener('click', function (event) {
                    // Abbrechen, wenn das geklickte Element Teil der Plotly-UI ist
                    if (event.target.closest('.modebar-container, .plotly, .zoomlayer')) {
                        return; // Ignoriere Plotly-interne Klicks
                    }

                    edit_box(this);
                });

                box._hasEditBoxClickListener = true;
            }
        });
    }
}

async function edit_box_event(event) {
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
        //edit_box(event.target)
    }
}

document.addEventListener("click", edit_box_event);

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

    // loadPlot(
    //     "tester",
    //     [
    //         {
    //             x: [1, 2, 3, 4, 5],
    //             y: [1, 2, 4, 8, 16]
    //         },
    //         {
    //             x: [1, 2, 3, 4, 5],
    //             y: [2, 2, 1, 11, 15]
    //         }
    //     ],
    //     {
    //         margin: { t: 1 }
    //     }
    // );

    if (response.status != 'error') {
        //TODO: iterate single functions over a shared loop
        await show(response);
        loadImages();

        loadPlots();

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

    initEditBoxes();
    initUneditHandler();
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
    item.setAttribute("draggable", "true");
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

        // TODO: redraw plotly
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

function json_parse(data) {
    try {
        var res = JSON.parse(data);
        return res;
    } catch (e) {
        console.error(e);
        console.error("This JSON caused this to happen:", data)
    }

    return null;
}

function loadPlots() {
    var boxes = document.getElementById("boxes");

    for (let i = 0; i < boxes.children.length; i++) {
        var parent_element = boxes.children[i];
        const data_content = parent_element.getAttribute("data-content");

        //TODO: bug?!
        console.log("content", parent_element.innerText);

        const head_data = data_content.match(/(?<=\<p\s)[\s,placeholder\=\"plotly\",(\w+\=\"?\'?\w+\"?\'?)]+(?=\>)/sgm);
        const body = data_content.match(/(?<=<p\s?[\s,(\w+\=\"?\'?\w+\"?\'?)]*>).*?(?=\<\/p\>)/gsm);

        if (head_data) {
            const placeholder_list = parent_element.querySelectorAll('p[placeholder]');

            for (let j = 0; j < placeholder_list.length; j++) {
                if (placeholder_list[j].getAttribute("placeholder") == "plotly") {

                    var content = json_parse(repairJson(body[j]));

                    if (content && Object.keys(content).length != 0) {
                        placeholder_list[j].id = "plotly-" + i + "-" + j;
                        Plotly.newPlot(placeholder_list[j], content["data"], content["layout"], content["config"]);
                        placeholder_list[j].innerHTML = placeholder_list[j].innerHTML.replace(/\{[\{,\},\[,\],\,\:,\",\',\s,\w+]*\}/gm, "");
                    }
                }
            }
        }
    }
}

function header_data_to_json(content) {
    var json = {};

    content.split(" ").forEach(element => {
        const s = element.split("=");

        json[String(s[0])] = s[1].replaceAll(`"`, ``);
    });
    return json;
}

function repairQuoting(string) {
    return string.replace(/\b(\w+)(?=\s*:)/g, function (i) {
        return `"` + i + `"`;
    }).replaceAll(`'`, `"`);
}

function repairJson(input) {
    var str = input.replace(/\b(\w+)(?=\s*:)/g, function (i) {
        return `"` + i + `"`;
    }).replaceAll(`'`, `"`);

    return str.replaceAll(/\/\/.*\n/gm, `\n`);
}

var dragged_text = "";
var is_draging = false;
var dragend_item = null;

document.addEventListener("dragstart", function (event) {

    if (document.getElementById("typeahead-container").contains(event.target)) {
        event.target.style.border = "dashed";
        event.target.style.borderColor = "#83d252";
        event.target.style.border.width = "thin";

        dragend_item = event.target;
    }
    is_draging = true;
});

document.addEventListener("dragover", function (event) {
    event.preventDefault();

    // if (document.getElementById("typeahead-container").contains(event.target)) {
    //     console.log(event.target);
    // }

});

// document.addEventListener("dragend", function (event) {
//     console.log("C: ", event.target);

// });

document.addEventListener("drop", function (event) {
    event.preventDefault();

    if (document.getElementById("typeahead-container").contains(event.target)) {
        if (event.target.tagName == "P") {
            event.target.parentElement.after(dragend_item);
        } else {
            event.target.after(dragend_item);
        }
    }
    dragend_item.style.border = "solid";
    dragend_item.style.borderColor = "#83d252";
    dragend_item.style.borderWidth = "1px";

    is_draging = false;
});

document.addEventListener("mouseover", function (event) {
    // if (is_draging) {
    //     console.log(is_draging);
    //     console.log(event.target);
    // }
});
