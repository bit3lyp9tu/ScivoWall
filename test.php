<?php
    //include("queries.php");

    function foo(...$param) {
        return gettype($param[0]);
    }
    echo foo("abc123") . "<br>";
    echo foo() . "<br>";
    echo foo(1,2,3) . "<br>";
    echo foo(1,2,"ab","c") . "<br>";
    echo foo("TestingTitle2") . "<br>";
    echo gettype("TestingTitle2") . "<br>";
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

    <script src="https://cdn.jsdelivr.net/npm/typeahead-standalone"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/typeahead-standalone/dist/basic.css" />
</head>

<body>

</body>

<script>


</script>

</html>
