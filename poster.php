<!DOCTYPE html>
<html lang='en'>
<?php
    $site_script="poster.js";

    include(__DIR__ . "/" . "account_management.php");
    include(__DIR__ . "/" . "header.html");

    include(__DIR__ . "/" . "poster_edit.php")
?>
<body>
    <div id="logo_headline">
        <img class="nomargin" id='scadslogo' src="img/scadslogo.png" draggable="false"/>
        <div style="float: right;">
            <!-- <img id='bgpattern' src="bgpattern.jpeg" /> -->
            <?php
                // if(!get_get("disable_video")) {
            ?>
                <!-- <video autoplay="true" loop="true" muted="muted" [muted]="'muted'" id="bgpattern" src="img/scads-graphic_edited.mov" draggable="false"></video> -->
            <?php
                // }
            ?>
        </div>
    </div>

<!--
    <div id="drop-zone" style="width: 300px; height: 300px; border: 2px dashed #ccc; text-align: center; line-height: 300px;">
        Drop your image here
    </div>
    <div id="preview">
        <img id="preview-img" src="" alt="Preview" style="max-width: 100%; max-height: 100%; display: none;">
    </div>-->
    <!-- <div id="img"></div> -->

    <!-- <p id="tester" style="width:600px;height:250px;"></p> -->

    <button id="img-load">Load Image</button>

    <div id="titles">
        <div>
            <div id="title">Title</div>   <!-- change ids -->
            <br>
            <!-- <h2 id="authors">Heading 2</h2> -->
            <div id="typeahead-container" style="display: flex;">
                <!-- <div style="display: flex;">
                    <input type="search" id="typeahead" autocomplete="on" placeholder="...">
                    <button id="remove-element">X</button>
                </div> -->
                <input type="text" id="typeahead" values=""></input>
            </div>
        </div>
        <div id="edit-options">
            <!-- <a href="login.php">Login</a> -->
            <!-- <button id="add-box">Add Box</button> -->
            <!-- <button id="save-content">Save</button> -->
            <!-- <select name="" id="view-mode"> -->
                <!-- <option value="2">---/option> -->
            <!-- </select> -->
        </div>
    </div>

    <div class="container" id="boxes"></div>
</body>
<footer>
    <!-- <div id="resize_me_according_to_bottom_table"></div> -->
    <div class="grid-container">
        <div class="large-div">
            <img src="img/qrcode.png" class="large_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/tudlogo.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/leipzig.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/CBGlogo.jpg" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/leibnitz.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/helmholtz.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/hzdr.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/infai.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/maxplanck2.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/fraunhofer1.jpg" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/fraunhofer2.jpg" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/dlr.png" class="small_logo" alt="logo" draggable="false">
        </div>
        <div class="small-div">
            <img src="img/maxplanck3.jpeg" class="small_logo" alt="logo" draggable="false">
        </div>

        <div class="bottom-div">
            <?php
                // if(!get_get("disable_video")) {
            ?>
                <video id="bottom_video" autoplay="true" loop="true" muted="muted" [muted]="'muted'" class="bottompattern" src="img/footer.mp4" draggable="false"></video>
            <?php
                // }
            ?>
        </div>
    </div>
</footer>
</html>
