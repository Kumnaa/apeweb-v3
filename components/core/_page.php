<?php

/*
  Base page class

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

// for unit testing
if (file_exists(RELATIVE_PATH . 'config/_config.php')) {
    require(RELATIVE_PATH . 'config/_config.php');
    require(RELATIVE_PATH . 'config/messages.php');
    require(RELATIVE_PATH . 'config/userlevels.php');
    require(RELATIVE_PATH . 'config/bbcode.php');
} else {
    require('config/core/_config.php');
    require('config/core/messages.php');
    require('config/core/userlevels.php');
    require('config/core/bbcode.php');
}
// end for unit testing

config::set_timezone();

require('components/core/_templates.php');
require('components/core/apetech.php');
require('components/core/input.php');
require('components/core/smtp.php');
require('components/core/socket.php');
require('components/core/sql.php');
require('components/core/validator.php');
require('components/core/exceptions.php');
require('components/core/security_type.php');
require('components/core/businesslogic_base.php');
require('components/core/component_types.php');

// for unit testing
if (file_exists(RELATIVE_PATH . 'components/user.php')) {
    require(RELATIVE_PATH . 'components/user.php');
} else {
    require('components/core/user.php');
}
// end for unit testing

require('bl/core/user_bl.php');
require('html/core/html.php');
require('html/core/login.php');

html::$site_url = config::site_url() . RELATIVE_URL;

set_error_handler(array('apetech', 'error_handler'));

abstract class _page {

    protected $template_name = 'default';
    protected $replace_text = array();
    protected $data = array();
    protected $debug = false;
    protected $debug_list;
    protected $inner = false;
    public static $db_connection;
    public static $user;
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

    public function __construct($template = false) {
        if ($template !== false) {
            $this->template = $template;
            $this->inner = true;
        } else {
            $this->template = new template();
            _page::$db_connection = new sql_db();
            $this->action = input::validate('action', 'string');
            $this->username = input::validate('username', 'message');
            $this->password = input::validate('password', 'message');
            $this->login = input::validate('login', 'string');
            $this->confirm = input::validate('confirm', 'string');
            $this->remember = input::validate('remember', 'string');
            $this->recaptcha_challenge_field = input::validate('recaptcha_challenge_field', 'message');
            $this->recaptcha_response_field = input::validate('recaptcha_response_field', 'message');
            $this->login_error = '';
            _page::$db_connection->debug = config::debug();
            $this->perform_login = false;
            userlevels::build_permissions();
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

    public function enable_component($component) {
        switch ($component) {
            case component_types::$recaptcha:
                if (class_exists('recaptcha') == false) {
                    require('components/core/recaptcha.php');
                    require(RELATIVE_PATH . 'config/recaptcha.php');
                }
                break;

            case component_types::$forums:
                if (class_exists('forum_bl') == false) {
                    require('bl/core/forum_bl.php');
                    require(RELATIVE_PATH . 'config/forum_config.php');
                    require(RELATIVE_PATH . 'config/forum_images.php');
                    require(RELATIVE_PATH . 'config/forumlevels.php');
                }
                break;

            case component_types::$images:
                if (class_exists('images_bl') == false) {
                    require('bl/core/images_bl.php');
                    require('components/core/image_manager.php');
                }
                break;

            case component_types::$portal:
                if (class_exists('portal_bl') == false) {
                    require('bl/core/portal_bl.php');
                    require('components/core/portal/portal_columns.php');
                    require(RELATIVE_PATH . 'config/portal_images.php');
                }
                break;

            case component_types::$shoutbox:
                if (class_exists('shoutbox_bl') == false) {
                    require('bl/core/shoutbox_bl.php');
                    require('html/core/shoutbox.php');
                }
                break;

            case component_types::$calendar:
                if (class_exists('calendar_bl') == false) {
                    require('bl/core/calendar_bl.php');
                    require('html/core/calendar.php');
                }
                break;

            case component_types::$apeweb_menu:
                if (class_exists('menu_item') == false) {
                    require('components/core/menu_item.php');
                }
                break;

            case component_types::$breadcrumbs:
                if (class_exists('breadcrumb') == false) {
                    require('html/core/breadcrumb.php');
                }
                break;

            case component_types::$tables:
                if (class_exists('table') == false) {
                    require('html/core/table.php');
                }
                break;

            case component_types::$paging:
                if (class_exists('paging') == false) {
                    require('html/core/paging.php');
                }
                break;

            case component_types::$streamline:
                if (class_exists('streamline') == false) {
                    require('apis/core/streamline_api.php');
                    require(RELATIVE_PATH . 'config/streamline.php');
                }
                break;
        }
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
        if ($this->inner == false) {
            $debug = _page::$db_connection->debug;
            $this->add_debug(_page::$db_connection->sql_close());
            if ($debug == true) {
                $this->replace_text['debug'] = $this->debug_list;
            }
        }

        $html = $this->template->get_template($this->template_name);
        foreach ($this->replace_text AS $k => $v) {
            if (strtoupper($k) == 'JAVASCRIPT') {
                $v = '//<!--
                    ' . $v . '
                //-->';
            }
            $html = str_replace('<!--' . strtoupper($k) . '-->', $v, $html);
        }

        $html = preg_replace('/<!--ATTR_(.*?)-->/', '', $html);

        if ($this->inner == false) {
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
        $this->add_text('copyright', 'Powered by <a href="http://www.amplifycreative.net/" target="_blank">apetechv3</a> | &#169;2008-2012 <a href="http://www.amplifycreative.net">Amplify</a>');
        if ($this->perform_login == true && strlen($this->login) > 0) {
            if ($this->login == 'true') {
                try {
                    page::$user->login($this->username, $this->password, $this->remember);
                } catch (Exception $ex) {
                    $this->login_error = $ex->getMessage();
                }
            } else if ($this->login == 'logout') {
                page::$user->logout();
            } else {
                page::$user->grab_info();
            }
        } else {
            page::$user->grab_info();
        }
    }

    abstract protected function post_action();

    abstract protected function notice($message);

    abstract protected function initialise_bl();

    abstract protected function create_user();

    abstract protected function please_login($element = 'main');
}

?>