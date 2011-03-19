<?php

/*
  Base config class

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

class config {

    static $user_type = 'web_user';

    static $error_destination = '';
    
    static function completed_login_url() {
        return config::site_url();
    }

    static function site_protocol() {
        if (isset($_SERVER['HTTPS'])) {
            $site_protocol = 'https://';
        } else {
            $site_protocol = 'http://';
        }
        return $site_protocol;
    }

    static function site_url() {
        return config::site_protocol() . apetech::server_name() . '/';
    }

    static function template() {
        return 'default';
    }

    static function db_engine() {
        return 'mysql'; // other dbs supported are 'postgresql' and 'mssql'
    }

    static function db_host() {
        return 'localhost';
    }

    static function db_username() {
        return 'root';
    }

    static function db_password() {
        return 'password';
    }

    static function db_database() {
        return 'apetech_template';
    }

    static function db_instance() {
        return 'SQLEXPRESS'; // only used for mssql db connections
    }

    static function smtp_server() {
        return '';
    }

    static function smtp_post() {
        return '';
    }

    static function smtp_port() {
        return '';
    }

    static function smtp_login() {
        return '';
    }

    static function smtp_password() {
        return '';
    }

    static function smtp_sender() {
        return '';
    }

    static function domain() {
        if (apetech::server_name() == '127.0.0.1') {
            $domain = 'localhost';
        } else {
            $domain = apetech::server_name();
        }
        return $domain;
    }

    static function salt() {
        return '1234567890';
    }

    static function cookie_name() {
        return 'apetech';
    }

    static function document_root() {
        return '';
    }

    static function debug() {
        return true;
    }

    static function strict_mode() {
        error_reporting(E_ALL);
    }

    static function set_timezone() {
        date_default_timezone_set('Europe/London');
    }
    
    static function allow_registration() {
        return true;
    }

}

?>