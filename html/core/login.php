<?php

/*
  Login HTML generator

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

class html_login {

    public static function generate_form($username = '', $password = '') {
        return '
            <form action="' . html::gen_url('login.php') . '" method="post">
                <fieldset>
                    <legend>Login</legend>
                    <label>Username:</label>
                    <input type="text" name="username" value="' . html::clean_text($username) . '" />
                    <br />
                    <label>Password:</label>
                    <input type="password" name="password" value="' . html::clean_text($password) . '" />
                    <br />
                    <input type="submit" value="Login" />
                </fieldset>
            </form>';
    }

    public static function logout() {
        return '<a href="' . html::gen_url('login.php', array('action' => 'logout')) . '">Logout</a>';
    }

}

?>
