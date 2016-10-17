<?php
class Tag extends AppModel
{
    public $name = 'Tag';
    public $useTable = 'tags';
    public $uses = array('Tag', 'EventsTag');
    public $recursive = -1;

    /*
     * validation rules
     */

    public $validate = array(
        'name' => array(
            'length' => array(
                'rule' => array('minLength', 2),
                'message' => 'Name must be at least 2 characters long',
                'required' => true,
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'This tag already exists',
            ), ), );

    /*
     * model associations
     */

    public $hasAndBelongsToMany = array('Event' => array(
        'className' => 'Event',
        'unique' => false, ));

    /**
     * enable custom find methods.
     */
    public $findMethods = array('top' =>  true, 'active' => true);

    /**
     * create slug before saving tags to database
     * if tag already exists keep current name to avoid changing capital letters.
     *
     * @param array $options
     */
    public function beforeSave($options = array())
    {
        $this->data['Tag']['slug'] = Inflector::slug(strtolower(trim($this->data['Tag']['name'])), '-');
        if (!$this->data['Tag']['slug']) {
            $this->data['Tag']['slug'] = urlencode($this->data['Tag']['name']);
        }
        $tagExists = $this->find('first', array('conditions' => array('slug' => $this->data['Tag']['slug'])));
        if (!empty($tagExists)) {
            $this->data['Tag']['name'] = $tagExists['Tag']['name'];
        }

        return parent::beforeSave($options);
    }

    /**
     * delete promoted events after we save a tag just in case the event has this tag.
     *
     * @param bool $created
     */
    public function afterSave($created, $options = array())
    {
        Cache::delete('evento_promoted');

        return parent::afterSave($created, $options);
    }

    /**
     * return event ids with a specific tag or false if tag does not exist.
     *
     * @param string $tagSlug
     */
    public function getTagEventIds($tagSlug)
    {
        $tagId = $this->field('id', array('Tag.slug' => $tagSlug));
        if ($tagId) {
            $tagConditions['conditions']['tag_id'] = $tagId;
            $tagConditions['fields'] = 'event_id';
            $eventsId = $this->EventsTag->find('list', $tagConditions);

            return $eventsId;
        }

        return false;
    }

    /**
     * return tags used by events ordered by the number of times a tag is used.
     *
     * @param string $state
     * @param array  $query
     * @param array  $results
     *
     * @return array
     */
    public function _findActive($state, $query, $results = array())
    {
        if ($state == 'before') {
            return array_merge($query, array(
                'fields' => array('Tag.slug', 'Tag.name', 'Tag.id',
                    'count(events_tags.id) as events_num', ),
                'joins' => array(
                    array(
                        'table' => 'events_tags',
                        'alias' => 'events_tags',
                        'type' => 'right',
                        'conditions' => array('events_tags.tag_id = Tag.id'),
                    ), ),
                'group' => 'Tag.id',
                'order' => 'events_num DESC',
                ));
        } else {
            return $results;
        }
    }

    /**
     * list the top tags in a city	and category. Similar to active find method but this one also
     * takes care of countries, cities and categories.
     *
     * @param string $state
     * @param array  $query
     * @param array  $results
     *
     * @return array
     */
    public function _findTop($state, $query, $results = array())
    {
        if ($state == 'before') {
            return array_merge($query, array(
                'fields' => array('Tag.slug', 'Tag.name', 'Tag.id', 'count(Event.id) as totalEvents'),
                'joins' => array(
                    array(
                        'table' => 'events_tags',
                        'alias' => 'events_tags',
                        'type' => 'left',
                        'conditions' => array('events_tags.tag_id = Tag.id'),
                    ),
                    array(
                        'table' => 'events',
                        'alias' => 'Event',
                        'type' => 'left',
                        'conditions' => array('Event.id = events_tags.event_id'),
                    ),
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
                        'conditions' => array('Venue.city_id = City.id'),
                    ),
                    array(
                        'table' => 'countries',
                        'alias' => 'Country',
                        'type' => 'left',
                        'conditions' => array('City.country_id = Country.id'),
                    ), ),
                'group' => 'Tag.id HAVING totalEvents > 0',
                'order' => 'totalEvents DESC', ));
        } else {
            return $results;
        }
    }

    /**
     * delete caches after delete tag.
     *
     * @param int  $tagId
     * @param bool $cascade
     */
    public function delete($tagId = null, $cascade = true)
    {
        Cache::delete('evento_promoted');

        return parent::delete($tagId, $cascade);
    }

    /**
     * delete all tags in event.
     *
     * @param int $eventId
     */
    public function deleteEventTags($eventId)
    {
        $this->EventsTag->deleteAll(array('event_id' => $eventId));
    }

    /**
     * return tags in an Event.
     *
     * @param int $eventId
     */
    public function getEventTagIds($eventId)
    {
        $tagIds = $this->EventsTag->find('list', array('fields' => array('tag_id'),
            'conditions' => array('EventsTag.event_id' => $eventId), ));

        return $tagIds;
    }

    /**
     * merge two different tags in one.
     *
     * @param int    $tagId
     * @param string $tagSlug
     */
    public function merge($tagId, $tagSlug)
    {
        $tagMerge = $this->find('first', array('conditions' => array('Tag.id' => $tagId)));
        $tagOriginal = $this->find('first', array('conditions' => array('Tag.slug' => $tagSlug)));
        if (empty($tagMerge) || empty($tagOriginal)) {
            return false;
        }

        $events_original = $this->EventsTag->find('all',
            array('conditions' => array('EventsTag.tag_id' => $tagOriginal['Tag']['id']),
            'fields' => 'EventsTag.event_id', ));

        if (!empty($events_original)) {
            $events_original = Set::extract('/EventsTag/event_id', $events_original);
            $this->EventsTag->deleteAll(array('EventsTag.tag_id' => $tagMerge['Tag']['id'],
                'EventsTag.event_id' => $events_original, ));
        }

        $this->EventsTag->updateAll(array('EventsTag.tag_id' => $tagOriginal['Tag']['id']),
            array('EventsTag.tag_id' => $tagMerge['Tag']['id']));

        $this->delete(array('Tag.id' => $tagMerge['Tag']['id']));
        Cache::delete('evento_promoted');

        return true;
    }

    /**
     * bulk delete tags.
     *
     * @param array $ids
     */
    public function bulkDelete($ids)
    {
        $this->deleteAll(array('Tag.id' => $ids));
        Cache::delete('evento_promoted');

        return true;
    }
}
