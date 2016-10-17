<?php

class VenuesController extends ApiAppController
{
    public $name = 'Venues';
    public $uses = array('Venue');
    public $components = array('RequestHandler');

    /**
     * Overload beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index');

        $this->joins = array(
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
        );

        $this->fields = array(
            'Venue.name', 'Venue.id', 'Venue.slug', 'Venue.lat', 'Venue.lng',
            'City.name', 'City.id', 'City.slug',
            'Country.name', 'Country.id', 'Country.slug',
        );
    }

    /**
     * Get all categories
     */
    public function index()
    {
        list($page, $limit) = $this->_getUrlParams($this->params['url']);

        $conditions = array();

        if (isset($this->params['url']['city'])) {
            $conditions['City.id'] = (int) $this->params['url']['city'];
        }

        if (isset($this->params['url']['country'])) {
            $conditions['Country.id'] = (int) $this->params['url']['country'];
        }

        $venues = $this->Venue->find('all', array(
            'conditions' => $conditions,
            'fields'     => $this->fields,
            'page'       => $page,
            'limit'      => $limit,
            'joins'      => $this->joins
        ));

        $apiVenues = array();
        foreach ($venues as $venue) {
            $apiVenues[] = array(
                'name'      => $venue['Venue']['name'],
                'id'        => $venue['Venue']['id'],
                'latitude'  => $venue['Venue']['lat'],
                'longitude' => $venue['Venue']['lng'],
                'url'   => Router::url(array(
                    'plugin'     => null,
                    'controller' => 'events',
                    'action'     => 'index',
                    $venue['City']['slug'],
                    $venue['Country']['slug'],
                    'all-venues',
                    'all-categories',
                    'all-tags'), true),
                'city'      => $this->_getCity($venue),
                'country'   => $this->_getCountry($venue)
            );
        }

        $jsonp = isset($this->params['url']['callback'])? $this->params['url']['callback'] : false;
        $data['venues']     = $apiVenues;
        $data['pagination'] = $this->_getPagination($this->Venue);

        $this->set(array(
            'data' => $data,
            'jsonp' => $jsonp
        ));
    }
}