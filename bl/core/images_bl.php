<?php

/*
  Images bl/data access class

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

class images_bl {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __destruct() {
        $this->db = null;
    }

    public function get_image_count($user_id = null) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        COUNT(`image_id`) AS 'count'
                    FROM
                        `image_warehouse`
                ";
                break;
        }
        if ($user_id !== null) {
            switch (config::db_engine()) {
                default:
                    $query .= "
                        WHERE
                            owner_id = :owner_id
                    ";
                    break;
            }
            $count = $this->db->sql_select(
                            $query, array(
                        ':owner_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                            )
            );
        } else {
            $count = $this->db->sql_select(
                            $query
            );
        }

        return $count[0]['count'];
    }

    public function delete_image($image_id) {
        switch (config::db_engine()) {
            default:
                $query = "DELETE
                    FROM
                        `image_warehouse`
                    WHERE
                        `image_id` = :image_id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':image_id' => array('value' => $image_id, 'type' => PDO::PARAM_INT)
                )
        );

        switch (config::db_engine()) {
            default:
                $query = "DELETE
                    FROM
                        `image_tags`
                    WHERE
                        `image_id` = :image_id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':image_id' => array('value' => $image_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_image($image_id) {
        switch (config::db_engine()) {
            default:
                $query = "SELECT
                        `image_warehouse`.`image_id`,
                        `image_name`,
                        `timestamp`,
                        `size`,
                        `header`,
                        `username`,
                        `user_level`,
                        `users`.`id`,
                        `owner_id`,
                        `colour`,
                        `rank_colour`,
                        `description`,
                        `tag`
                    FROM
                        `image_warehouse`
                    LEFT JOIN
                        `image_tags`
                        ON
                        `image_tags`.`image_id` = `image_warehouse`.`image_id`
                    LEFT JOIN
                        `users`
                        ON
                        `users`.`id` = `owner_id`
                    LEFT JOIN
                        `ranks`
                        ON
                        `level` = `user_level`
                    WHERE
                        `image_warehouse`.`image_id` = :image_id
                    ORDER BY
                        timestamp DESC
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':image_id' => array('value' => $image_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_images($page, $user_id = null) {
        if ($page > 0) {
            $page = $page - 1;
        }

        switch (config::db_engine()) {
            default:
                $query = "SELECT
                        `image_warehouse`.`image_id`,
                        `image_name`,
                        `timestamp`,
                        `size`,
                        `header`,
                        `username`,
                        `user_level`,
                        `users`.`id`,
                        `owner_id`,
                        `colour`,
                        `rank_colour`,
                        `description`,
                        `tag`
                    FROM
                        `image_warehouse`
                    LEFT JOIN
                        `image_tags`
                        ON
                        `image_tags`.`image_id` = `image_warehouse`.`image_id`
                    LEFT JOIN
                        `users`
                        ON
                        `users`.`id` = `owner_id`
                    LEFT JOIN
                        `ranks`
                        ON
                        `level` = `user_level`";
                break;
        }
        if ($user_id !== null) {
            switch (config::db_engine()) {
                default:
                    $query .= "
                        WHERE
                            owner_id = :owner_id
                        ORDER BY
                            timestamp DESC
                        LIMIT
                            :limit
                        OFFSET
                            :offset
                    ";
                    break;
            }
            return $this->db->sql_select(
                    $query, array(
                ':owner_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
                ':limit' => array('value' => forum_config::$page_limit, 'type' => PDO::PARAM_INT),
                ':offset' => array('value' => forum_config::$page_limit * $page, 'type' => PDO::PARAM_INT)
                    )
            );
        } else {
            switch (config::db_engine()) {
                default:
                    $query .= "
                        ORDER BY
                            timestamp DESC
                        LIMIT
                            :limit
                        OFFSET
                            :offset
                    ";
                    break;
            }
            return $this->db->sql_select(
                    $query, array(
                ':limit' => array('value' => forum_config::$page_limit, 'type' => PDO::PARAM_INT),
                ':offset' => array('value' => forum_config::$page_limit * $page, 'type' => PDO::PARAM_INT)
                    )
            );
        }
    }

    public function add_image($image_id, $filename, $user_id, $time, $type, $size) {
        $this->db->sql_query('
    		INSERT INTO
    			image_warehouse
    			(`image_id`, `image_name`, `owner_id`, `timestamp`, `header`, `size`)
    		VALUES
    			(:image_id, :image_name, :owner_id, :timestamp, :header, :size)
    		', array(
            ':image_id' => array('value' => $image_id, 'type' => PDO::PARAM_STR),
            ':image_name' => array('value' => $filename, 'type' => PDO::PARAM_STR),
            ':owner_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':timestamp' => array('value' => $time, 'type' => PDO::PARAM_INT),
            ':header' => array('value' => $type, 'type' => PDO::PARAM_STR),
            ':size' => array('value' => $size, 'type' => PDO::PARAM_INT)
                )
        );
    }

}

?>
