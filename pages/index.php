<!DOCTYPE html>
<html lang='en'>
<?php
	//TODO:   box height changes between selected and unselected state
	//TODO:   if cursor releases click outside of box, deleted text will return
	//TODO:   storing project - might need a version id in json
	//TODO:   img bgpattern has strange border
	//TODO:   if img pasted in box, only visible if box selected
	//                        - not visible in latex/md view mode except if other box with image is selected
	//TODO:   error in address-link is constantly produced
	//TODO:   remove content redundancy in .box elements

    $site_script="index.js";
    include(__DIR__ . "/../src/php/header.php");

    // include_once(__DIR__ . "/../src/php/account_management.php");
    // include_once(__DIR__ . "/../src/php/poster_edit.php");

?>
<body>
    <div id="logo_headline">
        <img class="nomargin" id='scadslogo' src="img/scadslogo.png" draggable="false"/>
        <div class="header-video" style="float: right;"></div>

        <div class="account-box-moving">

            <div class="account-box-item">
                <div>
                    <input type="text" id="name" class="stateA" placeholder="Enter your Username...">
                    <input type="password" id="pw" class="stateA" placeholder="Enter your Password...">
                    <input id="login-btn" type="button" class="stateA" value="Login"/>

                    <input id="login-btn" class="vertical-marker" type="button" value="Login"/>

                    <!-- <p id="login-response"></p> -->
                </div>
            </div>
            <div class="account-box-item">
                <div>
                    <input type="text" id="username"  class="stateA" placeholder="Enter your Username...">
                    <input type="password" id="password"  class="stateA" placeholder="Enter your Password...">
                    <input type="password" id="password2"  class="stateA" placeholder="Repeat your Password...">

                    <input id="register-btn" type="button" class="stateA" value="Register"/>

                    <input id="register-btn" class="vertical-marker" type="button" value="Register"/>

                    <!-- <p id="register-response"></p> -->
                </div>
            </div>
        </div>
    </div>

    <div id="posters" class="poster-slide"></div>

    <span id="spinner" class="loader"></span>

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
