<?php
class bbcode {

    static $bbcode_array = array(
        array(
            'name_tag' => '/\[b\](.*?)\[\/b\]/is',
            'replace_tag' => '<span class="bold">\\1</span>',
            'inline' => true
            ),
        array(
            'name_tag' => '/\[u\](.*?)\[\/u\]/is',
            'replace_tag' => '<span class="underline">\\1</span>',
            'inline' => true
            ),
        array(
            'name_tag' => '/\[i\](.*?)\[\/i\]/is',
            'replace_tag' => '<span class="italic">\\1</span>',
            'inline' => true
            ),
        array(
            'name_tag' => '/\[bp\](.*?)\[\/bp\]/is',
            'replace_tag' => '<ul>\\1</ul>',
            'inline' => false
            ),
        array(
            'name_tag' => '/\[dot\](.*?)\[\/dot\]/is',
            'replace_tag' => '<li>\\1</li>',
            'inline' => false
            ),
        array(
            'name_tag' => '/\[img\](.*?)\[\/img\]/i',
            'replace_tag' => '<img src="\\1" alt="image" />',
            'inline' => false
            ),
        array(
            'name_tag' => '/\[colour=(.*?)\](.*?)\[\/colour\]/is',
            'replace_tag' => '<span style=\"color: \\1\">\\2</span>',
            'inline' => true
            ),
        array(
            'name_tag' => '/\[quote\](.*?)\[\/quote\]/is',
            'replace_tag' => '<div class="quote auto">\\1</div>',
            'inline' => false
            ),
        array(
            'name_tag' => '/\[quote=&quot;(.*?)&quot;\](.*?)\[\/quote\]/is',
            'replace_tag' => '<div class="quote auto"><span class="bold">\\1 said:</span><br /><br />\\2</div>',
            'inline' => false
            ),
        array(
            'name_tag' => '/\[size=([\d]*?)\](.*?)\[\/size\]/is',
            'replace_tag' => '<span style="font-size:\\1pt;">\\2</span>',
            'inline' => false
            ),
        array(
            'name_tag' => '/\[center\](.*?)\[\/center\]/is',
            'replace_tag' => '<div class="center">\\1</div>',
            'inline' => false
            )
    );

    static $smiley_array = array(
            
    );
}
?>
