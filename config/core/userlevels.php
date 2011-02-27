<?php

/*
  User levels config class

  @author Ben Bowtell

  @date 22-Nov-2009

  (c) 2009 by http://www.apetechnologies.net/

  contact: ben@apetechnologies.net

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

class userlevels {

    static $guest = 0;
    static $registered = 1;
    static $friend = 20;
    static $member = 30;
    static $officer = 40;
    static $moderator = 50;
    static $administrator = 60;
    static $senior_administrator = 70;
    static $technical_administrator = 80;
    static $noaccess = 1000;

}

?>
