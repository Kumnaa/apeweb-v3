<?php

/*
  Calendar page

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

class calendar_page extends page {

    protected $month;

    public function __construct() {
        $this->enable_component(component_types::$calendar);
        parent::__construct();
        $today = new DateTime();
        $this->add_text('title', 'Category Viewer');
        $this->month = input::validate('month', 'string', $today->format('Ym'));
    }

    public function generate_display() {
        switch ($this->action) {
            case "data":
                $this->display_xml();
                break;
            default:
                $this->display();
                break;
        }
    }

    protected function action() {
        try {
            switch ($this->action) {
                case "data":
                    $date = new DateTime(substr($this->month, 0, 4) . '-' . substr($this->month, 4, 2) . '-1');
                    $calendar = new calendar($date);
                    $this->add_text('xml', $calendar->display_xml());
                    break;
                
                default:
                    $date = new DateTime(substr($this->month, 0, 4) . '-' . substr($this->month, 4, 2) . '-1');
                    $calendar = new calendar($date);
                    $this->add_text('main', $calendar->display($this->template, 'calendar_large'));
                    break;
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

}

?>
