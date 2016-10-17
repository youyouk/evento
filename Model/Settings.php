<?php
App::uses('Sanitize', 'Utility');

class Settings extends AppModel
{
    public $name = 'Settings';

    /*
     * global application settings variable
     */

    public $appSettings = null;

    /**
     * overwrite before save function
     * update new settings values and delete settings cache.
     *
     * @param array options
     *
     * @return bool
     */
    public function beforeSave($options = array())
    {
        if ($this->data['Settings']['city_name'] && !$this->data['Settings']['country_id']) {
            $this->invalidate('city_name', 'You must select a country.');

            return false;
        }
        foreach ($this->data['Settings'] as $key => $value) {
            $this->updateKeyValue($key, $value);
        }
        Cache::delete('evento_settings');
        $this->appSettings = null;

        return parent::beforeSave($options);
    }

    /**
     * if $appSettings is not set read settings from cache or database and return settings.
     * if param $key is specified return its value.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSettings($key = null)
    {
        if ($this->appSettings === null && ($this->appSettings = Cache::read('evento_settings')) === false) {
            $this->appSettings = Set::combine($this->find('all'), '{n}.Settings.key', '{n}.Settings.value');
            $this->appSettings['languages'] = $this->getLanguages();
            Cache::write('evento_settings', $this->appSettings);
        }

        if ($key) {
            return $this->appSettings[$key];
        } else {
            return $this->appSettings;
        }
    }

    /**
     * update settings with $key and $value.
     *
     * @param string $key
     * @param string $value
     */
    public function updateKeyValue($key, $value)
    {
        $this->updateAll(array('value' => '\''.Sanitize::escape($value).'\''), array('key' => $key));
    }

    /**
     * return an array with the languages installed in the app/locale folder.
     *
     * @return array
     */
    public function getLanguages()
    {
        $locale_path = APP.'Locale';
        $languages = array();
        if (is_dir($locale_path) && $handler = opendir($locale_path)) {
            while (($file = readdir($handler)) !== false) {
                if (strlen($file) == 3) {
                    $languages[$file] = __d('languages', $file);
                }
            }
        }

        return $languages;
    }

    /**
     * return an array of available themes.
     *
     * @return array
     */
    public function getThemes()
    {
        $themesPath = APP.'View/Themed';
        $themes = array();
        foreach (glob($themesPath.'/*', GLOB_ONLYDIR) as $theme) {
            $theme = basename($theme);
            $themes[$theme] = $theme;
        }

        return $themes;
    }
}
