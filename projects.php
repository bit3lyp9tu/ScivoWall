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
    <div>
        <form action="" id="load-form">
            <h1>My Projects</h1>
            <!-- <p>From User: </p>
            <input type="text" id="name" class="form-control" placeholder="Enter your Username..."> -->
            <button type="submit">Load my Projects</button>
        </form>
    </div>
    <div id="create-project">
        <input type="text" id="project-name">
        <button onclick="createProject()" >Create New Project</button>
    </div>
    <br>
    <div id="table-container"></div>

    <!-- List Authors the User worked with + add new Authors -->

    <br>

    <button id="fetch-authors">Fetch Authors</button>
    <div id="author-list"></div>
    <br>

    <button id="fetch-images">Fetch Images</button>
    <div id="image-list"></div>

</body>
</html>
