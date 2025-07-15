
var author_names = [];

var log = console.log;

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
            url: "post_traffic.php",
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
        url: "post_traffic.php",
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
        url: "post_traffic.php",
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
        // console.log(elem);
        // converter(element, elem.split("=")[0], elem.split("=")[1]);
    }
}

async function getLoadedImg(poster_id, img_name, style) {
    const resp = await $.ajax({
        type: "POST",
        url: "post_traffic.php",
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
    // console.log(JSON.parse(resp));

    const container = document.createElement("div");
    const img = document.createElement("img");
    img.classList.add("box-img");
    img.src = JSON.parse(resp).data;

    container.appendChild(img);
    return container;
}

async function upload(id, data) {
    return await $.ajax({
        type: "POST",
        url: "post_traffic.php",
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

    // console.log(data["mode"], isValid);

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

function createMenu() {
    var menu = document.createElement("input");
    menu.type = "file";
    // menu.value = "*";
    menu.accept = "image/png, image/jpeg, image/jpg, image/gif, .csv, text/csv, application/json, .json";
    menu.classList.add("box-menu");
    menu.onclick = function () {
        console.log("box menu");
    }
    return menu;
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
    } else {
        console.error("no or unusual author suggestions");
    }

    $(selector).catcomplete({
        delay: 0,
        source: data
    });
}

async function show(response) {

    //TODO:   load interactive elements only if mode=private + session-id valid

    // document.getElementById("title").innerHTML = /*marked.marked*/(response.title);
    await typeset(document.getElementById("title"), () => marked.marked(response.title));
    document.getElementById("title").setAttribute("data-content", response.title);

    // document.getElementById("authors").value = response.authors != null ? response.authors.toString(", ") : "";
    filloutAuthors(response.authors);

    const data = await getAuthorCollection();
    setAuthorSuggestions("#add_author", data);

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

            if (isInIframe()) {
                obj.setAttribute("in-iframe", "");
            } else {
                obj.appendChild(createMenu());
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
    element.rows = Math.round((_target.innerHTML.match(/(<br>|\n)/g) || []).length * 1.5) + 1;
    element.style.resize = "none"; //"vertical";
    element.value = _target.getAttribute("data-content");

    element.querySelectorAll('p[placeholder="plotly"]').forEach(e => {
        e.remove();
    });

    _target.parentNode.replaceChild(element, _target);

    element.focus();
    element.setSelectionRange(element.value.length, element.value.length);

    //  remember box as previously selected
    selected_box = element;
}

async function importFile(output, file) {

    if (!file) return;

    if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const text = `\n<p placeholder="plotly" chart="scatter">\n` + e.target.result + `</p>`;

            output.setAttribute("data-content", output.getAttribute("data-content") + text);
            output.value = output.value + text;
        };
        reader.readAsText(file);
    } else if (file.type.startsWith('image/')) {

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

            await imageUpload(data, url["id"]);

            const text = '\n<p placeholder="image">\includegraphics{' + file.name + "}</p>";
            output.setAttribute("data-content", output.getAttribute("data-content") + text);
            output.value = output.value + text;

        };
        reader.readAsDataURL(file);

    } else if (file.type === 'application/json' || file.name.endsWith('.json')) {

        // TODO:    violin-plot.json/violin-plot-small.json doesn't work
        // TODO:    polar-sub-chart.json only works occasionally

        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const json = JSON.parse(e.target.result);
                const text = `\n<p placeholder="plotly">\n` + JSON.stringify(json, null, 2) + `\n</p>`;
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

        element.appendChild(createMenu());

        var inbtn = document.querySelector('#' + selected_box.id + '>input[type="file"]');
        // console.log(inbtn);

        // TODO: [BUG] editBox needs to be selected previously at least once for the change event to be detected
        inbtn.addEventListener('change', function (event) {

            const file = event.target.files[0];
            importFile(selected_box, file);
        });

        loadImages();

        loadPlots();

        // forget old selected
        selected_box = null;

        await save_content();
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

	//TODO:   check if session-id valid
	if (url["mode"] != null && url["mode"] == 'private') {

		console.log("event.target", event.target);

		// Edit Title

		if (typeof event !== 'undefined' && event.target) {
			console.log(event.target);
			if (event.target.tagName === "DIV" && !event.target.id.startsWith("editBox") && event.target.id.startsWith("title") && selected_title === null) { // if new editBox gets selected

				// change box to editable
				const element = createArea("textarea", event.target.id, "", event.target.getAttribute("data-content"));
				element.style.resize = "none"; //"vertical";
				element.value = event.target.getAttribute("data-content");
				event.target.parentNode.replaceChild(element, event.target);
				element.style['pointer-events'] = 'auto';

				element.focus();
				element.setSelectionRange(element.value.length, element.value.length);

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
		} else {
			console.error("event.target is empty. event:", event);
		}

		// Edit Boxes
		//edit_box(event.target)
	}

	await save_content();
}

document.addEventListener("click", edit_box_event);

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

                        loadImages();
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

        document.getElementById("img-load").style.display = "none";

        document.getElementsByTagName("footer")[0].style.display = "none";
    }

    const state = await isEditView();
    if (state) {
        console.log("logged in");
        createEditMenu();
    } else {
        console.log("logged out");
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
        loadImages();

        loadPlots();

        imgDragDrop();
        buttonEvents();

    } else {
        toastr["warning"]("Not Logged in");
    }

    initEditBoxes();
    initUneditHandler();
};

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
        btn.innerText = "";
        btn.onclick = function () {
            this.closest("div").remove();
        };

        const data = await getAuthorCollection();
        data.push({ "label": value, "category": title });
        console.log("item", data);
        setAuthorSuggestions("#add_author", data);

        item.appendChild(btn);
    } else {
        item.setAttribute("draggable", "false");
        item.setAttribute("in-iframe", "");
    }

    return item;
}

document.addEventListener("focusout", async function (event) {
    if (event.target.id == "add_author") {
        console.log(event.target);
        if (event.target.value != "") {
            // convert target into item

            const field = event.target;

            author_names.push(event.target.value);

            const new_elem = await author_item(event.target.value);

            const author_element = document.getElementById("authors");
            console.log(author_element);

            insertElementAtIndex(author_element, new_elem, author_element.children.length - 1);
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
            url: "post_traffic.php",
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
                    console.log(data);

                    resolve(data);
                } else {
                    resolve({});
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
    console.log("author children", container.children, index);

    container.insertBefore(await newElement, container.children[index]);
}

function filloutAuthors(list) {
    for (let i = 0; i < list.length; i++) {
        author_names.push(list[i]);
        insertElementAtIndex(document.getElementById("authors"), author_item(list[i]), i);
    }

    $('.author-item').on('mouseenter', function () {
        $(this).find('.author-item-btn').show();
    });

    $('.author-item').on('mouseleave', function () {
        $(this).find('.author-item-btn').hide();
    });

    $('.author-item-btn').hide();

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
}

function buttonEvents() {

    if (!document.getElementById("add-box")) {
        return;
    }
    document.getElementById("add-box").onclick = function () {

        const container = document.getElementById("boxes");

        const content = "Content";

        const box = createArea("div", "editBox-" + (container.children.length), "box", content);
        box.innerHTML = content;

        container.appendChild(box);

        loadPlots();
    };
}

function match_placeholder_image(str) {
    return str.replace("\n", "").match(/\<p\splaceholder\=\"image\"\>includegraphics\{[\w+,\/,\-]+\.(png|jpg|gif)\}\<\/p\>/g);
}

//TODO:   make load on page reload
async function loadImages() {
    const url = url_to_json();

    const boxes = document.getElementById("boxes");

    for (let i = 0; i < boxes.children.length; i++) {

        const word = match_placeholder_image(boxes.children[i].innerHTML);
        if (word) {
            const box_images = boxes.children[i].querySelectorAll("p[placeholder]");
            for (let k = 0; k < word.length; k++) {
                var name = word[k].match(/[\w+,\/,\-]+?\.(png|jpg|gif)/)[0];

                if (box_images[k].getAttribute("placeholder") == "image") {
                    const el = await getLoadedImg(url["id"], name);
                    boxes.children[i].replaceChild(el, box_images[k], "");
                }
            }
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
        console.error(e);
        console.error("This JSON caused this to happen:", data)
    }

    return null;
}

async function loadPlots() {
    // TODO:   plotly json with with breaks gets read but also converted into markdown

    var boxes = document.getElementById("boxes");

    for (let i = 0; i < boxes.children.length; i++) {
        var parent_element = boxes.children[i];
        const data_content = parent_element.getAttribute("data-content");

        // console.log("content", parent_element.innerText);

        const head_data = data_content.match(/(?<=\<p\s)[\s,placeholder\=\"plotly\",(\w+\=\"?\'?\w+\"?\'?)]+(?=\>)/sgm);
        const body = data_content.match(/(?<=<p\s?[\s,(\w+\=\"?\'?\w+\"?\'?)]*>).*?(?=\<\/p\>)/gsm);

        if (head_data) {
            const placeholder_list = parent_element.querySelectorAll('p[placeholder]');

            for (let j = 0; j < placeholder_list.length; j++) {
                if (placeholder_list[j].getAttribute("placeholder") == "plotly") {

                    var content = {};
                    if (placeholder_list[j].hasAttribute("chart") && placeholder_list[j].getAttribute("chart") !== "" && ["scatter", "line", "bar", "pie"].includes(placeholder_list[j].getAttribute("chart"))) {
                        chart_path = "./plotly/" + placeholder_list[j].getAttribute("chart") + ".json";

                        // console.log("type", placeholder_list[j].getAttribute("chart"));
                        // console.log("body", body[j]);

                        var template_content = simple_plot(placeholder_list[j].getAttribute("chart"), body[j]);

                        content = template_content;
                        // placeholder_list[j].innerHTML = JSON.stringify(template_content);
                        // placeholder_list[j].setAttribute("data-content", JSON.stringify(template_content));

                        console.log("templete", template_content);
                        // console.log("p-element", placeholder_list[j].innerHTML);

                    } else {

                        if (placeholder_list[j].hasAttribute("chart") && !["scatter", "line", "bar", "pie"].includes(placeholder_list[j].getAttribute("chart"))) {
                            console.error("Unknown Chart Type");
                        } else {

                            if (body[j] != "") {
                                // console.log(body[j]);

                                content = json_parse(repairJson(body[j]));
                            } else {
                                content = {};
                            }
                        }
                    }

                    if (content && Object.keys(content).length != 0) {
                        placeholder_list[j].id = "plotly-" + i + "-" + j;
                        Plotly.newPlot(placeholder_list[j], content["data"], content["layout"], content["config"]);
                        placeholder_list[j].innerHTML = placeholder_list[j].innerHTML.replace(/\{[\{,\},\[,\],\,\:,\",\',\-,\s,\w+]*\}/gm, "");
                    }
                }
            }
        }
    }
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

                    console.warn("Unsupported chart type: [" + type + "]");
                }

                list.push(point_group);
            }
            template_content["data"] = list;
        } else {
            console.warn("Unsupported input", content);
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

document.addEventListener("dragstart", async function (event) {

    console.log("drawstart event", event.target);

    if (document.getElementById("add_author").contains(event.target)) {
        event.target.style.border = "dashed";
        event.target.style.borderColor = "#83d252";
        event.target.style.border.width = "thin";

        dragend_item = event.target;
    }
    is_draging = true;

    await save_content();
});

document.addEventListener("dragover", function (event) {
    event.preventDefault();
});

// document.addEventListener("dragend", function (event) {
//     console.log("C: ", event.target);

// });

document.addEventListener("drop", async function (event) {
    event.preventDefault();

    if (document.getElementById("add_author").contains(event.target)) {
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

    await save_content();
});

document.addEventListener("mouseover", function (event) {
    // if (is_draging) {
    //     console.log(is_draging);
    //     console.log(event.target);
    // }
});
