<?php
class FeedsController extends AppController
{
    public $name = 'Feeds';
    public $uses = array('Event');
    public $helpers = array('Time');
    public $components = array('RequestHandler');

    /**
     * overload beforeFilter to allow access to feeds.
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index');

        return parent::beforeFilter();
    }

    /**
     * create xml feed with all events if no city name is provided.
     * create xml feed with events in city if city name is provided.
     * set debug mode to 0 just in case.
     *
     * @param string $feed
     */
    public function index($feed = 'events')
    {
        Configure::write('debug', 0); // just in case

        $eventsJoins = array(
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
            ), );
        $fields = array('Event.created', 'Event.modified', 'Event.name', 'Event.notes', 'Event.slug',
            'Event.start_date', 'Event.end_date', 'Venue.slug', 'City.slug', 'Country.slug', 'User.username',
            'Category.name', );
        $conditions = array('Event.published' => true);
        if ($feed != 'events') {
            $conditions['City.slug'] = $feed;
        }

        $this->Event->recursive = -1;
        $this->set('events', $this->Event->find('all', array('conditions' => $conditions, 'joins' => $eventsJoins,
            'fields' => $fields, 'limit' => 25, 'order' => 'Event.created DESC', )));
        $this->set('feed', $feed);
    }
}
