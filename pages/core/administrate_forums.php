<?php

/*
  Administrate Forums page

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

class administrate_forums_page extends page
{

    private $forum_bl;
    private $forum_id;
    private $category_id;
    private $direction;
    private $category_name;

    public function __construct()
    {
        parent::__construct();
        $this->enable_component(component_types::$forums);
        $this->enable_component(component_types::$breadcrumbs);
        $this->enable_component(component_types::$tables);
        $this->forum_id = input::validate('forum_id', 'int');
        $this->category_id = input::validate('category_id', 'int');
        $this->direction = input::validate('direction', 'string');
        $this->category_name = input::validate('category_name', 'message');

        $this->breadcrumb = new breadcrumb();
        $this->add_text('title', 'Forum Administration');
        $this->forum_bl = new forum_bl();
        $this->initialise_bl();
    }

    public function generate_display()
    {
        $this->display();
    }

    protected function action()
    {
        try {
            if (page::$user->get_level() >= userlevels::$administrator) {
                if ($this->category_id > 0 && $this->action != 'move' && $this->action != 'delete') {

                } else if ($this->forum_id > 0) {

                } else {
                    $this->administrate_categories();
                }
            } else {
                throw new Exception("Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function administrate_categories()
    {
        switch ($this->action) {
            case "delete":
                try {
                    $this->forum_bl->delete_category($this->category_id);
                } catch (Exception $ex) {
                    $this->add_text('main', $ex->getMessage());
                }
                $this->category_default();
                break;
            case "new":
                try {
                    $this->forum_bl->add_category($this->category_name);
                } catch (Exception $ex) {
                    $this->add_text('main', $ex->getMessage());
                }
                $this->category_default();
                break;
            case "move":
                switch ($this->direction) {
                    case "up":
                        try {
                            $this->forum_bl->move_category_up($this->category_id);
                        } catch (Exception $ex) {
                            $this->add_text('main', $ex->getMessage());
                        }
                        $this->category_default();
                        break;
                    case "down":
                        try {
                            $this->forum_bl->move_category_down($this->category_id);
                        } catch (Exception $ex) {
                            $this->add_text('main', $ex->getMessage());
                        }
                        $this->category_default();
                        break;
                }
                break;

            default:
                $this->category_default();
                break;
        }
    }

    protected function category_default()
    {
        $categories = $this->forum_bl->get_categories();
        if (is_array($categories) && count($categories) > 0) {
            $table = new table('', 'categories');
            $table->add_aligns(array('left', 'right', 'right'));
            $table_header = array(
                'Name',
                'Position',
                'Delete');
            $table->add_header($table_header);

            foreach ($categories AS $category) {
                $data = array(
                    html::clean_text($category['cat_title']),
                    '<a href="' . html::gen_url('administrate_forums.php', array('action' => 'move', 'direction' => 'up', 'category_id' => html::clean_text($category['cat_id']))) . '">[Up]</a> - <a href="' . html::gen_url('administrate_forums.php', array('action' => 'move', 'direction' => 'down', 'category_id' => html::clean_text($category['cat_id']))) . '">[Down]</a>',
                    '<a href="' . html::gen_url('administrate_forums.php', array('action' => 'delete', 'category_id' => html::clean_text($category['cat_id']))) . '">[Delete]</a>'
                );

                $table->add_data($data);
            }

            $this->add_text('main', $table->v_display());

            $this->add_text('main', '<form action="' . html::gen_url('administrate_forums.php', array('action' => 'new')) . '" method="post">
                <input type="text" name="category_name" value="" /><br />
                <input type="submit" value="Add" />
            </form>
            ');
        }
    }
}

?>