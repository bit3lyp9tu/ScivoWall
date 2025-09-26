<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang='en'>
    <head>
        <script>
            window.addEventListener("load", function () {
                let startX = 0;
                let endX = 0;

                function handleGesture() {
                    if (endX < startX - 50) {
                        console.log("swipeleft");
                    }
                    if (endX > startX + 50) {
                        console.log("swiperight");
                    }
                }

                document.addEventListener("pointerdown", function (e) {
                    startX = e.clientX;
                }, false);

                document.addEventListener("pointerup", function (e) {
                    endX = e.clientX;
                    handleGesture();
                }, false);
            });
        </script>

    </head>

    <body>
        <div class="ui-widget">
            <!-- <label for="tags">Tags: </label> -->
            <input id="testest" type="text">
        </div>
    </body>
</html>
