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
    
    public function display_shoutbox() {
        $shout_bl = new portal_bl(page::$db_connection);
        return $this->process_shouts($shout_bl->get_shouts($this->shout_count));
    }
    
    protected function process_shouts($shouts) {
        $output = '';
        if (is_array($shouts) && count($shouts) > 0) {
            foreach ($shouts AS $shout) {
                $output .= '<div>';
                if (page::$user->get_level() >= userlevels::$moderator || $shout['id'] == page::$user->get_user_id())
                {
                    $output .= '<a href="'. html::gen_url('shoutbox.php', array('id' => $shout['post_id'])) .'"><img src="'. forum_images::edit_icon(page::$user->get_style()) .'" alt="e" /></a> ';
                }
                
                if ($this->anonymous == true)
                {
                    $output .= '&#8855; ';
                }
                else
                {
                    $output .= '<img src="'. forum_images::clock_icon(page::$user->get_style()) .'" alt="clock" />&#160;'. html::clean_text($shout['username']) .' - ';
                }
                $output .= html::clean_text($shout['post_text']);
                $output .= '
                <div style="display:none;">
                    <div class="tb_item" style="border-width:1px; border-style:solid;" id="sh_ps_'. $shout['post_id'] .'">
                        <span class="italic gensmall">'. date(forum_config::$date_format, $shout['post_time']) .'</span>
                    </div>
                </div>';
                $output .= '</div>';
            }
        }
        
        return $output;
    }
}

?>
