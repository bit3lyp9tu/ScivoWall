<?php
    include("queries.php");

    // function register($name, $salt, $pepper, $hash) {
    //     // return "Name: " . $name . " Salt: " . $salt . " Pepper: " . $pepper . " Hash: " . $hash;
    //     $sql = "INSERT INTO user (name, pass_sha, salt, pepper) VALUES (?, ?, ?, ?)";
    //     return insertQuery($sql, [$name, $hash, $salt, $pepper]);
    // }

    // echo register('max', '9d27551d0979891b5b46c97be2c5a07d', '841ae4f433bc273d9f2151dd9bbe5da', 'a2d47c981889513c5e2ddbca71f414');
    echo "test";
    echo insertQuery('max', '9d27551d0979891b5b46c97be2c5a07d', '841ae4f433bc273d9f2151dd9bbe5da', 'a2d47c981889513c5e2ddbca71f414');
?>
