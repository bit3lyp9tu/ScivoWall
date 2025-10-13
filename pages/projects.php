<!DOCTYPE html>
<html lang='en'>
<?php

    $site_script="projects.js";
    include(__DIR__ . "/../src/php/header.php");

    // include_once(__DIR__ . "/" . "index.php");
    // include_once(__DIR__ . "/../src/php/account_management.php");
    // include_once(__DIR__ . "/../src/php/poster_edit.php");
?>
<body class="bgimg">
    <div id="logo_headline">
        <img class="nomargin" id='scadslogo' src="img/scadslogo.png" draggable="false"/>
        <div class="header-video" style="float: right;"></div>
    </div>

    <div class="menu">
        <div id="create-project">
            <input type="text" id="project-name">
            <button onclick="createProject()" >Create New Project</button>
        </div>
        <div id="filter" class="filter-container"></div>
    </div>

    <div class="account-menu">
        <div class="account-menu-item">
            <div>
                <input type="button" id="logout" value="Logout">
            </div>
            <div class="icon-btn">
                <a href="/documentation.php">
                    <img src="img/icons/instruction_manual.svg" alt="">
                </a>
            </div>
        </div>
    </div>

    <div class="tables">
        <div>
            <div>
                <h2>Posters</h2>
                <div id="table-container" class="table"></div>
            </div>
            <div>
                <h2>Authors</h2>
                <div id="author-list" class="table"></div>
            </div>
            <div>
                <h2>Images</h2>
                <div id="image-list" class="table"></div>
            </div>
        </div>
    </div>
</body>
</html>
