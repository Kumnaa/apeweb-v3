<?php

/*
  Profile configuration

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

class profile_config {

    static function access_level() {
        return userlevels::$guest;
    }

    static function profile_list() {
        return array(
            'username' => profile_config::username(),
            'avatar' => profile_config::avatar(),
            'avatar_img' => profile_config::avatar_img(),
            'bio' => profile_config::bio(),
            'colour' => profile_config::colour(),
            'contact_number' => profile_config::contact_number(),
            'country' => profile_config::country(),
            'current_play' => profile_config::current_play(),
            'email' => profile_config::email(),
            'firstname' => profile_config::firstname(),
            'user_lastrefresh' => profile_config::last_refresh(),
            'latitude' => profile_config::latitude(),
            'location' => profile_config::location(),
            'longitude' => profile_config::longitude(),
            'mobile_number' => profile_config::mobile_number(),
            'pic' => profile_config::pic(),
            'pic_img' => profile_config::pic_img(),
            'position' => profile_config::position(),
            'user_posts' => profile_config::user_posts(),
            'rss_password' => profile_config::rss_password(),
            'signature' => profile_config::signature(),
            'signature_url' => profile_config::signature_url(),
            'status' => profile_config::status(),
            'steam_id' => profile_config::steam_id(),
            'style' => profile_config::style(),
            'title' => profile_config::title(),
            'user_level' => profile_config::user_level(),
            'visible' => profile_config::visible(),
            'address' => profile_config::address()
        );
    }

    static function address() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$noaccess,
            'view' => userlevels::$noaccess,
            'type' => 'text',
            'hrtext' => 'Address',
            'registration' => false
        );
    }

    static function pic() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$noaccess,
            'type' => 'text',
            'hrtext' => 'Picture',
            'registration' => false
        );
    }

    static function pic_img() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$noaccess,
            'view' => userlevels::$member,
            'type' => 'image',
            'hrtext' => 'Picture Image',
            'registration' => false
        );
    }

    static function email() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$moderator,
            'type' => 'text',
            'hrtext' => 'Email',
            'registration' => true
        );
    }

    static function position() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$registered,
            'type' => 'text',
            'hrtext' => 'Position',
            'registration' => false
        );
    }

    static function location() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$registered,
            'type' => 'text',
            'hrtext' => 'Location',
            'registration' => false
        );
    }

    static function contact_number() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Contact Number',
            'registration' => false
        );
    }

    static function mobile_number() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Mobile Number',
            'registration' => false
        );
    }

    static function username() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$administrator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Username',
            'registration' => true
        );
    }

    static function user_posts() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$administrator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Posts',
            'registration' => false
        );
    }

    static function last_refresh() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$noaccess,
            'view' => userlevels::$administrator,
            'type' => 'text',
            'hrtext' => 'Last Login',
            'registration' => false
        );
    }

    static function status() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$administrator,
            'view' => userlevels::$guest,
            'type' => 'dropdown',
            'hrtext' => 'Status',
            'registration' => false
        );
    }

    static function visible() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'dropdown',
            'hrtext' => 'Visible',
            'registration' => false
        );
    }

    static function user_level() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$administrator,
            'view' => userlevels::$administrator,
            'type' => 'dropdown',
            'hrtext' => 'Access Level',
            'registration' => false
        );
    }

    static function title() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Title',
            'registration' => false
        );
    }

    static function colour() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$administrator,
            'view' => userlevels::$administrator,
            'type' => 'text',
            'hrtext' => 'Colour',
            'registration' => false
        );
    }

    static function firstname() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$registered,
            'type' => 'text',
            'hrtext' => 'Firstname',
            'registration' => false
        );
    }

    static function rss_password() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$administrator,
            'view' => userlevels::$administrator,
            'type' => 'text',
            'hrtext' => 'RSS Password',
            'registration' => false
        );
    }

    static function country() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$registered,
            'type' => 'text',
            'hrtext' => 'Country',
            'registration' => false
        );
    }

    static function bio() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Biography',
            'registration' => false
        );
    }

    static function current_play() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$registered,
            'type' => 'text',
            'hrtext' => 'Current Activity',
            'registration' => false
        );
    }

    static function steam_id() {
        return array(
            'enabled' => false,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Steam I.D.',
            'registration' => false
        );
    }

    static function signature() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'text',
            'hrtext' => 'Signature',
            'registration' => false
        );
    }

    static function signature_url() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$noaccess,
            'type' => 'text',
            'hrtext' => 'Signature Link',
            'registration' => false
        );
    }

    static function avatar() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$noaccess,
            'type' => 'text',
            'hrtext' => 'Avatar',
            'registration' => false
        );
    }

    static function avatar_img() {
        return array(
            'enabled' => true,
            'self' => 'view',
            'auth' => userlevels::$noaccess,
            'view' => userlevels::$guest,
            'type' => 'image',
            'hrtext' => 'Avatar Image',
            'registration' => false
        );
    }

    static function style() {
        return array(
            'enabled' => false,
            'self' => 'view',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$guest,
            'type' => 'dropdown',
            'hrtext' => 'Style',
            'registration' => false
        );
    }

    static function longitude() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$member,
            'type' => 'text',
            'hrtext' => 'Longitude',
            'registration' => false
        );
    }

    static function latitude() {
        return array(
            'enabled' => true,
            'self' => 'auth',
            'auth' => userlevels::$moderator,
            'view' => userlevels::$member,
            'type' => 'text',
            'hrtext' => 'Latitude',
            'registration' => false
        );
    }

}

?>
