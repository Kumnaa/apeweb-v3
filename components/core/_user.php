<?php

/*
  Base user class

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

abstract class _user {

    protected $id;
    protected $username;
    protected $level;
    protected $email;
    protected $last_visit;
    protected $last_refresh;
    protected $user_ip;
    protected $session_username;
    protected $session_security;
    protected $session_random;
    protected $fix_ip;
    protected $cookie;
    protected $style;
    protected $in_login;
    protected $db;

    abstract protected function on_login();

    abstract protected function on_logout();

    abstract protected function extended_grab_info();

    public function get_email() {
        return $this->email;
    }

    public function get_style() {
        return $this->style;
    }

    public function get_user_ip() {
        return $this->user_ip;
    }

    public function get_user_id() {
        return $this->id;
    }

    public function get_username() {
        return $this->username;
    }

    public function get_level() {
        return $this->level;
    }

    public function get_last_visit() {
        return $this->last_visit;
    }

    public function get_address() {
        return $this->address;
    }

    public function get_phone_number() {
        return $this->phone_number;
    }

    public function __construct() {
        $this->db = page::$db_connection;

        $this->in_login = false;

        if (!isset($_COOKIE[config::cookie_name()])) {
            $this->set_cookie(md5('Guest'), 'L9pnLFBf', apetech::random_string(), 0);
        } else {
            $this->session_username = $_COOKIE[config::cookie_name()]['user'];
            $this->session_security = $_COOKIE[config::cookie_name()]['sec'];
            $this->session_random = $_COOKIE[config::cookie_name()]['rndm'];
        }

        $this->charset = "UTF-8";
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '') {
            $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $user_ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $user_ip = '';
            }
        }
        if ($user_ip != '') {
            $this->user_ip = apetech::encode_ip($user_ip);
        } else {
            $this->user_ip = '';
        }
    }

    public function update_page($_page) {
        $ubl = new user_bl();
        if ($_page == '') {
            $_page == 'Unknown';
        }
        $ubl->update_user_page($_page, $this->id, $this->session_random);
    }

    public function grab_info() {
        $ubl = new user_bl();
        $month = time() - 2678400;
        $sql = $ubl->get_session_data($this->session_security, $this->session_username, $this->session_random);
        if (count($sql) > 0) {
            if ($sql[0]['user_level'] > 0) {
                $this->id = $sql[0]['id'];
                $this->username = $sql[0]['username'];
                $this->level = $sql[0]['user_level'];
                $this->last_visit = $sql[0]['user_lastvisit'];
                $this->style = $sql[0]['style'];
                $this->last_refresh = time();
                $this->address = $sql[0]['address'];
                $this->phone_number = $sql[0]['phone_number'];
                $this->email = $sql[0]['email'];
                $ubl->update_user_last_refresh($this->id);
                $this->update_session();
            } else {
                $this->logout();
            }
        } else {
            $this->logout();
        }
        $this->extended_grab_info();
    }

    public function login($_username, $_password, $remember = 'off') {
        $ubl = new user_bl();
        $message = '';
        if ($remember == 'on') {
            $remember = '2';
            $timeout = time() + 2592000;
        } else {
            $remember = '1';
            $timeout = 0;
        }
        $time = time() - 300;
        $day = time() - 86400;
        $long = time() - 25920000;
        $ubl->clear_unregistered_users($long);
        $ubl->clear_old_sessions($time, $day, $long);
        $sql = $ubl->auth_user($_username, md5($_password . config::salt()));
        if (count($sql) > 0) {
            switch ($sql[0]['status']) {
                case "0":
                    throw new Exception('You need to activate your account');
                    break;
                case "1":
                    throw new Exception('You have been banned');
                    break;

                case "3":
                    throw new Exception('Please wait for your account to be activated');
                    break;

                default:
                    if ($sql[0]['id'] < 1) {
                        throw new Exception('Invalid user');
                    } else {
                        $message = '';
                        $this->id = $sql[0]['id'];
                        $this->last_visit = $sql[0]['user_lastrefresh'];
                        $this->last_refresh = time();
                        $this->level = $sql[0]['user_level'];
                        $this->username = $sql[0]['username'];
                        $this->session_username = md5($_username);
                        $this->session_security = $sql[0]['security'];
                        $this->session_random = apetech::random_string();
                        $this->on_login();
                        $ubl->clear_sessions($this->id, $this->user_ip);
                        $ubl->insert_session($this->id, $this->session_username, $this->session_security, $this->user_ip, $this->session_random, $remember, $_SERVER['HTTP_USER_AGENT']);
                        $ubl->update_user_last_visit($this->id, $this->last_visit);
                        $this->set_cookie($this->session_username, $sql[0]['security'], $this->session_random, $timeout);
                    }
                    break;
            }
        } else {
            throw new Exception('Username/password incorrect');
        }
    }

    public function logout() {
        $this->set_cookie('0', '0', '0', '-1');
        $this->session_username = '';
        $this->session_random = '';
        $this->session_security = '';
        $this->id = '0';
        $this->username = 'Guest';
        $this->level = 0;
        $this->last_visit = time();
        $this->style = 0;
        $this->last_refresh = time();
        $this->address = '';
        $this->phone_number = '';
        $this->on_logout();
    }

    protected function set_cookie($_username, $_security, $_random, $_timeout) {
        setcookie(config::cookie_name() . '[user]', $_username, $_timeout, '/', config::domain());
        setcookie(config::cookie_name() . '[sec]', $_security, $_timeout, '/', config::domain());
        setcookie(config::cookie_name() . '[rndm]', $_random, $_timeout, '/', config::domain());
    }

    protected function update_session() {
        $ubl = new user_bl();
        $ubl->update_session($_SERVER['HTTP_USER_AGENT'], $this->id, $this->session_random);
    }

    public function send_activation_email($email, $username, $user_id, $security_code, $click_url) {
        $recipient = strtolower($email);
        $subject = config::domain() . ' Activation email';
        $headers = "From: " . config::smtp_sender() . "\n";
        $headers .= "Reply-To: " . config::smtp_sender() . "\n";
        $headers .= "Sender: " . config::smtp_sender() . "\n";


        $message = messages::registration_email();
        $message = str_replace('<!--ACTIVATION_URL-->', $click_url, $message);
        $message = str_replace('<!--DOMAIN-->', config::domain(), $message);
        $message = str_replace('<!--USERNAME-->', $username, $message);
        if (apetech::send_mail($email, $subject, $message, config::smtp_sender(), config::smtp_sender()) == false) {
            return true;
        } else {
            throw new Exception('Failure!<br /><br />Contact ' . config::smtp_sender() . '<br />There was a problem sending the email.');
        }
    }

    public function resend_activation_email($email, $redirect_url) {
        $ubl = new user_bl();
        $sql = $ubl->get_user_by_email($email);
        if (is_array($sql) && count($sql) > 0 && $sql[0]['status'] == 0) {
            $click_url = html::build_registration_url(array('action' => 'activate', 'user_id' => $sql[0]['id'], 'security_code' => $sql[0]['security'], 'redirect_url' => urlencode($redirect_url)));
            $this->send_activation_email($email, $sql[0]['username'], $sql[0]['id'], $sql[0]['security'], $click_url);
            return 'Email sent.';
        } else {
            throw new Exception("No email to resend.");
        }
    }

    public function update_password($old_password, $new_password, $new2_password) {
        $ubl = new user_bl();
        $sql = $ubl->get_old_password($this->id);
        if ($sql[0]['password'] == md5($old_password . config::salt())) {
            if ($new_password != '' && ($new_password == $new2_password)) {
                $ubl->update_password($new_password, $this->id);
            } else {
                throw new Exception("Error with new passwords");
            }
        } else {
            throw new Exception("Old password is incorrect");
        }
    }

    public function recover_password($email) {
        $ubl = new user_bl();

        $sql = $ubl->get_user_by_email($email);
        if (is_array($sql) && sizeof($sql) > 0) {
            if ($sql[0]['status'] == 2) {
                $new_pass = apetech::random_string();
                $recipient = $sql[0]['username'] . ' <' . $sql[0]['email'] . '>';
                $ubl->update_password($new_pass, $sql[0]['id']);
                $subject = 'Password change';
                $message = 'Your password has been changed to ' . $new_pass . '<br />';
                if (apetech::send_mail($recipient, $subject, $message, config::smtp_sender(), config::smtp_sender()) == false) {
                    return '<span class="reg_warn">Email sent</span>';
                } else {
                    throw new Exception("Error sending email");
                }
            } else {
                throw new Exception("This is an invalid action for this type of account");
            }
        } else {
            throw new Exception("User not found");
        }
    }

}

?>