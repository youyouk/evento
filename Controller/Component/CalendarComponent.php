<?php

/**
 * @copyright Copyright 2010 Evento
 */
class CalendarComponent extends Component
{
    /**
     * Controller.
     *
     * @var object
     */
    public $controller;

    /**
     * current year.
     *
     * @var string
     */
    public $year;

    /**
     * current month.
     *
     * @var string
     */
    public $month;

    /**
     * initialize component to keep the controller accessible.
     *
     * @param object $controller
     * @param array  $settings
     */
    public function initialize(Controller $controller)
    {
        $this->controller = & $controller;
        $this->year = date('Y');
        $this->month = date('n');
    }

    /**
     * if year is not valid return current year.
     *
     * @param int $year
     */
    public function getYear($year)
    {
        $this->year =  ($year>0) ? intval($year) : false;

        return $this->year;
    }

    /**
     * returns the month number if month is valid, or false if it isn't.
     *
     * @param string $month
     */
    public function getMonth($month)
    {
        if (!$month) {
            return date('n');
        }
        $month_list = array(
            __d('url', 'january'),
            __d('url', 'february'),
            __d('url', 'march'),
            __d('url', 'april'),
            __d('url', 'may'),
            __d('url', 'june'),
            __d('url', 'july'),
            __d('url', 'august'),
            __d('url', 'september'),
            __d('url', 'october'),
            __d('url', 'november'),
            __d('url', 'december'),
        );

        if (is_integer($month_num = array_search(strtolower($month), $month_list))) {
            $this->month = ++$month_num;

            return $this->month;
        }

        return false;
    }

    /**
     * return an array with the conditions needed to fetch the events.
     *
     * @param string $day
     * @param string $weekStart
     */
    public function getDayConditions($day)
    {
        if ($day == __d('url', 'week')) {
            if (Configure::read('evento_settings.weekStart') == 'monday') {
                $week_day = (date('w') == 0) ? 7 : date('w'); // date('N') php 5.1
                $days_left = 7-$week_day;
            } else {
                $week_day = date('w');
                $days_left = 6-$week_day;
            }
            $first_week_day = date('d'); // show events starting from today to last day of the week
            $last_week_day = $first_week_day+$days_left;

            $conditions['DAY(end_date) >='] = $first_week_day;
            $conditions['DAY(start_date) >='] = $first_week_day;
            $conditions['DAY(start_date) <='] = $last_week_day;
            $conditions['MONTH(start_date)'] = date('m');
        } else {
            if (!is_numeric($day) || !checkdate($this->month, $day, $this->year)) {
                return false;
            }
            $conditions['start_date <='] = $this->year.'-'.$this->month.'-'.$day.' 23:59:59';
            $conditions['end_date >= '] = $this->year.'-'.$this->month.'-'.$day;
        }

        return $conditions;
    }

    /**
     * return an array with the data needed by the calendar.
     *
     * @param int   $year
     * @param int   $month
     * @param int   $country_id
     * @param int   $city_id
     * @param int   $category_id
     * @param array $tag_events;
     */
    public function getCalendarData($year = null, $month = null, $country_id = null, $city_id = null,
    $category_id = null, $tag_events = null)
    {
        $data = array();
        if (!($year = $this->getYear($year))) {
            $year = date('Y');
        }
        if (!($month = $this->getMonth($month))) {
            $month = date('n');
        }
        $firstDay = $year.'-'.$month.'-01';
        $month = sprintf('%02d', $month);

        $conditions = array(
            'Event.end_date >=' => date('Y-m-d', strtotime($firstDay)),
            'Event.start_date <=' => date('Y-m-d', strtotime($firstDay.'+1 month')),
            'Event.published' => true,
        );

        if ($city_id) {
            $conditions['Venue.city_id'] = $city_id;
        }
        if ($country_id) {
            $conditions['City.country_id'] = $country_id;
        }
        if ($category_id) {
            $conditions['Event.category_id'] = $category_id;
        }
        if ($tag_events) {
            $conditions['Event.id'] = $tag_events;
        }

        $this->controller->Event->recursive = -1;
        $v = $this->controller->Event->find('all', array(
                'conditions' => $conditions,
                'fields' => array('DAY(start_date) as day', 'start_date', 'end_date'),
                'joins' => array(
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
                ), ));

        foreach ($v as $event) {
            $date = date('Y-m-d', strtotime($event['Event']['start_date']));
            $enddate = date('Y-m-d', strtotime($event['Event']['end_date']));
            $startdate = date('Y-m-d', strtotime($event['Event']['start_date']));

            if (date('Y-m', strtotime($date)) < $year.'-'.$month) {
                $date = date('Y-m-d', strtotime($firstDay));
            }

            while (($date <= $enddate) && (date('n', strtotime($date)) == $month)) {
                if (!isset($data[date('j', strtotime($date))])) {
                    $data[date('j', strtotime($date))] = 0;
                }
                $data[date('j', strtotime($date))] = $data[date('j', strtotime($date))] + 1;
                $date = date('Y-m-d', strtotime($date.'+1 day'));
            }
        }

        return $data;
    }
}
