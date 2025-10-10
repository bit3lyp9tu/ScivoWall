
var author_names = [];

var log = console.log;

var selected_editBox = null;

function isInIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

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
            url: "/api/post_traffic.php",
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
    try {
        const response = await $.ajax({
            type: "POST",
            url: "/api/post_traffic.php",
            data: {
                action: "has-valid-user-session"
            }
        });
        return response;
    } catch (err) {
        console.debug("Session check failed:", err);
        return false;
    }
}
async function imageUpload(data, poster_id) {
    try {
        const response = await $.ajax({
            type: "POST",
            url: "/api/post_traffic.php",
            data: {
                action: "image-upload",
                data: data,
                id: poster_id
            },
        });
        console.log(response);
        return response;

    } catch (err) {
        console.error("Error", error);
        return false;
    }
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
        // console.log(elem);
        // converter(element, elem.split("=")[0], elem.split("=")[1]);
    }
}

async function getLoadedImg(poster_id, img_name, style) {
    const resp = await $.ajax({
        type: "POST",
        url: "/api/post_traffic.php",
        data: {
            action: "get-image",
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

    const container = document.createElement("div");
    const img = document.createElement("img");
    img.classList.add("box-img");

    var data = JSON.parse(resp).data;

    if (data.length == 1) {
        img.src = data;
    } else {
        img.src = "";
        // console.info('Image not found');
    }

    container.appendChild(img);
    return [data.length == 1, container];
}

async function upload(id, data) {
    try {
        const response = await $.ajax({
            type: "POST",
            url: "/api/post_traffic.php",
            data: {
                action: "content-upload",
                id: id,
                data: data
            }
        });
        return response;
    } catch (err) {
        console.debug("Upload failed:", err);
        return false;
    }
}

async function isEditView() {
    const data = url_to_json();
    const isValid = await hasValidUserSession();

    // console.log(data["mode"], isValid);

    return (data["mode"] != null && data["mode"] == 'private' && isValid == 1);
}

function createArea(type, id, classes, value) {

    const element = document.createElement(type);
    element.id = id;
    if (classes) {
        element.className = classes;
    }
    element.setAttribute("data-content", value);

    return element;
}

async function typeset(container, code) {
    container.innerHTML = code();
    await MathJax.typesetPromise([container]);
}

function createIcon(path) {
    const icon = document.createElement("img");
    icon.classList.add("icon");
    icon.setAttribute('draggable', 'false');
    icon.src = path;
    return icon;
}

function createMenu(parent_id) {
    // Wrapper
    const wrapper = document.createElement("label");
    wrapper.classList.add("box-menu-wrapper");

    // Input
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/png, image/jpeg, image/jpg, image/gif, .csv, text/csv, application/json, .json";
    input.classList.add("box-menu");
    input.style.display = "none";

    input.addEventListener("change", async (event) => {
        var element = document.getElementById(parent_id);
        console.log(element, $(event.target).closest("div"));

        const file = event.target.files[0];
        console.log("change", element, file);

        await importFile(element, file);

        const index = $(event.target).closest("div")[0].id.split("-")[1];
        console.log(index);
        await renderBox(index);
    });
    wrapper.appendChild(input);

    wrapper.appendChild(createIcon("/img/icons/Icons8_flat_opened_folder.svg"));

    const text = document.createElement("span");
    text.textContent = "";

    wrapper.appendChild(text);

    return wrapper;
}

function setAuthorSuggestions(selector, data) {
    // https://jqueryui.com/autocomplete/#categories

    if (data.length > 0 && typeof (data[0]) != "number") {
        if (!$.customCatcompleteDefined) {
            $.widget("custom.catcomplete", $.ui.autocomplete, {
                _create: function () {
                    this._super();
                    this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
                },
                _renderMenu: function (ul, items) {
                    var that = this,
                        currentCategory = "";
                    $.each(items, function (index, item) {
                        var li;
                        if (item.category !== currentCategory) {
                            ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                            currentCategory = item.category;
                        }
                        li = that._renderItemData(ul, item);
                        if (item.category) {
                            li.attr("aria-label", item.category + " : " + item.label);
                        }
                    });
                }
            });
            $.customCatcompleteDefined = false;
        }

        $(selector).catcomplete({
            delay: 0,
            source: data
        });

    } else {
        console.debug("no or unusual author suggestions", data);
    }
}

async function show(response) {

    //TODO:   load interactive elements only if mode=private + session-id valid

    // document.getElementById("title").innerHTML = /*marked.marked*/(response.title);
    await typeset(document.getElementById("title"), () => marked.marked(response.title));
    document.getElementById("title").setAttribute("data-content", response.title);

    // document.getElementById("authors").value = response.authors != null ? response.authors.toString(", ") : "";
    await filloutAuthors(response.authors);

    if (!isInIframe()) {
        const data = await getAuthorCollection();
        setAuthorSuggestions("#add_author", data);
    }

    const boxes = document.getElementById("boxes");

    for (const key in response.boxes) {
        if (response.boxes[key]) {
            const obj = createArea("div", "editBox-" + key, "", response.boxes[key]);

            if (0) {
                obj.innerHTML = response.boxes[key];
            } else {
                // obj.setAttribute("data-original", toMarkdown(response.boxes[key]));
                // obj.innerText = toMarkdown(response.boxes[key]);
                await typeset(obj, () => marked.marked(response.boxes[key]));
            }

            if (isInIframe()) {
                obj.setAttribute("in-iframe", "");
            } else {
                obj.appendChild(createMenu(obj.id));
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

        select.onchange = await save_content;
    }
}

async function importFile(output, file) {

    if (!file) return;

    if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const text = "\n```plotly-scatter\n" + e.target.result + "\n```";

            output.setAttribute("data-content", output.getAttribute("data-content") + text);
            output.value = output.value + text;
        };
        reader.readAsText(file);
    } else if (file.type.startsWith('image/')) {

        if (file.size <= 8388608) {
            const url = url_to_json();

            const reader = new FileReader();
            reader.onload = async function (e) {
                const data = {
                    "name": file.name,
                    "type": file.type,
                    "size": file.size,
                    "last_modified": 0,//file.lastModified,
                    "webkit_relative_path": file.webkitRelativePath,
                    "data": e.target.result
                };

                const new_name = await imageUpload(data, url["id"]);

                var text = "\n![file not found](" + file.name + ")";
                if (typeof new_name === 'string') {
                    text = "\n![file not found](" + new_name + ")";
                }

                output.setAttribute("data-content", output.getAttribute("data-content") + text);
                output.value = output.value + text;

            };
            reader.readAsDataURL(file);
        } else {
            const msg = "File too large. Maximum file size is 8.38MB";
            console.info(msg);
            toastr["warning"](msg);
        }

    } else if (file.type === 'application/json' || file.name.endsWith('.json')) {

        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const json = JSON.parse(e.target.result);
                const text = "\n```plotly\n" + JSON.stringify(json, null, 2) + "\n```";
                output.setAttribute("data-content", output.getAttribute("data-content") + text);
                output.value = output.value + text;

            } catch (error) {
                console.error("Error parsing JSON: " + error.message);
            }
        };
        reader.readAsText(file);

    } else {
        console.error('Unsupported file type.');
    }
}

//TODO:   check for invalid names during image upload
async function imgDragDrop() {
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

                        const index = event.target.id.split("-")[1];
                        await renderBox(index);
                    };

                    reader.readAsDataURL(file); // Read the file as base64

                    //TODO:   modify editBox and insert \includegraphics{name}
                    insertImageMark(file.name, i);
                }
            });
        }
    }

    await save_content();
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

    const add_btn = document.createElement("input");
    add_btn.type = "button";
    add_btn.id = "add-box";
    add_btn.value = "Add Box";

    const select_view_mode = document.createElement("select");
    select_view_mode.id = "view-mode";
    select_view_mode.name = "";

    parent.appendChild(link_container);
    parent.appendChild(add_btn);
    parent.appendChild(select_view_mode);
}

window.onload = async function () {
    const data = url_to_json();
    let response = {};

    if (isInIframe()) {
        console.log("in IFrame");

        document.getElementById("add_author").style.display = "none";
        document.getElementById("logout").style.display = "none";
        document.getElementsByTagName("footer")[0].style.display = "none";

        document.getElementById("projects").style.display = "none";

        document.body.classList.remove("bgimg");
        document.getElementById("scadslogo").src = "";
        document.getElementById("logo_headline").style.display = "none";

        document.querySelector("body").style.pointerEvents = "none";
        document.querySelector("body").style.userSelect = "none";
    }

    const state = await isEditView();
    if (state) {
        console.log("logged in");
        createEditMenu();
    } else {
        console.warn("User is not logged in");
        document.getElementById("logout").style.display = "none";
    }

    try {
        response = await request(data);
        console.log("load content", response);

    } catch (error) {
        console.error("content head request failed " + error);
    }

    if (response.status != 'error') {
        //TODO:   iterate single functions over a shared loop
        await show(response);

        await renderBox();

        buttonEvents();

    } else {
        toastr["warning"]("Not Logged in");
    }

    if (!await hasValidUserSession()) {
        document.getElementById("add_author").style.display = "none";

        document.getElementById("titles").classList.add("prevent-pointer-events");
        document.getElementById("boxes").classList.add("prevent-pointer-events");

        toastr["warning"]("Not Logged in");
    }
};

function selectEvent(target) {

    // change box to editable
    const element = createArea("textarea", target.id, target.className, target.getAttribute("data-content"));
    element.rows = 40;//Math.round((target.innerHTML.match(/(<br>|\n)/g) || []).length * 2) + 1;
    element.style.resize = "none";
    element.value = target.getAttribute("data-content");

    element.querySelectorAll('p[placeholder="plotly"]').forEach(e => {
        e.remove();
    });

    target.parentNode.replaceChild(element, target);

    element.focus();
    element.setSelectionRange(element.value.length, element.value.length);

    //  remember box as previously selected
    selected_editBox = element;
}

async function unselectEvent(index) {

    if (selected_editBox) {

        if (selected_editBox.value == "") {
            selected_editBox.remove();

        } else {
            if ((selected_editBox.id == "title" && selected_editBox.value.length * 4 >= 1024) || (selected_editBox.value.length * 4 >= 16777215)) {
                console.warn("Too Much Text");
                toastr["warning"]("Too Much Text");
                return;
            }
            // console.info(selected_editBox.value.length * 4, "bytes");

            const element = createArea("div", selected_editBox.id, selected_editBox.className, selected_editBox.value);

            await typeset(element, () => marked.marked(selected_editBox.value));
            if (selected_editBox && selected_editBox.parentNode) {
                selected_editBox.parentNode.replaceChild(element, selected_editBox);
            }

            var is_title = false;
            if (selected_editBox.id != "title") {
                element.appendChild(createMenu(element.id));

                is_title = true;
            }

            await renderBox(index, is_title, is_title);
        }

        // forget old selected
        selected_editBox = null;

        await save_content();
    }
}

function getSelectedTitle(target) {
    const titleEl = document.getElementById("title");

    if (target === titleEl) {
        return titleEl;
    }
    if (titleEl.contains(target)) {
        return titleEl;
    }
    if (target === titleEl.parentElement) {
        return titleEl;
    }
    return null;
}

const handler = async (event) => {

    if (await hasValidUserSession() && !isInIframe()) {

        if (event.type == "click") {
            const boxes = $("#boxes");
            const closest = $(event.target).closest("[data-content]");

            if (selected_editBox == null && closest.length > 0 && boxes[0] !== closest[0] && boxes.has(closest).length) {
                // select box

                selectEvent(closest[0]);

            } else if (selected_editBox != null && closest.length > 0 && selected_editBox.id == closest[0].id) {
                // selected same box again

            } else if (getSelectedTitle(event.target) && selected_editBox == null) {
                // select title

                const title_elem = getSelectedTitle(event.target);
                selectEvent(title_elem);

            } else if (getSelectedTitle(event.target) && selected_editBox && !boxes[0].contains(selected_editBox)) {
                // select title again

            } else {
                // deselect box

                if (selected_editBox) {
                    const index = selected_editBox.id.split("-")[1];
                    // console.log(index);
                    await unselectEvent(index);
                }
            }
        }

        if (event.type == "keydown") {
            if ((event.ctrlKey && event.code === "Enter") || event.code === "Escape") {
                if (selected_editBox) {
                    const index = selected_editBox.id.split("-")[1];
                    // console.log(index);
                    await unselectEvent(index);
                }
            }
        }
    }
};

document.addEventListener("click", handler);
document.addEventListener("keydown", handler);

async function author_item(value) {
    const title = document.getElementById("title").innerText;

    const p = document.createElement("p");
    p.innerText = value

    const item = document.createElement("div");
    item.classList.add("author-item");
    item.setAttribute("draggable", "true");
    item.appendChild(p);

    if (!isInIframe()) {

        const btn = document.createElement("button");
        //btn.id = "remove-element";
        btn.classList.add("author-item-btn");
        btn.classList.add("remove-element");
        btn.onclick = async function () {
            this.closest("div").remove();
            await save_content();
        };
        btn.appendChild(createIcon("/img/icons/Icons8_flat_delete_generic.svg"));

        const data = await getAuthorCollection();
        data.push({ "label": value, "category": title });
        //console.log("item", data);
        setAuthorSuggestions("#add_author", data);

        item.appendChild(btn);
    } else {
        item.setAttribute("draggable", "false");
        item.setAttribute("in-iframe", "");
    }

    return item;
}
$(document).on("focusout keydown", async function (event) {
    if (event.target.id == "add_author") {
        if (
            (event.type === "focusout" && event.target.value != "") ||
            (event.type === "keydown" && event.key === "Enter" && event.target.value != "")
        ) {
            if (event.target.value.length * 4 >= 1024) {
                console.warn("Too Much Text");
                toastr["warning"]("Too Much Text");
                return;
            }
            // console.info(event.target.value.length * 4, "bytes");

            // convert target into item

            const field = event.target;

            author_names.push(event.target.value);

            const new_elem = await author_item(event.target.value);

            const author_element = document.getElementById("authors");

            await insertElementAtIndex(author_element, new_elem, author_element.children.length - 1);
            event.target.value = "";
            await save_content();
        }
    }
});

function addElementBeforeLast(parent_id, newElement, suffix) {
    const container = document.getElementById(parent_id);

    container.insertBefore(newElement, container.firstChild);
    // container.appendChild(suffix);
}

function getAuthorCollection() {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: "POST",
            url: "/api/post_traffic.php",
            data: {
                action: 'fetch_author_collection'
            },
            success: function (response) {
                if (response !== "No or invalid session" && response !== "No results found") {

                    var data = [];
                    var authors = JSON.parse(response).author;
                    var posters = JSON.parse(response).title;

                    for (var i = 0; i < authors.length; i++) {

                        data.push({ "label": authors[i], "category": posters[i] });
                    }
                    //console.log(data);

                    resolve(data);
                } else {
                    resolve([]);
                }
            },
            error: function (err) {
                console.error(err);
                reject(err);
            }
        });
    });
}

async function insertElementAtIndex(container, newElement, index) {
    // const children = document.querySelectorAll(".author-item");
    //console.log("author children", container.children, index);

    container.insertBefore(await newElement, container.children[index]);
}

async function filloutAuthors(list) {
    for (let i = 0; i < list.length; i++) {
        author_names.push(list[i]);
        await insertElementAtIndex(document.getElementById("authors"), author_item(list[i]), i);
    }
}

function getAuthorItems() {
    var list = [];
    const parent = document.getElementById("authors");

    for (let i = 0; i < parent.children.length; i++) {
        if (parent.children[i].tagName != "input" && parent.children[i].type != "text") {
            list.push(parent.children[i].querySelector('p').innerText);
        }
    }
    return list;
}

async function save_content() {
    console.debug("save content");

    const header = url_to_json();

    if (await hasValidUserSession()) {
        const content = [];
        var title = document.getElementById("title").getAttribute("data-content");//innerText;
        if (title == "") {
            title = "Title";
        }
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
            if (response != "") {
                console.log(response);
            }
        } else {
            console.error(response);
            toastr["error"]("An error occurred");
        }
    } else {
        console.info('User session is not valid');
    }
}

function buttonEvents() {
    if (!document.getElementById("add-box")) {
        return;
    }

    document.getElementById("add-box").onclick = async function () {

        const container = document.getElementById("boxes");

        const content = "## *Content here* \n\n*For more information go to the [documentation](https://github.com/bit3lyp9tu/scientific_poster_generator/blob/main/README.md).*";

        const box = createArea("div", "editBox-" + (container.children.length), "", content);
        await typeset(box, () => marked.marked(content));

        box.appendChild(createMenu(box.id));

        container.appendChild(box);

        await renderBox();

        await save_content();
    };
}

function drawChart(box_index, placeholders, local_index, content) {
    placeholders[local_index].id = "plotly-" + box_index + "-" + local_index;

    // if (content["layout"]["autosize"]) {
    // }
    // content["layout"]["autosize"] = "true";
    // "autosize": "true"
    content["config"] = {
        "resize": "true",
        "scrollZoom": "true",
        "staticPlot": "true",
        "displayModeBar": "false"
    };

    Plotly.newPlot(placeholders[local_index], content["data"], content["layout"], content["config"]);
    placeholders[local_index].innerHTML = placeholders[local_index].innerHTML.replace(/\{[\{,\},\[,\],\,\:,\",\',\-,\s,\w+]*\}/gm, "");
}

function soft_remove_class(element, class_name) {
    if (element.classList.contains(class_name)) {
        element.classList.remove(class_name);
    }
}

function class_to_data(class_str) {
    if (class_str) {
        var tag = "";
        if (class_str.includes(" ")) {
            tag = class_str.split(" ")[0];
        } else {
            tag = class_str;
        }
        if (tag.includes("language-")) {
            const placeholder = tag.split("language-")[1];
            if (placeholder.includes("-")) {
                return { "placeholder": placeholder.split("-")[0], "chart": placeholder.split("-")[1] };
            } else {
                return { "placeholder": placeholder, "chart": "" };
            }
        }
    }

}

function warn_msg(msg) {
    console.warn(msg);
    toastr["warning"](msg);
}

async function renderSingleBox(url, boxes, index, inclImg, inclPlotly) {
    const placeholders = boxes.children[index].querySelectorAll("p>img, pre>code.language-plotly, pre>code.language-plotly-scatter, pre>code.language-plotly-line, pre>code.language-plotly-bar, pre>code.language-plotly-pie");

    soft_remove_class(boxes.children[index], "found-error");

    for (var j = 0; j < placeholders.length; j++) {
        if (inclImg && placeholders[j].parentNode.querySelector("img")) {
            const img_info = placeholders[j].parentNode.innerHTML.match(/\<img\ssrc\=\"[^\"\.]*\.(png|jpg|gif)\"\salt\=\"[^\"]+\"\>/gm);

            if (img_info) {
                if (img_info[0]) {
                    var name = img_info[0].match(/[^\"\.]*\.(png|jpg|gif)/)[0];
                    var loaded_img = await getLoadedImg(url["id"], name);
                    var img = loaded_img[1];

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(img.innerHTML, "text/html");
                    const img_new = doc.body.querySelector('img');

                    if (!loaded_img[0]) {
                        boxes.children[index].classList.add("found-error");

                        const msg = "Image [" + name + "] not found";

                        console.warn(msg);
                        toastr["warning"](msg);
                    }
                    boxes.children[index].querySelectorAll("p>img")[j].src = img_new.src;

                } else {
                    warn_msg("No image data");
                }
            }
        }

        const data = class_to_data(placeholders[j].getAttribute("class"));
        if (data) {
            if (inclPlotly && data["placeholder"] == "plotly") {
                if (data["chart"] && ["scatter", "line", "bar", "pie"].includes(data["chart"])) {   // inport csv
                    var content = simple_plot(data["chart"], placeholders[j].innerHTML);
                    console.debug("template", content);

                    if (content) {
                        boxes.children[index].querySelectorAll("pre>code")[j].innerHTML = placeholders[j].innerHTML.replaceAll(/[\n]/gm, "");
                        drawChart(index, placeholders, j, content);
                    }

                } else if (data["chart"] == "") {  // inport json
                    console.groupCollapsed("JSON data");
                    console.info(placeholders[j].innerHTML);
                    console.groupEnd();

                    if (placeholders[j].innerHTML) {
                        const regex = /<\/?\w+[^>]*>/g;

                        var content = json_parse(repairJson(placeholders[j].innerHTML.replaceAll(regex, "")));
                        console.debug(content);

                        if (content) {
                            boxes.children[index].querySelectorAll("pre>code")[j].innerHTML = placeholders[j].innerHTML.replaceAll(/[\n]/gm, "");
                            drawChart(index, placeholders, j, content);
                        }

                    } else {
                        warn_msg("File empty");
                    }

                } else {    // error
                    warn_msg("Unsupported chart type");
                    console.warn("data", data);
                }
            }
        }
    }
}

async function renderBox(index = -1, inclImg = true, inclPlotly = true) {
    const url = url_to_json();
    const boxes = document.getElementById("boxes");

    if (Number.isInteger(Number(index)) && index >= 0) {
        renderSingleBox(url, boxes, index, inclImg, inclPlotly);
    } else {
        for (let i = 0; i < boxes.children.length; i++) {
            renderSingleBox(url, boxes, i, inclImg, inclPlotly);
        }
    }
}

function loadJSON(file) {
    return fetch(file)
        .then((response) => response.json())
        .then((json) => json);
}

function json_parse(data) {
    try {
        if (data !== "") {
            return JSON.parse(data);
        } else {
            return {};
        }
    } catch (e) {
        console.error("Invalid JSON:", data, "msg: ", e);
        console.log(data);

        toastr["warning"]("Invalid JSON");
    }

    return null;
}

function simple_plot(type, content) {
    var template_content = {};//await loadJSON("./plotly/" + type + ".json");

    if (true) {
        var data = CSVtoJSON(content);

        if (Object.keys(data).length % 2 === 0) {

            var list = [];
            var header = content.replace(/^\n/, '').replace(/\n$/, '').replace(" ", "").split("\n")[0].split(",")

            for (let i = 0; i < header.length; i += 2) {
                var point_group = {};

                point_group["x"] = data[header[i]];
                point_group["y"] = data[header[i + 1]];

                point_group["name"] = header[i + 1];

                if (type === "scatter") {
                    point_group["mode"] = "markers";
                    point_group["type"] = "scatter";

                } else if (type === "line") {
                    point_group["mode"] = "lines";
                    point_group["type"] = "scatter";

                } else if (type === "bar") {
                    point_group["type"] = "bar";

                } else if (type === "pie") {

                    delete point_group["x"];
                    delete point_group["y"];

                    point_group["labels"] = data[header[i]];
                    point_group["values"] = data[header[i + 1]];

                    point_group["type"] = "pie";
                } else {
                    const msg = "Unsupported chart type: [" + type + "]";

                    console.warn(msg);
                    toastr["warning"](msg);
                }

                list.push(point_group);
            }
            template_content["data"] = list;
        } else {
            const msg = "Unsupported input: Uneven Columns";

            console.warn(msg, content);
            toastr["warning"](msg);
        }
    }
    return template_content;
}

function CSVtoJSON(csv_string) {

    var result = {};
    var lines = csv_string.replace(/^\n/, '').replace(/\n$/, '').replace(" ", "").split("\n");

    var keys = lines[0].split(",");

    for (let i = 0; i < keys.length; i++) {
        var l = [];
        for (let j = 1; j < lines.length; j++) {

            var cell = lines[j].split(",")[i];

            if (!isNaN(cell)) {
                l.push(parseFloat(cell));
            } else {
                l.push(cell);
            }
        }
        result[keys[i]] = l;
    }
    return result;
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
var drop_target = null;

$(document).on("dragstart", async function (event) {

    if (document.getElementById("authors").contains(event.target)) {
        event.target.classList.add("ghost-author");

        dragend_item = event.target;//.cloneNode(true);
        // event.target.style.display = "none";
    }
    is_draging = true;

    await save_content();
});

function remove_ghost_author() {
    var ghosts = document.querySelectorAll('[is_ghost="true"]');
    for (var i = 0; i < ghosts.length; i++) {
        ghosts[i].remove();
    }
}

$(document).on("dragover", function (event) {
    event.preventDefault();

    if (document.getElementById("authors").contains(event.target) && $(event.target).prop("class") == "author-item") {

        const mouseX = event.clientX;
        const mouseY = event.clientY;

        const rect = event.target.getBoundingClientRect();
        const { x, y, width, height } = rect;

        if (dragend_item != event.target) {
            if (document.querySelectorAll('[is_ghost="true"]').length <= 0) {

                const p = document.createElement("p");
                p.innerText = "";
                const ghost_author = document.createElement("div");
                ghost_author.classList.add("author-item");
                ghost_author.classList.add("ghost-author");
                ghost_author.setAttribute("is_ghost", "true");
                ghost_author.appendChild(p);

                ghost_author.style.width = "5rem";

                if (mouseX >= x + width / 2 && mouseX <= x + width) {
                    if (!$(event.target).next().is('[is_ghost="true"]')) {
                        if ($(event.target).prop("tagName") == "P") {
                            $(event.target).parent().after(ghost_author);
                        } else {
                            event.target.after(ghost_author);
                        }
                        drop_target = event.target;
                    }
                }
                if (mouseX > x && mouseX < x + width / 2) {
                    if (!$(event.target).prev().is('[is_ghost="true"]')) {
                        if ($(event.target).prop("tagName") == "P") {
                            $(event.target).parent().before(ghost_author);
                        } else {
                            event.target.before(ghost_author);
                        }
                        drop_target = event.target;
                    }
                }

            } else {
                if (drop_target != event.target) {
                    remove_ghost_author();
                    drop_target = null;
                }
            }
        }
    } else {
        remove_ghost_author();
        drop_target = null;
    }
});

// document.addEventListener("dragend", function (event) {
//     console.log("C: ", event.target)
// });

$(document).on("drop", async function (event) {
    event.preventDefault();

    if (document.getElementById("authors").contains(event.target)) {
        if (event.target.tagName == "P") {
            event.target.parentElement.after(dragend_item);
        } else {
            event.target.after(dragend_item);
        }
    }
    dragend_item.classList.remove("ghost-author");

    is_draging = false;

    var ghosts = document.querySelectorAll('[is_ghost="true"]');
    for (var i = 0; i < ghosts.length; i++) {
        ghosts[i].remove();
    }

    // document.querySelectorAll('#authors>.author-item').forEach(element => {
    //     element.style.display = "show";
    // });

    await save_content();
});

$(document).on("mouseover", function (event) {
    // if (is_draging) {
    //     console.log(is_draging);
    //     console.log(event.target);
    // }
});

$(document).on("click", "#logout", function () {
    console.debug("logout");

    $.ajax({
        type: "POST",
        url: "/api/post_traffic.php",
        data: {
            action: 'logout'
        },
        success: function (response) {
            // console.log(response);
            window.location.href = "login.php";
            if (response == "1") {
                toastr["success"]("Logout Successful");
            } else {
                toastr["error"]("An logout error occurred");
            }
        },
        error: function () {
            toastr["error"]("An logout error occurred");
        }
    });
});
