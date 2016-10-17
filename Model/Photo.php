<?php
class Photo extends AppModel
{
    public $name = 'Photo';
    public $use = array('Photo','Event');
    public $recursive = -1;
    public $actsAs = array('Containable', 'Image' => array(
        'settings' => array(
            'titleField' => 'title',
            'fileField' => 'file', ),
        'photos' => array(
            'big' => array(
                'destination' => 'events',
                'size' => array('width' => 600, 'height' => 500),
            ),
            'small' => array(
                'destination' => 'events/small',
                'size' => array('width' => 75, 'height' => 75), ),
        ), ));

    /*
     * model associations
     */
    public $belongsTo = array('Event','User');

    /**
     * delete a photo uploaded by the user
     * it returns the photo data if it exists or false if it does not.
     *
     * @param int $photoId
     * @param int $userId
     */
    public function deletePhoto($photoId, $userId)
    {
        $this->contain(array('Event' => array('Venue' => array('City' => array('Country')))));
        $photo = $this->find('first', array('conditions' => array('Photo.id' => $photoId,
            'Photo.user_id' => $userId, )));
        if ($photo) {
            $this->delete($photoId);

            return $photo;
        } else {
            return false;
        }
    }
}
