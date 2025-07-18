<?php
/*
include("functions.php");

// 1. Voller REQUEST_URI, z.B. /scientific_poster_generator/index.php oder /scientific_poster_generator/post_page
$requestUri = $_SERVER['REQUEST_URI']; 

// 2. Aktuellen Scriptpfad, z.B. /scientific_poster_generator/index.php
$scriptName = $_SERVER['SCRIPT_NAME'];

// 3. Projektverzeichnis ableiten, indem man SCRIPT_NAME nimmt und den letzten Teil (Dateiname) abschneidet
// z.B. "/scientific_poster_generator/index.php" => "/scientific_poster_generator"
$projectDir = dirname($scriptName);
if ($projectDir === DIRECTORY_SEPARATOR) {
    // root-Verzeichnis, z.B. "/"
    $projectDir = "";
}

// 4. Entferne Projektverzeichnis vom REQUEST_URI
if ($projectDir !== "" && strpos($requestUri, $projectDir) === 0) {
    $requestUri = substr($requestUri, strlen($projectDir));
}

// 5. Datei nach letztem / holen (z.B. aus "/post_page" wird "post_page")
$file = $requestUri;
if ($file === '') {
    // Wenn leer, z.B. bei "/" oder "/scientific_poster_generator/", setze default z.B. index.php
    $file = 'index.php';
}

// 6. Pfade bauen
$docRoot = $_SERVER['DOCUMENT_ROOT'] . $projectDir; // z.B. /var/www/html/scientific_poster_generator
$file = preg_replace("/\/scientific_poster_generator\//", "", $file);
$fullPath = "./$file";
//
// 7. Funktion zum transparenten Einbinden
function safe_include($path) {
	if (file_exists($path)) {
		$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

		switch ($ext) {
		case 'css':
			header('Content-Type: text/css');
			break;
		case 'js':
			header('Content-Type: application/javascript');
			break;
		case 'mp4':
			header('Content-Type: video/mp4');
			break;
			// hier ggf. noch mehr Typen ergänzen
		default:
			// kein Header setzen
			break;
		}

		include $path;
		exit;
	}
}

// 8. Prüfen und includen
safe_include($fullPath);           // Datei existiert direkt
safe_include($fullPath . '.php');  // Datei mit .php-Endung

$pagesPath = $docRoot . '/pages/' . $file;
safe_include($pagesPath);           // Datei in pages/
safe_include($pagesPath . '.php');  // Datei in pages/ mit .php-Endung

// 9. Nichts gefunden
*/
echo "ERROR: konnte den müll $fullPath nicht finden";
exit;
?>
