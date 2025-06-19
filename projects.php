<!DOCTYPE html>
<html lang='en'>
<?php
    // include_once(__DIR__ . "/" . "index.php");
    include(__DIR__ . "/" . "account_management.php");
    include(__DIR__ . "/" . "header.html");

	// automatisch die projekte laden
?>
<body>

    <div>
        <button id="logout">Logout</button>
    </div>
    <div id="create-project">
        <input type="text" id="project-name">
        <button onclick="createProject()" >Create New Project</button>
    </div>
    <br>
    <div id="filter" class="filter-container"></div>

    <div id="table-container"></div>

    <!-- List Authors the User worked with + add new Authors -->

    <br>

    <div id="author-list"></div>
    <br>

    <div id="image-list"></div>

</body>
</html>
