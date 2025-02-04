<?php
    include_once("queries.php");
    include_once("install.php");
    include_once("account_management.php");

    function getData() {
        $data = isset($_GET['id']) ? $_GET['id'] : '';

        return $data;
    }

    function validation($poster_id) {
        //validate user
        $user_id = getterQuery(
            "SELECT user_id
            FROM poster
            WHERE poster.poster_id=?",
            ["user_id"],
            "s", $poster_id
        );

        $res = json_decode($user_id, true)["user_id"][0];

        // if ($user_id != "No results found" && $res != null) {
        //     //validate session
        //     $session_time = getterQuery(
        //         "SELECT expiration_date
        //         FROM session
        //         WHERE session.user_id = ?",
        //         ["expiration_date"],
        //         "s", $res
        //     );
        //     return $session_time;
        // }


        return $user_id;
    }


    function getTitle($poster_id) {
        $title = getterQuery(
            "SELECT title
            FROM poster
            WHERE poster.poster_id=?",
            ["title"],
            "i", $poster_id
        );

        return json_decode($title, true)["title"][0];
    }

    function getAuthors($poster_id) {
        $ids = getterQuery(
            "SELECT author_id
            FROM author_to_poster
            WHERE author_to_poster.poster_id=?",
            ["author_id"],
            "s", $poster_id
        );
        $id_list = json_decode($ids, true)["author_id"];

        $names = getterQuery(
            "SELECT name
            FROM author
            WHERE author.id IN (?)",
            ["name"],
            "s", $id_list
        );
        $result = json_decode($names, true)["name"];
        // if (in_array($user_name, $result)) {
        //     array_unshift($result, $user_name);
        // }
        return $result;
    }

    function getBoxes($poster_id) {
        $content = getterQuery(
            "SELECT content
            FROM box
            WHERE box.poster_id=?",
            ["content"],
            "i", $poster_id
        );

        return json_decode($content, true)["content"];
    }

    function load_content_head($poster_id) {
        $content->title = getTitle($poster_id);
        $content->authors = getAuthors($poster_id);

        return json_encode($content);
    }

    if (isset($_GET['id'])) {
        $poster_id = $_GET['id'];
        $user_id = getValidUserFromSession();

        if ($user_id != null) {

            echo load_content_head($poster_id);
        }else{

            echo "ERROR";
        }
    }
?>
