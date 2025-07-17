<?php
    include_once(__DIR__ . "/post_function.php");

    if(isset($_POST['action'])) {
        if ($_POST['action'] == 'test-request') {
            echo test();
        }
    }
?>
