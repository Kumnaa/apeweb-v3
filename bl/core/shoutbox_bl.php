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

    public function clear_shouts() {
                    switch (config::db_engine()) {
                default:
                    $query = "
                    UPDATE 
                        shoutbox
                    SET
                        status = 0
                    ";
                    break;
            }
            $this->db->sql_query($query);
    }
    
    public function add_shout($post, $user_id, $user_ip) {
        $this->db->begin_transaction();
        try {
            // insert post details
            switch (config::db_engine()) {
                default:
                    $query = "
                    INSERT INTO
                        shoutbox
                        (
                            poster_id,
                            post_time,
                            poster_ip,
                            status
                        )
                        VALUES
                        (
                            :poster_id,
                            :post_time,
                            :poster_ip,
                            :status
                        )
                ";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':poster_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
                ':post_time' => array('value' => time(), 'type' => PDO::PARAM_INT),
                ':poster_ip' => array('value' => $user_ip, 'type' => PDO::PARAM_INT),
                ':status' => array('value' => 1, 'type' => PDO::PARAM_INT)
                    )
            );

            // get post id
            $last_post = $this->db->sql_insert_id('shoutbox', 'post_id');

            // insert post text
            switch (config::db_engine()) {
                default:
                    $query = "
                    INSERT INTO
                        shoutbox_text
                        (
                            post_id,
                            post_text
                        )
                        VALUES
                        (
                            :post_id,
                            :post_text
                        )
                ";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':post_id' => array('value' => $last_post, 'type' => PDO::PARAM_INT),
                ':post_text' => array('value' => $post, 'type' => PDO::PARAM_STR)
                    )
            );
            $this->db->end_transaction();
        } catch (Exception $ex) {
            $this->db->rollback_transaction();
            throw new Exception("Error adding post.");
        }
    }

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
    		WHERE
                    `p`.`status` =  1
    		ORDER BY 
                    p.`post_time` DESC
    		LIMIT
                    :limit
    		OFFSET
    			0
    		', array(
            ':limit' => array('value' => $limit, 'type' => PDO::PARAM_INT)
                )
        );
    }

}

?>
