<?php
    $site_script="poster.js";

    include("account_management.php");
    include("header.html");

    include("poster_edit.php")
?>
<!DOCTYPE html>
<html lang='en'>
<body>
    <div id="logo_headline">
        <img class="nomargin" id='scadslogo' src="img/scadslogo.png" />
        <div style="float: right;">
            <!-- <img id='bgpattern' src="bgpattern.jpeg" /> -->
            <?php
                // if(!get_get("disable_video")) {
            ?>
                <!-- <video autoplay="true" loop="true" muted="muted" [muted]="'muted'" id="bgpattern" src="img/scads-graphic_edited.mov"></video> -->
            <?php
                // }
            ?>
        </div>
    </div>

    <div id="titles">
        <div id="title">Title</div>   <!-- change ids -->
        <div>
            <h2 id="authors">Heading 2</h2>
        </div>
        <div>
            <a href="login.php">Login</a>
            <button id="add-box">Add Box</button>
            <button id="save-content">Save</button>
        </div>
    </div>

    <div class="container" id="boxes">
        <!-- <div class="box"></div> -->
    </div>
</body>
<footer>
    <!-- <div id="resize_me_according_to_bottom_table"></div> -->
    <div class="grid-container">
        <div class="large-div">
            <img src="img/qrcode.png" class="large_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/tudlogo.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/leipzig.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/cbg.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/leibnitz.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/helmholtz.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/hzdr.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/infai.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/maxplanck2.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/fraunhofer1.jpg" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/fraunhofer2.jpg" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/dlr.png" class="small_logo" alt="logo">
        </div>
        <div class="small-div">
            <img src="img/maxplanck3.jpeg" class="small_logo" alt="logo">
        </div>

        <div class="bottom-div">
            <?php
                // if(!get_get("disable_video")) {
            ?>
                <video id="bottom_video" autoplay="true" loop="true" muted="muted" [muted]="'muted'" class="bottompattern" src="img/footer.mp4"></video>
            <?php
                // }
            ?>
        </div>
    </div>
</footer>
</html>
