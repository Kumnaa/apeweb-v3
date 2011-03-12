<?php

/*
  Socket class

  @author Ben Bowtell

  @date 27-Feb-2011

  (c) 2011 by http://www.amplifycreative.net

  contact: ben@amplifycreative.net.net

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class socket_class {

    private $socket;
    private $buffer;
    public $socket_end;
    public $socket_error;

    //create the old socket
    function create($ip, $port, $timeout_s = 5, $timeout_m = 0, $debug = FALSE) {
        if ($debug == true) {
            echo config::ip() . "\n";
        }
        $this->buffer = array();
        $this->socket_end = FALSE;
        $this->socket_error = FALSE;
        if ($debug == true) {
            echo "Timeout sec: " . $timeout_s . " Timeout usec: " . $timeout_m . "\n";
        }
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $timeout_s, "usec" => $timeout_m));
        if ($debug == true) {
            echo "Bind to " . IP_ADDRESS . "\n";
        }
        socket_bind($this->socket, 0);
        if ($debug == true) {
            echo "Connect to " . $ip . ":" . $port . "\n";
        }
        if ($socket = socket_connect($this->socket, $ip, $port)) {
            $return = TRUE;
        } else {
            $return = FALSE;
        }
        return($return);
    }

    //these functions are pretty self explanatory
    function read($bytes = 1024, $debug = FALSE) {
        /* $data = socket_read($this->socket, $bytes, PHP_BINARY_READ);
          if ($data === '') */
        if (@socket_recv($this->socket, $data, $bytes, MSG_WAITALL) == false) {
            $this->socket_end = TRUE;
            $this->socket_error = TRUE;
            if ($debug == true) {
                echo "Error\n\n";
            }
        }
        return ($data);
    }

    //adds data to the buffer
    function add_buffer($_data) {
        array_push($this->buffer, str_replace("&#039;", "'", html_entity_decode(utf8_decode($_data))));
    }

    //writes the buffer
    function write($debug = FALSE) {
        // if send isn't empty add it to the buffer
        while (count($this->buffer) > 0) {
            $this->buffer = array_reverse($this->buffer);
            $r_line = array_pop($this->buffer);
            $this->buffer = array_reverse($this->buffer);
            if ($debug == true) {
                echo "Writing buffer:\n\n";
            }
            socket_write($this->socket, $r_line, strlen($r_line));
            if ($debug == true) {
                echo "Sent:" . $r_line . "\n\n";
            }
        }
    }

    function close() {
        if ($this->socket) {
            socket_close($this->socket);
        }
        $this->socket_end = TRUE;
    }
}

?>
