<?php
class SettingsController extends AppController
{
    public $name = 'Settings';
    public $uses = array('Settings', 'Event', 'Group');

    /**
     * allow access to the lang method.
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow('lang');
    }

    /**
     * set language option in a cookie.
     *
     * @param string lang
     */
    public function lang($lang)
    {
        if (array_key_exists($lang, $this->Settings->getLanguages())) {
            $this->Cookie->write('language', $lang, true, '1 month');
            $this->Session->write('Config.language', $lang);
        }
        $referer = $this->referer(null, true);
        if (!$referer) {
            $this->redirect(array('admin' => false, 'plugin' => false, 'controller' => 'events', 'action' => 'index'));
        }
        $this->redirect($referer);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////  ADMIN
    ////////////////////////////////////////////////////////////////////

    /**
     * Dislay admin control panel settings and save changes made by admin users.
     * Settings are stored in the database using a key / value system.
     *
     * Application version number is set in app/app_controller.php
     */
    public function admin_index()
    {
        $this->set('title_for_layout', __('Application settings'));
        if ($this->request->data) {
            $this->Settings->save($this->request->data);
            if (!$this->Settings->validationErrors) {
                $group = new $this->Group();
                $group->id = 3;
                if ($this->request->data['Settings']['guestsCanAddEvents'] == true) {
                    $this->Acl->allow($group, 'controllers/Events');
                } else {
                    $this->Acl->deny($group, 'controllers/Events');
                }
                $this->Session->write('Config.language', $this->request->data['Settings']['language']);
                $this->redirect(array('admin' => true, 'controller' => 'events', 'action' => 'index'));
            }
        } else {
            $this->request->data['Settings'] = Configure::read('evento_settings');
        }

        $this->set('currencies', $this->__getCurrencies());
        $this->set('tm', $this->__getTimezones());
        $this->request->data['languages'] = $this->Settings->getLanguages();
        $this->request->data['themes'] = $this->Settings->getThemes();
        $this->set('countries', $this->Event->Venue->City->Country->find('countrylist'));
    }

    /**
     * Return an array with all the available currencies
     */
    private function __getCurrencies()
    {
      $c['AUD'] = 'Australian Dollar';
      $c['BRL'] = 'Brazilian Real';
      $c['CAD'] = 'Canadian Dollar';
      $c['CZK'] = 'Czech Koruna';
      $c['DKK'] = 'Danish Krone';
      $c['EUR'] = 'Euro';
      $c['JPY'] = 'Japanese Yen';
      $c['NOK'] = 'Norwegian Krone';
      $c['SGD'] = 'Singapore Dollar';
      $c['SEK'] = 'Swedish Krona';
      $c['CHF'] = 'Swiss Franc';
      $c['USD'] = 'U.S. Dollar';
      return $c;
    }

    /**
     * Return an array with all the available timezones.
     */
    private function __getTimezones()
    {
        // timezones array
        $tm['Pacific/Midway'] = '(GMT-11:00) Midway Island, Samoa';
        $tm['America/Adak'] = '(GMT-10:00) Hawaii-Aleutian';
        $tm['Etc/GMT+10'] = '(GMT-10:00) Hawaii';
        $tm['Pacific/Marquesas'] = '(GMT-09:30) Marquesas Islands';
        $tm['Pacific/Gambier'] = '(GMT-09:00) Gambier Islands';
        $tm['America/Anchorage'] = '(GMT-09:00) Alaska';
        $tm['America/Ensenada'] = '(GMT-08:00) Tijuana, Baja California';
        $tm['Etc/GMT+8'] = '(GMT-08:00) Pitcairn Islands';
        $tm['America/Los_Angeles'] = '(GMT-08:00) Pacific Time (US & Canada)';
        $tm['America/Denver'] = '(GMT-07:00) Mountain Time (US & Canada)';
        $tm['America/Chihuahua'] = '(GMT-07:00) Chihuahua, La Paz, Mazatlan';
        $tm['America/Dawson_Creek'] = '(GMT-07:00) Arizona';
        $tm['America/Belize'] = '(GMT-06:00) Saskatchewan, Central America';
        $tm['America/Cancun'] = '(GMT-06:00) Guadalajara, Mexico City, Monterrey';
        $tm['Chile/EasterIsland'] = '(GMT-06:00) Easter Island';
        $tm['America/Chicago'] = '(GMT-06:00) Central Time (US & Canada)';
        $tm['America/New_York'] = '(GMT-05:00) Eastern Time (US & Canada)';
        $tm['America/Havana'] = '(GMT-05:00) Cuba';
        $tm['America/Bogota'] = '(GMT-05:00) Bogota, Lima, Quito, Rio Branco';
        $tm['America/Caracas'] = '(GMT-04:30) Caracas';
        $tm['America/Santiago'] = '(GMT-04:00) Santiago';
        $tm['America/La_Paz'] = '(GMT-04:00) La Paz';
        $tm['Atlantic/Stanley'] = '(GMT-04:00) Faukland Islands';
        $tm['America/Campo_Grande'] = '(GMT-04:00) Brazil';
        $tm['America/Goose_Bay'] = '(GMT-04:00) Atlantic Time (Goose Bay)';
        $tm['America/Glace_Bay'] = '(GMT-04:00) Atlantic Time (Canada)';
        $tm['America/St_Johns'] = '(GMT-03:30) Newfoundland';
        $tm['America/Montevideo'] = '(GMT-03:00) Montevideo';
        $tm['America/Miquelon'] = '(GMT-03:00) Miquelon, St. Pierre';
        $tm['America/Godthab'] = '(GMT-03:00) Greenland';
        $tm['America/Argentina/Buenos_Aires'] = '(GMT-03:00) Buenos Aires';
        $tm['America/Sao_Paulo'] = '(GMT-03:00) Brasilia';
        $tm['America/Noronha'] = '(GMT-02:00) Mid-Atlantic';
        $tm['Atlantic/Cape_Verde'] = '(GMT-01:00) Cape Verde Is.';
        $tm['Atlantic/Azores'] = '(GMT-01:00) Azores';
        $tm['Europe/Belfast'] = '(GMT) Belfast, Dublin, Lisbon, London';
        $tm['Africa/Abidjan'] = '(GMT) Monrovia, Reykjavik';
        $tm['Europe/Amsterdam'] = '(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna';
        $tm['Europe/Belgrade'] = '(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague';
        $tm['Europe/Brussels'] = '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris';
        $tm['Africa/Algiers'] = '(GMT+01:00) West Central Africa';
        $tm['Africa/Windhoek'] = '(GMT+01:00) Windhoek';
        $tm['Asia/Beirut'] = '(GMT+02:00) Beirut';
        $tm['Africa/Cairo'] = '(GMT+02:00) Cairo';
        $tm['Asia/Gaza'] = '(GMT+02:00) Gaza';
        $tm['Africa/Blantyre'] = '(GMT+02:00) Harare, Pretoria';
        $tm['Asia/Jerusalem'] = '(GMT+02:00) Jerusalem';
        $tm['Europe/Minsk'] = '(GMT+02:00) Minsk';
        $tm['Asia/Damascus'] = '(GMT+02:00) Syria';
        $tm['Europe/Moscow'] = '(GMT+03:00) Moscow, St. Petersburg, Volgograd';
        $tm['Africa/Addis_Ababa'] = '(GMT+03:00) Nairobi';
        $tm['Asia/Tehran'] = '(GMT+03:30) Tehran';
        $tm['Asia/Dubai'] = '(GMT+04:00) Abu Dhabi, Muscat';
        $tm['Asia/Kabul'] = '(GMT+04:30) Kabul';
        $tm['Asia/Yekaterinburg'] = '(GMT+05:00) Ekaterinburg';
        $tm['Asia/Tashkent'] = '(GMT+05:00) Tashkent';
        $tm['Asia/Kolkata'] = '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi';
        $tm['Asia/Katmandu'] = '(GMT+05:45) Kathmandu';
        $tm['Asia/Dhaka'] = '(GMT+06:00) Astana, Dhaka';
        $tm['Asia/Novosibirsk'] = '(GMT+06:00) Novosibirsk';
        $tm['Asia/Rangoon'] = '(GMT+06:30) Yangon (Rangoon)';
        $tm['Asia/Bangkok'] = '(GMT+07:00) Bangkok, Hanoi, Jakarta';
        $tm['Asia/Krasnoyarsk'] = '(GMT+07:00) Krasnoyarsk';
        $tm['Asia/Hong_Kong'] = '(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi';
        $tm['Asia/Irkutsk'] = '(GMT+08:00) Irkutsk, Ulaan Bataar';
        $tm['Australia/Perth'] = '(GMT+08:00) Perth';
        $tm['Australia/Eucla'] = '(GMT+08:45) Eucla';
        $tm['Asia/Tokyo'] = '(GMT+09:00) Osaka, Sapporo, Tokyo, Seoul';
        $tm['Asia/Yakutsk'] = '(GMT+09:00) Yakutsk';
        $tm['Australia/Adelaide'] = '(GMT+09:30) Adelaide';
        $tm['Australia/Darwin'] = '(GMT+09:30) Darwin';
        $tm['Australia/Brisbane'] = '(GMT+10:00) Brisbane';
        $tm['Australia/Hobart'] = '(GMT+10:00) Hobart';
        $tm['Asia/Vladivostok'] = '(GMT+10:00) Vladivostok';
        $tm['Australia/Lord_Howe'] = '(GMT+10:30) Lord Howe Island';
        $tm['Asia/Magadan'] = '(GMT+11:00) Magadan';
        $tm['Pacific/Norfolk'] = '(GMT+11:30) Norfolk Island';
        $tm['Asia/Anadyr'] = '(GMT+12:00) Anadyr, Kamchatka';
        $tm['Pacific/Auckland'] = '(GMT+12:00) Auckland, Wellington';
        $tm['Etc/GMT-12'] = '(GMT+12:00) Fiji, Kamchatka, Marshall Is.';
        $tm['Pacific/Chatham'] = '(GMT+12:45) Chatham Islands';
        $tm['Pacific/Tongatapu'] = '(GMT+13:00) Nuku\'alofa';
        $tm['Pacific/Kiritimati'] = '(GMT+14:00) Kiritimati';

        return $tm;
    }
}
