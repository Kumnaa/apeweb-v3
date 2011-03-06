<?php
/*
  Breadcrumb generator

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

class breadcrumb {

    private $trail;

    public function __construct() {
        $this->trail = array();
    }

    public function add_crumb($text, $link = null) {
        $this->trail[] = array($text, $link);
    }

    public function display() {
        $array = array();
        foreach ($this->trail AS $_array) {
            if ($_array[1] === null) {
                $array[] = $_array[0];
            } else {
                $array[] = '<a href="' . $_array[1] . '">' . $_array[0] . '</a>';
            }
        }

        return implode(' &gt; ', $array);
    }

}