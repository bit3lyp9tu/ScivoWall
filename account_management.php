<?php
    include_once("queries.php");

    if(isset($_POST['action'])) {
        if ($_POST['action'] == 'register') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $salt = isset($_POST['salt']) ? $_POST['salt'] : '';
            $pepper = isset($_POST['pepper']) ? $_POST['pepper'] : '';

            $hash = isset($_POST['hash']) ? $_POST['hash'] : '';

            echo insertQuery("INSERT INTO poster_generator.user (`name`, `pass_sha`, `salt`, `pepper`)
                VALUES (?, ?, ?, ?)",
                "ssss", $name, $hash, $salt, $pepper);
        }
    }
?>
