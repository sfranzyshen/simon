<?php

	/*
		Simon Says - Websocket LED Controller Game Client
		led.php is a php deamon that connects to a (Simon Says) websocket server, opens a serial connection with a led controller,
		listens for messages, and turns on/off the led lights in accordance with the game play.
		sfranzyshen@facebook.com
	*/

	require_once("inc/websocket.client.php");
	include "inc/serial.class.php";

	//$input = "Hello World!";
	//$msg = WebSocketMessage::create($input);

	$client = new WebSocket("ws://localhost:12345/simon"); //runs on the same machine as the game server ...
	$client->open();
	//$client->sendMessage($msg);

	$serial = new phpSerial;
	$serial->deviceSet("/dev/ttyACM0");
	$serial->confBaudRate(115200);
	$serial->confParity("none");
	$serial->confCharacterLength(8);
	$serial->confStopBits(1);
	$serial->deviceOpen();

	$speed = 250000;

	$theResult = '';

	while ( $theResult != "Connected") {
	        $read = $serial->readPort();
 	       if ($read != '') {
	                $theResult .= $read;
	        }
	}

	echo $theResult . "\n";

	$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 10, 4);
	$serial->sendMessage($message, 0);

	$theResult = '';

	while(($theResult !== "21") && ($theResult !== "6")) {
		$read = $serial->readPort();
		if ($read != '') {
			$theResult .= $read;
		}
	}
	
	if($theResult == "21") {
		exit("something went wrong ...\n");
	}

	while(true) {
		// Wait for an incoming message
		$msg = $client->readMessage();

		//$client->close();
		$input = $msg->getData();

		if($input === "1") {
			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 255, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}

			if($theResult == "21") {
				exit("something went wrong ...\n");
			}

			usleep($speed);

			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}
	
			if($theResult == "21") {
				exit("something went wrong ...\n");
			}
		}

		if($input === "2") {
			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 255, 255, 255, 0, 10, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}

			if($theResult == "21") {
				exit("something went wrong ...\n");
			}

			usleep($speed);

			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}
	
			if($theResult == "21") {
				exit("something went wrong ...\n");
			}
		}

		if($input === "3") {
			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 255, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}

			if($theResult == "21") {
				exit("something went wrong ...\n");
			}

			usleep($speed);

			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}
	
			if($theResult == "21") {
				exit("something went wrong ...\n");
			}
		}

		if($input === "4") {
			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 255, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}

			if($theResult == "21") {
				exit("something went wrong ...\n");
			}

			usleep($speed);

			$message = sprintf("%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c%c", 1, 0, 0, 255, 10, 0, 255, 0, 10, 255, 255, 0, 10, 255, 0, 0, 10, 4);
			$serial->sendMessage($message, 0);

			$theResult = '';

			while(($theResult !== "21") && ($theResult !== "6")) {
				$read = $serial->readPort();
				if ($read != '') {
					$theResult .= $read;
				}
			}
	
			if($theResult == "21") {
				exit("something went wrong ...\n");
			}
		}

		echo $msg->getData(); // Prints "Hello World!" when using the demo.php server
		echo ("\n");
	}
?>

