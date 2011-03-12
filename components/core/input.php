<?php
/*
  Input validator

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

class input {

    public static function cleanup($var, $data_type, $default = false) {
        return input::commonmethod($var, $data_type, $default);
    }

    public static function validate($var, $data_type, $default = false) {
        if (isset($_POST[$var])) {
            $return = $_POST[$var];
        } else if (isset($_GET[$var])) {
            $return = $_GET[$var];
        } else {
            $return = null;
        }

        return input::commonmethod($return, $data_type, $default);
    }

    private static function commonmethod($return, $data_type, $default = false) {
        switch ($data_type) {
            case "checkbox":
                $return = self::check_checkbox($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = 0;
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "array":
                break;

            case "email":
                break;

            case "float":
                $return = self::check_float($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = 0;
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "intlist":
                $return = self::check_int_list($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = 0;
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "int":
                $return = self::check_int($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = 0;
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "string":
                $return = self::check_string($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = '';
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "message":
                $return = self::check_message($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = '';
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "time":
                $return = self::check_time($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = FALSE;
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "string_list":
                $return = self::check_string_list($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = '';
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "date":
                $return = self::check_date($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = FALSE;
                    } else {
                        $return = $default;
                    }
                }
                break;

            case "post_id":
                $return = self::check_post_id($return);
                if ($return == false) {
                    if ($default == false) {
                        $return = '';
                    } else {
                        $return = $default;
                    }
                }
                break;

            default:
                throw new Exception("Invalid input validator type: ". $data_type);
                break;
        }
        
        return ($return);
    }

    //check if self::magic_quotes are on or off and remove quotes accordingly
    public static function magic_quotes($_value) {
        if (ini_get('magic_quotes_gpc') != "Off") {
            $_value = stripslashes($_value);
        }
        return ($_value);
    }

    //check for '0-9', '-' and '.' - positive/negative number with optional decimal places
    public static function check_float($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^[0-9.\-]+$/', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    //check for '0-9' and '-' - postive/negative integer
    public static function check_int($_value) {
        $_value = self::magic_quotes($_value);

        if (preg_match('/^[0-9\-]+$/', $_value)) {
            $value = $_value;
        } else {
            $value = FALSE;
        }
        return($value);
    }

    public static function check_checkbox($_value) {
        if ($_value == "on") {
            $value = 1;
        } else {
            $value = 0;
        }
        return $value;
    }

    //check for '0-9' and '-' - postive/negative integer in a ; seperated list
    public static function check_int_list($_value) {
        $_value = self::magic_quotes($_value);

        if (preg_match('/^[0-9\-;]+$/', $_value)) {
            $value = $_value;
        } else {
            $value = FALSE;
        }
        return($value);
    }

    //check for 'a-z', 'A-Z', '0-9', '_', '/' and ' ' - a simple string
    public static function check_string($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^[a-zA-Z0-9_\-\/ ]+$/', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    //simply makes the string mysqlable
    public static function check_message($_value) {
        $_value = self::magic_quotes($_value);
        $_value = str_replace("[quote]", "[quote=\"Somebody\"]", $_value);
        return($_value);
    }

    //checks for a valid email layout
    public static function check_email($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*?[a-z]+$/is', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    //checks for '0-9', '#', '-' and '_'
    public static function check_post_id($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^[0-9#-_]+$/', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    //checks for a 00/00/00 type layout
    public static function check_date($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^\d\d?\/\d\d?\/\d\d$/', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    //check for a list of strings with ';' seperator
    public static function check_string_list($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^[a-zA-Z0-9_;\/ ]+$/', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    //check for a 00:00 type layout
    public static function check_time($_value) {
        $_value = self::magic_quotes($_value);
        if (preg_match('/^\d\d?:\d\d?$/', $_value)) {
            $return = $_value;
        } else {
            $return = FALSE;
        }
        return ($return);
    }

}

?>