<?php
    include_once(__DIR__ . "/" . "queries.php");
    include_once(__DIR__ . "/" . "install.php");
    include_once(__DIR__ . "/" . "functions.php");
    include_once(__DIR__ . "/" . "account_management.php");


    // TODO:   check for all functions using poster_id if id exists
    // TODO:   check for all functions using user_id if id exists

    function getTitle($poster_id) {
        //TODO:   add test if id doesnt exists

        $title = getterQuery2(
            "SELECT title
            FROM poster
            WHERE poster.poster_id=?",
            $poster_id
        );

        return $title["title"][0];
    }
    function setTitle($poster_id, $title) {
        $result = editQuery(
            "UPDATE poster SET poster.title=?
            WHERE poster.poster_id=?",
            "si", $title, $poster_id
        );
        return $result;
    }

    function getAuthors($poster_id) {
        $author_names = getterQuery2(
            "SELECT id, name
            FROM
                author, (
                    SELECT author_id
                    FROM author_to_poster
                    WHERE author_to_poster.poster_id=?
                ) AS sub
            WHERE sub.author_id=author.id",
            $poster_id
        );
        return $author_names;
    }

    function getBoxes($poster_id) {
        $content = getterQuery2(
            "SELECT content
            FROM box
            WHERE box.poster_id=?",
            $poster_id
        )["content"];
        // if ($content != "No results found") {

        //     return json_decode($content, true)["content"];
        // }else{
        //     return [];
        // }
        return $content;
    }

    function addBox($poster_id, $content="") {

        $result = insertQuery(
            "INSERT INTO box (poster_id, content) VALUE (?, ?)",
            "is", $poster_id, $content
        );
        return $result;
    }
    function editBox($local_id, $poster_id, $content="") {

        $result = editQuery(
            "UPDATE box SET box.content=?
            WHERE box.box_id=(
                SELECT box_id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY box_id) AS local_id, box_id
                    FROM box
                    WHERE box.poster_id=?
                ) AS ranked_boxes
                WHERE local_id=?
            )",
            "sii", $content, $poster_id, $local_id
        );
        return $result;
    }
    function overwriteBoxes($poster_id, $new_content) {

        $old_size = sizeof(getBoxes($poster_id));
        $new_size = sizeof($new_content);

        if ($new_size != 0) {

            for ($i=0; $i < $new_size; $i++) {
                editBox($i+1, $poster_id, $new_content[$i]);
            }

            if($old_size < $new_size) {

                for ($i = $old_size; $i < $new_size; $i++) {
                    addBox($poster_id, $new_content[$i]);
                }
            }else if ($old_size > $new_size) {

                for ($i = $old_size - 1; $i > $new_size - 1; $i--) {
                    deleteBox($i+1, $poster_id);
                }
            }
        }else{
            for ($i=$old_size; $i > 0; $i--) {
                // print_r($i . "\n");
                deleteBox($i, $poster_id);
            }
            // deleteBox(1, $poster_id);
        }
    }
    function getImage($image_id) {
        return json_encode(getterQuery2(
            "SELECT data FROM image WHERE image_id=?",
            $image_id
        ), true)["data"][0];
    }
    function getFullImage($name, $poster_id) {
        return json_encode(getterQuery2(
            "SELECT file_name, type, size, last_modified, data FROM image WHERE fk_poster=? AND file_name=? LIMIT 1",
            $poster_id, $name
        ), true);
    }

    // function str2bin($str) {
    //     $binary = '';
    //     for ($i = 0; $i < strlen($str); $i++) {
    //         $binary .= sprintf("%08b", ord($str[$i]));  // Converts each character to 8-bit binary
    //     }
    //     return  b'' . $binary;
    // }

    function addImage($json_data, $poster_id) {

        $count = getterQuery2("SELECT COUNT(image_id) as cnt_image_id FROM image WHERE file_name=? AND fk_poster=?", $json_data["name"], $poster_id);
        #dier($count["cnt_image_id"][0]);

        #error_log(print_r(array($count), true));

        if($count["cnt_image_id"][0] > 0) {
            $result = editQuery(
                "UPDATE image
                SET file_name = ?, type = ?, size = ?, last_modified = ?, webkit_relative_path = ?, data = ?, fk_poster = ?
                WHERE file_name = ? AND fk_poster = ?",
                "ssiissisi", $json_data["name"], $json_data["type"], $json_data["size"], $json_data["last_modified"], $json_data["webkit_relative_path"], $json_data["data"], $poster_id, $json_data["name"], $poster_id
            );
            return $result;
        }else{
            $result = insertQuery(
                "INSERT INTO image (file_name, type, size, last_modified, webkit_relative_path, data, fk_poster)
                VALUE (?, ?, ?, ?, ?, ?, ?)", "ssiissi",
                $json_data["name"], $json_data["type"], $json_data["size"],
                $json_data["last_modified"], $json_data["webkit_relative_path"], $json_data["data"], $poster_id
            );
            return $result;
        }
    }

    function deleteBox($local_id, $poster_id) {
        $result = deleteQuery(
            "DELETE FROM box
            WHERE box.box_id=(
                SELECT box_id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY box_id) AS row_nr, box_id
                    FROM box
                    WHERE box.poster_id=?
                ) AS ranked_boxes
                WHERE row_nr=?
            )",
            "ii", $poster_id, $local_id
        );
        return $result;
    }

    function addAuthor($name) {
        $result = insertQuery(
            "INSERT INTO author (name) VALUE (?)",
            "s", $name
        );
        return $result;
    }
    function connectAuthorToPoster($author_id, $poster_id) {
        $result = insertQuery(
            "INSERT INTO author_to_poster (author_id, poster_id) VALUE (?, ?)",
            "ii", $author_id, $poster_id
        );
        return $result;
    }
    function removeAuthor($local_id, $poster_id) {
        $result = deleteQuery(
            "DELETE FROM author_to_poster
            WHERE author_to_poster.id=(
                SELECT id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY id) AS local_id, id
                    FROM author_to_poster
                    WHERE author_to_poster.poster_id=?
                ) AS ranked_authors
                WHERE local_id=?
            )",
            "ii", $poster_id, $local_id
        );
        return $result;
    }
    function addAuthors($poster_id, $authors) {
        $results = "";

        for ($i=0; $i < sizeof($authors); $i++) {
            $id = 0;

            $res = getterQuery2(
                "SELECT id FROM author WHERE name=?", $authors[$i]
            )["id"];

            if (sizeof($res) == 0) {
                $results .= "[" . addAuthor($authors[$i]);
                $id = getLastInsertID();
            }else{

                $id = $res[0];
            }

            $results .= "|" . connectAuthorToPoster($id, $poster_id) . "],";
        }
        return $results;
    }
    // function searchAuthor($name, $poster_id) {
    //     $result = getterQuery2(
    //         "SELECT a.id AS author_id, a.name AS name, b.id AS id, b.poster_id AS poster_id
    //         FROM author AS a, author_to_poster AS b
    //         WHERE a.id=b.author_id AND a.name=? AND b.poster_id=?",
    //         $name, $poster_id
    //     );
    //     return $result;
    // }
    //  changes only author_to_poster, but not author
    function overwriteAuthors($poster_id, $authors) {
        $results = "";

        $results .= deleteQuery(
            "DELETE FROM author_to_poster
            WHERE poster_id=?", "i", $poster_id
        );

        $results .= addAuthors($poster_id, $authors);

        // $existing_authors = getAuthors($poster_id);
        // for ($i=0; $i < sizeof($authors); $i++) {
        //     // check if $authors[$i] already exits
        //     //      $existing_authors = searchAuthor($authors[$i], $poster_id);
        //     // $res = array_search($authors[$i], $existing_authors["name"]);
        //     if (in_array($authors[$i], $existing_authors["name"])) {
        //         //  -true:     check if local poster_id is equal to $poster_id
        //         //          -true:      none
        //         //          -false:     add new author connection($id, $poster_id)
        //         // $results .= "[-|" . connectAuthorToPoster($existing_authors["id"][$res], $poster_id) . "],";
        //     }else{
        //         //  -false:    add $authors[$i]; add author connection($id, $poster_id)
        //         $results .= "[" . addAuthor($authors[$i]);
        //         $id = getLastInsertID();
        //         $results .= "|" . connectAuthorToPoster($id, $poster_id) . "],";
        //     }
        // }
        return $results;
    }

    function addProject($user_id, $title) {
        //new poster
        $resultA = insertQuery(
            "INSERT INTO poster (title, user_id) VALUE (?, ?)",
            "si", $title, $user_id
        );
        $poster_id = getLastInsertID();

        //new author (user)
        $resultB = insertQuery(
            "INSERT INTO author (name)
            SELECT (
                SELECT name FROM user WHERE user.user_id=?
            )
            WHERE NOT EXISTS (
                SELECT name
                FROM author
                WHERE author.name=(
                    SELECT name FROM user WHERE user.user_id=?
                )
            )",
            "ii", $user_id, $user_id
        );
        $author_id = getLastInsertID();

        //new author_to_poster
        $resultC = insertQuery(
            "INSERT INTO author_to_poster (author_id, poster_id) VALUE (?, ?)",
            "ii", $author_id, $poster_id
        );

        //new box
        // addBox($poster_id, "$$ Content $$");

        return $resultA . " " . $resultB . " " . $resultC;
    }

    function getVisibilityOptions() {
        $result = getterQuery2(
            "SELECT name FROM view_modes"
        );
        return $result["name"];
    }
    function getVisibility($poster_id) {

        $result = getterQuery2(
            "SELECT fk_view_mode
            FROM poster
            WHERE poster_id=?", $poster_id
        );
        return $result["fk_view_mode"][0];
    }
    function setVisibility($poster_id, $value) {

        $result = editQuery(
            "UPDATE poster SET poster.fk_view_mode = ?
            WHERE poster.poster_id = ?", "ii", $value+1, $poster_id
        );
        return $result;
    }

    function setViewMode($user_id, $local_id, $view_id) {
        $result = editQuery(
            "UPDATE poster SET fk_view_mode=?
            WHERE poster_id=(
                SELECT poster_id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY poster_id) AS local_id, poster_id
                    FROM poster
                    WHERE user_id=?
                ) AS ranked_posters
                WHERE local_id=?
            )",
            "iii", $view_id, $user_id, $local_id
        );
        return $result;
    }
    // TODO: needs testing
    function setViewMode2($poster_id, $view_id) {
        $result = editQuery(
            "UPDATE poster SET fk_view_mode = ?
            WHERE poster_id = ?",
            "ii", $view_id, $poster_id
        );
        return $result;
    }

    //TODO:   function to change last_edit_date
    function updateEditDate($table, $id) {
        $attribute = array(
            "poster" => "last_edit_date",
            "user" => "last_login_date",
            "image" => "upload_date"
        );
        $query = "UPDATE " . $table . " SET " . $table . "." . $attribute[$table] . "=UNIX_TIMESTAMP() WHERE " . $table . "." . $table . "_id=?";

        if ($table="user" || $table="poster" || $table="image") {

            $result = editQuery(
                $query,
                "i", $id
            );
            return $result;
        }else{
            return null;
        }
    }

    function fetchPublicPosters() {
        $result = getterQuery2(
            "SELECT poster_id, title
            FROM poster
            WHERE fk_view_mode=? AND visible=?", 1, 1
        );
        return json_encode($result, true);
    }

    function isPublic($poster_id) {
        $result = getterQuery2(
            "SELECT 1 AS is_public FROM poster WHERE poster_id=? AND fk_view_mode=? AND visible=?",
            $poster_id, 1, 1
        );
        return sizeof($result["is_public"]) != 0 ? 1 : 0;
    }

    function load_content($poster_id) {
        $content = new stdClass();

        $content->title = null;
        $content->authors = array();
        $content->boxes = array();
        $content->visibility = null;
        $content->vis_options = getVisibilityOptions();

        $count = getterQuery2("SELECT COUNT(poster_id) as cnt_poster_id FROM poster WHERE poster_id = ?", $poster_id);
        if($count["cnt_poster_id"][0] > 0) {
            $content->title = getTitle($poster_id);
            $content->authors = getAuthors($poster_id)["name"]; //direct from project
            // $content->all_authors = ...      //other authors the user once wrote in a project// TODO:   request list of all authors the user has a connection with
            $content->boxes = getBoxes($poster_id);
            $content->visibility = getVisibility($poster_id);
            $content->vis_options = getVisibilityOptions();
        }

        return json_encode($content);
    }

    // TODO: use for every poster change event
    // TODO: needs testing
    function hasPermissionToChange($user_id, $poster_id) {
        if (isAdmin($user_id) === true) {
            return true;
        }
        if (getterQuery2("SELECT user_id FROM poster WHERE poster_id = ?", $poster_id)["user_id"][0] == $user_id) {
            return true;
        }

        return false;
    }

    if(isset($_POST['action'])) {
        if ($_POST['action'] == 'get-content') {

            $poster_id = (isset($_POST['key']) && $_POST['key']=='id' && isset($_POST['value'])) ? $_POST['value'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {

                echo load_content($poster_id);
            }else{

                echo json_encode(array('status' => 'error', 'message' => 'Invalid user'));
            }
        }
        if ($_POST['action'] == 'content-upload') {
            //TODO:   check if mode=private + session-id correct

            $data = json_decode((isset($_POST['data']) ? $_POST['data'] : ''), true);

            $poster_id = isset($_POST['id']) ? $_POST['id'] : '';
            $user_id = getValidUserFromSession();

            $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

            if ($user_id != null/* && $mode == 'edit'*/) {

                if (hasPermissionToChange($user_id, $poster_id) === true) {

                    $title = $data["title"];
                    $authors = $data["authors"];
                    $content = $data["content"];
                    $visibility = $data["visibility"];

                    setTitle($poster_id, $title);
                    updateEditDate("poster", $poster_id);
                    // addAuthors($poster_id, $authors);
                    overwriteAuthors($poster_id, $authors);
                    overwriteBoxes($poster_id, $content);
                    setVisibility($poster_id, $visibility);

                    echo "success?";
                } else {
                    echo "Insufficient permission";
                }

            }else{
                echo "ERROR";
            }
        }
        if($_POST['action'] == 'fetch-available-posters') {

            echo fetchPublicPosters();
        }
        if ($_POST['action'] == 'image-upload') {
            $data = isset($_POST['data']) ? $_POST['data'] : '';

            //TODO:   check if user has edit permissions for poster
            $poster_id = isset($_POST['id']) ? $_POST['id'] : '';

            echo addImage($data, $poster_id);
        }
        if($_POST['action'] == 'get-image') {
            // $image_id = isset($_POST['id']) ? $_POST['id'] : '';
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $poster_id = isset($_POST['poster_id']) ? $_POST['poster_id'] : '';

            // echo getImage(169);
            echo getFullImage($name, $poster_id);
        }

        if($_POST['action'] == 'list-view-options') {

            $user_id = getValidUserFromSession();
            if ($user_id != null) {
                echo json_encode(getVisibilityOptions());
            }else{
                echo "No or invalid session";
            }
        }

        if($_POST['action'] == 'set-view-option') {

            $poster_id = isset($_POST['poster_id']) ? $_POST['poster_id'] : '';
            $view_option = isset($_POST['view_id']) ? $_POST['view_id'] : '';
            $is_global = isset($_POST['is_global']) ? $_POST['is_global'] : '';

            $user_id = getValidUserFromSession();
            if ($user_id != null) {
                // if (hasPermissionToChange($user_id, $poster_id) === true) {

                if ($is_global && isAdmin($user_id)) {
                    updateEditDate("poster", $poster_id);
                    echo setViewMode2($poster_id, $view_option);
                } else {
                    // updateEditDate("poster", $poster_id); -> needs local_id
                    echo setViewMode($user_id, $poster_id, $view_option);
                }
                // }else{
                //     echo "Insufficient permission";
                // }

            }else{
                echo "No or invalid session";
            }
        }
    }

?>
