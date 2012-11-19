<?php

/*
  Administrate Portal page

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
if (file_exists(RELATIVE_PATH . 'components/page.php')) {
    require_once(RELATIVE_PATH . 'components/page.php');
} else {
    require_once('components/core/page.php');
}

// end for unit testing

class administrate_portal_page extends page {

    private $column;
    private $direction;
    private $message;
    private $title;
    private $tag;
    private $id;
    private $view_level;
    private $type;
    private $portal_bl;
    private $access_list;

    public function __construct() {
        parent::__construct();
        $this->enable_component(component_types::$forums);
        $this->enable_component(component_types::$portal);
        $this->enable_component(component_types::$tables);
        $this->portal_bl = new portal_bl();
        $side = input::validate('side', 'string');
        switch ($side) {
            case "main":
                $this->column = 1;
                break;
            case "right":
                $this->column = 2;
                break;
            case "bottom":
                $this->column = 3;
                break;

            default:
                $this->column = -1;
                break;
        }

        $this->direction = input::validate('direction', 'string');
        $this->message = input::validate('message', 'message');
        $this->title = input::validate('title', 'message');
        $this->tag = input::validate('tag', 'message');
        $this->id = input::validate('id', 'int');
        $this->view_level = input::validate('view_level', 'int');
        $this->type = input::validate('type', 'string');

        $this->access_list = userlevels::access_list(true);
        $this->add_text('title', 'Portal Administration');
        $this->initialise_bl();
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if (page::$user->get_level() >= userlevels::$administrator) {
                switch ($this->action) {
                    case "add":
                        $this->add_element();
                        break;
                    case "edit":
                        $this->edit_element();
                        break;
                    case "delete":
                        $this->delete_element();
                        break;
                    default:
                        $this->default_action();
                        break;
                }
            } else {
                throw new Exception("Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function edit_element() {
        $contents = '';
        if ($_POST) {
            switch ($this->type) {
                case "announcements":
                case "calendar":
                case "lastposts":
                case "suggestions":
                case "shoutbox":
                case "stats":
                case "post":
                    $_type = $this->type;
                    break;

                default:
                    $_type = 'text';
                    break;
            }

            $this->portal_bl->update_element($this->id, $this->message, $this->title, $_type, $this->view_level);

            $contents .= html::alert('Updated');
        }

        $element = $this->portal_bl->get_element($this->id);
        if ($element != null) {
            $element_types = array('post' => 'From Post', 'stats' => 'Current Stats', 'text' => 'Text Block', 'announcements' => 'Announcements', 'suggestions' => 'Suggestion Box', 'calendar' => 'Calendar', 'lastposts' => 'Recent Posts', 'shoutbox' => 'Shoutbox');
            $contents .= '<span class="head_text">' . html::clean_text($element['tag'], false, false) . '</span><br /><br />';
            $contents .= '<form action="' . html::gen_url('administrate_portal.php', array('action' => 'edit', 'id' => $this->id)) . '" method="post"><div>';
            $contents .= '<span class="bold italic">Title</span> <input name="title" type="text" value="' . html::clean_text($element['title'], false, false) . '" /><br /><br />';
            $contents .= '<span class="bold italic">View Level</span> <select name="view_level">';

            foreach ($this->access_list AS $ik => $iv) {
                if ($ik == $element['view_level']) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }

                $contents .= '<option' . $selected . ' value="' . $ik . '">' . $iv . '</option>';
            }

            $contents .= '</select><br /><br />';
            $contents .= '<span class="bold italic">Box Type</span> <select name="type">';
            foreach ($element_types AS $key => $val) {
                if ($element['type'] == $key) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = '';
                }

                $contents .= '<option value="' . $key . '"' . $selected . '>' . $val . '</option>';
            }

            $contents .= '</select><br /><br />';
            $contents .= '<textarea name="message" rows="17" cols="80">' . html::clean_text($element['message'], false, false) . '</textarea><br /><br />';
            $contents .= '<br /><br /><input type="submit" value="Update" /></div></form>';
        }

        $this->add_text('main', $this->display_block($contents, 'Portal Administration'));
    }

    protected function add_element() {
        if (strlen($this->tag) > 0) {
            $this->portal_bl->add_element($this->tag, $this->column);
            $this->default_action('Element added');
        }
    }

    protected function delete_element() {
        if ($this->confirm == 'Confirm') {
            $this->portal_bl->delete_element($this->id);
            $this->default_action('Element removed');
            return;
        } else {
            $contents = 'Are you sure you want to delete this element?<br /><br /><form action="' . html::gen_url('administrate_portal.php', array('action' => 'delete', 'id' => $this->id)) . '" method="post">
                <input type="submit" value="Confirm" name="confirm" />
                </form>';
        }

        $this->add_text('main', $this->display_block($contents, "Portal Administration"));
    }

    protected function default_action($contents = '') {
        if (strlen($contents) > 0) {
            $contents = '<span class="reg_warn">' . $contents . '</span>';
        }

        if ($this->direction != '' && $this->id > 0) {
            $current_position = $this->portal_bl->get_current_element_position($this->id);
            switch ($this->direction) {
                case "up":
                    $previous = $this->portal_bl->get_previous_element($current_position, $this->column);
                    if ($previous != null) {
                        $this->portal_bl->swap_element_positions($previous, $this->id);
                    }
                    break;
                case "down":
                    $next = $this->portal_bl->get_next_element($current_position, $this->column);
                    if ($next != null) {
                        $this->portal_bl->swap_element_positions($this->id, $next);
                    }
                    break;
            }
        }

        // main column
        $contents .= '<div class="head_text">Center Column</div>';
        $contents .= $this->display_elements('main', $this->portal_bl->get_column_elements(1));
        $contents .= '<br /><br ><span class="bold">Add Element:</span><br /><form action="' . html::gen_url('administrate_portal.php', array('action' => 'add', 'side' => 'main')) . '" method="post"><div>
                Box Name: <input type="text" name="tag" /> <input type="submit" value="Add" />
                </div></form>';

        // right column
        $contents .= '<br /><br /><div class="head_text">Right Column</div>';
        $contents .= $this->display_elements('main', $this->portal_bl->get_column_elements(2));
        $contents .= '<br /><br ><span class="bold">Add Element:</span><br /><form action="' . html::gen_url('administrate_portal.php', array('action' => 'add', 'side' => 'right')) . '" method="post"><div>
                Box Name: <input type="text" name="tag" /> <input type="submit" value="Add" />
                </div></form>';

        // bottom column
        $contents .= '<br /><br /><div class="head_text">Bottom Panel</div>';
        $contents .= $this->display_elements('main', $this->portal_bl->get_column_elements(3));

        $contents .= '<br /><br ><span class="bold">Add Element:</span><br /><form action="' . html::gen_url('administrate_portal.php', array('action' => 'add', 'side' => 'bottom')) . '" method="post"><div>
                Box Name: <input type="text" name="tag" /> <input type="submit" value="Add" />
                </div></form>';

        $this->add_text('main', $this->display_block($contents, "Portal Administration"));
    }

    protected function display_elements($title, $column_elements) {
        $table = new table();
        $table->add_header(array('', 'Element Name', 'Move', 'Edit'));
        if ($column_elements != null) {
            foreach ($column_elements AS $element) {
                $table->add_data(array(
                    '<a href="' . html::gen_url('administrate_portal.php', array('action' => 'delete', 'id' => $element['id'])) . '"><img src="' . forum_images::delete_icon(page::$user->get_style()) . '" alt="[x]" /></a>',
                    html::clean_text($element['tag']),
                    '<a href="' . html::gen_url('administrate_portal.php', array('direction' => 'up', 'id' => $element['id'], 'side' => 'main')) . '">[Up]</a> - <a href="' . html::gen_url('administrate_portal.php', array('direction' => 'down', 'id' => $element['id'], 'side' => 'main')) . '">[Down]</a>',
                    '<a href="' . html::gen_url('administrate_portal.php', array('action' => 'edit', 'id' => $element['id'])) . '">[Edit]</a>'
                ));
            }
        }

        return $table->v_display();
    }

}

?>