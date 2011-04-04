<?php

/*
  Calendar generator

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

class calendar {

    private $starting_date;
    private $month;
    private $year;
    private $first_day_of_month;
    private $last_day_of_month;
    private $array_of_events;

    public function __construct($starting_date, $array_of_events = array()) {
        $this->starting_date = $starting_date;
        $this->month = $starting_date->format("n");
        $this->year = $starting_date->format("Y");
        $this->array_of_events = $array_of_events;
        $this->first_day_of_month = apetech::first_day_of_month($starting_date);
        $this->last_day_of_month = apetech::last_day_of_month($starting_date);
    }

    public function display_xml() {
        $doc = new DomDocument('1.0');
        $root = $doc->createElement('days');
        $root = $doc->appendChild($root);

        $blank = 0;
        switch ($this->first_day_of_month->format("D")) {
            case "Sun": $blank = 0;
                break;
            case "Mon": $blank = 1;
                break;
            case "Tue": $blank = 2;
                break;
            case "Wed": $blank = 3;
                break;
            case "Thu": $blank = 4;
                break;
            case "Fri": $blank = 5;
                break;
            case "Sat": $blank = 6;
                break;
        }

        $current_day_number = 1;
        for ($n = 1; $n <= 42; $n++) {
            $day = $doc->createElement('day');
            if ($n > $blank && $current_day_number <= $this->last_day_of_month->format("j")) {
                $day->setAttribute('id', $n);
                $day->setAttribute('number', $current_day_number);
                $current_day_number += 1;
            } else {
                $day->setAttribute('id', $n);
                $day->setAttribute('number', '-');
            }

            $day = $root->appendChild($day);
        }

        return $doc->saveXML();
    }

    public function display($template, $template_name = 'calendar') {
        $page = new page($template);
        $page->set_template($template_name);
        $blank = 0;
        switch ($this->first_day_of_month->format("D")) {
            case "Sun": $blank = 0;
                break;
            case "Mon": $blank = 1;
                break;
            case "Tue": $blank = 2;
                break;
            case "Wed": $blank = 3;
                break;
            case "Thu": $blank = 4;
                break;
            case "Fri": $blank = 5;
                break;
            case "Sat": $blank = 6;
                break;
        }

        $page->add_text('month', $this->first_day_of_month->format("M"));
        $page->add_text('year', $this->first_day_of_month->format("Y"));

        $current_day_number = 1;
        for ($n = 1; $n <= 42; $n++) {
            if ($n > $blank && $current_day_number <= $this->last_day_of_month->format("j")) {
                $page->add_text('calendar_day_' . $n . '_event', '');
                $page->add_text('calendar_day_' . $n . '_text', $current_day_number);
                $current_day_number += 1;
            } else {
                $page->add_text('calendar_day_' . $n . '_event', '');
                $page->add_text('calendar_day_' . $n . '_text', '-');
            }
        }

        $page->add_text('cal_javascript', '
        	<script type="text/javascript">
        	<!--
        		$(\'#calendar_table\').apetech_calendar();
        	-->
        	</script>
        ');
        return $page->display();
    }

}

?>
