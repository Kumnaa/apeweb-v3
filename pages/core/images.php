<?php

/*
  Images page

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

class images_page extends page {

    private $images_bl;
    protected $image_manager;
    protected $page_id;
    protected $image_id;
    protected $paging;
    protected $tag;
    protected $description;

    public function __construct() {
        $this->enable_component(component_types::$images);
        $this->enable_component(component_types::$paging);
        $this->enable_component(component_types::$tables);
        parent::__construct();
        $this->page_id = input::validate('page_id', 'int');
        $this->image_id = input::validate('image_id', 'string');
        $this->image_manager = new image_manager(forum_config::image_warehouse_filesystem(), forum_config::image_warehouse_filesystem() . 'thumb/', null, null, 75, 75);
        $this->image_manager->set_filename_length(8);
        $this->images_bl = new images_bl();
        $this->add_text('title', 'Images');
        $this->paging = new paging();
        $this->paging->items_per_page = forum_config::$page_limit;
        $this->paging->relative_url = 'images.php';
        $this->paging->page = $this->page_id;
        $this->tag = input::validate('tag', 'message');
        $this->description = input::validate('description', 'message');
    }

    public function generate_display() {
        $this->display();
    }

    protected function action() {
        try {
            switch ($this->action) {
                case "delete":
                    $this->delete();
                    break;
                case "view":
                    $this->view();
                    break;
                case "upload":
                    $this->upload();
                    break;
                default:
                    $this->browse();
                    break;
                case "browseall":
                    $this->browse_all();
                    break;
            }
        } catch (Exception $ex) {
            $this->notice($ex->getMessage());
        }
    }

    protected function delete() {
        $image = $this->images_bl->get_image($this->image_id);
        if (is_array($image) && count($image) > 0) {
            if (page::$user->get_level() >= userlevels::$moderator || page::$user->get_user_id() == $image[0]['owner_id']) {
                if ($this->confirm == "confirm") {
                    $head_exp = explode('/', $image[0]['header']);
                    $extension = array_pop($head_exp);
                    $image_fs = forum_config::image_warehouse_filesystem() . $this->image_id . '.' . $extension;
                    $image_t_fs = forum_config::image_warehouse_filesystem() . 'thumb/' . $this->image_id . '.' . $extension;
                    @unlink($image_fs);
                    @unlink($image_t_fs);
                    $this->images_bl->delete_image($this->image_id);
                    $this->notice("Image deleted.");
                } else {
                    $this->notice('
                        <form action="' . html::capture_url(true) . '" method="post">
                            <div>
                                Delete this image?<br />
                                <input type="hidden" name="confirm" value="confirm" />
                                <input type="submit" value="Yes" />
                            </div>
                        </form>
                    ');
                }
            } else {
                throw new Exception("Access denied.");
            }
        } else {
            throw new Exception("Image not found.");
        }
    }

    protected function view() {
        $image = $this->images_bl->get_image($this->image_id);
        if (is_array($image) && count($image) > 0) {
            $current_tag = $image[0]['tag'];
            $current_description = $image[0]['description'];
            
            if (page::$user->get_level() >= userlevels::$moderator) {
                if (strlen($this->tag) > 0) {
                    $this->images_bl->update_tag($this->image_id, $this->tag);
                    $current_tag = $this->tag;
                }

                if (strlen($this->description) > 0) {
                    $this->images_bl->update_description($this->image_id, $this->description);
                    $current_description = $this->description;
                }
            }
            
            $image_name = html::clean_text($image[0]['image_name']);
            $time = date(forum_config::$date_format, $image[0]['timestamp']);
            $head_exp = explode('/', $image[0]['header']);
            $extension = array_pop($head_exp);
            $image_url = forum_config::image_warehouse_url() . $this->image_id . '.' . $extension;
            $this->add_text('main', '<span class="head_text">' . $image_name . '</span><br/ ><span class="italic">uploaded by ' . html::clean_text($image[0]['username']) . '</span><br /><br />');
            if (page::$user->get_level() > 0) {
                $this->add_text('main', '<form action="'. html::gen_url('images.php', array('action' => 'view', 'image_id' => $this->image_id)) .'" method="post">
                        <div class="div_center dotted_box sixty">
                            <span class="italic">Description:</span><br />
                            <input name="description" size="60" value="'. html::clean_text($current_description) .'" /><br /><br />
                            <span class="italic">Tags:</span><br />
                            <input name="tag" size="60" value="'. html::clean_text($current_tag) .'" /><br /><br />
                            <input type="submit" value="Update" />
                        </div>
                        </form><br />
                        <br />');
            }
            $this->add_text('main', '<a href="' . $image_url . '" target="_blank"><img src="' . $image_url . '" alt="user_image" class="user_image" /></a>');
        } else {
            throw new Exception("Image not found.");
        }
    }

    protected function browse() {
        $your_images = $this->images_bl->get_image_count(page::$user->get_user_id());
        $this->paging->total_items = $your_images;
        $this->paging->url_arguments = array('action' => 'browse');
        $this->add_text('main', $this->paging->display());

        $images = $this->images_bl->get_images($this->page_id, page::$user->get_user_id());
        if (is_array($images) && count($images) > 0) {
            $this->list_images($images);
        } else {
            $this->notice('No images found.');
        }

        $this->add_text('main', $this->paging->display());
    }

    protected function browse_all() {
        $your_images = $this->images_bl->get_image_count();
        $this->paging->total_items = $your_images;
        $this->paging->url_arguments = array('action' => 'browseall');
        $this->add_text('main', $this->paging->display());

        $images = $this->images_bl->get_images($this->page_id);
        if (is_array($images) && count($images) > 0) {
            $this->list_images($images);
        } else {
            $this->notice('No images found.');
        }

        $this->add_text('main', $this->paging->display());
    }

    protected function list_images($result) {
        $table = new table();

        $table->add_header(array(
            'Thumb',
            'Description',
            'ID',
            'Size',
            'Owner',
            ''
        ));

        foreach ($result AS $repeat) {
            $image_name = htmlentities(stripslashes($repeat['image_name']));
            $time = date(forum_config::$date_format, $repeat['timestamp']);
            $image_id = $repeat['image_id'];
            $head_exp = explode('/', $repeat['header']);
            $extension = array_pop($head_exp);
            $owner = $repeat['username'];
            if ($repeat['owner_id'] == page::$user->get_user_id() || page::$user->get_level() >= userlevels::$moderator) {
                $del = '<a href="' . html::gen_url('images.php', array('action' => 'delete', 'image_id' => $repeat['image_id'])) . '"><img alt="" src="' . forum_images::delete_icon(page::$user->get_style()) . '" /></a>';
            } else {
                $del = '';
            }

            $extra = '';
            if ($repeat['description'] != '') {
                $extra .= html::clean_text($repeat['description']);
            }

            if ($repeat['tag'] != '') {
                if ($repeat['description'] != '') {
                    $extra .= '<br /><br />';
                }
                $extra .= '<span class="italic">Tags: ' . html::clean_text($repeat['tag']) . '</span>';
            }

            if (strstr($repeat['tag'], 'NSFW') != FALSE && !isset($_GET['nsfw'])) {
                $image = '<a href="' . html::gen_url('images.php', array('action' => 'view', 'image_id' => $image_id)) . '"><img src="http://images.apegaming.net/nsfw.png" alt="No Thumb" /></a>';
            } else {
                $image = '<a href="' . html::gen_url('images.php', array('action' => 'view', 'image_id' => $image_id)) . '"><img src="' . forum_config::image_warehouse_url() . 'thumb/' . $image_id . '.' . $extension . '" alt="No Thumb" /></a>';
            }

            $table->add_data(array(
                $image,
                $extra,
                $image_id,
                intval($repeat['size'] / 1024) . 'KB',
                $owner,
                $del
            ));

            $table->add_data(html::clean_text($image_name) . ' - ' . $time);
        }

        $this->add_text('main', $table->v_display());
    }

    protected function upload() {
        if (page::$user->get_level() >= userlevels::$member) {
            if (isset($_FILES) && count($_FILES) > 0) {
                $image = $this->image_manager->image_upload($_FILES, 'uploaded_image', 0, null, false);
                if ($image == false) {
                    $this->notice("Failed to upload image.");
                } else {
                    $this->images_bl->add_image($image->ImageName(), $image->ImageFileName(), page::$user->get_user_id(), time(), $image->ImageType(), $image->ImageSize());
                }
            }

            $this->add_text('main', '<form enctype="multipart/form-data" action="' . html::gen_url('images.php', array('action' => 'upload')) . '" method="post">
                    <div>
                            <input type="file" name="uploaded_image[]" /> <input type="submit" value="Upload" />
                    </div>
                    </form>
            ');
        } else {
            throw new Exception('Access denied.');
        }
    }
}

?>