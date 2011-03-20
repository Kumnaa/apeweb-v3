<?php

/*
  Dummy page class for unit testing

  @author Ben Bowtell

  @date 27-Feb-2011

  (c) 2011 by http://www.amplifycreative.net

  contact: ben@amplifycreative.net.net

  ﻿   This program is free software: you can redistribute it and/or modify
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

ini_set('display_errors', 1);

require('components/core/_page.php');
require('bl/core/forum_bl.php');
require('config/core/bbcode.php');
require('config/core/forum_config.php');
require('config/core/forum_images.php');
require('config/core/irc_config.php');
require('config/core/profile_config.php');

config::strict_mode();

class page extends _page {

    protected $menu;

    protected function initialise_bl() {
        
    }

    protected function create_user() {
        page::$user = new user();
    }

    protected function pre_action() {
        page::$user->grab_info();
        page::$user->update_page(get_class($this));
        $this->add_text('javascript', 'var url_root = "' . config::site_url() . '";');
    }

    protected function post_action() {}

    protected function notice($message) {
        $this->add_text('main_header', 'Error');
        $this->add_text('main', html::notice($message));
    }

    protected function please_login($element = 'main') {
        $this->add_text($element, '<div id="please_login">Please login</div>');
    }

}

?>