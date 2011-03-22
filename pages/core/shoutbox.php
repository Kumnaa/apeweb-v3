<?php

/*
  shoutbox page

  @author Ben Bowtell

  @date 16-Mar-2011

  (c) 2011 by http://www.amplifycreative.net/

  contact: ben@amplifycreative.net

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

class shoutbox_page extends page {

    private $message;
    
    public function __construct() {
        $this->enable_component(component_types::$shoutbox);
        parent::__construct();
        $this->add_text('title', 'Shoutbox');
        $this->set_template('default');
        $this->message = input::validate('message', 'message');
    }

    public function generate_display() {
        switch ($this->action) {
            case "html":
                $this->display_plain();
                break;
            case "post":
                break;
            default:
                $this->display();
                break;
        }
    }

    protected function action() {
        try {
            switch ($this->action) {
                case "html":
                    $shoutbox = new shoutbox();
                    $this->add_text('html', $shoutbox->display_plain_shoutbox());
                    break;
                case "post":
                    if (strlen($this->message) > 0) {
                        $this->post_validator();
                    }
                    break;
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function post_validator() {
        if (page::$user->get_level() > userlevels::$guest) {
            $this->add_post();
        }
    }
    
    protected function add_post() {
        $shoutbox_bl = new shoutbox_bl();
        $shoutbox_bl->add_shout($this->message, page::$user->get_user_id(), page::$user->get_user_ip());
    }
}

?>
