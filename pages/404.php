<!DOCTYPE html>
<html lang='en'>
<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Error - The Rock Keeps Rolling</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
	<style>
		body {
			font-family: Arial, sans-serif;
			background-color: #f0f0f0;
			display: flex;
			justify-content: center;
			align-items: center;
			height: 100vh;
			margin: 0;
		}

		.error-container {
			text-align: center;
			background-color: #fff;
			padding: 20px;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0,0,0,0.1);
		}

		.sisyphus-container {
            position: relative;
            width: 600px;
            height: 200px;
            margin: 2rem auto;

			overflow: hidden;
        }

        .hill {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 50%;
            background: linear-gradient(to top right, #7f8c8d 50%, transparent 51%);
        }

        .rock {
            position: absolute;
            font-size: 3rem;
            animation: roll-down 3s infinite linear;
			animation-timing-function: cubic-bezier(0.55, 0.085, 0.68, 0.53);
            transform-origin: 50% 50%;
        }

        @keyframes roll-down {
            0% {
                top: 30%;
				left: 0;
                transform: rotate(0deg);
            }
            100% {
                top: 80%;
				left: 100%;
                transform: rotate(560deg);
            }
        }

		button {
			background-color: #56af31;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 2px;
			cursor: pointer;
		}

		button:hover {
			background-color: #1275ab;
			transform: translateY(-2px);
		}

		h1 {
			font-size:4rem;
			color: #e4615c;
		}
	</style>

    <div class="error-container">
        <h1>404 Error</h1>
        <p>Like Sisyphus and his eternal task, we keep pushing this website uphill...</p>
		<p>...only to watch it roll back down.</p>
		<div class="sisyphus-container">
			<div class="hill"></div>
            <div class="rock">🪨</div>
        </div>
		<p>Just like Sisyphus, we're pushing to keep our website running, but sometimes it feels like the rock just rolls back down.</p>
		<br>
        <p>Try searching for what you're looking for or check back later.</p>
        <button onclick="window.location.href='/'">Back up the Hill</button>
    </div>
</body>
</html>
