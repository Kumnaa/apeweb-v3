<?php

/*
  Recaptcha api wrapper

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
require_once('apis/core/recaptchalib.php');

class recaptcha {

    public function generate() {
        return recaptcha_get_html(recaptcha_config::$public_key);
    }

    public function validate() {
        $resp = recaptcha_check_answer(recaptcha_config::$private_key, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
        if (!$resp->is_valid) {
            throw new Exception("The reCAPTCHA wasn't entered correctly. Go back and try it again.");
            // (reCAPTCHA said: " . $resp->error . ")");
        }
    }

}