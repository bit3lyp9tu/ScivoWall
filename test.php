<?php
    //include("queries.php");
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <title>Poster Generator</title>
    <meta charset='utf-8'>

    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

    <!-- <script src="https://code.jquery.com/jquery.min.js"></script> -->
    <!-- <script src="https://code.jquery.com/typeahead.bundle.min.js"></script> -->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" rel="stylesheet"> -->
    <!-- <link rel='stylesheet' type='text/css' href=style.css> -->
    <!-- <script src="poster.js"></script> -->

    <script src="https://cdn.jsdelivr.net/npm/typeahead-standalone"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/typeahead-standalone/dist/basic.css" />
</head>

<body>
    Page

    <div id="typeahead-container" style="display: flex;">
        <div style="display: flex;">
            <input type="search" id="typeahead" autocomplete="on" placeholder="...">
            <button id="remove-element" onclick=remove()>X</button>
        </div>
    </div>

    <script>
        // start with single textfield
        // once filed with content,
        //      insert new button field
        //      should button pressed - field gets removed

        const inputElement = document.getElementById("typeahead");

        var list = ['Grey', 'Brown', 'Black', 'Blue'];

        const instance = typeahead({
            input: inputElement,
            source: {
                local: list,
            }
        });

        if (document.getElementById("typeahead").value) {
            console.log("content");

            const input = document.createElement("input");
            input.type = "search";
            input.id = "typeahead";
            // input.class = "tt-input";
            input.autocomplete = "on";
            input.placeholder = "...";

            const btn = document.createElement("button");
            btn.id = "remove-element";
            btn.onclick = remove();
            btn.innerText = "X";

            const container = document.createElement("div");
            container.style.display = "flex";
            container.appendChild(input);
            container.appendChild(btn);

            document.getElementById("typeahead-container").appendChild(container);
        }

        function remove() {
            console.log("remove item", this.closest("div"));
            this.closest("div").remove();
        }

    </script>

</body>

<script>


</script>

</html>
