<?php
class SitemapController extends AppController
{
    public $name = 'Sitemap';
    public $uses = array('Event');
    public $helpers = array('Time');
    public $components = array('RequestHandler');

    /**
     * overload beforeFilter to allow access to sitemap.
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index');
    }

    /**
     * create xml sitemap for search engines.
     *
     * @access public
     */
    public function index()
    {
        if (!isset($this->params->params['ext']) || $this->params->params['ext'] != 'xml') {
            throw new NotFoundException();
        }
        Configure::write('debug', 0);
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
            ), );
        $fields = array('Event.created', 'Event.slug', 'Venue.slug', 'City.slug', 'Country.slug');
        $conditions = array('Event.published' => true);
        $this->Event->recursive = -1;
        $events = $this->Event->find('all', array('conditions' => $conditions, 'joins' => $eventsJoins,
            'fields' => $fields, ));
        $this->set('events', $events);
    }
}
