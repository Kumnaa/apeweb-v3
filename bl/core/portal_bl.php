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

class portal_bl extends businesslogic_base {

    public function get_latest_topics($user) {
        return $this->db->sql_select('
    		SELECT
    			`topic_title`,
    			`topics`.`topic_id`,
    			`post_icons`.`url`,
    			`p`.`poster_id`,
    			`username`,
    			`p`.`post_time`,
    			`topic_last_post_id`,
    			`forum_name`,
    			`users`.`id`,
    			`user_level`,
    			`topics`.`forum_id`,
    			`users`.`colour`,
    			`ranks`.`rank_colour`
    		FROM
    			`topics`
    		LEFT JOIN
    			`posts` AS `p`
    			ON
    			`p`.`post_id` = `topics`.`topic_last_post_id`
                LEFT JOIN
                        `users`
                        ON
                        `id` = `p`.`poster_id`
                LEFT JOIN
                        `forums`
                        ON
                        `forums`.`forum_id` = `topics`.`forum_id`
                LEFT JOIN
                        `categories`
                        ON
                        `categories`.`cat_id` = `forums`.`cat_id`
                LEFT JOIN
                        `post_icons`
                        ON
                        `post_icons`.`id` = `topic_icon`
                LEFT JOIN
                        `ranks`
                        ON
                        `ranks`.`level` = `users`.`user_level`    		
    		WHERE
    			`forum_view_level` <= :user_level
    			AND
    			`categories`.`cat_id` > 1
    			AND
    			`forum_status` = 0
    		ORDER BY 
    			p.`post_time` DESC
    		LIMIT
    			10
    		OFFSET
    			0
    		', array(
            ':user_level' => array('value' => $user->get_level(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_announcements($user) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `topics`.`topic_title`,
                       	`topics`.`topic_id`,
                       	`posts_text`.`post_text`,
                       	`users`.`username`,
                       	`users`.`user_level`,
                       	`users`.`colour`,
                       	`ranks`.`rank_colour`,
                       	`forums`.`forum_name`
                    FROM
                    	`topics`
                    LEFT JOIN
                    	`posts_text`
                    	ON
                    	`post_id` = `topic_first_post_id`
                    LEFT JOIN
                    	users
                    	ON
                    	`id` = `topic_poster`
                    LEFT JOIN
                    	forums
                    	ON
                    	`forums`.`forum_id` = `topics`.`forum_id`
                    LEFT JOIN
                    	ranks
                    	ON
                    	`ranks`.`level` = `users`.`user_level`
                    WHERE
                    	(
                    		`forums`.`announcement` = 1
                    		OR
                    		`forums`.`forum_id` = -2
                    	)
                    	AND
                    	`topics`.`topic_type` >= :topic_type
                    	AND
                    	`forums`.`forum_view_level` <= :user_level
                    ORDER BY
                    	`topics`.`topic_time` DESC
                    LIMIT 4
                    OFFSET 0
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':topic_type' => array('value' => forumlevels::$announcement, 'type' => PDO::PARAM_INT),
            ':user_level' => array('value' => $user->get_level(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_portal() {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `message`,
                       	`tag`,
                       	`title`,
                       	`type`,
                       	`col`,
                       	`view_level`
                    FROM
                    	`portal`
                    ORDER BY
                    	`order` ASC
                ";
                break;
        }
        return $this->db->sql_select(
                $query
        );
    }

}

?>
