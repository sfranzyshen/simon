<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Simon Says</title>
	<META NAME="description" CONTENT="Simon says simulator. Enjoy a free memory game in your browser.">
	<META NAME="keywords" CONTENT="game, color, memory, sound">

	<meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0">
  	<meta name="apple-mobile-web-app-capable" content="yes" />

	<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />

	<link rel="shortcut icon" href="images/favicon.ico" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1">
	<script>
		var host = "ws://<?php 
					if(strpos($_SERVER['HTTP_HOST'], ':'))
						echo strstr($_SERVER['HTTP_HOST'], ':', true);
					else
						echo $_SERVER['HTTP_HOST'];
				 ?>:12345/simon";
	</script>
</head>
<body>

	<div id="intro">
		<h1>Simon Says</h1>
		<div class="bodyContent">
			<p>I'll play a melody, and you play it back. Ready?</p>
			<a href="#" id="start" class="button">Start!</a>
			<p class="info visible-desktop">Controls: touch, click, or numeric keys 1 – 4.</p>
		</div>
		
	</div>

	<div id="content">
		<a id="col1"></a>
		<a id="col2"></a>
		<a id="col3"></a>
		<a id="col4"></a>
	</div>

	<div id="endScreen">
		<div class="content">
			<h2>GREAT WORK!</h2>
			Final score: <span id="finalScore">0</span>
			<a href="#" id="reset" class="button">Play Again!</a>
		</div>
	</div>

	<div id="ctrl">
		<div id="score"><span class="title">Score:</span> <span id="scoreNumber">0</span></div>
	</div>

	<script src="js/MIDI.js/js/Widgets/Loader.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/MIDI/AudioDetect.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/MIDI/LoadPlugin.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/MIDI/Plugin.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/MIDI/Player.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/Window/DOMLoader.XMLHttp.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/Window/Event.js" type="text/javascript"></script>
	<script src="js/MIDI.js/js/Window/DOMLoader.script.js" type="text/javascript"></script>
	<script src="js/MIDI.js/inc/WebMIDIAPI.js" type="text/javascript"></script>
	<script src="js/MIDI.js/inc/Base64.js" type="text/javascript"></script>
	<script src="js/MIDI.js/inc/base64binary.js" type="text/javascript"></script>

	
	<script type="text/javascript" src="js/client.js"></script
</body>
</html>
