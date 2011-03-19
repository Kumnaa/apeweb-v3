<?php

/*
  Core methods

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

class apetech {

    public static function query_string () {
        if (isset($_SERVER['QUERY_STRING'])) {
            return $_SERVER['QUERY_STRING'];
        } else {
            return 'cgi-script';
        }
    }
    
    public static function server_name() {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        } else {
            return 'cgi-script';
        }
    }
    
    public static function server_port() {
        if (isset($_SERVER['SERVER_PORT'])) {
            return $_SERVER['SERVER_PORT'];
        } else {
            return 'cgi-script';
        }
    }
    
    public static function server_uri() {
                    if (isset($_SERVER['REQUEST_URI'])) {
                return $_SERVER['REQUEST_URI'];
            } else {
                return 'cgi-script';
            }
    }
    
    public static function form_to_email($recipient, $subject, $exceptions = array()) {
        if (isset($_POST) && is_array($_POST) && is_array($exceptions)) {
            $string = '';
            foreach ($_POST AS $key => $value) {
                if (array_search($key, $exceptions) == false) {
                    $string .= '<b>' . html::clean_text(ucwords(str_replace(array("_", "-"), " ", $key))) . '</b>:<br />' . html::clean_text($value) . '<br /><br />';
                }
            }
            apetech::send_mail($recipient, $subject, $string, config::smtp_sender(), config::smtp_sender());
        } else {
            throw new Exception("Invalid arguments.");
        }
    }

    public static function error_handler($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_USER_ERROR:
                echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
                break;

            case E_USER_WARNING:
                echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
                break;

            case E_USER_NOTICE:
                echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
                break;

            default:
                echo "Unknown error type: [$errno] $errstr<br />\n";
                break;
        }
        
        echo "  on line $errline in file $errfile";
        debug_backtrace();
        /* Don't execute PHP internal error handler */
        exit(1);
    }

    public static function send_mail($to, $subject, $msg, $fromaddress, $fromname) {
        $msg = '<html><body>' . $msg . '</body></html>';
        $smtp = new smtp_class();
        $smtp->from = $fromaddress;
        $smtp->from_name = $fromname;
        $smtp->to = $to;
        $smtp->subject = $subject;
        $smtp->body = $msg;
        return $smtp->send();
    }

    // mail errors
    public static function error_email($_data) {
        $error = '';
        if (is_array($_data)) {
            $error = implode("\n", $_data);
        } else {
            $error = $_data;
        }
        self::send_mail(config::$error_destination, config::domain() . ' information', $error, config::$error_destination, 'Server Admin');
    }

    public static function random_numeric($length = 8) {
        //Generate the random string
        $chars = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        $textstr = '';
        for ($i = 0; $i < $length; $i++) {
            $textstr .= $chars[rand(0, count($chars) - 1)];
        }

        return ($textstr);
    }

    public static function random_string($length = 8) {
        //Generate the random string
        $chars = array("a", "A", "b", "B", "c", "C", "d", "D", "e", "E", "f", "F", "g", "G",
            "h", "H", "i", "I", "j", "J", "k",
            "K", "l", "L", "m", "M", "n", "N", "o", "O", "p", "P", "q", "Q", "r",
            "R", "s", "S", "t", "T", "u", "U", "v",
            "V", "w", "W", "x", "X", "y", "Y", "z", "Z", "1", "2", "3", "4", "5",
            "6", "7", "8", "9");
        $textstr = '';
        for ($i = 0; $i < $length; $i++) {
            $textstr .= $chars[rand(0, count($chars) - 1)];
        }
        return ($textstr);
    }

    public static function decode_ip($int_ip) {
        $hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
        if (sizeof($hexipbang) != 5) {
            $hexipbang = array('0', '0', '0', '0');
        }
        return hexdec($hexipbang[0]) . '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
    }

    public static function encode_ip($dotquad_ip) {
        $ip_sep = explode('.', $dotquad_ip);
        if (sizeof($ip_sep) != 4) {
            $ip_sep = array('0', '0', '0', '0');
        }
        return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
    }

    public static function time_lapsed($units, $time) {
        if ($units === null) {
            $time = time() - $time;
            if ($time < 60) {
                return $time . " seconds ago";
            }
            $time = $time / 60;
            if ($time < 60) {
                return floor($time) . " minutes ago";
            }
            $time = $time / 60;
            if ($time < 24) {
                return floor($time) . " hours ago";
            }
            $time = $time / 24;
            if ($time < 7) {
                return floor($time) . " days ago";
            }
            $time = $time / 7;
            if ($time < 52) {
                return floor($time) . " weeks ago";
            }
            $time = $time / 52;
            return floor($time) . " years ago";
        } else {
            switch ($units) {
                case "minutes":
                    return (round((time() - $time) / 60, 0));
                    break;
            }
        }
    }

    public static function first_day_of_month($datetime) {
        $month = $datetime->format("n");
        $year = $datetime->format("Y");
        $date = new DateTime($year . "-" . $month . "-1");
        return $date;
    }

    public static function first_day_of_next_month($datetime) {
        $date = apetech::first_day_of_month($datetime);
        $date->modify("+1 month");
        return $date;
    }

    public static function last_day_of_month($datetime) {
        $date = apetech::first_day_of_next_month($datetime);
        $date->modify("-1 seconds");
        return $date;
    }

    public static function unixtime_from_mysqltime($time) {
        $date = new DateTime($time, $object);
        return $date->getTimestamp();
    }

    public static function mysqltime($time = null) {
        if ($time === null) {
            $time = time();
        }
        $date = new DateTime();
        $date->setTimestamp($time);
        return $date->format("Y-m-d H:i:s");
    }

}

?>