<?php
App::uses('CakeEmail', 'Network/Email');

class EventsController extends AppController
{
    public $name = 'Events';
    public $uses = array('Event', 'Tag', 'EventsTag', 'Country', 'City', 'User', 'Settings', 'Category');
    public $helpers = array('Html', 'Form', 'Calendar', 'Time', 'Text', 'Timeformat', 'Bookmark', 'Js');
    public $components = array('RequestHandler', 'Calendar', 'PaypalEvento', 'RecaptchaEvento');
    public $paginate = array('limit' => 15, 'order' => array('Event.start_date' => 'ASC', 'Event.id' => 'DESC'));

    /**
     * Overload beforeFilter to set some permissions.
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('index', 'view', 'search', 'ical');
    }

    /**
     * display a list of events depending on city, tag or date.
     *
     * @param string country
     * @param string $city
     * @param string $venue
     * @param string $category
     * @param string $tag
     * @param int    $year
     * @param string $month
     * @param int    $day
     **/
    public function index($country = null, $city = null, $venue = null,
        $category = null, $tag = null, $year = null, $month = null, $day = null)
    {
        $feed = 'events';
        $data = null;
        $cityId = null;
        $venueId = null;
        $categoryId = null;
        $countryId = null;
        $tagEvents = null;
        $monthNum = date('n');

        if ($country === null) {
            $country = __d('url', 'all-countries');
        }
        if ($city === null) {
            $city = __d('url', 'all-cities');
        }
        if ($venue === null) {
            $venue = __d('url', 'all-venues');
        }
        if ($category == null) {
            $category = __d('url', 'all-categories');
        }
        if ($tag == null) {
            $tag = __d('url', 'all-tags');
        }

        /*
         * set basic conditions to query events.
         * we don't want to show events marked as unpublished
         */

        $conditions = array();
        $conditions['Event.published'] = 1;
        $conditions['Event.promoted'] = 0;

        /*
         *	if country is set and is valid set variables and conditions to query events
         */

        if ($country != __d('url', 'all-countries')) {
            $currentCountry = $this->Country->find('first', array('conditions' => array('Country.slug' => $country)),
                array('Country.name', 'Country.id'), null, -1);
            if (empty($currentCountry)) {
                throw new NotFoundException();
            }
            $countryId = $currentCountry['Country']['id'];
            $this->set('country_name', $currentCountry['Country']['name']);
            $conditions['Country.id'] = $countryId;

            // set title for events in this country
            $this->set('title_for_layout', sprintf(__('Events in %s'),
                __d('countries', $currentCountry['Country']['name'])));

            /*
             * if a city is set in the url get it from the database
             * set basic variables and conditions for events query.
             */

            if ($city != __d('url', 'all-cities')) {
                $cityConditions['City.slug'] = $city;
                if ($countryId) {
                    $cityConditions['City.country_id'] = $countryId;
                }
                $currentCity = $this->City->find('first', array('conditions' => array($cityConditions),
                'fields' => array('City.id', 'City.name'), ));
                if ($currentCity) {
                    $cityId = $currentCity['City']['id'];
                    $feed = $city;
                    $conditions['Venue.city_id'] = $cityId;
                    $this->set('city_name', $currentCity['City']['name']);

                    // set title for events in this city
                    $this->set('title_for_layout', sprintf(__('Events in %s'),
                        $currentCity['City']['name'].', '.__d('countries', $currentCountry['Country']['name'])));

                    /*
                     * if a venue is set in the url get it from the database
                     * to set variables and conditions for the events query
                     */
                    if ($venue != __d('url', 'all-venues')) {
                        $venueConditions['Venue.slug'] = $venue;
                        $currentVenue = $this->Event->Venue->find('first', array('conditions' => $venueConditions,
                            'fields' => array('Venue.id', 'Venue.name'), ));
                        if (!empty($currentVenue)) {
                            $venueId = $currentVenue['Venue']['id'];
                            $conditions['Venue.id'] = $venueId;
                            $this->set('venue_name', $currentVenue['Venue']['name']);
                        } else {
                            throw new NotFoundException();
                        }
                    }
                } else {
                    throw new NotFoundException();
                }
            }
        } else {
            if ($city != __d('url', 'all-cities') || $venue != __d('url', 'all-venues')) {
                throw new NotFoundException();
            }
        }

        /*
         * if category is set check if it exists and set variables and conditions for the events query
         */

        if (($categories = Cache::read('evento_categories')) === false) {
            $categories = $this->Category->find('all', array('order' => 'name ASC'));
            Cache::write('evento_categories', $categories);
        }

        if ($category != __d('url', 'all-categories')) {
            $currentCategory = Set::extract('/Category[slug='.$category.']', $categories);
            if (empty($currentCategory)) {
                throw new NotFoundException();
            }
            $currentCategory = array_shift($currentCategory);
            $categoryId = $currentCategory['Category']['id'];
            $conditions['Event.category_id'] = $categoryId;
            $this->set('category_name', $currentCategory['Category']['name']);

            // set title for layout
            if (isset($currentCountry['Country']['name'])) {
                $countryTitle = __d('countries', $currentCountry['Country']['name']);
                if (isset($currentCity['City']['name'])) {
                    $countryTitle = $currentCity['City']['name'].', '
                .$countryTitle;
                }

                $this->set('title_for_layout', sprintf(__('%1$s events in %2$s'),
                    $currentCategory['Category']['name'], $countryTitle));
            } else {
                $this->set('title_for_layout', sprintf(__('%s events'), $currentCategory['Category']['name']));
            }
        }

        /*
         * if a tag is set in the url look for it in the database and set basic variables
         * and conditions to query events
         */

        if ($tag != __d('url', 'all-tags')) {
            $currentTag = $this->Tag->find('first', array('conditions' => array('Tag.slug' => $tag)));
            if (empty($currentTag)) {
                throw new NotFoundException();
            }
            $conditions['Event.id'] = $this->Tag->getTagEventIds($tag);
            if (!$conditions['Event.id']) {
                throw new NotFoundException();
            }
            $tagEvents = $conditions['Event.id'];
            $this->set('tag_name', $currentTag['Tag']['name']);
        }

        /*
         * if year is not null check if it is a valid year and trowh error 404 if it isn't
         */

        if ($year !== null) {
            if (!($year = $this->Calendar->getYear($year))) {
                throw new NotFoundException();
            } else {
                $conditions['YEAR(end_date) >='] = $year;
                $conditions['YEAR(start_date) <='] = $year;
            }

            /*
             * if month is not null check if it is valid and set conditions to query events
             */
            if ($month !== null) {
                if (!($monthNum = $this->Calendar->getMonth($month))) {
                    throw new NotFoundException();
                }

                /*
                 * if day is set get conditions to query for events
                 */
                if ($day !== null) {
                    if (!($dayConditions = $this->Calendar->getDayConditions($day))) {
                        throw new NotFoundException();
                    } else {
                        $conditions = array_merge($conditions, $dayConditions);

                        // set title for layout
                        $monthTitle =  __d('cake', date('F', strtotime($day.'-'.$monthNum.'-'.$year)));
                        $dayTitle = date('j', strtotime($day.'-'.$monthNum.'-'.$year));

                        if (isset($currentCountry['Country']['name'])) {
                            $countryTitle = __d('countries', $currentCountry['Country']['name']);
                            if (isset($currentCity['City']['name'])) {
                                $countryTitle = $currentCity['City']['name']
                                .', '.$countryTitle;
                            }
                            if (isset($currentCategory['Category']['name'])) {
                                $this->set('title_for_layout', sprintf(__('%1$s events in %2$s on %3$s %4$s'),
                                    $currentCategory['Category']['name'], $countryTitle, $monthTitle, $dayTitle));
                            } else {
                                $this->set('title_for_layout', sprintf(__('events in %1$s on %2$s %3$s'),
                                $countryTitle, $monthTitle, $dayTitle));
                            }
                        } else {
                            if (isset($currentCategory['Category']['name'])) {
                                $this->set('title_for_layout', sprintf(__('%1$s events on %2$s %3$s'),
                                $currentCategory['Category']['name'], $monthTitle, $dayTitle));
                            } else {
                                $this->set('title_for_layout', sprintf(__('events on %1$s %2$s'), $monthTitle, $dayTitle));
                            }
                        }
                    }
                } else {
                    $lastDay = date('t', strtotime($year.'-'.sprintf('%02d', $monthNum)));
                    $conditions['OR'] = array(
                        'DATE_FORMAT(end_date, "%Y-%m")' => $year.'-'.sprintf('%02d', $monthNum),
                        'DATE_FORMAT(start_date, "%Y-%m")' => $year.'-'.sprintf('%02d', $monthNum),
                        'AND' => array(
                            'start_date <=' => date('Y-m-d', strtotime($year.'-'.sprintf('%02d', $monthNum).'-01')),
                            'end_date >=' => date('Y-m-d', strtotime($year.'-'.sprintf('%02d', $monthNum).'-'.$lastDay)),
                        ),
                    );
                }
            }
        } else {
            $conditions['end_date >='] = date('Y-m-d');
        }

        /*
         * paginate events using according to conditions
         */
        $this->paginate['contain'] = array('Tag');
        $this->paginate['fields'] = array('Event.id', 'Event.name', 'Event.slug', 'Event.start_date',
            'Event.notes', 'Event.promoted', 'Event.logo', 'Venue.name', 'Venue.slug', 'City.name', 'City.slug',
            'Country.name', 'Country.slug', 'Category.name',
            'Category.slug', );

        $this->paginate['joins'] = array(
                array(
                    'table' => 'venues',
                    'alias' => 'Venue',
                    'type' => 'left',
                    'conditions' => array('Venue.id = Event.venue_id'),
                ),
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
                ),
                array(
                    'table' => 'categories',
                    'alias' => 'Category',
                    'type' => 'left',
                    'conditions' => array('Category.id = Event.category_id'),
                ),
            );

        $promotedConditions = array(
            'Event.promoted' => 1,
            'Event.published' => 1,
            'Event.end_date >=' => date('Y-m-d 00:00:00')
        );

        if ($categoryId != null) {
            $promotedConditions = array(
                'Event.promoted_in_category' => 1,
                'Event.published' => 1,
                'Event.end_date >=' => date('Y-m-d 00:00:00'),
                'Event.category_id' => $categoryId
            );
        }

        $this->Event->contain(array('Tag'));
        $promoted = $this->Event->find('all',
            array('conditions' => $promotedConditions,
            'joins' => $this->paginate['joins'], 'fields' => $this->paginate['fields']));

        if (Configure::read('evento_settings.city_name')) {
            $activeCities = false;
        } elseif (($activeCities = Cache::read('evento_active_cities')) === false) {
            $activeCities = $this->City->find('active');
            Cache::write('evento_active_cities', $activeCities);
        }

        if ($categoryId) {
            $conditions['category_id'] = $categoryId;
        }
        if ($cityId) {
            $conditions['Venue.city_id'] = $cityId;
        }
        $toptags = $this->Tag->find('top', array('conditions' => $conditions, 'limit' => 30));
        $this->set('events', $this->paginate('Event', $conditions));

        $this->set('weekStart', Configure::read('evento_settings.weekStart'));
        $this->set('feed', $feed);
        $this->set('year', $year);
        $this->set('month', $month);
        $this->set('monthNum', $monthNum);
        $this->set('day', $day);
        $this->set('city', $city);
        $this->set('venue', $venue);
        $this->set('country', $country);
        $this->set('current_tag', $tag);
        $this->set('category', $category);
        $this->set('data', $this->Calendar->getCalendarData($year, $month,
            $countryId, $cityId, $categoryId, $tagEvents));
        $this->set('countries', $activeCities);
        $this->set('toptags', $toptags);
        $this->set('categories', $categories);
        $this->set('promoted', $promoted);
    }

    /**
     * Display an Event with all it's data.
     *
     * @access public
     *
     * @param string $city
     * @param string $slug
     */
    public function view($country = null, $city = null, $venue = null, $slug = null)
    {
        if (!$country || !$city || !$venue || !$slug) {
            throw new NotFoundException();
        }
        if (!($event = $this->Event->viewEvent($slug))) {
            throw new NotFoundException();
        }
        if (!$event['Event']['published']) {
            throw new NotFoundException();
        }

        $this->set('title_for_layout',
            h($event['Event']['name'].'. '.' '.$event['Venue']['name'].', '.$event['City']['name']));
        $isAttendee = Set::extract('/Attendees/User[id='.$this->Auth->user('id').']/id', $event);

        if ($event['Event']['repeat_parent'] != null) {
            $this->Event->contain(array('Venue' => array('City' => array('Country'))));
            $repeatEvents = $this->Event->find('all',
                array('conditions' => array('Event.repeat_parent' => $event['Event']['repeat_parent'],
                'Event.start_date >=' => date('Y-m-d'), 'Event.id not' => $event['Event']['id'], ),
                'order' => 'Event.start_date ASC', ));
            $this->set('repeatEvents', $repeatEvents);
        }

        $photoPagination = false;
        if (count($event['Photo']) > 20) {
            $event['Photo'] = array_slice($event['Photo'], 0, 20);
            $photoPagination = true;
        }
        $this->set('photoPagination', $photoPagination);
        $this->set('useRecaptcha', $this->RecaptchaEvento->getCommentsRecaptchaStatus());
        $this->set('me', $this->Auth->user('id'));
        $this->set('event', $event);
        $this->set('isAttendee', !empty($isAttendee));
    }

    /**
     * Add a new Event. If the Event's city and tags do not exist insert
     * it all in the database and get a short url from bit.ly if enabled.
     *
     * @access public
     */
    public function add()
    {
        $this->set('title_for_layout', __('Add an event'));
        $useRecaptcha = $this->RecaptchaEvento->getEventsRecaptchaStatus();

        if ($this->PaypalEvento->isPaypalEnabled() && $this->PaypalEvento->isPaypalRequest()) {
          if ($this->PaypalEvento->doPayment()) {
            $this->request->data = $this->PaypalEvento->getEventData();
          }
        }

        if (!empty($this->request->data)) {
            if (!$useRecaptcha || ($useRecaptcha && $this->RecaptchaEvento->verify())) {
                $this->request->data['Event']['recaptcha'] = 'correct';
            }

            $this->request->data['Event']['user_id'] = $this->Auth->user('id');

            $this->Event->set($this->request->data);
            if ($this->Event->validates()) {

              if ($this->PaypalEvento->isPaypalEnabled() && !$this->PaypalEvento->isPaypalRequest()) {
                $this->PaypalEvento->checkout($this->request->data);
              }

              $this->Event->save($this->request->data);
              $eventId = $this->Event->getInsertID();
              $this->Event->contain(array('Venue' => array('City' => array('Country'))));
              $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
              $this->request->data['Event']['venue_id'] = $event['Event']['venue_id'];
              // if it is a repeat event create all needed entries
              if ($this->request->data['Event']['repeat'] != 'does_not_repeat') {
                  $event['Event']['repeat_parent'] = $eventId;
                  $this->Event->save($event);
                  $this->request->data['Event']['repeat_parent'] = $eventId;
                  $this->request->data['Event']['logo'] = $event['Event']['logo'];
                  $repeat = $this->Event->saveRepeat($this->request->data);
              }
              if (Configure::read('evento_settings.moderateEvents')) {
                  $this->set('moderation', true);

                  $cachedDate = Cache::read('evento_event_moderation');
                  if (!$cachedDate || $cachedDate > date('Y-m-d')) {
                      Cache::write('evento_event_moderation', date('Y-m-d'));
                      $email = new CakeEmail();
                      $email->from(Configure::read('evento_settings.systemEmail'));
                      $email->to(Configure::read('evento_settings.adminEmail'));
                      $email->subject(__('There are events awaiting moderation'));
                      $email->template('event_moderation');
                      $email->emailFormat('both');
                      $email->send();
                  }

                  return $this->render();
                } else {
                    $this->redirect(array('controller' => 'events', 'action' => 'view',
                        $event['Venue']['City']['Country']['slug'],
                        $event['Venue']['City']['slug'], $event['Venue']['slug'], $event['Event']['slug'], ));
                }
            }

            if (isset($this->request->data['Event']['venue_id'])
            && $this->request->data['Event']['venue_id']) {
                $this->Event->Venue->contain(array('City' => array('Country')));
                $venue = $this->Event->Venue->find('first',
                    array('conditions' => array('Venue.id' => $this->request->data['Event']['venue_id'])));

                $this->request->data['Venue'] = $venue['Venue'];
                $this->request->data['Venue']['City'] = $venue['City'];
            }
            if (isset($this->request->data['Event']['end_date'])
                && $this->request->data['Event']['start_date'] != $this->request->data['Event']['end_date']) {
                $this->set('end_date_checked', true);
            }
        }

        $this->__setLocationData();

        if (($categoryList = Cache::read('evento_categorylist')) === false) {
            $categoryList =     $this->Category->find('list', array('fields' => array('Category.name'),
                'order' => 'name', ));
            Cache::write('evento_categorylist', $categoryList);
        }

        if ($this->PaypalEvento->isPaypalEnabled()) {
          $this->set('paypal', true);
          $this->set('paypalPublishPrice', $this->PaypalEvento->getPublishPrice());
          $this->set('paypalCurrency', $this->PaypalEvento->getCurrency());
        }
        $this->set('useRecaptcha', $useRecaptcha);
        $this->set('categories', $categoryList);
    }

    /**
     * Edit an existing event. Only the user who submited the event can edit it.
     *
     * @access public
     *
     * @param int $eventId
     **/
    public function edit($eventId = null)
    {
        if (!$eventId && !($eventId = $this->request->data['Event']['id'])) {
            throw new NotFoundException();
        }
        $this->set('title_for_layout', __('edit event'));

        $this->Event->contain(array('Tag', 'Venue' => array('City' => array('Country'))));
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId,
            'Event.user_id' => $this->Auth->user('id'), 'Event.published' => true, )));

        if (empty($event)) {
            throw new NotFoundException();
        }
        unset($this->request->data['Event']['published']);
        unset($this->request->data['Event']['promoted']);
        unset($this->request->data['Event']['promoted_in_category']);
        if ($this->__editEvent($event)) {
            unset($event);
            $this->Event->contain(array('Venue' => array('City' => array('Country'))));
            $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));

            $this->redirect(array('controller' => 'events', 'action' => 'view',
                $event['Venue']['City']['Country']['slug'], $event['Venue']['City']['slug'],
                $event['Venue']['slug'], $event['Event']['slug'], ));
        }
    }

    /**
     * export event as ical file.
     *
     * @param int $eventId
     */
    public function ical($eventId = null)
    {
        if (!$eventId) {
            throw new NotFoundException();
        }
        $this->Event->contain(array('User', 'Category', 'Venue' => array('City' => array('Country'))));
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
        if (empty($event)) {
            throw new NotFoundException();
        }
        $this->layout = 'ical';
        $this->set('event', $event);
        $this->response->header('Cache-Control: public');
        $this->response->header('Content-Description: File Transfer');
        $this->response->header('Content-Disposition', 'inline; filename='.$event['Event']['slug'].'.ics');
        $this->RequestHandler->respondAs('text/calendar');
        Configure::write('debug', 0);
    }

    /**
     * search events looks at search term for a comma and if it does exist tryes to extract
     * the city name from the string.
     */
    public function search()
    {
        $data = $this->Session->read('Search.term');
        if (!empty($this->request->data) || !empty($data)) {
            if ($this->request->data) {
                $this->Session->write('Search.term', $this->request->data);
            } else {
                $this->request->data = $data;
            }

            if (($n = strrpos($this->request->data['Search']['term'], ',')) !== false) {
                $searchCity = trim(str_replace(',', '', substr($this->request->data['Search']['term'], $n)));
                $searchTerm = trim(substr($this->request->data['Search']['term'], 0, $n));
                $city = $this->City->find('first', array('conditions' => array('City.name' => $searchCity)));
                if (!empty($city)) {
                    $this->request->data['Search']['term'] = $searchTerm;
                    $this->request->data['Search']['city_id'] = $city['City']['id'];
                }
            }

            $conditions = array(
                'or' => array('Event.name like' => '%'.$this->request->data['Search']['term'].'%',
                    'Event.notes like ' => '%'.$this->request->data['Search']['term'].'%', ),
                    'Event.end_date >=' => date('Y-m-d'),
                'Event.published' => true,
            );

            if (isset($this->request->data['Search']['city_id'])
            && $this->request->data['Search']['city_id']) {
                $conditions['Venue.city_id'] = $this->request->data['Search']['city_id'];
            }

            $this->paginate['recursive'] = -1;
            $this->paginate['order'] = 'Event.start_date ASC';
            $this->paginate['fields'] = array('Event.id', 'Event.name', 'Event.slug', 'Event.start_date',
                'Event.notes', 'Event.logo', 'City.name', 'City.slug', 'User.username', 'User.slug',
                'User.photo', 'Country.name', 'Country.slug', 'Venue.slug', 'Venue.name', );

            $this->paginate['joins'] = array(
                    array(
                        'table' => 'venues',
                        'alias' => 'Venue',
                        'type' => 'left',
                        'conditions' => array('Venue.id = Event.venue_id'),
                    ),
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
                    ),
                    array(
                        'table' => 'users',
                        'alias' => 'User',
                        'type' => 'left',
                        'conditions' => array('User.id = Event.user_id'),
                    ), );

            $this->set('events', $this->paginate('Event', $conditions));
            if (isset($searchCity) && isset($searchTerm)) {
                $this->request->data['Search']['term'] = $searchTerm.', '.$searchCity;
            }
        }
        if (($categories = Cache::read('evento_categories')) === false) {
            $categories = $this->Category->find('all');
            Cache::write('evento_categories', $categories);
        }
        $this->set('categories', $categories);
        $this->set('title_for_layout', __('Search'));
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////// ADMIN
    //////////////////////////////////////////////////////////////////////

    /**
     * Show main admin page with a list of events.
     *
     * @access public
     */
    public function admin_index()
    {
        $this->Session->delete('Search.term');
        $this->set('title_for_layout', __('Manage events'));
        $this->paginate['recursive'] = -1;
        $this->paginate['joins'] = array(
            array(
                'table' => 'venues',
                'alias' => 'Venue',
                'type' => 'left',
                'conditions' => array('Venue.id = Event.venue_id'),
            ),
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
        $this->paginate['fields'] = array('Event.id', 'Event.name', 'Event.slug', 'Event.promoted',
            'Event.published', 'Event.start_date', 'Event.end_date', 'City.name', 'City.slug', 'Country.slug',
            'Venue.slug', );
        $this->paginate['order'] = 'Event.start_date DESC';
        $this->set('events', $this->paginate('Event'));
    }

    /**
     * Delete event using its id.
     *
     * @access public
     *
     * @param int $id
     * @param int $repeat
     */
    public function admin_delete($id = null, $repeat = null)
    {
        if (!$id) {
            throw new NotFoundException();
        }
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $id), 'recursive' => -1));
        if (empty($event)) {
            throw new NotFoundException();
        }

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        $conditions = null;
        $action = 'index';
        if ($this->Session->read('Search.term')) {
            $action = 'search';
            $term = $this->Session->read('Search.term');
            $conditions = array('Event.name like' => '%'.$term['Search']['term'].'%');
        }

        $deleted = false;
        if ($event['Event']['repeat_parent'] === null || ($event['Event']['repeat_parent'] != null && $repeat == 1)) {
            $this->Event->delete($id);
            $deleted = true;
        } elseif ($event['Event']['repeat_parent'] !== null && $repeat == 2) {
            $this->Event->deleteAll(array('Event.repeat_parent' => $event['Event']['repeat_parent']), true, true);
            $deleted = true;
        }
        if ($deleted) {
            if ($page) {
                $count = $this->Event->find('count', array('conditions' => $conditions));
                $lastPage = ceil($count / $this->paginate['limit']);
                if ($page > $lastPage) {
                    $page = $lastPage;
                }
            }
            $this->redirect(array('action' => $action, 'page' => $page));
        }
        $this->set('page', $page);
        $this->set('eventId', $id);
    }

    /**
     * admin can add events in admin panel.
     */
    public function admin_add()
    {
        if (!empty($this->request->data)) {
            $this->request->data['Event']['recaptcha'] = 'correct';
            $this->request->data['Event']['user_id'] = $this->Auth->user('id');
            if ($this->Event->save($this->request->data)) {
                $eventId = $this->Event->getInsertID();
                $this->Event->recursive = -1;
                $this->Event->contain(array('Venue' => array('City' => array('Country'))));
                $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
                $this->request->data['Event']['venue_id'] = $event['Event']['venue_id'];
                // if it is a repeat event create all needed entries
                if ($this->request->data['Event']['repeat'] != 'does_not_repeat') {
                    $event['Event']['repeat_parent'] = $eventId;
                    $this->Event->save($event);
                    $this->request->data['Event']['repeat_parent'] = $eventId;
                    $this->request->data['Event']['logo'] = $event['Event']['logo'];
                    $repeat = $this->Event->saveRepeat($this->request->data);
                }
                $this->redirect(array('action' => 'index'));
            }
            if ($this->request->data['Event']['venue_id']) {
                $this->Event->Venue->contain(array('City' => array('Country')));
                $venue = $this->Event->Venue->find('first',
                    array('conditions' => array('Venue.id' => $this->request->data['Event']['venue_id'])));

                $this->request->data['Venue'] = $venue['Venue'];
                $this->request->data['Venue']['City'] = $venue['City'];
            }
            if (isset($this->request->data['Event']['end_date'])
                && $this->request->data['Event']['start_date'] != $this->request->data['Event']['end_date']) {
                $this->set('end_date_checked', true);
            }
        }
        $this->set('title_for_layout', __('Add an event'));
        if (Configure::read('evento_settings.country_id')) {
            $this->set('country_id', Configure::read('evento_settings.country_id'));
            $this->set('country_name', $this->Country->field('name',
                array('Country.id' => Configure::read('evento_settings.country_id'))));
        } else {
            if (($countryList = Cache::read('evento_countrylist')) === false) {
                $countryList = $this->Country->find('countrylist');
                Cache::write('evento_countrylist', $countryList);
            }
            $this->set('countries', $countryList);
        }

        if (Configure::read('evento_settings.city_name')) {
            $this->set('default_city', Configure::read('evento_settings.city_name'));
        }

        if (Configure::read('evento_settings.adminVenues')) {
            $this->set('venues', $this->Event->Venue->find('list'));
        }

        if (($categoryList = Cache::read('evento_categorylist')) === false) {
            $categoryList =     $this->Category->find('list', array('fields' => array('Category.name'),
                'order' => 'name', ));
            Cache::write('evento_categorylist', $categoryList);
        }
        $this->set('categories', $categoryList);
        $this->set('useRecaptcha', false);
        $this->request->data['Event']['published'] = true;
    }

    /**
     * Edit an existing event.
     *
     * @access public
     *
     * @param int $eventId
     */
    public function admin_edit($eventId = null)
    {
        if (!$eventId && !($eventId = $this->request->data['Event']['id'])) {
            throw new NotFoundException();
        }
        $this->set('title_for_layout', __('edit event'));
        $this->Event->contain(array('Tag', 'Venue' => array('City' => array('Country'))));
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));

        if (empty($event)) {
            throw new NotFoundException();
        }
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;

        if ($this->__editEvent($event)) {
            unset($event);
            $action = 'index';
            if ($this->Session->read('Search.term')) {
                $action = 'search';
            }
            $this->redirect(array('action' => $action, 'page' => $page));
        }
        $this->set('page', $page);
    }

    /**
     * admin search events.
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

            $conditions = array('Event.name like' => '%'.$this->request->data['Search']['term'].'%');
            $this->paginate['recursive'] = -1;
            $this->paginate['joins'] = array(
                array(
                    'table' => 'venues',
                    'alias' => 'Venue',
                    'type' => 'left',
                    'conditions' => array('Venue.id = Event.venue_id'),
                ),
                array(
                    'table' => 'cities',
                    'alias' => 'City',
                    'type' => 'left',
                    'conditions' => array('Venue.city_id = City.id'),
                ),
                array(
                    'table' => 'countries',
                    'alias' => 'Country',
                    'type' => 'left',
                    'conditions' => array('Country.id = City.country_id'),
                ), );
            $this->paginate['order'] = 'Event.created DESC';
            $this->paginate['fields'] = array('Event.id', 'Event.name', 'Event.slug',
                'Event.published', 'Event.promoted', 'City.slug', 'Country.slug', 'Venue.slug', );
            $this->set('events', $this->paginate('Event', $conditions));
        }
        $this->set('title_for_layout', __('Search'));
        $this->render('admin_index');
    }

    /**
     * merge events.
     *
     * @param int $eventId
     * @param int $eventOriginalId
     */
    public function admin_merge($eventId = null, $eventOriginalId = null)
    {
        if (!$eventId) {
            throw new NotFoundException();
        }
        $this->Event->recursive = -1;
        $eventMerge = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
        if (empty($eventMerge)) {
            throw new NotFoundException();
        }

        if ($eventOriginalId === null) {
            $conditions = array();
            if (!empty($this->request->data)) {
                $conditions['Event.name like'] = '%'.$this->request->data['Search']['term'].'%';
            }
            $conditions['Event.id <>'] = $eventMerge['Event']['id'];
            $this->paginate['limit'] = 50;
            $this->paginate['recursive'] = -1;
            $this->paginate['order'] = 'Event.created DESC';
            $this->paginate['fields'] = array('Event.id', 'Event.name', 'Event.slug',
                'Event.published', 'Event.promoted', );
            $this->set('events', $this->paginate('Event', $conditions));
            $this->set('event_merge', $eventMerge);
        } else {
            $this->Event->recursive = -1;
            $eventOriginal = $this->Event->find('first',
                array('conditions' => array('Event.id' => $eventOriginalId)));
            if (empty($eventOriginal)) {
                throw new NotFoundException();
            }

            // merge comments
            $this->Event->Comment->updateAll(array('Comment.event_id' => $eventOriginal['Event']['id']),
                array('Comment.event_id' => $eventMerge['Event']['id']));

            // merge photos
            $this->Event->Photo->updateAll(array('Photo.event_id' => $eventOriginal['Event']['id']),
                array('Photo.event_id' => $eventMerge['Event']['id']));

            // merge tags
            $originalTags = $this->Tag->EventsTag->find('all',
                array('conditions' => array('EventsTag.event_id' => $eventOriginal['Event']['id']),
                'fields' => array('tag_id'), ));
            if (!empty($originalTags)) {
                $originalTags = Set::extract('/EventsTag/tag_id', $originalTags);
                $this->Tag->EventsTag->deleteAll(array('EventsTag.event_id' => $eventMerge['Event']['id'],
                    'EventsTag.tag_id' => $originalTags, ));
            }
            $this->Tag->EventsTag->updateAll(array('EventsTag.event_id' => $eventOriginal['Event']['id']),
                array('EventsTag.event_id' => $eventMerge['Event']['id']));

            // merge users
            $originalUsers = $this->User->EventsUser->find('all',
                array('conditions' => array('EventsUser.event_id' => $eventOriginal['Event']['id']),
                'fields' => array('user_id'), ));
            if (!empty($originalUsers)) {
                $originalUsers = Set::extract('/EventsUser/user_id', $originalUsers);
                $this->User->EventsUser->deleteAll(array('EventsUser.event_id' => $eventMerge['Event']['id'],
                    'EventsUser.user_id' => $originalUsers, ));
            }
            $this->User->EventsUser->updateAll(array('EventsUser.event_id' => $eventOriginal['Event']['id']),
                array('EventsUser.event_id' => $eventMerge['Event']['id']));

            // delete merge event
            $this->Event->delete($eventMerge['Event']['id']);
            $this->redirect(array('action' => 'index'));
        }
    }

    /**
     * show event attendees for admin.
     *
     * @param int $eventId
     */
    public function admin_attendees($eventId = null)
    {
        if (!$eventId) {
            throw new NotFoundException();
        }
        $this->Event->contain(array('Attendees'));
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
        $this->set('event', $event);
    }

    /**
     * export event attendees as csv.
     *
     * @param int $eventId
     */
    public function admin_export_attendees($eventId = null)
    {
        if (!$eventId) {
            throw new NotFoundException();
        }
        Configure::write('debug', 0);
        $this->layout = 'export';
        $this->response->header('Cache-Control: private');
        $this->response->header('Content-Description: File Transfer');
        $this->response->header('Content-Disposition: attachment; filename=users.csv');
        $this->RequestHandler->respondAs('csv');

        $this->Event->contain(array('Attendees' => array('fields' => array('id'))));
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
        $userIds = Set::extract('/Attendees/id', $event);

        $joins = array(
            array(
                'table' => 'cities',
                'alias' => 'City',
                'type' => 'left',
                'conditions' => array('City.id = User.city_id'),
            ),
            array(
                'table' => 'countries',
                'alias' => 'Country',
                'type' => 'left',
                'conditions' => array('Country.id = City.country_id'),
            ), );
        $users = $this->User->find('all', array('conditions' => array('User.id' => $userIds),
             'fields' => array('User.username', 'User.email', 'User.web', 'City.name', 'Country.name'),
            'joins' => $joins, ));
        $this->set('users', $users);
    }

    /**
     * bulk manage events.
     */
    public function admin_bulk()
    {
        if (empty($this->request->data)) {
            throw new NotFoundException();
        }
        $ids = array();
        foreach ($this->request->data['Event']['id'] as $key => $value) {
            if ($value != 0) {
                $ids[] = $key;
            }
        }
        switch ($this->request->data['Event']['option']) {
            case 'publish':
                $this->Event->bulkPublish($ids, 1);
                break;
            case 'unpublish':
                $this->Event->bulkPublish($ids, 0);
                break;
            case 'delete':
                $this->Event->bulkDelete($ids);
                break;
        }
        $this->redirect(array('action' => 'index'));
    }

    //////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////// Private Methods
    //////////////////////////////////////////////////////////////////////

    /**
     * We use this method to edit events in user mode and admin mode.
     *
     * @param array $event
     */
    private function __editEvent($event)
    {
        if (!empty($this->request->data)) {
            if ($this->Event->save($this->request->data)) {
                return true;
            }
            if (!empty($this->request->data['Event']['venue_id'])) {
                // if event could not be saved check if venue changed and retrive its data from database
                if ($this->request->data['Event']['venue_id'] != $event['Event']['venue_id']) {
                    $this->Event->Venue->contain(array('City' => array('Country')));
                    $tmpVenue = $this->Event->Venue->find('first',
                        array('conditions' => array('Venue.id' => $this->request->data['Event']['venue_id'])));
                    $venue['Venue'] = $tmpVenue['Venue'];
                    $venue['Venue']['City'] = $tmpVenue['City'];
                    $this->request->data['Venue'] = $venue['Venue'];
                } else {
                    // if venue didn't change use the original data
                    $this->request->data['Venue'] = $event['Venue'];
                }
            }
        } else {
            $tags = Set::extract('/Tag/name', $event);
            $event['Event']['tags'] = implode(', ', $tags);
            $this->request->data = $event;
        }

        // load needed data for the event form
        if (Configure::read('evento_settings.country_id')) {
            $this->set('country_id', Configure::read('evento_settings.country_id'));
            $this->set('country_name', $this->Country->field('name',
                array('Country.id' => Configure::read('evento_settings.country_id'))));
        } else {
            if (($countryList = Cache::read('evento_countrylist')) === false) {
                $countryList = $this->Country->find('countrylist');
                Cache::write('evento_countrylist', $countryList);
            }
            $this->set('countries', $countryList);
        }

        if (Configure::read('evento_settings.city_name')) {
            $this->set('default_city', Configure::read('evento_settings.city_name'));
        }

        if (Configure::read('evento_settings.adminVenues')) {
            $this->set('venues', $this->Event->Venue->find('list'));
        }

        if (($categoryList = Cache::read('evento_categorylist')) === false) {
            $categoryList =     $this->Category->find('list', array('fields' => array('Category.name'),
                'order' => 'name', ));
            Cache::write('evento_categorylist', $categoryList);
        }
        if ($event['Event']['start_date'] != $event['Event']['end_date']) {
            $this->set('end_date_checked', true);
        }
        $this->set('categories', $categoryList);
        $this->set('event', $event);
    }

    /**
     * set location data
     */
    private function __setLocationData()
    {
      if (Configure::read('evento_settings.country_id')) {
          $this->set('country_id', Configure::read('evento_settings.country_id'));
          $this->set('country_name', $this->Country->field('name',
              array('Country.id' => Configure::read('evento_settings.country_id'))));
      } else {
          if (($countryList = Cache::read('evento_countrylist')) === false) {
              $countryList = $this->Country->find('countrylist');
              Cache::write('evento_countrylist', $countryList);
          }
          $this->set('countries', $countryList);
      }

      if (Configure::read('evento_settings.city_name')) {
          $this->set('default_city', Configure::read('evento_settings.city_name'));
      }

      if (Configure::read('evento_settings.adminVenues')) {
          $this->set('venues', $this->Event->Venue->find('list'));
      }
    }
}
