async function make_editable(div, text) {
    let currently_edited_element = null;
    div.contentEditable = true;

    div.addEventListener("focus", function () {
        log("FOCUS");
        if (!this.getAttribute("data-original")) {
            this.setAttribute("data-original", toMarkdown(this.innerHTML));
            this.innerText = toMarkdown(this.innerHTML);
        } else {
            this.innerHTML = this.getAttribute("data-original");
        }
        currently_edited_element = div;
    });

    div.addEventListener("drop", function (event) {
        log("DROP");
        event.preventDefault();
        var file = event.dataTransfer.files[0];
        uploadImage(file, function (response) {
            var filename = response.filePath;
            div.innerText += `![](${filename})`;
            div.setAttribute("data-original", div.innerHTML);
        });
    });

    div.addEventListener("dragover", function (event) {
        event.preventDefault();
    });

    document.addEventListener("click", async function (event) {
        if (currently_edited_element && !div.contains(event.target)) {
            log("CLICK");
            div.setAttribute("data-original", div.innerHTML);
            if (!div.innerText.match(/^\s*$/)) {
                var innerText = filter_html(div.innerText);
                var html = mdToHtml(innerText);
                div.innerHTML = html;
                await typeset(currently_edited_element);
                currently_edited_element = null;
                $(".MathJax").css("pointer-events", "none");
            } else {
                $(div).remove();
            }
            save_current_json();
        }
    });

    div.setAttribute("data-original", text);

    //log("Sample text:", text);
    text = filter_html(text);
    var html = mdToHtml(text);
    html = html.split("<span>").join("").split("</span>").join("");
    //log("HTML:", html);
    div.innerHTML = html;
    //await typeset(div);
    currently_edited_element = null;
    $(".MathJax").css("pointer-events", "none");
}
