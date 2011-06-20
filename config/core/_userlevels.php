<?php

/*
  User levels base config class

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

abstract class _userlevels {

    public static $guest;
    public static $registered;
    public static $friend;
    public static $member;
    public static $officer;
    public static $moderator;
    public static $administrator;
    public static $senior_administrator;
    public static $technical_administrator;
    public static $noaccess;
    
    protected static $access_config = array(
        'guest' => 0,
        'registered' => 1,
        'friend' => 20,
        'member' => 30,
        'officer' => 40,
        'moderator' => 50,
        'administrator' => 60,
        'senior_administrator' => 70,
        'technical_administrator' => 80,
        'noaccess' => 1000
    );
    
    public static function userlevel_to_text($user_level) {
        foreach (userlevels::$access_config AS $keys=>$value) {
            if ($user_level == $value) {
                $access_list = userlevels::access_list();
                return $access_list[$value];
            }
        }
        
        return 'Unknown';
    }
    
    public static abstract function add_extra_permissions();

    public static function build_permissions()
    {
        foreach (userlevels::$access_config AS $key=>$value) {
            userlevels::$$key = $value;
        }
    }
    
    public static function access_list()
    {
        return array(
            userlevels::$guest => 'Guest',
            userlevels::$registered => 'Registered',
            userlevels::$friend => 'Friend',
            userlevels::$member => 'Member',
            userlevels::$officer => 'Officer',
            userlevels::$moderator => 'Moderator',
            userlevels::$administrator => 'Administrator',
            userlevels::$senior_administrator => 'Senior Administrator',
            userlevels::$technical_administrator => 'Technical Administrator'
        );
    }
}
?>
