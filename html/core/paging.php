<?php

/*
  Paging html generator

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

class paging {

    public $total_items = 0;
    public $items_per_page = 0;
    public $page = 0;
    public $relative_url = '';
    public $url_arguments = array();
    public $type = 'arguments';
    private $total_pages = 0;
    private $current_page = 0;
    private $start_item;
    private $page_array = array();

    public function display() {
        if ($this->page > 0) {
            $page = $this->page - 1;
        } else {
            $page = 0;
        }

        $this->start_item = $page * $this->items_per_page;
        $this->generate_total_pages();
        if ($this->total_pages == 1) {
            return '';
        } else {
            $this->current_page();
            $this->generate_page_array();
            return $this->generate_html();
        }
    }

    private function generate_html() {
        $return = array();
        if ($this->page > 0) {
            $_page = $this->page - 1;
        } else {
            $_page = 0;
        }

        $page_total = count($this->page_array);
        $page_list = array(1, 2, 3, 4, $page_total - 3, $page_total - 2, $page_total - 1, $page_total);

        if ($this->page > 2 || $this->page < $page_total - 1) {
            $page_list = array_merge($page_list, array($this->page - 2, $this->page - 1, $this->page, $this->page + 1, $this->page + 2));
        }

        $last_page = -1;
        foreach ($this->page_array AS $key => $value) {
            if (array_search($key + 1, $page_list) !== false) {
                if ($last_page != $key - 1) {
                    $return[] = '..';
                }
                $return[] = $this->generate_page_entry($key, $value, $_page);
                $last_page = $key;
            }
        }

        return '<div class="paging">'. str_replace(',..,', '..', implode(',', $return)) .'</div>';
    }

    private function generate_page_entry($key, $value, $_page) {
        $page = $key + 1;
        if ($key == $_page) {
            $return = '<b>' . $page . '</b>';
        } else {
            $return = '<a href="' . $value . '">' . $page . '</a>';
        }

        return ($return);
    }

    private function generate_page_array() {
        $arguments = array();
        for ($i = 1; $i <= $this->total_pages; $i++) {
            $url = $this->relative_url;
            if ($this->type == 'arguments') {
                $arguments = array_merge($this->url_arguments, array('page_id' => ( $i )));
            } else {
                $url .= ( $i );
            }
            $this->page_array[$i - 1] = html::gen_url($url, $arguments);
        }
    }

    private function current_page() {
        $this->current_page = floor($this->start_item / $this->items_per_page);
    }

    private function generate_total_pages() {
        if ($this->items_per_page > 0) {
            $this->total_pages = ceil($this->total_items / $this->items_per_page);
        } else {
            throw new Exception("Zero items per page");
        }
    }

}

?>