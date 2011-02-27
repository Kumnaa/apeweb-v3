<?php

/*
  Base user class business logic/data access for erepublik API based users

  @author Ben Bowtell

  @date 27-Feb-2011

  (c) 2011 by http://www.amplifycreative.net

  contact: ben@amplifycreative.net.net

  ﻿   This program is free software: you can redistribute it and/or modify
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

class _erep_user_bl {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function clear_sessions($citizen_id, $user_ip) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        sessions
                    WHERE
                        (
                            citizen_id = :citizen_id
                            AND
                            user_ip = :user_ip
                        )
                        OR
                        (
                            timestamp < :time
                        )";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':citizen_id' => array('value' => $citizen_id, 'type' => PDO::PARAM_INT),
            ':user_ip' => array('value' => $user_ip, 'type' => PDO::PARAM_STR),
            ':time' => array('value' => time() - 86400, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function insert_session($citizen_id, $oauth) {
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        sessions
                        (
                            citizen_id,
                            oauth,
                            timestamp
                        )
                    VALUES
                        (
                            :citizen_id,
                            :oauth,
                            :timestamp
                         )";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':citizen_id' => array('value' => $citizen_id, 'type' => PDO::PARAM_INT),
            ':oauth' => array('value' => $oauth, 'type' => PDO::PARAM_STR),
            ':timestamp' => array('value' => time(), 'type' => PDO::PARAM_INT)
                )
        );
    }

}

?>
