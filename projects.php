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
    <div id="table-container">

    </div>
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
                    $('#table-container').html(response);

                    var data = [
                        ['Alice', 30, 'New York'],
                        ['Bob', 25, 'Los Angeles'],
                        ['Charlie', 35, 'Chicago']
                    ];

                    const lines = response.split("<br>");

                    for (let i = 0; i < lines.length; i++) {
                        const cells = lines.split(" ");

                        for (let j = 0; j < cells.length; j++) {
                            const element = cells[j];
                            dat[i][j] = cells[j];
                        }
                    }

                    var table = document.createElement('table');

                    var headerRow = table.insertRow();

                    var headers = ['Name', 'Age', 'City'];
                    for (var i = 0; i < headers.length; i++) {
                        var cell = headerRow.insertCell();
                        cell.textContent = headers[i];
                    }

                    for (var i = 0; i < data.length; i++) {
                        var row = table.insertRow();

                        for (var j = 0; j < data[i].length; j++) {
                            var cell = row.insertCell();
                            cell.textContent = data[i][j];
                        }
                    }
                    document.getElementById('table-container').appendChild(table);
                },
                error: function() {
                    alert("An error occurred");
                }
            });
        }
    });
</script>
