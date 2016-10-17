<?php
class User extends AppModel
{
    public $name = 'User';
    public $useTable = 'users';
    public $recursive = -1;
    public $actsAs = array('Containable', 'Acl' => array('type' => 'requester', 'enabled' => false),
        'Image' => array(
            'settings' => array(
                'titleField' => 'username',
                'fileField' => 'photo',
                'defaultFile' => 'user_photo.jpg', ),
            'photos' => array(
                'big' => array(
                    'destination' => 'users',
                    'size' => array('width' => 75, 'height' => 75), ),
                'small' => array(
                    'destination' => 'users/small',
                    'size' => array('width' => 16, 'height' => 16), ),
            ), ), );

    /*
     * Validation rules
     *
     * alter_email field is used when a user wants to edit the email address and
     * needs email confirmation option has been enabled by the admin.
     * The new email address will be stored here but not used until is confirmed.
     */

    public $validate = array(
        'city_id' => array(
            'rule' => array('numeric'),
            'required' => false,
            'allowEmpty' => true,
            'message' => 'Please select a country and enter a city name.', ),
        'username' => array(
            'notEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please write your desired username.', ),
            'length' => array(
                'rule' => array('between',2 , 25),
                'message' => 'Username must have between 2 and 25 characters.',
                'allowEmpty' => true, ),
        ),
        'password' => array(
            'notEmpty' => array(
                'rule' => 'notBlank',
                'message' => 'Please write your desired password.', ),
            'confirmation' => array(
                'rule' => 'passwordConfirmation',
                'message' => 'Password and password confirmation must be the same.',
                'on' => 'update', ),
        ),
        'recaptcha' => array(
            'notEmpty' => array(
                'rule' => 'notBlank',
                'on' => 'create',
                'message' => 'Incorrect captcha',
                'required' => true,
            ),
        ),
        'web' => array(
            'rule' => 'url',
            'message' => 'Please enter a valid web address.',
            'required' => false,
            'allowEmpty' => true, ),
        'email' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Please enter a valid email address.', ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This email is already in use.',
                'allowEmpty' => true, ),
        ),
        'alter_email' => array(
            'email' => array(
                'rule' => 'email',
                'message' => 'Please enter a valid email address.',
                'allowEmpty' => true, ),
            'unique' => array(
                'rule' => 'emailUnique',
                'message' => 'This email is already in use.',
                'allowEmpty' => true, ),
        ), );

    /*
     * model associations
     */

    public $belongsTo = array('City' => array('className' => 'City'), 'Group');

    public $hasAndBelongsToMany = array(
        'Attendee' => array(
            'className' => 'Event',
            'joinTable' => 'events_users',
            'foreignKey' => 'user_id',
            'associationForeignKey' => 'event_id',
            'unique' => false,
            'dependent' => true,
        ),
    );

    public $hasMany = array(
        'Event' => array('className' => 'Event', 'dependent' => true),
        'Comment' => array('className' => 'Comment', 'dependent' => true),
        'Photo' => array('className' => 'Photo', 'dependent' => true),
    );

    public function bindNode($user)
    {
        return array('model' => 'Group', 'foreign_key' => $user['User']['group_id']);
    }

    public function parentNode()
    {
        if (!$this->id && empty($this->data)) {
            return;
        }
        if (isset($this->data['User']['group_id'])) {
            $groupId = $this->data['User']['group_id'];
        } else {
            $groupId = $this->field('group_id');
        }
        if (!$groupId) {
            return;
        }

        return array('Group' => array('id' => $groupId));
    }

    /**
     * if country and city fields are not set unset data['City'].
     * in case it is set save the city if it does not exist in the cities table and get the id.
     *
     * @param array $options
     */
    public function beforeValidate($options = array())
    {
        if ((isset($this->data['City']['name']) && !empty($this->data['City']['name'])) ||
        (isset($this->data['City']['country_id']) && $this->data['City']['country_id'])) {
            $this->data['User']['city_id'] = $this->City->field('id',
                array('City.name' => $this->data['City']['name'],
                'City.country_id' => $this->data['City']['country_id'], ));
            if (!$this->data['User']['city_id']) {
                if ($this->City->save($this->data)) {
                    $this->data['User']['city_id'] = $this->City->getInsertID();
                }
            }
        } elseif (isset($this->data['City'])) {
            unset($this->data['City']);
            $this->data['User']['city_id'] = null;
        }
        if (isset($this->data['User']['filedata']['name']) && empty($this->data['User']['filedata']['name'])) {
            unset($this->data['User']['filedata']);
        }

        return parent::beforeValidate($options);
    }

    /**
     * create the user slug before saving to database.
     *
     * @param array $options
     *
     * @return bool
     */
    public function beforeSave($options = array())
    {
        if (!isset($this->data['User']['id']) || empty($this->data['User']['id'])) {
            $this->data['User']['slug'] = $this->__getSlug($this->data);
        }
        if (isset($this->data['User']['password'])) {
            $this->data = $this->hashPasswords($this->data, true);
        }

        return parent::beforeSave($options);
    }

    /**
     * delete the caches after deleting a user.
     */
    public function afterDelete()
    {
        Cache::delete('evento_active_cities');
        Cache::delete('evento_promoted');
    }

    /**
     * Check if password and password confirmation are the same.
     *
     * @return bool
     */
    public function passwordConfirmation()
    {
        return ($this->data['User']['password'] == $this->data['User']['password_confirm']);
    }

    /**
     * hashPasswords for Auth component
     * Don't use Auth autohash so if there are errors we can populate the password fields in
     * the form without having the password hash.
     *
     * @access public
     *
     * @param mixed $data
     * @param bool  $force
     *
     * @return array
     */
    public function hashPasswords($data, $force = false)
    {
        if (is_array($data) && isset($data[$this->alias])) {
            if ($force && isset($data[$this->alias]['password'])) {
                $data[$this->alias]['password'] =
                    Security::hash($data[$this->alias]['password'], null, true);
            }
        }

        return $data;
    }

    /**
     * custom validation rule to make sure user does not change his email to an existing one
     * this validation rule is used in alter_email but it needs to check the email field too.
     *
     * @param string $email
     */
    public function emailUnique($email)
    {
        $exists = $this->find('first', array('conditions' => array('email' => $email)));

        return empty($exists);
    }

    /**
     * bulk activate and deactivate users.
     *
     * @param array $ids
     * @param bool  $activate
     */
    public function bulkPublish($ids, $activate = true)
    {
        $this->revursive = -1;
        $this->updateAll(array('User.active' => $activate), array('User.id' => $ids));

        return true;
    }

    /**
     * bulk delete users.
     *
     * @param array $ids
     */
    public function bulkDelete($ids)
    {
        return $this->deleteAll(array('User.id' => $ids), true, true);
    }

    /**
     * get username slug for the user url.
     *
     * @param array $user
     */
    private function __getSlug($user)
    {
        if (isset($user['User']['username'])) {
            $slug = Inflector::slug(strtolower($user['User']['username']), '-');
            if (!$slug) {
                $slug = urlencode($user['User']['username']);
            }
            $this->recursive = -1;
            $users = $this->find('all', array('conditions' => array('User.slug like' => $slug.'%')));
            if (!empty($events)) {
                $n = 0;
                $tmpSlug = $slug;
                $slugs = Set::extract('/User/slug', $users);
                while (in_array($tmpSlug, $slugs)) {
                    $n++;
                    $tmpSlug = $slug.'-'.$n;
                }
                $slug = $tmpSlug;
            }

            return $slug;
        }
    }
}
