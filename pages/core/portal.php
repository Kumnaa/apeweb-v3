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
if (file_exists(RELATIVE_PATH . 'components/page.php')) {
    require_once(RELATIVE_PATH . 'components/page.php');
} else {
    require_once('components/core/page.php');
}

// end for unit testing

class portal_page extends page {

    private $portal_bl;
    private $forum_bl;
    private $portal_template;

    public function __construct() {
        parent::__construct();
        $this->enable_component(component_types::$portal);
        $this->enable_component(component_types::$shoutbox);
        $this->enable_component(component_types::$calendar);
        $this->add_text('title', 'Portal');
        $this->portal_bl = new portal_bl();
        $this->forum_bl = new forum_bl();
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
            if (is_array($portal_elements)) {
                foreach ($portal_elements AS $element) {
                    if ($element['view_level'] <= page::$user->get_level()) {
                        $this->process_element($element);
                    }
                }
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

    protected function process_additional_element_html($element) {
        
    }

    protected function process_element_html($element) {
        $element_html = '';
        switch ($element['type']) {
            case "post":
                $post_details = $this->forum_bl->get_post_details($element['message']);
                if (is_array($post_details) && count($post_details) > 0) {
                    $element_html .= $this->display_block(html::clean_text($post_details[0]['post_text'], true, true, true));
                }
                break;
            case "text":
                if (strlen($element['message']) > 0) {
                    if ($element['tag'] == 'main') {
                        if (strlen(portal_images::news(page::$user->get_style())) > 0) {
                            $t_img = '<img class="float" src="' . portal_images::news(page::$user->get_style()) . '" alt="" />';
                        } else {
                            $t_img = '';
                        }

                        $element_html .= $this->display_block($t_img . html::clean_text($element['message'], true, true), html::clean_text($element['title']));
                    } else {
                        $element_html .= $this->display_block(html::clean_text($element['message'], true, true), html::clean_text($element['title']));
                    }
                }
                break;

            case "calendar":
                $calendar = new calendar(new DateTime());
                $element_html .= $this->display_block($calendar->display($this->template), "Calendar");
                break;

            case "announcements":
                $sql = $this->portal_bl->get_announcements(page::$user);
                if (is_array($sql) && count($sql) > 0) {
                    foreach ($sql AS $wee) {
                        $content = '
	                    <div class="auto" style="padding-bottom:3px;">
	                        <img class="float" src="' . portal_images::news(page::$user->get_style()) . '" alt="" />
	                        ' . html::clean_text($wee['post_text'], true, true) . '<br /><br />
	                        <span class="italic">
	                            <a href="' . html::gen_url('viewtopic.php', array('topic_id' => $wee['topic_id'])) . '">View Whole Post/Comment</a>
	                        </span> - Posted by ' . html::clean_text($wee['username']) . '<br /><br />
	                        <div class="cleaner"></div>
	                    </div>';
                        $element_html .= $this->display_block($content, html::clean_text($wee['forum_name']) . ' - ' . html::clean_text($wee['topic_title']));
                    }
                }
                break;

            case "lastposts":
                $topics = $this->portal_bl->get_latest_topics(page::$user);
                if (is_array($topics) && count($topics) > 0) {
                    $icon = '';
                    $content = '';
                    foreach ($topics AS $_lt) {
                        $mark_read = forum_images::post(page::$user->get_style());
                        $read_topics = page::$user->get_read_topics();
                        if (page::$user->get_level() > userlevels::$guest && $_lt['post_time'] > page::$user->get_last_visit()) {
                            $mark_read = forum_images::new_post(page::$user->get_style());
                            if (isset($read_topics[$_lt['topic_id']])) {
                                if ($read_topics[$_lt['topic_id']] >= $_lt['topic_last_post_id']) {
                                    $mark_read = forum_images::post(page::$user->get_style());
                                }
                            }
                        }

                        $content .= '<a class="port_lastp_topic_title" href="' . html::gen_url('viewtopic.php', array('topic_id' => $_lt['topic_id'])) . '">' . html::clean_text($_lt['topic_title']) . '</a><br />
	                        <a class="port_lastp_forum_title" href="' . html::gen_url('viewforum.php', array('forum_id' => $_lt['forum_id'])) . '">' . html::clean_text($_lt['forum_name']) . '</a><br />
	                        <a href="' . html::gen_url('viewtopic.php', array('post_id' => $_lt['topic_last_post_id']), false, '#p_' . $_lt['topic_last_post_id']) . '"><img src="' . $mark_read . '" alt="View latest post" title="View latest post" /></a>
	                        <br />
	                        <span class="port_lastp_user_title">by ' . $_lt['username'] . ' ' . date(forum_config::$date_format, $_lt['post_time']) . '</span><br /><br />';
                    }

                    $element_html .= $this->display_block($content, 'Latest Posts');
                }
                break;

            case "shoutbox":
                $shoutbox = new shoutbox();
                if (page::$user->get_level() > userlevels::$guest) {
                    $shoutbox->set_allow_post(true);
                }

                $element_html .= $this->display_block($shoutbox->display_shoutbox(), 'Shoutbox');
                break;

            default:
                $element_html .= $this->process_additional_element_html($element);
                break;
        }

        return $element_html;
    }

}

?>