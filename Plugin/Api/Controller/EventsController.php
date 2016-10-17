<?php

class EventsController extends ApiAppController
{
    public $name = 'Events';
    public $uses = array('Event');
    public $components = array('RequestHandler');

    private $joins  = array();
    private $fields = array();

    /**
     * Overload beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index', 'view');

        $this->joins = array(
            array(
                'table' => 'venues',
                'alias' => 'Venue',
                'type'  => 'left',
                'conditions' => array('Venue.id = Event.venue_id'),
            ),
            array(
                'table' => 'cities',
                'alias' => 'City',
                'type'  => 'left',
                'conditions' => array('City.id = Venue.city_id'),
            ),
            array(
                'table' => 'countries',
                'alias' => 'Country',
                'type'  => 'left',
                'conditions' => array('Country.id = City.country_id'),
            ),
            array(
                'table' => 'categories',
                'alias' => 'Category',
                'type'  => 'left',
                'conditions' => array('Category.id = Event.category_id'),
            ),
        );

        $this->fields = array(
            'Event.id', 'Event.name', 'Event.notes', 'Event.start_date', 'Event.end_date',
            'Event.logo', 'Event.slug',
            'Category.name', 'Category.id', 'Category.slug',
            'Venue.name', 'Venue.id', 'Venue.slug', 'Venue.lat', 'Venue.lng',
            'City.name', 'City.id', 'City.slug',
            'Country.name', 'Country.id', 'Country.slug',
        );
    }

    /**
     * Get events
     */
    public function index() {
        list($page, $limit) = $this->_getUrlParams($this->params['url']);

        $conditions = array(
            'start_date >=' => date('Y-m-d 00:00:00'),
            'published'     => 1
        );

        if (isset($this->params['url']['category'])) {
            $conditions['category_id'] = (int) $this->params['url']['category'];
        }

        if (isset($this->params['url']['date'])) {
            $conditions['start_date >='] = date('Y-m-d 00:00:00', strtotime($this->params['url']['date']));
            $conditions['start_date <='] = date('Y-m-d 23:59:59', strtotime($this->params['url']['date']));
        }

        if (isset($this->params['url']['venue'])) {
            $conditions['Venue.id'] = (int) $this->params['url']['venue'];
        }

        if (isset($this->params['url']['city'])) {
            $conditions['City.id'] = (int) $this->params['url']['city'];
        }

        if (isset($this->params['url']['country'])) {
            $conditions['Country.id'] = (int) $this->params['url']['country'];
        }

        $events = $this->Event->find('all', array(
            'conditions' => $conditions,
            'joins'      => $this->joins,
            'limit'      => $limit,
            'page'       => $page,
            'recursive'  => -1,
            'fields'     => $this->fields
        ));

        $apiEvents = array();
        foreach ($events as $event) {
            $apiEvents[] = $this->_getApiEvent($event);
        }

        $jsonp = isset($this->params['url']['callback'])? $this->params['url']['callback'] : false;
        $data['events'] = $apiEvents;
        $data['pagination'] = $this->_getPagination($this->Event, array('published'=>1));

        $this->set(array(
            'events' => $data,
            'jsonp'  => $jsonp
        ));
    }

    /**
     * Get event by id
     *
     * @param int $id
     */
    public function view($id = null)
    {
        $event = $this->Event->find('first', array(
            'conditions' => array('Event.id' => $id),
            'joins'     => $this->joins,
            'recursive' => -1,
            'fields'    => $this->fields,
        ));

        $apiEvent = $this->_getApiEvent($event);
        $jsonp = isset($this->params['url']['callback'])? $this->params['url']['callback'] : false;

        $this->set(array(
            'event' => $apiEvent,
            'jsonp' => $jsonp
        ));
    }

    /**
     * Get event data
     *
     * @param array $event
     * @return array
     */
    private function _getApiEvent($event)
    {
        if (!isset($event['Event'])) {
            return array();
        }

        $logo = null;
        if($event['Event']['logo'] != '') {
            $logo = Router::url('/', true) . IMAGES_URL. 'logos/' . $event['Event']['logo'];
        }

        return array(
            'id'            => $event['Event']['id'],
            'name'          => $event['Event']['name'],
            'start_date'    => $event['Event']['start_date'],
            'end_date'      => $event['Event']['end_date'],
            'description'   => $event['Event']['notes'],
            'image'         => $logo,
            'url'           => Router::url(array(
                'plugin'        => null,
                'controller'    => 'events',
                'action'        => 'view',
                $event['Country']['slug'],
                $event['City']['slug'],
                $event['Venue']['slug'],
                $event['Event']['slug']),true),
            'category'      => $this->_getCategory($event),
            'venue'         => $this->_getVenue($event),
            'city'          => $this->_getCity($event),
            'country'       => $this->_getCountry($event),
        );
    }

}
