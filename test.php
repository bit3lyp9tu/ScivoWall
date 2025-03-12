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
    <!-- Draggable DIV -->
    <!-- <div id="mydiv">
        <div id="mydivheader">Click here to move</div>
        <p>Move</p>
        <p>this</p>
        <p>DIV</p>
        <p id="x"></p>
        <p id="y"></p>
    </div> -->

    <div id="container"></div>
</body>

<script>

    for (let i = 0; i < 8; i++) {
        var parent = document.createElement("div");

        parent.id = "nr-" + i;
        parent.classList.add("item");

        const t = document.createElement("p");
        t.innerText = "Dragable Object " + i;
        parent.appendChild(t);

        const tx = document.createElement("p");
        tx.innerText = "px";
        parent.appendChild(tx);

        const ty = document.createElement("p");
        ty.innerText = "py";
        parent.appendChild(ty);

        parent.style.left = (50 + i * 150) + "px";

        parent.setAttribute("draggable", "true");

        document.getElementById("container").appendChild(parent);
    }

    function getClickedElementId(event) {
        if (document.getElementById("container").contains(event.target)) {
            if (!event.target.id && event.target.parentNode.id.startsWith("nr-")) {
                return event.target.parentNode.id;
            }else{
                return event.target.id;
            }
        }else{
        }
        return "";
    }

    function realign_items(id_of_missing_item, modifier) {
        let parent = document.getElementById("container");
        for (let i = id_of_missing_item.split("-")[1]; i < parent.children.length; i++) {
            let element = parent.children[i];

            if (element.id != id_of_missing_item) {
                element.style.color = "blue";//left = (50 + i * 150) + "px";
                element.style.left = Number(element.style.left.replace("px", "")) + Number(modifier * 150) + "px";
            }
        }
    }

    var dragId = "";
    document.addEventListener("dragstart", function (event) {
        dragId = getClickedElementId(event);
        if(event.target.getAttribute("draggable") === "true") {
            console.log(event.target, dragId);
            console.log("dragId: ", dragId)
            console.log("event:", event)

        }

        if (dragId) {
            // dragElement(document.getElementById(dragId));
            // realign_items(dragId, 1);
        }
    });

    document.addEventListener("dragend", function (event) {
        // event.preventDefault();

        console.log(event.target);

    });

    function dragElement(elmnt) {
        var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        if (document.getElementById(elmnt.id + "header")) {
            // if present, the header is where you move the DIV from:
            document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
        } else {
            // otherwise, move the DIV from anywhere inside the DIV:
            elmnt.onmousedown = dragMouseDown;
        }

        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            // get the mouse cursor position at startup:
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            // call a function whenever the cursor moves:
            document.onmousemove = elementDrag;
        }

        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            // calculate the new cursor position:
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            // set the element's new position:
            elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
            elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";

            // document.getElementById("x").innerText = elmnt.style.top;
            // document.getElementById("y").innerText = elmnt.style.left;
        }

        function closeDragElement() {
            // stop moving when mouse button is released:
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }
</script>

<style>
     #mydiv {
        position: absolute;
        z-index: 9;
        background-color: #f1f1f1;
        border: 1px solid #d3d3d3;
        text-align: center;
    }

    #mydivheader {
        padding: 10px;
        cursor: move;
        z-index: 10;
        background-color: #2196F3;
        color: #fff;
    }

    .item {
        border: 1px solid black;
        width: 140px;

        position: absolute;
    }

</style>

</html>
