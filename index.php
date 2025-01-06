
<!-- TODO: box height changes between selected and unselected state-->
<!-- TODO: if cursor releases click outside of box, deleted text will return-->
<!-- TODO: storing project - might need a version id in json-->
<!-- TODO: img bgpattern has strange border-->
<!-- TODO: footer too big -->
<!-- TODO: after page load latex/md view does not activate-->
<!-- TODO: reorganizing html format -->
<!-- TODO: footer animation sometimes slightly misplaced (not to right border; moves out of window)-->
<!-- TODO: if img pasted in box, only visible if box selected
 			- not visible in latex/md view mode except if other box with image is selected-->
<!-- TODO: scads-graphic-edited.mov does not have a clean transition after replay
 	 TODO: video sequence .mov does not play-->
<!-- TODO: error in address-link is constantly produced-->
<!-- TODO: remove content redundancy in .box elements -->

<?php
	include_once("functions.php");
?>

<!DOCTYPE html>
<html>
	<head>
		<title>v2 Poster-Generator</title>

		<link rel="stylesheet" href="style.css">

		<script src="marked.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/to-markdown/dist/to-markdown.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>

		<script type="text/x-mathjax-config" src="myscripts.js"></script>
		<script src="events.js"></script>
		<script src="json-functions.js"></script>

		<script src="encryption.js"></script>
	</head>

	<body>
		<div id="logo_headline">
			<img class="nomargin" id='scadslogo' src="img/scadslogo.png" />
			<div style="float: right;">
				<img id='bgpattern' src="img/bgpattern.jpeg" />
				<!-- <?php //if(!get_get("disable_video")) {?> -->
				<video autoplay="true" loop="true" muted="muted" [muted]="'muted'" id="bgpattern" src="scads-graphic_edited.mov"></video>
				<!-- <?php //}?> -->
			</div>
		</div>

		<div id="titles">
			<h1 id="maintitle">Heading 1</h1>
			<h2 id="mainsubtitle">Heading 2</h2>
			<button id="add-box-button">Add Box</button>
		</div>

		<div class="container">
			<div class="box">
			</div>
		</div>

		<div id="resize_me_according_to_bottom_table"></div>

		<div class="grid-container">
			<div class="large-div">
				<img src="img/qrcode.png" class="large_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/tudlogo.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/leipzig.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/cbg.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/leibnitz.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/helmholtz.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/hzdr.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/infai.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/maxplanck2.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/fraunhofer1.jpg" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/fraunhofer2.jpg" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/dlr.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="img/maxplanck3.jpeg" class="small_logo" alt="logo">
			</div>
			<div class="bottom-div">
				<?php if(!get_get("disable_video")) {?>
					<video id="bottom_video" autoplay="true" loop="true" muted="muted" [muted]="'muted'" class="bottompattern" src="img/footer.mp4"></video>
				<?php }?>
			</div>
		</div>

		<script>

			function mdToHtml(md) {
				// log("md", md);
				var html = marked.marked(md);
				// log("html", html);
				return html;
			}

			async function typeset(element) {
				//log("typeset start >>>>");
				//log("element:", element);
				//console.trace();
				if(element) {
					await MathJax.typesetPromise([element]);
				} else {
					await MathJax.typesetPromise();
				}
				//log("<<<< typeset end");
			}

			var last_saved_hash = null;

			function log(...args) { console.log(args); }

			var sample_text = /* TODO: move to sample-data.json*/
				"## Hallo Welt\n<br>\n\nhallo \\\\(\\sum^10_{i = 0} i\\\\)<br>\n\n $$ \\frac{a}{b} = b $$";
				//sample_data["latex"][0];

			function adjust_video_height_depending_on_logo_height( ) {
				$("#bgpattern").css("max-height", $("#scadslogo").get()[0].scrollHeight);
			}

			//???
			function filter_html(data) {
				//TODO: prevent possible code injectionadd-box-button

				data = data.split("<div>").join("\n\n").split("</div>").join("\n\n").split("<br>").join("\n\n").split("<span>").join("").split("</span>").join("");

				data = data.split("\n").filter(function (el) { return el != null && el != ""; }).join("\n<br>\n\n");

				const regex = /<br>\n{1,}-(.*)/g;

				//log("Before re", data);
				data = data.replace(regex, '\n-$1');
				//log("After re", data);

				return data;
			}

			function uploadImage(file, callback) {
				var formData = new FormData();
				formData.append("image", file);
				var xhr = new XMLHttpRequest();
				xhr.open("POST", "upload.php", true);
				xhr.onload = function() {
					if (xhr.status === 200) {
						var response = JSON.parse(xhr.responseText);
						callback(response);
					}
				};
				xhr.send(formData);
			}

			const make_editable_simple = (element) => {
				element.setAttribute('data-original', element.innerText);
				element.contentEditable = false;	//why???

				element.addEventListener('click',( ) => {
					element.contentEditable = true;	//?
					element.focus();
				});

				document.addEventListener('click', (event) => {
					if( !element.contains(event.target)) {
						element.contentEditable = false;	//?
						save_current_json();
					}
				});

				element.addEventListener('input', () => {
					element.setAttribute('data-original', element.innerText);
				});
			};

			async function make_editable(div, text) {
				let currently_edited_element = null;
				div.contentEditable = true;		//???

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

			make_editable_simple($("#maintitle")[0]);
			make_editable_simple($("#mainsubtitle")[0]);

			// var div = $(".box")[0];
			// make_editable(div, sample_text);

			// document.addEventListener('keypress', (event) => {
			// 	log("--------------------");
			// 	for (var div of $('.box')) {
			// 		console.log(div);

			// 		// div.innerHTML = div.getAttribute("data-original");

			// 		// div.addEventListener("focus", function () {
			// 		// 	log("FOCUS");
			// 		// 	if (!this.getAttribute("data-original")) {
			// 		// 		this.setAttribute("data-original", toMarkdown(this.innerHTML));
			// 		// 		this.innerText = toMarkdown(this.innerHTML);
			// 		// 	} else {
			// 		// 		this.innerHTML = this.getAttribute("data-original");
			// 		// 	}
			// 		// });
			// 		currently_edited_element = div;

			// 		if (currently_edited_element && !div.contains(event.target)) {
			// 			log("CLICK");
			// 			div.setAttribute("data-original", div.innerHTML);
			// 			if (!div.innerText.match(/^\s*$/)) {
			// 				var innerText = filter_html(div.innerText);
			// 				var html = mdToHtml(innerText);
			// 				div.innerHTML = html;
			// 				await typeset(currently_edited_element);
			// 				currently_edited_element = null;
			// 				$(".MathJax").css("pointer-events", "none");
			// 			} else {
			// 				$(div).remove();
			// 			}
			// 			// save_current_json();
			// 		}
			// 	}
			// });

			window.addEventListener("load", (event) => {
				log("PAGE LOAD");
				// console.log(document.getElementsByTagName('.container').length);

			});

			async function add_box( text, save_json = 1) {
				const box = document.createElement("div");
				box.classList.add("box");
				await make_editable(box, text);
				document.querySelector(".container").appendChild(box);

				if(save_json) {
					save_current_json();
				}

				await typeset(box);
				$(".MathJax").css("pointer-events", "none");
				return box;
			}

			async function add_box_sample_text( ) {
				return await add_box(sample_text);
			}

			document.querySelector("#add-box-button").addEventListener("click", add_box_sample_text);

			function resize_free_space_according_to_bottom_table( ) {
				$("#resize_me_according_to_bottom_table").css("height", $(".grid-container")[0].scrollHeight);
			}


			$(window).resize(function() {
				adjust_video_height_depending_on_logo_height();
				resize_free_space_according_to_bottom_table();
			});

			$(document).ready(function() {
				adjust_video_height_depending_on_logo_height();
				resize_free_space_according_to_bottom_table();
			});

			setInterval(adjust_video_height_depending_on_logo_height,1000);
			setInterval(resize_free_space_according_to_bottom_table,1000);

			<?php
				$json_dir = isDocker() ? "/poster_generator_json" : "json";

				if(isset($_GET["id"]) && preg_match("/^[a-zA-Z0-9]+$/", $_GET["id"]) && file_exists("$json_dir/".$_GET["id"].".json")) {
			?>
				var load_from_here = <?php print file_get_contents("$json_dir/".$_GET["id"].".json") ?>;

				log(load_from_here);

				load_from_json(load_from_here);

			<?php } else {?>
				var load_from_here = {
					/* TODO: move to sample-data.json*/
					"maintitle": "Name of the project",
					"mainsubtitle": "Author(s)",
					"boxes":[
						`## Example Box<br>
						Write inline math: \\\\( \\sum^10_{i = 0} i \\\\)<br>

						Or write \\displaystyle math:<br>

						$$ \\sum_{[\\mathrm{bla}]}^{i} a^i $$<br>

						You can also write text or lists:<br>
						- Element 1<br>
						- Element 2<br>
						- Element 3<br>
						<br>
						Or insert images (simply click in a field and drop an image here).<br>
						`,
						`## How to delete a box<br>

						Just click in it and delete all text. Then click outside again. It will delete the box.<br>`
					]
				}

				load_from_json(load_from_here);
			<?php }?>
		</script>
	</body>
</html>
