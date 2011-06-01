<?php

/*
  shoutbox

  @author Ben Bowtell

  @date 06-Mar-2011

  (c) 2011 by http://www.amplifycreative.net/

  contact: ben@amplifycreative.net

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

class shoutbox {

    private $shout_count = 10;
    private $anonymous = false;
    private $allow_post = false;
    private $shout_text = 'Shout!';
    
    public function set_annonymous($value) {
        $this->anonymous = $value;
    }

    public function set_allow_post($value) {
        $this->allow_post = $value;
    }
    
    public function set_shout_text($value) {
        $this->shout_text = $value;
    }
    
    public function display_shoutbox() {
        $shout_bl = new shoutbox_bl();
        $output = '<div id="shoutboxcontainer">'. $this->process_shouts($shout_bl->get_shouts($this->shout_count));
        if ($this->allow_post == true) {
            $output .= '<br />
                <form id="shoutform" method="post" action="">
                    <div> 
                        <input id="shout_box_data" size="50" type="text" name="message"> 
                        <input type="submit" value="'. html::clean_text($this->shout_text) .'">
                    </div>
                </form>';
        }
        
        $output .= '<script type="text/javascript">
                //<!--
                    $("#shoutboxcontainer").apetech_shoutbox();
                    $("#shoutbox img[title]").tooltip({ tipClass: "shout_time"});
                //-->
                </script>';

        return $output .'</div>';
    }

    public function display_plain_shoutbox() {
        $shout_bl = new shoutbox_bl();
        $output = $this->process_inner_shouts($shout_bl->get_shouts($this->shout_count));
        $output .= '<script type="text/javascript">
            //<!--
            $("#shoutbox img[title]").tooltip({ tipClass: "shout_time"});
            //-->
            </script>';
        return $output;
    }
    
    protected function process_inner_shouts($shouts) {
        $output = '';
        if (is_array($shouts) && count($shouts) > 0) {
            foreach ($shouts AS $shout) {
                $output .= '<div>';
                if (page::$user->get_level() >= userlevels::$moderator || $shout['id'] == page::$user->get_user_id()) {
                    $output .= '<a href="' . html::gen_url('shoutbox.php', array('id' => $shout['post_id'])) . '"><img src="' . forum_images::edit_icon(page::$user->get_style()) . '" alt="e" /></a> ';
                }

                if ($this->anonymous == true) {
                    $output .= '&#8855; ';
                } else {
                    $output .= '<img src="' . forum_images::clock_icon(page::$user->get_style()) . '" alt="clock" title="'. date(forum_config::$date_format, $shout['post_time']) .'" />&#160;' . html::clean_text($shout['username']) . ' - ';
                }
                $output .= html::clean_text($shout['post_text']);
                $output .= '</div>';
            }
        }
        
        return $output;
    }
    
    protected function process_shouts($shouts) {
        $output = '<div id="shoutbox">';
        $output .= $this->process_inner_shouts($shouts);
        $output .= '</div>';
        
        return $output;
    }

}

?>
