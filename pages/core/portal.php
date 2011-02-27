<?php

/*
  Portal page

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
require_once('bl/core/portal_bl.php');
require_once('components/core/portal/portal_columns.php');
require_once('config/portal_images.php');

class portal_page extends page {

    private $portal_bl;
    private $portal_template;

    public function __construct() {
        parent::__construct();
        $this->add_text('title', 'Portal');
        $this->portal_bl = new portal_bl($this->db);
        $this->set_template('default');
        $this->portal_template = new page($this->template);
        $this->portal_template->set_template('portal');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            $portal_elements = $this->portal_bl->get_portal();
            foreach ($portal_elements AS $element) {
                $this->process_element($element);
            }
            $this->add_text('main', $this->portal_template->display());
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function process_element($element) {
        $element_html = $this->process_element_html($element);
        switch ($element['col']) {
            case portal_columns::MAIN:
                $this->portal_template->add_text('portal_center', $element_html);
                break;

            case portal_columns::RIGHT:
                $this->portal_template->add_text('portal_right', $element_html);
                break;

            case portal_columns::LEFT:
                $this->portal_template->add_text('portal_left', $element_html);
                break;
        }
    }

    protected function process_element_html($element) {
        $element_html = '';
        switch ($element['type']) {
            case "text":
                if (strlen($element['message']) > 0) {
                    if ($element['tag'] == 'main') {
                        if (strlen(portal_images::news($this->user->get_style())) > 0) {
                            $t_img = '<img class="float" src="' . portal_images::news($this->user->get_style()) . '" alt="" />';
                        } else {
                            $t_img = '';
                        }
                        $element_html .= $this->display_block($t_img . html::clean_text($element['message'], true, true), html::clean_text($element['title']));
                    } else {
                        if ($this->user->get_level() >= $element['view_level']) {
                            $element_html .= $this->display_block(html::clean_text($element['message'], true, true), html::clean_text($element['title']));
                        }
                    }
                }
                break;

            case "calendar":
                $calendar = new calendar(new DateTime());
                $element_html = $this->display_block($calendar->display($this->template), "Calendar");
                break;

            case "announcements":
                $sql = $this->portal_bl->get_announcements($this->user);
                if (is_array($sql) && count($sql) > 0) {
                    foreach ($sql AS $wee) {
                        $content = '
	                    <div class="auto" style="padding-bottom:3px;">
	                        <img class="float" src="' . portal_images::news($this->user->get_style()) . '" alt="" />
	                        ' . html::clean_text($wee['post_text'], true, true) . '<br /><br />
	                        <span class="italic">
	                            <a href="' . html::gen_url('index.php', array('page' => 'viewtopic', 't' => $wee['topic_id'])) . '">
	                                View Whole Post/Comment
	                            </a>
	                        </span> - Posted by ' . html::clean_text($wee['username']) . '<br /><br />
	                        <div class="cleaner"></div>
	                    </div>';
                        $element_html .= $this->display_block($content, html::clean_text($wee['forum_name']) . ' - ' . html::clean_text($wee['topic_title']));
                    }
                }
                break;

            case "lastposts":
                $topics = $this->portal_bl->get_latest_topics($this->user);
                if (is_array($topics) && count($topics) > 0) {
                    $icon = '';
                    $content = '';
                    foreach ($topics AS $_lt) {
                        $mark_read = forum_images::post($this->user->get_style());
                        $read_topics = $this->user->get_read_topics();
                        if ($this->user->get_level() > userlevels::$guest && $_lt['post_time'] > $this->user->get_last_visit()) {
                            $mark_read = forum_images::new_post($this->user->get_style());
                            if (isset($read_topics[$_lt['topic_id']])) {
                                if ($read_topics[$_lt['topic_id']] >= $_lt['topic_last_post_id']) {
                                    $mark_read = forum_images::post($this->user->get_style());
                                }
                            }
                        }

                        $content .= '<a href="' . html::gen_url('viewtopic.php', array('topic_id' => $_lt['topic_id'])) . '">
	                            <span class="bold">' . html::clean_text($_lt['topic_title']) . '</span>
	                        </a><br />
	                        <a href="' . html::gen_url('viewforum.php', array('forum_id' => $_lt['forum_id'])) . '">
	                            ' . html::clean_text($_lt['forum_name']) . '
	                        </a><br />
	                        <a href="' . html::gen_url('viewtopic.php', array('post_id' => $_lt['topic_last_post_id']), false, '#p_' . $_lt['topic_last_post_id']) . '"><img src="' . $mark_read . '" alt="View latest post" title="View latest post" /></a>
	                        <br />
	                        <span style="font-size: 9px;">by ' . $_lt['username'] . ' ' . date(forum_config::$date_format, $_lt['post_time']) . '</span><br /><br />';
                    }

                    $element_html .= $this->display_block($content, 'Latest Posts');
                }
                break;

            case "shoutbox":
                break;
        }

        return $element_html;
    }

}

?>