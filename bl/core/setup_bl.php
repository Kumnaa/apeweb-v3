<?php

/*
  Setup logic class

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

class setup_bl extends businesslogic_base {

    public function create_forum_tables() {

        // categories

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `categories` (
                    `cat_id` mediumint(9) NOT NULL AUTO_INCREMENT,
                    `cat_title` varchar(100) NOT NULL DEFAULT '',
                    `cat_order` mediumint(9) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`cat_id`),
                    KEY `cat_order` (`cat_order`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO categories
                        (cat_id, cat_title, cat_order)
                        VALUES
                        (1, 'System', 2000)
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        // forums

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `forums` (
                    `forum_id` mediumint(9) NOT NULL AUTO_INCREMENT,
                    `cat_id` mediumint(9) NOT NULL DEFAULT '0',
                    `forum_name` varchar(150) NOT NULL DEFAULT '',
                    `forum_desc` text,
                    `forum_status` tinyint(4) NOT NULL DEFAULT '0',
                    `forum_order` mediumint(9) NOT NULL DEFAULT '1',
                    `forum_posts` mediumint(9) NOT NULL DEFAULT '0',
                    `forum_topics` mediumint(9) NOT NULL DEFAULT '0',
                    `forum_last_post_id` mediumint(9) NOT NULL DEFAULT '0',
                    `forum_level` smallint(6) NOT NULL DEFAULT '0',
                    `forum_view_level` smallint(6) NOT NULL DEFAULT '0',
                    `forum_post_level` mediumint(9) NOT NULL DEFAULT '0',
                    `forum_icon` mediumint(9) NOT NULL DEFAULT '0',
                    `private` tinyint(4) NOT NULL DEFAULT '0',
                    `announcement` tinyint(4) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`forum_id`),
                    KEY `forums_order` (`forum_order`),
                    KEY `cat_id` (`cat_id`),
                    KEY `forum_last_post_id` (`forum_last_post_id`),
                    KEY `forum_id` (`forum_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO forums
                        (forum_id, cat_id, forum_name, forum_desc, forum_status, forum_level, forum_view_level, forum_post_level)
                        VALUES
                        (1, 1, 'System', 'System Forums', 0, 1, -1, 1)
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO forums
                        (forum_id, cat_id, forum_name, forum_desc, forum_status, forum_level, forum_view_level, forum_post_level)
                        VALUES
                        (2, 1, 'Deleted Thread House', 'All deleted threads are moved here', 0, 80, 80, 80)
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        // topics 

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `topics` (
                    `topic_id` mediumint(9) NOT NULL AUTO_INCREMENT,
                    `forum_id` smallint(6) NOT NULL DEFAULT '0',
                    `topic_title` char(60) NOT NULL DEFAULT '0',
                    `topic_poster` mediumint(9) NOT NULL DEFAULT '0',
                    `topic_time` int(11) NOT NULL DEFAULT '0',
                    `topic_views` mediumint(9) NOT NULL DEFAULT '0',
                    `topic_replies` mediumint(9) NOT NULL DEFAULT '0',
                    `topic_status` tinyint(4) NOT NULL DEFAULT '0',
                    `topic_type` tinyint(4) NOT NULL DEFAULT '0',
                    `topic_icon` mediumint(9) NOT NULL DEFAULT '0',
                    `topic_first_post_id` mediumint(9) NOT NULL DEFAULT '0',
                    `topic_last_post_id` mediumint(9) NOT NULL DEFAULT '0',
                    `no_votes` mediumint(9) NOT NULL DEFAULT '0',
                    `sum_votes` int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`topic_id`),
                    KEY `forum_id` (`forum_id`),
                    KEY `topic_status` (`topic_status`),
                    KEY `topic_type` (`topic_type`),
                    KEY `topic_first_post_id` (`topic_first_post_id`),
                    KEY `topic_last_post_id` (`topic_last_post_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        // posts

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `posts` (
                    `post_id` mediumint(9) NOT NULL AUTO_INCREMENT,
                    `topic_id` mediumint(9) NOT NULL DEFAULT '0',
                    `forum_id` smallint(6) NOT NULL DEFAULT '0',
                    `poster_id` mediumint(9) NOT NULL DEFAULT '0',
                    `post_time` int(11) NOT NULL DEFAULT '0',
                    `poster_ip` varchar(10) NOT NULL DEFAULT '',
                    `post_edit_time` int(11) NOT NULL DEFAULT '0',
                    `post_edit_count` smallint(6) NOT NULL DEFAULT '0',
                    `post_edit_id` mediumint(9) NOT NULL DEFAULT '0',
                    `posted_from` tinyint(4) NOT NULL DEFAULT '1',
                    `status` tinyint(4) NOT NULL DEFAULT '1',
                    `wow_char_id` mediumint(9) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`post_id`),
                    KEY `forum_id` (`forum_id`),
                    KEY `topic_id` (`topic_id`),
                    KEY `poster_id` (`poster_id`),
                    KEY `post_time` (`post_time`),
                    KEY `status` (`status`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `posts_text` (
                    `post_id` mediumint(9) NOT NULL DEFAULT '0',
                    `post_subject` varchar(60) NOT NULL DEFAULT '',
                    `post_text` text,
                    PRIMARY KEY (`post_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        // search index

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `search_words` (
                    `word_text` varchar(30) NOT NULL,
                    `word_id` int(11) NOT NULL AUTO_INCREMENT,
                    PRIMARY KEY (`word_id`),
                    UNIQUE KEY `uidx_3` (`word_text`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `search_matches` (
                    `post_id` int(11) NOT NULL DEFAULT '0',
                    `word_id` int(11) NOT NULL DEFAULT '0',
                    UNIQUE KEY `uidx_4` (`post_id`,`word_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );
    }

    public function create_users_table() {
        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `users` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `username` varchar(45) DEFAULT NULL,
                    `password` char(32) DEFAULT NULL,
                    `email` varchar(75) DEFAULT NULL,
                    `user_level` mediumint(9) DEFAULT NULL,
                    `status` tinyint(4) DEFAULT NULL,
                    `security` char(8) DEFAULT NULL,
                    `randm` char(8) DEFAULT NULL,
                    `firstname` varchar(150) DEFAULT NULL,
                    `position` varchar(45) DEFAULT NULL,
                    `address` text,
                    `country` varchar(45) DEFAULT NULL,
                    `location` varchar(45) DEFAULT NULL,
                    `phone_number` varchar(20) DEFAULT NULL,
                    `mobile_number` varchar(20) DEFAULT NULL,
                    `bio` text,
                    `reg_date` int(11) DEFAULT NULL,
                    `signature` varchar(255) DEFAULT NULL,
                    `signature_url` varchar(255) DEFAULT NULL,
                    `avatar` int(11) DEFAULT NULL,
                    `pic` int(11) DEFAULT NULL,
                    `visible` tinyint(4) DEFAULT NULL,
                    `colour` varchar(15) DEFAULT NULL,
                    `rss_password` varchar(45) DEFAULT NULL,
                    `current_play` varchar(255) DEFAULT NULL,
                    `steam_id` varchar(255) DEFAULT NULL,
                    `style` mediumint(9) DEFAULT NULL,
                    `longitude` float DEFAULT NULL,
                    `latitude` float DEFAULT NULL,
                    `user_lastvisit` int(11) DEFAULT NULL,
                    `user_lastrefresh` int(11) DEFAULT NULL,
                    `lastname` varchar(45) DEFAULT NULL,
                    `contact_number` varchar(45) DEFAULT NULL,
                    `address1` varchar(255) DEFAULT NULL,
                    `address2` varchar(255) DEFAULT NULL,
                    `postcode` varchar(10) DEFAULT NULL,
                    `town` varchar(45) DEFAULT NULL,
                    `county` varchar(45) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );

        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO users
                        (id, username, user_level)
                        VALUES
                        (1, 'System', -1)
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );
    }

    public function create_sessions_table() {
        switch (config::db_engine()) {
            default:
                $query = "
                    CREATE TABLE `sessions` (
                    `user_id` int(11) NOT NULL,
                    `username` varchar(45) DEFAULT NULL,
                    `security` char(8) DEFAULT NULL,
                    `encode_ip` varchar(8) DEFAULT NULL,
                    `timestamp` int(11) DEFAULT NULL,
                    `randm` char(8) DEFAULT NULL,
                    `user_agent` varchar(150) DEFAULT NULL,
                    `page` varchar(30) DEFAULT NULL,
                    `remember` tinyint(4) DEFAULT NULL,
                    `session_id` varchar(32) DEFAULT NULL,
                    `fixed_ip` tinyint(1) DEFAULT NULL,
                    `domain` varchar(150) DEFAULT NULL,
                    KEY `user_id_idx` (`user_id`),
                    KEY `randm_idx` (`randm`),
                    KEY `timestamp_idx` (`timestamp`),
                    KEY `remember_idx` (`remember`),
                    KEY `security_idx` (`security`),
                    KEY `username_idx` (`username`),
                    KEY `domain_idx` (`domain`),
                    CONSTRAINT `fk_sessions_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                        ";
                break;
        }

        $this->db->sql_query(
                $query
        );
    }

}

?>
