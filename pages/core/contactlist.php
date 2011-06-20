<?php

/*
  Contact list page

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

require('bl/core/contact_list_bl.php');

class contact_list_page extends page {

    protected $contact_list_bl;
    protected $user_id;

    public function __construct() {
        $this->enable_component(component_types::$tables);
        parent::__construct();
        $this->contact_list_bl = new contact_list_bl();
        $this->user_id = input::validate('user_id', 'int');
        $this->add_text('title', 'Contact List');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            switch ($this->action) {
                case "up":
                    $this->move_up();
                    $this->default_action();
                    break;
                case "down":
                    $this->move_down();
                    $this->default_action();
                    break;
                default:
                    $this->default_action();
                    break;
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function gen_profile_link_url($id, $name) {
        return '<a href="' . html::gen_url('contactlist.php', array('user_id' => $id)) . '">' . $name . '</a>';
    }

    protected function move_up() {
        $this->contact_list_bl->move_up($this->user_id);
    }
    
    protected function move_down() {
        $this->contact_list_bl->move_down($this->user_id);
    }
    
    protected function default_action() {
        $contacts = $this->contact_list_bl->get_contact_list();
        if (is_array($contacts) && count($contacts) > 0) {
            $table = new table('', 'contactlist');
            $table->add_aligns(array('left', 'left', 'center', 'center', 'left', 'left'));
            $table_header = array(
                'Name',
                'Position',
                'Contact No.',
                'Email',
                'Location');
            if (page::$user->get_level() >= userlevels::$moderator) {
                $table_header[] = '';
                // $table_header[] = '';
            }

            $table->add_header($table_header);

            foreach ($contacts AS $_mem) {
               
                $data = array(
                    $this->gen_profile_link_url($_mem['id'], $_mem['lastname'] . ', '. $_mem['firstname']),
                    html::clean_text($_mem['position']),
                    html::clean_text("T: ". $_mem['phone_number']) ."<br />M: ". html::clean_text($_mem['mobile_number']),
                    '<a href="mailto:' . html::clean_text($_mem['email']) . '">' . html::clean_text($_mem['email']) . '</a>',
                    html::clean_text($_mem['location'])
                );
                if (page::$user->get_level() >= userlevels::$moderator) {
                    $data[] = '<a href="' . html::gen_url('profile.php', array('user_id' => html::clean_text($_mem['id']))) . '">Edit</a>';
                    // $data[] = '<a href="' . html::gen_url('contactlist.php', array('user_id' => $_mem['id'], 'action' => 'up')) . '">[U]</a> - <a href="' . html::gen_url('contactlist.php', array('user_id' => $_mem['id'], 'action' => 'down')) . '">[D]</a>';
                }

                $table->add_data($data);
            }

            $this->add_text('main', $table->v_display());
        } else {
            throw new Exception("No contacts found.");
        }
    }

}

?>