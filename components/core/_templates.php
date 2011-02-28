<?php

/*
  Template class

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

class template {

    private $templates = array();

    public function get_template($template_name) {
        if (!isset($this->templates[$template_name])) {
            try {
                if (file_exists('html/templates/' . $template_name . '.html')) {
                    $this->templates[$template_name] = file_get_contents('html/templates/' . $template_name . '.html');
                } else if (file_exists('templates/' . $template_name . '.html')) {
                    $this->templates[$template_name] = file_get_contents('templates/' . $template_name . '.html');
                } else {
                    throw new Exception("Template " . $template_name . " not found.");
                }
            } catch (Exception $e) {
                $this->templates[$template_name] = '<html><head></head><body>' . html::clean_text($e->getMessage()) . '</body></html>';
            }
        }

        return ($this->templates[$template_name]);
    }

}

?>
