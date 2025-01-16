<?php
    include_once("queries.php");

    function generate_salt($len=32) {
        $pr_bits = '';
        $fp = @fopen('/dev/urandom','rb');
        if ($fp !== FALSE) {
            $pr_bits .= @fread($fp, $len);
            @fclose($fp);
        }
        $pr_bits = unpack("C*", $pr_bits);
        $str = "";

        foreach ($pr_bits as $entry) {
            $str .= chr(33 + ($entry % 94));
        }
        return substr($str, 0, $len);
    }

    /*
    Validates current session
    if session not valid, returns null
    if session valid, returns the corresponding user_id
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
            FROM poster_generator.session
            WHERE session.sessionID = ?",
            ["user_id"],
            "s", $sessionID
        );
        return json_decode($session, true)["user_id"][0];
    }

    $msgs = array(
        "error" => [],
        "success" => [],
        "warning" => []
    );

    //echo generate_salt();

    if(isset($_POST['action'])) {
        # evtl noch checken dass der name mindestens 3 zeichen hat (oder so)
        # pw komplexität checken!
        if ($_POST['action'] == 'register') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

            $salt = generate_salt();//"a2d47c981889513c5e2ddbca71f414";//

            echo "SALT:$salt:SALT";

            $pepper = "a2d47c981889513c5e2ddbca71f414"; //TODO: use pepper dependency
            $hash = md5($pw . ":" . $salt . ":" . $pepper);

            echo $pw . ":" . $salt . ":" . $pepper . "----" . $hash;
            try {
                echo insertQuery("INSERT INTO poster_generator.user (`name`, `pass_sha`, `salt`, `pepper`)
                    VALUES (?, ?, ?, ?)",
                    "ssss", $name, $hash, $salt, $pepper);

            } catch (mysqli_sql_exception $th) {
                if ($th->getCode() == 1062) {
                    echo "The user " . $name . " already exists.";
                }else{
                    echo $th->getMessage();
                }
            }
        }

        if ($_POST['action'] == 'login') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';
            $pw = isset($_POST['pw']) ? $_POST['pw'] : '';

	        # TODO: select user_id from poster_generator.user where username = ? and passwort = sha(?);

            $result = getterQuery(
                "SELECT user_id, pass_sha, salt, pepper FROM poster_generator.user WHERE user.name=?",
                ["user_id", "pass_sha", "salt", "pepper"],
                "s", $name);

            if ($result == "No results found") {
                echo "Wrong Password or Username";
            } else {

                $res_dec = json_decode($result, true);

                $hash = $res_dec["pass_sha"][0];
                $salt = $res_dec["salt"][0];
                $pepper = $res_dec["pepper"][0];

		        # NICHT md5, sondern sha überall
                if (md5($pw . ":" . $salt . ":" . $pepper) == $hash) {

                    //Create new Session
                    $user_id = $res_dec["user_id"][0];
                    $sid = session_create_id();
		            # evtl umstellen auf unix-zeit
                    $exp_date = new DateTime('now', new DateTimeZone('Europe/Berlin'));
                    // $exp_date->add(new DateInterval("P1D"));
                    $exp_date->add(new DateInterval("PT5M"));

                    $insertion = insertQuery(
                        "INSERT INTO poster_generator.session (user_id, sessionID, expiration_date)
                        VALUE (?, ?, ?)",
                        "iss", $user_id, $sid, $exp_date->format("Y-m-d H:i:s"));

                    if ($insertion == "success") {
                        setcookie("sessionID", $sid, time() + 300, "/", "", false, true);
                        //TODO: do the cookie settings need a rework? Update to PHP 7.3 and later might be required
                        //PHP 7.3 and later has additional parameter SameSide=Strict and SameSide=Lax

                        echo "Correct Password: " . $user_id . " " . $sid . " " . $exp_date->format("Y-m-d H:i:s");
                    }else {
                        echo $insertion;
                    }
                }else {
                    echo "Wrong Username or Password";
                }
            }
        }

        if ($_POST['action'] == 'fetch_all_projects') {
            // $name = isset($_POST['name']) ? $_POST['name'] : '';

            // $user_id = getterQuery(
            //     "SELECT user_id FROM poster_generator.user WHERE user.name=?",
            //     ["user_id"],
            //     "s", $name
            // );

            $user_id = getValidUserFromSession();
            if ($user_id != null) {

                if ($user_id == "No results found") {
                    echo "No results found";
                }else{
                    $result = getterQuery(
                        "SELECT title FROM poster_generator.poster WHERE poster.user_id=?",
                        ["title"],
                        "s", $user_id//json_decode($user_id, true)["user_id"][0]
                    );
                    echo $result;
                }

            }else{
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'delete_project') {
            $local_id = isset($_POST['local_id']) ? $_POST['local_id'] : '';

            $user_id = getValidUserFromSession();
            if ($user_id != null) {

		    # evtl aus der ranked_posters view herausholen statt aus der subquery
                $result = getterQuery(
                    "SELECT poster_id
                    FROM (
                        SELECT ROW_NUMBER() OVER (ORDER BY poster_id) AS local_id, poster_id
                        FROM poster_generator.poster
                        WHERE poster.user_id = ?
                    ) AS ranked_posters
                    WHERE local_id = ?",
                    ["poster_id"],
                    "ii", $user_id, $local_id
                );

                $res = deleteQuery(
                    "DELETE FROM poster_generator.poster WHERE poster.poster_id = ?",
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
                    "SELECT title FROM poster_generator.poster WHERE poster.user_id=?",
                    ["title"],
                    "s", $user_id
                );
                echo $data;
                # todo: nach jedem ausgeben von json exit 0, damit nicht ausversehen 2 oder mehr jsons konkatiniert werden
                # exit(0);
            }else{
                echo "No or invalid session";
            }
        }

        if ($_POST['action'] == 'create_project') {
            $name = isset($_POST['name']) ? $_POST['name'] : '';

            $user_id = getValidUserFromSession();
            if ($user_id != null) {

                if ($user_id != "") {
                    $res = insertQuery(
                        "INSERT into poster_generator.poster (title, user_id) VALUE (?, ?)",
                        "si", $name, $user_id//["user_id"][0]
                    );

                    //refresh list
                    $data = getterQuery(
                        "SELECT title FROM poster_generator.poster WHERE poster.user_id=?",
                        ["title"],
                        "s", $user_id//["user_id"][0]
                    );
                    echo $data;
                }else {
                    echo "ERROR";
                }

            }else{
                echo "No or invalid session";
		    #$msgs["errors"][] = "No or invalid session";
            }
        }

	    #print(json_encode($msgs));
    }
?>
