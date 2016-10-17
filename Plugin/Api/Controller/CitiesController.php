<?php

class CitiesController extends ApiAppController
{
    public $name = 'Cities';
    public $uses = array('City');
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
                'table' => 'countries',
                'alias' => 'Country',
                'type'  => 'left',
                'conditions' => array('Country.id = City.country_id'),
            ),
        );

        $this->fields = array(
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

        if (isset($this->params['url']['country'])) {
            $conditions['Country.id'] = (int) $this->params['url']['country'];
        }

        $cities = $this->City->find('all', array(
            'conditinos' => $conditions,
            'fields'     => $this->fields,
            'joins'      => $this->joins,
            'page'       => $page,
            'limit'      => $limit
        ));

        $apiCities = array();
        foreach ($cities as $city) {
            $apiCities[] = array(
                'name'  => $city['City']['name'],
                'id'    => $city['City']['id'],
                'url'   => Router::url(array(
                    'plugin'     => null,
                    'controller' => 'events',
                    'action'     => 'index',
                    $city['City']['slug'],
                    $city['Country']['slug'],
                    'all-venues',
                    'all-categories',
                    'all-tags'), true),
                'country'       => $this->_getCountry($city),
            );
        }

        $jsonp = isset($this->params['url']['callback'])? $this->params['url']['callback'] : false;
        $data['cities'] = $apiCities;
        $data['pagination'] = $this->_getPagination($this->City);

        $this->set(array(
            'data' => $data,
            'jsonp' => $jsonp
        ));
    }
}