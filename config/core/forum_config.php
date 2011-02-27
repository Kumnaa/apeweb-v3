<?php

/*
  Forum config

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

class forum_config {

    static $globalannouncement = 4;
    static $announcement = 3;
    static $link = 2;
    static $stick = 1;
    static $normal = 0;
    static $date_format = "F j, Y, g:i a";
    static $time_format = "g:i";
    static $page_limit = 20;

    static function image_warehouse_url() {
        return '';
    }

    static function image_warehouse_filesystem() {
        return '';
    }

    static function default_avatar() {
        return '';
    }

}

?>
