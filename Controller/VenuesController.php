<?php
class VenuesController extends AppController
{
    /*
     * Controller name
     *
     * @var string
     * @access public
     */

    public $name = 'Venues';
    public $uses = array('Venue');
    public $helpers = array('Html', 'Form', 'Js');
    public $paginate = array('limit' => 30, 'order' => array('Venue.name' => 'asc'));

    /**
     * Overload beforeFilter allow display action.
     *
     * @access public
     */
    public function beforeFilter()
    {
        $this->Auth->allow('autoComplete');

        return parent::beforeFilter();
    }

    /**
     * autocomplete for venues in event form.
     */
    public function autoComplete()
    {
        $conditions = array('Venue.name LIKE' => $this->request->data['Venue']['name'].'%');
        $this->Venue->contain(array('City' => array('Country.name')));
        $this->set('venues', $this->Venue->find('all', array(
            'conditions' => $conditions, 'limit' => 10, )));
        $this->layout = 'ajax';
    }

    /**
     * set venues variable to display the tags table to admin.
     */
    public function admin_index()
    {
        $this->paginate['joins'] = array(
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
        $this->paginate['fields'] = array('Country.name', 'City.name', 'Venue.id', 'Venue.name', 'Venue.address');
        $this->Session->delete('Search.term');
        $this->set('venues', $this->paginate('Venue'));
        $this->set('title_for_layout', __('Manage venues'));
    }

    /**
     * edit a venue.
     *
     * @param int $id
     */
    public function admin_edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException();
        }
        $this->Venue->contain(array('City'));
        $venue = $this->Venue->find('first', array('conditions' => array('Venue.id' => $id)));
        if (empty($venue)) {
            throw new NotFoundException();
        }

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        $action = 'index';
        if ($this->Session->read('Search.term')) {
            $action = 'search';
        }

        if (!empty($this->request->data)) {
            if ($this->Venue->save($this->request->data)) {
                $this->redirect(array('action' => $action, 'page' => $page));
            }
        } else {
            $this->request->data = $venue;
        }

        if (Configure::read('evento_settings.country_id')) {
            $this->set('country_id', Configure::read('evento_settings.country_id'));
            $this->set('country_name', $this->Venue->City->Country->field('name',
                array('Country.id' => Configure::read('evento_settings.country_id'))));
        } else {
            $this->set('countries', $this->Venue->City->Country->find('countrylist'));
        }

        if (Configure::read('evento_settings.city_name')) {
            $this->set('default_city', Configure::read('evento_settings.city_name'));
        }
        $this->set('page', $page);
        $this->set('title_for_layout', __('Edit venue'));
    }

    /**
     * delete venue and all its events with the user confirmation.
     *
     * @param int  $id
     * @param bool $confirmation
     */
    public function admin_delete($id, $confirmation = false)
    {
        $events = $this->Venue->Event->find('all', array('conditions' => array('Event.venue_id' => $id),
            'fields' => array('id'), ));

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;

        if (!$events || $confirmation) {
            $this->Venue->Event->bulkDelete(Set::extract('/Event/id', $events));
            $this->Venue->delete($id, true);
            $conditions = null;
            $action = 'index';
            if ($this->Session->read('Search.term')) {
                $action = 'search';
                $term = $this->Session->read('Search.term');
                $conditions = array('Venue.name like' => '%'.$term['Search']['term'].'%');
            }
            if ($page) {
                $count = $this->Venue->find('count', array('conditions' => $conditions));
                $lastPage = ceil($count / $this->paginate['limit']);
                if ($page > $lastPage) {
                    $page = $lastPage;
                }
            }
            $this->redirect(array('action' => $action, 'page' => $page));
        }
        $this->set('page', $page);
        $this->set('events', count($events));
        $this->set('id', $id);
    }

    /**
     * admin can add new venues.
     */
    public function admin_add()
    {
        if (!empty($this->request->data)) {
            if ($this->Venue->save($this->request->data)) {
                $this->redirect(array('action' => 'index'));
            }
        }
        if (Configure::read('evento_settings.country_id')) {
            $this->set('country_id', Configure::read('evento_settings.country_id'));
            $this->set('country_name', $this->Venue->City->Country->field('name',
                array('Country.id' => Configure::read('evento_settings.country_id'))));
        } else {
            $this->set('countries', $this->Venue->City->Country->find('countrylist'));
        }

        if (Configure::read('evento_settings.city_name')) {
            $this->set('default_city', Configure::read('evento_settings.city_name'));
        }
        $this->set('title_for_layout', __('Add venue'));
    }

    /**
     * merge two different venues in one.
     *
     * @param int    $venueMergeId
     * @param string $venueOriginalId
     */
    public function admin_merge($venueMergeId, $venueOriginalId = null)
    {
        if ($venueOriginalId === null) {
            $this->paginate['contain'] = array('City' => array('Country'));
            $this->set('venues', $this->paginate('Venue', array('Venue.id <>' => $venueMergeId)));
            $this->set('merge_id', $venueMergeId);
            $this->set('title_for_layout', __('Merge venues'));
        } else {
            $this->Venue->merge($venueMergeId, $venueOriginalId);
            $this->redirect(array('action' => 'index'));
        }
    }

    /**
     * search venues.
     */
    public function admin_search()
    {
        $data = $this->Session->read('Search.term');
        if (!empty($this->request->data) || !empty($data)) {
            if ($this->request->data) {
                $this->Session->write('Search.term', $this->request->data);
            } else {
                $this->request->data = $data;
            }

            $conditions = array('or' => array(
                'Venue.name like' => '%'.$this->request->data['Search']['term'].'%',
                'Venue.address like' => '%'.$this->request->data['Search']['term'].'%',
            ));
            $this->paginate['joins'] = array(
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
            $this->paginate['fields'] = array('Country.name', 'City.name', 'Venue.id', 'Venue.name',
                'Venue.address', );
            $this->paginate['order'] = 'Venue.created DESC';
            $this->set('venues', $this->paginate('Venue', $conditions));
        }
        $this->set('title_for_layout', __('Search'));
        $this->render('admin_index');
    }
}
