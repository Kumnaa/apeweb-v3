<?php

/*
  SQL access object

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

class db_connector {

    protected $sql_db;
    protected $created_tables;
    protected $statement;
    protected $arguments;
    protected $last_query_time;
    public $queries = 0;
    public $query_list;
    public $no_queries = false;
    public $db_name;
    public $error;
    public $file;
    public $debug;
    private $transaction = false;

    public function __construct() {
        $this->query_list = array();
        $this->error = false;
        $this->created_tables = array();
        $this->last_query_time = 0;
        $this->db_connect();
    }

    //connect function - just kills the script if no db is available
    protected function db_connect() {
        try {
            switch (config::db_engine()) {
                case "mysql":
                    $this->sql_db = new PDO("mysql:host=" . config::db_host() . ";dbname=" . config::db_database(), config::db_username(), config::db_password());
                    break;

                case "mssql":
                    $this->sql_db = new PDO("odbc:Driver={SQL Native Client};Server=" . config::db_host() . "\\" . config::db_instance() . ";Database=" . config::db_database() . ";Uid=" . config::db_username() . ";Pwd=" . config::db_password() . ";");
                    break;

                case "postgresql":
                    $this->sql_db = new PDO("pgsql:host=" . config::db_host() . ";dbname=" . config::db_database(), config::db_username(), config::db_password());
                    break;
            }
            $this->sql_db->exec("SET NAMES 'utf8'");
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }

    //closes the db
    public function sql_close() {
        if ($this->error === true) {
            apetech::error_email('Error: ' . implode('<br />', $this->query_list) . '<br /><br />' . nl2br($this->extra_debug_info()));
        }
        if ($this->debug === true) {
            return implode('<br />', $this->query_list);
        } else {
            return '';
        }
    }

    public function begin_transaction() {
        $this->sql_db->beginTransaction();
        $this->add_debug_info('begin transaction', null, null, true);
        $this->transaction = true;
    }

    public function rollback_transaction() {
        $this->sql_db->rollBack();
        $this->add_debug_info('rollback transaction', null, null, true);
        $this->transaction = false;
    }

    public function end_transaction() {
        $this->sql_db->commit();
        $this->add_debug_info('end transaction', null, null, true);
        $this->transaction = false;
    }

    protected function prepare($statement, $arguments) {
        $sth = $this->sql_db->prepare($statement);
        if ($sth === false) {
            $error = $this->sql_db->errorInfo();
            throw new Exception($error[2]);
        } else {
            return $sth;
        }
    }

    protected function execute($sth, $arguments) {
        $start_time = round(microtime(), 6);
        $this->queries += 1;
        foreach ($arguments AS $key => $value) {
            if (isset($value['length'])) {
                if (@!$sth->bindParam($key, $value['value'], $value['type'], $value['length'])) {
                    $error = $sth->errorInfo();
                    throw new Exception("Error binding paramters: " . print_r($error, true));
                }
            } else {
                if (@!$sth->bindParam($key, $value['value'], $value['type'])) {
                    $error = $sth->errorInfo();
                    throw new Exception("Error binding paramters: " . print_r($error, true));
                }
            }
        }

        $execute = @$sth->execute();

        $this->last_query_time = round(microtime(), 6) - $start_time;
        if ($execute === false) {
            $error = $sth->errorInfo();
            throw new Exception("Error executing sql: " . print_r($error, true));
        } else {
            return $sth;
        }
    }

    protected function extra_debug_info() {
        try {
            $return = array('');
            switch (config::db_engine()) {
                case "mysql":
                    //$return = $this->sql_select('SHOW INNODB STATUS');
                    break;
            }
            return print_r($return[0], true);
        } catch (Exception $ex) {
            return 'Error getting extra debug info: ' . $ex->getMessage();
        }
    }

    protected function add_debug_info($statement, $arguments, $sth, $success, $message = '') {
        if ($success === true) {
            $colour = 'green';
        } else {
            $this->error = true;
            $colour = 'red';
            $this->log_error($statement, $arguments, $message);
            if ($this->transaction === true) {
                $this->transaction = false;
                throw new Exception("Transaction error.");
            }
        }

        $this->query_list[] = '<br />
            <span style="color:' . $colour . '">' . html::clean_text($statement) . '</span><br />
            ' . print_r($arguments, true) . '<br />
            Time: ' . html::clean_text($this->last_query_time) . '<br />
            Message: ' . html::clean_text($message) . '<br />
            ';
    }

    public function sql_select($statement, $arguments = array()) {
        $this->statement = $statement;
        $this->arguments = $arguments;
        try {
            $sth = $this->prepare($statement, $arguments);
            $sth = $this->execute($sth, $arguments);
            $this->add_debug_info($statement, $arguments, $sth, true);
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->add_debug_info($statement, $arguments, $sth, false, $e->getMessage());
            // throw $e;
        }
    }

    public function sql_prepare_select($statement, $arguments = array()) {
        $this->statement = $statement;
        $this->arguments = $arguments;
        try {
            $sth = $this->prepare($statement, $arguments);
            $sth = $this->execute($sth, $arguments);
            $this->add_debug_info($statement, $arguments, $sth, true);
            return $sth;
        } catch (Exception $e) {
            $this->add_debug_info($statement, $arguments, $sth, false, $e->getMessage());
            // throw $e;
        }
    }

    public function sql_fetch_row($sth) {
        try {
            return $sth->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $ex) {
            
        }
    }

    public function sql_query($statement, $arguments = array()) {
        $this->statement = $statement;
        $this->arguments = $arguments;
        try {
            $sth = $this->prepare($statement, $arguments);
            $sth = $this->execute($sth, $arguments);
            $this->add_debug_info($statement, $arguments, $sth, true);
        } catch (Exception $e) {
            $this->add_debug_info($statement, $arguments, $sth, false, $e->getMessage());
            // throw $e;
        }
    }

    public function sql_insert_id($tablename, $field_name = '') {
        switch (config::db_engine()) {
            case "mysql":
                $query = $this->sql_select("SELECT LAST_INSERT_ID()");
                $id = $query[0]['LAST_INSERT_ID()'];
                break;

            case "postgresql":
            case "mssql":
                $query = $this->sql_select("SELECT @@IDENTITY AS insert_id");
                $id = $query[0]['insert_id'];
                break;
        }
        return ($id);
    }

    public function log_error($_query, $_data, $_message) {
        $error_str = "There was an error with this sql statement:<br />
            <br />
            " . $_query . "<br />
            <br />
            " . print_r($_data, true) . "<br />
            <br />
            Page http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "<br />
            <br />
            " . $_message . "<br />
            <br />";
        $this->query_list[] = $error_str;
    }

    public function data_to_where($data) {
        $return = array('', array());
        foreach ($data AS $key => $value) {
            if (!isset($value['operator'])) {
                $value['operator'] = '=';
            }
            $return[0] .= $value['field'] . ' ' . $value['operator'] . ' :value' . $key;
            if (isset($value['logic']) && sizeof($data) - 1 > $key) {
                $return[0] .= ' ' . $value['logic'] . ' ';
            }
            $return[1][':value' . $key] = array('value' => $value['value'], 'type' => $value['type']);
        }
        $return[0] .= ' ';
        return $return;
    }

}

/* this class if for backwards compatability - don't write new code using it! */

class sql_db extends db_connector {

    private $query;
    private $prefix;
    private $value_array;
    private $value_array_id;

    public function __construct() {
        $this->reset_value_array();
        if (defined('DB_PREFIX')) {
            $this->prefix = DB_PREFIX;
        } else {
            $this->prefix = '';
        }
        parent::__construct();
    }

    public function sql_insert($tablename, $data, $prefix = TRUE) {
        $eol = "\n";
        $return[0] = TRUE;
        foreach ($data AS $_data) {
            $query = '';
            $field_list = '(';
            $value_list = '(';
            foreach ($_data AS $field => $value) {
                $field_list .= $this->escape_field($field) . ',';
                $value_list .= ' :value' . $this->value_array_id . ' ,';
                $this->value_array['value' . $this->value_array_id] = $value;
                $this->value_array_id++;
            }
            $field_list = substr($field_list, 0, -1) . ')';
            $value_list = substr($value_list, 0, -1) . ')';
            $query = "INSERT INTO " . $this->escape_table_name($tablename, $prefix) . " " . $field_list . " VALUES " . $value_list . ";" . $eol;
            if ($this->sql_escaped_query($query) === FALSE) {
                $return[0] = FALSE;
            } else {
                if ($return[0] != FALSE) {
                    $return[0] = TRUE;
                }
            }
        }
        $return[1] = $query;
        return ($return);
    }

    public function sql_old_select($tablename, $fields, $where = array(), $joins = array(), $order = array(), $limits = array(), $group = array(), $prefix = TRUE, $exec = TRUE) {
        $field_str = '';
        $join_str = '';
        $limit_str = '';
        $group_str = '';
        $order_str = '';

        switch (config::db_engine()) {
            case "mysql":
                break;
            case "mssql":
                break;
            case "postgresql":
                break;
        }

        foreach ($fields AS $sql_function => $_fields) {
            $_fields = $this->escape_field($_fields, $sql_function);
            $field_str .= $_fields . ", ";
        }

        $field_str = substr($field_str, 0, -2);

        $where_str = $this->build_where($where, TRUE, $prefix, $exec);

        if (is_array($tablename)) {
            $sub_data = array('table' => '', 'fields' => '', 'where' => array(), 'joins' => array(), 'order' => array(), 'limits' => array(), 'group' => array());
            foreach ($tablename AS $key => $_data) {
                $sub_data[$key] = $_data;
            }
            $tablename = '(' . $this->sql_select($sub_data['table'], $sub_data['fields'], $sub_data['where'], $sub_data['joins'], $sub_data['order'], $sub_data['limits'], $sub_data['group'], $prefix, FALSE) . ') AS query' . $this->queries;
        } else {
            $tablename = $this->escape_table_name($tablename, $prefix);
        }

        if (sizeof($joins) > 0) {
            foreach ($joins AS $_joins) {
                $join_str .= ' ' . $_joins['type'] . ' JOIN ' . $this->escape_table_name($_joins['table'], $prefix) . ' ON ' . $this->build_join_on($_joins['on']);
            }
        }

        if (sizeof($group) > 0) {
            $group_str .= ' GROUP BY ';
            foreach ($group AS $_group) {
                $group_str .= $this->escape_field($_group) . ',';
            }
            $group_str = substr($group_str, 0, -1);
        }

        if (sizeof($order) > 0) {
            $order_str = $this->make_order($order);
        }

        if (sizeof($limits) > 0) {
            switch (config::db_engine()) {
                case "mysql":
                case "postgresql":
                    $query = "SELECT " . $field_str . " FROM " . $tablename . $join_str . $where_str . $group_str . $order_str;
                    $query .= $limit_str = ' LIMIT ' . $limits[0] . ' OFFSET ' . $limits[1];
                    break;

                case "mssql":
                    // does nothing? $top_query = str_replace('SELECT ', 'SELECT TOP '. $limits[0] .' ', $query);
                    $offset = $limits[1] + 1;
                    $_offset = $limits[0] + $limits[1];
                    $offset_string = array_keys($order);
                    $offset_order = array_values($order);
                    if ($offset_string[0] == 'RAND' && $offset_order[0] === true) {
                        $offset_select = '';
                        $for_order = $this->make_order(array($offset_string[0] => TRUE));
                        $rev_order = $this->make_order(array($offset_string[0] => TRUE));
                    } else {
                        switch ($offset_order[0]) {
                            case "ASC":
                                $offset_order_rev = 'DESC';
                                break;
                            case "DESC":
                                $offset_order_rev = 'ASC';
                                break;
                        }
                        $for_order = $this->make_order(array('offset_str' => $offset_order[0]));
                        $rev_order = $this->make_order(array('offset_str' => $offset_order_rev));
                        $offset_select = ', ' . $this->escape_field($offset_string[0]) . ' AS [offset_str]';
                    }
                    $query = "SELECT * FROM
                            (SELECT TOP " . $_offset . " * FROM(
                                    SELECT TOP " . $_offset . ' ' . $field_str . $offset_select . " FROM " . $tablename . $join_str . $where_str . $group_str . $order_str . "
                            ) AS limit " . $rev_order . ")
                    AS offset " . $for_order;
                    break;
            }
        } else {
            $query = "SELECT " . $field_str . " FROM " . $tablename . $join_str . $where_str . $group_str . $order_str;
        }

        if ($exec === true) {
            $time_start = microtime(true);
            $_return = $this->sql_fetchresult($query);
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            if ($time > 0.09) {
                $time = '<span style="color:red">' . $time . '</span>';
            }
            if ($_return === FALSE) {
                if ($this->debug === true) {
                    $this->query_list[] = '<br /><span style="color:red">' . html::clean_text($query) . '</span> - ' . $time . '<br /><br />';
                }
                $_return = array();
            } else {
                if ($this->debug === true) {
                    $this->query_list[] = '<br /><span style="color:green">' . html::clean_text($query) . '</span> - ' . $time . '<br /><br />';
                }
            }
            if ($this->debug === true) {
                $this->query_list[] = '<br /><span style="color:blue">' . print_r($this->value_array, true) . '</span><br /><br />';
            }
        } else {
            $_return = $query;
        }
        $this->reset_value_array();
        return ($_return);
    }

    public function sql_update($tablename, $data, $where = array(), $prefix = TRUE) {
        $update_str = '';
        switch (config::db_engine()) {
            case "mysql":
                break;
            case "mssql":
                break;
            case "postgresql":
                break;
        }
        foreach ($data AS $field => $value) {
            $field = $this->escape_field($field);
            //array adds support for field = field + 1
            if (is_array($value)) {
                $_value = $this->escape_field($value['field']) . ' ' . $value['operator'] . ' :value' . $this->value_array_id;
                $this->value_array['value' . $this->value_array_id] = $value['value'];
            } else {
                $_value = ':value' . $this->value_array_id;
                $this->value_array['value' . $this->value_array_id] = $value;
            }
            $this->value_array_id++;
            $update_str .= $field . " = " . $_value . ",";
        }
        $update_str = substr($update_str, 0, -1);
        $where_str = $this->build_where($where, TRUE, $prefix);
        $query = "UPDATE " . $this->escape_table_name($tablename, $prefix) . " SET " . $update_str . $where_str;
        return ($this->sql_escaped_query($query));
    }

    public function sql_delete($tablename, $where = array(), $prefix = TRUE) {
        switch (config::db_engine()) {
            case "mysql":
                break;
            case "mssql":
                break;
            case "postgresql":
                break;
        }
        $where_str = $this->build_where($where, TRUE, $prefix);
        $query = "DELETE FROM " . $this->escape_table_name($tablename, $prefix) . " " . $where_str;
        return ($this->sql_escaped_query($query));
    }

    private function sql_escaped_query($_query) {
        $time_start = microtime(true);
        $this->query = false;
        if ($this->statement = $this->sql_db->prepare($_query)) {
            $this->prepare_value_array();
            $this->query = $this->statement->execute($this->value_array);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if ($time > 0.09) {
            $time = '<span style="color:red">' . $time . '</span>';
        }
        if ($this->query === FALSE) {
            $this->log_error($_query, print_r($this->value_array, true));
            if ($this->debug === true) {
                $this->query_list[] = '<br /><span style="color:red">' . html::clean_text($_query) . '</span> - ' . $time . '<br /><br />';
            }
        } else {
            if ($this->no_queries === false) {
                $this->queries++;
            }
            if ($this->debug === true) {
                $this->query_list[] = '<br /><span style="color:green">' . html::clean_text($_query) . '</span> - ' . $time . '<br /><br />';
            }
        }
        if ($this->debug === true) {
            $this->query_list[] = '<br /><span style="color:blue">' . print_r($this->value_array, true) . '</span><br /><br />';
        }
        $this->reset_value_array();
        unset($time_start);
        unset($time_end);
        unset($time);
        return $this->query;
    }

    public function sql_fetchresult($_query) {
        $this->prepare_value_array();
        if ($query = $this->sql_db->prepare($_query)) {
            if ($query->execute($this->value_array)) {
                if ($this->no_queries === false) {
                    $this->queries++;
                }
                $query->setFetchMode(PDO::FETCH_ASSOC);
                $result = $query->fetchAll();
            } else {
                $error = $query->errorInfo();
                print_r($error);
                $result = FALSE;
            }
        } else {
            $result = false;
        }
        unset($query);
        return ($result);
    }

    public function insert_into_table($tablename, $data) {
        $_table = $this->prefix . $tablename;
        $return = array();
        if ($this->created_tables[$_table] === true) {
            $_return = $this->sql_insert($tablename, $data);
            if ($_return[0] === TRUE) {
                echo '<span style="color:green;">`' . $tablename . '` inserts complete</span><br />' . nl2br($_return[1]);
                $this->created_tables[$tablename] = TRUE;
                echo '<br />';
            } else {
                $error = $this->last_error();
                echo '<span style="color:red;">`' . $tablename . '` insert error</span><br />' . nl2br($_return[1]) . ' - <span style="color:red">' . $error . '</span><br />';
                $return[] = $_return;
            }
        }
        return ($return);
    }

    public function create_sql_index($tablename, $index) {
        static $key_id = 1;

        $tablename = $this->prefix . $tablename;
        $this->created_tables[$tablename] = FALSE;
        $keys = array();
        $eol = "\n";

        //build up create statement
        foreach ($index AS $_index) {
            switch ($_index['type']) {
                case "foreign":
                    $_keys = 'ALTER TABLE ' . $this->escape_table_name($tablename, FALSE) . ' ADD FOREIGN KEY (';
                    foreach ($_index['fields'] AS $_fields) {
                        $_keys .= $this->escape_field($_fields) . ',';
                    }
                    $_keys = substr($_keys, 0, -1);
                    $_keys .=') REFERENCES ' . $this->escape_table_name($data['is_fk']['table'], FALSE) . '(' . $this->escape_field($data['is_fk']['field']) . '),' . $eol;
                    $keys[] = $_keys;
                    $key_id++;
                    break;
                case "unique":
                    $_keys = 'CREATE UNIQUE INDEX uidx_' . $key_id . ' ON ' . $this->escape_table_name($tablename, FALSE) . ' (';
                    foreach ($_index['fields'] AS $_fields) {
                        $_keys .= $this->escape_field($_fields) . ',';
                    }
                    $_keys = substr($_keys, 0, -1);
                    $_keys .= ')' . $eol;
                    $keys[] = $_keys;
                    $key_id++;
                    break;
                case "index":
                    $_keys = 'CREATE INDEX idx_' . $key_id . ' ON ' . $this->escape_table_name($tablename, FALSE) . ' (';
                    foreach ($_index['fields'] AS $_fields) {
                        $_keys .= $this->escape_field($_fields) . ',';
                    }
                    $_keys = substr($_keys, 0, -1);
                    $_keys .= ')' . $eol;
                    $keys[] = $_keys;
                    $key_id++;
                    break;
            }
        }

        foreach ($keys AS $_keys) {
            if ($this->sql_escaped_query($_keys) === FALSE) {
                $error = $this->last_error();
                echo '<span style="color:red;">`' . $tablename . '` indexes error</span><br />' . str_replace(",", ",<br />", $_keys) . ' - <span style="color:red">' . $error . '</span><br />';
            } else {
                echo '<span style="color:green;">`' . $tablename . '` indexes added</span><br />' . nl2br($_keys);
                echo '<br />';
            }
        }
    }

    public function drop_sql_table($tablename) {
        $tablename = $this->prefix . $tablename;
        switch (config::db_engine()) {
            default:
                $drop = 'DROP table ' . $tablename;
        }

        if ($this->sql_escaped_query($drop) === FALSE) {
            $error = $this->last_error();
            echo '<span style="color:red;">`' . $tablename . '` table error</span><br />' . str_replace(",", ",<br />", $drop) . ' - <span style="color:red">' . $error . '</span><br />';
        } else {
            echo '<span style="color:green;">`' . $tablename . '` table dropped</span><br />' . nl2br($drop);
            echo '<br />';
        }
    }

    public function create_sql_table($tablename, $structure) {
        $tablename = $this->prefix . $tablename;
        $this->created_tables[$tablename] = FALSE;
        $keys = array();
        $index = array();
        $key_id = 2;
        $eol = "\n";

        //build up create statement
        switch (config::db_engine()) {
            case "mssql":
                $create = 'CREATE TABLE [' . $tablename . '] (' . $eol;
                foreach ($structure AS $name => $data) {
                    $size = '';
                    $default = TRUE;
                    switch ($data['data_type']) {
                        case "text":
                        case "blob":
                            $default = FALSE;
                            break;
                        case "varchar":
                            if (!isset($data['default'])) {
                                $data['default'] = '';
                            }
                            $size = ' (' . $data['size'] . ')';
                            break;
                        case "decimal":
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = ' (' . $data['size'] . ')';
                            break;
                        case "mediumint":
                            $data['data_type'] = 'int';
                        case "int":
                        case "tinyint":
                        case "smallint":
                        case "float":
                        case "bigint":
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = '';
                            break;
                    }
                    if (array_key_exists('auto_inc', $data) && ($data['auto_inc'] === true)) {
                        $create .= '[' . $name . '] [' . $data['data_type'] . '] IDENTITY (1,1),' . $eol;
                    } else {
                        $create .= '[' . $name . '] [' . $data['data_type'] . ']' . $size;
                        if ($default === true) {
                            $create .= ' NOT NULL DEFAULT \'' . $data['default'] . '\'';
                        }
                        $create .= ',' . $eol;
                    }
                    if (array_key_exists('is_primary', $data) && ($data['is_primary'] === true)) {
                        $keys[] = 'PRIMARY KEY (' . $this->escape_field($name) . '),' . $eol;
                    }
                }
                foreach ($keys AS $_keys) {
                    $create .= $_keys;
                }
                $create = substr($create, 0, -2) . $eol . ')' . $eol;
                break;
            case "mysql":
                $key = '';
                $create = 'CREATE TABLE `' . $tablename . '` (' . $eol;
                foreach ($structure AS $name => $data) {
                    $default = TRUE;
                    $size = '';
                    switch ($data['data_type']) {
                        case "text":
                        case "blob":
                            $default = FALSE;
                            break;
                        case "decimal":
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = ' (' . $data['size'] . ')';
                            break;
                        case "varchar":
                            if (!isset($data['default'])) {
                                $data['default'] = '';
                            }
                            $size = ' (' . $data['size'] . ')';
                            break;
                        case "mediumint":
                            $data['data_type'] = 'int';
                        case "int":
                        case "tinyint":
                        case "smallint":
                        case "float":
                        case "bigint":
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = '';
                            break;
                    }
                    if ($data['auto_inc'] === true) {
                        $create .= '`' . $name . '` ' . $data['data_type'] . ' NOT NULL auto_increment,' . $eol;
                    } else {
                        $create .= '`' . $name . '` ' . $data['data_type'] . '' . $size;
                        if ($default === true) {
                            $create .= ' NOT NULL DEFAULT \'' . $data['default'] . '\'';
                        }
                        $create .= ',' . $eol;
                    }
                    if ($data['is_primary'] === true) {
                        $keys[] = 'PRIMARY KEY(' . $this->escape_field($name) . '),' . $eol;
                    }
                }
                foreach ($keys AS $_keys) {
                    $create .= $_keys;
                }
                $create = substr($create, 0, -2) . $eol . ') ENGINE=InnoDB  DEFAULT CHARSET=utf8' . $eol;
                break;
            case "postgresql":
                $create = 'CREATE TABLE "' . $tablename . '" (' . $eol;
                foreach ($structure AS $name => $data) {
                    $size = '';
                    $default = TRUE;
                    switch ($data['data_type']) {
                        case "longtext":
                            $data['data_type'] = 'text';
                        case "text":
                        case "blob":
                            $default = FALSE;
                            break;
                        case "varchar":
                            $size = ' (' . $data['size'] . ')';
                            if (!isset($data['default'])) {
                                $data['default'] = '';
                            }
                            break;
                        case "decimal":
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = ' (' . $data['size'] . ')';
                            break;
                        case "mediumint":
                        case "int":
                            $data['data_type'] = 'integer';
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = '';
                            break;

                        case "tinyint":
                            $data['data_type'] = 'smallint';
                        case "smallint":
                        case "float":
                        case "bigint":
                            if (!isset($data['default']) || $data['default'] == '') {
                                $data['default'] = '0';
                            }
                            $size = '';
                            break;
                    }
                    if ($data['auto_inc'] === true) {
                        $create .= '"' . $name . '" SERIAL,' . $eol;
                    } else {
                        $create .= '"' . $name . '" ' . $data['data_type'] . '' . $size;
                        if ($default === true) {
                            $create .= ' NOT NULL DEFAULT \'' . $data['default'] . '\'';
                        }
                        $create .= ',' . $eol;
                    }
                    if ($data['is_primary'] === true) {
                        $keys[] = 'PRIMARY KEY(' . $this->escape_field($name) . '),' . $eol;
                    }
                }
                foreach ($keys AS $_keys) {
                    $create .= $_keys;
                }
                $create = substr($create, 0, -2) . $eol . ')' . $eol;
                break;
        }
        if ($this->sql_escaped_query($create) === FALSE) {
            $error = $this->last_error();
            echo '<span style="color:red;">`' . $tablename . '` table error</span><br />' . str_replace(",", ",<br />", $create) . ' - <span style="color:red">' . $error . '</span><br />';
        } else {
            echo '<span style="color:green;">`' . $tablename . '` table created</span><br />' . nl2br($create);
            $this->created_tables[$tablename] = TRUE;
            echo '<br />';
        }
    }

    private function build_where($where, $first, $prefix = '', $exec = TRUE) {
        switch (config::db_engine()) {
            case "mysql":
                break;
            case "mssql":
                break;
            case "postgresql":
                break;
        }
        if (is_array($where) && sizeof($where) > 0) {
            if ($first === true) {
                $where_str = ' WHERE ';
            } else {
                $where_str = ' (';
            }
            foreach ($where AS $data) {
                if (is_array($data)) {
                    if (isset($data['data'])) {
                        $where_str .= $this->build_where($data, FALSE, $prefix);
                    } else {
                        if (!isset($data['operator'])) {
                            $data['operator'] = '=';
                        }
                        if (isset($data['field'])) {
                            if (is_array($data['value'])) {
                                $sub_data = array('table' => '', 'fields' => '', 'where' => array(), 'joins' => array(), 'order' => array(), 'limits' => array(), 'group' => array());
                                foreach ($data['value'] AS $key => $_data) {
                                    $sub_data[$key] = $_data;
                                }
                                $data['value'] = '';
                                $data['value'] .= '(' . $this->sql_select($sub_data['table'], $sub_data['fields'], $sub_data['where'], $sub_data['joins'], $sub_data['order'], $sub_data['limits'], $sub_data['group'], $prefix, FALSE) . ')';
                            } else {
                                $this->value_array['value' . $this->value_array_id] = $data['value'];
                                $data['value'] = ':value' . $this->value_array_id;
                                $this->value_array_id++;
                            }
                            switch ($data['operator']) {
                                case "FULLTEXT":
                                    switch (config::db_engine()) {
                                        case "postgresql":
                                            break;

                                        case "mssql":
                                            break;

                                        case "mysql":
                                            $where_str .= " MATCH(" . $this->escape_field($data['field']) . ") AGAINST (" . $data['value'] . ")";
                                            break;
                                    }
                                    break;

                                case "IS NULL":
                                    $where_str .= " " . $this->escape_field($data['field']) . " IS NULL";
                                    break;

                                case "IS NOT NULL":
                                    $where_str .= " " . $this->escape_field($data['field']) . " IS NOT NULL";
                                    break;

                                case "LIKE":
                                    $where_str .= " " . $this->escape_field($data['field']) . " LIKE (" . $data['value'] . ")";
                                    break;

                                default:
                                    $where_str .= " " . $this->escape_field($data['field']) . " " . $data['operator'] . " " . $data['value'];
                                    break;
                            }
                        }
                        if (isset($data['logic'])) {
                            $where_str .= ' ' . $data['logic'];
                        }
                    }
                }
            }
            if ($first === true) {
                
            } else {
                $where_str .= ')';
            }
            if (isset($where['logic'])) {
                $where_str .= ' ' . $where['logic'];
            }
        } else {
            $where_str = '';
        }
        return ($where_str);
    }

    private function escape_table_name($tablename, $prefix) {
        if ($prefix === true) {
            $_prefix = $this->prefix;
        } else {
            $_prefix = '';
        }

        $_table = explode(" ", $tablename);
        if (sizeof($_table) == 3) {
            switch (config::db_engine()) {
                case "mysql":
                    $_table[0] = '`' . $_prefix . $_table[0] . '`';
                    $_table[2] = '`' . $_table[2] . '`';
                    break;

                case "mssql":
                    $_table[0] = '[' . $_prefix . $_table[0] . ']';
                    $_table[2] = '[' . $_table[2] . ']';
                    break;

                case "postgresql":
                    $_table[0] = '"' . $_prefix . $_table[0] . '"';
                    $_table[2] = '"' . $_table[2] . '"';
                    break;
            }
            $tablename = implode(" ", $_table);
        } else {
            $_table = explode(".", $tablename);
            if (sizeof($_table) == 2) {
                switch (config::db_engine()) {
                    case "mysql":
                        $_table[0] = '`' . $_prefix . $_table[0] . '`';
                        $_table[1] = '`' . $_table[1] . '`';
                        break;

                    case "mssql":
                        $_table[0] = '[' . $_prefix . $_table[0] . ']';
                        $_table[1] = '[' . $_table[1] . ']';
                        break;

                    case "postgresql":
                        $_table[0] = '"' . $_prefix . $_table[0] . '"';
                        $_table[1] = '"' . $_table[1] . '"';
                        break;
                }
                $tablename = implode(".", $_table);
            } else {
                switch (config::db_engine()) {
                    case "mysql":
                        $tablename = '`' . $_prefix . $tablename . '`';
                        break;

                    case "mssql":
                        $tablename = '[' . $_prefix . $tablename . ']';
                        break;

                    case "postgresql":
                        $tablename = '"' . $_prefix . $tablename . '"';
                        break;
                }
            }
        }
        return ($tablename);
    }

    private function escape_field($field, $sql_function = '') {
        if ($sql_function != '') {
            switch ($field) {
                case "COUNT":
                case "SUM":
                case "MIN":
                case "MAX":
                case "AVG":
                    $_sql_function = $field;
                    $field = $sql_function;
                    $sql_function = $_sql_function;
                    $_sql_function = '';
                    $func_prefix = $sql_function . '(';
                    $func_suffix = ')';
                    break;
                default:
                    $func_prefix = '';
                    $func_suffix = '';
                    break;
            }
        } else {
            $func_prefix = '';
            $func_suffix = '';
        }
        $_field_exp = explode(" ", $field);
        if (sizeof($_field_exp) == 3) {
            $_field_exp[0] = $func_prefix . $this->escape_field($_field_exp[0]) . $func_suffix;
            $_field_exp[2] = $this->escape_field($_field_exp[2]);
            $field = implode(" ", $_field_exp);
        } else {
            $_field_exp = explode("/", $field);
            if (count($_field_exp) == 2) {
                $field = $this->escape_field($_field_exp[0]) . '/' . $this->escape_field($_field_exp[1]);
            } else {
                $_field_exp = explode(".", $field);
                if (sizeof($_field_exp) == 2) {
                    foreach ($_field_exp AS $key => $data) {
                        switch (config::db_engine()) {
                            case "mysql":
                                $data = str_replace('\\', '\\\\', $data);
                                $_field_exp[$key] = '`' . $data . '`';
                                break;

                            case "postgresql":
                                $_field_exp[$key] = '"' . $data . '"';
                                break;

                            case "mssql":
                                $_field_exp[$key] = '[' . $data . ']';
                                break;
                        }
                    }
                    $field = $func_prefix . implode(".", $_field_exp) . $func_suffix;
                } else {
                    switch (config::db_engine()) {
                        case "mysql":
                            $field = '`' . $field . '`';
                            break;

                        case "postgresql":
                            $field = '"' . $field . '"';
                            break;

                        case "mssql":
                            $field = '[' . $field . ']';
                            break;
                    }
                    $field = $func_prefix . $field . $func_suffix;
                }
            }
        }
        return($field);
    }

    public function describe($tablename) {
        $result = $this->sql_select('INFORMATION_SCHEMA.COLUMNS', array('COLUMN_NAME', 'DATA_TYPE', 'COLUMN_DEFAULT', 'CHARACTER_MAXIMUM_LENGTH'), array(
                    array('field' => 'table_name', 'value' => $tablename, 'operator' => '=')
                        )
        );
        $data = array();
        foreach ($result AS $value) {
            //'event_id' => array('data_type' => 'mediumint', 'size' => '', 'default' => '0', 'is_primary' => TRUE),
            if ($value['CHARACTER_MAXIMUM_LENGTH'] === null) {
                $value['CHARACTER_MAXIMUM_LENGTH'] = '';
            }
            switch (config::db_engine()) {
                case "mysql":
                    break;

                case "mssql":
                    break;

                case "postgresql":
                    break;
            }
            $data[$value['COLUMN_NAME']] = array(
                'data_type' => $value['DATA_TYPE'],
                'size' => $value['CHARACTER_MAXIMUM_LENGTH'],
                'default' => ereg_replace('[^0-9]+', '', $value['COLUMN_DEFAULT']),
                'is_primary' => FALSE);
        }
        return ($data);
    }

    private function last_error() {
        $error = $this->statement->errorInfo();
        if (isset($error[2])) {
            $return = $error[2];
        } else {
            $return = FALSE;
        }
        return ($return);
    }

    private function build_join_on($on) {
        if (is_array($on['field1'])) {
            $this->value_array['value' . $this->value_array_id] = $on['field1'][0];
            $field1 = ':value' . $this->value_array_id;
            $this->value_array_id++;
        } else {
            $field1 = $this->escape_field($on['field1']);
        }
        if (is_array($on['field2'])) {
            $this->value_array['value' . $this->value_array_id] = $on['field2'][0];
            $field2 = ':value' . $this->value_array_id;
            $this->value_array_id++;
        } else {
            $field2 = $this->escape_field($on['field2']);
        }
        $return = $field1 . ' ' . $on['operator'] . ' ' . $field2 . ' ';
        if (isset($on['logic'])) {
            $return .= $on['logic'] . ' ' . $this->build_join_on($on['on']);
        }
        return ($return);
    }

    private function make_order($order, $reverse = FALSE) {
        $order_str = ' ORDER BY ';
        foreach ($order AS $field => $_order) {
            if ($field == 'RAND' && $_order === true) {
                switch (config::db_engine()) {
                    case "mysql":
                    case "mssql":
                        $order_str .= ' RAND(),';
                        break;

                    case "postgresql":
                        $order_str .= ' RANDOM(),';
                        break;
                }
            } else {
                $order_str .= $this->escape_field($field);
                if (isset($_order)) {
                    if ($reverse === true) {
                        switch ($_order) {
                            case "DESC":
                                $_order = 'ASC';
                                break;
                            case "ASC":
                                $_order = 'DESC';
                                break;
                        }
                    }
                    $order_str .= ' ' . $_order . ',';
                } else {
                    if ($reverse === true) {
                        $order_str .= ' ASC,';
                    } else {
                        $order_str .= ' DESC,';
                    }
                }
            }
        }
        $order_str = substr($order_str, 0, -1);
        return($order_str);
    }

    private function prepare_value_array() {
        foreach ($this->value_array AS $k => $v) {
            if ($v === false) {
                $this->value_array[$k] = '';
            }
        }
    }

    public function sql_truncate($tablename, $prefix = TRUE) {
        $query = "TRUNCATE TABLE " . $this->escape_table_name($tablename, $prefix);
        if ($this->sql_escaped_query($query) === FALSE) {
            $return[0] = FALSE;
        } else {
            if ($return[0] != FALSE) {
                $return[0] = TRUE;
            }
        }
        $return[1] = $query;
        return ($return);
    }

    private function reset_value_array() {
        unset($this->value_array);
        unset($this->value_array_id);
        $this->value_array = array();
        $this->value_array_id = 1;
    }

}

?>