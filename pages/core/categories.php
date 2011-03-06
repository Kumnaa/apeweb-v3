<?php

/*
  Forum categories page

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

class category_page extends page {

    protected $forum_bl;

    public function __construct() {
        parent::__construct();
        $this->add_text('title', 'Category Viewer');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            $fbl = new forum_bl();
            $this->display_forums($fbl->get_forum_list(page::$user->get_level()));
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function display_forums($forum_list) {
        if (count($forum_list) > 0) {
            $current_category = $forum_list[0]['cat_id'];

            $page = new page($this->template);
            $page->set_template('forums/category');
            $page->add_text('category_name', '<div class="category_title">' . $forum_list[0]['cat_title'] . '</div>');
            foreach ($forum_list AS $forum) {
                if ($forum['cat_id'] != $current_category) {
                    $this->add_text('main', $page->display());
                    $page = new page($this->template);
                    $page->set_template('forums/category');
                    $page->add_text('category_name', '<div class="category_title">' . $forum['cat_title'] . '</div>');
                }
                $page->add_text('category_list', $this->display_forum($forum));
                $current_category = $forum['cat_id'];
            }
            $this->add_text('main', $page->display());
        } else {
            throw new Exception("No forums");
        }
    }

    protected function display_forum($forum) {
        $page = new page($this->template);
        $page->set_template('forums/forum');
        if (isset(page::$user->unread_forums[$forum['forum_id']])) {
            $forum_icon = '<img src="' . forum_images::new_forum(page::$user->get_style()) . '" alt="Unread" />';
        } else {
            $forum_icon = '<img src="' . forum_images::forum(page::$user->get_style()) . '" alt="Read" />';
        }
        $page->add_text('forum_icon', $forum_icon);
        $page->add_text('forum_title', html::clean_text($forum['forum_name']));
        $page->add_text('forum_description', html::clean_text($forum['forum_desc'], true, true));
        $page->add_text('forum_link', html::gen_url('viewforum.php', array('forum_id' => $forum['forum_id'])));
        $page->add_text('posts', html::clean_text($forum['forum_posts']));
        $page->add_text('topics', html::clean_text($forum['forum_topics']));
        $page->add_text('last_post', date(forum_config::$date_format, $forum['post_time']));
        $page->add_text('last_formatted_poster', html::clean_text($forum['username'])); // add some colour based on user level
        $page->add_text('last_poster_link', html::gen_url('profile.php', array('user_id' => $forum['poster_id'])));
        return ($page->display());
    }

}

?>
