<?php

/*
  Profile page

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
if (file_exists('components/page.php')) {
    require_once('components/page.php');
} else {
    require_once('components/core/page.php');
}

// end for unit testing

class profile_page extends page {

    private $user_id;
    private $profile;
    private $full_profile;
    private $profile_settings;
    private $status_list;
    private $visible_list;
    private $access_list;
    private $is_submittable;

    public function __construct() {
        parent::__construct();

        $this->status_list = array(
            '2' => 'Active',
            '1' => 'Banned',
            '4' => 'Email Activated'
        );

        $this->visible_list = array(
            '0' => 'No',
            '1' => 'Yes'
        );

        $this->access_list = userlevels::access_list();

        $this->is_submittable = false;

        $this->user_id = input::validate('user_id', 'int');
        $this->profile = input::validate('profile', 'array');

        $this->profile_settings = profile_config::profile_list();

        $this->add_text('title', 'Profile Viewer');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if (is_array($this->profile) && count($this->profile) > 0) {
                foreach ($this->profile AS $key => $value) {
                    $this->profile[$key] = input::cleanup($value, $this->profile_settings[$key]['datatype']);
                }

                $this->save_profile();
            }

            if (profile_config::access_level() <= page::$user->get_level()) {
                if ($this->user_id == 0 && page::$user->get_user_id() > 0) {
                    $this->user_id = page::$user->get_user_id();
                }

                if ($this->user_id > 0) {
                    $ubl = new user_bl();
                    $this->display_profile($ubl->get_full_profile_details($this->user_id));
                } else {
                    throw new Exception("Invalid user id.");
                }
            } else {
                throw new Exception("Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function save_profile() {
        $ubl = new user_bl();
        $old_user = $ubl->get_full_profile_details($this->user_id);
        $parameters = array(':id' => array('value' => $this->user_id, 'type' => PDO::PARAM_INT));
        $fields = array();
        foreach ($this->profile AS $key => $value) {
            if ($this->profile_settings[$key]['auth'] <= page::$user->get_level() || ($this->user_id == page::$user->get_user_id() && $this->profile_settings[$key]['self'] == 'auth' && $this->profile_settings[$key][$this->profile_settings[$key]['self']] <= page::$user->get_level())) {
                if (
                        $key != 'user_level'
                        ||
                        ($this->user_id != page::$user->get_user_id() && $old_user[0]['user_level'] < page::$user->get_level() && $value < page::$user->get_level())
                ) {
                    switch ($this->profile_settings[$key]['datatype']) {
                        case "int":
                        case "float":
                            $pdotype = PDO::PARAM_INT;
                            break;

                        default:
                            $pdotype = PDO::PARAM_STR;
                            break;
                    }
                    $fields[] = $key . " = :" . $key;
                    $parameters = array_merge($parameters, array(':' . $key => array('value' => $value, 'type' => $pdotype)));
                }
            }
        }

        $ubl->update_profile(implode(", ", $fields), $parameters);
    }

    protected function display_profile($profile_list) {
        if (count($profile_list) > 0) {
            $this->full_profile = $profile_list[0];
            $page = new page($this->template);
            $page->set_template('profile');
            $page->add_text('username', html::clean_text($this->full_profile['username']));
            $page->add_text('profile_url', html::gen_url('profile.php', array('user_id' => $this->user_id)));

            foreach ($this->profile_settings AS $key => $value) {
                if ($value['enabled'] == true) {
                    if (
                            (
                                ($key == 'user_level' && page::$user->get_user_id() != $this->user_id)
                                ||
                                $key != 'user_level'
                            )
                            &&
                            (
                                $value['auth'] <= page::$user->get_level() 
                                ||
                                (
                                    $this->user_id == page::$user->get_user_id()
                                    &&
                                    $value['self'] == 'auth'

                                )
                            )
                    ) {
                        $page->add_text('profile', $this->display_profile_edit($key));
                    } else if ($value['view'] <= page::$user->get_level() || ($this->user_id == page::$user->get_user_id() && $value['self'] == 'view' && $value[$value['self']] <= page::$user->get_level())) {
                        $page->add_text('profile', $this->display_profile_view($key));
                    }
                }
            }
            if ($this->is_submittable == true) {
                $page->add_text('profile', '<input type="submit" value="Save" />');
            }
            $this->add_text('main', $page->display());
        } else {
            throw new Exception("User not found");
        }
    }

    protected function display_profile_edit($key) {
        $page = new page($this->template);
        $page->set_template('profile_row');
        $page->add_text('profile_title', html::clean_text($this->profile_settings[$key]['hrtext']));
        switch ($this->profile_settings[$key]['type']) {
            case "text":
                $page->add_text('profile_text', '<input class="profile_input" type="text" name="profile[' . html::clean_text($key) . ']" value="' . html::clean_input_text($this->full_profile[$key]) . '" />');
                break;

            case "textbox":
                $page->add_text('profile_text', '<textarea class="profile_textbox" name="profile[' . html::clean_text($key) . ']">' . html::clean_input_text($this->full_profile[$key]) . '</textarea>');
                break;

            case "image":
                $page->add_text('profile_text', $this->display_image($key));
                break;

            case "dropdown":
                $text = '<select name="profile[' . html::clean_text($key) . ']">';
                switch ($key) {
                    case "status":
                        foreach ($this->status_list AS $ik => $iv) {
                            if ($ik == $this->full_profile[$key]) {
                                $selected = ' selected="selected"';
                            } else {
                                $selected = '';
                            }

                            $text .= '<option' . $selected . ' value="' . $ik . '">' . $iv . '</option>';
                        }

                        break;

                    case "visible":
                        foreach ($this->visible_list AS $ik => $iv) {
                            if ($ik == $this->full_profile[$key]) {
                                $selected = ' selected="selected"';
                            } else {
                                $selected = '';
                            }

                            $text .= '<option' . $selected . ' value="' . $ik . '">' . $iv . '</option>';
                        }
                        break;

                    case "style":
                        $text .= '';
                        break;

                    case "user_level":
                        foreach ($this->access_list AS $ik => $iv) {
                            if (page::$user->get_level() > $ik) {
                                if ($ik == $this->full_profile[$key]) {
                                    $selected = ' selected="selected"';
                                } else {
                                    $selected = '';
                                }

                                $text .= '<option' . $selected . ' value="' . $ik . '">' . $iv . '</option>';
                            }
                        }
                        break;
                }
                $text .= '</select>';

                $page->add_text('profile_text', $text);
                break;
        }

        $this->is_submittable = true;
        return $page->display();
    }

    protected function display_profile_view($key) {
        $page = new page($this->template);
        $page->set_template('profile_row');
        $page->add_text('profile_title', html::clean_text($this->profile_settings[$key]['hrtext']));
        switch ($this->profile_settings[$key]['type']) {
            case "text":
                switch ($key) {
                    case "user_lastrefresh":
                        $text = date(forum_config::$date_format, $this->full_profile[$key]);
                        break;

                    case "signature":
                        if (strlen($this->full_profile['signature_url']) > 0) {
                            $text = '<a href="' . html::clean_input_text($this->full_profile['signature_url']) . '">' . html::clean_text($this->full_profile[$key]) . '</a>';
                        } else {
                            $text = html::clean_text($this->full_profile[$key]);
                        }
                        break;

                    default:
                        $text = html::clean_text($this->full_profile[$key]);
                        break;
                }

                if ($text == "") {
                    $text = "&#160;";
                }

                $page->add_text('profile_text', $text);
                break;
            case "dropdown":
                switch ($key) {
                    case "status":
                        $text = $this->status_list[$this->full_profile[$key]];
                        break;

                    case "visible":
                        $text = $this->visible_list[$this->full_profile[$key]];
                        break;

                    case "style":
                        $text = '';
                        break;

                    case "user_level":
                        $text = $this->access_list[$this->full_profile[$key]];
                        break;
                }

                if ($text == "") {
                    $text = "&#160;";
                }

                $page->add_text('profile_text', $text);
                break;

            case "image":
                $page->add_text('profile_text', $this->display_image($key));
                break;
        }
        return $page->display();
    }

    protected function display_image($key) {
        switch ($key) {
            case "avatar_img":
                $image_extension = explode("/", $this->full_profile['ava_h']);
                if (count($image_extension) > 1) {
                    return '<img src="' . forum_config::image_warehouse_url() . html::clean_input_text($this->full_profile['avatar']) . '.' . $image_extension[1] . '" alt="' . html::clean_text($key) . '" />';
                } else {
                    return '';
                }
                break;

            case "pic_img":
                $image_extension = explode("/", $this->full_profile['pic_h']);
                if (count($image_extension) > 1) {
                    return '<img src="' . forum_config::image_warehouse_url() . html::clean_input_text($this->full_profile['pic']) . '.' . $image_extension[1] . '" alt="' . html::clean_text($key) . '" />';
                } else {
                    return '';
                }
                break;

            default:
                return '<img src="' . html::clean_input_text($key) . '" alt="' . html::clean_text($key) . '" />';
                break;
        }
    }

}

?>