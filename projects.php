<?php
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
                    var data = JSON.parse(response);

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

                    const numRows = data[headers[0]].length;

                    for (let i = 0; i < numRows; i++) {
                            const row = document.createElement("tr");
                            headers.forEach(header => {
                                const td = document.createElement("td");
                                td.innerText = data[header][i];
                                row.appendChild(td);
                        });
                        table.appendChild(row);
                    }
                    $('#table-container').empty();
                    document.getElementById("table-container").appendChild(table);
                },
                error: function() {
                    alert("An error occurred");
                }
            });
        }
    });
</script>
