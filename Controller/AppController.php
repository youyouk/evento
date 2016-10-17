<?php

/* SVN FILE: $Id: app_controller.php 6311 2008-01-02 06:33:52Z phpnut $ */
/**
 * Short description for file.
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 *
 * @copyright		Copyright 2005-2008, Cake Software Foundation, Inc.
 *
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @since			CakePHP(tm) v 0.2.9
 *
 * @version			$Revision: 6311 $
 * @lastmodified	$Date: 2009-04-19 $
 *
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Short description for class.
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 */
class AppController extends Controller
{
    public $uses = array('Settings', 'User', 'Group');
    public $components = array('Auth', 'Acl', 'Session', 'Cookie');
    public $helpers = array('Html', 'Form', 'Js', 'Session', 'Text');
    public $theme = 'Default';

    /**
     * beforeFilter overload
     * set login and logout actions.
     */
    public function beforeFilter()
    {
        // load site settings
        $this->__loadSettings();

        // set language
        $languageCookie = $this->Cookie->read('language');
        if (!$languageCookie || !array_key_exists($languageCookie, $this->Settings->getLanguages())) {
            Configure::write('Config.language', Configure::read('evento_settings.language'));
        } else {
            Configure::write('evento_settings.language', $languageCookie);
            Configure::write('Config.language', $languageCookie);
            $this->Cookie->write('language', $languageCookie, true, '1 month');
        }

        // set time zone
        if (Configure::read('evento_settings.timeZone')) {
            date_default_timezone_set(Configure::read('evento_settings.timeZone'));
        }

        // set auth options
        $this->Auth->userScope = array('User.active' => true);
        $this->Auth->logoutRedirect = array('admin' => false, 'controller' => 'events', 'action' => 'index');
        $this->Auth->authError = __('Please log in to continue');
        $this->Auth->authenticate = array('Form' => array('fields' => array('username' => 'email')));
        $this->Auth->loginAction = array(
            'controller' => 'users',
            'action' => 'login',
            'admin' => 0,
            'plugin' => null,
        );
        $this->Auth->authorize  = array('Evento');

        // set theme
        $this->theme = Configure::read('evento_settings.theme');

        // set default page title
        $this->set('title_for_layout', __(Configure::read('evento_settings.appSlogan')));

        // load the Facebook SDK
        $this->__loadFacebookSDK();

        return parent::beforeFilter();
    }

    /**
     * Set the Facebook URL in the beforeRender method so it is available to all views.
     * Check user permissions so we can show or hide the menu options.
     */
    public function beforeRender()
    {
        if ($this->__facebookLoginEnabled()) {
            $loginUrl = Router::url(array('controller' => 'users', 'action' => 'login'), true);
            $permissions = array('email');
            $helper = $this->Facebook->getRedirectLoginHelper();
            $url = $helper->getLoginUrl($loginUrl, $permissions);
            $this->set('facebookLoginUrl', $url);
        }

        if (isset($this->request->params['admin']) && $this->layout == 'default') {
            $this->layout = 'admin_default';
        }

        $showAddEventsButton = Configure::read('evento_settings.guestsCanAddEvents');
        if ($this->Auth->user()) {
            $group = new $this->Group();
            $group->id = ($this->Auth->user()) ? $this->Auth->user('group_id') : 3;
            $showAddEventsButton = $this->Acl->check($group, 'controllers/Events');

            $group = new $this->Group();
            $group->id = $this->Auth->user('group_id');
            $this->set('showAdminButton', $this->Acl->check($group, 'admin'));
        }
        $this->set('showAddEventsButton', $showAddEventsButton);
    }

    /**
     * load application settings.
     */
    private function __loadSettings()
    {
        $settings = $this->Settings->getSettings();
        Configure::write('evento_settings', $settings);
        if (Configure::read('evento_settings.recaptchaPublicKey')) {
            Configure::write('Recaptcha.publicKey', Configure::read('evento_settings.recaptchaPublicKey'));
            Configure::write('Recaptcha.privateKey', Configure::read('evento_settings.recaptchaPrivateKey'));
        }
    }

    /**
     * check if facebook login is enabled.
     */
    protected function __facebookLoginEnabled()
    {
        $facebookAppId = Configure::read('evento_settings.facebookAppId');
        $facebookSecret = Configure::read('evento_settings.facebookSecret');

        return ($facebookAppId && $facebookSecret);
    }

    /**
     * check if facebook config exists and create the facebook object.
     */
    private function __loadFacebookSDK()
    {
        if ($this->__facebookLoginEnabled()) {
            // load Facebook SDK
            $this->Session->write('evento.facebookSDK', 'true');
            App::import('Vendor', 'FacebookAuto', array('file' => 'facebook-sdk'.DS.'src'.DS.'Facebook'.DS.'autoload.php'));
            $this->Facebook = new Facebook\Facebook(array(
                'app_id' => Configure::read('evento_settings.facebookAppId'),
                'app_secret' => Configure::read('evento_settings.facebookSecret'),
                'default_graph_version' => 'v2.2',
            ));
        }
    }
}
