<!DOCTYPE html>
<html lang='en'>
<?php
    // include_once(__DIR__ . "/" . "index.php");
    include(__DIR__ . "/" . "account_management.php");
    include(__DIR__ . "/" . "header.html");
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
        <br>
        <div id="filter" class="filter-container"></div>
    </div>

    <div class="account-menu">
        <div class="account-menu-item">
            <div>
                <input type="button" id="logout" value="Logout">
            </div>
        </div>
    </div>

    <div class="tables">
        <div>
            <div>
                <div id="table-container" class="table"></div>
            </div>
            <div>
                <div id="author-list" class="table"></div>
            </div>
            <div>
                <div id="image-list" class="table"></div>
            </div>
        </div>
    </div>
</body>

<footer>
    <div class="grid-container">
        <div class="large-div">
            <img src="img/qrcode.png" class="large_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/tudlogo.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/leipzig.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/CBGlogo.jpg" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/leibnitz.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/helmholtz.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/hzdr.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/infai.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/maxplanck2.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/fraunhofer1.jpg" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/fraunhofer2.jpg" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/dlr.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="item">
            <img src="img/maxplanck3.jpeg" class="small_logo" alt="logo" draggable="false">
        </div>
    </div>

    <div class="bottom-div">
        <video id="bottom_video" autoplay="true" loop="true" muted="muted" [muted]="'muted'" class="bottompattern" src="img/footer.mp4" draggable="false" disablePictureInPicture></video>
    </div>
</footer>
</html>
