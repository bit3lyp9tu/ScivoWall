<?php

    $path_prefix = "";
    //$path_prefix = "/../src/php";
    $root_path = "";//"/scientific_poster_generator";

    // print "inDocker: " . getenv("inDocker");

    include_once(__DIR__ . "/../header.html");

    include_once(__DIR__ . $path_prefix . "/account_management.php");
    include_once(__DIR__ . $path_prefix . "/poster_edit.php");

    // include_once(__DIR__ . "/../css/style_test.css");
    // print '<script src="' . $root_path . '/src/js/page_test.js"></script>';
    // print '<link rel="stylesheet" type="text/css" href="' . $root_path . '/src/css/style_test.css">';

    print '<link rel="stylesheet" type="text/css" href="' . $root_path . '/src/css/style.css">';

    print '<script src="' . $root_path . '/src/js//marked.min.js"></script>';

    print '<script src="' . $root_path . '/src/js/' . $site_script . '"></script>';

    if ($site_script == "index.js") {

        print '<link rel="stylesheet" href="' . $root_path . '/jquery-flipster/jquery.flipster.css">';
        print '<script src="' . $root_path . '/jquery-flipster/jquery.flipster.min.js"></script>';
    }
?>
