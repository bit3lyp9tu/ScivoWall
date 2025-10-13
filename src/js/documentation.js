window.onload = function () {
    const file = "documentation/docu.md";

    console.info("Loading " + file + "...");
    fetch(file)
        .then(response => response.text())
        .then(text => {
            document.getElementById("content").innerHTML = marked.parse(text);
        });
};

