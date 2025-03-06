<?php
    include_once("queries.php");
    include_once("install.php");

    $msgs = array(
        "success" => [],
        "warning" => [],
        "error" => []
    );

    function generate_salt($len=32) {
        return substr(bin2hex(random_bytes($len)), 0, $len);
    }

    function getPwComplexityLevel($pw) {
        $complexity_points = 0;

        $numbers = str_split("0123456789");
        $letters = "abcdefghijklmnopqrstuvwxyz";
        $lower_letters = str_split($letters);
        $upper_letters = str_split(strtoupper($letters));
        $special_char = str_split("_=!#$%&()*+-.:/\?@[]*$");

        if (strlen($pw) >= 8) {
            $complexity_points++;
        }
        if (!empty(array_intersect($numbers, str_split($pw)))) {
            $complexity_points++;
        }
        if (!empty(array_intersect($upper_letters, str_split($pw)))) {
            $complexity_points++;
        }
        if (!empty(array_intersect($special_char, str_split($pw)))) {
            $complexity_points++;
        }

        return $complexity_points;
    }

    //TODO: move queries to own functions + tests
    //      make queries more compact
    /*
        *    Validates current session
        *    if session not valid, returns null
        *    if session valid, returns the corresponding user_id
    */
    function getValidUserFromSession() {

        if (!array_key_exists("sessionID", $_COOKIE)) {
            return null;
        }
        $sessionID = $_COOKIE["sessionID"];
        if ($sessionID == null) {
            return null;
        }

        $session = getterQuery(
            "SELECT user_id
            FROM session
            WHERE session.sessionID = ?",
            ["user_id"],
            "s", $sessionID
        );
        return json_decode($session, true)["user_id"][0];
    }

    //TODO: make id not required
    function isAdmin($user_id) {
        // if ($user_id == null) {
        //     $user_id = getValidUserFromSession();
        // }
        $result = getterQuery2(
            "SELECT access_level FROM user WHERE user_id=?", $user_id
        );
        return $result["access_level"][0] >= 2;
    }

    function register($name, $pw) {

        if (getPwComplexityLevel($pw) != 4) {
            return "Password not complex enough";
        }

        $salt = generate_salt();
        $pepper = "a2d47c981889513c5e2ddbca71f414"; //TODO: use pepper dependency
        $hash = sha1($pw . ":" . $salt . ":" . $pepper);

        try {
            return insertQuery("INSERT INTO user (`name`, `pass_sha`, `salt`, `pepper`)
                VALUES (?, ?, ?, ?)",
                "ssss", $name, $hash, $salt, $pepper);

        } catch (mysqli_sql_exception $th) {
            if ($th->getCode() == 1062) {
                return "The user " . $name . " already exists.";
            }else{
                return $th->getMessage();
            }
        }
    }

    function login($name, $pw) {

        # TODO: select user_id from user where username = ? and passwort = sha(?);

        $result = getterQuery2(
            "SELECT user_id, pass_sha, salt, pepper FROM user WHERE user.name=?",
            $name
        );

        if (sizeof($result["user_id"]) == 0 &&
            sizeof($result["pass_sha"]) == 0 &&
            sizeof($result["salt"]) == 0 &&
            sizeof($result["pepper"]) == 0) {

            return "Wrong Username or Password";
        } else {
            $hash = $result["pass_sha"][0];
            $salt = $result["salt"][0];
            $pepper = $result["pepper"][0];

            if (md5($pw . ":" . $salt . ":" . $pepper) == $hash || sha1($pw . ":" . $salt . ":" . $pepper) == $hash) {  //TODO: remove md5 when test-phase finished

                //Create new Session
                $user_id = $result["user_id"][0];
                $sid = session_create_id();

                $insertion = insertQuery(
                        "INSERT INTO session (user_id, sessionID, expiration_date)
                        VALUE (?, ?, UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL 5 MINUTE)))",
                        "is", $user_id, $sid);

                // TODO: is expiration_date for sql and cookie async??? Bug?

                if ($insertion == "success") {
                    setcookie("sessionID", $sid, time() + 5 * 60, "/", "", false, true);   //TODO: placing additional time in variable
                    //TODO: do the cookie settings need a rework? Update to PHP 7.3 and later might be required
                    //PHP 7.3 and later has additional parameter SameSide=Strict and SameSide=Lax

                    return "Correct Password";
                }else {
                    return $insertion;
                }
            }else {
                return "Wrong Username or Password";
            }
        }

    }

    //TODO: editing after logout bug
    function logout($user_id) {

        $sid = getterQuery(
            "SELECT sessionID FROM session WHERE session.user_id=?",
            ["sessionID"],
            "s", $user_id
        );

        //set browser-session to expired
        setcookie("sessionID", $sid, time() - 1, "/", "", false, true);   //TODO: placing additional time in variable

        //set database-session to expired
        //get list of all currently valid sessions (from user)
        //update list and replace expiration_date with an expired one
        $edit = editQuery(
            "UPDATE session
            SET session.expiration_date=UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MINUTE))
            WHERE session.expiration_date>UNIX_TIMESTAMP() AND session.user_id=?",
            "i", $user_id
        );

        //return if logout successfully
        return $edit;
    }

    function fetchAsAdmin($user_id) {

    }

    function fetch_projects($user_id, $priv_acc=true) {

        if ($user_id != null) {

            if ($user_id == "No results found") {
                return "No results found";
            }else{
                if (!$priv_acc && isAdmin($user_id)) {
                    $result = getterQuery2(
                        "SELECT title, from_unixtime(last_edit_date) AS 'last edit', visible FROM poster WHERE fk_view_mode=?",
                        1
                    );
                    return $result;
                }else{
                    $result = getterQuery2(
                        "SELECT title, from_unixtime(last_edit_date) AS 'last edit' FROM poster WHERE poster.user_id=?",
                        $user_id
                    );
                    return $result;
                }
            }

        }else{
            return "No or invalid session";
        }
    }

    function create_project($name, $user_id) {

        if ($user_id != null) {

            if ($user_id != "") {
                $res = insertQuery(
                    "INSERT into poster (title, user_id) VALUE (?, ?)",
                    "si", $name, $user_id
                );

                //refresh list
                $data = getterQuery2(
                    "SELECT title FROM poster WHERE poster.user_id=?",
                    $user_id
                );
                return json_encode($data, true);
            }else {
                return "ERRORhvjjghgc";
            }

        }else{
            return "No or invalid session";
            #$msgs["errors"][] = "No or invalid session";
        }
    }

    function delete_project($local_id, $user_id) {

        if ($user_id != null) {

            # evtl aus der ranked_posters view herausholen statt aus der subquery
            $result = getterQuery(
                "SELECT poster_id
                FROM (
                    SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                    FROM poster
                    WHERE poster.user_id = ?
                ) AS ranked_posters
                WHERE local_id = ?",
                ["poster_id"],
                "ii", $user_id, $local_id
            );
            $res = deleteQuery(
                "DELETE FROM poster WHERE poster.poster_id = ?",
                "i", json_decode($result, true)["poster_id"][0]
            );
            /*
                if(!$res) {
                    $msgs["error"][] = "deleteQuery failed...";
                }
            */
            // res  checken: wenn deleteQuery true/false (boolean) zurückgibt chekcen, also fehlerbehandlung
            //refresh list
            $data = getterQuery(
                "SELECT title FROM poster WHERE poster.user_id=?",
                ["title"],
                "s", $user_id
            );
            return $data;
            # TODO: nach jedem ausgeben von json exit 0, damit nicht ausversehen 2 oder mehr jsons konkatiniert werden
            # exit(0);
        }else{
            return "No or invalid session";
        }
    }

    function updateVisibility($id, $value) {
        $result = editQuery(
            "UPDATE poster SET visible=?
            WHERE poster_id=(
                SELECT poster_id FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id
                FROM poster
                WHERE fk_view_mode = ?
            ) AS inner_query
            WHERE local_id = ?)", "iii", $value, 1, $id
        );
        return $result;
    }

    if(isset($_POST['action'])) {
        # evtl noch checken dass der name mindestens 3 zeichen hat (oder so)
        if ($_POST['action'] == 'register') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            echo register($name, $pw);
        }

        if ($_POST['action'] == 'login') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            echo login($name, $pw);
        }

        if ($_POST['action'] == 'fetch_all_projects') {

            $user_id = getValidUserFromSession();
            echo fetch_projects($user_id, false);
        }

        if ($_POST['action'] == 'delete_project') {
            $local_id = isset($_POST['local_id']) ? $_POST['local_id'] : '';

            $user_id = getValidUserFromSession();
            echo delete_project($local_id, $user_id);
        }

        if ($_POST['action'] == 'create_project') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';

            $user_id = getValidUserFromSession();
            echo create_project($name, $user_id);
        }

        if ($_POST['action'] == 'logout') {

            $user_id = getValidUserFromSession();

            if($user_id != null) {

                echo logout($user_id);
            }else{
                echo "logout error";
            }
        }

        if ($_POST['action'] == 'edit-translation') {
            $local_id = isset($_POST['local_id']) ? $_POST['local_id'] : '';
            $user_id = getValidUserFromSession();

            if ($user_id != null) {
                $poster_id = json_decode(getterQuery(
                        "SELECT poster_id
                    FROM (
                        SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                        FROM poster
                        WHERE poster.user_id = ?
                    ) AS ranked_posters
                    WHERE local_id = ?",
                    ["poster_id"],
                    "ii", $user_id, $local_id
                ), true)["poster_id"][0];

                echo $poster_id;    //"success: local_id: " . $local_id . " poster_id: " . $poster_id . " user_id: " . $user_id;
            }else {
                echo "ERROR";
            }

        }

        if ($_POST['action'] == 'is-admin') {
            $user_id = getValidUserFromSession();

            if ($user_id != null) {

                echo isAdmin($user_id);
            }else{
                echo false;
            }
        }

        if ($_POST['action'] == 'update-visibility') {
            $local_id = isset($_POST['id']) ? $_POST['id'] : '';
            $value = isset($_POST['value']) ? $_POST['value'] : '';

            $user_id = getValidUserFromSession();

            // echo "test";//$local_id . " " . $value . " " . $user_id;

            if ($user_id != null && isAdmin($user_id)) {

                echo $value . " " . updateVisibility($local_id, $value);
            }else{
                echo "ERROR";
            }
        }

        if($_POST['action'] == 'has-valid-user-session') {

            echo getValidUserFromSession() != null;
        }

	    #print(json_encode($msgs));
    }
?>
