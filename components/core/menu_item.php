<?php

/*
  Menu item object

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

class menu_item {

    private $text;
    private $id;
    private $link;
    private $link_id;
    private $children;
    private $children_id;

    public function __construct($text, $link = '') {
        $this->text = $text;
        $this->link = $link;
        $this->children = array();
    }

    public function add_id($id) {
        $this->id = $id;
    }

    public function add_child($child) {
        $this->children[] = $child;
    }

    public function add_child_id($id) {
        $this->children_id = $id;
    }

    public function add_link_id($id) {
        $this->link_id = $id;
    }

    public function generate($use_container = true) {
        $id = '';
        $html = '';
        if ($this->id) {
            $id = ' id="' . $this->id . '"';
        }

        if ($use_container == true) {
            $html .= '<li' . $id . '>';
        }

        if ($this->link) {
            $link_id = '';
            if ($this->link_id) {
                $link_id = ' id="' . $this->link_id . '"';
            }
            $html .= '<a' . $link_id . ' href="' . $this->link . '">' . $this->text . '</a>
                ';
        } else {
            $html .= '<a>' . $this->text . '</a>';
        }
        if (count($this->children) > 0) {
            $id = '';
            if ($this->children_id) {
                $id = ' id="' . $this->children_id . '"';
            }
            $html .= '<ul'. $id . '>
                ';
            foreach ($this->children AS $child) {
                $html .= $child->generate();
            }
            
            $html .= '</ul>
                ';
        }

        if ($use_container == true) {
            $html .= '</li>
            ';
        }

        return $html;
    }

}

?>
