<?php

/*
  Image manager

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

class image_object {

    private $image_name;
    private $image_full_name;
    private $image_type;
    private $image_file_name;
    private $image_size;

    public function ImageName($value = null) {
        if ($value === null) {
            return $this->image_name;
        } else {
            $this->image_name = $value;
        }
    }

    public function ImageFullName($value = null) {
        if ($value === null) {
            return $this->image_full_name;
        } else {
            $this->image_full_name = $value;
        }
    }

    public function ImageType($value = null) {
        if ($value === null) {
            return $this->image_type;
        } else {
            $this->image_type = $value;
        }
    }

    public function ImageFileName($value = null) {
        if ($value === null) {
            return $this->image_file_name;
        } else {
            $this->image_file_name = $value;
        }
    }

    public function ImageSize($value = null) {
        if ($value === null) {
            return $this->image_size;
        } else {
            $this->image_size = $value;
        }
    }

}

class image_manager {

    private $image_location;
    private $image_thumbnail_location;
    private $max_image_width = 0;
    private $max_image_height = 0;
    private $thumb_width;
    private $thumb_height;
    private $thumbnails = false;
    private $overwrite = false;
    private $generated_filename_length = 12;
    private $image_suffix = '';

    public function __construct($image_location, $image_thumb_location = null, $max_width = null, $max_height = null, $thumb_width = null, $thumb_height = null) {
        $this->image_location = $image_location;

        if ($image_thumb_location !== null) {
            $this->image_thumbnail_location = $image_thumb_location;
        }

        if ($max_width !== null) {
            $this->max_image_width = $max_width;
        }

        if ($max_height !== null) {
            $this->max_image_height = $max_height;
        }

        if ($thumb_width !== null) {
            $this->thumb_width = $thumb_width;
            $this->thumbnails = true;
        }

        if ($thumb_height !== null) {
            $this->thumb_height = $thumb_height;
            $this->thumbnails = true;
        }
    }

    public function set_filename_length($value) {
        $this->generated_filename_length = $value;
    }

    public function set_image_location($location) {
        $this->image_location = $location;
    }

    public function set_image_thumbnail_location($location) {
        $this->image_thumbnail_location = $location;
    }

    public function set_image_suffix($value) {
        $this->image_suffix = $value;
    }
    
    public function set_overwrite($value) {
        $this->overwrite = $value;
    }

    public function set_thumbnail_width($width) {
        if ($width === 0) {
            $width = null;
        }

        $this->thumb_width = $width;
        if ($this->thumb_width === null && $this->thumb_height === null) {
            $this->thumbnails = false;
        } else {
            $this->thumbnails = true;
        }
    }

    public function set_thumbnail_height($height) {
        if ($height === 0) {
            $height = null;
        }

        $this->thumb_height = $height;
        if ($this->thumb_width === null && $this->thumb_height === null) {
            $this->thumbnails = false;
        } else {
            $this->thumbnails = true;
        }
    }

    public function set_max_image_width($width) {
        $this->max_image_width = $width;
    }

    public function set_max_image_height($height) {
        $this->max_image_height = $height;
    }

    private function remove_file_extension($input) {
        $array = explode(".", $input);
        array_pop($array);
        $output = implode(".", $array);
        return $output;
    }

    public function image_upload($files, $upload_reference, $image_reference, $filename = null, $use_original_name = false) {
        $image = new image_object();
        if (strlen($files[$upload_reference]['name'][$image_reference]) === 0) {
            return false;
        }

        $newfilename = '';

        if ($use_original_name == true) {
            $filename = $this->remove_file_extension($files[$upload_reference]['name'][$image_reference]) . $this->image_suffix;
        }

        $image->ImageFileName($files[$upload_reference]['name'][$image_reference]);
        $image->ImageSize($files[$upload_reference]['size'][$image_reference]);
        $image->ImageType($files[$upload_reference]['type'][$image_reference]);

        switch ($files[$upload_reference]['type'][$image_reference]) {
            case "image/pjpeg":
            case "image/jpeg":
                $extension = 'jpeg';
                break;
            case "image/png":
                $extension = 'png';
                break;
            case "image/gif":
                $extension = 'gif';
                break;
            default:
                throw new Exception("Unsupported file type.");
                break;
        }

        if ($files[$upload_reference]['type'][$image_reference] == "image/pjpeg") {
            $files[$upload_reference]['type'][$image_reference] = "image/jpeg";
        }

        $fileinput = $files[$upload_reference]['tmp_name'][$image_reference];

        if (strlen(chop($fileinput)) > 0) {
            if ($filename === null) {
                $image->ImageName(apetech::random_string($this->generated_filename_length));
                $newfilename = $image->ImageName() . '.' . $extension;
                if ($this->overwrite == false) {
                    while (file_exists($this->image_location . $newfilename) == true) {
                        $image->ImageName(apetech::random_string($this->generated_filename_length));
                        $newfilename = $image->ImageName() . '.' . $extension;
                    }
                } else {
                    if (file_exists($this->image_location . $newfilename) == true) {
                        unlink($this->image_location . $newfilename);
                    }
                }
            } else {
                $n = 1;
                $image->ImageName($filename . $n);
                $newfilename = htmlentities($image->ImageName() . '.' . $extension);
                if ($this->overwrite == false) {
                    while (file_exists($this->image_location . $newfilename) == true) {
                        $n++;
                        $image->ImageName($filename . $n);
                        $newfilename = htmlentities($image->ImageName() . '.' . $extension);
                    }
                } else {
                    if (file_exists($this->image_location . $newfilename) == true) {
                        unlink($this->image_location . $newfilename);
                    }
                }
            }

            $image->ImageFullName($image->ImageName() . '.' . $extension);

            if (!move_uploaded_file($files[$upload_reference]['tmp_name'][$image_reference], $this->image_location . $newfilename)) {
                throw new Exception("Unable to move file.");
            } else {
                chmod($this->image_location . $newfilename, 0644);
                $size = getimagesize($this->image_location . $newfilename);
                if ($this->max_image_height > 0 || $this->max_image_width > 0) {
                    if (($size[0] > $this->max_image_width && $this->max_image_width != 0) || ($size[1] > $this->max_image_height && $this->max_image_height != 0)) {
                        self::gen_thumb($this->image_location . $newfilename, $this->image_location . $newfilename, $this->max_image_width, $this->max_image_height);
                        chmod($this->image_location . $newfilename, 0644);
                    }
                }

                if ($this->thumbnails == true) {
                    if (self::gen_thumb($this->image_location . $newfilename, $this->image_thumbnail_location . $newfilename)) {
                        chmod($this->image_thumbnail_location . $newfilename, 0644);
                    }
                }
            }
        }

        return ($image);
    }

    //thumbnail generator
    private function gen_thumb($img_in, $img_out, $width = null, $height = null) {
        if ($width === null) {
            $width = $this->thumb_width;
        }

        if ($height === null) {
            $height = $this->thumb_height;
        }

        $return = true;
        $name_split = explode(".", $img_in);
        $extension = array_pop($name_split);
        list($_width, $_height) = getimagesize($img_in);
        $newheight = $_height;
        $newwidth = $_width;

        if ($newwidth > $width && $width > 0) {
            $ratio = $width / $newwidth;
            $newwidth = $width;
            $newheight = intval($newheight * $ratio);
        }

        if ($newheight > $height && $height > 0) {
            $ratio = $height / $newheight;
            $newheight = $height;
            $newwidth = intval($newwidth * $ratio);
        }

        // Load
        $thumb = imagecreatetruecolor($newwidth, $newheight);

        switch ($extension) {
            case "jpeg":
                $source = @imagecreatefromjpeg($img_in);
                break;

            case "png":
                $source = @imagecreatefrompng($img_in);
                break;

            case "gif":
                $source = @imagecreatefromgif($img_in);
                break;

            default:
                $source = false;
                break;
        }

        if (!$source) {
            throw new Exception("Error creating thumbnail.");
        } else {
            // Resize
            if (@imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $_width, $_height)) {

                switch ($extension) {
                    case "jpeg":
                        $data = @imagejpeg($thumb, $img_out, 100);
                        break;

                    case "png":
                        $data = @imagepng($thumb, $img_out, 9);
                        break;

                    case "gif":
                        $data = @imagegif($thumb, $img_out);
                        break;

                    default:
                        throw new Exception("Unsupported file type.");
                        break;
                }
            } else {
                throw new Exception("Error resizing thumbnail");
            }
        }
        return($return);
    }

}

?>