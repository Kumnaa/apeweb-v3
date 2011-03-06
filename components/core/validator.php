<?php

/*
  Validator class

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

class validator {

    public static function validate_username_email($username, $email) {
        $user_bl = new user_bl();

        $check = $user_bl->get_mini_user_by_username_or_email($username, $email);
        if (count($check) > 0) {
            throw new Exception('Username/email already exists.');
        } else {
            if (strlen($username) < 3) {
                throw new Exception('Username must be greater than 3 characters.');
            }
        }
    }

    public static function validate_password($_password, $_conf_password) {
        if (strlen($_password) < 6) {
            if ($_password != $_conf_password) {
                throw new Exception('Password confirmation failed.');
            } else {
                throw new Exception('Password is too short.');
            }
        }
    }

    public static function validate_email($_email, $_conf_email) {
        if (strlen($_email) > 0) {
            if ($_email != $_conf_email) {
                throw new Exception('Email verification failed.');
            }
        } else {
            throw new Exception('Address not valid');
        }
    }

}

?>
