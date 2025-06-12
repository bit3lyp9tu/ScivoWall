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
        <!-- <div id="wrapper">
            <div id="myCarousel" data-carousel-3d="">
                <img src="./img/scadslogo.png" selected=""/>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
                <iframe src="https://wikiroulette.co/" width="500" height="500"></iframe>
            </div>
        </div> -->

        <input id="import" type="file" accept="image/png, image/jpeg, image/gif, .csv, text/csv, application/json, .json" />

        <div id="body"></div>

        <script>
            document.getElementById('import').addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (!file) return;

                const output = document.getElementById('body');

                if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = `<p placeholder="plotly" chart="points">\n` + e.target.result + `</p>`;

                        console.log(text);
                        output.innerHTML = text;
                    };
                    reader.readAsText(file);
                } else if (file.type.startsWith('image/')) {

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '300px';
                        output.appendChild(img);
                    };
                    reader.readAsDataURL(file);

                } else if (file.type === 'application/json' || file.name.endsWith('.json')) {

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        try {
                            const json = JSON.parse(e.target.result);
                            output.innerHTML = `<p placeholder="plotly">\n` + JSON.stringify(json, null, 2) + `</p>`;
                            console.log(json);

                        } catch (error) {
                            output.innerHTML = "Error parsing JSON: " + error.message;
                        }
                    };
                    reader.readAsText(file);

                } else {
                    output.textContent = 'Unsupported file type.';
                }

                console.log(file.name);
            });
        </script>
    </body>
</html>
