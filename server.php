<?php  

include "inc/websocket.server.php";


class SimonServer extends WebSocket{

	public $in_game = false;
	public $current_player = NULL;
	public $input = array(1, 2, 3, 4);
	public $LISTEN = false;
	public $SCORE = 0;
	public $RESPONSE = array(); // USER PLAYBACK
	public $PATTERN = array(); // PATTERN TO PLAY
	public $failPattern = array(1, 3, 2, 4);
	public $SPEED = 250;
	public $SPACING = 360;
	public $OFFSET = 700;

	function broadcast($str) {
		foreach ($this->users as $sendto) {
        		$this->send($sendto->socket, $str);
		}
	}

	function inputSingle($el) { 
		if($this->LISTEN) { 
			$this->playSingle($el);
			$this->record($el);
		} 
	}

	function record($el) { 
		if(count($this->PATTERN) >= 1) {
			$this->RESPONSE[] = $el;
			$this->evaluate();
		}
	}

	function evaluate() { // how did the user do?
		$response = implode("", $this->RESPONSE);
		$pattern = implode("", array_slice($this->PATTERN, 0, count($this->RESPONSE)));

		if($response === $pattern && count($this->RESPONSE) === count($this->PATTERN)) {
			$this->LISTEN = false;
			$this->RESPONSE = array();
			$this->success();
		} else if($response !== $pattern) {
			$this->fail();
		}
	}

	function success() { 
		$this->SCORE++;

		foreach ($this->users as $sendto) {
        		$this->send($sendto->socket, "s");
		}
		usleep(($this->SPEED + ($this->SPACING * 2)) * $this->OFFSET );
		$this->playPattern();
	}

	function playPattern() { // playback a pattern
		$this->PATTERN[] = $this->input[array_rand($this->input, 1)];

		$this->SPACING = $this->SPACING - 30;
		$this->SPACING = max($this->SPACING, 60);

		for ($i = 0; $i < count($this->PATTERN); $i++) {
			usleep( ($this->SPACING + $this->SPEED) * $this->OFFSET );
			$this->playSingle($this->PATTERN[$i]);
		}			
		usleep($this->SPEED * $this->OFFSET);
		$this->LISTEN = true;
	}

	function fail() {
		usleep($this->SPACING * $this->OFFSET);

		foreach ($this->users as $sendto) {
			if($sendto->id === $this->current_player) {
	        		$this->send($sendto->socket, "f1");
			} else {
				$this->send($sendto->socket, "f");
			}
		}

		for ($i = 0; $i < count($this->failPattern); $i++) {
			$this->playSingle($this->failPattern[$i]);
			usleep(floor($this->SPEED * .7) * $this->OFFSET);
		}

		$this->in_game = false;
		$this->current_player = NULL;
	}

	function setDefault() { // set default values
		$this->LISTEN = false;
		$this->SCORE = 0;
		$this->SPACING = 360;
		$this->SPEED = 250;
		$this->RESPONSE = array();
		$this->PATTERN = array();
		$this->in_game = false;
		$this->current_player = NULL;
	}

	function reset($user) { // start/restart game
		if($this->in_game) return; // shouldn't be here
		$this->broadcast("r");
		$this->setDefault();
		$this->current_player = $user->id;
		$this->in_game = true;
		$this->playPattern();
	}

	function playSingle($el) { // play a color/note
		$this->broadcast($el);
		usleep($this->SPEED * $this->OFFSET);
	}

	function dohandshake($user,$buffer){
		$this->log("\nRequesting handshake...");
		$this->log($buffer);
		list($resource,$host,$origin,$key1,$key2,$l8b,$key0) = $this->getheaders($buffer);
		$this->log("Handshaking...");
		$upgrade  = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" .
			"Upgrade: WebSocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Sec-WebSocket-Origin: " . $origin . "\r\n" .
			"Sec-WebSocket-Accept: " .  $this->calcKeyHybi10($key0) . "\r\n" . "\r\n" ;

		socket_write($user->socket,$upgrade,strlen($upgrade));
		$user->handshake=true;
		$this->log($upgrade);
		$this->log("Done handshaking...");
		if($this->in_game) {
			$this->send($user->socket,"w" . $SCORE);
		} else {
			$this->send($user->socket,"f");
		}
		return true;
	}

	function process($user,$msg){
		$cleanstring = $this->filterUnicode($msg);
		if($cleanstring === "") return;

		if($cleanstring === "r") $this->reset($user);
		if($cleanstring === "1" || $cleanstring === "2" || $cleanstring === "3" || $cleanstring === "4") {
			if($user->id === $this->current_player)	$this->inputSingle($cleanstring);
		}
	}

	function filterUnicode ($str) {
		$str = preg_replace('/[\x0\x00\x00-\x1F\x80-\xFF]/', '', $str);
		return $str;
	}

}

$master = new SimonServer("0.0.0.0",12345);
