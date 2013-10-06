<h2> Simon </h2>
This project is based on the top rated HTML5 <a href="http://uxmonk.com/">“Simon Says”</a> game by Daniel Christopher. It was originally featured on the <a href="http://www.chromeexperiments.com/detail/simon-says/">Chrome Experiments Web Site.</a> <br>

While this game is addictive and fun to play, it only allows for a single player experience. Sure, you and your freinds can connect to Daniel's site and each play your own individual games of "Simon Says" and then compare your individual scores. But it's still not much fun to do with a group of people. So I set out to create a version of the game that could be shared with many people all at the same time. In more of a collabrative "Group Experience". The idea is that many clients connect to the server and interact with a single running version of the game simutaniously. Only one person gets control of the game at a time but all the other people are able to observe the game play. To make things a little more "Group Experience" I have also included a client to drive a 4 color led controller (based on arduino) to brighten things up.

Watch a screen capture of two browser windows each connected to the server <a href="http://www.youtube.com/watch?v=HwCa9by7AK4">Here on Youtube.com</a>

I split Daniel's code into a game server and a web-browser client that communicate between themselves using WebSockets. The client side of things is basiclly a stripped down version of Daniel's excellent code. The server side of things is another story. The first thing you will notice is that the server side code is php. I originally started this project using nodejs ... but switched to php because of size limitations. My deployment platform is a dlink router (with usb) and nodejs was not an option. (I may at some point port this over to nodejs as well anyway. But for now ...) My idea is to have the dlink router function as the AP (access point) for the wireless devices and the game server code. This way I will be able to reduce the delay between the server and each connected player and try to minimize any sync issues between the various players. 

While WebSockets are fun ... this setup doesn't scale very well. The more connected devices the slower it goes. Somewhere around 5-6 connected players starts to screw with the timing.

<h2>How to use it:</h2>

  Again, this is intended to be run from the same machine as the connected clients (like a wireless router running openwrt) but should work over a local wireless network as well. I wouldn't try to use it over the internet.
  
  to start the web server:
  <code>php-cli -S 0.0.0.0:80 -t public/ router.php &</code>
  
  to start the websocket server:
  <code>php-cli server.php &</code>
  
  to start the websocket led-controller client:
  <code>php-cli led-client.php &</code>
  
  connect web browser to:
  <code>http://yourip/</code>
  
  <h2> Notes: </h2>
  I have a dlink/openwrt router setup without an internet connection. A wifi network setup with the ssid set as "Simon" and no password.  I added hostnames for Simon & simon to point to 192.168.0.1 (the routers ip), and I added "address=/#/192.168.0.1" to the bottom of /etc/dnsmasq.conf file. This will cause any wifi connected device to only connect to the server. So, all the players need to do is connect to the "Simon" wifi router and open a web browser (chrome or firefox ...) and type in anything ... and they will be connected to the game. I also have an arduino uno with a tlc5940 circut connected to the usb port of the router driving 4 RGB LED amps -> 4 RGB lamps.
  
  
<pre>
Server:
README                    This file
server.php                Websocket Game Server
router.php                php router file directs all request to index.php
led-client.php            Websocket Game "RGB LED Controller" Client

Client:
public/js/client.js       Websocket Game "Web Javascipt" Client
public/css/style.css      "			"
public/images/favicon.ico "			"
public/index.php          "			"

Dependancies:(batteries included)
public/js/MIDI.js/*       <a href="http://mudcu.be/midi-js/">http://mudcu.be/midi-js/</a>
inc/serial.class.php      <a href="http://code.google.com/p/php-serial/">http://code.google.com/p/php-serial/</a>	(*modified)
inc/websocket.client.php  <a href="https://github.com/Devristo/phpws">https://github.com/Devristo/phpws</a>
inc/websocket.server.php  <a href="https://github.com/nicokaiser/php-websocket">https://github.com/nicokaiser/php-websocket</a>

LED Controller:	
arduino/firmware.ino arduino (c) firmware code
</pre>
