<?php

/*
  Register page

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
if (file_exists(RELATIVE_PATH . 'components/page.php')) {
    require_once(RELATIVE_PATH . 'components/page.php');
} else {
    require_once('components/core/page.php');
}

// end for unit testing

class register_page extends page {

    protected $confirm_password;
    protected $email;
    protected $confirm_email;
    protected $contact_number;
    protected $address;
    protected $activation_code;
    protected $security_code;
    protected $error;
    protected $user_id;
    protected $recaptcha;
    protected $site_root;

    public function __construct() {
        try {
            $this->enable_component(component_types::$recaptcha);
            parent::__construct();
            $this->site_root = 'index.php';
            $this->recaptcha = new recaptcha();
            $this->username = input::validate('username', 'string');
            $this->password = input::validate('password', 'message');
            $this->confirm_password = input::validate('confirm_password', 'message');
            $this->email = input::validate('email', 'email');
            $this->confirm_email = input::validate('confirm_email', 'message');
            $this->activation_code = input::validate('activation_code', 'message');
            $this->security_code = input::validate('security_code', 'message');
            $this->user_id = input::validate('user_id', 'int');
            $this->contact_number = input::validate('contact_number', 'message');
            $this->address = input::validate('address', 'message');

            $this->add_text('title', 'User Registration');
        } catch (Exception $ex) {
            $this->notice('<div class="reg_text">' . $ex->getMessage() . '</div>');
        }
    }

    public function generate_display() {
        $this->display();
    }

    public function set_site_root($root) {
        $this->site_root = $root;
    }
    
    protected function action() {
        try {
            switch ($this->action) {
                case "validate":
                    $this->add_text('main', $this->validate());
                    break;

                case "activate";
                    $this->add_text('main', $this->activate());
                    break;

                default:
                    $this->add_text('main', $this->gen_reg_form());
                    break;
            }
        } catch (Exception $ex) {
            $this->notice('<div class="reg_text">' . $ex->getMessage() . '</div>');
        }
    }

    protected function activate($message = null) {
        $ubl = new user_bl();
        $security = $ubl->get_user_by_security_code($this->user_id, $this->security_code);
        if (count($security) > 0) {
            $ubl->activate_user($security[0]['id']);
            return $this->display_user_activated();
        } else {
            throw new Exception("Invalid Activation Details");
        }
    }

    protected function gen_reg_form() {
        $ubl = new user_bl();
        $activation_code = apetech::random_string();
        $security_code = apetech::random_string();
        $ubl->add_registration_code($activation_code, $security_code);
        return $this->display_reg_form($activation_code, $security_code);
    }

    protected function validate() {
        if (page::$user->get_level() > userlevels::$guest) {
            throw new Exception("You are already registered.");
        } else {
            if (config::allow_registration() == true) {
                try {
                    validator::validate_username_email($this->username, $this->email);

                    validator::validate_password($this->password, $this->confirm_password);

                    validator::validate_email($this->email, $this->confirm_email);

                    $this->recaptcha->validate();

                    return $this->add_user(apetech::random_string());
                } catch (Exception $ex) {
                    $this->error = $ex->getMessage();
                    return $this->gen_reg_form();
                }
            }
        }
    }

    protected function add_user($security_code) {
        $ubl = new user_bl();
        $new_user_array = array(
            'username' => array('value' => $this->username, 'type' => PDO::PARAM_STR),
            'password' => array('value' => md5($this->password . config::salt()), 'type' => PDO::PARAM_STR),
            'email' => array('value' => $this->email, 'type' => PDO::PARAM_STR),
            'security' => array('value' => $security_code, 'type' => PDO::PARAM_STR),
            'phone_number' => array('value' => $this->contact_number, 'type' => PDO::PARAM_STR),
            'address' => array('value' => $this->address, 'type' => PDO::PARAM_STR)
        );

        $new_user = $ubl->add_user($new_user_array);
        if ($new_user > 0) {
            $click_url = html::build_registration_url(array('action' => 'activate', 'user_id' => $new_user, 'security_code' => $security_code));
            page::$user->send_activation_email($this->email, $this->username, $new_user, $security_code, $click_url);
            return $this->display_user_created();
        } else {
            throw new Exception("Error adding user. Contact " . config::smtp_sender());
        }
    }

    protected function display_user_activated() {
        return "<span class=\"reg_text\">User activated. You can now login.<br />
            	<br />
                <a href=\"" . html::gen_url($this->site_root) . "\">Click here to login.</a></span>";
    }
    
    protected function display_user_created() {
        return "Email sent to: ". html::clean_text($this->email) ."<br />
            <br />
            Click on the link in the email to continue.<br />
            <br />
            Remember to check your spam folder.";
    }

    protected function display_reg_form($activation_code, $security_code) {
        $form = '<form id="reg_form" action="' . html::gen_url('register.php', array('action' => 'validate')) . '" method="post">
                    <fieldset>
                        <legend>Registration Form</legend>
                        <div>' . $this->error . '</div>

                        <label>Username - <i>Between 1 and 15 characters long amd only letters, numbers, underscores and spaces</i></label>
                        <input id="username" type="text" name="username" value="' . html::clean_text($this->username) . '" /><br />

                        <label>Password - <i>Minimum of 6 characters long</i></label>
                        <input id="password" type="password" name="password" value="' . html::clean_text($this->password) . '" />
                        Confirm: <input id="password_conf" type="password" name="confirm_password" value="' . html::clean_text($this->confirm_password) . '" /><br />

                        <label>Email address - <i>Valid email address required to complete registration</i></label>
                        <input id="email" type="text" name="email" value="' . html::clean_text($this->email) . '" />
                        Confirm: <input id="email_conf" type="text" name="confirm_email" value="' . html::clean_text($this->confirm_email) . '" /><br />';
        foreach (profile_config::profile_list() AS $key => $profile) {
            if ($profile['registration'] == true && $key != 'username' && $key != 'password' && $key != 'email') {
                $form .= '<label>' . $profile['hrtext'] . ':</label>';
                switch ($profile['type']) {
                    case "text":
                        $form .= '<input name="' . $key . '" value="" /><br />';
                        break;

                    case "textarea":
                        $form .= '<textarea rows="5" cols="25" name="' . $key . '"></textarea><br />';
                        break;

                    default:
                        $form .= 'Not yet<br />';
                        break;
                }
            }
        }
        $form .= $this->recaptcha->generate() . '
                        <input type="hidden" name="activation_code" value="' . html::clean_text($activation_code) . '" />
                        <input name="register" type="submit" value="Register" />
                    </fieldset>
                </form>';

        return $form;
    }

}

?>
