const log = console.log;
const err = console.error;
const warn = console.warn;

function createProject() {
    const project_name = document.getElementById("project-name");

    if (project_name.value == "") {
        return;
    }

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'create_project',
            name: project_name.value
        },
        success: function (response) {
            if (response == "ERROR" || response == "No or invalid session") {
                toastr["warning"]("Not logged in");
            } else {
                load_project_page_data();
                toastr["success"]("New Project created");
            }
        },
        error: function () {
            toastr["error"]("An error occurred");
        }
    });
}

//TODO:   reloaded data after delete should include last edit time
function delete_project(id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'delete_project',
            id: Number(id),
        },
        success: function (response) {
            console.log(response);
        },
        error: function () {
            toastr["error"]("An error occurred");
        }
    });
}

function delete_author(id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "delete-author",
            id: Number(id)
        },
        success: async function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function delete_image(id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "delete-image",
            id: Number(id)
        },
        success: function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function deleteItem(parent_id, local_id) {
    error_if_not_number("local_id", local_id);

    if (parent_id == 'author-list') {
        delete_author(local_id);
    } else if (parent_id == 'image-list') {
        delete_image(local_id);
    } else {
        console.error(`deleteItem: unknown parent_id ${parent_id}`)
    }
}

function updateVisibility(id, value) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "update-visibility",
            id: id,
            value: value
        },
        success: function (response) {
            // TODO: returns ERROR msgs if non-admin tries to toggle
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function rename_author(_this, id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "rename-author",
            name: _this.value,
            id: id
        },
        success: function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function rename_image(_this, id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "rename-image",
            name: _this.value,
            id: id
        },
        success: function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function rename_poster(_this, id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "rename_poster",
            name: _this.value,
            id: id
        },
        success: function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

async function insert_visibility_column(_this, data, td, header, i) {
    const elem = document.createElement("INPUT");
    elem.setAttribute("type", "checkbox");
    elem.checked = data[header][i];
    elem.onclick = function () {
        var id = $(this).closest("tr")[0].getAttribute("pk_id");
        if (id) {
            updateVisibility(id, this.checked ? 1 : 0);
        } else {
            console.error("pk id not found");
        }
    }
    // TODO: needs testing
    if (!await isAdmin()) {
        elem.toggleAttribute("disabled");
    }
    td.appendChild(elem);
}

function create_and_append_image_container(td) {
    const container = document.createElement("DIV");
    const img = document.createElement("IMG");
    img.src = '';

    container.appendChild(img);
    td.appendChild(container);
}

async function change_action() {
    const is_global = $(this).closest("tr")[0].hasAttribute("pk_id");
    var param = parse_id_name(this);
    var id = Number(param[1]);


    if (is_global) {
        id = $(this).closest("tr")[0].getAttribute("pk_id");
        if (await isAdmin()) {
            var filter_elements = getFilterElements();
        }
    }
    if (param[0] == 'author-list') {
        rename_author(this, id);
        if (is_global && await isAdmin()) {
            await fetch_authors_filtered(
                filter_to_json(
                    filter_elements["user"],
                    filter_elements["poster"],
                    filter_elements["view_mode"],
                    filter_elements["last_edit"],
                    filter_elements["visibility"]
                )
            );
        }
    }
    if (param[0] == 'table-container') {
        rename_poster(this, id);
    }
    if (param[0] == 'image-list') {
        rename_image(this, id);
        if (is_global && await isAdmin()) {
            await fetch_images_filtered(
                filter_to_json(
                    filter_elements["user"],
                    filter_elements["poster"],
                    filter_elements["view_mode"],
                    filter_elements["last_edit"],
                    filter_elements["visibility"]
                )
            );
        }
    }
    load_project_page_data();
}

function make_column_editable(data, header, i, td) {
    const elem = document.createElement("INPUT");
    elem.setAttribute("type", "text");
    elem.value = data[header][i];
    elem.setAttribute("value", data[header][i]);

    elem.onchange = change_action;

    td.appendChild(elem);
}

async function getViewOptions() {
    try {
        const response = await $.ajax({
            type: "POST",
            url: "poster_edit.php",
            data: {
                action: "list-view-options"
            }
        });
        return response;
    } catch (err) {
        console.error(`view list: ${err}`);
        return `[ERROR] ${err}`;
    }
}

async function setViewOption(poster_id, view_id, is_global) {
    try {
        const response = await $.ajax({
            type: "POST",
            url: "poster_edit.php",
            data: {
                action: "set-view-option",
                poster_id: poster_id,
                view_id: (view_id + 1)
            }
        });
        return response;
    } catch (err) {
        console.error(`set view mode: ${err}`);
        return `[ERROR] ${err}`;
    }
}

async function make_headers_editable(editable_columns, headers, data, i, row) {
    for (const header of headers) {
        const td = document.createElement("td");

        if (header == "visible") {
            insert_visibility_column(this, data, td, header, i);
        } else if (header == "image_data") {
            create_and_append_image_container(td);
        } else if (header == "view_mode") {
            const selection = document.createElement("select");
            const children = JSON.parse(await getViewOptions());

            for (let j = 0; j < children.length; j++) {
                const option = document.createElement("option");
                option.value = j;
                option.text = children[j];
                selection.appendChild(option);
            }

            selection.value = children.indexOf(data[header][i]);

            selection.onchange = async function () {
                var id = null;
                var is_global = $(this).closest("tr")[0].hasAttribute("pk_id");
                if (is_global) {
                    id = $(this).closest("tr")[0].getAttribute("pk_id");
                } else {
                    id = this.closest('tr').id.split("--nr-")[1];
                }
                // console.log("change", this.value, this.closest('tr').id.split("--nr-")[1]);
                await setViewOption(id, Number(this.value), is_global);
            };

            td.appendChild(selection);

        } else {
            if (editable_columns.includes(headers.indexOf(header))) {
                make_column_editable(data, header, i, td);
            } else {
                td.innerText = data[header][i];
            }
        }
        row.appendChild(td);
    }
}

function append_additional_columns(additional_columns, i, row) {
    additional_columns.forEach(column => {
        const td = document.createElement("td");
        td.appendChild(column(i));
        row.appendChild(td);
    });
}

// TODO:   may need an overwork
async function createTableFromJSON(id, pk_ids, data, editable_columns, ...additional_columns) {
    const table = document.createElement("table");
    table.setAttribute("border", "1");

    const headerRow = document.createElement("tr");
    const headers = Object.keys(data);

    headers.forEach(header => {
        const th = document.createElement("th");
        th.innerText = header;
        headerRow.appendChild(th);
    });

    table.appendChild(headerRow);

    if (data !== "") {
        for (let i = 0; i < data[headers[0]].length; i++) {
            const row = document.createElement("tr");
            row.id = id + "--nr-" + (i + 1);

            if (pk_ids && pk_ids.length == data[headers[0]].length) {
                row.setAttribute("pk_id", pk_ids[i]);
            } else {
                console.error("no pk_id", pk_ids, data);
            }

            await make_headers_editable(editable_columns, headers, data, i, row);

            append_additional_columns(additional_columns, i, row);

            table.appendChild(row);
        }
    }

    document.getElementById(id).appendChild(table);
}

async function edit_translation(local_id) {
    error_if_not_number("local_id", local_id);

    const result = await $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "edit-translation",
            local_id: local_id
        },
        success: function (response) {
            // console.log(response);
            return response;
        },
        error: function (err) {
            return `[ERROR] ${err}`;
        }
    });
    return result;
}

async function isAdmin() {
    try {
        const response = await $.ajax({
            type: "POST",
            url: "account_management.php",
            data: {
                action: "is-admin"
            }
        });
        return !!response;
    } catch (err) {
        console.error(`Error while checking isAdmin: ${err}`);
        return false;
    }
}

function parse_id_name(_this) {
    var elem = null;
    if (!_this) {
        console.error("_this is not defined");
        return;
    }

    elem = _this;

    if (!$(elem).closest("tr")) {
        console.error('_this.closest("tr") could not be found');
        return;
    }

    elem = $(elem).closest("tr");

    if (!$(elem).attr("id")) {
        console.error("elem.closest('tr') does not have an ID!", elem);
        return;
    }

    elem = $(elem).attr("id");

    var splitted = elem.split("--nr-");

    return splitted;
}

function get_found_id(_this) {
    var parsed_id_name = parse_id_name(_this);
    var found_id = parsed_id_name[1];
    if (!found_id.match(/^\d+$/)) {
        console.error(`Found-ID is not a number: ${found_id}`);
        return null;
    }

    found_id = Number(found_id);

    return found_id;
}

function get_found_element_name(_this) {
    var parsed_id_name = parse_id_name(_this);

    if (parsed_id_name.length < 2) {
        err(`Error in get_found_element: element had no 2 splitted elements`, _this)
        return null;
    }

    return parsed_id_name[1];
}

function isJSON(data) {
    try {
        JSON.parse(data);
    } catch (e) {
        return false;
    }
    return true;
}

async function get_selectable_filters() {
    try {
        const response = await $.ajax({
            type: "POST",
            url: "account_management.php",
            data: {
                action: 'selectable_filters'
            }
        });
        return response;
    } catch (err) {
        console.error(`filter selectables: ${err}`);
        return `[ERROR] ${err}`;
    }
}

function process_data(response) {
    var data = isJSON(response) ? JSON.parse(response) : response;

    if (data === "") {
        console.error("no data received, not logged in?");
    }

    return data;
}

function getIndex(element) {
    const row = element.closest("tr");
    const table = row.closest("table");
    const index = Array.from(table.rows).indexOf(row);

    return index - 1;
}

async function fetch_projects_filtered(filter) {
    document.getElementById("table-container").replaceChildren();

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'fetch_filtered_projects',
            filter: filter
        },
        success: function (response) {
            if (response != "No or invalid session") {
                if (response != "No results found") {
                    // loadTable(response);
                    // toastr["success"]("Loading Projects");

                    var data = process_data(response);
                    var pk_ids = null;

                    if (Object.keys(data).includes("id")) {
                        pk_ids = data["id"];
                        delete data["id"];
                    }

                    function deleteColumn(index) {
                        const td = document.createElement("td");
                        const btn = document.createElement('input');
                        btn.type = "button";
                        btn.className = "btn";
                        btn.value = "Delete";
                        btn.onclick = async function () {

                            if (pk_ids) {
                                var id = $(this).closest("tr")[0].getAttribute("pk_id");

                                var filter = "";

                                if (await isAdmin()) {
                                    var filter_elements = getFilterElements();

                                    filter = (filter_to_json(
                                        filter_elements["user"],
                                        filter_elements["poster"],
                                        filter_elements["view_mode"],
                                        filter_elements["last_edit"],
                                        filter_elements["visibility"]
                                    ));
                                }
                                console.log("poster_id", id);
                                delete_project(id);
                                $("#table-container").empty();

                                const local_id = getIndex(this);
                                remove_local_data(local_id, data);
                                pk_ids.splice(local_id, 1);

                                createTableFromJSON("table-container", pk_ids, data, [1], deleteColumn);
                            } else {
                                console.error("no pk_ids");
                            }
                        }
                        td.appendChild(btn);
                        return td;
                    };

                    createTableFromJSON("table-container", pk_ids, data, [1], deleteColumn);

                } else {
                    toastr["warning"]("No results found");
                }
            } else {
                toastr["warning"]("Not logged in");
            }
        },
        error: function () {
            toastr["error"]("An error occurred");
        }
    });
}

function remove_local_data(local_id, data) {
    Object.keys(data).forEach(key => {
        data[key].splice(local_id, 1);
    });
}

async function fetch_authors_filtered(filter) {
    document.getElementById("author-list").replaceChildren();

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'fetch_filtered_authors',
            filter: filter
        },
        success: function (response) {
            // console.log(JSON.parse(response));

            if (response != "No or invalid session") {
                if (response != "No results found") {
                    var data = process_data(response);
                    var pk_ids = null;

                    if (Object.keys(data).includes("id")) {
                        pk_ids = data["id"];
                        delete data["id"];
                    }

                    function deleteColumn(index) {
                        const td = document.createElement("td");
                        const btn = document.createElement('input');
                        btn.type = "button";
                        btn.className = "btn";
                        btn.value = "Delete";
                        btn.onclick = async function () {

                            if (pk_ids) {
                                var id = $(this).closest("tr")[0].getAttribute("pk_id");

                                var filter = "";

                                if (await isAdmin()) {
                                    var filter_elements = getFilterElements();

                                    filter = (filter_to_json(
                                        filter_elements["user"],
                                        filter_elements["poster"],
                                        filter_elements["view_mode"],
                                        filter_elements["last_edit"],
                                        filter_elements["visibility"]
                                    ));
                                }
                                delete_author(id);
                                $("#author-list").empty();

                                const local_id = getIndex(this);
                                remove_local_data(local_id, data);
                                pk_ids.splice(local_id, 1);
                                // console.log(data);

                                createTableFromJSON("author-list", pk_ids, data, [2], deleteColumn);
                            } else {
                                console.error("no pk_ids");
                            }
                        }
                        td.appendChild(btn);
                        return td;
                    };

                    createTableFromJSON("author-list", pk_ids, data, [2], deleteColumn);
                }
            }
        },
        error: function (err) {
            console.error(err);
        }
    });
}

async function fetch_images_filtered(filter) {
    document.getElementById("image-list").replaceChildren();

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'fetch_filtered_images',
            filter: filter
        },
        success: function (response) {
            // console.log(JSON.parse(response));

            if (response != "No or invalid session") {
                if (response != "No results found") {
                    var data = process_data(response);
                    var pk_ids = null;

                    if (Object.keys(data).includes("id")) {
                        pk_ids = data["id"];
                        delete data["id"];
                    }

                    function deleteColumn(index) {
                        const td = document.createElement("td");
                        const btn = document.createElement('input');
                        btn.type = "button";
                        btn.className = "btn";
                        btn.value = "Delete";
                        btn.onclick = async function () {

                            if (pk_ids) {
                                var id = $(this).closest("tr")[0].getAttribute("pk_id");

                                var filter = "";

                                if (await isAdmin()) {
                                    var filter_elements = getFilterElements();

                                    filter = (filter_to_json(
                                        filter_elements["user"],
                                        filter_elements["poster"],
                                        filter_elements["view_mode"],
                                        filter_elements["last_edit"],
                                        filter_elements["visibility"]
                                    ));
                                }
                                delete_image(id);
                                $("#image-list").empty();

                                const local_id = getIndex(this);
                                remove_local_data(local_id, data);
                                pk_ids.splice(local_id, 1);

                                createTableFromJSON("image-list", pk_ids, data, [1], deleteColumn);
                            } else {
                                console.error("no pk_ids");
                            }
                        }
                        td.appendChild(btn);
                        return td;
                    };

                    createTableFromJSON("image-list", pk_ids, data, [1], deleteColumn);
                }
            }
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function unixToDate(number) {
    const date = new Date(number);
    return date.getFullYear() + "-" + date.getMonth() + "-" + date.getDate();
}

function filter_to_json(user, poster, view_mode, last_edit, visiblitly) {
    var json = JSON.parse(`{
		"attributes": {
			"user.name": {
				"min": "",
				"max": "",
				"list": [
				]
			},
			"poster.title": {
				"min": "",
				"max": "",
				"list": [
				]
			},
			"last_edit_date": {
				"min": "",
				"max": "",
				"list": [
				]
			},
			"visible": {
				"min": "",
				"max": "",
				"list": [
				]
			},
			"view_modes.name": {
				"min": "",
				"max": "",
				"list": [
				]
			}
		}
	}`);

    if (user !== "-") {
        json["attributes"]["user.name"]["list"].push(user);
    }
    if (poster !== "-") {
        json["attributes"]["poster.title"]["list"].push(poster);
    }
    // if (last_edit !== "-") {
    //     json["attributes"]["last_edit_date"].push("{}");
    // }
    if (visiblitly !== "-") {
        json["attributes"]["visible"]["list"].push(visiblitly);
    }
    if (view_mode !== "-") {
        json["attributes"]["view_modes.name"]["list"].push(view_mode);
    }

    console.log("request: ", json);

    return JSON.stringify(json);
}

function getFilterElements() {
    const user = document.getElementById("select_user");
    const poster = document.getElementById("select_title");
    const view_mode = document.getElementById("select_view_mode");
    // const last_edit = document.getElementById("last_edit");
    const visibility = document.getElementById("visibility");

    return {
        "user": user.options[user.selectedIndex].text,
        "poster": poster.options[poster.selectedIndex].text,
        "view_mode": view_mode.options[view_mode.selectedIndex].text,
        "last_edit": "",
        "visibility": visibility.options[visibility.selectedIndex].text
    };
}

async function createFilter() {
    const filter = document.getElementById("filter");

    var selectables = JSON.parse(await get_selectable_filters());
    // console.log(selectables);

    const json = ["user", "title", "view_mode"];
    const db = ["name", "title", "name"];

    for (let i = 0; i < json.length; i++) {
        const container = document.createElement("div");
        container.classList.add("filter-select");
        const select = document.createElement("select");
        select.id = "select_" + json[i];

        const option = document.createElement("option");
        option.value = -1;
        option.text = "-";
        select.appendChild(option);

        for (let j = 0; j < selectables[json[i]][db[i]].length; j++) {
            const option = document.createElement("option");
            option.value = j;
            option.text = selectables[json[i]][db[i]][j];
            select.appendChild(option);
        }
        container.appendChild(select);
        filter.appendChild(container);
    }

    const containerB = document.createElement("div");
    containerB.classList.add("filter-select");
    const last_edit = document.createElement("input");
    last_edit.id = "last_edit";
    last_edit.type = "date";
    last_edit.min = unixToDate(selectables["last_edit"]["min"]);
    last_edit.max = unixToDate(selectables["last_edit"]["max"]);
    containerB.appendChild(last_edit);
    filter.appendChild(containerB);

    // const visibility_btn = document.createElement("input");
    // visibility_btn.id = "visibility";
    // visibility_btn.type = "checkbox";
    // visibility_btn.indeterminate = true;
    // filter.appendChild(visibility_btn);

    const containerC = document.createElement("div");
    containerC.classList.add("filter-select");
    const visibility_select = document.createElement("select");
    visibility_select.id = "visibility";
    const mode_optionA = document.createElement("option");
    mode_optionA.value = -1;
    mode_optionA.text = "-";
    visibility_select.appendChild(mode_optionA);
    const mode_optionB = document.createElement("option");
    mode_optionB.value = 0;
    mode_optionB.text = "0";
    visibility_select.appendChild(mode_optionB);
    const mode_optionC = document.createElement("option");
    mode_optionC.value = 1;
    mode_optionC.text = "1";
    visibility_select.appendChild(mode_optionC);
    filter.appendChild(visibility_select);
    containerC.appendChild(visibility_select);
    filter.appendChild(containerC);

    const containerD = document.createElement("div");
    const submit = document.createElement("input");
    submit.type = "button";
    submit.id = "submit-filter";
    submit.value = "Submit";
    submit.onclick = async function () {

        var filter_elements = getFilterElements();

        await fetch_projects_filtered(
            filter_to_json(
                filter_elements["user"],
                filter_elements["poster"],
                filter_elements["view_mode"],
                filter_elements["last_edit"],
                filter_elements["visibility"]
            )
        );
        await fetch_authors_filtered(
            filter_to_json(
                filter_elements["user"],
                filter_elements["poster"],
                filter_elements["view_mode"],
                filter_elements["last_edit"],
                filter_elements["visibility"]
            )
        );
        await fetch_images_filtered(
            filter_to_json(
                filter_elements["user"],
                filter_elements["poster"],
                filter_elements["view_mode"],
                filter_elements["last_edit"],
                filter_elements["visibility"]
            )
        );
    }
    containerD.appendChild(submit);
    filter.appendChild(containerD);
}

async function load_project_page_data() {

    if (await isAdmin()) {
        $("#filter").empty();
        createFilter();

    } else {
        // TODO: [BUG] occasionally projects are loaded in wrong order (wrong title/link)
        fetch_projects_filtered("");
        fetch_authors_filtered("");
        fetch_images_filtered("");
    }
}

async function loadImgsInTable(id) {
    var json_string = await $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'fetch_img_data'
        },
        success: function (response) {
            return response;
        },
        error: function () {
            console.warn("Unable to fetch Image Data");
            return "{}";
        }
    });

    if (!isJSON(json_string)) {
        err(`loadImgsInTable: Is invalid JSON-string: ${json_string}`)
        return null;
    }

    var json_parsed = JSON.parse(json_string);

    if (!Object.keys(json_parsed).includes("image_data")) {
        console.error(`loadImgsInTable: json_parsed does not include image_data`, json_parsed);
        return null;
    }

    const img_data = json_parsed["image_data"];

    var elem = document.getElementById(id);

    if (!elem) {
        console.error(`loadImgsInTable: elem not found by id ${id}`)
        return null;
    }

    var elem_children = elem.children;

    if (elem_children.length == 0) {
        console.error(`loadImgsInTable: elem has no children: `, elem);
        return null;
    }

    var first_child = elem_children[0];

    if (!first_child.children.length) {
        console.error(`loadImgsInTable: first_child has no children`, first_child);
        return null;
    }

    const table = first_child.children;
    if (img_data.length == table.length - 1) {
        for (let i = 1; i < table.length; i++) {
            const img = table[i].querySelector('img');

            img.classList.add("table-img");
            img.src = img_data[i - 1];
            // console.log(i, img_data[i - 1]);

        }
    } else {
        console.warn(`Rows and Images are unequal [${img_data.length}: ${(table.length - 1)}]`);
    }
}

document.getElementById("logout").onclick = function () {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'logout'
        },
        success: function (response) {
            // console.log(response);
            window.location.href = "login.php";
            toastr["success"](response);
        },
        error: function () {
            toastr["error"]("An logout error occurred");
        }
    });
}

$(document).ready(function () {
    load_project_page_data();
});
