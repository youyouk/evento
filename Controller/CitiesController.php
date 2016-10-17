<?php
class CitiesController extends AppController
{
    public $name = 'Cities';
    public $uses = array('City');
    public $paginate = array('limit' => 30, 'order' => array('City.name' => 'asc'));

    /**
     * Function for the Ajax autocompleter used for the city names.
     * It is used in the add form for the Events model.
     *
     * @access public
     */
    public function autoComplete()
    {
        $this->layout = 'ajax';
        $conditions = array('City.name LIKE' => $this->request->data['City']['name'].'%');
        if (isset($this->request->data['City']['country_id'])) {
            $conditions['country_id'] = $this->request->data['City']['country_id'];
        }
        $this->set('cities', $this->City->find('all', array(
            'conditions' => $conditions, 'fields' => array('name'), 'limit' => 10, )));
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////  ADMIN
    ////////////////////////////////////////////////////////////////////

    /**
     * admin cities index.
     */
    public function admin_index()
    {
        $this->paginate['contain'] = array('Country.name');
        $this->paginate['fields'] = array('City.id', 'City.name', 'Country.name');
        $this->Session->delete('Search.term');
        $this->set('cities', $this->paginate('City'));
    }

    /**
     * delete city.
     *
     * @param int $id
     */
    public function admin_delete($id)
    {
        $venues = $this->City->Venue->find('count', array('conditions' => array('Venue.city_id' => $id)));
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;

        if (!$venues) {
            $this->City->User->updateAll(array('User.city_id' => null), array('User.city_id' => $id));
            $this->City->delete($id);
            $conditions = null;
            $action = 'index';
            if ($this->Session->read('Search.term')) {
                $action = 'search';
                $term = $this->Session->read('Search.term');
                $conditions = array('City.name like' => '%'.$this->request->data['Search']['city'].'%');
            }
            if ($page) {
                $count = $this->City->find('count', array('conditions' => $conditions));
                $lastPage = ceil($count / $this->paginate['limit']);
                if ($page > $lastPage) {
                    $page = $lastPage;
                }
            }
            $this->redirect(array('action' => $action, 'page' => $page));
        }
        $this->set('page', $page);
        $this->set('id', $id);
    }

    /**
     * edit city.
     *
     * @param int $cityId
     */
    public function admin_edit($cityId)
    {
        $city = $this->City->find('first', array('conditions' => array('City.id' => $cityId),
            'fields' => array('City.id', 'City.country_id', 'City.name'), ));
        if (empty($city)) {
            throw new NotFoundException();
        }

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;

        if (!empty($this->request->data)) {
            $action = 'index';
            if ($this->Session->read('Search.term')) {
                $action = 'search';
            }
            if ($this->City->save($this->request->data)) {
                $this->redirect(array('action' => $action, 'page' => $page));
            }
        } else {
            $this->request->data = $city;
        }
        $this->set('page', $page);
        $this->set('countries', $this->City->Country->find('countrylist'));
    }

    /**
     * merge cities.
     *
     * @param int    $cityId
     * @param string $citySlug
     *
     * @see City->merge()
     */
    public function admin_merge($cityId, $citySlug)
    {
        $this->City->merge($cityId, $citySlug);
        $this->redirect(array('action' => 'index'));
    }

    /**
     * allow admin to search cities.
     */
    public function admin_search()
    {
        $this->set('title_for_layout', __('Search'));
        $data = $this->Session->read('Search.term');
        if (!empty($this->request->data) || !empty($data)) {
            if ($this->request->data) {
                $this->Session->write('Search.term', $this->request->data);
            } else {
                $this->request->data = $data;
            }
            $conditions = array('City.name like' => '%'.$this->request->data['Search']['city'].'%');
            $this->paginate['contain'] = array('Country.name');
            $this->set('cities', $this->paginate('City', $conditions));
        }
        $this->render('admin_index');
    }
}
