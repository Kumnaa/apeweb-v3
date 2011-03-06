<?php

/*
  Post page

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

class post_page extends page {

    private $forum_id;
    private $topic_id;
    private $group_id;
    private $banter_id;
    private $subject;
    private $post;
    private $forum_bl;
    private $breadcrumb;

    public function __construct() {
        parent::__construct();
        $this->forum_bl = new forum_bl();
        $this->forum_id = input::validate('forum_id', 'int');
        $this->topic_id = input::validate('topic_id', 'int');
        $this->group_id = input::validate('group_id', 'int');
        $this->banter_id = input::validate('banter_id', 'int');

        $this->post_id = input::validate('post_id', 'int');
        $this->group_post_id = input::validate('group_post_id', 'int');
        $this->banter_post_id = input::validate('banter_post_id', 'int');

        $this->subject = input::validate('subject', 'message');
        $this->post = input::validate('post', 'message');

        $this->breadcrumb = new breadcrumb();
        $this->add_text('title', 'Forum Viewer');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            $this->breadcrumb->add_crumb('Portal', html::gen_url('index.php'));
            switch ($this->action) {
                case "edit":
                    if ($this->post_id > 0) {
                        $this->edit_post();
                    } else if ($this->group_post_id) {
                        $this->edit_group_post();
                    } else if ($this->banter_post_id) {
                        $this->edit_banter_post();
                    } else {
                        throw new Exception("No valid post.");
                    }
                    break;
                default:
                    if ($this->topic_id > 0) {
                        $this->new_post();
                    } else if ($this->forum_id > 0) {
                        $this->new_topic();
                    } else if ($this->group_id > 0) {
                        $this->new_group_post();
                    } else if ($this->banter_id > 0) {
                        $this->new_banter_post();
                    } else {
                        throw new Exception("No valid destination for this topic.");
                    }
                    break;
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    private function edit_post() {
       $details = $this->forum_bl->get_post_details($this->post_id);
        if (count($details) > 0) {
            $security = new security_type();

            if ((page::$user->get_level() >= userlevels::$moderator || page::$user->get_user_id() == $details[0]['poster_id']) && page::$user->get_level() >= $details[0]['forum_level']) {
                $security->AllowEdit(true);
            }

            if (page::$user->get_level() >= userlevels::$moderator && page::$user->get_level() >= $details[0]['forum_level']) {
                $security->AllowDelete(true);
            }

            if (page::$user->get_level() >= $details[0]['forum_post_level']) {
                $security->AllowAdd(true);
            }
            
            if ($security->AllowEdit() == true) {
                $this->forum_id = $details[0]['forum_id'];
                $this->breadcrumb->add_crumb('Forums', html::gen_url('forums.php'));
                $this->breadcrumb->add_crumb(html::clean_text($details[0]['forum_name']), html::gen_url('viewforum.php', array('forum_id' => $this->forum_id)));
                $this->breadcrumb->add_crumb(html::clean_text($details[0]['topic_title']), html::gen_url('viewtopic.php', array('topic_id' => $this->topic_id)));
                if ($_POST) {
                    try {
                        $this->update_post();
                    } catch (Exception $ex) {
                        $this->post = $details[0]['post_text'];
                        $this->generate_html(html::gen_url('post.php', array('action' => 'edit', 'post_id' => $this->post_id)), false, $ex->getMessage());
                    }
                } else {
                    $this->post = $details[0]['post_text'];
                    $this->generate_html(html::gen_url('post.php', array('action' => 'edit', 'post_id' => $this->post_id)));
                }
            } else {
                throw new Exception("Access denied.");
            }
        } else {
            throw new Exception("Post does not exist.");
        } 
    }

    private function new_post() {
        $details = $this->forum_bl->get_topic_details($this->topic_id);
        if (count($details) > 0) {
            $this->forum_id = $details[0]['forum_id'];
            if ($details[0]['forum_post_level'] <= page::$user->get_level()) {
                $this->breadcrumb->add_crumb('Forums', html::gen_url('forums.php'));
                $this->breadcrumb->add_crumb(html::clean_text($details[0]['forum_name']), html::gen_url('viewforum.php', array('forum_id' => $this->forum_id)));
                $this->breadcrumb->add_crumb(html::clean_text($details[0]['topic_title']), html::gen_url('viewtopic.php', array('topic_id' => $this->topic_id)));
                if ($_POST) {
                    try {
                        $this->insert_new_post(false);
                    } catch (Exception $ex) {
                        $this->generate_html(html::gen_url('post.php', array('topic_id' => $this->topic_id)), false, $ex->getMessage());
                    }
                } else {
                    $this->generate_html(html::gen_url('post.php', array('topic_id' => $this->topic_id)));
                }
            } else {
                throw new Exception("Access denied.");
            }
        } else {
            throw new Exception("Topic does not exist.");
        }
    }

    private function new_topic() {
        $details = $this->forum_bl->get_forum_details($this->forum_id);
        if (count($details) > 0) {
            if ($details[0]['forum_level'] <= page::$user->get_level()) {
                $this->breadcrumb->add_crumb('Forums', html::gen_url('forums.php'));
                $this->breadcrumb->add_crumb(html::clean_text($details[0]['forum_name']), html::gen_url('viewforum.php', array('forum_id' => $this->forum_id)));
                if ($_POST) {
                    try {
                        if (strlen($this->subject) == 0) {
                            throw new Exception("Subject too short.");
                        }
                        if (strlen($this->post) == 0) {
                            throw new Exception("Post too short.");
                        }
                        $this->insert_new_post(true);
                    } catch (Exception $ex) {
                        $this->generate_html(html::gen_url('post.php', array('forum_id' => $this->forum_id)), true, $ex->getMessage());
                    }
                } else {
                    $this->generate_html(html::gen_url('post.php', array('forum_id' => $this->forum_id)), true);
                }
            }
        } else {
            throw new Exception("Topic does not exist.");
        }
    }

    private function display_post_details() {
        $page = new page($this->template);
        $page->set_template('forums/new_post_details');
        $page->add_text('breadcrumb_trail', $this->breadcrumb->display());
        $this->add_text('main', $page->display());
    }

    private function generate_html($action, $subject = false, $notice = '') {
        $this->breadcrumb->add_crumb('Post');
        $page = new page($this->template);
        $page->set_template('forums/new_post');
        if (strlen($notice) > 0) {
            $page->add_text('new_post_notice', $notice);
        }
        if ($subject == true) {
            $page->add_text('subject', 'Subject: <input id="new_post_subject" type="text" name="subject" value="' . $this->subject . '" />
                <br />
                <br />
                ');
        }
        $page->add_text('post', html::clean_text($this->post, false, false));
        $page->add_text('action', $action);
        $this->display_post_details();
        $this->add_text('main', $page->display());
        $this->display_post_details();
    }

    private function insert_new_post() {
        $this->forum_bl->insert_post(
                $this->post, $this->subject, page::$user->get_user_id(), page::$user->get_user_ip(), $this->forum_id, $this->topic_id
        );
        $page = new page($this->template);
        $page->set_template('forums/new_post_details');
        $page->add_text('breadcrumb_trail', $this->breadcrumb->display());
        $this->add_text('main', $page->display());
        $this->notice("Post added.");
    }
    
    private function update_post() {
        $this->forum_bl->update_post($this->post_id, $this->post, time(), page::$user->get_user_id());
        $this->notice("Post saved.");
    }

}

?>