<?php

/*
  Forum page

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

class forum_page extends page {

    protected $forum_id;
    protected $page_id;
    protected $forum_name;
    protected $forum_description;
    protected $forum_read_level;
    protected $forum_level;
    protected $pages;
    protected $breadcrumb;
    protected $forum_bl;

    public function __construct() {
        parent::__construct();
        $this->forum_id = input::validate('forum_id', 'int');
        $this->page_id = input::validate('page_id', 'int');
        $this->breadcrumb = new breadcrumb();
        $this->add_text('title', 'Forum Viewer');
        $this->initialise_bl();
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if ($this->forum_id > 0) {
                $this->get_forum_details();
                $this->display_forum_details();

                $count = $this->forum_bl->count_forum_topics($this->forum_id);

                $this->pages = new paging();
                $this->pages->items_per_page = forum_config::$page_limit;
                $this->pages->total_items = $count[0]['count'];
                $this->pages->relative_url = 'viewforum.php';
                $this->pages->url_arguments = array('forum_id' => $this->forum_id);
                $this->pages->page = $this->page_id;

                $this->display_topics($this->forum_bl->get_global_topics(page::$user), 'Global Announcements');
                $this->display_topics($this->forum_bl->get_forum_by_id(page::$user, $this->forum_id, $this->page_id), 'Topics');
                $this->display_forum_details();
                $this->display_paging();
                $this->add_text('forum_name', html::clean_text($this->forum_name));
            } else {
                $this->add_text('main', "Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function initialise_bl() {
        $this->forum_bl = new forum_bl();
    }

    protected function display_new_topic() {
        return '
                <a href="' . html::gen_url('post.php', array('forum_id' => $this->forum_id)) . '">
                    <img src="' . forum_images::make_post(page::$user->get_style()) . '" alt="" />
                </a>
            ';
    }

    protected function display_paging() {
        $this->add_text('paging', $this->pages->display());
    }

    protected function get_forum_details() {
        $details = $this->forum_bl->get_forum_details($this->forum_id);
        $this->breadcrumb->add_crumb('Portal', html::gen_url('index.php'));
        $this->breadcrumb->add_crumb('Forums', html::gen_url('forums.php'));
        if (count($details) == 1) {
            if ($details[0]['forum_view_level'] <= page::$user->get_level()) {
                $this->forum_name = $details[0]['forum_name'];
                $this->breadcrumb->add_crumb(html::clean_text($this->forum_name));
                $this->forum_description = $details[0]['forum_desc'];
                $this->forum_read_level = $details[0]['forum_view_level'];
                $this->forum_level = $details[0]['forum_level'];
            } else {
                throw new Exception("Permission denied.");
            }
        } else {
            throw new Exception("Forum does not exist.");
        }
    }

    protected function display_forum_details() {
        $page = new page($this->template);
        $page->set_template('forums/forum_details');
        $page->add_text('forum_description', html::clean_text($this->forum_description));
        $page->add_text('breadcrumb_trail', $this->breadcrumb->display());
        if (page::$user->get_level() >= $this->forum_level) {
            $page->add_text('new_post', $this->display_new_topic());
        }
        $this->add_text('main', $page->display());
    }

    protected function list_topics($topics) {
        return $page->display();
    }

    protected function display_topics($topics, $header) {
        if (count($topics) > 0) {
            $topic_page = new page($this->template);

            $topic_page->set_template('forums/topic_list');

            $topic_page->add_text('header', $header);

            foreach ($topics AS $topic) {
                // add topic to the main page
                $topic_page->add_text('topic_list', $this->display_topic($topic));
            }

            $this->add_text('main', $topic_page->display());
        }
    }

    protected function display_topic($topic) {
        $read_topics = page::$user->get_read_topics();
        $mark_read = forum_images::topic(page::$user->get_style());
        if (page::$user->get_level() > userlevels::$guest && $topic['post_time'] > page::$user->get_last_visit()) {
            $mark_read = forum_images::new_topic(page::$user->get_style());
            if (isset($read_topics[$topic['topic_id']])) {
                if ($read_topics[$topic['topic_id']] >= $topic['topic_last_post_id']) {
                    $mark_read = forum_images::topic(page::$user->get_style());
                }
            }
        }

        $page = new page($this->template);
        $page->set_template('forums/topic');

        // topic details
        $page->add_text('topic_id', html::clean_text($topic['topic_id']));
        $page->add_text('topic_title', html::clean_text($topic['topic_title']));
        $page->add_text('topic_link', html::gen_url('viewtopic.php', array('topic_id' => $topic['topic_id'])));
        $page->add_text('replies', html::clean_text($topic['topic_replies']));
        $page->add_text('views', html::clean_text($topic['topic_views']));
        $page->add_text('topic_icon', '<img src="' . $mark_read . '" alt="Topic Icon" />');
        switch ($topic['topic_type']) {
            case forum_config::$announcement:
                $page->add_text('topic_type', 'Announcemnt: ');
                break;
            case forum_config::$globalannouncement:
                $page->add_text('topic_type', 'Global: ');
                break;
            case forum_config::$normal:
                $page->add_text('topic_type', '');
                break;
            case forum_config::$stick:
                $page->add_text('topic_type', 'Sticky: ');
                break;
            case forum_config::$link:
                $page->add_text('topic_type', 'Link: ');
                break;
        }

        // topic starter
        $page->add_text('formatted_poster_name', html::clean_text($topic['username'])); // add some colour based on user level
        $page->add_text('poster_link', html::gen_url('profile.php', array('user_id' => $topic['topic_poster'])));

        // last poster
        $page->add_text('last_formatted_poster', html::clean_text($topic['last_user'])); // add some colour based on user level
        $page->add_text('last_poster_link', html::gen_url('profile.php', array('user_id' => $topic['poster_id'])));

        // last post
        $page->add_text('last_post', date(forum_config::$date_format, $topic['post_time']));
        $page->add_text('last_post_link', html::gen_url('viewtopic.php', array('post_id' => $topic['topic_last_post_id'])));
        $page->add_text('last_post_icon', '');

//            url
//            topic_last_post_id
//            topic_poster
//            post_time
//            last_user
//            user_level
//            last_user_level
//            poster_id
//            colour
//            poster_colour
//            rank_colour
//            poster_rank_colour
//            topic_status

        return $page->display();
    }

}

?>