<?php

/*
  Forum topic page

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

class topic_page extends page {

    protected $topic_id;
    protected $post_id;
    protected $page_id;
    protected $forum_view_level;
    protected $forum_level;
    protected $forum_post_level;
    protected $topic_title;
    protected $post_count;
    protected $breadcrumb;
    protected $forum_bl;

    public function __construct() {
        $this->enable_component(component_types::$forums);
        $this->enable_component(component_types::$paging);
        $this->enable_component(component_types::$breadcrumbs);
        parent::__construct();
        $this->topic_id = input::validate('topic_id', 'int');
        $this->post_id = input::validate('post_id', 'int');
        $this->page_id = input::validate('page_id', 'int');
        $this->breadcrumb = new breadcrumb();
        $this->initialise_bl();
        $this->add_text('title', 'Topic Viewer');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if ($this->topic_id > 0) {
                $this->generate_topic_by_topic_id();
            } else if ($this->post_id > 0) {
                $this->generate_topic_by_post_id();
            } else {
                throw new Exception("Invalid topic.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function initialise_bl() {
        $this->forum_bl = new forum_bl();
    }

    protected function display_new_post() {
        return '
                <a href="' . html::gen_url('post.php', array('topic_id' => $this->topic_id)) . '">
                    <img src="' . forum_images::make_post(page::$user->get_style()) . '" alt="" />
                </a>
            ';
    }

    protected function generate_topic_by_topic_id() {
        if (page::$user->get_level() >= userlevels::$administrator) {
            $post_count = $this->forum_bl->count_posts_in_topic($this->topic_id, 2);
        } else {
            $post_count = $this->forum_bl->count_posts_in_topic($this->topic_id, 1);
        }

        $this->breadcrumb->add_crumb('Portal', html::gen_url('index.php'));
        $this->breadcrumb->add_crumb('Forum', html::gen_url('forums.php'));
        if ($post_count > 0) {
            $this->post_count = $post_count;
            $topic_details = $this->forum_bl->get_topic_details($this->topic_id);
            $this->breadcrumb->add_crumb(html::clean_text($topic_details[0]['forum_name']), html::gen_url('viewforum.php', array('forum_id' => $topic_details[0]['forum_id'])));
            $this->forum_level = $topic_details[0]['forum_level'];
            $this->forum_view_level = $topic_details[0]['forum_view_level'];
            $this->forum_post_level = $topic_details[0]['forum_post_level'];
            $this->topic_title = $topic_details[0]['topic_title'];
            $this->breadcrumb->add_crumb(html::clean_text($this->topic_title));
            if ($this->forum_view_level <= page::$user->get_level()) {
                $this->display_topic();
            }
        } else {
            throw new Exception("Topic not found.");
        }
    }

    protected function generate_topic_by_post_id() {
        $topic_id = $this->forum_bl->get_topic_id_from_post_id($this->post_id);
        if ($topic_id > 0) {
            $this->topic_id = $topic_id;
            $this->generate_topic_by_topic_id();
        } else {
            throw new Exception("Topic not found.");
        }
    }

    protected function display_topic_details() {
        $page = new page($this->template);
        $page->set_template('forums/topic_details');
        $page->add_text('breadcrumb_trail', $this->breadcrumb->display());
        if (page::$user->get_level() >= $this->forum_post_level) {
            $page->add_text('new_post', $this->display_new_post());
        }
        $this->add_text('main', $page->display());
    }

    protected function display_topic() {
        $this->pages = new paging();
        $this->pages->items_per_page = forum_config::$page_limit;
        $this->pages->total_items = $this->post_count;
        $this->pages->relative_url = 'viewtopic.php';
        $this->pages->url_arguments = array('topic_id' => $this->topic_id);
        $this->pages->page = $this->page_id;

        $this->display_topic_details();
        $this->display_posts();
        $this->display_topic_details();
        $this->display_paging();
        $this->add_text('topic_name', html::clean_text($this->topic_title));
    }

    protected function display_posts() {
        if (page::$user->get_level() >= userlevels::$administrator) {
            $posts = $this->forum_bl->get_topic_posts($this->topic_id, $this->page_id, 2);
        } else {
            $posts = $this->forum_bl->get_topic_posts($this->topic_id, $this->page_id, 1);
        }

        if (count($posts) > 0) {
            $last_post_id = $posts[count($posts) - 1]['post_id'];
            $last_post_time = $posts[count($posts) - 1]['post_time'];
            $this->mark_last_post_read($last_post_id, $last_post_time);
            $this->forum_bl->update_topic_views($this->topic_id);
            foreach ($posts AS $post) {
                $this->add_text('main', $this->display_post($post) . '<br />');
            }
        } else {
            throw new Exception("No posts on this page.");
        }
    }

    protected function mark_last_post_read($post_id, $post_time) {
        $read_topics = $this->forum_bl->get_read_topics(page::$user->get_user_id(), page::$user->get_last_visit());
        if (array_key_exists($this->topic_id, $read_topics)) {
            if ($read_topics[$this->topic_id] < $post_id) {
                $this->forum_bl->update_last_read_post_id(page::$user->get_user_id(), $this->topic_id, $post_id);
            }
        } else {
            $this->forum_bl->update_last_read_post_id(page::$user->get_user_id(), $this->topic_id, $post_id);
        }
    }

    protected function display_post($post) {
        $mark_read = forum_images::mini_post(page::$user->get_style());
        $read_topics = $this->forum_bl->get_read_topics(page::$user->get_user_id(), page::$user->get_last_visit());
        if (page::$user->get_level() > userlevels::$guest && $post['post_time'] > page::$user->get_last_visit()) {
            $mark_read = forum_images::new_mini_post(page::$user->get_style());
            if (isset($read_topics[$post['topic_id']])) {
                if ($read_topics[$post['topic_id']] >= $post['post_id']) {
                    $mark_read = forum_images::mini_post(page::$user->get_style());
                }
            }
        }

        $security = new security_type();

        if ((page::$user->get_level() >= userlevels::$moderator || page::$user->get_user_id() == $post['poster_id']) && page::$user->get_level() >= $this->forum_level) {
            $security->AllowEdit(true);
        }

        if (page::$user->get_level() >= userlevels::$moderator && page::$user->get_level() >= $this->forum_level) {
            $security->AllowDelete(true);
        }

        if (page::$user->get_level() >= $this->forum_post_level) {
            $security->AllowAdd(true);
        }

        $page = new page($this->template);
        $page->set_template('forums/post');

        // post details
        $page->add_text('post_id', html::clean_text($post['post_id']));
        $page->add_text('post_text', html::clean_text($post['post_text'], true, true));
        $page->add_text('poster_name', html::clean_text($post['username']));
        $page->add_text('poster_join_date', date(forum_config::$date_format, $post['reg_date']));
        $page->add_text('post_icon', $mark_read);
        $page->add_text('post_time', date(forum_config::$date_format, $post['post_time']));
        $page->add_text('poster_avatar', $this->display_avatar($post));
        $page->add_text('post_url', html::gen_url('viewtopic.php', array('post_id' => $post['post_id']), false, '#p_' . $post['post_id']));
        $this->add_profile_link($page, $post);

        // security
        if ($security->AllowEdit() == true) {
            $page->add_text('edit', html::gen_link(html::gen_url('post.php', array('action' => 'edit', 'post_id' => $post['post_id'])), html::gen_image(forum_images::edit_icon(page::$user->get_style()))));
        }

        if ($security->AllowAdd() == true) {
            $page->add_text('quote', html::gen_link(html::gen_url('post.php', array('action' => 'quote', 'post_id' => $post['post_id'])), html::gen_image(forum_images::quote_icon(page::$user->get_style()))));
        }

        if ($security->AllowDelete() == true) {
            $page->add_text('delete', html::gen_link(html::gen_url('post.php', array('action' => 'delete', 'post_id' => $post['post_id'])), html::gen_image(forum_images::delete_icon(page::$user->get_style()))));
        }

        return $page->display();
    }

    protected function add_profile_link($page, $post) {
        $page->add_text('profile_link', html::gen_url('profile.php', array('user_id' => $post['poster_id'])));
    }

    protected function display_avatar($post) {
        $avatar_extension = explode('/', $post['header']);
        if (count($avatar_extension) > 1) {
            return html::gen_image(forum_config::image_warehouse_url() . $post['avatar'] . '.' . $avatar_extension[1], "portrait");
        } else {
            return html::gen_image(forum_config::default_avatar(), "portrait");
        }
    }

    protected function display_paging() {
        $this->add_text('paging', $this->pages->display());
    }

}

?>
