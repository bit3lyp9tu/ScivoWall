<?php
    // getterQuery: $stmt->close in exception damit nie offene statements bleiben, auch wenns fehlschlägt
    // getterQuery: nicht per default als json sondern als php datenstruktur zurückgeben; json when needed machen, meist ist es einfacher im code wenn man es als php datenstruktur hat

    // function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	//     throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	// }
	// set_error_handler("exception_error_handler");
	// ini_set('display_errors', '1');

    $db_path = "/etc/dbpw";

    $password = null;

    if (file_exists($db_path)) {
        $password = file_get_contents($db_path);
        $password = chop($password);
    } else {
        die("$db_path not found!");
    }

    // Create connection
    $GLOBALS["conn"] = new mysqli("localhost", "poster_generator", $password);

    // Check connection
    if ($GLOBALS["conn"]->connect_error) {
        die("Connection failed: " . $GLOBALS["conn"]->connect_error);
    }

    function insertQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        // $stmt->set_charset('utf8mb4');
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "success";
    }

    function editQuery($sql, $types, ...$param) {   //TODO: ??????????????????
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully updated";
    }

    # TODO: true/false zurückgeben
    function deleteQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully deleted";
    }

    function getterQuery($sql, $target_values, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        if ($types != "") {
            $stmt->bind_param($types, ...$param);
        }
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $out = array();

            for ($i = 0; $i < count($target_values); $i++) {
                $out[$target_values[$i]] = array();
            }

            while ($row = $result->fetch_assoc()) {
                try {
                    for ($i = 0; $i < count($target_values); $i++) {

                        $out[$target_values[$i]][] = $row[$target_values[$i]];
                    }
                } catch (mysqli_sql_exception $th) {
		            #$stmt->close();
                    return "Error: " . $th->getMessage();
                }
            }
	        #$stmt->close();
            return json_encode($out);

        } else {
	        #$stmt->close();
            return "No results found";
        }
        $stmt->close();
    }

    function getCoulmnNames($query) {
        // print_r("\n" . $query . "\n");
        $colnames = [];
        $str = trim(preg_replace('/\s\s*/', " ", $query));

        if(preg_match("/select\s*(.*)\s*from/i", $str, $matches)) {
            $in_between = $matches[1];

            foreach (explode(",", $in_between) as $col) {
                $col = preg_replace("/^\s*/", "", $col);
                $col = preg_replace("/\s*$/", "", $col);
                $col = preg_replace("/\s+/", " ", $col);

                $colname = $col;
                // print_r(preg_match("/(?<=.+ AS )(\'?[\w\d_ ]+\'?)/i", $col));

                if(preg_match("/.+ as ([\w\d_]+)/i", $col, $internal_matches)) {
                    // (?<=.+ AS )(\'?[\w\d_ ]+\'?)
                    $colname = $internal_matches[1];
                    // if(preg_match("/(?<=.+ AS )(\'?[\w\d_ ]+\'?)/i", $internal_matches[1], $var)) {
                    //     $colname = $var;
                    // }
                }
                $colnames[] = $colname;
            }
        } else {
            $colnames[] = "[ERROR]: ".htmlentities($query)." does not match";
        }
        return $colnames;
    }

    // TODO: needs to be finished (for reference: test complex table with linebreaks)
    function getTableNames($query) {
        $colnames = array();
        // print_r("-----------------------------------------------------------------------------\n" . $query . "\n");

        if(preg_match('/(?<=FROM )[\w+\, ]+/i', $query, $matches)) {

            for ($i=0; $i < sizeof($matches); $i++) {
                foreach (explode(",", str_replace("\n", "", $matches[$i])) as $col) {
                    // print_r("--" . $col);
                    foreach(explode(" ", $col) as $j) {
                        $val = preg_replace("/\s/", "", $j);
                        if (preg_match("/WHERE/i", $val)) {
                            break;
                        }elseif (preg_match("/AS/i", $val)) {
                            continue 2;
                        } else {
                            if ($val != "") {
                                $colnames[] = $val;
                            }
                        }
                        // print_r($val . "\n");
                    }
                }
            }
        } else {
            $colnames[] = "[ERROR]: ".htmlentities($query)." does not match";
        }
        return $colnames;
    }

    function getTypeStr(...$params) {
        $result = "";

        if (is_array($params)) {
            foreach ($params as $i) {
                // if ($i != "") {

                // print_r(implode(",", $params) . "\n-- " . $i . "\n");
                if (gettype($i) == "integer") {
                    $result .= 'i';
                }elseif (gettype($i) == "string") {
                    $result .= 's';
                }else{
                    $result .= '_';
                    throw new Exception('[ERROR] unknown type at index [' . $i . ']');
                }
                // }
            }
        }
        return $result;
    }

    function getDBHeader($table) {
        return json_decode(getterQuery("DESC " . $table . ";", ["Field", "Type", "Null", "Key", "Default", "Extra"], "", null), true);
    }

    function getterQuery2($sql, ...$param) {
        $out = array();

        $target_values = array();
        if (str_contains($sql, '*')) {
            foreach (getTableNames($sql) as $i) {
                $target_values = getDBHeader($i)["Field"];
            }
        }else{
            $target_values = getCoulmnNames($sql);
        }

        if (preg_match_all("/\?/i", $sql) != count($param)) {
            $out["[ERROR]"] =
            "Found param-references '?' (" . preg_match("/\?/i", $sql) . ") in query does not match the amound of params (" . count($param) . ") given.";
            return $out;
            // throw new Exception(
            //     "Found param-references '?' (" . preg_match("/\?/i", $sql) .
            //     ") in query does not match the amound of params (" . sizeof($param) . ") given.");
        }

        $types = "";
        try {
            if (count($param) >= 1) {
                $types = getTypeStr(...$param);
            }
        } catch (Exception $e) {
            $out["[ERROR]"] = $e->getMessage() . " at " . $e->getLine();
            return $out;
        }

        $stmt = $GLOBALS["conn"]->prepare($sql);
        if ($types != "") {
            $stmt->bind_param($types, ...$param);
        }
        $stmt->execute();

        $result = $stmt->get_result();

        for ($i = 0; $i < count($target_values); $i++) {
            $out[$target_values[$i]] = array();
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                try {
                    for ($i = 0; $i < count($target_values); $i++) {
                        $out[$target_values[$i]][] = $row[$target_values[$i]];
                    }
                } catch (mysqli_sql_exception $th) {
                    $out["[ERROR]"] = $th->getMessage() . " " . $th->getLine();
                    break;
                }
            }
        }
        $stmt->close();
        return $out;
    }

    function simpleQuery($sql) {
        // $stmt = $GLOBALS["conn"]->prepare($sql);
        // $stmt->execute();
        // $stmt->close();

        return "";
    }

    // Old
    function runSingleQuery ($sql, $allows_num_rows=true) {
	    $result = $GLOBALS["conn"]->query($sql);
	    $substr = explode(" ",$sql);

	    if (!$result) {
		    return "ERROR";
	    }

	    if($allows_num_rows) {
		    if ($result->num_rows > 0) {
			    // Initialize an empty string to store the result
			    $output = "";

			    // Fetch all rows and generate HTML output
			    while ($row = $result->fetch_assoc()) {
				    $output .= "<div>" . htmlspecialchars($row[$substr[1]]) . "</div>";
			    }

			    $output .= "";
			    return $output;
		    } else {
			    return "No results found";
		    }
	    }
    }


    // function getTitle($id) {
	//     # prepared statement oder mysqli_real_escape_string
    //     return runSingleQuery("SELECT title FROM poster WHERE poster.poster_id=" . $id);
    // }
?>
