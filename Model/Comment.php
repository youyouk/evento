<?php
class Comment extends AppModel
{
    public $name = 'Comment';
    public $useTable = 'comments';
    public $actsAs = array('Containable');
    public $use = array('Comment');
    public $recursive = -1;

    /*
     * validation
     */

    public $validate = array(
        'comment' => array(
            'rule' => array('minLength', 2),
            'message' => 'Comment must be at least 2 characters long',
        ),
        'recaptcha' => array(
            'notEmpty'    => array(
                'rule' => 'notBlank',
                'on' => 'create',
                'message' => 'Incorrect captcha',
                'required' => true,
            ),
        ), );

    /*
     * model associations
     */

    public $belongsTo = array(
        'Event' => array('className' => 'Event'),
        'User' => array('className' => 'User',
            'fields' => array('User.username', 'User.slug', 'User.photo', 'User.active'), ),
    );

    /**
     * Save user comment.
     *
     * @param array $data
     * @param int   $eventId
     * @param int   $userId
     *
     * @return bool
     */
    public function saveComment($data, $eventId, $userId)
    {
        $data['Comment']['event_id'] = $eventId;
        $data['Comment']['user_id'] =  $userId;

        return $this->save($data);
    }

    /**
     * bulk delete comments.
     *
     * @param array $ids
     *
     * @return bool
     */
    public function bulkDelete($ids)
    {
        return $this->deleteAll(array('Comment.id' => $ids));
    }
}
