<?php

/*
  Administrate Forums page

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

class administrate_forums_page extends page {

    public function __construct() {
        parent::__construct();
        $this->enable_component(component_types::$forums);
        $this->forum_id = input::validate('forum_id', 'int');
        $this->category_id = input::validate('page_id', 'int');
        $this->breadcrumb = new breadcrumb();
        $this->add_text('title', 'Forum Administration');
        $this->initialise_bl();
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if (page::$user->get_level() >= userlevels::$administrator) {
                if ($this->category_id > 0) {

                } else if ($this->forum_id > 0) {

                } else {
                    $this->administrate_categories();
                }
            } else {
                throw new Exception("Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }
    
    protected function administrate_categories() {
        $categories = $this->forum_bl->get_categories();
        if (is_array($categories) && count($categories) > 0) {
            
        }
    }
}

?>