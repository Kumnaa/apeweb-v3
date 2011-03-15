<?php

/*
  Portal business logic class

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

class shoutbox_bl extends businesslogic_base {

    public function get_shouts($limit = 10) {
        return $this->db->sql_select('
    		SELECT
                    `p`.`post_id`,
                    `p`.`poster_id`,
                    `p`.`post_time`,
                    `p`.`poster_ip`,
                    `pt`.`post_text`,
                    `u`.`username`,
                    `u`.`user_level`,
                    `u`.`colour`,
                    `rank_colour`,
                    `u`.`id`
    		FROM
                    `shoutbox` AS `p`
                LEFT JOIN
                    `users` AS `u`
                    ON
                    `id` = `p`.`poster_id`
                LEFT JOIN
                    `shoutbox_text` AS `pt`
                    ON
                    `pt`.`post_id` = `p`.`post_id`
                LEFT JOIN
                    `ranks`
                    ON
                    `ranks`.`level` = `u`.`user_level`    		
    		WHERE
                    `p`.`status` =  1
    		ORDER BY 
                    p.`post_time` DESC
    		LIMIT
                    :limit
    		OFFSET
    			0
    		',
                array(
                    ':limit' => array('value' => $limit, 'type' => PDO::PARAM_INT)
                )
        );
    }
}

?>
