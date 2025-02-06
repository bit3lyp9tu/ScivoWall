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
        return $resultA . " " . $resultB . " " . $resultC;
    }

    function load_content($poster_id) {
        $content = new stdClass();

        $content->title = getTitle($poster_id);
        $content->authors = getAuthors($poster_id);
        $content->boxes = getBoxes($poster_id);

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
    }

?>
