<?php

/*
  Security type

  @author Ben Bowtell

  @date 27-Feb-2011

  (c) 2011 by http://www.amplifycreative.net/

  contact: ben@apetechnologies.net

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

class security_type {

    private $edit = false;
    private $new = false;
    private $delete = false;
    private $add = false;

    public function AllowEdit($value = null) {
        if ($value === null) {
            return $this->edit;
        } else {
            $this->edit = $value;
        }
    }

    public function AllowNew($value = null) {
        if ($value === null) {
            return $this->new;
        } else {
            $this->new = $value;
        }
    }

    public function AllowAdd($value = null) {
        if ($value === null) {
            return $this->add;
        } else {
            $this->add = $value;
        }
    }

    public function AllowDelete($value = null) {
        if ($value === null) {
            return $this->delete;
        } else {
            $this->delete = $value;
        }
    }

}

?>
