window.onload = function () {
    const file = "README.md";

    console.info("Loading " + file + "...");
    fetch(file)
        .then(response => response.text())
        .then(text => {
            document.getElementById("content").innerHTML = marked.parse(text);
        });
};

