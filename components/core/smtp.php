<?php

/*
  SMTP class

  @author Ben Bowtell

  @date 27-Feb-2011

  (c) 2011 by http://www.amplifycreative.net

  contact: ben@amplifycreative.net.net

  ﻿   This program is free software: you can redistribute it and/or modify
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

class smtp_class {

    private $headers;
    public $to;
    public $from;
    public $from_name;
    public $subject;
    public $body;
    public $eol = "\r\n";

    private function format_email() {
        $this->headers = '';
        $this->headers .= "Date: " . date('D, j M Y H:i:s O') . $this->eol;
        $this->headers .= "Subject: " . $this->subject . $this->eol;
        $this->headers .= "From: " . $this->from . $this->eol;
        $this->headers .= "To: " . $this->to . $this->eol;
        $this->headers .= "User-Agent: Apeweb mail" . $this->eol;
        $this->headers .= "MIME-Version: 1.0" . $this->eol;
        $this->headers .= "Content-type: text/html;charset=iso-8859-1" . $this->eol;
        $this->headers .= "Content-Transfer-Encoding: 8bit" . $this->eol;
        $this->headers .= $this->eol;
        $this->body .= $this->eol . '.' . $this->eol;
    }

    public function send() {
        $authing = FALSE;
        $contents = '';
        $output = '';
        $return = FALSE;
        $this->format_email();
        if (defined(config::smtp_server())) {
            $socket = new socket_class();
            if ($socket->create(gethostbyname(config::smtp_server()), config::smtp_port()) === FALSE) {
                throw new Exception('Connection could not be made');
            } else {
                $time[microtime()] = 'Connection made';
                do {
                    $data = $socket->read('1', $debug);
                    $contents .= $data;
                } while (strstr($contents, "\r\n") === FALSE && $socket->socket_end === FALSE);
                $output .= $contents;
                // if connection accepted
                if (substr($contents, 0, 3) == '220') {
                    $time[microtime()] = 'EHLO being sent';
                    $contents = '';
                    $outout .= 'EHLO mail.apetechnologies.net' . $this->eol;
                    $socket->add_buffer('EHLO mail.apetechnologies.net' . $this->eol);
                    $socket->write();
                    do {
                        $data = $socket->read('1', $debug);
                        $contents .= $data;
                        if (strstr($contents, 'AUTH=PLAIN LOGIN') != FALSE || strstr($contents, 'AUTH PLAIN LOGIN') != FALSE) {
                            $authing = TRUE;
                        }
                    } while (preg_match("/250 (.*?)\n/m", $contents) == 0 && $socket->socket_end === FALSE);
                    $output .= $contents;
                    if ($authing === true) {
                        $time[microtime()] = 'Authing starting';
                        $contents = '';
                        $auth_text = base64_encode(config::smtp_login() . "\0" . config::smtp_login() . "\0" . config::smtp_password());
                        $output .= 'AUTH PLAIN ' . $auth_text . $this->eol;
                        $socket->add_buffer('AUTH PLAIN ' . $auth_text . $this->eol);
                        $socket->write();
                        do {
                            $data = $socket->read('1', $debug);
                            $contents .= $data;
                        } while (strstr($contents, "\r\n") === FALSE && $socket->socket_end === FALSE);
                        $output .= $contents;
                        //sender ok?
                        if (substr($contents, 0, 3) == '235') {
                            $access = TRUE;
                        } else {
                            $access = FALSE;
                        }
                    } else {
                        $time[microtime()] = 'Authing not required';
                        if (substr($contents, 0, 3) == '250') {
                            $access = TRUE;
                        } else {
                            $access = FALSE;
                        }
                    }
                    if ($access === true) {
                        //server ok?
                        $time[microtime()] = 'Sending first header MAIL FROM';
                        $contents = '';
                        $output .= 'MAIL FROM:<' . $this->from . '>' . $this->eol;
                        $socket->add_buffer('MAIL FROM:<' . $this->from . '>' . $this->eol);
                        $socket->write();
                        do {
                            $data = $socket->read('1', $debug);
                            $contents .= $data;
                        } while (strstr($contents, "\r\n") === FALSE && $socket->socket_end === FALSE);
                        $output .= $contents;
                        //sender ok?
                        if (substr($contents, 0, 3) == '250') {
                            $time[microtime()] = 'Sending second header RCPT TO';
                            $contents = '';
                            $output .= 'RCPT TO:<' . $this->to . '>' . $this->eol;
                            $socket->add_buffer('RCPT TO:<' . $this->to . '>' . $this->eol);
                            $socket->write();
                            do {
                                $data = $socket->read('1', $debug);
                                $contents .= $data;
                            } while (strstr($contents, "\r\n") === FALSE && $socket->socket_end === FALSE);
                            $output .= $contents;
                            //recipient ok?
                            if (substr($contents, 0, 3) == '250') {
                                $time[microtime()] = 'Start sending DATA';
                                $contents = '';
                                $output .= 'DATA' . $this->eol;
                                $socket->add_buffer('DATA' . $this->eol);
                                $socket->write();
                                do {
                                    $data = $socket->read('1', $debug);
                                    $contents .= $data;
                                } while (strstr($contents, "\r\n") === FALSE && $socket->socket_end === FALSE);
                                $output .= $contents;
                                //DATA ok?
                                if (substr($contents, 0, 3) == '354') {
                                    $time[microtime()] = 'Start sending headers + message';
                                    $contents = '';
                                    $output .= $this->headers . $this->body;
                                    $socket->add_buffer($this->headers);
                                    $socket->add_buffer($this->body);
                                    $socket->write();
                                    do {
                                        $data = $socket->read('1', $debug);
                                        $contents .= $data;
                                    } while (strstr($contents, "\r\n") === FALSE && $socket->socket_end === FALSE);
                                    $output .= $contents;
                                    if (substr($contents, 0, 3) == '250') {
                                        $output .= 'QUIT' . $this->eol;
                                        $socket->add_buffer('QUIT' . $this->eol);
                                        $socket->write();
                                        $socket->close();
                                    } else {
                                        $return = $contents;
                                    }
                                    $time[microtime()] = 'Connection ended';
                                } else {
                                    $return = $contents;
                                }
                            } else {
                                $return = $contents;
                            }
                        } else {
                            $return = $contents;
                        }
                    } else {
                        $return = $contents;
                    }
                } else {
                    $return = $contents;
                }
            }
        } else {
            if (mail($this->to, $this->subject, $this->body, $this->headers, '-f ' . $this->from) === false) {
                throw new Exception("Error sending: " . $this->body . " to " . $this->to . " with " . $this->subject . " as the subject.");
            }
        }
        return ($return);
    }

}

?>
