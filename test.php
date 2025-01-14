<?php
    include("queries.php");



    // error_reporting(E_ALL);
    // set_error_handler(
    //     function ($severity, $message, $file, $line) {
    //         throw new \ErrorException($message, $severity, $severity, $file, $line);
    //     }
    // );

    // ini_set('display_errors', 1);

    // $array = [1, 2, 3];
    // echo $array[10];

    // echo "done";

    $result = getterQuery(
        "SELECT poster_id
        FROM (
            SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
            FROM poster_generator.poster
            WHERE poster.user_id = 2
        ) AS ranked_posters
        WHERE local_id = ?",
        ["poster_id"],
        "i", 17
    );

    // $res = deleteQuery(
    //     "DELETE FROM poster_generator.poster WHERE poster.poster_id = ?",
    //     "i", json_decode($result, true)[0]
    // );

    print_r(json_decode($result, true)["poster_id"][0]);

?>
