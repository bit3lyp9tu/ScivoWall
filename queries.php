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

		return true;
	}

	function getLastInsertID() {
		return getterQuery(
			"SELECT LAST_INSERT_ID()"
		)["LAST_INSERT_ID()"][0];
	}

	function editQuery($sql, $types, ...$param) {
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

		return true;
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
		return getterQuery("DESC " . $table);
	}

	function assert_utf8_or_die($value) {
		if (!mb_check_encoding($value, 'UTF-8')) {
			// Generate a stack trace
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$message = "Invalid UTF-8 detected.\nStack trace:\n";

			foreach ($trace as $index => $frame) {
				$file = $frame['file'] ?? '[internal]';
				$line = $frame['line'] ?? '?';
				$function = $frame['function'] ?? '?';
				$class = isset($frame['class']) ? $frame['class'] . $frame['type'] : '';
				$message .= "#$index $file($line): $class$function()\n";
			}

			die($message);
		}
	}

	function getterQuery($sql, ...$param) {
		$out = array();
		$attributes = array();

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

		$meta = $result->fetch_fields();
		foreach ($meta as $col) {
			$out[$col->name] = [];
		}

		while ($row = $result->fetch_assoc()) {
			foreach ($row as $key => $value) {
				if (is_string($value)) {
					assert_utf8_or_die($value);
					//$value = mb_convert_encoding($value, 'UTF-8');
				}
				$out[$key][] = $value;
			}
		}
		$stmt->close();
		return $out;
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
