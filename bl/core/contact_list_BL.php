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

    public function get_contact_list() {
        return $this->db->sql_select('SELECT
                `username`,
                `mobile_number`,
                `firstname`,
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
                list_order ASC');
    }

}

?>
