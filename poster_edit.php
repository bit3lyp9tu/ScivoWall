<?php
    include_once("queries.php");
    include_once("install.php");
    include_once("account_management.php");

    function getData() {
        $data = isset($_GET['id']) ? $_GET['id'] : '';

        return $data;
    }

    function getTitle($poster_id) {
        //TODO: add test if id doesnt exists

        $title = getterQuery(
            "SELECT title
            FROM poster
            WHERE poster.poster_id=?",
            ["title"],
            "i", $poster_id
        );

        return json_decode($title, true)["title"][0];
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
        $author_names = getterQuery(
            "SELECT name
            FROM
                author, (
                    SELECT author_id
                    FROM author_to_poster
                    WHERE author_to_poster.poster_id=?
                ) AS sub
            WHERE sub.author_id=author.id",
            ["name"],
            "i", $poster_id
        );

        return json_decode($author_names, true)["name"];
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

    function addBox($poster_id, $content="") {

        $result = insertQuery(
            "INSERT INTO box (poster_id, content) VALUE (?, ?)",
            "is", $poster_id, $content
        );
        return $result;
    }
    function editBox($index, $poster_id, $content="") {

        $result = editQuery(
            "UPDATE box SET box.content=?
            WHERE box.box_id=(
                SELECT box_id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY box_id) AS row_nr, box_id
                    FROM box
                    WHERE box.poster_id=?
                ) AS ranked_boxes
                WHERE row_nr=?
            )",
            "sii", $content, $poster_id, $index
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

                for ($i = $new_size; $i < $old_size; $i++) {
                    deleteBox($i+1, $poster_id);
                }
            }
        }else{
            for ($i=$old_size; $i > 1; $i--) {
                // print_r($i . "\n");
                deleteBox($i, $poster_id);
            }
            // deleteBox(1, $poster_id);
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
    function removeAuthor($index, $poster_id) {

        $result = deleteQuery(
            "DELETE FROM author_to_poster
            WHERE author_to_poster.id=(
                SELECT id
                FROM (
                    SELECT ROW_NUMBER() OVER(ORDER BY id) AS row_nr, id
                    FROM author_to_poster
                    WHERE author_to_poster.poster_id=?
                ) AS ranked_authors
                WHERE row_nr=?
            )",
            "ii", $poster_id, $index
        );
        return $result;
    }

    function getLastInsertID() {
        return json_decode(getterQuery(
            "SELECT LAST_INSERT_ID()",
            ["LAST_INSERT_ID()"], "", null
        ), true)["LAST_INSERT_ID()"][0];
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
        $result = getterQuery(
            "SELECT name FROM view_modes", ["name"], "", null
        );
        return json_decode($result, true)["name"];
    }
    function getVisibility($poster_id) {

        $result = getterQuery(
            "SELECT fk_view_mode
            FROM poster
            WHERE poster_id=?", ["fk_view_mode"], "i", $poster_id
        );
        return json_decode($result, true)["fk_view_mode"][0];
    }
    function setVisibility($poster_id, $value) {

        $result = editQuery(
            "UPDATE poster SET poster.fk_view_mode = ?
            WHERE poster.poster_id = ?", "ii", $value+1, $poster_id
        );
        return $result;
    }

    //TODO: function to change last_edit_date
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

    function load_content($poster_id) {
        $content = new stdClass();

        $content->title = getTitle($poster_id);
        $content->authors = getAuthors($poster_id);
        $content->boxes = getBoxes($poster_id);
        $content->visibility = getVisibility($poster_id);
        $content->vis_options = getVisibilityOptions();

        return json_encode($content);
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
            $data = json_decode((isset($_POST['data']) ? $_POST['data'] : ''), true);

            $poster_id = isset($_POST['id']) ? $_POST['id'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {

                $title = $data["title"];
                $authors = $data["authors"];
                $content = $data["content"];
                $visibility = $data["visibility"];

                setTitle($poster_id, $title);
                updateEditDate("poster", $poster_id);
                //TODO: setAuthors()
                overwriteBoxes($poster_id, $content);
                setVisibility($poster_id, $visibility);

                echo "success?" . implode($authors);

            }else{
                echo "ERROR";
            }
        }
    }

?>
