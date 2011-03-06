<?php

/*
  Table html generator

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

class table {

    private $header;
    private $data;
    private $cols;
    private $align;
    private $max_cols;
    public $style;
    public $position;
    private $id;
    private $class;

    function __construct($id = '', $class = '') {
        $this->header = array();
        $this->data = array();
        $this->cols = array();
        $this->style = TRUE;
        $this->id = $id;
        $this->class = $class;
    }

    function add_aligns($aligns) {
        $this->align = array();
        if (is_array($aligns)) {
            foreach ($aligns AS $_al) {
                $this->align[] = $_al;
            }
        } else {
            $this->align = $aligns;
        }
    }

    function add_dims($dims) {
        if (is_array($dims)) {
            foreach ($dims AS $_dims) {
                $this->cols[] = $_dims;
            }
        } else {
            $this->cols = $dims;
        }
    }

    function add_header($data) {
        if (is_array($data)) {
            foreach ($data AS $_data) {
                $this->header[] = $_data;
            }
        } else {
            $this->header[] = $data;
        }
        $this->max_cols = sizeof($this->header);
    }

    function add_data($data) {
        $this->data[] = $data;
        $max = sizeof($data);
        if ($this->max_cols < $max) {
            $this->max_cols = $max;
        }
    }

    function clear_data() {
        $this->header = array();
        $this->data = array();
    }

    function v_display($head = 'tb_head') {
        if ($this->style === true) {
            $class = $head . ' tb_cell';
        } else {
            $class = '';
        }
        if (strlen($this->class) > 0) {
            $table_class = ' class="' . $this->class . '"';
        } else {
            $table_class = '';
        }
        if (strlen($this->id) > 0) {
            $id = ' id="' . $this->id . '"';
        } else {
            $id = '';
        }
        $output = '<table' . $table_class . $id . '>
            <thead>
                <tr>
                ';
        if (is_array($this->header)) {
            $n = 0;
            foreach ($this->header AS $key => $_c) {
                if ($_c == '') {
                    $_c = '&#160;';
                }
                $output .= '<th class="' . $class . '">' . $_c . '</th>' . "\n";
                $n++;
            }
            $output .= '</tr></thead>
            ';
        }

        $row_count = 0;
        if (is_array($this->data)) {
            $output .= '<tbody>';
            foreach ($this->data AS $key => $_data) {
                $row_style = ($row_count % 2) ? '2' : '1';
                $row_count++;
                if ($this->style === true) {
                    $class = 'tb_cell tb_row' . $row_style;
                } else {
                    $class = '';
                }
                $output .= '<tr>' . "\n";
                if (is_array($_data)) {
                    for ($n = 0; $n < $this->max_cols; $n++) {
                        if ($_data[$n] == '')
                            $_data[$n] = '&#160;';
                        $output .= '<td class="' . $class . '">' . $_data[$n] . '</td>' . "\n";
                    }
                }
                else {
                    if ($_data == '') {
                        $_data = '&#160;';
                    }
                    $output .= '<td colspan="' . $this->max_cols . '" class="' . $class . '">' . $_data . '</td>' . "\n";
                }
                $output .= '</tr>' . "\n";
            }
        }
        $output .= '</tbody></table>' . "\n";
        $this->clear_data();
        return ($output);
    }

    function h_display($head = 'tb_head') {
        if ($this->style === true) {
            $class = $headclass = $head . ' tb_cell';
        } else {
            $class = $headclass = '';
        }
        if (strlen($this->class) > 0) {
            $table_class = ' class="' . $this->class . '"';
        } else {
            $table_class = '';
        }
        if (strlen($this->id) > 0) {
            $id = ' id="' . $this->id . '"';
        } else {
            $id = '';
        }
        $output = '<table' . $table_class . $id . '>';
        foreach ($this->header AS $key => $_header) {
            $n = 0;
            $output .= '<tr><td class="' . $headclass . '">' . $_header . '</td>';
            $row_count = 0;
            foreach ($this->data AS $_data) {
                $n++;
                $row_style = ($row_count % 2) ? '1' : '2';
                $row_count++;
                if ($this->style === true) {
                    $class = 'tb_cell tb_row' . $row_style;
                } else {
                    $class = '';
                }
                if ($_data[$key] == '')
                    $_data[$key] = '&#160;';
                $output .= '<td class="' . $class . '">' . $_data[$key] . '</td>';
            }
            $output .= '</tr>';
        }
        $output .= '</table>';
        $this->clear_data();
        return($output);
    }

}

?>
