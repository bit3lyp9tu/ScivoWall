
function createProject() {
    const project_name = document.getElementById("project-name");

    if (project_name.value != "") {
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
}

function deleteRow(local_id) {
    $.ajax({
        type: "POST",
        url: "account_management.php",
        data: {
            action: 'delete_project',
            local_id: local_id
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

function createTableFromJSON(id, data, ...additional_columns) {
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
        row.id = "nr-" + (i + 1);

        headers.forEach(header => {
            const td = document.createElement("td");

            if (header == "visible") {
                const elem = document.createElement("INPUT");
                elem.setAttribute("type", "checkbox");
                elem.checked = data[header][i];
                elem.onclick = function () {
                    updateVisibility(this.closest('tr').id.split("-")[1], this.checked ? 1 : 0);
                }
                td.appendChild(elem);
            } else {
                td.innerText = data[header][i];
            }
            row.appendChild(td);
        });

        additional_columns.forEach(column => {
            const td = document.createElement("td");
            td.appendChild(column(i));
            row.appendChild(td);
        });

        table.appendChild(row);
    }
    document.getElementById(id).appendChild(table);
}

async function edit_translation(local_id) {
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
        error: function () {
            return "[ERROR]";
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
            return response;
        },
        error: function () {
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
                var local_id = this.closest('tr').id.split("-")[1];
                const poster_id = await edit_translation(local_id);
                window.location.href = "poster.php?id=" + poster_id;
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
                deleteRow(this.closest('tr').id.split("-")[1]);
            }
            td.appendChild(btn);
            return td;
        };

        createTableFromJSON("table-container", data, editColumn, deleteColumn);
    }
}

function isJSON(data) {
    try {
        JSON.parse(data);
    } catch (e) {
        return false;
    }
    return true;
}

$(document).ready(function () {
    let registerForm = document.getElementById("load-form");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

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
    });
});

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
