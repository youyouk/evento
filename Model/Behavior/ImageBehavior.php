<?php
/**
 * Image Behavior uploads, resizes and deletes image files associated to models
 *
 * Use:
 * Include and set up the behavior in the models that are associated with images:
 *
 *  var $actsAs = array('Image' => array(
 *      'settings' => array(
 *          'titleField' => 'title', // the model field storing the model title
 *          'fileField' => 'file'), // the field used to store the file name
 *      'photos' => array( // photo resizing and paths
 *          'big' => array(
 *              'destination' => 'photos',
 *              'size' => 600,
 *              'type' => 'resize'),
 *          'small' => array(
 *              'destination' => 'photos/small',
 *              'size' => array('width' => 75, 'height' => 75))
 *      )));
 *
 * This example would create an image of 600x600 pixels in the img/photos folder,
 * a thumbnail of the image of 75x75 pixels in the photos/small folder and
 * store the the file name in the model's 'file' field.
 *
 * In the views create a form with a file input named 'filedata',
 *
 * 	echo $this->Form->file('Photo.filedata');
 *
 * In the controller just save the data as you usually do with:
 *
 * $this->Photo->save($this->data)
 */
class ImageBehavior extends ModelBehavior
{
    /*
     * behavior name
     */

    public $__name = 'Image';

    /*
     * store the unique file name for the current upload
     */
    public $__uniqueName = null;

    /*
     * errors array
     */
    public $errors = array();

    /*
     * current model
     */
    public $__Model;

    /*
     * images to be deleted
     */
    public $__deletedPhotos = array();

    /*
     * array to store the model settings also contains default settings
     */
    public $__settings = array(
        'default' => array(
            'filedata' => 'filedata',
            'fileField' => 'filename',
            'titleField' => 'title',
            'defaultFile' => null,
            'filename' => null,
            'plugin' => null,
            'allowed' => array('jpg','jpeg','gif','png'),
        ),
    );

    /*
     * image upload default rules
     */
    public $__rules = array(
        'default' => array(
            'destination' => 'photos',
            'type' => 'resizecrop',
            'size' => array('width' => '800', 'height' => '600'),
            'quality' => 100,
            'output' => 'jpg',
        ),
    );

    /*
     * default validation rules
     */
    public $__default_validation = array(
        'validType' => array(
            'rule' => array('validType'),
            'message' => 'File type is not valid',
        ),
        'uploadError' => array(
            'rule' => array('uploadError'),
            'message' => 'Error uploading file',
        ), );

    /**
     * set up models settings for the Image behavior.
     *
     * @param object $model
     * @param array  $settings
     */
    public function setup(Model $model, $settings = array())
    {

        // check if GD library is installed or not
        if (!function_exists('imagecreatefromjpeg')) {
            trigger_error('GD library not installed');
        }

        // set default model settings
        if (is_array($settings)) {
            if (isset($settings['settings']) && is_array($settings['settings'])) {
                $this->__settings[$model->alias] = array_merge($this->__settings['default'],
                    $settings['settings']);
            }
            if (isset($settings['photos']) || is_array($settings['photos'])) {
                $this->__settings[$model->alias]['photos'] = $settings['photos'];
            } else {
                $this->__settings[$model->alias]['photos'] = $this->__rules;
            }
        } else {
            $this->__settings[$model->alias] = $this->__settings['default'];
        }

        // make sure model has the required field
        if (!$model->hasField($this->__settings[$model->alias]['fileField'])) {
            trigger_error('ImageBehavior Error: The field "'.$this->__settings[$model->alias]['fileField']
                .'" doesn\'t exists in the model "'.$model->name.'".', E_USER_WARNING);
        }

        if (!isset($model->validate['filedata'])) {
            $model->validate['filedata'] = array();
        }

        $model->validate['filedata'] =
            array_merge($this->__default_validation, $model->validate['filedata']);
    }

    /**
     * If filedata field is not empty upload the file using the model settings
     * If it is empty set the fileField to the default.
     *
     * @param object $model
     */
    public function beforeSave(Model $model, $options = array())
    {
        // set current model
        $this->__Model = $model;

        if (isset($model->data[$model->alias][$this->__settings[$model->alias]['filedata']])) {

            // delete images if model already exists
            if (!empty($model->data[$model->alias][$model->primaryKey])) {
                $model->recursive = -1;
                $photo_model = $model->find('first', array('conditions' => array('id' => $model->id),
                    'fields' => array($this->__settings[$model->alias]['fileField']), ));
                array_push($this->__deletedPhotos,
                    $photo_model[$model->alias][$this->__settings[$model->alias]['fileField']]);
            }

            // upload images or set the default image for this model
            if (!empty($model->data[$model->alias][$this->__settings[$model->alias]['filedata']])) {
                $uniqueName = $this->__uniqueName(strtolower($model->data[$model->alias]['filedata']['name']));
                foreach ($this->__settings[$model->alias]['photos'] as $settings) {
                    $this->__upload(array_merge($this->__rules['default'], $settings), $uniqueName);
                }
                $this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['fileField']]
                    =  $uniqueName;
            } else {
                $this->__Model->data[$model->alias][$this->__settings[$model->alias]['fileField']] =
                    $this->__settings[$model->alias]['defaultFile'];
            }
        }

        return parent::beforeSave($model, $options);
    }

    /**
     * Upload the file and proccess it using the settings.
     *
     * @access private
     *
     * @param $settings mixed
     */
    public function __upload($settings, $uniqueName)
    {
        $file =
            $this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['filedata']];
        $destination = IMAGES.rtrim($settings['destination'], DS).DS.$uniqueName;

        // if the image has already been resized just copy it
        if (isset($this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['fileField']])
        && $this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['fileField']]
        != $this->__settings[$this->__Model->alias]['defaultFile']) {
            $ofile = IMAGES.rtrim($settings['destination'], DS).DS
            .$this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['fileField']];
            if (file_exists($ofile)) {
                $v = copy($ofile, $destination);
            }
        } else {
            // resize the image
            $this->__image($file, $destination, $settings['type'],
                $settings['size'], $settings['output'], $settings['quality']);
        }
    }

    /**
     * set image size and save it to destination.
     *
     * @access private
     *
     * @param array  $file
     * @param string $path
     * @param string $type
     * @param int    $size
     * @param string $output
     * @param int    $quality
     */
    public function __image($file, $path, $type = 'resize', $size = 100, $output = 'jpg', $quality = 75)
    {
        $type = strtolower($type);
        $output = strtolower($output);

        if (is_array($size)) {
            $maxW = intval($size['width']);
            $maxH = intval($size['height']);
        } else {
            $maxScale = intval($size);
        }

        // check sizes
        if (isset($maxScale)) {
            if (!$maxScale) {
                trigger_error('Max scale must be set', E_USER_WARNING);
            }
        } else {
            if (!$maxW || !$maxH) {
                trigger_error('Size width and height must be set', E_USER_WARNING);
            }
            if ($type == 'resize') {
                trigger_error('Provide only one number for size', E_USER_WARNING);
            }
        }

        if (is_numeric($quality)) {
            $quality = intval($quality);
            if ($quality > 100 || $quality < 1) {
                $quality = 75;
            }
        } else {
            $quality = 75;
        }

         // make sure there's enough memory to resize the file
        $this->setMemoryForImage($file['tmp_name']);

        // get some information about the file
        $uploadSize = getimagesize($file['tmp_name']);
        $uploadWidth  = $uploadSize[0];
        $uploadHeight = $uploadSize[1];
        $uploadType = $uploadSize[2];

        switch ($uploadType) {
            case 1: $srcImg = imagecreatefromgif($file['tmp_name']); break;
            case 2: $srcImg = imagecreatefromjpeg($file['tmp_name']); break;
            case 3: $srcImg = imagecreatefrompng($file['tmp_name']); break;
            default: $this->error('File type must be GIF, PNG, or JPG to resize');
        }

        switch ($type) {
            case 'resize':
                # Maintains the aspect ration of the image and makes sure that it fits
                # within the maxW and maxH (thus some side will be smaller)
                // -- determine new size
                if ($uploadWidth > $maxScale || $uploadHeight > $maxScale) {
                    if ($uploadWidth > $uploadHeight) {
                        $newX = $maxScale;
                        $newY = ($uploadHeight*$newX)/$uploadWidth;
                    } elseif ($uploadWidth < $uploadHeight) {
                        $newY = $maxScale;
                        $newX = ($newY*$uploadWidth)/$uploadHeight;
                    } elseif ($uploadWidth == $uploadHeight) {
                        $newX = $newY = $maxScale;
                    }
                } else {
                    $newX = $uploadWidth;
                    $newY = $uploadHeight;
                }

                $dstImg = imagecreatetruecolor($newX, $newY);
                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newX, $newY, $uploadWidth, $uploadHeight);
                break;

            case 'resizemin':
                # Maintains aspect ratio but resizes the image so that once
                # one side meets its maxW or maxH condition, it stays at that size
                # (thus one side will be larger)
                #get ratios
                $ratioX = $maxW / $uploadWidth;
                $ratioY = $maxH / $uploadHeight;

                #figure out new dimensions
                if (($uploadWidth == $maxW) && ($uploadHeight == $maxH)) {
                    $newX = $uploadWidth;
                    $newY = $uploadHeight;
                } elseif (($ratioX * $uploadHeight) > $maxH) {
                    $newX = $maxW;
                    $newY = ceil($ratioX * $uploadHeight);
                } else {
                    $newX = ceil($ratioY * $uploadWidth);
                    $newY = $maxH;
                }

                $dstImg = imagecreatetruecolor($newX, $newY);
                imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newX, $newY, $uploadWidth, $uploadHeight);
                break;

            case 'resizecrop':
                // resize to max, then crop to center
                $ratioX = $maxW / $uploadWidth;
                $ratioY = $maxH / $uploadHeight;

                if ($ratioX < $ratioY) {
                    $newX = round(($uploadWidth - ($maxW / $ratioY))/2);
                    $newY = 0;
                    $uploadWidth = round($maxW / $ratioY);
                    $uploadHeight = $uploadHeight;
                } else {
                    $newX = 0;
                    $newY = round(($uploadHeight - ($maxH / $ratioX))/2);
                    $uploadWidth = $uploadWidth;
                    $uploadHeight = round($maxH / $ratioX);
                }

                $dstImg = imagecreatetruecolor($maxW, $maxH);
                imagecopyresampled($dstImg, $srcImg, 0, 0, $newX, $newY, $maxW, $maxH,
                    $uploadWidth, $uploadHeight);
                break;

            case 'crop':
                // -- a straight centered crop
                $startY = ($uploadHeight - $maxH)/2;
                $startX = ($uploadWidth - $maxW)/2;
                $dstImg = imageCreateTrueColor($maxW, $maxH);
                ImageCopyResampled($dstImg, $srcImg, 0, 0, $startX, $startY, $maxW, $maxH, $maxW, $maxH);
                break;
            }

        $write = false;
        // write the file
        switch ($output) {
            case 'jpg':
                $write = @imagejpeg($dstImg, $path, $quality);
                break;
            case 'png':
                $write = @imagepng($dstImg, $path.'.png', $quality);
                break;
            case 'gif':
                $write = @imagegif($dstImg, $path.'.gif', $quality);
                break;
        }

        // clean up
        imagedestroy($dstImg);
        imagedestroy($srcImg);

        if (!$write) {
            $this->error('Error saving file');
        }
    }

    /**
     * return the extension of a file.
     *
     * @param string file
     */
    public function __ext($file)
    {
        return trim(substr($file, strrpos($file, '.') + 1, strlen($file)));
    }

    /**
     * add a message to the error stack (for outside checking).
     *
     * @param string $message
     */
    public function error($message)
    {
        array_push($this->errors, $message);
    }

    /**
     * returns unique name for the file looking into the provided destination directory.
     *
     * @access private
     *
     * @param $file string
     */
    public function __uniqueName($name)
    {
        $parts = pathinfo($name);
        $ext = $parts['extension'];
        $name = $parts['filename'];
        $exists = true;
        $n = 1;

        if ($this->__settings[$this->__Model->alias]['filename'] == null
        && isset($this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['titleField']])) {
            $name = strtolower(Inflector::slug($this->__Model->data[$this->__Model->alias][$this->__settings[$this->__Model->alias]['titleField']], '-'));
        }
        while ($exists) {
            foreach ($this->__getDestinations() as $destination) {
                $full_name = $name.'-'.$n.'.'.$ext;
                $full_path = IMAGES.str_replace('/', DS, $destination).DS.$full_name;
                $exists = file_exists($full_path) || in_array($full_name, $this->__deletedPhotos);
            }
            $n++;
        }

        return $full_name;
    }

    /**
     * set the current model before validating the data.
     *
     * @param object $model
     */
    public function beforeValidate(Model $model, $options = array())
    {
        $this->__Model = $model;

        return parent::beforeValidate($model, $options);
    }

    /**
     * delete photos in filesystem before deleting the model.
     *
     * @param object $model
     */
    public function beforeDelete(Model $model, $cascade = true)
    {
        $this->__Model = $model;
        $model->read(null, $model->id);
        $photo = $model->data;
        $settings = $model->actsAs['Image'];

        if ($photo[$model->alias][$this->__settings[$model->alias]['fileField']] !=
            $this->__settings[$model->alias]['defaultFile']) {
            array_push($this->__deletedPhotos, $photo[$model->alias][$this->__settings[$model->alias]['fileField']]);
            $this->__purgePhotos();
        }

        return parent::beforeDelete($model, $cascade);
    }

    /**
     * get the directory names where the photos and thumbnails are being saved.
     *
     * @access private
     */
    public function __getDestinations()
    {
        $behavior_name = $this->__name;
        $destinations_array = array();
        if ($this->__settings[$this->__Model->alias]['plugin']) {
            $behavior_name = $this->__settings[$this->__Model->alias]['plugin'].'.'.$this->__name;
        }

        if (isset($this->__Model->actsAs[$behavior_name]['photos'])
        && is_array($this->__Model->actsAs[$behavior_name]['photos'])) {
            foreach ($this->__Model->actsAs[$behavior_name]['photos'] as $photo) {
                $destination = isset($photo['destination']) ? $photo['destination']
                    : $this->__rules['default']['destination'];
                array_push($destinations_array, $destination);
            }
        } else {
            array_push($destinations_array, $this->__rules['default']['destination']);
        }

        return $destinations_array;
    }

    /**
     * Delete all photos in  $this->__deletePhotos array.
     */
    public function __purgePhotos()
    {
        foreach ($this->__deletedPhotos as $photo) {
            if ($photo != $this->__settings[$this->__Model->alias]['defaultFile']) {
                foreach ($this->__getDestinations() as $destination) {
                    if (is_file(IMAGES.$destination.DS.$photo)) {
                        unlink(IMAGES.$destination.DS.$photo);
                    }
                }
            }
        }
    }

    /**
     * Delete photos after saving.
     *
     * @param object $model
     * @param bool   $created
     */
    public function afterSave(Model $model, $created, $options = array())
    {
        $this->__purgePhotos();
        if (!empty($model->data[$model->alias][$this->__settings[$model->alias]['filedata']])) {
          unlink($model->data[$model->alias][$this->__settings[$model->alias]['filedata']]['tmp_name']);
        }
        return parent::afterSave($model, $created, $options);
    }

    /**
     * Make sure we have enought memory to upload the image.
     *
     * @param string $filename
     */
    public function setMemoryForImage($filename)
    {
        $imageInfo = getimagesize($filename);
        $MB = 1048576; // number of bytes in 1M
        $K64 = 65536; // number of bytes in 64K
        $TWEAKFACTOR = 3; // Or whatever works for you
        $channels = (isset($imageInfo['channels'])) ? $imageInfo['channels'] : 4;
        $bits = (isset($imageInfo['bits'])) ? $imageInfo['bits'] : 8;
        $memoryNeeded = round(($imageInfo[0] * $imageInfo[1] * $bits * $channels / 8 + $K64) * $TWEAKFACTOR);
        $memoryLimitMB = intval(ini_get('memory_limit'));
        if (!$memoryLimitMB) {
            $memoryLimitMB = 8;
        }
        $memoryLimit = $memoryLimitMB * $MB;

        if (function_exists('memory_get_usage')
        && memory_get_usage() + $memoryNeeded > $memoryLimit) {
            $newLimit = $memoryLimitMB + ceil((memory_get_usage() + $memoryNeeded - $memoryLimit) / $MB);
            ini_set('memory_limit', $newLimit.'M');

            return true;
        } else {
            return false;
        }
    }

    ////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////// VALIDATION RULES
    ////////////////////////////////////////////////////////////////

    /**
     * file can't be empty.
     *
     * @param $data mixed
     */
    public function fileNotEmpty($data)
    {
        return (!empty($this->__Model->data[$this->__Model->alias]['filedata']));
    }

    /**
     * check valid file type.
     *
     * @param $data mixed
     */
    public function validType($data)
    {
        if (isset($data->data[$this->__Model->alias]['filedata']['name'])) {
            return (in_array($this->__ext(strtolower(
                $data->data[$this->__Model->alias]['filedata']['name'])),
                $this->__settings[$this->__Model->alias]['allowed']));
        }

        return true;
    }

    /**
     * check upload error.
     *
     * @param $data mixed
     */
    public function uploadError($data)
    {
        if (isset($data->data[$this->__Model->alias]['filedata']['name'])) {
            return !($data->data[$this->__Model->alias]['filedata']['error']);
        }

        return true;
    }
}
