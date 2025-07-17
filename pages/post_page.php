<!DOCTYPE html>
<html lang='en'>
<?php
    $root_path = "/scientific_poster_generator";

    print '<script src="' . $root_path . '/src/js/page_test.js"></script>';
    print '<link rel="stylesheet" type="text/css" href="' . $root_path . '/src/css/style_test.css">';
?>
<body>
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

    <!-- <script src="/scientific_poster_generator/src/js/page_test.js"></script>
    <link rel='stylesheet' type='text/css' href="/scientific_poster_generator/src/css/style_test.css"> -->

    <div>
        <p>TEST</p>
        <input type="button" id="test-btn" value="Test" onclick="test_request()">
    </div>
</body>
