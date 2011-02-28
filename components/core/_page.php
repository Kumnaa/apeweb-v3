<?php

/*
  Base page class

  @author Ben Bowtell

  @date 22-Nov-2009

  (c) 2009 by http://www.apetechnologies.net/

  contact: ben@apetechnologies.net

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

require('bl/core/user_bl.php');
require('bl/core/forum_bl.php');
require('bl/core/images_bl.php');
require('bl/core/install.php');
require('components/core/_templates.php');
require('components/core/apetech.php');
require('components/core/image_manager.php');
require('components/core/input.php');
require('components/core/menu_item.php');
require('components/core/smtp.php');
require('components/core/socket.php');
require('components/core/sql.php');
require('components/core/validator.php');
require('components/core/exceptions.php');
require('components/core/security_type.php');
require('html/core/calendar.php');
require('html/core/breadcrumb.php');
require('html/core/html.php');
require('html/core/login.php');
require('html/core/paging.php');
require('html/core/table.php');
require('config/_config.php');
require('config/messages.php');

set_error_handler(array('apetech', 'error_handler'));

abstract class _page {

    protected $template_name = 'default';
    protected $replace_text = array();
    protected $data = array();
    protected $debug = false;
    protected $debug_list;
    protected $inner = false;
    protected $db;
    protected $template;
    protected $action;
    protected $confirm;
    protected $perform_action = true;
    protected $perform_login;
    protected $login_error;
    protected $login;
    protected $username;
    protected $password;
    protected $remember;
    protected $recaptcha_challenge_field;
    protected $recaptcha_response_field;
    public $user;

    public function __construct($template = false) {
        if ($template !== false) {
            $this->template = $template;
            $this->inner = true;
        } else {
            $this->template = new template();
            $this->db = new sql_db();
            $this->action = input::validate('action', 'string');
            $this->username = input::validate('username', 'message');
            $this->password = input::validate('password', 'message');
            $this->login = input::validate('login', 'string');
            $this->confirm = input::validate('confirm', 'string');
            $this->remember = input::validate('remember', 'string');
            $this->recaptcha_challenge_field = input::validate('recaptcha_challenge_field', 'message');
            $this->recaptcha_response_field = input::validate('recaptcha_response_field', 'message');
            $this->login_error = '';
            $this->db->debug = config::debug();
            $this->perform_login = false;
            $this->initialise_bl();
        }
    }

    public function execute() {
        $this->create_user();
        $this->pre_action();
        if ($this->perform_action == true) {
            $this->action();
        }

        $this->post_action();
        $this->generate_display();
    }

    public function redirect($url) {
        header('Location: ' . $url);
    }

    public function set_template($template) {
        $this->template_name = $template;
    }

    public function add_debug($text) {
        $this->debug_list .= $text;
    }

    public function add_text($var, $text) {
        $var = strtolower($var);
        if (!isset($this->replace_text[$var])) {
            $this->replace_text[$var] = $text;
        } else {
            $this->replace_text[$var] .= $text;
        }
    }

    public function add_data($var, $data) {
        $var = strtolower($var);
        $this->data[$var] = $data;
    }

    public function display_block($text, $title = '') {
        $page = new page($this->template);
        $page->set_template('styled_block');
        $page->add_text('title', $title);
        $page->add_text('text', $text);
        return $page->display();
    }

    public function display() {
        if ($this->inner === false) {
            $debug = $this->db->debug;
            $this->add_debug($this->db->sql_close());
            if ($debug == true) {
                $this->replace_text['debug'] = $this->debug_list;
            }
        }

        $html = $this->template->get_template($this->template_name);
        foreach ($this->replace_text AS $k => $v) {
            $html = str_replace('<!--' . strtoupper($k) . '-->', $v, $html);
        }

        $html = preg_replace('/<!--ATTR_(.*?)-->/', '', $html);

        if ($this->inner === false) {
            echo $html;
        } else {
            return $html;
        }
    }

    public function display_csv() {
        if (isset($this->replace_text['text'])) {
            header("Content-Type: text/csv");
            header('Content-Disposition: attachment; filename="export.csv"');
            echo $this->replace_text['text'];
        }
    }

    public function display_plain() {
        if (isset($this->replace_text['html'])) {
            echo $this->replace_text['html'];
        }
    }

    public function display_png() {
        if (isset($this->data['png'])) {
            header("Content-Type: image/png");
            ImagePNG($this->data['png']);
            imagedestroy($this->data['png']);
        }
    }

    public function display_xml() {
        header("Content-Type: text/xml");
        if (isset($this->replace_text['xml'])) {
            echo $this->replace_text['xml'];
        } else {
            echo '<error />';
        }
    }

    protected function pre_action() {
        $this->add_text('copyright', 'Powered by <a href="http://www.apegaming.net/" target="_blank">apetechv3</a> | &#169;2008-2011 <a href="http://www.amplifycreative.net">Amplify</a>');
        if ($this->perform_login == true && strlen($this->login) > 0) {
            if ($this->login == 'true') {
                try {
                    $this->user->login($this->username, $this->password, $this->remember);
                } catch (Exception $ex) {
                    $this->login_error = $ex->getMessage();
                }
            } else if ($this->login == 'logout') {
                $this->user->logout();
            } else {
                $this->user->grab_info();
            }
        } else {
            $this->user->grab_info();
        }
    }

    abstract protected function post_action();

    abstract protected function notice($message);

    abstract protected function initialise_bl();

    abstract protected function create_user();

    abstract protected function please_login($element = 'main');
}

?>