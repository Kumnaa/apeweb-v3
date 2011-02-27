<?php

/*
  Forum images

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

class forum_images {

    static function forum($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/folder.gif';
                break;
        }
    }

    static function new_forum($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/folder2.gif';
                break;
        }
    }

    static function topic($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/t_icons/general_default.gif';
                break;
        }
    }

    static function new_topic($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/t_icons/general_default2.gif';
                break;
        }
    }

    static function post($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/folder.gif';
                break;
        }
    }

    static function new_post($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/t_icons/general_default2.gif';
                break;
        }
    }

    static function mini_post($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/icon_minipost.gif';
                break;
        }
    }

    static function new_mini_post($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/icon_minipost_new.gif';
                break;
        }
    }

    static function make_post($style) {
        switch ($style) {
            default:
                return 'http://images.apegaming.net/post.gif';
                break;
        }
    }

}

?>
