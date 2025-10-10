<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang='en'>
    <script src="https://cdn.plot.ly/plotly-3.1.1.min.js" charset="utf-8"></script>

    <div id="chart-wrapper" style="
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100vh;    /* or any preferred height */
    overflow: hidden; /* prevents scrolling */
"><div id="myChart" style="width: 100%; max-width: 800px; height: 100%;"></div></div>

    <script>
        var trace1 = {
            x:['2020-10-04', '2021-11-04', '2023-12-04'],
            y: [90, 40, 60],
            type: 'scatter'
        };
        var data = [trace1];

        var layout = {
            autosize: true,
            margin: { l: 40, r: 40, t: 40, b: 40 }
        };

        Plotly.newPlot('myChart', data, layout, {responsive: true});
    </script>

</html>
