<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang='en'>
    <head>
        <link rel="stylesheet" href="./bower_components/carousel-3d/dist/styles/jquery.carousel-3d.default.css"></link>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

        <!-- <link rel="stylesheet" href="./dist/styles/jquery.carousel-3d.default.css" /> -->

        <!-- <script src="./bower_components/jquery/jquery.js"></script>
        <script src="./bower_components/javascript-detect-element-resize/jquery.resize.js"></script>
        <script src="./bower_components/waitForImages/dist/jquery.waitforimages.js"></script>-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.waitforimages/2.4.0/jquery.waitforimages.js"></script>
        <script src="./bower_components/modernizr/modernizr.js"></script>
        <script src="./bower_components/carousel-3d/dist/jquery.carousel-3d.js" ></script>

    </head>
    <body>
        <div id="wrapper">
            <div id="myCarousel" data-carousel-3d="">
                <img src="./img/scadslogo.png" selected=""/>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
            </div>
        </div>
    </body>
</html>
