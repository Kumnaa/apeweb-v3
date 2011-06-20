<?php

/*
  Contact list business logic class

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

class contact_list_bl extends businesslogic_base {

    public function move_up($user_id) {
        $position = $this->get_list_position($user_id);
        $this->db->sql_query('UPDATE
                `users`
            SET
                `list_order` = `list_order` - 1
            WHERE
                `id` = :user_id',
            array(
               ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
           ));
        
        $this->db->sql_query('UPDATE
                `users`
            SET
                `list_order` = `list_order` + 1
            WHERE
                `list_order` = :position
                AND
                `id` != :user_id',
            array(
               ':position' => array('value' => $position - 1, 'type' => PDO::PARAM_INT),
               ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
           ));
    }
    
    public function move_down($user_id) {
        $position = $this->get_list_position($user_id);
        $this->db->sql_query('UPDATE
                `users`
            SET
                `list_order` = `list_order` + 1
            WHERE
                `id` = :user_id',
            array(
               ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
           ));
        $this->db->sql_query('UPDATE
                `users`
            SET
                `list_order` = `list_order` - 1
            WHERE
                `list_order` = :position
                AND
                `id` != :user_id',
            array(
               ':position' => array('value' => $position + 1, 'type' => PDO::PARAM_INT),
               ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
           ));
    }
    
    private function get_list_position($user_id) {
        $list_value = $this->db->sql_select('SELECT
                `list_order`
            FROM
                `users`
            WHERE
                `id` = :user_id',
           array(
               ':user_id' => array('value' => $user_id, 'type' => PDO::PARAM_INT)
           ));
        if (is_array($list_value) && count($list_value) > 0) {
            return $list_value[0]['list_order'];
        } else {
            throw new Exception("User not found.");
        }
    }
    
    public function get_contact_list() {
        return $this->db->sql_select('SELECT
                `username`,
                `mobile_number`,
                `firstname`,
                `lastname`,
                `id`,
                `location`,
                `email`,
                `phone_number`,
                `position`
            FROM
                `users`
            WHERE
                `visible` = 1
                AND
                (
                    `status` = 2
                    OR
                    `status` = 4
                )
            ORDER BY
                lastname ASC, 
                firstname ASC');
    }

}

?>
