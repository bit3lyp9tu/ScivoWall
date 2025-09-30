<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang='en'>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <div id="content"></div>
    <script>
        fetch('README.md')
            .then(response => response.text())
            .then(text => {
            document.getElementById('content').innerHTML = marked.parse(text);
            });
    </script>

    <style>

        #content {
            padding-left: 20px;
            padding-right: 20px;
        }

        code {
            background-color: #f5f5f5;
            padding: 2px 4px;
            border-radius: 4px;
            font-family: monospace;
        }

        /* Block code (from triple backticks) */
        pre {
            background-color: #f5f5f5;
            padding: 12px;
            border-radius: 6px;
            overflow-x: auto;
            /* Horizontal scroll if long lines */
        }

        pre code {
            background: none;
            /* Prevent double background */
            padding: 0;
            font-size: 0.9em;
            display: block;
        }
    </style>
</html>
