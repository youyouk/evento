<?php
class Event extends AppModel
{
    public $name = 'Event';
    public $useTable = 'events';
    public $actsAs = array('Containable',
        'Image' => array(
            'settings' => array(
                'titleField' => 'name',
                'fileField' => 'logo',
                'defaultFile' => 'event_logo.jpg', ),
            'photos' => array(
                'logo' => array(
                    'destination' => 'logos',
                    'size' => array('width' => 150, 'height' => 152), ),
                'small_logo' => array(
                    'destination' => 'logos/small',
                    'size' => array('width' => 75, 'height' => 75),
                ),
                'big_logo' => array(
                    'destination' => 'logos/big',
                    'size' => array('width' => 1200, 'height' => 280),
                ),
            ), ),
        );

    // limit the events added with the repeat option to avoid server overload
    // 0 disables the limit check
    public $repeatLimit = 0;

    // limit repeat events to a number of months in the future
    // 0 disables the month limit check
    public $repeatMonthsLimit = 0;

    /*
     * validation rules
     */

    public $validate = array(
        'name' => array(
            'rule' => array('minLength', 2),
            'message' => 'Name must be at least 2 characters long',
            'required' => true, ),
        'notes' => array(
            'rule' => array('minLength', 2),
            'message' => 'Please add some notes',
            'required' => true, ),
        'web' => array(
            'rule' => 'url',
            'message' => 'Please enter a valid web address.',
            'required' => false,
            'allowEmpty' => true, ),
        'end_date' => array(
            'rule' => 'checkEventDate',
            'message' => 'Please enter a valid end date',
            'required' => true, ),
        'category_id' => array(
            'rule' => 'numeric',
            'required' => true,
            'allowEmpty' => false,
            'message' => 'Please select a category',
        ),
        'venue_id' => array(
            'rule' => 'validVenue',
            'message' => 'Please select a venue.',
            'required' => true,
            ),
        'recaptcha' => array(
            'notEmpty'    => array(
                'rule' => 'notBlank',
                'on' => 'create',
                'message' => 'Incorrect captcha',
                'required' => true, ),
            ),
        );

    /*
     * model asociations
     */

    public $hasAndBelongsToMany = array(
        'Tag' => array('className' => 'Tag'),
        'Attendees' => array(
            'className' => 'User',
            'unique' => false,
            'joinTable' => 'events_users',
            'foreignKey' => 'event_id',
            'asociationForeignKey' => 'user_id',
            'conditions' => array('active' => true),
            'fields' => array('id','username','photo', 'slug'), ),
        );

    public $belongsTo = array(
        'Venue',
        'Category',
        'User' => array(
            'fields' => array('id','username','slug', 'photo'), ),
        );

    public $hasMany = array(
        'Comment' => array(
            'className' => 'Comment',
            'order' => 'Comment.created ASC',
            'dependent' => true, ),
        'Photo' => array(
            'className' => 'Photo',
            'dependent' => true,
        ),
    );

    /**
     * get the city id if exists or save it if doesn't before saving the event.
     *
     * @param array $options
     */
    public function beforeValidate($options = array())
    {
        if (!isset($this->data['Event']['id']) || !$this->data['Event']['id']) {
            $this->data['Event']['promoted'] = 0;
            $this->data['Event']['promoted_in_category'] = 0;
        }

        if (isset($this->data['Event']['filedata']['name']) && empty($this->data['Event']['filedata']['name'])) {
            unset($this->data['Event']['filedata']);
        }

        if (isset($this->data['Event']['delete_logo']) && $this->data['Event']['delete_logo'] &&
        isset($this->data['Event']['id'])) {
            $this->recursive = -1;
            $e = $this->find('first', array('conditions' => array('Event.id' => $this->data['Event']['id']),
                'fields' => array('logo'), ));
            if ($e['Event']['logo']) {
                if (is_file(WWW_ROOT.'img/logos/'.$e['Event']['logo'])) {
                    unlink(WWW_ROOT.'img/logos/'.$e['Event']['logo']);
                }
                if (is_file(WWW_ROOT.'img/logos/small/'.$e['Event']['logo'])) {
                    unlink(WWW_ROOT.'img/logos/small/'.$e['Event']['logo']);
                }
                $this->data['Event']['logo'] = null;
            }
        }

        if ((!isset($this->data['Event']['end_date'])
        || $this->data['Event']['end_date'] < $this->data['Event']['start_date'])
        && isset($this->data['Event']['start_date'])) {
            $this->data['Event']['end_date'] = $this->data['Event']['start_date'];
        }

        if (!isset($this->data['Event']['status']) || ($this->data['Event']['status'] != 'TENTATIVE'
        && $this->data['Event']['status'] != 'CANCELLED')) {
            $this->data['Event']['status'] = 'CONFIRMED';
        }

        return parent::beforeValidate($options);
    }

    /**
     * create event slug.
     *
     * @param array $options
     *
     * @return bool
     */
    public function beforeSave($options = array())
    {
        if (isset($this->data['Event']['web'])) {
            if (!preg_match('(^http)', $this->data['Event']['web'])) {
                $this->data['Event']['web'] = 'http://' . $this->data['Event']['web'];
            }
        }

        if (isset($this->data['Event']['name'])) {
            if (isset($this->data['Event']['id']) && !empty($this->data['Event']['id'])) {
                $this->recursive = -1;
                $event = $this->find('first', array('conditions' => array('Event.id' => $this->data['Event']['id'])));
                if (strtolower($event['Event']['name']) != strtolower($this->data['Event']['name'])) {
                    $this->data['Event']['slug'] = $this->__getSlug($this->data);
                }
            } else {
                $this->data['Event']['slug'] = $this->__getSlug($this->data);
            }
        }

        if (empty($this->data['Event']['venue_id'])) {
            $this->Venue->save($this->data);
            $this->data['Event']['venue_id'] = $this->Venue->getLastInsertID();
        }

        if (!isset($this->data['Event']['id']) || empty($this->data['Event']['id'])) {
          if (Configure::read('evento_settings.moderateEvents')) {
              $this->data['Event']['published'] = 0;
          } else {
              $this->data['Event']['published'] = 1;
          }
        }

        return parent::beforeSave($options);
    }

    /**
     * As the event id is used in the slug we create it after saving the event.
     * If event is edited remove tags to update with the new event tags.
     *
     * @param $created bool
     */
    public function afterSave($created, $options = array())
    {
        Cache::delete('evento_active_cities');
        if (isset($this->data['Event']['tags'])) {
            if (!$created) {
                $this->Tag->deleteEventTags($this->id);
            }
            $tags = explode(',', $this->data['Event']['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if ($tag != '') {
                    $this->Tag->create();
                    $newTag['Tag']['id'] = $this->Tag->field('id', array('name' => $tag));
                    $newTag['Tag']['name'] = $tag;
                    $newTag['Event']['id'] = $this->id;
                    if (!in_array($newTag['Tag']['id'], $this->Tag->getEventTagIds($this->id))) {
                        $this->Tag->save($newTag);
                    }
                }
            }
        }

        return parent::afterSave($created, $options);
    }

    /**
     * delete promoted events cache file after delete.
     */
    public function afterDelete()
    {
        Cache::delete('evento_active_cities');
    }

    /**
     * Date validation rule.
     * End date must be later than start date, at least while we can't travel in time.
     *
     * @return bool
     */
    public function checkEventDate()
    {
        if ($this->data['Event']['end_date'] < $this->data['Event']['start_date']) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * make sure user selected a valid venue or provided the data
     * to add a new one.
     */
    public function validVenue()
    {
        if (!isset($this->data['Event']['venue_id']) || empty($this->data['Event']['venue_id'])) {
            if ($this->Venue->create($this->data) && $this->Venue->validates()) {
                return true;
            } else {
                return false;
            }
        } else {
            return $this->Venue->exists($this->data['Event']['venue_id']);
        }
    }

    /**
     * add an attendee to the event if it has not been added before.
     *
     * @param int  $eventId
     * @param bool $isGoing
     * @param int  $userId
     */
    public function attendee($eventId, $isGoing, $userId)
    {
        if ($isGoing) {
            $exists = $this->EventsUser->find('first',
                array('conditions' => array('event_id' => $eventId, 'user_id' => $userId)));
            if (!$exists) {
                $data['EventsUser']['event_id'] = $eventId;
                $data['EventsUser']['user_id'] = $userId;
                if ($this->EventsUser->save($data)) {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return $this->EventsUser->deleteAll(array('user_id' => $userId, 'event_id' => $eventId));
        }
    }

    /**
     * query database and get needed data to display event view page.
     *
     * @param string $slug
     *
     * @return array
     */
    public function viewEvent($slug)
    {
        $this->recursive = -1;

        $joins = array(
            array(
                'table' => 'venues',
                'alias' => 'Venue',
                'type' => 'left',
                'conditions' => array('Venue.id = Event.venue_id'),
            ),
            array(
                'table' => 'cities',
                'alias' => 'City',
                'type' => 'left',
                'conditions' => array('City.id = Venue.city_id'),
            ),
            array(
                'table' => 'countries',
                'alias' => 'Country',
                'type' => 'left',
                'conditions' => array('Country.id = City.country_id'),
            ),
            array(
                'table' => 'users',
                'alias' => 'User',
                'type' => 'left',
                'conditions' => array('User.id = Event.user_id'),
            ),
            array(
                'table' => 'categories',
                'alias' => 'Category',
                'type' => 'left',
                'conditions' => array('Category.id = Event.category_id'),
            ),
        );

        $fields = array('Event.*', 'City.*', 'Venue.*', 'Country.*', 'Category.*', 'User.*');
        $event = $this->find('first', array('conditions' => array('Event.slug' => $slug),
            'joins' => $joins, 'fields' => $fields, ));

        if (!empty($event)) {
            // tags
            $event['Tag'] = $this->Tag->EventsTag->find('all',
                array('conditions' => array('EventsTag.event_id' => $event['Event']['id']),
                'joins' => array(
                    array(
                        'table' => 'tags',
                        'alias' => 'Tag',
                        'type' => 'left',
                        'conditions' => array('Tag.id = EventsTag.tag_id'), ), ),
                'fields' => array('Tag.name', 'Tag.slug'), ));

            // photos
            $event['Photo'] = $this->Photo->find('all',
             array('conditions' => array('Photo.event_id' => $event['Event']['id']),
            'fields' => array('Photo.id', 'Photo.title', 'Photo.file'), 'order' => 'Photo.created DESC',
            'limit' => 21, ));

            // attendees
            $event['Attendees'] = $this->EventsUser->find('all',
                array('conditions' => array('EventsUser.event_id' => $event['Event']['id']),
                'joins' => array(
                    array(
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'left',
                        'conditions' => array('EventsUser.user_id = User.id'),
                    ),
                    array(
                       'table' => 'cities',
                       'alias' => 'City',
                       'type' => 'left',
                       'conditions' => array('User.city_id = City.id'),
                    ),
                    array(
                       'table' => 'countries',
                       'alias' => 'Country',
                       'type' => 'left',
                       'conditions' => array('City.country_id = Country.id'),
                    ),
                ),
                'fields' => array('User.id', 'User.username', 'User.slug', 'User.photo', 'City.name', 'Country.name'),
                'order' => 'EventsUser.id DESC', ));

            // comments
            $this->Comment->contain(array('User'));
            $event['Comment'] = $this->Comment->find('all',
                array('conditions' => array('Comment.event_id' => $event['Event']['id']),
                'fields' => array('User.username', 'User.slug', 'User.photo', 'Comment.id', 'Comment.comment',
                'Comment.created', ), ));
        }

        return $event;
    }

    /**
     * bulk publish and unpublish events.
     *
     * @param array $ids
     */
    public function bulkPublish($ids, $publish = true)
    {
        $this->revursive = -1;
        $this->updateAll(array('Event.published' => $publish), array('Event.id' => $ids));

        return true;
    }

    /**
     * bulk delete events.
     *
     * @param array $ids
     */
    public function bulkDelete($ids)
    {
        $this->deleteAll(array('Event.id' => $ids), true, true);
        Cache::delete('evento_active_cities');

        return true;
    }

    /**
     * dispatch save repeat events to the corresponding function.
     *
     * @param array $data
     */
    public function saveRepeat($data)
    {
        switch ($data['Event']['repeat']) {
            case 'daily':
                $this->saveRepeatDaily($data);
                break;
            case 'weekly':
                $this->saveRepeatWeekly($data);
                break;
            case 'monthly':
                $this->saveRepeatMonthly($data);
                break;
        }

        return true;
    }

    /**
     * save daily repeat events.
     *
     * @param array $data
     */
    public function saveRepeatDaily($data)
    {
        list($start_date, $end_date, $until, $occurrences) = $this->getDateVars($data);
        $data['Event']['repeat_daily_freq']++;
        $addedEvents = 1;

        // interval must be between 1 and 7
        if ($data['Event']['repeat_daily_freq'] < 1 || $data['Event']['repeat_daily_freq'] > 7) {
            return false;
        }

        // add 'repeat_dayly_freq' days to start and end dates
        $start_date = date('Y-m-d H:i:s',
            strtotime($start_date.' + '.$data['Event']['repeat_daily_freq'].' day'));
        $end_date = date('Y-m-d H:i:s',
            strtotime($end_date.' + '.$data['Event']['repeat_daily_freq'].' day'));

        // loop until 'repeat_until' date to add the repeat events
        $eventsData = array();
        while (($until != null && strtotime($start_date) <= strtotime($until))
            || ($occurrences != null && $addedEvents < $occurrences)) {

            // if start_date is later than months limit break the loop
            if ($this->repeatMonthsLimit > 0
                && strtotime($start_date) > strtotime('+ '.$this->repeatMonthsLimit.' months')) {
                break;
            }

            // check if 'daily_weekdays' is false or start_date is not
            // weekend before creating the event
            if ($data['Event']['daily_weekdays'] == 0 || !$this->isWeekend($start_date)) {
                $data['Event']['start_date'] = $start_date;
                $data['Event']['end_date'] = $end_date;
                //$this->create();
                //$saved = $this->save($data);
                $eventsData[] = $data;
                $addedEvents++;
            }
            if ($this->repeatLimit > 0 && $addedEvents >= $this->repeatLimit) {
                break;
            }

            // add 'repeat_dayly_freq' days to start and end dates
            $start_date = date('Y-m-d H:i:s',
                strtotime($start_date.' + '.$data['Event']['repeat_daily_freq'].' day'));
            $end_date = date('Y-m-d H:i:s',
                strtotime($end_date.' + '.$data['Event']['repeat_daily_freq'].' day'));
        }
        $this->saveMany($eventsData);

        return true;
    }

    /**
     * save weekly repeat events.
     *
     * @param array $data
     */
    public function saveRepeatWeekly($data)
    {
        list($start_date, $end_date, $until, $occurrences) = $this->getDateVars($data);
        $addedEvents = 1;

        $start_date = date('Y-m-d H:i:s', strtotime($start_date.' + 1 day'));
        $end_date = date('Y-m-d H:i:s', strtotime($end_date.' + 1 day'));

        // make sure at least a day has been selected to repeat the event
        $days_selected = false;
        foreach ($data['Event']['weekly'] as $wday) {
            if ($wday == 1) {
                $days_selected = true;
                break;
            }
        }
        if (!$days_selected) {
            return false;
        }

        // loop to create the events from 'start_date' to the 'until' date
        $eventsData = array();
        while (($until != null && strtotime($start_date) <= strtotime($until))
            || ($occurrences != null && $addedEvents < $occurrences)) {

            // if start_date is later than months limit break the loop
            if ($this->repeatMonthsLimit > 0
                && strtotime($start_date) > strtotime('+ '.$this->repeatMonthsLimit.' months')) {
                break;
            }

            // if current day is selected create the event
            $current_day = date('N', strtotime($start_date));
            if (isset($data['Event']['weekly'][$current_day])
                && $data['Event']['weekly'][$current_day] == 1) {
                $data['Event']['start_date'] = $start_date;
                $data['Event']['end_date'] = $end_date;
                    //$this->create();
                    //$saved = $this->save($data);
                    $eventsData[] = $data;
                $addedEvents++;
            }

            $start_date = date('Y-m-d H:i:s', strtotime($start_date.' + 1 day'));
            $end_date = date('Y-m-d H:i:s', strtotime($end_date.' + 1 day'));

            // add weeks to the event date if it is not repeated every week
            if (date('N', strtotime($start_date)) == 7) {
                $start_date = date('Y-m-d H:i:s',
                    strtotime($start_date.' + '.$data['Event']['repeat_weeks'].' weeks'));
                $end_date = date('Y-m-d H:i:s',
                    strtotime($end_date.' + '.$data['Event']['repeat_weeks'].' weeks'));
            }
            if ($this->repeatLimit > 0 && $addedEvents >= $this->repeatLimit) {
                break;
            }
        }
        $this->saveMany($eventsData);

        return true;
    }

    /**
     * save monthly repeat events.
     *
     * @param array $data
     */
    public function saveRepeatMonthly($data)
    {
        $addedEvents = 1;
        if (isset($data['Event']['repeat_months'])) {
            $data['Event']['repeat_months']++;
        }
        list($start_date, $end_date, $until, $occurrences) = $this->getDateVars($data);
        $original_start_date = $start_date;
        $date_diff = strtotime($end_date) - strtotime($start_date);

        // repeat months must be between 1 and 12
        if ($data['Event']['repeat_months'] < 1 || $data['Event']['repeat_months'] > 12) {
            return false;
        }

        // loop to create the events from 'start_date' to the 'until' date
        $eventsData = array();
        while (($until != null && strtotime($start_date) < strtotime($until))
            || ($occurrences != null && $addedEvents < $occurrences)) {

            // if start_date is later than months limit break the loop
            if (($this->repeatMonthsLimit > 0
                && strtotime($start_date) > strtotime('+ '.$this->repeatMonthsLimit.' months'))) {
                break;
            }

            if ($data['Event']['day'] == 'specific_day') { // a specific day is selected
                if ($data['Event']['direction'] == 'first') {
                    $start_date = $this->__getFirstDay($start_date, $data['Event']['month_day']);
                } else {
                    $start_date = $this->__getLastDay($start_date, $data['Event']['month_day']);
                }
                $data['Event']['monthly_weekdays'] = 0; // make sure this is set to 0
            } else {
                $start_date = $this->__get_monthly_date($data, $start_date);
            }
            $end_date = date('Y-m-d H:i:s', strtotime($start_date) + $date_diff);

            // check if 'monthly_weekdays' is false or start_date is not weekend
            if (($data['Event']['monthly_weekdays'] == 0 || !$this->isWeekend($start_date))
                && strtotime($start_date) > strtotime($original_start_date)) {
                $data['Event']['start_date'] = $start_date;
                $data['Event']['end_date'] = $end_date;
                //$this->create();
                //$saved = $this->save($data);
                $eventsData[] = $data;
                $addedEvents++;
            }
            if ($this->repeatLimit > 0 && $addedEvents >= $this->repeatLimit) {
                break;
            }

            if ($data['Event']['day'] == 'specific_day') {
                $month = date('m', strtotime($start_date));
                $year = date('Y', strtotime($start_date));
                $start_date = date($year.'-'.$month.'-1 '.date('H:i:s', strtotime($start_date)));
                $start_date = date('Y-m-d H:i:s', strtotime($start_date.' '
                .$data['Event']['repeat_months'].' months'));
            }
        }
        $this->saveMany($eventsData);

        return true;
    }

    /**
     * get new start date for monthly repeat event.
     *
     * @param array  $data
     * @param string $start_date
     */
    private function __get_monthly_date($data, $start_date)
    {
        $month = date('m', strtotime($start_date));
        $year = date('Y', strtotime($start_date));
        do {
            $month += $data['Event']['repeat_months'];
            if ($month > 12) {
                $month = $month - 12;
                $year++;
            }
        } while (!checkdate($month, date('d', strtotime($start_date)), $year));

        return date($year.'-'.sprintf('%02d', $month).'-'.date('d H:i:s', strtotime($start_date)));
    }

    /**
     * get first day of month for the monthly event repeat dates.
     *
     * @param string $start_date
     * @param int    $day
     */
    private function __getFirstDay($start_date, $day)
    {
        $month = date('m', strtotime($start_date));
        $year = date('Y', strtotime($start_date));
        $date = date($year.'-'.$month.'-01 '.date('H:i:s', strtotime($start_date)));
        if (date('N', strtotime($date)) !== $day) {
            $n = date('N', strtotime($date));
            if ($n > $day) {
                $offset = 8;
            } else {
                $offset = 1;
            }
            $day = $day + $offset - $n;
            $date = date($year.'-'.$month.'-'.sprintf('%02d', $day)
            .' '.date('H:i:s', strtotime($start_date)));
        }

        return $date;
    }

    /**
     * get last day of month for the monthly event repeat dates.
     *
     * @param string $start_date
     * @param int    $day
     */
    private function __getLastDay($start_date, $day)
    {
        $month = date('m', strtotime($start_date));
        $year = date('Y', strtotime($start_date));
        $date = date($year.'-'.$month.date('-t H:i:s', strtotime($start_date)));
        if (date('N', strtotime($date)) !== $day) {
            $n = date('N', strtotime($date));
            if ($n < $day) {
                $offset = $n - $day + 7;
            } else {
                $offset = $n - $day;
            }
            $day = date('t', strtotime($date)) - $offset;
            $date = date($year.'-'.$month.'-'.sprintf('%02d', $day)
            .' '.date('H:i:s', strtotime($start_date)));
        }

        return $date;
    }

    /**
     * check if $date is weekend.
     *
     * @param string $date
     */
    private function isWeekend($date)
    {
        return (date('N', strtotime($date)) > 5);
    }

    /**
     * return date variables from the data array.
     *
     * @param array data
     */
    private function getDateVars($data)
    {
        $until = null;
        $occurrences = null;
        if ($data['Event']['repeat_times'] == 'date') {
            $until = $this->deconstruct('start_date', $data['Event']['repeat_until']);
        } else {
            $occurrences = $data['Event']['repeat_occurrences'] + 1;
        }
        if (!isset($data['Event']['end_date'])) {
            $data['Event']['end_date'] = $data['Event']['start_date'];
        }

        return array(
            $this->deconstruct('start_date', $data['Event']['start_date']),        // start date
            $this->deconstruct('end_date', $data['Event']['end_date']),            // end date
            $until,                                                                // until date
            $occurrences,                                                        // number of occurences
        );
    }

    /**
     * generate unique slug for the event.
     *
     * @param array $event
     */
    private function __getSlug($event)
    {
        if (isset($event['Event']['name'])) {
            $slug = Inflector::slug(strtolower($event['Event']['name']), '-');
            if (!$slug) {
                $slug = urlencode($event['Event']['name']);
            }
            $this->recursive = -1;
            $events = $this->find('all', array('conditions' => array('Event.slug like' => $slug.'%')));
            if (!empty($events)) {
                $n = 0;
                $tmpSlug = $slug;
                $slugs = Set::extract('/Event/slug', $events);
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
