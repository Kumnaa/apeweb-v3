<?php

/*
  Gallery page

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

class images_page extends page {

    private $forum_bl;
    protected $image_manager;
    protected $page_id;
    protected $image_id;
    protected $paging;

    public function __construct() {
        parent::__construct();
        $this->gallery_id = input::validate('gallery_id', 'int');
        $this->forum_bl = new forum_bl();
        $this->add_text('title', 'Gallery');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            switch ($this->action) {
                case "delete":
                    $this->delete();
                    break;
                case "view":
                    $this->view();
                    break;
                case "create":
                    $this->create();
                    break;
                default:
                    $this->browse();
                    break;
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function delete() {
    }

    protected function view() {
    }

    protected function browse() {
    }

    protected function create() {
    }
}

?>