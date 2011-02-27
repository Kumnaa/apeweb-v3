<?php

/*
  Login page

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

// for unit testing
if (file_exists('components/page.php')) {
    require_once('components/page.php');
} else {
    require_once('components/core/page.php');
}

// end for unit testing

class login_page extends page {

    private $login_forgotten_password = '';
    private $login_resend_email = '';
    private $login_action = '';
    private $login_register = '';

    public function __construct() {
        parent::__construct();
        $this->add_text('title', 'Login/Logout');
    }

    public function set_fields($action, $register, $forgotten, $resend) {
        $this->login_action = $action;
        $this->login_register = $register;
        $this->login_forgotten_password = $forgotten;
        $this->login_resend_email = $resend;
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        switch ($this->action) {
            case "logout":
                try {
                    $this->user->logout();
                    $this->notice('Logout complete');
                } catch (Exception $e) {
                    $this->notice($e->getMessage());
                }
                $this->login_form();
                break;

            default:
                if (strlen($this->username) > 0 && strlen($this->password) > 0) {
                    try {
                        $this->user->login($this->username, $this->password, $this->remember);
                        $this->notice('Login successful<br />
                            <a href="' . config::completed_login_url() . '">Click here to proceed.</a>');
                    } catch (Exception $e) {
                        $this->notice($e->getMessage());
                        $this->login_form();
                    }
                } else {
                    if ($this->user->get_level() > userlevels::$guest) {
                        $this->notice(html_login::logout());
                    } else {
                        $this->login_form();
                    }
                }
                break;
        }
    }

    protected function login_form() {
        if (file_exists('templates/login.html')) {
            $login_template = new page($this->template);
            $login_template->set_template('login');
            if (strlen($this->login_forgotten_password) > 0) {
                $login_template->add_text('forgotten', $this->login_forgotten_password);
            }

            if (strlen($this->login_resend_email) > 0) {
                $login_template->add_text('resend', $this->login_resend_email);
            }

            $login_template->add_text('register', $this->login_register);

            $login_template->add_text('action', $this->login_action);

            $this->add_text('main', $login_template->display());
        } else {
            $this->add_text('main', html_login::generate_form($this->username, $this->password));
        }
    }

}

?>
