<?php

/*
  HTML generator

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

class html {

    public static function build_registration_url($parameters = array()) {
        return str_replace('&amp;', '&', html::gen_url('register.php', $parameters));
    }

    public static function build_richtextbox($textarea, $form_name, $catch_submit) {
        $boxbuilder = "
        	jQuery(function() {
	    		jQuery('#" . $textarea . "').wymeditor(
	    			{
	    				containersHtml: '',
	    				classesHtml: '',
						toolsItems: [
						    {'name': 'Bold', 'title': 'Strong', 'css': 'wym_tools_strong'}, 
						    {'name': 'Italic', 'title': 'Emphasis', 'css': 'wym_tools_emphasis'},
						    {'name': 'Superscript', 'title': 'Superscript', 'css': 'wym_tools_superscript'},
						    {'name': 'Subscript', 'title': 'Subscript', 'css': 'wym_tools_subscript'},
						    {'name': 'InsertOrderedList', 'title': 'Ordered_List', 'css': 'wym_tools_ordered_list'},
						    {'name': 'InsertUnorderedList', 'title': 'Unordered_List', 'css': 'wym_tools_unordered_list'},
						    {'name': 'Indent', 'title': 'Indent', 'css': 'wym_tools_indent'},
						    {'name': 'Outdent', 'title': 'Outdent', 'css': 'wym_tools_outdent'},
						    {'name': 'Undo', 'title': 'Undo', 'css': 'wym_tools_undo'},
						    {'name': 'Redo', 'title': 'Redo', 'css': 'wym_tools_redo'},
						    {'name': 'CreateLink', 'title': 'Link', 'css': 'wym_tools_link'},
						    {'name': 'Unlink', 'title': 'Unlink', 'css': 'wym_tools_unlink'},
						    {'name': 'Paste', 'title': 'Paste_From_Word', 'css': 'wym_tools_paste'},
						    {'name': 'ToggleHtml', 'title': 'HTML', 'css': 'wym_tools_html'},
						    {'name': 'Preview', 'title': 'Preview', 'css': 'wym_tools_preview'}
						]
	    			}
	    		);
			});";

        if ($catch_submit == true) {
            $formcatcher = '$(\'#' . $form_name . '\').submit(function() {
		        		try
		        		{
		        			$(\'#' . $textarea . '\').html(jQuery.wymeditors(0).xhtml());
	                		return true;
	        			}
	        			catch (error)
	        			{
	        				alert(error);
	                		return false;
	        			}
		        	});';
        } else {
            $formcatcher = '';
        }

        return '<script language="javascript">
	        	//<!--
	        	' . $boxbuilder . $formcatcher . '
	        	//-->
	        	</script>';
    }

    public static function truncate($string, $length) {
        $done = false;
        while ($done == false && strlen($string) >= $length) {
            $alpha = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
            $opening_elements = 0;
            foreach ($alpha AS $_alpha) {
                $opening_elements += substr_count($string, '<' . $_alpha, 0, $length);
            }
            $ending_elements = substr_count($string, '</', 0, $length) + substr_count($string, '/>', 0, $length);
            if ($opening_elements == $ending_elements && substr_count($string, '<', $length) == substr_count($string, '>', $length)) {
                $new_string = substr($string, 0, $length);
                $done = true;
            }
            $length++;
        }
        return ($new_string);
    }

    //make valid url
    public static function check_url($url) {
        if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://' || substr($url, 0, 7) == 'mailto:' || substr($url, 0, 6) == 'irc://' || substr($url, 0, 7) == 'file://') {
            str_replace(" ", "", $url);
        } else {
            $url = '';
        }
        return ($url);
    }

    public static function url_parsing_ext($matches) {
        if (isset($matches[2])) {
            $return = "<a href=\"" . self::check_url(str_replace('"', '', $matches[1])) . "\" target=\"_blank\">" . $matches[2] . "</a>";
        } else {
            $return = "<a href=\"" . self::check_url(str_replace('"', '', $matches[1])) . "\" target=\"_blank\">" . $matches[1] . "</a>";
        }
        return $return;
    }

    public static function clean_for_static_url($text) {
        $return = '';
        if (strlen($text) > 0) {
            $text = str_replace(' ', '-', $text);
            $text = trim($text, '-');
            $previouschar = '';
            $return = '';
            foreach (str_split($text) AS $char) {
                if ($char == '-' && $char != $previouschar) {
                    $return .= $char;
                } else if ($char != '-') {
                    $return .= $char;
                }

                $previouschar = $char;
            }

            $return = preg_replace("/[^a-zA-Z0-9\s\-]/", "", $return);
        }
        return html::clean_text($return, false, false, false);
    }

    public static function clean_input_text($text) {
        return htmlentities($text);
    }
   
    public static function parse_bbcode($post, $newline, $parse_urls) {
        foreach (bbcode::$bbcode_array AS $array) {
            if (($newline == false && $array['inline'] == true) || $newline == true) {
                $post = preg_replace($array['name_tag'], $array['replace_tag'], $post);
            }
        }

        foreach (bbcode::$smiley_array AS $array) {
            if (($newline == false && $array['inline'] == true) || $newline == true) {
                $post = preg_replace($array['name_tag'], $array['replace_tag'], $post);
            }
        }

        $post = preg_replace_callback("/\[url=(.*?)\](.*?)\[\/url\]/", create_function('$matches', 'return (html::url_parsing_ext($matches));'), $post);
        $post = preg_replace_callback("/\[url\](.*?)\[\/url\]/", create_function('$matches', 'return (html::url_parsing_ext($matches));'), $post);
        if ($parse_urls == true) {
            $post = preg_replace('|([A-Za-z]{3,9})://([-;:&=\+\$,\w]+@{1})?([-A-Za-z0-9\.]+)+:?(\d+)?((/[-\+~%/\.\w]+)?\??([-\+=&;%@\.\w]+)?#?([\w]+)?)?|', '<a href="\0" target="_blank">\1</a>', $post);
        }

        return $post;
    }

    public static function clean_text($post, $newline = false, $bbcode = true, $parse_urls = false) {
        $post = self::utf_safe($post);
        $post = self::remove_htmljava($post);
        if ($bbcode == true) {
            $post = html::parse_bbcode($post, $newline, $parse_urls);
        }

        if ($newline == true) {
            $post = nl2br($post);
        }
        
        // tidy up
        $post = preg_replace('/<(\/*)ul>(<br \/>*)/', '<\\1ul>', $post);
        $post = preg_replace('/<\/li>(<br \/>*)/', '</li>', $post);
        return ($post);
    }

    // html/javascript remover
    public static function remove_htmljava($post) {
        $post = htmlentities($post, ENT_QUOTES, 'UTF-8');
        return ($post);
    }

    // make safe utf-8
    public static function utf_safe($string) {
        /* $clean = '';
          $len = strlen($string);
          for ($n = 1; $n <= $len; $n++)
          {
          $current = substr($string, $n-1, 1);
          //if (preg_match('/[\w\pL]/u', $current) == false && $current != "\n" && $current != "\r")
          if (preg_match('/([\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*\z/', $current) == false)
          {
          $clean .= '?';
          }
          else
          {
          $clean .= $current;
          }
          }
          return ($clean); */
        return ($string);
    }

    public static function gen_link($url, $text = '', $class = null) {
        $return = '<a href="' . $url . '"';
        if ($class != null) {
            $return .= ' class="' . $class . '"';
        }
        $return .= '>';

        if (strlen($text) > 0) {
            $return .= $text;
        } else {
            $return .= $url;
        }
        $return .= '</a>';
        return $return;
    }

    public static function gen_image($url, $class = null) {
        $url = html::clean_text($url, false, false, false);
        $return = '<img src="' . $url . '" alt="' . $url . '"';
        if ($class != null) {
            $return .= ' class="' . $class . '"';
        }
        $return .= ' />';
        return $return;
    }

    public static function gen_url($_file = '', $_args = array(), $_append = false, $_jumpto = '', $encode = true) {
        if (isset($_GET['wap'])) {
            $_args['wap'] = $_GET['wap'];
        }

        if (isset($_GET['sql'])) {
            $_args['sql'] = $_GET['sql'];
        }

        if (isset($_GET['xhtml'])) {
            $_args['xhtml'] = $_GET['xhtml'];
        }

        $url = config::site_url() . $_file;
        if ($encode == true) {
            $and = '&amp;';
        } else {
            $and = '&';
        }

        if ($_append == true) {
            $prefix = $and;
            $query_string = '';
            foreach ($_GET AS $key => $get) {
                if (!isset($_args[$key])) {
                    $_args[$key] = $get;
                }
            }
        }

        if (sizeof($_args) > 0) {
            $prefix = '?';
            $args = html::args_to_str($_args);
            $url .= '?' . $args;
        }

        $url .= $_jumpto;
        return($url);
    }

    public static function args_to_str($_args) {
        $start = true;
        $args = '';
        if (is_array($_args)) {
            foreach ($_args AS $key => $arg) {
                //$arg = urlencode($arg);
                if (defined('STATIC_URLS')) {
                    $args .= $key . '-' . $arg . '/';
                } else {
                    if ($start == true) {
                        $args .= $key . '=' . $arg;
                        $start = false;
                    } else {
                        $args .= '&amp;' . $key . '=' . $arg;
                    }
                }
            }
        }
        return ($args);
    }

    public static function notice($message) {
        return ('<div id="notice">' . $message . '</div>');
    }

    public static function capture_url($capture_arguments = true) {
        if ($capture_arguments == true) {
            $file = apetech::server_uri();
        } else {
            $file = $_SERVER['SCRIPT_NAME'];
        }

        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if (apetech::server_port() != "80") {
            $pageURL .= apetech::server_name() . ":" . apetech::server_port() . $file;
        } else {
            $pageURL .= apetech::server_name() . $file;
        }
        return $pageURL;
    }

}

?>