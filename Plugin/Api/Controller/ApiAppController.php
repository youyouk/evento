<?php

class ApiAppController extends AppController
{

    protected $paginationLimit = 10;
    protected $page            = 1;
    private $apiIsEnabled           = true;

    public function beforeFilter()
    {
        if ($this->apiIsEnabled != true) {
            throw new NotFoundException();
        }
        return;
    }

    /**
     * Get the event's category data
     *
     * @param array $event
     * @return array
     */
    protected function _getCategory($event)
    {
        return array(
            'name'  => $event['Category']['name'],
            'id'    => $event['Category']['id'],
            'url'   => Router::url(array(
                'plugin'     => null,
                'controller' => 'events',
                'action'     =>'index',
                'all-countries',
                'all-cities',
                'all-venues',
                $event['Category']['slug'], 'all-tags'), true),
        );
    }

    /**
     * Get event's venue data
     *
     * @param array $event
     * @return array
     */
    protected function _getVenue($event)
    {
        return array(
            'name'      => $event['Venue']['name'],
            'id'        => $event['Venue']['id'],
            'latitude'  => $event['Venue']['lat'],
            'longitude' => $event['Venue']['lng'],
            'url' => Router::url(array(
                'plugin'     => null,
                'controller' => 'events',
                'action'     => 'index',
                'all-countries',
                'all-cities',
                $event['Venue']['slug'],
                'all-categories',
                'all-tags'), true),
        );
    }

    /**
     * Get event's city data
     *
     * @param array $event
     * @return $event
     */
    protected function _getCity($event)
    {
        return array(
            'name'  => $event['City']['name'],
            'id'    => $event['City']['id'],
            'url'   => Router::url(array(
                'plugin'     => null,
                'controller' => 'events',
                'action'     => 'index',
                'all-countries',
                $event['City']['slug'],
                'all-venues',
                'all-categories',
                'all-tags'), true)
        );
    }

    /**
     * Get event's country data
     *
     * @param array $event
     * @return array
     */
    protected function _getCountry($event)
    {
        return array(
            'name'  => $event['Country']['name'],
            'id'    => $event['Country']['id'],
            'url'   => Router::url(array(
                'plugin'     => null,
                'controller' => 'events',
                'action'     => 'index',
                $event['Country']['slug'],
                'all-cities',
                'all-venues',
                'all-categories',
                'all-tags'), true),
        );
    }

    /**
     * Get api url parameters
     *
     * @param array $params
     * @return array
     */
    protected function _getUrlParams($params)
    {
        $params = array(
            $this->page,                // page
            $this->paginationLimit,     // limit
        );

        if (isset($this->params['url']['page'])) {
            $params[0]  = (int) $this->params['url']['page'];
            $this->page = $params[0];
        }

        if (isset($this->params['url']['limit'])) {
            $params[1] = (int) $this->params['url']['limit'];
            $this->paginationLimit = $params[1];
        }

        return $params;
    }

    /**
     * Get pagination data
     *
     * @return array
    */
    protected function _getPagination($model, $conditions = array())
    {
        $totalPages = $model->find('count', array('conditions' => $conditions));
        $totalPages = ceil($totalPages / $this->paginationLimit);

        return array(
            'current_page'  => $this->page,
            'total_pages'   => $totalPages,
            'limit'         => $this->paginationLimit
        );
    }

}
