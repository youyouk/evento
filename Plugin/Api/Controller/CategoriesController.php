<?php

class CategoriesController extends ApiAppController
{
    public $name = 'Categories';
    public $uses = array('Category');
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
        $categories = $this->Category->find('all', array(
            'fields'    => array('id', 'name', 'slug'),
            'page'      => $page,
            'limit'     => $limit
        ));

        foreach ($categories as $category) {
            $apiCategories[] = array(
                'name'  => $category['Category']['name'],
                'id'    => $category['Category']['id'],
                'url'   => Router::url(array(
                    'plugin'     => null,
                    'controller' => 'events',
                    'action'     =>'index',
                    'all-countries',
                    'all-cities',
                    'all-venues',
                    $category['Category']['slug'], 'all-tags'), true),
            );
        }

        $jsonp = isset($this->params['url']['callback'])? $this->params['url']['callback'] : false;
        $data['categories'] = $apiCategories;
        $data['pagination'] = $this->_getPagination($this->Category);

        $this->set(array(
            'data' => $data,
            'jsonp' => $jsonp
        ));
    }
}