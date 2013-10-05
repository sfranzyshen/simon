<?php

// start of websocket.functions.php
// mamta
class HixieKey {

    public $number;
    public $key;

    public function __construct($number, $key) {
        $this->number = $number;
        $this->key = $key;
    }

}

class WebSocketProtocolVersions {

    const HIXIE_76 = 0;
    const HYBI_8 = 8;
    const HYBI_9 = 8;
    const HYBI_10 = 8;
    const HYBI_11 = 8;
    const HYBI_12 = 8;
    const LATEST = self::HYBI_12;

    private function __construct() {
        
    }

}

class WebSocketFunctions {

    /**
     * Parse a HTTP HEADER 'Cookie:' value into a key-value pair array
     *
     * @param string $line Value of the COOKIE header
     * @return array Key-value pair array
     */
    public static function cookie_parse($line) {
        $cookies = array();
        $csplit = explode(';', $line);
        $cdata = array();

        foreach ($csplit as $data) {

            $cinfo = explode('=', $data);
            $key = trim($cinfo[0]);
            $val = urldecode($cinfo[1]);

            $cookies[$key] = $val;
        }

        return $cookies;
    }

    public static function writeWholeBuffer($fp, $string) {
        for ($written = 0; $written < strlen($string); $written += $fwrite) {
            $fwrite = fwrite($fp, substr($string, $written));
            if ($fwrite === false) {
                return $written;
            }
        }
        return $written;
    }

    public static function readWholeBuffer($resource) {
        $buffer = '';
        $buffsize = 8192;

        $metadata['unread_bytes'] = 0;

        do {
            if (feof($resource)) {
                return false;
            }

            $result = fread($resource, $buffsize);
            if ($result === false) {
                return false;
            }
            $buffer .= $result;

            $metadata = stream_get_meta_data($resource);

            $buffsize = min($buffsize, $metadata['unread_bytes']);
        } while ($metadata['unread_bytes'] > 0);

        return $buffer;
    }

    /**
     * Parse HTTP request into an array
     *
     * @param string $header HTTP request as a string
     * @return array Headers as a key-value pair array
     */
    public static function parseHeaders($header) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function($m) {return strtoupper($m[0]); }, strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }

        if (preg_match("/GET (.*) HTTP/", $header, $match)) {
            $retVal['GET'] = $match[1];
        }

        return $retVal;
    }

    public static function calcHybiResponse($challenge) {
        return base64_encode(sha1($challenge . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
    }

    /**
     * Calculate the #76 draft key based on the 2 challenges from the client and the last 8 bytes of the request
     *
     * @param string $key1 Sec-WebSocket-Key1
     * @param string $key2 Sec-Websocket-Key2
     * @param string $l8b Last 8 bytes of the client's opening handshake
     */
    public static function calcHixieResponse($key1, $key2, $l8b) {
        // Get the numbers from the opening handshake
        $numbers1 = preg_replace("/[^0-9]/", "", $key1);
        $numbers2 = preg_replace("/[^0-9]/", "", $key2);

        //Count spaces
        $spaces1 = substr_count($key1, " ");
        $spaces2 = substr_count($key2, " ");

        if ($spaces1 == 0 || $spaces2 == 0) {
            throw new WebSocketInvalidKeyException($key1, $key2, $l8b);
            return null;
        }

        // Key is the number divided by the amount of spaces expressed as a big-endian 32 bit integer
        $key1_sec = pack("N", $numbers1 / $spaces1);
        $key2_sec = pack("N", $numbers2 / $spaces2);

        // The response is the md5-hash of the 2 keys and the last 8 bytes of the opening handshake, expressed as a binary string
        return md5($key1_sec . $key2_sec . $l8b, 1);
    }

    public static function randHybiKey() {
        return base64_encode(
                chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255))
                . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255))
                . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255))
                . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255))
        );
    }

    /**
     * Output a line to stdout
     *
     * @param string $msg Message to output to the STDOUT
     */
    public static function say($msg = "") {
        echo date("Y-m-d H:i:s") . " | " . $msg . "\n";
    }

    // mamta
    public static function genKey3() {
        return "" . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255))
                . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255)) . chr(rand(0, 255));
    }

    public static function randHixieKey() {
        $_MAX_INTEGER = (1 << 32) - 1;
        #$_AVAILABLE_KEY_CHARS = range(0x21, 0x2f + 1) + range(0x3a, 0x7e + 1);
        #$_MAX_CHAR_BYTE = (1<<8) -1;
        # $spaces_n = 2;
        $spaces_n = rand(1, 12); // random.randint(1, 12)
        $max_n = $_MAX_INTEGER / $spaces_n;
        # $number_n = 123456789;
        $number_n = rand(0, $max_n); // random.randint(0, max_n)
        $product_n = $number_n * $spaces_n;
        $key_n = "" . $product_n;
        # $range = 3; //
        $range = rand(1, 12);
        for ($i = 0; $i < $range; $i++) {
            #i in range(random.randint(1, 12)):
            if (rand(0, 1) > 0) {
                $c = chr(rand(0x21, 0x2f + 1)); #random.choice(_AVAILABLE_KEY_CHARS)
            } else {
                $c = chr(rand(0x3a, 0x7e + 1)); #random.choice(_AVAILABLE_KEY_CHARS)
            }
            # $c = chr(65);
            $len = strlen($key_n);
            # $pos = 2;
            $pos = rand(0, $len);
            $key_n1 = substr($key_n, 0, $pos);
            $key_n2 = substr($key_n, $pos);
            $key_n = $key_n1 . $c . $key_n2;
        }
        for ($i = 0; $i < $spaces_n; $i++) {
            $len = strlen($key_n);
            # $pos = 2;
            $pos = rand(1, $len - 1);
            $key_n1 = substr($key_n, 0, $pos);
            $key_n2 = substr($key_n, $pos);
            $key_n = $key_n1 . " " . $key_n2;
        }

        return new HixieKey($number_n, $key_n);
    }

}
// end of websocket.functions.php
// start of websocket.exceptions.php
class WebSocketMessageNotFinalised extends Exception {

    public function __construct(IWebSocketMessage $msg) {
        parent::__construct("WebSocketMessage is not finalised!");
    }

}

class WebSocketFrameSizeMismatch extends Exception {

    public function __construct(IWebSocketFrame $msg) {
        parent::__construct("Frame size mismatches with the expected frame size. Maybe a buggy client.");
    }

}

class WebSocketInvalidChallengeResponse extends Exception {

    public function __construct() {
        parent::__construct("Server send an incorrect response to the clients challenge!");
    }

}

class WebSocketInvalidUrlScheme extends Exception {

    public function __construct() {
        parent::__construct("Only 'ws://' urls are supported!");
    }

}
class WebSocketInvalidKeyException extends Exception {

    public function __construct($key1, $key2, $l8b) {
        parent::__construct("Client sent an invalid opening handshake!");
        fwrite(STDERR, "Key 1: \t$key1\nKey 2: \t$key2\nL8b: \t$l8b");
    }

}
// end of websocket.exceptions.php
// start of websocket.framing.php
/**
 * Enum-like construct containing all opcodes defined in the WebSocket protocol

 * @author Chris
 *
 */
class WebSocketOpcode {

    const __default = 0;
    const ContinuationFrame = 0x00;
    const TextFrame = 0x01;
    const BinaryFrame = 0x02;
    const CloseFrame = 0x08;
    const PingFrame = 0x09;
    const PongFrame = 0x09;

    private function __construct() {
        
    }

    /**
     * Check if a opcode is a control frame. Control frames should be handled internally by the server.
     * @param int $type
     */
    public static function isControlFrame($type) {
        $controlframes = array(self::CloseFrame, self::PingFrame, self::PongFrame);

        return array_search($type, $controlframes) !== false;
    }

}

/**
 * Interface for WebSocket frames. One or more frames compose a message.
 * In the case of the Hixie protocol, a message contains of one frame only
 *
 * @author Chris
 */
interface IWebSocketFrame {

    /**
     * Serialize the frame so that it can be send over a socket
     * @return string Serialized binary string
     */
    public function encode();

    /**
     * Deserialize a binary string into a IWebSocketFrame
     * @param $string
     * @param null $head
     * @return string Serialized binary string
     */
    public static function decode(&$string, $head = null);

    /**
     * @return string Payload Data inside the frame
     */
    public function getData();

    /**
     * @return int The frame type (opcode)
     */
    public function getType();

    /**
     * Create a frame by type and payload data
     * @param int $type
     * @param string $data
     *
     * @return IWebSocketFrame
     */
    public static function create($type, $data = null);
}

/**
 * HYBIE WebSocketFrame
 *
 * @author Chris
 *
 */
class WebSocketFrame implements IWebSocketFrame {

    // First Byte
    protected $FIN = 0;
    protected $RSV1 = 0;
    protected $RSV2 = 0;
    protected $RSV3 = 0;
    protected $opcode = WebSocketOpcode::TextFrame;
    // Second Byte
    protected $mask = 0;
    protected $payloadLength = 0;
    protected $maskingKey = 0;
    protected $payloadData = '';
    protected $actualLength = 0;

    private function __construct() {
        
    }

    public static function create($type, $data = null) {
        $o = new self();

        $o->FIN = true;
        $o->payloadData = $data;
        $o->payloadLength = $data != null ? strlen($data) : 0;
        $o->setType($type);

        return $o;
    }

    public function setMasked($mask) {
        $this->mask = $mask ? 1 : 0;
    }

    public function isMasked() {
        return $this->mask == 1;
    }

    protected function setType($type) {
        $this->opcode = $type;

        if ($type == WebSocketOpcode::CloseFrame)
            $this->mask = 1;
    }

    protected static function IsBitSet($byte, $pos) {
        return ($byte & pow(2, $pos)) > 0 ? 1 : 0;
    }

    protected static function rotMask($data, $key, $offset = 0) {
        $res = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $j = ($i + $offset) % 4;
            $res .= chr(ord($data[$i]) ^ ord($key[$j]));
        }

        return $res;
    }

    public function getType() {
        return $this->opcode;
    }

    public function encode() {
        $this->payloadLength = strlen($this->payloadData);

        $firstByte = $this->opcode;

        $firstByte += $this->FIN * 128 + $this->RSV1 * 64 + $this->RSV2 * 32 + $this->RSV3 * 16;

        $encoded = chr($firstByte);

        if ($this->payloadLength <= 125) {
            $secondByte = $this->payloadLength;
            $secondByte += $this->mask * 128;

            $encoded .= chr($secondByte);
        } else if ($this->payloadLength <= 255 * 255 - 1) {
            $secondByte = 126;
            $secondByte += $this->mask * 128;

            $encoded .= chr($secondByte) . pack("n", $this->payloadLength);
        } else {
            // TODO: max length is now 32 bits instead of 64 !!!!!
            $secondByte = 127;
            $secondByte += $this->mask * 128;

            $encoded .= chr($secondByte);
            $encoded .= pack("N", 0);
            $encoded .= pack("N", $this->payloadLength);
        }

        $key = 0;
        if ($this->mask) {
            $key = pack("N", rand(0, pow(255, 4) - 1));
            $encoded .= $key;
        }

        if ($this->payloadData)
            $encoded .= ($this->mask == 1) ? $this->rotMask($this->payloadData, $key) : $this->payloadData;

        return $encoded;
    }

    public static function decode(&$raw, $head = null) {
        if ($head != null) {
            $frame = $head;
        } else {
            $frame = new self();

            // Read the first two bytes, then chop them off
            list($firstByte, $secondByte) = substr($raw, 0, 2);
            $raw = substr($raw, 2);

            $firstByte = ord($firstByte);
            $secondByte = ord($secondByte);

            $frame->FIN = self::IsBitSet($firstByte, 7);
            $frame->RSV1 = self::IsBitSet($firstByte, 6);
            $frame->RSV2 = self::IsBitSet($firstByte, 5);
            $frame->RSV3 = self::IsBitSet($firstByte, 4);

            $frame->mask = self::IsBitSet($secondByte, 7);

            $frame->opcode = ($firstByte & 0x0F);

            $len = $secondByte & ~128;

            if ($len <= 125)
                $frame->payloadLength = $len;
            elseif ($len == 126) {
                $arr = unpack("nfirst", $raw);
                $frame->payloadLength = array_pop($arr);
                $raw = substr($raw, 2);
            } elseif ($len == 127) {
                list(, $h, $l) = unpack('N2', $raw);
                $frame->payloadLength = ($l + ($h * 0x0100000000));
                $raw = substr($raw, 8);
            }

            if ($frame->mask) {
                $frame->maskingKey = substr($raw, 0, 4);
                $raw = substr($raw, 4);
            }
        }

        $currentOffset = $frame->actualLength;
        $fullLength = min($frame->payloadLength - $frame->actualLength, strlen($raw));
        $frame->actualLength += $fullLength;

        if ($fullLength < strlen($raw)) {
            $frameData = substr($raw, 0, $fullLength);
            $raw = substr($raw, $fullLength);
        } else {
            $frameData = $raw;
            $raw = '';
        }

        if ($frame->mask)
            $frame->payloadData .= self::rotMask($frameData, $frame->maskingKey, $currentOffset);
        else
            $frame->payloadData .= $frameData;

        return $frame;
    }

    public function isReady() {
        if ($this->actualLength > $this->payloadLength) {
            throw new WebSocketFrameSizeMismatch($this);
        }
        return ($this->actualLength == $this->payloadLength);
    }

    public function isFinal() {
        return $this->FIN == 1;
    }

    public function getData() {
        return $this->payloadData;
    }

}

class WebSocketFrame76 implements IWebSocketFrame {

    public $payloadData = '';
    protected $opcode = WebSocketOpcode::TextFrame;

    public static function create($type, $data = null) {
        $o = new self();

        $o->payloadData = $data;

        return $o;
    }

    public function encode() {
        return chr(0) . $this->payloadData . chr(255);
    }

    public function getData() {
        return $this->payloadData;
    }

    public function getType() {
        return $this->opcode;
    }

    public static function decode(&$str, $head = null) {
        $o = new self();
        $o->payloadData = substr($str, 1, strlen($str) - 2);

        return $o;
    }

}
// end of websocket.framing.php
// start of websocket.message.php
/**
 * 
 * Interface for incoming and outgoing messages
 * @author Chris
 *
 */
interface IWebSocketMessage {

    /**
     * Retreive an array of frames of which this message is composed
     * 
     * @return WebSocketFrame[]
     */
    public function getFrames();

    /**
     * Set the body of the message 
     * This should recompile the array of frames
     * @param string $data
     */
    public function setData($data);

    /**
     * Retreive the body of the message
     * @return string
     */
    public function getData();

    /**
     * Create a new message
     * @param string $data Content of the message to be created
     */
    public static function create($data);

    /**
     * Check if we have received the last frame of the message
     *  
     * @return bool
     */
    public function isFinalised();

    /**
     * Create a message from it's first frame
     * @param IWebSocketFrame $frame
     * @throws Exception
     */
    public static function fromFrame(IWebSocketFrame $frame);
}

/**
 * WebSocketMessage compatible with the Hixie Draft #76
 * Used for backwards compatibility with older versions of Chrome and
 * several Flash fallback solutions
 * 
 * @author Chris
 */
class WebSocketMessage76 implements IWebSocketMessage {

    protected $data = '';
    protected $frame = null;

    public static function create($data) {
        $o = new self();

        $o->setData($data);
        return $o;
    }

    public function getFrames() {
        $arr = array();

        $arr[] = $this->frame;

        return $arr;
    }

    public function setData($data) {
        $this->data = $data;
        $this->frame = WebSocketFrame76::create(WebSocketOpcode::TextFrame, $data);
    }

    public function getData() {
        return $this->frame->getData();
    }

    public function isFinalised() {
        return true;
    }

    /**
     * Creates a new WebSocketMessage76 from a IWebSocketFrame
     * @param IWebSocketFrame $frame
     * 
     * @return WebSocketMessage76 Message composed of the frame provided
     */
    public static function fromFrame(IWebSocketFrame $frame) {
        $o = new self();
        $o->frame = $frame;

        return $o;
    }

}

/**
 * WebSocketMessage compatible with the latest draft.
 * Should be updated to keep up with the latest changes.
 * 
 * @author Chris
 *
 */
class WebSocketMessage implements IWebSocketMessage {

    /**
     * 
     * Enter description here ...
     * @var WebSocketFrame[];
     */
    protected $frames = array();
    protected $data = '';

    public function setData($data) {
        $this->data = $data;

        $this->createFrames();
    }

    public static function create($data) {
        $o = new self();

        $o->setData($data);
        return $o;
    }

    public function getData() {
        if ($this->isFinalised() == false)
            throw new WebSocketMessageNotFinalised($this);

        $data = '';

        foreach ($this->frames as $frame) {
            $data .= $frame->getData();
        }

        return $data;
    }

    public static function fromFrame(IWebSocketFrame $frame) {
        $o = new self();
        $o->takeFrame($frame);

        return $o;
    }

    protected function createFrames() {
        $this->frames = array(WebSocketFrame::create(WebSocketOpcode::TextFrame, $this->data));
    }

    public function getFrames() {
        return $this->frames;
    }

    public function isFinalised() {
        if (count($this->frames) == 0)
            return false;

        return $this->frames[count($this->frames) - 1]->isFinal();
    }

    /**
     * Append a frame to the message
     * @param \WebSocketFrame $frame
     */
    public function takeFrame(WebSocketFrame $frame) {
        $this->frames[] = $frame;
    }

}
// end of websocket.message.php
// start of websocket.resources.php
interface IWebSocketUriHandler {

    public function addConnection(IWebSocketConnection $user);

    public function removeConnection(IWebSocketConnection $user);

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg);

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg);

    public function setServer(WebSocketServer $server);

    public function getConnections();
}

abstract class WebSocketUriHandler implements IWebSocketUriHandler {

    /**
     *
     * Enter description here ...
     * @var SplObjectStorage
     */
    protected $users;

    /**
     *
     * Enter description here ...
     * @var WebSocketServer
     */
    protected $server;

    public function __construct() {
        $this->users = new SplObjectStorage();
    }

    public function addConnection(IWebSocketConnection $user) {
        $this->users->attach($user);
    }

    public function removeConnection(IWebSocketConnection $user) {
        $this->users->detach($user);
        $this->onDisconnect($user);
    }

    public function setServer(WebSocketServer $server) {
        $this->server = $server;
    }

    public function say($msg = '') {
        return $this->server->say($msg);
    }

    public function send(IWebSocketConnection $client, $str) {
        return $client->sendString($str);
    }

    public function onDisconnect(IWebSocketConnection $user) {
        
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
        
    }

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
        
    }

    //abstract public function onMessage(WebSocketUser $user, IWebSocketMessage $msg);

    public function getConnections() {
        return $this->users;
    }

}
// end of websocket.resources.php
// start of websocket.protocol.php
class WebSocketConnectionFactory {

    public static function fromSocketData(WebSocketSocket $socket, $data) {
        $headers = WebSocketFunctions::parseHeaders($data);

        if (isset($headers['Sec-Websocket-Key1'])) {
            $s = new WebSocketConnectionHixie($socket, $headers, $data);
            $s->sendHandshakeResponse();
        } else if (strpos($data, '<policy-file-request/>') === 0) {
            $s = new WebSocketConnectionFlash($socket, $data);
        } else {
            $s = new WebSocketConnectionHybi($socket, $headers);
            $s->sendHandshakeResponse();
        }

        $s->setRole(WebSocketConnectionRole::SERVER);


        return $s;
    }

}

class WebSocketConnectionRole {

    const CLIENT = 0;
    const SERVER = 1;

}

interface IWebSocketConnection {

    public function sendHandshakeResponse();

    public function setRole($role);

    public function readFrame($data);

    public function sendFrame(IWebSocketFrame $frame);

    public function sendMessage(IWebSocketMessage $msg);

    public function sendString($msg);

    public function getHeaders();

    public function getUriRequested();

    public function getCookies();

    public function getIp();

    public function disconnect();
}

abstract class WebSocketConnection implements IWebSocketConnection {

    protected $_headers = array();

    /**
     *
     * @var WebSocketSocket
     */
    protected $_socket = null;
    protected $_cookies = array();
    public $parameters = null;
    protected $_role = WebSocketConnectionRole::CLIENT;

    public function __construct(WebSocketSocket $socket, array $headers) {
        $this->setHeaders($headers);
        $this->_socket = $socket;
    }

    public function getIp() {
        return stream_socket_get_name($this->_socket->getResource(), true);
    }

    public function getId() {
        return (int) $this->_socket->getResource();
    }

    public function sendFrame(IWebSocketFrame $frame) {
        if ($this->_socket->write($frame->encode()) === false)
            return FALSE;
    }

    public function sendMessage(IWebSocketMessage $msg) {
        foreach ($msg->getFrames() as $frame) {
            if ($this->sendFrame($frame) === false)
                return FALSE;
        }

        return TRUE;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function setHeaders($headers) {
        $this->_headers = $headers;

        if (array_key_exists('Cookie', $this->_headers) && is_array($this->_headers['Cookie'])) {
            $this->cookie = array();
        } else {
            if (array_key_exists("Cookie", $this->_headers)) {
                $this->_cookies = WebSocketFunctions::cookie_parse($this->_headers['Cookie']);
            }
            else
                $this->_cookies = array();
        }

        $this->getQueryParts();
    }

    public function getCookies() {
        return $this->_cookies;
    }

    public function getUriRequested() {
        if (array_key_exists('GET', $this->_headers))
            return $this->_headers['GET'];
        else
            return null;
    }

    public function setRole($role) {
        $this->_role = $role;
    }

    protected function getQueryParts() {
        $url = $this->getUriRequested();

        // We dont have an URL to process (this is the case for the client)
        if ($url == null)
            return;

        if (($pos = strpos($url, "?")) == -1) {
            $this->parameters = array();
        }

        $q = substr($url, strpos($url, "?") + 1);

        $kvpairs = explode("&", $q);
        $this->parameters = array();

        foreach ($kvpairs as $kv) {
            if (strpos($kv, "=") == -1)
                continue;

            @list($k, $v) = explode("=", $kv);

            $this->parameters[urldecode($k)] = urldecode($v);
        }
    }

    public function getAdminKey() {
        return isset($this->_headers['Admin-Key']) ? $this->_headers['Admin-Key'] : null;
    }

    public function getSocket() {
        return $this->_socket;
    }

}

class WebSocketConnectionFlash extends WebSocketConnection {

    public function __construct($socket, $data) {
        $this->_socket = $socket;
        $this->_socket->onFlashXMLRequest($this);
    }

    public function sendString($msg) {
        $this->_socket->write($msg);
    }

    public function disconnect() {
        $this->_socket->disconnect();
    }

    public function sendHandshakeResponse()
    {
        throw new Exception("Not supported!");
    }

    public function readFrame($data)
    {
        throw new Exception("Not supported!");
    }
}

class WebSocketConnectionHybi extends WebSocketConnection {

    private $_openMessage = null;
    private $lastFrame = null;

    public function sendHandshakeResponse() {
        // Check for newer handshake
        $challenge = isset($this->_headers['Sec-Websocket-Key']) ? $this->_headers['Sec-Websocket-Key'] : null;

        // Build response
        $response = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n";

        // Build HYBI response
        $response .= "Sec-WebSocket-Accept: " . WebSocketFunctions::calcHybiResponse($challenge) . "\r\n\r\n";

        $this->_socket->write($response);

        WebSocketFunctions::say("HYBI Response SENT!");
    }

    public function readFrame($data) {
        $frames = array();
        while (!empty($data)) {
            $frame = WebSocketFrame::decode($data, $this->lastFrame);
            if ($frame->isReady()) {

                if (WebSocketOpcode::isControlFrame($frame->getType()))
                    $this->processControlFrame($frame);
                else
                    $this->processMessageFrame($frame);

                $this->lastFrame = null;
            } else {
                $this->lastFrame = $frame;
            }

            $frames[] = $frame;
        }

        return $frames;
    }

    public function sendFrame(IWebSocketFrame $frame) {
        /**
         * @var WebSocketFrame
         */
        $hybiFrame = $frame;

        // Mask IFF client!
        $hybiFrame->setMasked($this->_role == WebSocketConnectionRole::CLIENT);

        parent::sendFrame($hybiFrame);
    }

    /**
     * Process a Message Frame
     *
     * Appends or creates a new message and attaches it to the user sending it.
     *
     * When the last frame of a message is received, the message is sent for processing to the
     * abstract WebSocket::onMessage() method.
     *
     * @param WebSocketFrame $frame
     */
    protected function processMessageFrame(WebSocketFrame $frame) {
        if ($this->_openMessage && $this->_openMessage->isFinalised() == false) {
            $this->_openMessage->takeFrame($frame);
        } else {
            $this->_openMessage = WebSocketMessage::fromFrame($frame);
        }

        if ($this->_openMessage && $this->_openMessage->isFinalised()) {
            $this->_socket->onMessage($this->_openMessage);
            $this->_openMessage = null;
        }
    }

    /**
     * Handle incoming control frames
     *
     * Sends Pong on Ping and closes the connection after a Close request.
     *
     * @param WebSocketFrame $frame
     */
    protected function processControlFrame(WebSocketFrame $frame) {
        switch ($frame->getType()) {
            case WebSocketOpcode::CloseFrame :
                $frame = WebSocketFrame::create(WebSocketOpcode::CloseFrame);
                $this->sendFrame($frame);

                $this->_socket->disconnect();
                break;
            case WebSocketOpcode::PingFrame :
                $frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
                $this->sendFrame($frame);
                break;
        }
    }

    public function sendString($msg) {
        try {
            $m = WebSocketMessage::create($msg);

            return $this->sendMessage($m);
        } catch (Exception $e) {
            $this->disconnect();
        }
    }

    public function disconnect() {
        $f = WebSocketFrame::create(WebSocketOpcode::CloseFrame);
        $this->sendFrame($f);

        $this->_socket->disconnect();
    }

}

class WebSocketConnectionHixie extends WebSocketConnection {

    private $_clientHandshake;

    public function __construct(WebSocketSocket $socket, array $headers, $clientHandshake) {
        $this->_clientHandshake = $clientHandshake;
        parent::__construct($socket, $headers);
    }

    public function sendHandshakeResponse() {
        // Last 8 bytes of the client's handshake are used for key calculation later
        $l8b = substr($this->_clientHandshake, -8);

        // Check for 2-key based handshake (Hixie protocol draft)
        $key1 = isset($this->_headers['Sec-Websocket-Key1']) ? $this->_headers['Sec-Websocket-Key1'] : null;
        $key2 = isset($this->_headers['Sec-Websocket-Key2']) ? $this->_headers['Sec-Websocket-Key2'] : null;

        // Origin checking (TODO)
        $origin = isset($this->_headers['Origin']) ? $this->_headers['Origin'] : null;
        $host = $this->_headers['Host'];
        $location = $this->_headers['GET'];

        // Build response
        $response = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n";

        // Build HIXIE response
        $response .= "Sec-WebSocket-Origin: $origin\r\n" . "Sec-WebSocket-Location: ws://{$host}$location\r\n";
        $response .= "\r\n" . WebSocketFunctions::calcHixieResponse($key1, $key2, $l8b);

        $this->_socket->write($response);
        echo "HIXIE Response SENT!";
    }

    public function readFrame($data) {
        $f = WebSocketFrame76::decode($data);
        $m = WebSocketMessage76::fromFrame($f);

        $this->_socket->onMessage($m);

        return array($f);
    }

    public function sendString($msg) {
        $m = WebSocketMessage76::create($msg);

        return $this->sendMessage($m);
    }

    public function disconnect() {
        $this->_socket->disconnect();
    }

}
// end of websocket.protocol.php
// start of websocket.socket.php
interface WebSocketObserver {

    public function onDisconnect(WebSocketSocket $s);

    public function onConnectionEstablished(WebSocketSocket $s);

    public function onMessage(IWebSocketConnection $s, IWebSocketMessage $msg);

    public function onFlashXMLRequest(WebSocketConnectionFlash $connection);
}

class WebSocketSocket {

    private $_socket = null;
    private $_protocol = null;

    /**
     *
     * @var IWebSocketConnection
     */
    private $_connection = null;
    private $_writeBuffer = '';
    private $_lastChanged = null;
    private $_disconnecting = false;
    private $_immediateWrite = false;

    /**
     *
     * Enter description here ...
     * @var WebSocketObserver[]
     */
    private $_observers = array();

    public function __construct(WebSocketObserver $server, $socket, $immediateWrite = false) {
        $this->_socket = $socket;
        $this->_lastChanged = time();
        $this->_immediateWrite = $immediateWrite;

        $this->addObserver($server);
    }

    public function onData($data) {
        try {
            $this->_lastChanged = time();

            if ($this->_connection)
                $this->_connection->readFrame($data);
            else
                $this->establishConnection($data);
        } catch (Exception $e) {
            $this->disconnect();
        }
    }

    public function setConnection(IWebSocketConnection $con) {
        $this->_connection = $con;
    }

    public function onMessage(IWebSocketMessage $m) {
        foreach ($this->_observers as $observer) {
            $observer->onMessage($this->getConnection(), $m);
        }
    }

    public function establishConnection($data) {
        $this->_connection = WebSocketConnectionFactory::fromSocketData($this, $data);

        if ($this->_connection instanceof WebSocketConnectionFlash)
            return;

        foreach ($this->_observers as $observer) {
            $observer->onConnectionEstablished($this);
        }
    }

    public function write($data) {
        $this->_writeBuffer .= $data;

        if ($this->_immediateWrite == true) {
            while ($this->_writeBuffer != '')
                $this->mayWrite();
        }
    }

    public function mustWrite() {
        return strlen($this->_writeBuffer);
    }

    public function mayWrite() {
        if (strlen($this->_writeBuffer) > 4096) {
            $buff = substr($this->_writeBuffer, 0, 4096);
            $this->_writeBuffer = strlen($buff) > 0 ? substr($this->_writeBuffer, 4096) : '';
        } else {
            $buff = $this->_writeBuffer;
            $this->_writeBuffer = '';
        }


        if (WebSocketFunctions::writeWholeBuffer($this->_socket, $buff) == false) {
            $this->close();
        }

        if (strlen($this->_writeBuffer) == 0 && $this->isClosing())
            $this->close();
    }

    public function getLastChanged() {
        return $this->_lastChanged;
    }

    public function onFlashXMLRequest(WebSocketConnectionFlash $connection) {
        foreach ($this->_observers as $observer) {
            $observer->onFlashXMLRequest($connection);
        }
    }

    public function disconnect() {
        $this->_disconnecting = true;

        if ($this->_writeBuffer == '')
            $this->close();
    }

    public function isClosing() {
        return $this->_disconnecting;
    }

    public function close() {
        fclose($this->_socket);
        foreach ($this->_observers as $observer) {
            $observer->onDisconnect($this);
        }
    }

    public function getResource() {
        return $this->_socket;
    }

    /**
     *
     * @return IWebSocketConnection
     */
    public function getConnection() {
        return $this->_connection;
    }

    public function addObserver(WebSocketObserver $s) {
        $this->_observers[] = $s;
    }

}
// end websocket.socket.php

class WebSocket implements WebSocketObserver {

    protected $socket;
    protected $handshakeChallenge;
    protected $hixieKey1;
    protected $hixieKey2;
    protected $host;
    protected $port;
    protected $origin;
    protected $requestUri;
    protected $url;
    protected $hybi;
    protected $_frames = array();
    protected $_messages = array();
    protected $_head = '';
    protected $_timeOut = 1;

    // mamta
    public function __construct($url, $useHybie = true) {
        $this->hybi = $useHybie;
        $parts = parse_url($url);

        $this->url = $url;

        if (in_array($parts['scheme'], array('ws', 'wss')) === false)
            throw new WebSocketInvalidUrlScheme();

        $this->scheme = $parts['scheme'];

        $this->host = $parts['host'];
        $this->port = $parts['port'];

        $this->origin = 'http://' . $this->host;

        if (isset($parts['path']))
            $this->requestUri = $parts['path'];
        else
            $this->requestUri = "/";

        if (isset($parts['query']))
            $this->requestUri .= "?" . $parts['query'];

        // mamta
        if ($useHybie) {
            $this->buildHeaderArray();
        } else {
            $this->buildHeaderArrayHixie76();
        }
    }

    public function onDisconnect(WebSocketSocket $s) {
        
    }

    public function onConnectionEstablished(WebSocketSocket $s) {
        
    }

    public function onMessage(IWebSocketConnection $s, IWebSocketMessage $msg) {
        $this->_messages[] = $msg;
    }

    public function onFlashXMLRequest(WebSocketConnectionFlash $connection) {
        
    }

    public function setTimeOut($seconds) {
        $this->_timeOut = $seconds;
    }

    public function getTimeOut() {
        return $this->_timeOut;
    }

    /**
     * TODO: Proper header generation!
     * TODO: Check server response!
     */
    public function open() {
        $errno = $errstr = null;

        $protocol = $this->scheme == 'ws' ? "tcp" : "ssl";

        $this->socket = stream_socket_client("$protocol://{$this->host}:{$this->port}", $errno, $errstr, $this->getTimeOut());
        // socket_connect($this->socket, $this->host, $this->port);

        $buffer = $this->serializeHeaders();

        fwrite($this->socket, $buffer, strlen($buffer));

        // wait for response
        $buffer = WebSocketFunctions::readWholeBuffer($this->socket);
        $headers = WebSocketFunctions::parseHeaders($buffer);

        $s = new WebSocketSocket($this, $this->socket, $immediateWrite = true);

        if ($this->hybi)
            $this->_connection = new WebSocketConnectionHybi($s, $headers);
        else
            $this->_connection = new WebSocketConnectionHixie($s, $headers, $buffer);

        $s->setConnection($this->_connection);

        return true;
    }

    private function serializeHeaders() {
        $str = '';

        foreach ($this->headers as $k => $v) {
            $str .= $k . " " . $v . "\r\n";
        }
        # mamta add key 3 needed for the handshake/swithching protocol compatible with glassfish
        $key3 = WebSocketFunctions::genKey3();
        $str .= "\r\n" . $key3;

        return $str;
    }

    public function addHeader($key, $value) {
        $this->headers[$key . ":"] = $value;
    }

    protected function buildHeaderArray() {
        $this->handshakeChallenge = WebSocketFunctions::randHybiKey();

        $this->headers = array("GET" => "{$this->url} HTTP/1.1", "Connection:" => "Upgrade", "Host:" => "{$this->host}:{$this->port}", "Sec-WebSocket-Key:" => "{$this->handshakeChallenge}", "Sec-WebSocket-Origin:" => "{$this->origin}", "Sec-WebSocket-Version:" => 8, "Upgrade:" => "websocket");

        return $this->headers;
    }

    # mamta: hixie 76

    protected function buildHeaderArrayHixie76() {
        $this->hixieKey1 = WebSocketFunctions::randHixieKey();
        $this->hixieKey2 = WebSocketFunctions::randHixieKey();
        $this->headers = array("GET" => "{$this->url} HTTP/1.1", "Connection:" => "Upgrade", "Host:" => "{$this->host}:{$this->port}", "Origin:" => "{$this->origin}", "Sec-WebSocket-Key1:" => "{$this->hixieKey1->key}", "Sec-WebSocket-Key2:" => "{$this->hixieKey2->key}", "Upgrade:" => "websocket", "Sec-WebSocket-Protocol: " => "hiwavenet");

        return $this->headers;
    }

    public function send($string) {
        $this->_connection->sendString($string);
    }

    public function sendMessage($msg) {
        $this->_connection->sendMessage($msg);
    }

    public function sendFrame(IWebSocketFrame $frame) {
        $this->_connection->sendFrame($frame);
    }

    /**
     * @return WebSocketFrame
     */
    public function readFrame() {
        $buffer = WebSocketFunctions::readWholeBuffer($this->socket);

        $this->_frames = array_merge($this->_frames, $this->_connection->readFrame($buffer));

        return array_shift($this->_frames);
    }

    /**
     * 
     * @return IWebSocketMessage
     */
    public function readMessage() {
        while (count($this->_messages) == 0)
            $this->readFrame();



        return array_shift($this->_messages);
    }

    public function close() {
        /**
         * @var WebSocketFrame
         */
        $frame = null;
        $this->sendFrame(WebSocketFrame::create(WebSocketOpcode::CloseFrame));

        $i = 0;
        do {
            $i++;
            $frame = @$this->readFrame();
        } while ($i < 2 && $frame && $frame->getType() == WebSocketOpcode::CloseFrame);

        @fclose($this->socket);
    }

}
