<?php
    // getterQuery: $stmt->close in exception damit nie offene statements bleiben, auch wenns fehlschlägt
    // getterQuery: nicht per default als json sondern als php datenstruktur zurückgeben; json when needed machen, meist ist es einfacher im code wenn man es als php datenstruktur hat

    // function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	//     throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
	// }
	// set_error_handler("exception_error_handler");
	// ini_set('display_errors', '1');

    include_once(__DIR__ . "/" . "mysql.php");

    function insertQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        // $stmt->set_charset('utf8mb4');
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "success";
    }

    function getLastInsertID() {
        return getterQuery2(
            "SELECT LAST_INSERT_ID()"
        )["LAST_INSERT_ID()"][0];
    }

    function editQuery($sql, $types, ...$param) {   //TODO:   ??????????????????
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully updated";
    }

    # TODO:   true/false zurückgeben
    function deleteQuery($sql, $types, ...$param) {
        $stmt = $GLOBALS["conn"]->prepare($sql);
        $stmt->bind_param($types, ...$param);
        $stmt->execute();
        $stmt->close();

		return "successfully deleted";
    }

    // TODO:   include matching after '*'
    function getColumnNames($query, $rm_kleene=true) {
        $colnames = [];

        $n = str_replace("\n", " ", $query);
        $n = str_replace("\t", " ", $n);

	$n = preg_replace("/\s?(\w+\([\w\.]+\))(?=( AS \w+)?\,?)/i", " FILLER ", $n);

	if(preg_match_all("/(?<=SELECT )[\w\d\s\,\*\-\.]+(?= FROM)/i", $n, $matches)) {
		foreach ($matches[0] as $j) {
			$res = explode(",", $j);
			foreach ($res as $i) {
				if(preg_match("/(?<= AS )\w+/i", $i, $res2)) {
					$colnames[] = str_replace(" ", "", $res2[0]);
				}else{
					$colnames[] = str_replace(" ", "", $i);
				}
			}
		}
	}elseif (preg_match_all('/(?<=SELECT )\w+\(\w?\)( AS \w+)?\;?/i', $query, $matches)) {
		if(preg_match("/(?<= AS )\w+/i", $matches[0][0], $res2)) {
			$colnames[] = $res2[0];
		}else{
			$colnames[] = $matches[0][0];
		}
	} else {
		$colnames[] = "[ERROR]: ".htmlentities($query)." does not match";
	}

	$result = [];
	if($rm_kleene) {
		for ($i=0; $i < sizeof($colnames); $i++) {
			if ($colnames[$i] != '*' && $rm_kleene) {
				$result[] = $colnames[$i];
			}//else {
			// $res = getDBHeader(getTableNames($query));
			// foreach ($res as $j) {
			//     $result[] = $res[$j];
			// }
			// }
		}
	}else{
		//TODO:   how to treat cases with kleene?
		$result = $colnames;
	}
	return array_values(array_unique($result));//TODO:   ???
    }

    // TODO:   needs to be finished (for reference: test complex table with linebreaks)
    function getTableNames($query) {
	    $colnames = array();

	    if(preg_match('/(?<=FROM )[\w+\, ]+/i', $query, $matches)) {

		    for ($i=0; $i < sizeof($matches); $i++) {
			    foreach (explode(",", str_replace("\n", "", $matches[$i])) as $col) {
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
				    }
			    }
		    }
	    }elseif (preg_match('/(?<=SELECT )\w+\(\w?\)( AS \w+)?\;?/i', $query)) {
		    // TODO:
	    } else {
		    $colnames[] = "[ERROR]: ".htmlentities($query)." does not match";
	    }
	    return $colnames;
    }

    function matchColTable($query) {

	    $n = str_replace("\n", " ", $query);
	    $n = str_replace("\t", " ", $n);

	    $words = explode(" ", $n);

	    for ($i=0; $i < sizeof($words); $i++) {
		    if ($words[$i] == '*') {
			    for ($j=$i; $j < sizeof($words); $j++) {
				    if ($words[$i] == '*') {

				    }
			    }
		    }else{

		    }
	    }

	    return $words;
    }

    function resolveBrackets($input) {
	    $result = [];

	    $str = $input;
	    $i = 0;

	    $pattern = "/\([\w*\d*\s\{\}\.\*\,\=\<\>]+\)/i";

	    while (true) {
		    //TODO:   throw error if runs longer then 1 min
		    if(str_contains($str, '(') && str_contains($str, ')')) {
			    if(preg_match_all($pattern, $str, $matches)) {
				    $result[] = $matches[0];
				    $str = preg_replace($pattern, "{" . $i . "}", $str);

				    $i += 1;
			    }
		    }else{
			    $result[][] = $str;
			    break;
		    }
	    }
	    return $result;
    }

    function last($list) {
	    return $list[array_key_last($list)];
    }

    function buildBrackets($structure) {
	    $str = last(last($structure));

	    while (true) {
		    preg_match_all("/\{[0-9]+\}/i", $str, $matches, PREG_OFFSET_CAPTURE);
		    if (sizeof($matches[0]) > 0) {

			    $end = last($matches[0])[0];
			    $value = array_pop($structure[str_replace("{", "", str_replace("}", "", $end))]);
			    $match_index = count($matches[0]) - 1;
			    $match_position = $matches[0][$match_index][1];
			    $str = substr_replace($str, $value, str_replace("{", "", str_replace("}", "", $match_position)), strlen($matches[0][$match_index][0]));
		    }else{
			    break;
		    }
	    }
	    return $str;
    }

    function getTypeStr(...$params) {
	    $result = "";

	    if (is_array($params)) {
		    foreach ($params as $i) {
			    if (gettype($i) == "integer") {
				    $result .= 'i';
			    }elseif (gettype($i) == "string") {
				    $result .= 's';
			    }else{
				    $result .= '_';
				    throw new Exception('[ERROR] unknown type at index [' . $i . ']');
			    }
		    }
	    }
	    return $result;
    }

    function getDBHeader($table) {
	    return getterQuery2("DESC " . $table . ";");
    }

    // TODO:      using from_unixtime(last_edit_date) AS 'last edit' as selector throws errors
    function getterQuery2($sql, ...$param) {
	    $out = array();

	    $target_values = array();
	    if(explode(" ", $sql)[0] == "DESC") {
		    $target_values = ["Field", "Type", "Null", "Key", "Default", "Extra"];
	    }else{
		    if (str_contains($sql, '*')) {
			    foreach (getTableNames($sql) as $i) {
				    $target_values = getDBHeader($i)["Field"];
			    }
		    }else{
			    $target_values = getColumnNames($sql);
            }
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
            // if(array_key_exists($target_values[$i], $row)) {
            $out[$target_values[$i]] = array();
            // }
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                try {
                    for ($i = 0; $i < count($target_values); $i++) {
                        if(array_key_exists($target_values[$i], $row)) {
                            $out[$target_values[$i]][] = $row[$target_values[$i]];
                        }
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
				    $key = $substr[1];
				    if (!isset($row[$key])) {
					    $errmsg = "ERROR: ".json_encode($row)." -> $key is not set. Query: ".json_encode($sql)."\n";
					    throw new Exception($errmsg);
				    }
				    $result_to_be_printed = $row[$key];
				    $output .= "<div>" . htmlspecialchars($result_to_be_printed) . "</div>";
			    }

			    $output .= "";
			    return $output;
		    } else {
			    return "No results found";
		    }
	    }
    }

    function runQuery($sql) {
        try {
            $result = $GLOBALS["conn"]->query($sql);
        } catch (Throwable $th) {

            return array();
        }


        if ($result === True) {
            return $result;
        }else{
            try {
                return $result->fetch_all();
            } catch (Throwable $th) {
                return array();
            }
        }

        // $obj = mysqli_fetch_object($result);
        // print_r($obj);
    }
?>
