<?php
class City extends AppModel
{
    public $name = 'City';
    public $useTable = 'cities';
    public $recursive = -1;
    public $actsAs = array('Containable');

    /*
     * validation
     */

    public $validate = array(
        'name' => array(
            'length' => array(
                'rule' => array('minLength', 2),
                'message' => 'Name must be at least 2 characters long',
                'required' => true,
                'allowEmpty' => false, ),
            'notExists' => array(
                'rule' => 'notExists',
                'message' => 'This city already exists',
            ), ),
        'country_id' => array(
            'rule' => array('numeric'),
            'message' => 'You must select a country',
            'required' => true,
            'allowEmpty' => false, ),
    );

    /*
     * model associations
     */

    public $belongsTo = array('Country' => array('className' => 'Country'));
    public $hasMany = array(
        'Venue' => array('className' => 'Venue'),
        'User' => array('className' => 'User'),
        );

    /**
     * enable custom find methods.
     */
    public $findMethods = array('citylist' =>  true, 'active' => true);

    /**
     * ucfirst city name before save.
     *
     * @param array $options
     */
    public function beforeSave($options = array())
    {
        $this->data['City']['name'] = ucfirst($this->data['City']['name']);

        return parent::beforeSave($options);
    }

    /**
     * Delete caches after saving a city.
     *
     * @param bool $created
     */
    public function afterSave($created, $options = array())
    {
        Cache::delete('evento_active_cities');
        Cache::delete('evento_promoted');

        return parent::afterSave($created, $options);
    }

    /**
     * Custom validation to check if a city has already been added.
     *
     * @param string $cityName
     */
    public function notExists($cityName)
    {
        $this->data['City']['slug'] = Inflector::slug(strtolower($this->data['City']['name']), '-');
        if (!$this->data['City']['slug']) {
            $this->data['City']['slug'] = urlencode($this->data['City']['name']);
        }
        $this->recursive = -1;
        $city = $this->find('first', array('conditions' => array('slug' => $this->data['City']['slug'],
            'country_id' => $this->data['City']['country_id'], )));

        return empty($city);
    }

    /**
     * merge cities.
     *
     * @param int    $cityId
     * @param string $citySlug
     */
    public function merge($cityId, $citySlug)
    {
        $cityOriginal = $this->find('first', array('conditions' => array('City.slug' => $citySlug,
            'City.id <>' => $cityId, )));
        $cityMerge = $this->find('first', array('conditions' => array('City.id' => $cityId)));
        if (empty($cityOriginal) || empty($cityMerge)) {
            return false;
        }

        $this->Venue->updateAll(array('Venue.city_id' => $cityOriginal['City']['id']),
            array('Venue.city_id' => $cityMerge['City']['id']));
        $this->delete($cityMerge['City']['id']);

        return true;
    }

    /**
     *	Get all cities with events with custom find method.
     *
     * @param string $state
     * @param array  $query
     * @param array  $results
     *
     * @return array
     */
    public function _findActive($state, $query, $results = array())
    {
        if ($state == 'before') {
            return array_merge($query, array('joins' => array(
                array(
                    'table' => 'venues',
                    'alias' => 'Venue',
                    'type' => 'left',
                    'conditions' => array('Venue.city_id = City.id'),
                ),
                array(
                    'table' => 'countries',
                    'alias' => 'Country',
                    'type' => 'left',
                    'conditions' => array('Country.id = City.country_id'),
                ),
                array(
                    'table' => 'events',
                    'alias' => 'Event',
                    'type' => 'left',
                    'conditions' => array('Event.venue_id = Venue.id'),
                ), ),
                'fields' => array('Country.name', 'Country.id', 'Country.slug', 'City.name',
                    'City.slug', 'count(Event.id)', ),
                'group' => 'City.id HAVING count(Event.id)>0', ));
        } else {
            $activeCities = array();
            $tmp = array();
            foreach ($results as $c) {
                $country['name'] = $c['Country']['name'];
                $country['slug'] = $c['Country']['slug'];
                $tmp[$c['Country']['id']]['City'][] = $c['City'];
                $tmp[$c['Country']['id']]['Country'] = $country;
            }
            uasort($tmp, array($this, '_countryCompare'));
            foreach ($tmp as $k => $v) {
                usort($v['City'], array($this, '_cityCompare'));
                $activeCities[$k] = $v;
            }
            ksort($activeCities);

            return $activeCities;
        }
    }

    /**
     * return a lis tof cities with their country name ready to use in form selects.
     *
     * @param string $state
     * @param array  $query
     * @param array  $results
     *
     * @return array
     */
    public function _findCitylist($state, $query, $results = array())
    {
        if ($state == 'before') {
            return array_merge($query, array('joins' => array(
                array(
                    'table' => 'venues',
                    'alias' => 'Venue',
                    'type' => 'left',
                    'conditions' => array('Venue.city_id = City.id'),
                ),
                array(
                    'table' => 'countries',
                    'alias' => 'Country',
                    'type' => 'left',
                    'conditions' => array('Country.id = City.country_id'),
                ),
                array(
                    'table' => 'events',
                    'alias' => 'Event',
                    'type' => 'left',
                    'conditions' => array('Event.venue_id = Venue.id'),
                ), ),
                'fields' => array('Country.name', 'City.name', 'City.id'),
                'group' => 'City.id HAVING count(Event.id)>0', ));
        } else {
            $cities = array();
            $tmp = array();
            foreach ($results as $city) {
                $tmp[__d('countries', $city['Country']['name'])][$city['City']['id']] = $city['City']['name'];
            }
            foreach ($tmp as $k => $v) {
                asort($v);
                $cities[$k] = $v;
            }
            ksort($cities);

            return $cities;
        }
    }

    /**
     * function to sort cities by name.
     *
     * @param array $cityA
     * @param array $cityB
     */
    private function _cityCompare($cityA, $cityB)
    {
        return $cityA['name'] > $cityB['name'];
    }

    /**
     * sort cities array by country.
     *
     * @param array $countryA
     * @param array $countryB
     */
    private function _countryCompare($countryA, $countryB)
    {
        return __d('countries', $countryA['Country']['name']) > __d('countries', $countryB['Country']['name']);
    }
}
