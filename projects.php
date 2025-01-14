<?php
    // include_once("index.php");
    include("account_management.php");
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <title>Login</title>
    <meta charset='utf-8'>
    <link rel='stylesheet' type='text/css' href=style.css>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div>
        <form action="" id="form">
            <h1>My Projects</h1>
            <p>From User: </p>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username...">
            <button type="submit">Submit</button>
        </form>
    </div>
    <div>
        <input type="text" id="project-name">
        <button onclick="createProject()" >Create New Project</button>
    </div>
    <br>
    <div id="table-container"></div>
</body>
</html>

<script>
    let registerForm = document.getElementById("form");

    registerForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const username = document.getElementById("name");

        if (username.value != "") {
            $.ajax({
                type: "POST",
                url: "account_management.php",
                data: {
                    action: 'fetch_all_projects',
                    name: username.value
                },
                success: function(response) {
                    loadTable(response);
                },
                error: function() {
                    alert("An error occurred");
                }
            });
        }
    });

    function deleteRow(local_id, session_id) {
        $.ajax({
            type: "POST",
            url: "account_management.php",
            data: {
                action: 'delete_project',
                local_id: local_id,
                session_id: session_id
            },
            success: function (response) {
                console.log((response));
                // loadTable();
            },
            error: function() {
                alert("An error occurred");
            }
        });
    }

    function createProject() {
        const project_name = document.getElementById("project-name");

        console.log(project_name.value);

        if (project_name.value != "") {
            $.ajax({
                type: "POST",
                url: "account_management.php",
                data: {
                    action: 'create_project',
                    name: project_name.value,
                    user_id: 19
                },
                success: function(response) {
                    console.log(response);
                },
                error: function() {
                    alert("An error occurred");
                }
            });
        }
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
            row.id = "nr-" + (i+1);

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
                td.innerText = "Edit";
                return td;
            };
            const deleteColumn = (index) => {
                const td = document.createElement("td");
                const btn = document.createElement('input');
                btn.type = "button";
                btn.className = "btn";
                btn.value = "Delete";
                btn.onclick = function() {
                    deleteRow(this.closest('tr').id.split("-")[1], "-");
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

</script>
