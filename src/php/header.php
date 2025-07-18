<?php
    $root_path = "/scientific_poster_generator";

    include_once(__DIR__ . "/../header.html");

    include_once(__DIR__ . "/../src/php/account_management.php");
    include_once(__DIR__ . "/../src/php/poster_edit.php");

    // include_once(__DIR__ . "/../css/style_test.css");
    // print '<script src="' . $root_path . '/src/js/page_test.js"></script>';
    // print '<link rel="stylesheet" type="text/css" href="' . $root_path . '/src/css/style_test.css">';

    print '<link rel="stylesheet" type="text/css" href="' . $root_path . '/src/css/style.css">';

    print '<script src="' . $root_path . '/src/js//marked.min.js"></script>';

    print '<script src="' . $root_path . '/src/js/' . $site_script . '"></script>';

?>
