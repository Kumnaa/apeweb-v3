<?php

require('components/core/_user.php');

class user extends _user
{
    protected $read_topics;
    protected $read_private_topics;
    protected $read_group_topics;

    public $unread_forums;
    public $unread_banters;
    public $unread_groups;

    public function  __construct()
    {
        parent::__construct();
    }

    public function generate_read_private_topics() {}

    public function generate_read_group_topics() {}

    public function get_read_topics() {}
    
    public function generate_read_topics() {}

    public function get_latest_topics() {}

    public function update_last_read_post_id($user_id, $topic_id, $post_id) {}
    
    protected function populate_unread_forums($list) {}

    protected function extended_grab_info() {}

    protected function on_login() {}

    protected function on_logout() {}

}
?>
