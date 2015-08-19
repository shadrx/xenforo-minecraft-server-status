<?php
class GS4QueryException extends Exception
{
}

/**
 * Class MinecraftStat
 * Author Joseph Bennett (shadrxninga)
 *
 * Queries Minecraft servers using the query protocol built into the game.
 * Information on the protocol was found:
 *   - http://wiki.vg/Query
 *   - http://dinnerbone.com/blog/2011/10/14/minecraft-19-has-rcon-and-query/
 *
 * Originally made for a Xenforo Add-on I created, but feel free to use it elsewhere if you want to.
 */
class HeroDev_MinecraftStatus_Helper_GS4QueryHelper
{

    const PACKET_TYPE_CHALLENGE = 9;
    const PACKET_TYPE_QUERY = 0;

    private $_magicPrefix;

    private $_sessionId = 2;

    private $_socket;

    private $_debug = false;

    private $_errors = array();

    public function __construct()
    {
        $this->_magicPrefix = "\xFE"."\xFD";
        $this->_sessionId = pack("N", $this->_sessionId);
    }

    /**
     * Sets up the connection so you can query a Minecraft server.
     *
     * @param $address
     * @param int|number $port
     * @param int|number $timeout
     * @throws GS4QueryException
     */
    public function connect($address, $port = 25565, $timeout = 3)
    {
        set_error_handler(array('HeroDev_MinecraftStatus_Helper_GS4QueryHelper', 'handlePhpError'));

        $this->_socket = @fsockopen("udp://" . $address, $port, $errno, $errstr, $timeout);

        if($errno || $this->_socket === false){
            throw new GS4QueryException('There was a problem while trying to connect to: '.$address.':'.$port);
        }
        //Set the timeout for reading and writing data over the socket, as the timeout paramater in fsockopen only applies
        //while connecting to the socket
        stream_set_timeout($this->_socket, $timeout);
    }


    /**
     * Requests a short status from the server.
     *
     * Will return throw an exception if unable to retrieve status.
     *
     * @throws GS4QueryException
     */
    public function getShortStatus()
    {
        //Get a challenge token from the server
        $challengeToken = $this->_getChallenge();

        //Send a query request (it will give us back a shorter result as we don't send out session id again)
        $this->_sendPacket(self::PACKET_TYPE_QUERY, $challengeToken);

        //Get the query result back from the server
        $shortStatus = fread($this->_socket, 4096);

        //Cleanup data and put into array
        //TODO Make this as clean as possible

        $keys = array("motd", "gametype", "map", "numplayers", "maxplayers", "port", "hostip");

        //Strip unwanted data at beginning of packet.
        $shortStatus = substr($shortStatus, 5);

        //Split up into array!
        $shortStatus = explode("\x00" , $shortStatus, 6);

        $shortStatus = array_merge($shortStatus, unpack("vp/A*h", $shortStatus[5]));

        unset($shortStatus[5]);

        $shortStatus = array_combine($keys, $shortStatus);

        return $shortStatus;
    }


    /**
     * Requests a longer status from the server. Includes more information such as a player list.
     *
     * Will throw an exception if unable to retrieve status.
     *
     * @return array|bool $longStatus
     */
    public function getLongStatus()
    {
        $challengeToken = $this->_getChallenge();

        //Send session id again after challenge token to get the long stats
        $this->_sendPacket(self::PACKET_TYPE_QUERY, $challengeToken . $this->_sessionId);

        //Get the query result back from the server
        $longStatus = fread($this->_socket, 4096);

        //Cleanup the data received and put it into an array!

        //Strip unwanted data at beginning of packet.
        $longStatus = substr($longStatus, 5);

        //Split into two parts. The Key-Value Information section and the Player List
        $longStatus = explode("\x00\x01player_\x00\x00", $longStatus);

        //Cleaning up Information Section

        //Key-Value pairs are null terminated strings. This regex separates them into groups matching the keys
        //to their values
        $re = '/([^\x00]+)\x00([^\x00]+)/';
        $matches = array();

        preg_match_all($re, $longStatus[0], $matches);

        //Creates an array out of the key values pairs the regex found
        $serverInfo = array_combine($matches[1], $matches[2]);

        //Why does Notch think a hostname is the motd....?

        if(array_key_exists("hostname", $serverInfo)){
            $serverInfo['motd'] = $serverInfo['hostname'];
            unset($serverInfo['hostname']);
        }


        //Cleaning up Player List
        if(strlen($longStatus[1]) > 2){ //Player list is false if no players are online
            //Remove the two trailing null characters
            $longStatus[1] = substr($longStatus[1], 0, -2);
            //Separate the players
            $playerList = explode("\x00", $longStatus[1]);
            $longStatus  = array_merge($serverInfo, array("playerList" => $playerList));
        }else{
            $longStatus = $serverInfo;
        }

        $longStatus['motd'] = $this->_decodeString($longStatus['motd']);

        return $longStatus;
    }

    /**
     * Start handshake with server. This function returns the challenge token from the server that we need to keep
     * to make further requests. The server expires all challenge tokens every 30 seconds. It also seems that tokens can only be used once.
     * @return bool|string challengeToken
     */
    private function _getChallenge()
    {
        $this->_sendPacket(self::PACKET_TYPE_CHALLENGE);

        $response = fread($this->_socket, 256);



        //TODO Find better way of doing this...
        $steam_data = stream_get_meta_data($this->_socket);

        //The server took too long to reply, i.e timeout. We cannot get the challenge and therefore can't continue
        if ($steam_data ['timed_out']) {
            throw new GS4QueryException("Timed out while trying to read challenge from socket");
        }

        //Throw an exception if we have a empty response
        if(empty($response)) throw new  GS4QueryException("Received empty challenge response from server");

        if ($response[0] != "\x09") {
            throw new GS4QueryException("Received invalid challenge response from server");
        }

        $type = unpack("C", $response[0]);
        $sessionId = unpack("N", substr($response, 1, 4));
        $challengeToken = substr($response, 5, -1);

        //Give back challenge token as a 32bit Unsigned Long
        return pack("N", $challengeToken);
    }

    private function _sendRaw($data)
    {
       $success =  fwrite($this->_socket, $this->_magicPrefix . $data);
       if($success === false){
           throw new GS4QueryException('Could not write to socket'.error_get_last());
       }
    }

    private function _sendPacket($type, $data = '')
    {
        $this->_sendRaw(pack("C", $type) . $this->_sessionId . $data);
    }

    /**
     * Converts a string to back to UTF-8
     * @param $string
     */
    protected function _decodeString($string){
        return mb_convert_encoding($string, "UTF-8");
    }

    /**
     * Handler for set_error_handler to convert notices, warnings, and other errors
     * into exceptions. We want to pick this up before XenForo does so we can make some
     * of the errors more meaningful! We'll then pass it on.
     *
     * @param integer $errorType Type of error (one of the E_* constants)
     * @param string $errorString
     * @param string $file
     * @param integer $line
     */
    public static function handlePhpError($errorType, $errorString, $file, $line){


        XenForo_Application::handlePhpError($errorType, $errorString, $file, $line);
    }

}