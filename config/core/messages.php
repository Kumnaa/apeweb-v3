<?php

/*
  Message template class

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

class messages {

    public static function registration_email() {
        return 'Welcome to <!--DOMAIN-->, <!--USERNAME--><br /><br />
	            Your account is currently inactive. You cannot use it until you visit the following link:<br /><br />
	            <!--ACTIVATION_URL--><br /><br />
	            Thank you for registering';
    }

}

?>
