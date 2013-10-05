
	/*
		Simon Says - Websocket Game (web) Client
		client.js is a javascript websocket game client that connects using a web browser to the game server
		sfranzyshen@facebook.com
	*/

(function() { "use strict";
	// disable mobile safari "bounce"
	document.addEventListener('touchmove', function(e){ e.preventDefault(); }, false);

	// REMOVE BLANK CHARS FROM BEGINNING AND END OF STRING
	String.prototype.trim = function () {
		return this.replace(/^\s*(\S*(\s+\S+)*)\s*$/, "$1");
	};

	var Simon = function() {
		var SELF = this,
			INPUTS = document.getElementById('content').getElementsByTagName('a'),
			SPEED = 250,
			SPACING = 360,
			SCORE = 0,
			NOTES = [70, 74, 75, 77],
			CTRL = document.getElementById('ctrl'),
			SCOREKEEPER = document.getElementById('scoreNumber'), // CONTROL BAR
			socket;

		this.init = function() {

			// Firefox accept only MozWebSocket
			socket = ("MozWebSocket" in window ? new MozWebSocket (host) : new WebSocket(host));
					
			socket.onopen = function (msg) {
				//log ("Welcome - status " + this.readyState);
			}
					
			socket.onmessage = function (msg) {
				if(msg.data === "1" || msg.data === "2" || msg.data === "3" || msg.data === "4") {
					// note recieved
					var el = INPUTS[ msg.data -1];
					SELF.playSingle(el);
				}

				if(msg.data === "r") {
					// reset recieved
					SELF.setDefault();
					document.getElementById('endScreen').className = '';
					document.getElementById('intro').className = '';
				}

				if(msg.data.substr(0, 1) === "f") {
					// failed recieved
					SPEED = 175;
					if(msg.data.substr(1, 1) === "1") {
						document.getElementById('endScreen').className = 'active';
						document.getElementById('finalScore').innerHTML = SCORE;
					} else {
						document.getElementById('intro').className = 'active';
					}
				}

				if(msg.data.substr(0, 1) === "s") {
					// score recieved

					if(msg.data.length > 1) {
						SCORE = msg.data.substr(1);
					} else {
						SCORE++;
					}
 					CTRL.className = 'active';
		 			SCOREKEEPER.innerHTML = SCORE;
		 			setTimeout( function() { CTRL.className = ''; }, SPEED + ( SPACING * 2 ));
				}
	
				if(msg.data.substr(0, 1) === "w") {
					// welcome recieved (+ current score)
					if(msg.data.length > 1) {
						SCORE = msg.data.substr(1);
					}
				}
			}

			socket.onclose = function (msg) {
				//log ("Disconnected - status " + this.readyState); 
			}

			var reset = document.getElementById('reset'),
			    start = document.getElementById('start');

			// connect color to sound
			for (var i = 0; i < INPUTS.length; i++) {
				Event.add(INPUTS[i], 'mousedown', function(event) { 
					var code = event.target.id.replace('col','');
					socket.send(code);
				} );
			}

			document.getElementById('intro').className = '';

			reset.onclick = function() { return false };
			start.onclick = function() { return false };

			Event.add(reset, 'click', function(event) {
				socket.send('r');
			});

			Event.add(start, 'click',  function(event) {
				socket.send('r'); 
			});

			// add keypress events
			Event.add(window, 'keydown', function(event) {
				var code = event.keyCode - 49;
				if(code >= 48) code -= 48; // adjust for 10-key pad
				if(code >= 0 && code <= 3) {
					socket.send(code +1);	
				}
				
			});
		}

		this.setDefault = function() { // set default values
			SCORE = 0;
			SPACING = 360;
			SPEED = 250;
		}

		this.playSingle = function (el) { // play a color/note
			var note = el.id.replace('col','') - 1;
			el.className = 'active';
			MIDI.noteOn(0, NOTES[note], 127, 0);
			setTimeout(function() { // turn off color
				MIDI.noteOff(0, note, 0);
				el.className = '';
			}, SPEED);
		}
	}

	MIDI.loadPlugin({
		soundfontUrl: "./js/MIDI.js/soundfont/",
		instrument: "acoustic_grand_piano",
		callback: function() {
			var simonSays = new Simon;
			simonSays.init();
			MIDI.loader.stop();
		}
	});
	
	Event.add("body", "ready", function() {
		MIDI.loader = new widgets.Loader({
			message: "Loading Simon Says",
			bars: 20,
			lineWidth: 5,
			lineHeight: 25
		});
	});

})();
