<?php

class CountriesController extends ApiAppController
{
    public $name = 'Countries';
    public $uses = array('Country');
    public $components = array('RequestHandler');

    /**
     * Overload beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index');
    }

    /**
     * Get all categories
     */
    public function index()
    {
        list($page, $limit) = $this->_getUrlParams($this->params['url']);
        $countries = $this->Country->find('all', array(
            'fields' => array('id', 'name', 'slug'),
            'page'   => $page,
            'limit'  => $limit
        ));

        $apiCountries = array();
        foreach ($countries as $country) {
            $apiCountries[] = array(
                'name'  => $country['Country']['name'],
                'id'    => $country['Country']['id'],
                'url'   => Router::url(array(
                    'plugin'     => null,
                    'controller' => 'events',
                    'action'     => 'index',
                    $country['Country']['slug'],
                    'all-cities',
                    'all-venues',
                    'all-categories',
                    'all-tags'), true),
            );
        }

        $jsonp = isset($this->params['url']['callback'])? $this->params['url']['callback'] : false;
        $data['countries']  = $apiCountries;
        $data['pagination'] = $this->_getPagination($this->Country);

        $this->set(array(
            'data' => $data,
            'jsonp' => $jsonp
        ));
    }
}