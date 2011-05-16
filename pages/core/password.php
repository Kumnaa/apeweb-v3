<?php

/*
  Password manager page

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

class password_page extends page {

    private $old_password;
    private $new_password;
    private $new2_password;
    private $email;
    private $redirect_url;

    public function __construct() {
        parent::__construct();
        $this->old_password = input::validate('old_password', 'string');
        $this->new_password = input::validate('new_password', 'string');
        $this->new2_password = input::validate('new2_password', 'string');
        $this->email = input::validate('email', 'message');
        $this->redirect_url = input::validate('redirect_url', 'message');
        $this->add_text('title', 'User Password Management');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        switch ($this->action) {
            default:
                $this->lost_password();
                break;
            case "resend":
                $this->resend();
                break;
            case "change":
                $this->change_password();
                break;
        }
    }

    protected function resend() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->notice(page::$user->resend_activation_email($this->email, $this->redirect_url));
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }

        $this->add_text('main', '
            <form action="' . html::capture_url(true) . '" method="post">
                <fieldset>
                    <legend style="font-weight:bold;">Resend activation email</legend>
                    <label>Enter your email address:</label>
                    <input type="text" class="text" size="25" name="email" value="" />
                    <input type="hidden" class="text" name="redirect_url" value="' . html::clean_text($this->redirect_url) . '" />
                    <input type="submit" value="Send" />
                </fieldset>
            </form>');
    }

    protected function lost_password() {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->notice(page::$user->recover_password($this->email));
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }

        $this->add_text('main', '
            <form action="' . html::capture_url(true) . '" method="post">
                <fieldset>
                    <legend style="font-weight:bold;">Recover password</legend>
                    <label>Enter your email address:</label>
                    <input type="text" class="text" size="25" name="email" value="" />
                    <input type="hidden" class="text" name="redirect_url" value="' . html::clean_text($this->redirect_url) . '" />
                    <input type="submit" value="Send" />
                </fieldset>
            </form>');
    }

    protected function change_password() {
        if (page::$user->get_level() >= userlevels::$registered) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    page::$user->update_password($this->old_password, $this->new_password, $this->new2_password);
                    $this->add_text('main', 'Password updated');
                } catch (Exception $ex) {
                    $this->notice('Password not updated');
                }
            }

            $this->add_text('main', '
                <form action="' . html::capture_url(true) . '" method="post">
                    <fieldset>
                        <legend style="font-weight:bold;">Change password</legend><br />

                    	<div style="float:left;">
	                        <label>Old password:</label>
                        </div>
						<div style="float:left;position:absolute;padding-left: 175px;">
	                        <input type="password" size="25" name="old_password" maxlength="25" value="" />
	                    </div>
	                    <div style="height:15px;clear:both;"></div>
	                    
                        <div style="float:left;">
	                        <label>New password:</label>
                        </div>
						<div style="float:left;position:absolute;padding-left: 175px;">
	                        <input type="password" size="25" name="new_password" maxlength="25" value="" />
	                    </div>
						<div style="height:15px;clear:both;"></div>
	                    	                    
                        <div style="float:left;">
							<label>Confirm new password:</label>
						</div>
 						<div style="float:left;position:absolute;padding-left: 175px;">
	                        <input type="password" size="25" name="new2_password" maxlength="25" value="" />
	                    </div>
                        <div style="height:15px;clear:both;"></div>
                        <input type="hidden" class="text" name="email" value="' . html::clean_text($this->redirect_url) . '" />
                        <input type="submit" value="Update" />
                    </fieldset>
                </form>');
        } else {
            $this->add_text('main', 'Please login');
        }
    }

}

?>