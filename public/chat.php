<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>WebSocket</title>
		<style type="text/css">
			html , body {
				font: normal 0.9em arial , helvetica;
			}
			
			#log {
				width: 440px;
				height: 200px;
				border: 1px solid #7F9DB9;
				overflow: auto;
			}
			
			#msg {
				width: 330px;
			}
		</style>
			
		<script type="text/javascript">
			var socket;

			function init () {
			var host = "ws://<?php 
					if(strpos($_SERVER['HTTP_HOST'], ':'))
						echo strstr($_SERVER['HTTP_HOST'], ':', true);
					else
						echo $_SERVER['HTTP_HOST'];
				 ?>:12345/simon";
				
				try {
					// Firefox accept only MozWebSocket
					socket = ("MozWebSocket" in window ? new MozWebSocket (host) : new WebSocket(host));
					log ('WebSocket - status ' + socket.readyState);
					
					socket.onopen = function (msg) {
						log ("Welcome - status " + this.readyState);
					}
					
					socket.onmessage = function (msg) {
						log ("Received: " + msg.data);
					}

					socket.onclose = function (msg) {
						log ("Disconnected - status " + this.readyState); 
					}
				}
				catch (ex) {
					log (ex);
				}
				
				$("msg").focus ();
			}

			function send () {
				var txt, msg;
				
				txt = $("msg");
				msg = txt.value;
				
				if (!msg) {
					alert ("Message can not be empty");
					return;
				}

				txt.value = "";
				txt.focus ();
				
				try {
					socket.send (msg);
					log ('Sent: ' + msg);
				}
				catch (ex) {
					log (ex);
				}
			}
			
			function quit () {
				log ("Goodbye!");
				socket.close ();
				socket = null;
			}

			// Utilities
			function $ (id) {
				return document.getElementById (id);
			}
			
			function log (msg) {
				$("log").innerHTML += "<br>" + msg;
			}
			
			function onkey (event) {
				if (event.keyCode == 13) {
					send();
				}
			}
		</script>
	</head>
	<body onload="init ();">
		<h3>HTML5 WebSocket with Hybi-17 protocol</h3>
		<div id="log"></div>
		<input id="msg" type="textbox" onkeypress="onkey (event);" />
		<button onclick="send ();">Send</button>
		<button onclick="quit ();">Quit</button>
	</body>
</html>
