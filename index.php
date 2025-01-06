<?php
//TODO: box height changes between selected and unselected state
//TODO: if cursor releases click outside of box, deleted text will return
//TODO: storing project - might need a version id in json
//TODO: img bgpattern has strange border
//TODO: footer too big
//TODO: after page load latex/md view does not activate
//TODO: reorganizing html format
//TODO: footer animation sometimes slightly misplaced (not to right border; moves out of window)
//TODO: if img pasted in box, only visible if box selected
//                        - not visible in latex/md view mode except if other box with image is selected
//TODO: scads-graphic-edited.mov does not have a clean transition after replay
//         TODO: video sequence .mov does not play
//TODO: error in address-link is constantly produced
//TODO: remove content redundancy in .box elements

	include_once("functions.php");
?>
<!DOCTYPE html>
<html>
       <head>
		<title>v2 Poster-Generator</title>
		<style>
			#titles {
				margin: 20px;
			}

			.grid-container {
				display: grid;
				grid-template-columns: repeat(7, auto);
				grid-template-rows: auto auto;
				padding-top: 10px;
				position: fixed;
				bottom: 0px;
				background-color: white;
				max-height: 250px;
			}

			.large-div {
				grid-column: 1 / span 1;
				grid-row: 1 / span 2;
			}

			.small-div {
				justify-content: center;
				align-items: center;
			}

			.bottom-div {
				grid-column: 1 / span 7;
			}

			.small_logo {
				width: 10vw
			}

			.large_logo {
				max-width: 10vw;
			}
			.container {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
				grid-gap: 10px;
				margin: 20px;
				padding: 0;
			}

			.box {
				padding: 0px;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				overflow-wrap: anywhere;
				border-bottom: 1px solid #83d252;
				margin: 0px;
			}

			.box img {
				max-width: 100%;
				max-height: 100%;
				object-fit: contain;
			}

			.box h2 {
				display: inline-block;
				width: 100%;
				border-bottom: 1px solid;
			}

			h1 {
				font-family: "Barlow", sans-serif;
				margin: 0;
				color: #0e496a;
			}

			h2 {
				font-family: "Barlow", sans-serif;
				margin: 0;
				color: #83d252;
				font-weight: lighter;
			}

			.bottomtable {
				background-color: white;
				margin: -5px;
				position: absolute;
				left: 0px;
				margin-top: 50px;
			}

			body {
				font-family: "Open Sans", sans-serif;
				background-color: #d4d4d4;
				margin: 0px;

				display: block;
				overflow: auto;
				height: 100%;
				min-height: 100%;
				max-height: 100%;
				font-size: calc(14px + 0.1vw);

				margin-left: auto;
				margin-right: auto;
				position: relative;
				background-color: white;
				width: 100%;
				height: 100%;
			}

			#bgpattern {
				padding: 1vw;
				width: 30%;
				top: 0px;
				right: 0px;
				position: absolute;
			}

			.sponsors_logo_large {
				width: calc(80px + 0.1vw);
			}

			.sponsors_logo {
				width: 5vw;
			}

			#scadslogo {
				padding: 1vw;
				width: 10vw;
				min-width: 200px;
			}

			h3 {
				font-family: "Barlow", sans-serif;
				margin: 0;
				color: #0e496a;
			}

			h4 {
				font-family: "Barlow", sans-serif;
				margin: 0;
				color: #0e496a;
				font-weight: lighter;
			}

			#bottom_video {
				width: 100%;
			}
		</style>

		<script src="marked.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/to-markdown/dist/to-markdown.js"></script>
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.min.js"></script>

		<script type="text/x-mathjax-config">
			MathJax.Hub.Config({
				tex2jax: {
					inlineMath: [['\\(', '\\)']],
					displayMath: [['$$', '$$]']],
					processEscapes: true,
					skipTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
					processEnvironments: true,
					processRefs: true
				},
				jax: ["input/TeX", "output/SVG"],
			});

			MathJax.Hub.Queue(function() {
				var all = MathJax.Hub.getAllJax(), i;
				//log(all);
				for(i = 0; i < all.length; i += 1) {
					all[i].SourceElement().parentNode.className += ' has-jax';
				}
			});
		</script>
       </head>
       <body>
		<div id="logo_headline">
			<img class="nomargin" id='scadslogo' src="scadslogo.png" />
			<div style="float: right;">
				<!--<img id='bgpattern' src="bgpattern.jpeg" />-->
<?php
if(!get_get("disable_video")) {
?>
					<video autoplay="true" loop="true" muted="muted" [muted]="'muted'" id="bgpattern" src="scads-graphic_edited.mov"></video>
<?php
}
?>
			</div>
		</div>



















		<div id="titles">
			<h1 id="maintitle">Heading 1</h1>
			<h2 id="mainsubtitle">Heading 2</h2>
			<button id="add-box-btn">Add Box</button>
		</div>


		<div class="container">
			<div class="box">
			</div>
		</div>

		<div id="resize_me_according_to_bottom_table"></div>

		<div class="grid-container">
			<div class="large-div">
				<img src="qrcode.png" class="large_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="tudlogo.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="leipzig.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="cbg.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="leibnitz.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="helmholtz.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="hzdr.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="infai.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="maxplanck2.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="fraunhofer1.jpg" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="fraunhofer2.jpg" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="dlr.png" class="small_logo" alt="logo">
			</div>
			<div class="small-div">
				<img src="maxplanck3.jpeg" class="small_logo" alt="logo">
			</div>
			<div class="bottom-div">
<?php
				if(!get_get("disable_video")) {
?>
					<video id="bottom_video" autoplay="true" loop="true" muted="muted" [muted]="'muted'" class="bottompattern" src="footer.mp4"></video>
<?php
				}
?>
			</div>
		</div>
		<script>

			function mdToHtml(md) {
				//log("md", md);
				var html = marked.marked(md);
				//log("html", html);
				return html;
			}

			//  A formatted version of a popular md5 implementation.
			//  Original copyright (c) Paul Johnston & Greg Holt.
			//  The function itself is now 42 lines long.

			function md5(inputString) {
				var hc="0123456789abcdef";
				function rh(n) {var j,s="";for(j=0;j<=3;j++) s+=hc.charAt((n>>(j*8+4))&0x0F)+hc.charAt((n>>(j*8))&0x0F);return s;}
				function ad(x,y) {var l=(x&0xFFFF)+(y&0xFFFF);var m=(x>>16)+(y>>16)+(l>>16);return (m<<16)|(l&0xFFFF);}
				function rl(n,c)            {return (n<<c)|(n>>>(32-c));}
				function cm(q,a,b,x,s,t)    {return ad(rl(ad(ad(a,q),ad(x,t)),s),b);}
				function ff(a,b,c,d,x,s,t)  {return cm((b&c)|((~b)&d),a,b,x,s,t);}
				function gg(a,b,c,d,x,s,t)  {return cm((b&d)|(c&(~d)),a,b,x,s,t);}
				function hh(a,b,c,d,x,s,t)  {return cm(b^c^d,a,b,x,s,t);}
				function ii(a,b,c,d,x,s,t)  {return cm(c^(b|(~d)),a,b,x,s,t);}
				function sb(x) {
					var i;var nblk=((x.length+8)>>6)+1;var blks=new Array(nblk*16);for(i=0;i<nblk*16;i++) blks[i]=0;
					for(i=0;i<x.length;i++) blks[i>>2]|=x.charCodeAt(i)<<((i%4)*8);
					blks[i>>2]|=0x80<<((i%4)*8);blks[nblk*16-2]=x.length*8;return blks;
				}
				var i,x=sb(inputString),a=1732584193,b=-271733879,c=-1732584194,d=271733878,olda,oldb,oldc,oldd;
				for(i=0;i<x.length;i+=16) {olda=a;oldb=b;oldc=c;oldd=d;
				a=ff(a,b,c,d,x[i+ 0], 7, -680876936);d=ff(d,a,b,c,x[i+ 1],12, -389564586);c=ff(c,d,a,b,x[i+ 2],17,  606105819);
				b=ff(b,c,d,a,x[i+ 3],22,-1044525330);a=ff(a,b,c,d,x[i+ 4], 7, -176418897);d=ff(d,a,b,c,x[i+ 5],12, 1200080426);
				c=ff(c,d,a,b,x[i+ 6],17,-1473231341);b=ff(b,c,d,a,x[i+ 7],22,  -45705983);a=ff(a,b,c,d,x[i+ 8], 7, 1770035416);
				d=ff(d,a,b,c,x[i+ 9],12,-1958414417);c=ff(c,d,a,b,x[i+10],17,     -42063);b=ff(b,c,d,a,x[i+11],22,-1990404162);
				a=ff(a,b,c,d,x[i+12], 7, 1804603682);d=ff(d,a,b,c,x[i+13],12,  -40341101);c=ff(c,d,a,b,x[i+14],17,-1502002290);
				b=ff(b,c,d,a,x[i+15],22, 1236535329);a=gg(a,b,c,d,x[i+ 1], 5, -165796510);d=gg(d,a,b,c,x[i+ 6], 9,-1069501632);
				c=gg(c,d,a,b,x[i+11],14,  643717713);b=gg(b,c,d,a,x[i+ 0],20, -373897302);a=gg(a,b,c,d,x[i+ 5], 5, -701558691);
				d=gg(d,a,b,c,x[i+10], 9,   38016083);c=gg(c,d,a,b,x[i+15],14, -660478335);b=gg(b,c,d,a,x[i+ 4],20, -405537848);
				a=gg(a,b,c,d,x[i+ 9], 5,  568446438);d=gg(d,a,b,c,x[i+14], 9,-1019803690);c=gg(c,d,a,b,x[i+ 3],14, -187363961);
				b=gg(b,c,d,a,x[i+ 8],20, 1163531501);a=gg(a,b,c,d,x[i+13], 5,-1444681467);d=gg(d,a,b,c,x[i+ 2], 9,  -51403784);
				c=gg(c,d,a,b,x[i+ 7],14, 1735328473);b=gg(b,c,d,a,x[i+12],20,-1926607734);a=hh(a,b,c,d,x[i+ 5], 4,    -378558);
				d=hh(d,a,b,c,x[i+ 8],11,-2022574463);c=hh(c,d,a,b,x[i+11],16, 1839030562);b=hh(b,c,d,a,x[i+14],23,  -35309556);
				a=hh(a,b,c,d,x[i+ 1], 4,-1530992060);d=hh(d,a,b,c,x[i+ 4],11, 1272893353);c=hh(c,d,a,b,x[i+ 7],16, -155497632);
				b=hh(b,c,d,a,x[i+10],23,-1094730640);a=hh(a,b,c,d,x[i+13], 4,  681279174);d=hh(d,a,b,c,x[i+ 0],11, -358537222);
				c=hh(c,d,a,b,x[i+ 3],16, -722521979);b=hh(b,c,d,a,x[i+ 6],23,   76029189);a=hh(a,b,c,d,x[i+ 9], 4, -640364487);
				d=hh(d,a,b,c,x[i+12],11, -421815835);c=hh(c,d,a,b,x[i+15],16,  530742520);b=hh(b,c,d,a,x[i+ 2],23, -995338651);
				a=ii(a,b,c,d,x[i+ 0], 6, -198630844);d=ii(d,a,b,c,x[i+ 7],10, 1126891415);c=ii(c,d,a,b,x[i+14],15,-1416354905);
				b=ii(b,c,d,a,x[i+ 5],21,  -57434055);a=ii(a,b,c,d,x[i+12], 6, 1700485571);d=ii(d,a,b,c,x[i+ 3],10,-1894986606);
				c=ii(c,d,a,b,x[i+10],15,   -1051523);b=ii(b,c,d,a,x[i+ 1],21,-2054922799);a=ii(a,b,c,d,x[i+ 8], 6, 1873313359);
				d=ii(d,a,b,c,x[i+15],10,  -30611744);c=ii(c,d,a,b,x[i+ 6],15,-1560198380);b=ii(b,c,d,a,x[i+13],21, 1309151649);
				a=ii(a,b,c,d,x[i+ 4], 6, -145523070);d=ii(d,a,b,c,x[i+11],10,-1120210379);c=ii(c,d,a,b,x[i+ 2],15,  718787259);
				b=ii(b,c,d,a,x[i+ 9],21, -343485551);a=ad(a,olda);b=ad(b,oldb);c=ad(c,oldc);d=ad(d,oldd);
				}
				return rh(a)+rh(b)+rh(c)+rh(d);
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

			var sample_text = "## Hallo Welt\n<br>\n\nhallo \\\\(\\sum^10_{i = 0} i\\\\)<br>\n\n $$ \\frac{a}{b} = b $$";

			function adjust_video_height_depending_on_logo_height () {
				$("#bgpattern").css("max-height", $("#scadslogo").get()[0].scrollHeight);
			}

			async function make_editable(div, text) {
				let currently_edited_element = null;
				div.contentEditable = true;

				div.addEventListener("focus", function() {
					log("FOCUS");
					if (!this.getAttribute("data-original")) {
						this.setAttribute("data-original", toMarkdown(this.innerHTML));
						this.innerText = toMarkdown(this.innerHTML);
					} else {
						this.innerHTML = this.getAttribute("data-original");
					}
					currently_edited_element = div;
				});

				div.addEventListener("drop", function(event) {
					log("DROP");
					event.preventDefault();
					var file = event.dataTransfer.files[0];
					uploadImage(file, function(response) {
						var filename = response.filePath;
						div.innerText += `![](${filename})`;
						div.setAttribute("data-original", div.innerHTML);
					});
				});

				div.addEventListener("dragover", function(event) {
					event.preventDefault();
				});

				document.addEventListener("click", async function(event) {
					if (currently_edited_element && !div.contains(event.target)) {
						log("CLICK");
						div.setAttribute("data-original", div.innerHTML);
						if(!div.innerText.match(/^\s*$/)) {
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

			function filter_html (data) {
				data = data.
					split("<div>").join("\n\n").
					split("</div>").join("\n\n").
					split("<br>").join("\n\n").
					split("<span>").join("").
					split("</span>").join("")
				;

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
				element.contentEditable = false;

				element.addEventListener('click', () => {
					element.contentEditable = true;
					element.focus();
				});

				document.addEventListener('click', (event) => {
					if (!element.contains(event.target)) {
						element.contentEditable = false;
						save_current_json();
					}
				});

				element.addEventListener('input', () => {
					element.setAttribute('data-original', element.innerText);
				});
			};

			function get_current_page_json () {
				var json = {
					maintitle: $("#maintitle").text(),
					mainsubtitle: $("#mainsubtitle").text(),
					boxes: []
				};

				var b = $(".box");

				for (var i = 0; i < b.length; i++) {
					var data_original = b[i].getAttribute("data-original");
					data_original = filter_html(data_original);

					json["boxes"].push(data_original);
				}

				return json;
			}

			async function load_from_json (json) {
				$("#maintitle").text(json.maintitle)
				$("#mainsubtitle").text(json.mainsubtitle)

				$(".container").html("")

				for (var i = 0; i < json.boxes.length; i++) {
					await add_box(json.boxes[i], 0);
				}

				$(".MathJax").css("pointer-events", "none");
			}


			make_editable_simple($("#maintitle")[0]);
			make_editable_simple($("#mainsubtitle")[0]);

			//var div = $(".box")[0];
			//make_editable(div, sample_text);

			const container = document.querySelector(".container");
			const addBoxBtn = document.querySelector("#add-box-btn");

			async function add_box (text, save_json = 1) {
				const box = document.createElement("div");
				box.classList.add("box");
				await make_editable(box, text);
				container.appendChild(box);

				if(save_json) {
					save_current_json();
				}

				await typeset(box);
				$(".MathJax").css("pointer-events", "none");
				return box;
			}

			function save_current_json () {
				var jsonData = get_current_page_json();

				var stringified = JSON.stringify(jsonData);
				var md5_stringified = md5(stringified);

				if(last_saved_hash === null || last_saved_hash != md5_stringified) {
					$.ajax({
						type: "POST",
						url: "storeJson.php",
						data: {
							json: stringified
						},
						success: function(response) {
							history.pushState({}, null, "index.php?id=" + response);
						}
					});
					last_saved_hash = md5_stringified;
				}
			}

			async function add_box_sample_text () {
				return await add_box(sample_text);
			}

			addBoxBtn.addEventListener("click", add_box_sample_text);

			function resize_free_space_according_to_bottom_table () {
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
			$json_dir = "json";

			if(isDocker()) {
				$json_dir = "/poster_generator_json";
			}

			if(isset($_GET["id"]) && preg_match("/^[a-zA-Z0-9]+$/", $_GET["id"]) && file_exists("$json_dir/".$_GET["id"].".json")) {
?>
				var load_from_here = <?php print file_get_contents("$json_dir/".$_GET["id"].".json") ?>;

				//log(load_from_here);

				load_from_json(load_from_here);
<?php
			} else {
?>
				var load_from_here =

				{
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
<?php
			}
?>
		</script>
       </body>
</html>
