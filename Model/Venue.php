<?php
class Venue extends AppModel
{
    public $name = 'Venue';
    public $useTable = 'venues';
    public $use = array('Venue');
    public $actsAs = array('Containable');
    public $recursive = -1;

    /*
     * validation rules
     */

    public $validate = array(
        'name' => array(
            'rule' => array('minLength', 2),
            'message' => 'Name must be at least 2 characters long',
        ),
        'address' => array(
            'rule' => array('minLength', 2),
            'message' => 'Address must be at least 2 characters long',
        ),
        'city_id' => array(
            'rule' => array('numeric'),
            'required' => true,
            'message' => 'Please select a country and enter a city name.',
        ),
        'lat' => array(
            'rule' => 'notBlank',
            'required' => true,
        ),
        'lng' => array(
            'rule' => 'notBlank',
            'required' => true,
        ),
    );

    /*
     * model associations
     */

    public $hasMany = array('Event' => array('className' => 'Event'));
    public $belongsTo = array('City');

    /**
     * overload the beforeValidate method
     * to make sure the country and city values are properly set.
     *
     * @param array $options
     */
    public function beforeValidate($options = array())
    {
        if (Configure::read('evento_settings.country_id')) {
            $this->data['City']['country_id'] = Configure::read('evento_settings.country_id');
        }
        if (Configure::read('evento_settings.city_name')) {
            $this->data['City']['name'] = Configure::read('evento_settings.city_name');
        }
        if (isset($this->data['City']['name'])) {
            $this->data['Venue']['city_id'] = $this->City->field('id', array('City.name' => $this->data['City']['name'], 'City.country_id' => $this->data['City']['country_id']));

            if (!$this->data['Venue']['city_id']) {
                if ($this->City->save($this->data)) {
                    $this->data['Venue']['city_id'] = $this->City->getInsertID();
                }
            }
        }

        return parent::beforeValidate($options);
    }

    /**
     * create the venue slug before saving to the databse.
     *
     * @param array $options
     */
    public function beforeSave($options = array())
    {
        $slug = Inflector::slug(strtolower($this->data['Venue']['name']), '-');
        if (!$slug) {
            $slug = urlencode($this->data['Venue']['name']);
        }
        $venues = $this->find('all', array('conditions' => array('Venue.slug like' => $slug.'%')));
        $slugs = Set::extract('/Venue/slug', $venues);
        if (!empty($venues)) {
            $n = 0;
            $tmpSlug = $slug;
            while (in_array($tmpSlug, $slugs)) {
                $n++;
                $tmpSlug = $slug.'-'.$n;
            }
            $slug = $tmpSlug;
        }
        $this->data['Venue']['slug'] = $slug;

        return parent::beforeSave($options);
    }

    /**
     * Delete caches after saving a venue.
     *
     * @param $created bool
     */
    public function afterSave($created, $options = array())
    {
        Cache::delete('evento_active_cities');
        Cache::delete('evento_promoted');

        return parent::afterSave($created, $options);
    }

    /**
     * merge two different venues in one.
     *
     * @param int    $venueMergeId
     * @param string $venueOriginalId
     */
    public function merge($venueMergeId, $venueOriginalId)
    {
        $this->id = $venueMergeId;
        $mergeExists = $this->exists();
        $this->id = $venueOriginalId;
        $originalExists = $this->exists();
        if (!$originalExists || !$mergeExists) {
            return false;
        }
        $this->Event->updateAll(array('Event.venue_id' => $venueOriginalId),
            array('Event.venue_id' => $venueMergeId));
        $this->delete($venueMergeId);

        return true;
    }
}
