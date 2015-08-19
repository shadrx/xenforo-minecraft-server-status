<?php
class ServerListPingException extends Exception{};

class HeroDev_MinecraftStatus_Helper_ServerListPingHelper{

    public function pingServer($address, $port = 25565, $timeout = 3){
        $socket = @fsockopen($address, $port, $errno, $errstr, $timeout);

        if($errno || $socket === false){
            throw new ServerListPingException('There was a problem while trying to connect to: '.$address.':'.$port);
        }

        //Send Ping Packet to server
        fwrite($socket, "\xFE\x01\xFA"
            .$this->_encodeString("MC|PingHost")
            .pack('n', (7 + 2*strlen($address)))
            .pack('c', 74)
            .$this->_encodeString($address)
            .pack('N', $port));

        stream_set_timeout($socket, $timeout);

        //Get the ping response from the server
        $response = fread($socket, 4096);

        //Throw an exception if we have a empty response
        if(empty($response)) throw new ServerListPingException("Invalid response from server!");

        //If we didn't receive a kick packet from the server, then we have an invalid response.
        if($response[0] != "\xFF"){  //0xFF = Kick Packet
            throw new ServerListPingException("Invalid response from server!");
        }

        //The next two bytes of the response will be the length of the packet
        $length = unpack('n', $response[1].$response[2]);

        $status = explode("\0", $this->_decodeString((substr($response, 3, ($length[1]*2)))));

        //Remove unwanted data
        unset($status[0]);
        unset($status[1]);

        $keys = array("version", "motd", "numplayers", "maxplayers");

        $status = array_combine($keys, $status);

        $status['motd'] = $this->formatMotd($status['motd']);

        return $status;
    }

    /**
     * Converts a string to UTF-16BE and packs it ready to send
     * @param $string
     */
    protected function _encodeString($string){
        return pack('n', strlen($string)).mb_convert_encoding($string, "UTF-16BE");
    }


    /**
     * Converts a string to back to UTF-8
     * @param $string
     */
    protected function _decodeString($string){
        return mb_convert_encoding($string, "UTF-8", "UTF-16BE");
    }


    protected function formatMotd($motd){
        return preg_replace_callback('/§(\d+)([^§]*)/s',
            function($match)
            {
                $color = '';
                switch($match[1]) {
                    case '0':
                        $color = '000000';
                        break;
                    case '1':
                        $color = '0000AA';
                        break;
                    case '2':
                        $color = '00AA00';
                        break;
                    case '3':
                        $color = '00AAAA';
                        break;
                    case '4':
                        $color = 'AA0000';
                        break;
                    case '5':
                        $color = 'AA00AA';
                        break;
                    case '6':
                        $color = 'FFAA00';
                        break;
                    case '7':
                        $color = 'AAAAAA';
                        break;
                    case '8':
                        $color = '555555';
                        break;
                    case '9':
                        $color = '5555FF';
                        break;
                    case 'a':
                        $color = '55FF55';
                        break;
                    case 'b':
                        $color = '55FFFF';
                        break;
                    case 'c':
                        $color = 'FF5555';
                        break;
                    case 'd':
                        $color = 'FF55FF';
                        break;
                    case 'e':
                        $color = 'FFFF55';
                        break;
                    case 'f':
                        $color = '000000';
                        break;
                    default:
                        break;
                }
                return "<span style='color:#" . $color .";'>" . $match[2] . "</span>";
            },
            $motd);
    }

}