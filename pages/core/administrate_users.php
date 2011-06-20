<?php

/*
  Administrate Users page

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

class administrate_users_page extends page {

    protected $username;
    protected $password;
    protected $email;
    protected $location;
    protected $position;
    protected $phone_numner;
    protected $mobile_number;
    protected $user_bl;
    
    public function __construct() {
        parent::__construct();
        $this->enable_component(component_types::$forums);
        $this->enable_component(component_types::$tables);
        $this->username = input::validate('username', 'string');
        $this->password = input::validate('password', 'message');
        $this->email = input::validate('email', 'message');
        $this->position = input::validate('position', 'message');
        $this->location = input::validate('location', 'message');
        $this->phone_number = input::validate('phone_number', 'message');
        $this->mobile_number = input::validate('mobile_number', 'message');
        $this->user_bl = new user_bl();
        $this->add_text('title', 'User Administration');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if (page::$user->get_level() >= userlevels::$administrator) {
                switch ($this->action) {
                    case "add":
                        $this->add_user();
                        break;
                    case "list":
                        $this->list_users();
                        break;
                }
            } else {
                throw new Exception("Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }
    
    protected function list_users()
    {
        $users = $this->user_bl->get_all_users();
        if (is_array($users) && count($users) > 0){
            $table = new table();
            $table->add_header(array('Username', 'Email', 'Status', 'Access Level', 'Last Visit'));
            foreach ($users AS $user) {
                $table->add_data(
                        array(
                            '<a href="'. html::gen_url('profile.php', array('user_id' => html::clean_text($user['id']))) .'">'. html::clean_text($user['username']) .'</a>',
                            html::clean_text($user['email']),
                            html::clean_text(apetech::status_to_text($user['status'])),
                            html::clean_text(userlevels::userlevel_to_text(($user['user_level']))),
                            html::clean_text(date(forum_config::$date_format, $user['user_lastvisit']))
                            )
                        );
            }
            
            $this->add_text('main', $table->v_display());
        }
    }
    
    protected function add_user() {
        if (strlen($this->username) > 0 && strlen($this->password) && strlen($this->email)) {
            if (count($this->user_bl->get_mini_user_by_username_or_email($this->username, null)) > 0) {
                $this->add_text('main', 'User already exists<br /><br />');
            } else {
                $last_order = $this->user_bl->get_last_list_order();
                $new_user_array = array(
                    'username' => array('value' => $this->username, 'type' => PDO::PARAM_STR),
                    'password' => array('value' => md5($this->password . config::salt()), 'type' => PDO::PARAM_STR),
                    'email' => array('value' => $this->email, 'type' => PDO::PARAM_STR),
                    'security' => array('value' => apetech::random_string(), 'type' => PDO::PARAM_STR),
                    'phone_number' => array('value' => $this->phone_number, 'type' => PDO::PARAM_STR),
                    'mobile_number' => array('value' => $this->mobile_number, 'type' => PDO::PARAM_STR),
                    'position' => array('value' => $this->position, 'type' => PDO::PARAM_STR),
                    'location' => array('value' => $this->location, 'type' => PDO::PARAM_STR),
                    'status' => array('value' => 2, 'type' => PDO::PARAM_INT)
                );
                $this->user_bl->add_user($new_user_array);
                $this->notice(html::clean_text($this->username) .' added.');
            }
        }

        $this->add_text('main', '<b>Enter details here to add a new user</b><br /><br />
            <form action="'. html::gen_url('administrate_users.php', array('action' => 'add')) .'" method="post"><div>
                <label>Username</label> <input type="text" name="username" /> <span class="gensmall">(required)</span><br />
                <label>Password</label> <input type="text" name="password" /> <span class="gensmall">(required)</span><br />
                <label>Email</label> <input type="text" name="email" /> <span class="gensmall">(required)</span><br />
                <label>Location</label> <input type="text" name="location" /><br />
                <label>Position</label> <input type="text" name="position" /><br />
                <label>Phone Number</label> <input type="text" name="phone_number" /><br />
                <label>Mobile Number</label> <input type="text" name="mobile_number" /><br />
                <input type="submit" value="add" /><br />
                </div></form>');
    }
}

?>