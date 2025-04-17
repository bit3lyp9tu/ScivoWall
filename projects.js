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
                loadTable(response);
                toastr["success"]("New Project created");
            }
        },
        error: function () {
            toastr["error"]("An error occurred");
        }
    });
}

function error_if_not_number(name, data) {
    if (typeof (data) != "number") {
        console.error(`deleteRow: ${name} is not a number: ${data}`);
    }
}

//TODO: reloaded data after delete should include last edit time
function deleteRow(local_id) {
    error_if_not_number("local_id", local_id)

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'delete_project',
            local_id: Number(local_id)
        },
        success: function (response) {
            loadTable(response);

            if (response != "No results found") {
                toastr["success"]("No Projects available");
            } else {
                toastr["success"]("Project deleted");
            }
        },
        error: function () {
            toastr["error"]("An error occurred");
        }
    });
}

function delete_author(local_id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "delete-author",
            id: Number(local_id)
        },
        success: function (response) {
            console.log(response);
            fetch_authors_list();
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function delete_image(local_id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "delete-image",
            id: Number(local_id)
        },
        success: function (response) {
            console.log(response);
            fetch_images();
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
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function rename_author(_this, param) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "rename-author",
            name: _this.value,
            id: Number(param[1])
        },
        success: function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function rename_poster(_this, param) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "rename_poster",
            name: _this.value,
            id: Number(param[1])
        },
        success: function (response) {
            console.log(response);
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function insert_visibility_column(_this, data, td, header, i) {
    const elem = document.createElement("INPUT");
    elem.setAttribute("type", "checkbox");
    elem.checked = data[header][i];
    elem.onclick = function () {
        let found_id = get_found_id(this);
        if (found_id !== null) {
            updateVisibility(found_id, this.checked ? 1 : 0);
        }
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
    var param = parse_id_name(this);
    console.log(this.value, ...param);

    if (param[0] == 'author-list') {
        rename_author(this, param);
    }
    if (param[0] == 'table-container') {
        console.log("poster rename");

        rename_poster(this, param);
    }
    load_project_page_data();
}

function make_column_editable(data, header, i, td) {
    const elem = document.createElement("INPUT");
    elem.setAttribute("type", "text");
    elem.value = data[header][i];

    elem.onchange = change_action;

    td.appendChild(elem);
}

function make_headers_editable(editable_columns, headers, data, i, row) {
    headers.forEach(header => {
        const td = document.createElement("td");

        if (header == "visible") {
            insert_visibility_column(this, data, td, header, i);
        } else if (header == "image_data") {
            create_and_append_image_container(td);
        } else {
            if (editable_columns.includes(headers.indexOf(header))) {
                make_column_editable(data, header, i, td);
            } else {
                td.innerText = data[header][i];
            }
        }
        row.appendChild(td);
    });
}

function append_additional_columns(additional_columns, i, row) {
    additional_columns.forEach(column => {
        const td = document.createElement("td");
        td.appendChild(column(i));
        row.appendChild(td);
    });
}

// TODO: may need an overwork
function createTableFromJSON(id, data, editable_columns, ...additional_columns) {
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

    for (let i = 0; i < data[headers[0]].length; i++) {
        const row = document.createElement("tr");
        row.id = id + "--nr-" + (i + 1);

        make_headers_editable(editable_columns, headers, data, i, row);

        append_additional_columns(additional_columns, i, row);

        table.appendChild(row);
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
    const result = await $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "is-admin"
        },
        success: function (response) {
            return !!response;
        },
        error: function (err) {
            err(`Error while checking isAdmin: ${err}`);
            return false;
        }
    });
    return result;
}

function loadTable(response) {
    $('#table-container').empty();

    if (response == "No results found") {
        // $('#table-container').html(response);
        toastr["warning"]("No results found");  //TODO: bug? execute msg twice?

    } else {
        var data = isJSON(response) ? JSON.parse(response) : response;

        const editColumn = (index) => {
            const td = document.createElement("td");
            const link = document.createElement("a");

            var linkText = document.createTextNode("Edit");
            link.appendChild(linkText);
            // link.title = "Edit";

            link.onclick = async function () {
                var local_id = get_found_id(this);
                const poster_id = await edit_translation(local_id);
                window.location.href = "poster.php?id=" + poster_id + "&mode=private";
            }

            td.appendChild(link);
            return td;
        };
        const deleteColumn = (index) => {
            const td = document.createElement("td");
            const btn = document.createElement('input');
            btn.type = "button";
            btn.className = "btn";
            btn.value = "Delete";
            btn.onclick = function () {
                let found_id = get_found_id(this);
                if (found_id !== null) {
                    deleteRow(found_id);
                }
            }
            td.appendChild(btn);
            return td;
        };

        createTableFromJSON("table-container", data, [0], editColumn, deleteColumn);
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

async function fetch_all_projects() {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'fetch_all_projects'
        },
        success: function (response) {
            if (response != "No or invalid session") {
                if (response != "No results found") {
                    loadTable(response);
                    toastr["success"]("Loading Projects");
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

async function fetch_authors_list() {
    document.getElementById("author-list").replaceChildren();

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "fetch_authors",
        },
        success: function (response) {
            // console.log(JSON.parse(response));

            if (response != "No or invalid session") {
                if (response != "No results found") {

                    function deleteColumn(index) {
                        const td = document.createElement("td");
                        const btn = document.createElement('input');
                        btn.type = "button";
                        btn.className = "btn";
                        btn.value = "Delete";
                        btn.onclick = function () {
                            var param = parse_id_name(this);

                            console.log(param[0], param[1], this.value);

                            deleteItem(...param);
                        }
                        td.appendChild(btn);
                        return td;
                    };

                    createTableFromJSON("author-list", JSON.parse(response), [0], deleteColumn);
                }
            }
        },
        error: function (err) {
            console.error(err);
        }
    });
}

async function fetch_images() {
    document.getElementById("image-list").replaceChildren();

    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: "fetch_images",
        },
        success: function (response) {

            if (response != "No or invalid session") {
                // console.log(JSON.parse(response));

                if (response != "No results found") {

                    const deleteColumn = (index) => {
                        const td = document.createElement("td");
                        const btn = document.createElement('input');
                        btn.type = "button";
                        btn.className = "btn";
                        btn.value = "Delete";
                        btn.onclick = function () {
                            // TODO: error if poster was preiously deleted
                            // deleteRow(this.closest('tr').id.split("--nr-")[1]);
                            var parsed_id_name = parse_id_name(this);

                            console.log(parsed_id_name);

                            deleteItem(parsed_id_name);
                        }
                        td.appendChild(btn);
                        return td;
                    };
                    createTableFromJSON("image-list", JSON.parse(response), [1], deleteColumn);

                    loadImgsInTable("image-list");
                }
            }
        },
        error: function (err) {
            console.error(err);
        }
    });
}

function load_project_page_data() {
    fetch_all_projects();
    fetch_authors_list();
    fetch_images();
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
