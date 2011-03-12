<?php

/*
  Base user class buisness logic/data access

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

class user_bl extends businesslogic_base {

    public function clear_unregistered_users($long) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        users
                    WHERE
                        status = 0
                        AND
                        reg_date < :reg_date";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':reg_date' => array('value' => $long, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function auth_user($_username, $_password) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        id,
                        security,
                        status,
                        user_lastrefresh,
                        user_level,
                        username
                    FROM
                        users
                    WHERE
                        username = :username
                        AND
                        password = :password";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':username' => array('value' => $_username, 'type' => PDO::PARAM_STR),
            ':password' => array('value' => $_password, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function clear_sessions($id, $user_ip) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        sessions
                    WHERE
                        (
                            user_id = :user_id
                            AND
                            user_agent = :user_agent
                            AND
                            encode_ip = :encode_ip
                            AND
                            domain = :domain
                        )
                        OR
                        (
                            user_id = -1
                            AND
                            encode_ip = :encode_ip2
                            AND
                            user_agent = :user_agent2
                        )";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':user_agent' => array('value' => $_SERVER['HTTP_USER_AGENT'], 'type' => PDO::PARAM_STR),
            ':encode_ip' => array('value' => $user_ip, 'type' => PDO::PARAM_STR),
            ':domain' => array('value' => config::domain(), 'type' => PDO::PARAM_STR),
            ':user_agent2' => array('value' => $_SERVER['HTTP_USER_AGENT'], 'type' => PDO::PARAM_STR),
            ':encode_ip2' => array('value' => $user_ip, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function insert_session($id, $session_username, $session_security, $user_ip, $session_random, $remember, $user_agent) {
        if ($remember === null) {
            $remember = 0;
        }
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        sessions
                        (
                            user_id,
                            username,
                            security,
                            encode_ip,
                            timestamp,
                            randm,
                            user_agent,
                            remember,
                            domain
                        )
                    VALUES
                        (
                            :user_id,
                            :username,
                            :security,
                            :encode_ip,
                            :timestamp,
                            :randm,
                            :user_agent,
                            :remember,
                            :domain
                        )";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':username' => array('value' => $session_username, 'type' => PDO::PARAM_STR),
            ':security' => array('value' => $session_security, 'type' => PDO::PARAM_STR),
            ':encode_ip' => array('value' => $user_ip, 'type' => PDO::PARAM_STR),
            ':timestamp' => array('value' => time(), 'type' => PDO::PARAM_INT),
            ':randm' => array('value' => $session_random, 'type' => PDO::PARAM_STR),
            ':user_agent' => array('value' => $user_agent, 'type' => PDO::PARAM_STR),
            ':remember' => array('value' => $remember, 'type' => PDO::PARAM_INT),
            ':domain' => array('value' => config::domain(), 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function update_user_last_refresh($id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                        user_lastrefresh = :last_refresh
                    WHERE
                        id = :id";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':last_refresh' => array('value' => time(), 'type' => PDO::PARAM_INT),
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function update_user_last_visit($id, $last_visit) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                        user_lastvisit = :user_lastvisit,
                        user_lastrefresh = :last_refresh
                    WHERE
                        id = :id";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_lastvisit' => array('value' => $last_visit, 'type' => PDO::PARAM_INT),
            ':last_refresh' => array('value' => time(), 'type' => PDO::PARAM_INT),
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function update_session($user_agent, $id, $session_random) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        sessions
                    SET
                        timestamp = :timestamp,
                        user_agent = :user_agent
                    WHERE
                        user_id = :user_id
                        AND
                        randm = :randm";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':timestamp' => array('value' => time(), 'type' => PDO::PARAM_INT),
            ':user_agent' => array('value' => $user_agent, 'type' => PDO::PARAM_STR),
            ':user_id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':randm' => array('value' => $session_random, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function clear_old_sessions($time, $day, $long) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        sessions
                    WHERE
                        (
                            user_id = -1
                            AND
                            timestamp < :time
                         )
                         OR
                         (
                            user_id > 1
                            AND
                            timestamp < :day
                            AND
                            remember = 1
                         )
                         OR
                         (
                            user_id > 1
                            AND
                            timestamp < :long
                            AND
                            remember = 2
                         )";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':time' => array('value' => $time, 'type' => PDO::PARAM_INT),
            ':day' => array('value' => $day, 'type' => PDO::PARAM_INT),
            ':long' => array('value' => $long, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_old_password($id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        password
                    FROM
                        users
                    WHERE
                        id = :id";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function update_password($new_password, $id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                        password = :password
                    WHERE
                        id = :id";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':password' => array('value' => md5($new_password . config::salt()), 'type' => PDO::PARAM_STR),
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_user_contact_details($user_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        address,
                        firstname,
                        country,
                        location,
                        phone_number,
                        mobile_number,
                        email
                    FROM
                        users
                    WHERE
                        id = :user_id";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_user_by_email($email) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        id,
                        username,
                        email,
                        status,
                        randm,
                        password,
                        security
                    FROM
                        users
                    WHERE
                        email = :email";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':email' => array('value' => $email, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function get_user_by_sec($user_id, $sec) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        id,
                        username,
                        email,
                        status,
                        randm,
                        password,
                        security
                    FROM
                        users
                    WHERE
                        id = :id
                        AND
                        randm = :randm";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':randm' => array('value' => $sec, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function get_user_by_security_code($user_id, $security_code) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        id,
                        username,
                        email,
                        status,
                        randm,
                        password,
                        security
                    FROM
                        users
                    WHERE
                        security = :security_code
                        AND
                        id = :user_id";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':security_code' => array('value' => $security_code, 'type' => PDO::PARAM_STR),
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function activate_user($user_id) {
        $this->update_user_count();
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                        status = 2
                    WHERE
                        id = :id";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function activate_user_for_admin($user_id) {
        $this->update_user_count();
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                        status = 3,
                        user_lastvisit = :time,
                        user_lastrefresh = :time2
                    WHERE
                        id = :id";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':id' => array('value' => $user_id, 'type' => PDO::PARAM_INT),
            ':time' => array('value' => time(), 'type' => PDO::PARAM_INT),
            ':time2' => array('value' => time(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_security_code($activation_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        security
                    FROM
                        activation
                    WHERE
                        activation_id = :activation_id";
                break;
        }
        $return = $this->db->sql_select(
                        $query, array(
                    ':activation_id' => array('value' => $activation_id, 'type' => PDO::PARAM_STR)
                        )
        );
        if (isset($return[0]['security'])) {
            return $return[0]['security'];
        } else {
            return '';
        }
    }

    public function get_mini_user_by_username_or_email($username, $email) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        username,
                        email
                    FROM
                        users
                    WHERE
                        username = :username
                        OR
                        email = :email";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':username' => array('value' => $username, 'type' => PDO::PARAM_STR),
            ':email' => array('value' => $email, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function add_user($new_user_array) {
        if (array_key_exists('user_level', $new_user_array) == false) {
            $array = array('user_level' => array('value' => 1, 'type' => PDO::PARAM_INT));
            $new_user_array = array_merge($new_user_array, $array);
        }

        if (array_key_exists('status', $new_user_array) == false) {
            $array = array('status' => array('value' => 0, 'type' => PDO::PARAM_INT));
            $new_user_array = array_merge($new_user_array, $array);
        }

        if (array_key_exists('reg_date', $new_user_array) == false) {
            $array = array('reg_date' => array('value' => time(), 'type' => PDO::PARAM_INT));
            $new_user_array = array_merge($new_user_array, $array);
        }

        $temp_array = array();
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        users
                        (";
                foreach ($new_user_array AS $key => $value) {
                    $temp_array[] = '`' . $key . '`';
                }
                $query .= join(', ', $temp_array) . "
                        )
                    VALUES
                        (";
                $temp_array = array();
                foreach ($new_user_array AS $key => $value) {
                    $temp_array[] = ':' . $key;
                }
                $query .= join(', ', $temp_array) . "
                        )";
                break;
        }
        $param_array = array();
        foreach ($new_user_array AS $key => $value) {
            $array = array(':' . $key => $value);
            $param_array = array_merge($array, $param_array);
        }

        $this->db->sql_query(
                $query, $param_array
        );
        return $this->db->sql_insert_id('users');
    }

    public function update_random($id, $ref_id, $textstr) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        users
                    SET
                        security = :security
                    WHERE
                        id = :id,
                        randm = :randm,
                        status = 0";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':security' => array('value' => $textstr, 'type' => PDO::PARAM_STR),
            ':id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':randm' => array('value' => $ref_id, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function get_user($user_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        id,
                        username,
                        email,
                        status,
                        randm,
                        password,
                        security
                    FROM
                        users
                    WHERE
                        id = :id";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_user_count() {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        COUNT(id) AS users
                    FROM
                        users
                    WHERE
                        status = 2";
                break;
        }
        $count = $this->db->sql_select($query);
        return $count[0]['users'];
    }

    public function update_user_count() {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        stats
                    SET
                        s_value = :count
                    WHERE
                        s_name = 'USERS'";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':count' => array('value' => $this->get_user_count(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function remove_activation_record($activation_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        activation
                    WHERE
                        activation_id = :activation_id AND timestamp <= :date";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':activation_id' => array('value' => $activation_id, 'type' => PDO::PARAM_STR),
            ':date' => array('value' => time() - 86400, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function add_registration_code($activation_code, $security_code) {
        switch (config::db_engine()) {
            default:
                $query = "
                    INSERT INTO
                        activation
                        (
                            activation_id,
                            security,
                            timestamp
                        )
                    VALUES
                        (
                            :activation_id,
                            :security,
                            :date
                        )
                        ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':activation_id' => array('value' => $activation_code, 'type' => PDO::PARAM_STR),
            ':security' => array('value' => $security_code, 'type' => PDO::PARAM_STR),
            ':date' => array('value' => time(), 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_session_data($session_security, $session_username, $session_random) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        se.session_id,
                        se.encode_ip,
                        us.id,
                        us.username,
                        us.email,
                        us.user_level,
                        us.user_lastvisit,
                        us.user_lastrefresh,
                        us.address,
                        us.phone_number,
                        style,
                        rss_password
                    FROM
                        sessions AS se
                    LEFT JOIN
                        users AS us
                        ON
                        us.id = se.user_id
                    WHERE
                        se.security = :security
                        AND
                        se.username = :username
                        AND
                        se.randm = :randm
                        AND
                        se.domain = :domain
                        ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':security' => array('value' => $session_security, 'type' => PDO::PARAM_STR),
            ':username' => array('value' => $session_username, 'type' => PDO::PARAM_STR),
            ':randm' => array('value' => $session_random, 'type' => PDO::PARAM_STR),
            ':domain' => array('value' => config::domain(), 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function get_session($id, $user_agent, $security, $user_ip) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        user_id
                    FROM
                        sessions
                    WHERE
                        user_id = :user_id
                        AND
                        user_agent = :user_agent
                        AND
                        encode_ip = :encode_ip
                        AND
                        domain = :domain
                        AND
                        security = :security";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':user_id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':user_agent' => array('value' => $user_agent, 'type' => PDO::PARAM_STR),
            ':encode_ip' => array('value' => $user_ip, 'type' => PDO::PARAM_STR),
            ':domain' => array('value' => config::domain(), 'type' => PDO::PARAM_STR),
            ':security' => array('value' => $security, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function delete_session($id, $user_agent, $user_ip) {
        switch (config::db_engine()) {
            default:
                $query = "
                    DELETE FROM
                        sessions
                    WHERE
                        user_id = :user_id
                        AND
                        user_agent = :user_agent
                        AND
                        encode_ip = :encode_ip
                        AND
                        domain = :domain";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':user_id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':user_agent' => array('value' => $user_agent, 'type' => PDO::PARAM_STR),
            ':encode_ip' => array('value' => $user_ip, 'type' => PDO::PARAM_STR),
            ':domain' => array('value' => config::domain(), 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function update_user_page($name, $id, $session_random) {
        switch (config::db_engine()) {
            default:
                $query = "
                    UPDATE
                        sessions
                    SET
                        page = :page
                    WHERE
                        user_id = :user_id
                        AND
                        randm = :session_random
                ";
                break;
        }
        $this->db->sql_query(
                $query, array(
            ':page' => array('value' => $name, 'type' => PDO::PARAM_STR),
            ':user_id' => array('value' => $id, 'type' => PDO::PARAM_INT),
            ':session_random' => array('value' => $session_random, 'type' => PDO::PARAM_STR)
                )
        );
    }

    public function update_profile($fields, $parameters) {
        $this->db->sql_query('UPDATE 
                users
            SET 
                ' . $fields . '
            WHERE
                id = :id', $parameters);
    }

    public function update_bio($user_id, $bio) {
        $this->db->sql_query('
            REPLACE INTO
                biography
                (bio, user_id)
            VALUES
                (:bio, :user_id)
            ', array(
            ':bio' => array('value' => $bio, 'type' => PDO::PARAM_STR),
            ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_full_profile_details($user_id) {
        $array = array();
        foreach (profile_config::profile_list() AS $key => $value) {
            if ($value['db'] == true) {
                $array[] = $key;
            }
        }

        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT " . implode(", ", $array) . ",
                        iw1.header AS ava_h,
                        iw2.header AS pic_h
                    FROM
                        users
                    LEFT JOIN
                        subscriptions
                        ON
                        user_id = id
                    LEFT JOIN
                        image_warehouse AS iw1
                        ON
                        iw1.image_id = avatar
                    LEFT JOIN
                        image_warehouse AS iw2
                        ON
                        iw2.image_id = pic
                    LEFT JOIN
                        biography
                        ON
                        biography.user_id = id
                    WHERE
                        id = :id
                        ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

    public function get_mini_profile_details($user_id) {
        switch (config::db_engine()) {
            default:
                $query = "
                    SELECT
                        user_level,
                        email
                    FROM
                        users
                    WHERE
                        id = :id
                        ";
                break;
        }
        return $this->db->sql_select(
                $query, array(
            ':id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
                )
        );
    }

}

?>
