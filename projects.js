function createProject() {
    const project_name = document.getElementById("project-name");

    // console.log(project_name.value);

    if (project_name.value != "") {
        $.ajax({
            type: "POST",
            url: "account_management.php",
            data: {
                action: 'create_project',
                name: project_name.value
            },
            success: function (response) {
                // console.log(response);

                if (response == "ERROR") {
                    $('#table-container').html("User does not exist");
                } else {
                    loadTable(response);
                }
            },
            error: function () {
                alert("An error occurred");
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
            // console.log((response));
            loadTable(response);
        },
        error: function () {
            alert("An error occurred");
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
            td.innerText = data[header][i];
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

function loadTable(response) {
    $('#table-container').empty();

    if (response == "No results found") {
        $('#table-container').html(response);

    } else {
        var data = isJSON(response) ? JSON.parse(response) : response;

        const editColumn = (index) => {
            const td = document.createElement("td");
            td.innerText = "Edit"; // link zum editierbaren poster
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
                loadTable(response);
            },
            error: function () {
                alert("An error occurred");
            }
        });
    });
});
