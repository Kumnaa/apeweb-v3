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

    public function delete_element($id) {
        $current_position = $this->get_current_element_position($id);
        $current_column = $this->get_current_element_column($id);
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        `portal`
                    WHERE
                        `id` = :id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );

        $this->move_elements_up($current_position, $current_column);
    }

    public function move_elements_up($position, $column) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        `portal`
                    SET
                        `order` = `order` - 1
                    WHERE
                        `order` > :position
                        AND
                        `col` = :column
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':position' => array('value' => $position, 'type' => PDO::PARAM_INT),
            ':column' => array('value' => $column, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_column_elements($column) {
        $result = $this->db->sql_select('
    		SELECT
    			`message`, `tag`, `id`
    		FROM
    			`portal`
    		WHERE
                        `col` = :col
    		ORDER BY 
    			`order` ASC
    		', array(
            ':col' => array('value' => $column, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($result) > 0) {
            return $result;
        } else {
            return null;
        }
    }

    public function update_element_position($element, $position) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        `portal`
                    SET
                        `order` = :position
                    WHERE
                        `id` = :id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':position' => array('value' => $position, 'type' => PDO::PARAM_INT),
            ':id' => array('value' => $element, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function swap_element_positions($element1, $element2) {
        $element1_position = $this->get_current_element_position($element1);
        $element2_position = $this->get_current_element_position($element2);
        $this->update_element_position($element1, $element2_position);
        $this->update_element_position($element2, $element1_position);
    }

    public function get_element($id) {
        $result = $this->db->sql_select('
    		SELECT
    			`message`, `tag`, `title`, `type`, `view_level`
    		FROM
    			`portal`
    		WHERE
                        `id` = :id
    		', array(
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($result) > 0) {
            return $result[0];
        } else {
            return null;
        }
    }

    public function update_element($id, $message, $title, $type, $viewlevel) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        `portal`
                    SET
                        `message` = :message,
                        `title` = :title,
                        `type` = :type,
                        `view_level` = :viewlevel
                    WHERE
                        `id` = :id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':message' => array('value' => $message, 'type' => PDO::PARAM_STR),
            ':title' => array('value' => $title, 'type' => PDO::PARAM_STR),
            ':type' => array('value' => $type, 'type' => PDO::PARAM_STR),
            ':viewlevel' => array('value' => $viewlevel, 'type' => PDO::PARAM_INT),
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function add_element($tag, $column) {
        $last_element_position = $this->get_last_element_position($column);
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        `portal`
                    (`tag`, `col`, `order`, `view_level`)
                    VALUES
                    (:tag, :column, :order, 0)
                    ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':tag' => array('value' => $tag, 'type' => PDO::PARAM_STR),
            ':order' => array('value' => $last_element_position + 1, 'type' => PDO::PARAM_INT),
            ':column' => array('value' => $column, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_last_element_position($column) {
        $result = $this->db->sql_select('
    		SELECT
    			`order`
    		FROM
    			`portal`
    		WHERE
                        `col` = :col
    		ORDER BY 
    			`order` DESC
    		LIMIT
    			1
    		OFFSET
    			0
    		', array(
            ':col' => array('value' => $column, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($result) > 0) {
            return $result[0]['order'];
        } else {
            return 1;
        }
    }

    public function get_previous_element($position, $column) {
        $result = $this->db->sql_select('
    		SELECT
    			`id`
    		FROM
    			`portal`
    		WHERE
    			`order` < :order
                        AND
                        `col` = :col
    		ORDER BY 
    			`order` DESC
    		LIMIT
    			1
    		OFFSET
    			0
    		', array(
            ':order' => array('value' => $position, 'type' => PDO::PARAM_INT),
            ':col' => array('value' => $column, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($result) > 0) {
            return $result[0]['id'];
        } else {
            return null;
        }
    }

    public function get_next_element($position, $column) {
        $result = $this->db->sql_select('
    		SELECT
    			`id`
    		FROM
    			`portal`
    		WHERE
    			`order` > :order
                        AND
                        `col` = :col
    		ORDER BY 
    			`order` ASC
    		LIMIT
    			1
    		OFFSET
    			0
    		', array(
            ':order' => array('value' => $position, 'type' => PDO::PARAM_INT),
            ':col' => array('value' => $column, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($result) > 0) {
            return $result[0]['id'];
        } else {
            return null;
        }
    }

    public function get_current_element_column($id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `col`
                    FROM
                    	`portal`
                    WHERE
                    	`id` = :id
                ";
                break;
        }

        $results = $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($results) > 0) {
            return $results[0]['col'];
        } else {
            throw new Exception('Portal element not found');
        }
    }

    public function get_current_element_position($id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `order`
                    FROM
                    	`portal`
                    WHERE
                    	`id` = :id
                ";
                break;
        }

        $results = $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );

        if (count($results) > 0) {
            return $results[0]['order'];
        } else {
            throw new Exception('Portal element not found');
        }
    }

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
    			`users`.`colour`
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
