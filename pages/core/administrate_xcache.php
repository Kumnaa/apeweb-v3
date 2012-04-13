<?php

/*
  Administrate XCache page

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

// for unit testing
if (file_exists(RELATIVE_PATH . 'components/page.php')) {
    require_once(RELATIVE_PATH . 'components/page.php');
} else {
    require_once('components/core/page.php');
}

// end for unit testing

class administrate_xcache_page extends page {

    public function __construct() {
        $this->enable_component(component_types::$tables);
        parent::__construct();
        $this->add_text('title', 'XCache Administration');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            if (page::$user->get_level() >= userlevels::$administrator) {
                if (function_exists("xcache_info")) {
                    $cacheinfos = array();
                    for ($p = 0, $pc = xcache_count(XC_TYPE_PHP); $p < $pc; $p++) {
                        $data = xcache_info(XC_TYPE_PHP, $p);
                        $data['type'] = XC_TYPE_PHP;
                        $data['cache_name'] = "php#$p";
                        $data['cacheid'] = $p;
                        $cacheinfos[] = $data;
                    }
                    for ($v = 0, $vc = xcache_count(XC_TYPE_VAR); $v < $vc; $v++) {
                        $data = xcache_info(XC_TYPE_VAR, $v);
                        $data['type'] = XC_TYPE_VAR;
                        $data['cache_name'] = "var#$v";
                        $data['cacheid'] = $v;
                        $cacheinfos[] = $data;
                    }

                    $table = new table();
                    $table->add_header(array('Cache Name', 'Slot Use', 'Mem Use', 'Type', 'Cache ID', 'Compiling', 'Hits', 'Clogs', 'OOM', 'R/O', 'Deleted', 'GC'));

                    foreach ($cacheinfos as $i => $ci) {
                        if ($ci['hits'] + $ci['misses'] > 0) {
                            $ci['compiling'] = $ci['type'] == XC_TYPE_PHP ? ($ci['compiling'] ? "Yes" : "No") : '-';
                            $ci['can_readonly'] = $ci['can_readonly'] ? "Yes" : "No";

                            $mem_use = $ci['size'] - $ci['avail'];
                            $ci['gc'] = max(0, $ci['gc']);
                            $su_percent = ceil($ci['cached'] / $ci['slots'] * 100);

                            $data = array(
                                $ci['cache_name'],
                                sprintf('<acronym %stitle="%d / %d">%d%%</acronym>', ($su_percent >= 100 ? 'style="color:red" ' : ''), $ci['cached'], $ci['slots'], $su_percent),
                                sprintf('%s/%s <span style="float:right">(%d%%)</span>', $this->size($mem_use), $this->size($ci['size']), ceil($mem_use / $ci['size'] * 100)),
                                $ci['type'],
                                $ci['cacheid'],
                                $ci['compiling'],
                                sprintf('<acronym title="%d / %d">%d%%</acronym>', $ci['hits'], ($ci['hits'] + $ci['misses']), floor(($ci['hits'] / ($ci['hits'] + $ci['misses'])) * 100)),
                                $ci['clogs'],
                                $ci['ooms'],
                                $ci['can_readonly'],
                                $ci['deleted'],
                                sprintf('%s:%s:%s', floor($ci['gc'] / 3600), ($ci['gc'] / 60 < 10 ? '0' : '') . floor($ci['gc'] / 60), ($ci['gc'] % 60 < 10 ? '0' : '') . $ci['gc'] % 60)
                            );

                            $table->add_data($data);
                        }
                    }

                    $this->add_text('main', $table->v_display());
                } else {
                    throw new Exception("XCache is not enabled.");
                }
            } else {
                throw new Exception("Permission denied.");
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    private function size($size, $precision = 2) {
        $suffixes = array(
            'Y' => '1208925819614629174706176',
            'Z' => '1180591620717411303424',
            'E' => '1152921504606846976',
            'P' => '1125899906842624',
            'T' => '1099511627776',
            'G' => '1073741824',
            'M' => '1048576',
            'K' => '1024'
        );
        foreach ($suffixes as $suffix => $divisor) {
            if (bccomp($size, $divisor) > -1) {
                return bcdiv($size, $divisor, $precision) . $suffix;
            }
        }
        return $size . 'b';
    }

}

?>