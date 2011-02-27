<?php

/*
  Installation buisness logic/data access

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

class install_bl {

    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function __destruct() {
        $this->db = null;
    }

    public function create_user_table() {
        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE IF NOT EXISTS
                        `users`
                        (
                          `id` int(11) NOT NULL auto_increment,
                          `username` varchar(25) NOT NULL default '',
                          `password` varchar(32) NOT NULL default '',
                          `email` varchar(255) NOT NULL default '',
                          `user_level` smallint(6) NOT NULL default '0',
                          `user_lastvisit` int(11) NOT NULL default '0',
                          `last_refresh` varchar(13) NOT NULL default '',
                          `security` varchar(8) NOT NULL default '',
                          `status` smallint(6) NOT NULL default '0',
                          `reg_date` int(11) NOT NULL default '0',
                          `rss_password` varchar(25) NOT NULL default '',
                          `style` int(11) NOT NULL default '0',
                          `randm` varchar(8) NOT NULL,
                          `address` text NOT NULL default '',
                          `phone` varchar(15) NOT NULL default '',
                          PRIMARY KEY  (`id`)
                        )
                    ENGINE=InnoDB
                    DEFAULT CHARSET=utf8
                    ROW_FORMAT=COMPACT";
                break;
        }
        $this->db->sql_query($query);

        // add default users
        // system
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        `users`
                        (
                            `id`,
                            `username`,
                            `password`,
                            `email`,
                            `user_level`,
                            `user_lastvisit`,
                            `last_refresh`,
                            `security`,
                            `status`,
                            `reg_date`,
                            `rss_password`,
                            `style`,
                            `randm`,
                            `address`,
                            `phone`
                        )
                    VALUES
                        (
                            1,
                            'System',
                            '',
                            '',
                            -1,
                            0,
                            '',
                            '',
                            1,
                            1233492699,
                            '',
                            0,
                            '',
                            '',
                            ''
                        )";
                break;
        }
        $this->db->sql_query($query);

        // kumnaa
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        `users`
                        (
                            `id`,
                            `username`,
                            `password`,
                            `email`,
                            `user_level`,
                            `user_lastvisit`,
                            `last_refresh`,
                            `security`,
                            `status`,
                            `reg_date`,
                            `rss_password`,
                            `style`,
                            `randm`,
                            `address`,
                            `phone`
                        )
                    VALUES
                        (
                            2,
                            'Kumnaa',
                            :password,
                            '',
                            80,
                            0,
                            '',
                            'AV23RJG6',
                            2,
                            :date,
                            '',
                            0,
                            '',
                            '',
                            ''
                        )";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':password' => array('value' => md5("password" . config::salt()), 'type' => PDO::PARAM_STR),
            ':date' => array('value' => time(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function create_sessions_table() {
        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE IF NOT EXISTS
                        `sessions`
                        (
                            `user_id` int(11) NOT NULL default '0',
                            `username` varchar(40) NOT NULL default '',
                            `security` varchar(8) NOT NULL default '',
                            `encode_ip` varchar(8) NOT NULL default '',
                            `timestamp` varchar(13) NOT NULL default '',
                            `randm` varchar(8) NOT NULL default '',
                            `user_agent` varchar(150) NOT NULL default '',
                            `remember` tinyint(4) NOT NULL default '0',
                            `session_id` varchar(32) NOT NULL default '',
                            `domain` varchar(150) NOT NULL default '',
                            KEY `idx_1` (`user_id`)
                        )
                    ENGINE=InnoDB
                    DEFAULT CHARSET=utf8;";
                break;
        }
        $this->db->sql_query($query);
    }

}

?>
