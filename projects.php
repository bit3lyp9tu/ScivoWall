<?php
    // include_once("index.php");
    include("account_management.php");
    include("header.html");

	// automatisch die projekte laden
?>
<!DOCTYPE html>
<html lang='en'>
<body>
    <div>
        <form action="" id="load-form">
            <h1>My Projects</h1>
            <!-- <p>From User: </p>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username..."> -->
            <button type="submit">Load my Projects</button>
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
