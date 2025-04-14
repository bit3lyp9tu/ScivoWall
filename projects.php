<!DOCTYPE html>
<html lang='en'>
<?php
    // include_once("index.php");
    include("account_management.php");
    include("header.html");

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
    <div id="table-container"></div>

    <!-- List Authors the User worked with + add new Authors -->

    <br>

    <div id="author-list"></div>
    <br>

    <div id="image-list"></div>

</body>
</html>
