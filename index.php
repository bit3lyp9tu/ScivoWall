<!DOCTYPE html>
<html lang='en'>
<?php
	//TODO:   box height changes between selected and unselected state
	//TODO:   if cursor releases click outside of box, deleted text will return
	//TODO:   storing project - might need a version id in json
	//TODO:   img bgpattern has strange border
	//TODO:   footer too big
	//TODO:   after page load latex/md view does not activate
	//TODO:   reorganizing html format
	//TODO:   footer animation sometimes slightly misplaced (not to right border; moves out of window)
	//TODO:   if img pasted in box, only visible if box selected
	//                        - not visible in latex/md view mode except if other box with image is selected
	//TODO:   scads-graphic-edited.mov does not have a clean transition after replay
	//         TODO:   video sequence .mov does not play
	//TODO:   error in address-link is constantly produced
	//TODO:   remove content redundancy in .box elements

    // TODO:   add login/register button

    $site_script="index.js";

    include(__DIR__ . "/" . "account_management.php");
    include(__DIR__ . "/" . "header.html");

    include(__DIR__ . "/" . "poster_edit.php");

    # TODO: Admin User/PW erstellen wenn nicht existiert (checkUserTable)
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
        <div>
            <input type="button" onclick="location.href='./login.php';" value="Login" />
            <input type="button" onclick="location.href='./register.php';" value="Register" />
        </div>
    </div>

    <div id="posters" class="poster-slide">
        <!-- <iframe
            src="poster.php?mode=public"
            name="targetframe"
            allowTransparency="true"
            scrolling="yes"

            width=300
            height=600
        ></iframe> -->
    </div>


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
