<?php
class Country extends AppModel
{
    public $name = 'Country';
    public $actsAs = array('Containable');
    public $useTable = 'countries';

    /*
     * model associations
     */

    public $hasMany = array('City' => array('className' => 'City'));

    /**
     * enable custom find methods.
     */
    public $findMethods = array('countrylist' =>  true);

    /**
     * return a list with the translated names of the countries.
     *
     * @param string $state
     * @param array  $query
     * @param array  $results
     *
     * @return array
     */
    public function _findCountryList($state, $query, $results = array())
    {
        if ($state == 'before') {
            return array_merge($query, array('fields' => array('Country.id', 'Country.name'),
                'recursive' => -1, ));
        } else {
            $countryList = array();
            foreach ($results as $country) {
                $countryList[$country['Country']['id']] = __d('countries', $country['Country']['name']);
            }
            asort($countryList);

            return $countryList;
        }
    }
}
