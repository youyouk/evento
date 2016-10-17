<?php
App::uses('CakeEmail', 'Network/Email');

class UsersController extends AppController
{
    public $name = 'Users';
    public $components = array('RequestHandler');
    public $helpers = array('Html', 'Form', 'Time', 'Js', 'Text', 'Timeformat');
    public $uses = array('User', 'Group', 'Event', 'EventsUser', 'Country', 'City');
    public $paginate = array('limit' => 30, 'order' => array('User.created' => 'desc'));

    /**
     * Overload beforeFilter and set some permissions.
     */
    public function beforeFilter()
    {
        $this->Auth->allow('login', 'logout', 'view', 'register', 'recover',
            'code_login', 'activation', 'index');

        return parent::beforeFilter();
    }

    /**
     * User login, if user is already logged in then redirect to events index.
     *
     * @access public
     */
    public function login()
    {
        if ($this->Auth->user()) {
            $this->redirect(array('controller' => 'events', 'action' => 'index'));
        }
        if ($this->request->is('post')) {
            if ($this->Auth->login()) {
                return $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash(__('Invalid username or password.'), 'default', array(), 'auth');
            }
        } elseif ($this->request->query('code') && $this->__facebookLoginEnabled()) {
            $this->__facebookLogin();
        }
        $this->set('title_for_layout', __('users login'));
    }

    /**
     * User logout and redirect to homepage.
     *
     * @access public
     */
    public function logout()
    {
        $this->redirect($this->Auth->logout());
    }

    /**
     * User registration.
     * First user will have admin permissions. If option is set users will need to confirm
     * the provided email address.
     *
     * @access public
     */
    public function register()
    {
        if (Configure::read('evento_settings.adminAddsUsers') == true) {
            throw new NotFoundException();
        }
        if ($this->Auth->user()) {
            $this->redirect(array('controller' => 'events', 'action' => 'index'));
        }

        $useRecaptcha = false;
        if (Configure::read('evento_settings.recaptchaPublicKey')
        && Configure::read('evento_settings.recaptchaPrivateKey')) {
            $recaptcha = $this->Components->load('Recaptcha.Recaptcha');
            $recaptcha->enabled = true;
            $recaptcha->initialize($this);
            $recaptcha->startup($this);
            $useRecaptcha = true;
        }

        if ($this->request->data) {
            if (!$useRecaptcha || ($useRecaptcha && $recaptcha->verify())) {
                $this->request->data['User']['recaptcha'] = 'correct';
            }
            if (0 == $this->User->find('count')) {
                $this->request->data['User']['group_id'] = 1; // Administrators
            } else {
                $this->request->data['User']['group_id'] = 3; // Guests
            }

            if (Configure::read('evento_settings.validateEmails') == true) {
                $validationCode = sha1(rand().'-'.time());
                $this->request->data['User']['validation_code'] = $validationCode;
                $this->request->data['User']['validation_date'] = date('Y-m-d H-i-s');
                $this->request->data['User']['active'] = 0;
            }

            if ($this->User->save($this->request->data)) {
                $this->User->recursive = -1;
                $user = $this->User->find('first', array('conditions' => array(
                    'id' => $this->User->getLastInsertID(), )));
                if (Configure::read('evento_settings.validateEmails') == true) {
                    $this->set('code', $user['User']['validation_code']);
                    $email = new CakeEmail();
                    $email->viewVars(array(
                        'code' => $user['User']['validation_code'],
                        'user' => $user['User']['username'],
                    ));
                    $email->from(Configure::read('evento_settings.systemEmail'));
                    $email->to($user['User']['email']);
                    $email->subject(__('Email confirmation'));
                    $email->template('email_confirmation');
                    $email->emailFormat('both');
                    $email->send();
                } else {
                    $this->__sendWelcomeMessage($user);
                    $this->Auth->login();
                    $group = new $this->Group();
                    $group->id = $this->Auth->user('group_id');
                    if ($this->Acl->check($group, 'admin')) {
                        $this->redirect(array('admin' => true, 'controller' => 'settings',
                            'action' => 'index', ));
                    } else {
                        $this->redirect(array('controller' => 'users', 'action' => 'view',
                            $user['User']['slug'], ));
                    }
                }
            }
        }
        $this->set('useRecaptcha', $useRecaptcha);
        $this->set('title_for_layout', __('Registration form'));
    }

    /**
     * View user's profile.
     *
     * @param string $user_slug
     */
    public function view($user_slug = null, $mode = null)
    {
        if (!$user_slug) {
            throw new NotFoundException();
        }
        if (Configure::read('evento_settings.disableAttendees') == 1) {
            $mode = 'posted';
        }
        $user = $this->User->find('first', array('conditions' => array('User.slug' => $user_slug),
            'joins' => array(
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
                ), ),
            'fields' => array(
                'User.id', 'User.username', 'User.photo', 'User.created', 'User.web', 'User.slug',
                'City.name', 'City.slug', 'Country.name', 'Country.slug', ),
        ));
        if (empty($user)) {
            throw new NotFoundException();
        }

        $this->paginate['joins'] = array(
            array(
                'table' => 'events',
                'alias' => 'Event',
                'type' => 'left',
                'conditions' => array('Event.id = EventsUser.event_id'),
            ),
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
        );

        $model = 'EventsUser';
        $this->paginate['limit'] = 10;
        $this->paginate['fields'] = array('Event.name', 'Event.slug', 'Event.start_date',
            'Event.end_date', 'Venue.name','Venue.slug', 'Venue.address',
            'Country.name', 'Country.slug', 'City.name', 'City.slug', );

        $this->paginate['recursive'] = -1;
        $this->paginate['order'] = 'Event.start_date ASC';
        $conditions = array('Event.published' => true);

        if ($mode == 'posted') {
            $model = 'Event';
            $conditions['Event.user_id'] = $user['User']['id'];
            array_shift($this->paginate['joins']);
        } elseif ($mode == 'past') {
            $conditions['EventsUser.user_id'] = $user['User']['id'];
            $conditions['Event.end_date <'] = date('Y-m-d');
        } else {
            $conditions['EventsUser.user_id'] = $user['User']['id'];
            $conditions['Event.end_date >='] = date('Y-m-d');
        }

        $user['Attendee'] = $this->paginate($model, $conditions);
        if ($user['User']['id'] == $this->Auth->user('id')) {
            $homepage = true;
        } else {
            $homepage = false;
        }
        $this->set('user', $user);
        $this->set('homepage', $homepage);
        $this->set('title_for_layout', __(sprintf("%s's profile", ucfirst($user['User']['username']))));
        $this->set('mode', $mode);
    }

    /**
     * Edit user's profile.
     *
     * @access public
     */
    public function edit()
    {
        $this->User->contain('City');
        $user = $this->User->find('first', array('conditions' => array('User.id' => $this->Auth->user('id'))));
        unset($user['User']['password']);
        if (!empty($this->request->data)) {
            if (isset($this->request->data['User']['delete_photo']) && $this->request->data['User']['delete_photo']) {
                $this->request->data['User']['filedata'] = '';
            }

            if (empty($this->request->data['User']['password'])) {
                unset($this->request->data['User']['password']);
            }
            $this->request->data['User']['id'] = $this->Auth->user('id');
            $this->request->data['User']['username'] = $user['User']['username'];
            $this->request->data['User']['group_id'] = $this->Auth->user('group_id');

            // user changed email and needs confirmation
            if ($user['User']['email'] != $this->request->data['User']['email']
                && Configure::read('evento_settings.validateEmails') == true) {
                $this->request->data['User']['alter_email'] = $this->request->data['User']['email'];
                unset($this->request->data['User']['email']);
                $validationCode = sha1(rand().'-'.time());
                $this->request->data['User']['validation_code'] = $validationCode;
                $this->request->data['User']['validation_date'] = date('Y-m-d H-i-s');
            }

            if ($this->User->save($this->request->data)) {
                $this->User->recursive = -1;
                $user = $this->User->find('first', array('conditions' => array(
                    'id' => $this->Auth->user('id'), )));

                // email confirmation
                if (isset($this->request->data['User']['alter_email']) &&
                Configure::read('evento_settings.validateEmails') == true) {
                    $email = new CakeEmail();
                    $email->viewVars(array(
                        'code' => $user['User']['validation_code'],
                        'user' => $user['User']['username'],
                    ));
                    $email->from(Configure::read('evento_settings.systemEmail'));
                    $email->to($this->request->data['User']['alter_email']);
                    $email->subject(__('Email confirmation'));
                    $email->template('email_confirmation');
                    $email->emailFormat('both');
                    $email->send();

                    $this->set('email_confirmation', $this->request->data['User']['alter_email']);
                } else {
                    $this->redirect(array('action' => 'view', $this->Auth->user('slug')));
                }
            } else {
                //could not save data, reset some variables
                if (isset($this->request->data['User']['alter_email'])) {
                    $this->request->data['User']['email'] = $this->request->data['User']['alter_email'];
                }
                if (!isset($this->request->data['City']['country_id'])) {
                    $this->request->data['City']['country_id'] = null;
                }
            }
        } else {
            $this->request->data = $user;
            unset($this->request->data['User']['id']);
        }
        $this->set('title_for_layout', __('Edit user profile'));
        $this->set('user', $user);
        $this->set('countries', $this->Country->find('countrylist'));
    }

    /**
     * Delete user account after confirmation. It uses delete_user in users model
     * to make sure we delete all user data.
     *
     * @access public
     *
     * @param string $confirmation
     */
    public function delete_user($confirmation = null)
    {
        if ($confirmation != null) {
            $this->User->delete($this->Auth->user('id'), true);
            $this->redirect($this->Auth->logout());
        }
    }

    /**
     * If user forgot the password we create a new url for him
     * to access his account without password.
     *
     * @access public
     */
    public function recover()
    {
        if (!empty($this->request->data)) {
            $email = $this->request->data['User']['email'];
            $this->User->recursive = -1;
            $user = $this->User->find('first', array('conditions' => array('email' => $email),
                'fields' => array('id', 'username', 'email'), ));
            if (!empty($user)) {
                $validationCode = sha1(rand().'-'.time());
                $user['User']['validation_code'] = $validationCode;
                $user['User']['validation_date'] = date('Y-m-d H-i-s');
                if ($this->User->save($user)) {
                    $email = new CakeEmail();
                    $email->viewVars(array(
                        'code' => $validationCode,
                        'user' => $user,
                    ));
                    $email->from(Configure::read('evento_settings.systemEmail'));
                    $email->to($user['User']['email']);
                    $email->subject(__('Password recovery'));
                    $email->template('password_recovery');
                    $email->emailFormat('both');
                    $email->send();
                }
            }
            $this->set('recover', true);
        }
    }

    /**
     * login a user without using password with the random code
     * created with the recover action.
     *
     * The access code is valid for 2 hours only.
     *
     * @access public
     *
     * @param string $code
     */
    public function code_login($code = null)
    {
        if (!$code) {
            throw new NotFoundException();
        }
        $this->User->recursive = -1;
        $user = $this->User->find('first', array('conditions' => array('validation_code' => $code)));
        if (!empty($user) &&
        $user['User']['validation_date']<date('Y-m-d H-i-s', strtotime('+2 hours'))) {
            $this->Auth->login($user['User']);
            $remove_code['User']['id'] = $user['User']['id'];
            $remove_code['User']['validation_code'] = null;
            $remove_code['User']['validation_date'] = null;
            $this->User->save($remove_code);
            $this->redirect(array('action' => 'edit'));
        } else {
            $this->set('code_login', false);
        }
    }

    /**
     * confirm the email address is valid and activate the user account.
     *
     * @param int $code
     */
    public function activation($code = null)
    {
        if (!$code) {
            throw new NotFoundException();
        }
        $this->User->recursive = -1;
        $user = $this->User->find('first', array('conditions' => array('validation_code' => $code)));
        if (!empty($user)
        && $user['User']['validation_date'] < date('Y-m-d H-i-s', strtotime('+24 hours'))) {
            if ($user['User']['alter_email']) {
                $remove_code['User']['email'] = $user['User']['alter_email'];
                $remove_code['User']['alter_email'] = null;
            }
            $remove_code['User']['id'] = $user['User']['id'];
            $remove_code['User']['active'] = 1;
            $remove_code['User']['validation_code'] = null;
            $remove_code['User']['validation_date'] = null;

            if ($this->User->save($remove_code)) {
                if (!$this->Auth->user('id')) {
                    if (!$user['User']['active']) {
                        $this->__sendWelcomeMessage($user);
                    }
                    $this->Auth->login($user['User']);
                }
            }
        } else {
            throw new NotFoundException();
        }
    }

    ////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////  ADMIN
    ////////////////////////////////////////////////////////////////

    /**
     * admin index paginates a list of users.
     *
     * @access public
     */
    public function admin_index()
    {
        $this->set('title_for_layout', __('Manage users'));
        $this->Session->delete('Search.term');
        $this->paginate['fields'] = array('User.id', 'User.username', 'User.slug', 'User.photo', 'User.active');
        $this->set('users', $this->paginate('User'));
    }

    /**
     * Admin delete users.
     *
     * @access public
     *
     * @param int $userId
     */
    public function admin_delete_user($userId = null)
    {
        if ($userId === null) {
            throw new NotFoundException();
        }
        $this->User->recursive = -1;
        $user = $this->User->find('first', array('conditions' => array('id' => $userId), 'fields' => array('id')));
        if (empty($user)) {
            throw new NotFoundException();
        } else {
            $this->User->delete($userId, true);

            $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
            $conditions = null;
            $action = 'index';
            if ($this->Session->read('Search.term')) {
                $action = 'search';
                $term = $this->Session->read('Search.term');
                $conditions = array('or' => array('User.email like' => '%'.$term['Search']['user'].'%',
                    'User.username' => $term['Search']['user'].'%', ));
            }
            if ($page) {
                $count = $this->User->find('count', array('conditions' => $conditions));
                $lastPage = ceil($count / $this->paginate['limit']);
                if ($page > $lastPage) {
                    $page = $lastPage;
                }
            }
            $this->redirect(array('action' => $action, 'page' => $page));
        }
    }

    /**
     * Admin can edit user data.
     *
     * @access public
     *
     * @param int $userId
     */
    public function admin_edit($userId = null)
    {
        if ($userId === null) {
            throw new NotFoundException();
        }
        $this->User->recursive = -1;
        $user = $this->User->find('first', array('conditions' => array('id' => $userId),
            'fields' => array('id', 'username', 'group_id', 'email', 'active', 'photo'), ));

        if (empty($user)) {
            throw new NotFoundException();
        }

        $groups = $this->Group->find('list', array('order' => array('id' => 'DESC')));
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;

        if (!empty($this->request->data)) {
            $this->request->data['User']['id'] = $userId;
            if (!$this->request->data['User']['password']) {
                unset($this->request->data['User']['password']);
                unset($this->request->data['User']['password_confirm']);
            }
            if (isset($this->request->data['User']['delete_photo']) && $this->request->data['User']['delete_photo']) {
                $this->request->data['User']['filedata'] = '';
            }
            if ($this->User->save($this->request->data)) {
                $this->redirect(array('action' => 'index', 'page' => $page));
            } else {
                $this->request->data['User']['photo'] = $user['User']['photo'];
            }
        } else {
            $this->request->data = $user;
        }

        $this->set('groups', $groups);
        $this->set('page', $page);
        $this->set('title_for_layout', __('Edit user'));
    }

    /**
     * export users as plain text file.
     */
    public function admin_export()
    {
        Configure::write('debug', 0);
        $this->layout = 'export';
        $this->response->header('Cache-Control: private');
        $this->response->header('Content-Description: File Transfer');
        $this->response->header('Content-Disposition: attachment; filename=users.csv');
        $this->RequestHandler->respondAs('csv');
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
        $users = $this->User->find('all', array('fields' => array('User.username', 'User.email',
            'User.web', 'City.name', 'Country.name', ), 'joins' => $joins));
        $this->set('users', $users);
    }

    /**
     * allow admin to add new users.
     */
    public function admin_register()
    {
        if (!empty($this->request->data)) {
            $this->request->data['User']['recaptcha'] = 'correct';
            $this->request->data['User']['active'] = 1;
            if ($this->User->save($this->request->data)) {
                $this->redirect(array('action' => 'index'));
            }
        }
        $groups = $this->Group->find('list', array('order' => array('id' => 'DESC')));
        $this->set('groups', $groups);
        $this->set('title_for_layout', __('Add user'));
    }

    /**
     * allow admin to search a user.
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
            $conditions = array('or' => array('User.email like' => '%'.$this->request->data['Search']['user'].'%',
                'User.username' => $this->request->data['Search']['user'].'%', ));
            $this->set('users', $this->paginate('User', $conditions));
        }
        $this->render('admin_index');
    }

    /**
     * bulk manage users.
     */
    public function admin_bulk()
    {
        if (empty($this->request->data)) {
            throw new NotFoundException();
        }
        $ids = array();
        foreach ($this->request->data['User']['id'] as $key => $value) {
            if ($value != 0) {
                $ids[] = $key;
            }
        }
        switch ($this->request->data['User']['option']) {
            case 'activate':
                $this->User->bulkPublish($ids, 1);
                break;
            case 'deactivate':
                $this->User->bulkPublish($ids, 0);
                break;
            case 'delete':
                $this->User->bulkDelete($ids);
                break;
        }
        $this->redirect(array('action' => 'index'));
    }

    /**
     * function to send a welcome message to new users.
     *
     * @param array $user
     */
    private function __sendWelcomeMessage($user)
    {
        $email = new CakeEmail();
        $email->viewVars(array(
            'user' => $user['User']['username'],
        ));
        $email->from(Configure::read('evento_settings.systemEmail'));
        $email->to($user['User']['email']);
        $email->subject(sprintf(__('Welcome to %s'), Configure::read('evento_settings.appName')));
        $email->template('welcome_message');
        $email->emailFormat('both');
        $email->send();
    }

    /**
     * Login a user with facebook.
     */
    private function __facebookLogin()
    {
        $helper = $this->Facebook->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            $this->log('Graph returned an error: '.$e->getMessage(), 'error');
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            $this->log('Facebook SDK returned an error: '.$e->getMessage(), 'error');
        }

        if (isset($accessToken)) {
            $response = $this->Facebook->get('/me?fields=id,name,email', $accessToken);
            $facebookUser = $response->getGraphUser();
            $user = $this->User->find('first', array('conditions' => array('facebook_id' => $facebookUser['id'])));

            if (!$user) {
                $user = $this->User->find('first', array('conditions' => array('email' => $facebookUser['email'])));
                if ($user) {
                    $user['User']['facebook_id'] = $facebookUser['id'];
                    unset($user['User']['password']);
                    $this->User->save($user);
                } elseif (Configure::read('evento_settings.adminAddsUsers') != true) {
                    $user['User'] = array(
                        'username'        => $facebookUser['name'],
                        'email'                => $facebookUser['email'],
                        'password'        => AuthComponent::password(uniqid(md5(mt_rand()))),
                        'facebook_id'    => $facebookUser['id'],
                        'active'            => 1,
                        'group_id'        => 3,
                    );
                    $this->User->save($user, array('validate' => false));
                    $user['User']['id'] = $this->User->getLastInsertID();
                }
            }

            if ($user && $user['User']['active'] == 1) {
                $this->Auth->login($user['User']);
                $this->redirect($this->Auth->redirectUrl());
            }
        }
    }
}
