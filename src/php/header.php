<?php

    $path_prefix = "";
    //$path_prefix = "/../src/php";
    $root_path = "";//"/ScivoWall";

    // print "inDocker: " . getenv("inDocker");

    include_once(__DIR__ . "/../header.php");

    include_once(__DIR__ . $path_prefix . "/account_management.php");
    include_once(__DIR__ . $path_prefix . "/poster_edit.php");

    // include_once(__DIR__ . "/../css/style_test.css");
    // print '<script src="' . $root_path . '/src/js/page_test.js"></script>';
    // print '<link rel="stylesheet" type="text/css" href="' . $root_path . '/src/css/style_test.css">';
?>
