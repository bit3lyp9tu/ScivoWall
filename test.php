<?php
    //include("queries.php");

?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <title>Poster Generator</title>
    <meta charset='utf-8'>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

    <!-- <script src="https://code.jquery.com/jquery.min.js"></script> -->
    <!-- <script src="https://code.jquery.com/typeahead.bundle.min.js"></script> -->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"> -->
    <!-- <link rel='stylesheet' type='text/css' href=style.css> -->
    <!-- <script src="poster.js"></script> -->

    <script src="https://cdn.plot.ly/plotly-3.0.0.min.js" charset="utf-8"></script>

</head>

<body>
    <div id="container"></div>

    <div id="tester" style="width:600px;height:250px;"></div>
</body>

<script>

    window.onload = function () {


        const json = `{
  "data": [
    {
        "z": [
        [
            0,
            1,
            2,
            3,
            4,
            5,
            6
            ],
        [
            1,
            9,
            4,
            7,
            5,
            2,
            4
            ],
            [
          2,
          4,
          2,
          1,
          6,
          9,
          3
        ]
      ],
      "type": "heatmap"
    }
  ],
  "layout": {
    "title": {
        "text": "Customize The Edit Chart Link Text"
    }
    },
    "config": {
        "showLink": true,
    "plotlyServerURL": "https://chart-studio.plotly.com",
    "linkText": "This text is custom!"
    }
}`;
console.log(json);
console.log(JSON.parse(json));
};

</script>

<style>


</style>

</html>
