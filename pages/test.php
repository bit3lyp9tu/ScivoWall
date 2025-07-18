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

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
        <!-- <link rel="stylesheet" href="/resources/demos/style.css"> -->
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

        <script>
            $( function() {
                var availableTags = [
                    "A", "AA", "AB", "ABB"
                ];
                $( "#testest" ).autocomplete({
                    source: [
                        "A", "AA", "AB", "ABB"
                    ]
                });
            } );
        </script>

    </head>

    <body>
        <div class="ui-widget">
            <!-- <label for="tags">Tags: </label> -->
            <input id="testest" type="text">
        </div>
    </body>
</html>
