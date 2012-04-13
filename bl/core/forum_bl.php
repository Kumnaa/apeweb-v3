<?php

/*
  Forum business logic class

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

class forum_bl extends businesslogic_base {

    public function insert_post($post, $subject, $user_id, $user_ip, $forum, $topic, $icon = 0) {
        $this->db->begin_transaction();
        try {
            // insert post details
            switch (config::db_engine()) {
                default:
                    $query = "
                    INSERT INTO
                        posts
                        (
                            poster_id,
                            post_time,
                            poster_ip,
                            forum_id,
                            topic_id,
                            status
                        )
                        VALUES
                        (
                            :poster_id,
                            :post_time,
                            :poster_ip,
                            :forum_id,
                            :topic_id,
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
                ':forum_id' => array('value' => $forum, 'type' => PDO::PARAM_INT),
                ':topic_id' => array('value' => $topic, 'type' => PDO::PARAM_INT),
                ':status' => array('value' => 1, 'type' => PDO::PARAM_INT)
                    )
            );

            // get post id
            $last_post = $this->db->sql_insert_id('posts', 'post_id');

            // insert post text
            switch (config::db_engine()) {
                default:
                    $query = "
                    INSERT INTO
                        posts_text
                        (
                            post_id,
                            post_text,
                            post_subject
                        )
                        VALUES
                        (
                            :post_id,
                            :post_text,
                            :post_subject
                        )
                ";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':post_id' => array('value' => $last_post, 'type' => PDO::PARAM_INT),
                ':post_text' => array('value' => $post, 'type' => PDO::PARAM_STR),
                ':post_subject' => array('value' => $subject, 'type' => PDO::PARAM_STR)
                    )
            );

            // if this is a new topic
            if ($topic == 0) {
                // insert topic details
                switch (config::db_engine()) {
                    default:
                        $query = "
                        INSERT INTO
                            topics
                            (
                                topic_title,
                                topic_poster,
                                topic_time,
                                topic_first_post_id,
                                topic_last_post_id,
                                forum_id,
                                topic_icon,
                                topic_replies
                            )
                            VALUES
                            (
                                :topic_title,
                                :topic_poster,
                                :topic_time,
                                :topic_first_post_id,
                                :topic_last_post_id,
                                :forum_id,
                                :topic_icon,
                                :topic_replies
                            )
                    ";
                        break;
                }
                $this->db->sql_query(
                        $query, array(
                    ':topic_title' => array('value' => $subject, 'type' => PDO::PARAM_STR),
                    ':topic_poster' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
                    ':topic_time' => array('value' => time(), 'type' => PDO::PARAM_INT),
                    ':topic_first_post_id' => array('value' => $last_post, 'type' => PDO::PARAM_INT),
                    ':topic_last_post_id' => array('value' => $last_post, 'type' => PDO::PARAM_INT),
                    ':forum_id' => array('value' => $forum, 'type' => PDO::PARAM_INT),
                    ':topic_icon' => array('value' => $icon, 'type' => PDO::PARAM_INT),
                    ':topic_replies' => array('value' => 0, 'type' => PDO::PARAM_INT)
                        )
                );

                // get topic id
                $topic = $this->db->sql_insert_id('topics', 'topic_id');

                // update post details with the topic id
                switch (config::db_engine()) {
                    default:
                        $query = "
                        UPDATE
                            posts
                        SET
                            topic_id = :topic
                        WHERE
                            post_id = :post
                    ";
                        break;
                }
                $this->db->sql_query(
                        $query, array(
                    ':topic' => array('value' => $topic, 'type' => PDO::PARAM_INT),
                    ':post' => array('value' => $last_post, 'type' => PDO::PARAM_INT)
                        )
                );
            }

            // update topic details
            switch (config::db_engine()) {
                default:
                    $query = "
                        UPDATE
                            topics
                        SET
                            topic_last_post_id = :post,
                            topic_replies = IF(topic_replies > 0, topic_replies + 1, 0)
                        WHERE
                            topic_id = :topic
                    ";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':topic' => array('value' => $topic, 'type' => PDO::PARAM_INT),
                ':post' => array('value' => $last_post, 'type' => PDO::PARAM_INT)
                    )
            );

            // update forum details
            switch (config::db_engine()) {
                default:
                    $query = "
                    UPDATE
                         forums
                    SET
                        forum_last_post_id = :post
                    WHERE
                        forum_id = :forum
                ";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':forum' => array('value' => $forum, 'type' => PDO::PARAM_INT),
                ':post' => array('value' => $last_post, 'type' => PDO::PARAM_INT)
                    )
            );

            $this->update_last_read_post_id($user_id, $topic, $last_post);
            $this->update_user_post_count($user_id);
            $this->db->end_transaction();
        } catch (Exception $ex) {
            $this->db->rollback_transaction();
            throw new Exception("Error adding post.");
        }
    }

    public function update_user_post_count($user_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                    	user_posts = user_posts + 1
                    WHERE
                    	id = :user_id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function delete_last_read_post($user_id, $topic) {
        // delete read topic details for user
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        read_topics
                    WHERE
                    	user_id = :user_id
                    	AND
                    	topic_id = :topic_id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':topic_id' => array('value' => $topic, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function update_last_read_post_id($user_id, $topic, $last_post) {
        $this->delete_last_read_post($user_id, $topic);
        // add read topic details for user
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        read_topics
                        (
                            user_id,
                            topic_id,
                            last_post_id,
                            timestamp
                        )
                        VALUES
                        (
                            :user_id,
                            :topic_id,
                            :last_post_id,
                            :timestamp
                        )
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':topic_id' => array('value' => $topic, 'type' => PDO::PARAM_INT),
            ':last_post_id' => array('value' => $last_post, 'type' => PDO::PARAM_INT),
            ':timestamp' => array('value' => time(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_latest_topics($last_visit, $level) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        topics.topic_id,
                        topics.forum_id,
                        topics.topic_last_post_id,
                        posts.post_time
                    FROM
                        topics
                    LEFT JOIN
                        posts
                        ON
                        posts.post_id = topics.topic_last_post_id
                    LEFT JOIN
                        forums
                        ON
                        forums.forum_id = topics.forum_id
                    LEFT JOIN
                        categories
                        ON
                        categories.cat_id = forums.cat_id
                    WHERE
                        posts.post_time > :post_time
                        AND
                        forums.forum_level <= :forum_level
                        AND
                        topics.topic_id > :topic_id
                        AND
                        forums.forum_status = :forum_status
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':post_time' => array('value' => $last_visit, 'type' => PDO::PARAM_INT),
            ':forum_level' => array('value' => $level, 'type' => PDO::PARAM_INT),
            ':topic_id' => array('value' => 0, 'type' => PDO::PARAM_INT),
            ':forum_status' => array('value' => 0, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_topic_posts($topic_id, $page, $status) {
        if ($page > 0) {
            $page = $page - 1;
        }

        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        p.post_id,
                        p.topic_id,
                        p.poster_id,
                        p.post_time,
                        p.post_edit_count,
                        p.post_edit_time,
                        p.poster_ip,
                        pt.post_text,
                        u.username,
                        u.subscriber,
                        u.reg_date,
                        u.user_level,
                        u.user_posts,
                        u.location,
                        u.country,
                        u.current_play,
                        u.signature,
                        u.avatar,
                        u.donation,
                        u.signature_url,
                        iw.header,
                        u.status,
                        u.title,
                        u.colour,
                        ranks.rank_colour,
                        ranks.title AS rank_title,
                        p.status AS deleted
                    FROM
                        posts AS p
                    LEFT JOIN
                        users AS u
                        ON
                        u.id = p.poster_id
                    LEFT JOIN
                        posts_text AS pt
                        ON
                        pt.post_id = p.post_id
                    LEFT JOIN
                        image_warehouse AS iw
                        ON
                        iw.image_id = u.avatar
                    LEFT JOIN
                        ranks
                        ON
                        level = user_level
                    WHERE
                        topic_id = :topic_id
                        AND
                        p.status <= :status
                    ORDER BY
                        p.post_id ASC
                    LIMIT
                        :limit
                    OFFSET
                        :offset
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT),
            ':limit' => array('value' => forum_config::$page_limit, 'type' => PDO::PARAM_INT),
            ':offset' => array('value' => forum_config::$page_limit * $page, 'type' => PDO::PARAM_INT),
            ':status' => array('value' => $status, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function update_post($post_id, $post_text, $time, $user_id) {
        $this->db->begin_transaction();
        try {
            // update post text
            switch (config::db_engine()) {
                default:
                    $query = "
                    UPDATE
                        posts_text
                    SET
                        post_text = :post_text
                    WHERE
                        post_id = :post_id;";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':post_text' => array('value' => $post_text, 'type' => PDO::PARAM_STR),
                ':post_id' => array('value' => $post_id, 'type' => PDO::PARAM_INT)
                    )
            );

            // update post details
            switch (config::db_engine()) {
                default:
                    $query = "
                    UPDATE
                        posts
                    SET
                        post_edit_count = post_edit_count + 1,
                        post_edit_time = :post_edit_time,
                        post_edit_id = :post_edit_id
                    WHERE
                        post_id = :post_id;";
                    break;
            }
            $this->db->sql_query(
                    $query, array(
                ':post_edit_time' => array('value' => $time, 'type' => PDO::PARAM_INT),
                ':post_edit_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
                ':post_id' => array('value' => $post_id, 'type' => PDO::PARAM_INT)
                    )
            );

            $this->db->end_transaction();
        } catch (Exception $ex) {
            $this->db->rollback_transaction();
            throw new Exception("Error saving post.");
        }
    }

    public function update_topic_views($topic_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        topics
                    SET
                        topic_views = topic_views + 1
                    WHERE
                        topic_id = :topic_id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_post_details($post_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        forum_level,
                        forum_view_level,
                        forum_post_level,
                        forum_name,
                        forums.forum_id,
                        topic_title,
                        post_text,
                        poster_id,
                        posts.topic_id,
                        topics.topic_first_post_id
                    FROM
                        posts
                    LEFT JOIN
                        posts_text
                        ON
                        posts_text.post_id = posts.post_id
                    LEFT JOIN
                        topics
                        ON
                        topics.topic_id = posts.topic_id
                    LEFT JOIN
                        forums
                        ON
                        topics.forum_id = forums.forum_id
                    WHERE
                        posts.post_id = :post_id
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(':post_id' => array('value' => $post_id, 'type' => PDO::PARAM_INT))
        );
    }

    public function get_topic_details($topic_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        forum_level,
                        forum_view_level,
                        forum_post_level,
                        forum_name,
                        forums.forum_id,
                        topic_title
                    FROM
                        topics
                    LEFT JOIN
                        forums
                        ON
                        topics.forum_id = forums.forum_id
                    WHERE
                        topic_id = :topic_id
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function move_topic($topic_id, $old_forum_id, $new_forum_id) {
        // get post count
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        COUNT(post_id) AS posts
                    FROM
                        posts
                    WHERE
                        posts.topic_id = :topic_id
                ";
                break;
        }
        
        $result = $this->db->sql_select(
            $query, array(
                ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT)
            )
        );

        $posts = $result[0]['posts'];
        
        // update topic
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE 
                        topics 
                    SET
                        forum_id = :forum_id
                    WHERE
                        topic_id = :topic_id
                ";
                break;
        }
        $this->db->sql_query(
            $query, array(
                ':forum_id' => array('value' => $new_forum_id, 'type' => PDO::PARAM_INT),
                ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT)
            )
        );
        
        // update posts
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE 
                        posts 
                    SET
                        forum_id = :forum_id
                    WHERE
                        topic_id = :topic_id
                ";
                break;
        }
        $this->db->sql_query(
            $query, array(
                ':forum_id' => array('value' => $new_forum_id, 'type' => PDO::PARAM_INT),
                ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT)
            )
        );

        $this->update_last_post_id_for_forum($new_forum_id, 1, $posts);
        $this->update_last_post_id_for_forum($old_forum_id, -1, -$posts);
    }
    
    public function update_last_post_id_for_forum($forum_id, $topics_change = 0, $posts_change = 0) {
        // get current last post id
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        posts.post_id
                    FROM
                        topics
                    LEFT JOIN
                        posts
                    ON
                        posts.post_id = topics.topic_last_post_id
                    WHERE
                        topics.forum_id = :forum_id
                    ORDER BY
                        posts.post_time DESC
                    LIMIT 1
                ";
                break;
        }
        $post = $this->db->sql_select(
                $query, array(
                    ':forum_id' => array('value' => $forum_id, 'type' => PDO::PARAM_INT)
                )
        );
        
        if (is_array($post) && count($post) > 0) {
            switch (config::db_engine()) {
                default:
                    $query = "
                        UPDATE 
                            forums
                        SET
                            forum_last_post_id = :post_id,
                            forum_topics = forum_topics + :topics,
                            forum_posts = forum_posts + :posts
                        WHERE
                            forum_id = :forum_id
                    ";
                    break;
            }
            $this->db->sql_query(
                $query, array(
                    ':post_id' => array('value' => $post[0]['post_id'], 'type' => PDO::PARAM_INT),
                    ':topics' => array('value' => $topics_change, 'type' => PDO::PARAM_INT),
                    ':posts' => array('value' => $posts_change, 'type' => PDO::PARAM_INT),
                    ':forum_id' => array('value' => $forum_id, 'type' => PDO::PARAM_INT)
                )
            );
        }
    }
    
    public function get_topic_id_from_post_id($post_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        topic_id
                    FROM
                        posts
                    WHERE
                        post_id = :post_id
                ";
                break;
        }
        $post = $this->db->sql_select(
                        $query, array(
                    ':post_id' => array('value' => $post_id, 'type' => PDO::PARAM_INT)
                        )
        );
        return ($post[0]['topic_id']);
    }

    public function count_posts_in_topic($topic_id, $status) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        COUNT(post_id) AS total_posts
                    FROM
                        posts
                    WHERE
                        topic_id = :topic_id
                        AND
                        status <= :status
                ";
                break;
        }
        $count = $this->db->sql_select(
                        $query, array(
                    ':topic_id' => array('value' => $topic_id, 'type' => PDO::PARAM_INT),
                    ':status' => array('value' => $status, 'type' => PDO::PARAM_INT)
                        )
        );
        return ($count[0]['total_posts']);
    }

    public function get_forum_list($user_level) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        ct.cat_title,
                        ct.cat_id,
                        fm.forum_id,
                        fm.forum_name,
                        fm.forum_desc,
                        fm.forum_level,
                        fm.forum_view_level,
                        fm.forum_topics,
                        fm.forum_posts,
                        fm.forum_last_post_id,
                        p.post_id,
                        p.post_time,
                        p.poster_id,
                        u.username,
                        u.user_level,
                        u.id,
                        colour,
                        r.rank_colour
                    FROM
                        categories AS ct
                    RIGHT JOIN
                        forums AS fm
                        ON
                        fm.cat_id = ct.cat_id
                    LEFT JOIN
                        posts AS p
                        ON
                        p.post_id = fm.forum_last_post_id
                    LEFT JOIN
                        users AS u
                        ON
                        u.id = p.poster_id
                    LEFT JOIN
                        ranks AS r
                        ON
                        level = user_level
                    WHERE
                        fm.forum_status = :forum_status
                        AND
                        fm.forum_view_level <= :user_level
                    ORDER BY
                        ct.cat_order ASC,
                        fm.forum_order ASC
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':forum_status' => array('value' => 0, 'type' => PDO::PARAM_INT),
            ':user_level' => array('value' => $user_level, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_forum_details($forum_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        forum_name,
                        forum_desc,
                        forum_level,
                        forum_view_level,
                        forum_post_level
                    FROM
                        forums
                    WHERE
                        forum_id = :forum_id
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':forum_id' => array('value' => $forum_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_global_topics($user) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        t.topic_id,
                        t.topic_title,
                        post_icons.url,
                        t.topic_replies,
                        t.topic_views,
                        t.topic_type,
                        t.topic_last_post_id,
                        topic_poster,
                        p.post_time,
                        u.username,
                        u2.username AS last_user,
                        u.user_level,
                        u2.user_level AS last_user_level,
                        p.poster_id,
                        u.colour,
                        u2.colour AS poster_colour,
                        r.rank_colour,
                        r2.rank_colour AS poster_rank_colour,
                        topic_status
                    FROM
                        topics as t
                    LEFT JOIN
                        posts AS p
                        ON
                        p.post_id = t.topic_last_post_id
                    LEFT JOIN
                        posts AS p2
                        ON
                        p2.post_id = t.topic_first_post_id
                    LEFT JOIN
                        users AS u
                        ON
                        u.id = t.topic_poster
                    LEFT JOIN
                        users AS u2
                        ON
                        u2.id = p.poster_id
                    LEFT JOIN
                        post_icons
                        ON
                        post_icons.id = t.topic_icon
                    LEFT JOIN
                        ranks AS r
                        ON
                        r.level = u.user_level
                    LEFT JOIN
                        ranks AS r2
                        ON
                        r2.level = u2.user_level
                    LEFT JOIN
                        forums AS f
                        ON
                        f.forum_id = t.forum_id
                    WHERE
                        t.topic_id > 0
                        AND
                        t.topic_type = :topic_type
                        AND
                        f.forum_view_level <= :user_level
                        AND
                        t.forum_id > 0
                    ORDER BY
                        t.topic_type DESC,
                        p.post_id DESC
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':topic_type' => array('value' => forum_config::$globalannouncement, 'type' => PDO::PARAM_INT),
            ':user_level' => array('value' => $user->get_level(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function count_forum_topics($forum_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        COUNT(topic_id) AS count
                    FROM
                        topics
                    WHERE
                        forum_id = :forum_id
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':forum_id' => array('value' => $forum_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_read_topics($user_id, $last_visit) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `topic_id`, `last_post_id`
                    FROM
                        `read_topics`
                    WHERE
                        timestamp > :last_visit
                        AND
                        user_id = :user_id
                    ORDER BY
                        timestamp DESC
                    LIMIT 200
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':last_visit' => array('value' => $last_visit, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_read_private_topics($user_id, $last_visit) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `topic_id`, `last_post_id`
                    FROM
                        `priv_read_topics`
                    WHERE
                        timestamp > :last_visit
                        AND
                        user_id = :user_id
                    ORDER BY
                        timestamp DESC
                    LIMIT 200
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':last_visit' => array('value' => $last_visit, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_read_group_topics($user_id, $last_visit) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        `topic_id`, `last_post_id`
                    FROM
                        `groups_read_topics`
                    WHERE
                        timestamp > :last_visit
                        AND
                        user_id = :user_id
                    ORDER BY
                        timestamp DESC
                    LIMIT 200
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':last_visit' => array('value' => $last_visit, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function clear_read_topics($user_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        read_topics
                    WHERE
                        user_id = :user_id
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_categories() {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        cat_id,
                        cat_title,
                        cat_order
                    FROM
                        categories
                    ORDER BY
                        cat_order
                ";
                break;
        }
        $this->db->sql_query($query);
    }

    public function get_forum_by_id($user, $forum_id, $page) {
        if ($page > 0) {
            $page = $page - 1;
        }
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        t.topic_id,
                        t.topic_title,
                        post_icons.url,
                        t.topic_replies,
                        t.topic_views,
                        t.topic_type,
                        t.topic_last_post_id,
                        topic_poster,
                        p.post_time,
                        u.username,
                        u2.username AS last_user,
                        u.user_level,
                        u2.user_level AS last_user_level,
                        p.poster_id,
                        u.colour,
                        u2.colour AS poster_colour,
                        r.rank_colour,
                        r2.rank_colour AS poster_rank_colour,
                        topic_status
                    FROM
                        topics as t
                    LEFT JOIN
                        posts AS p
                        ON
                        p.post_id = t.topic_last_post_id
                    LEFT JOIN
                        posts AS p2
                        ON
                        p2.post_id = t.topic_first_post_id
                    LEFT JOIN
                        users AS u
                        ON
                        u.id = t.topic_poster
                    LEFT JOIN
                        users AS u2
                        ON
                        u2.id = p.poster_id
                    LEFT JOIN
                        post_icons
                        ON
                        post_icons.id = t.topic_icon
                    LEFT JOIN
                        ranks AS r
                        ON
                        r.level = u.user_level
                    LEFT JOIN
                        ranks AS r2
                        ON
                        r2.level = u2.user_level
                    LEFT JOIN
                        forums AS f
                        ON
                        f.forum_id = t.forum_id
                    WHERE
                        t.topic_id > 0
                        AND
                        f.forum_view_level <= :user_level
                        AND
                        t.forum_id = :forum_id
                    ORDER BY
                        t.topic_type DESC,
                        p.post_id DESC
                    LIMIT :limit OFFSET :offset
                ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':user_level' => array('value' => $user->get_level(), 'type' => PDO::PARAM_INT),
            ':forum_id' => array('value' => $forum_id, 'type' => PDO::PARAM_INT),
            ':limit' => array('value' => forum_config::$page_limit, 'type' => PDO::PARAM_INT),
            ':offset' => array('value' => forum_config::$page_limit * $page, 'type' => PDO::PARAM_INT)
                )
        );
    }

}

?>
