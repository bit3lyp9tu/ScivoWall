<!DOCTYPE html>
<html lang='en'>
<?php
	//TODO:   box height changes between selected and unselected state
	//TODO:   if cursor releases click outside of box, deleted text will return
	//TODO:   storing project - might need a version id in json
	//TODO:   img bgpattern has strange border
	//TODO:   reorganizing html format
	//TODO:   if img pasted in box, only visible if box selected
	//                        - not visible in latex/md view mode except if other box with image is selected
	//TODO:   error in address-link is constantly produced
	//TODO:   remove content redundancy in .box elements

    $site_script="index.js";

    include(__DIR__ . "/" . "account_management.php");
    include(__DIR__ . "/" . "header.html");

    include(__DIR__ . "/" . "poster_edit.php");

?>
<body>
    <div id="logo_headline">
        <img class="nomargin" id='scadslogo' src="img/scadslogo.png" draggable="false"/>
        <div class="header-video" style="float: right;"></div>
        <div>
            <input id="toggle_login_box" class="toggle-btn" type="button" value="Login" />
            <div id="login-box" class="account-box" style="display: none;">
                Login
                <input type="text" id="name" class="form-control" placeholder="Enter your Username...">
                <input type="password" id="pw" class="form-control" placeholder="Enter your Password...">

                <input id="login-btn" type="button" value="Submit"/>

                <p id="login-response"></p>
            </div>

            <input id="toggle_register_box" class="toggle-btn" type="button" value="Register" />
            <div id="register-box" class="account-box" style="display: none;">
                Register
                <input type="text" id="username" class="form-control" placeholder="Enter your Username...">
                <input type="password" id="password" class="form-control" placeholder="Enter your Password...">
                <input type="password" id="password2" class="form-control" placeholder="Repeat your Password...">

                <input id="register-btn" type="button" value="Submit"/>

                <p id="register-response"></p>
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
