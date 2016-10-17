<?php
class CommentsController extends AppController
{
    public $name = 'Comments';
    public $helpers = array('Html', 'Form', 'Time', 'Js');
    public $uses = array('Comment');
    public $paginate = array('limit' => 15, 'order' => array('Comment.created' => 'desc'));

    /**
     * if comments are disabled throw a 404 error.
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        if (Configure::read('evento_settings.disableComments') == 1) {
            throw new NotFoundException();
        }
    }

    /**
     * Write a comment for the event with id $eventId.
     *
     * @param $eventId int
     */
    public function write($eventId = null)
    {
        if (!$eventId) {
            throw new NotFoundException();
        }
        $this->Comment->Event->recursive = -1;
        $event = $this->Comment->Event->find('first', array('conditions' => array('Event.id' => $eventId,
            'Event.published' => true, )));
        if (empty($event)) {
            throw new NotFoundException();
        }

        $this->layout = 'ajax';
        $useRecaptcha = false;

        if (Configure::read('evento_settings.recaptchaPublicKey')
        && Configure::read('evento_settings.recaptchaPrivateKey')) {
            $this->Comment->recursive = -1;
            $recentComments = $this->Comment->find('count',     array(
                'conditions' => array('user_id' => $this->Auth->user('id'),
                'created >=' => date('Y-m-d H:i', strtotime(date('Y-m-d H:i').' -5 minutes')), ), ));
            if ($recentComments >= 5) {
                $recaptcha = $this->Components->load('Recaptcha.Recaptcha');
                $recaptcha->enabled = true;
                $recaptcha->initialize($this);
                $recaptcha->startup($this);
                $useRecaptcha = true;
                $this->params['isAjax'] = true;
            }
        }

        if (!empty($this->request->data)) {
            $saved = false;
            if (!$useRecaptcha || ($useRecaptcha && $recaptcha->verify())) {
                $this->request->data['Comment']['recaptcha'] = 'correct';
            }
            if ($this->Comment->saveComment($this->request->data, $eventId, $this->Auth->user('id'))) {
                $saved = true;
                $this->request->data = null;
                $this->Comment->contain(array('User'));
                $comment = $this->Comment->find('first',
                    array('conditions' => array('Comment.id' => $this->Comment->getLastInsertID())));
                $this->set('comment', $comment);
            }
        }

        $this->set('useRecaptcha', $useRecaptcha);
        $this->set('event', $event);
    }

    ////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////  ADMIN
    ////////////////////////////////////////////////////////////////////

    /**
     * Show all comments to admin joining tables to get required data for events and users links.
     *
     * @param int $eventId
     */
    public function admin_index($eventId = null)
    {
        $this->Session->delete('Search.term');
        $this->paginate['joins'] = array(
            array(
                'table' => 'users',
                'alias' => 'User',
                'type' => 'left',
                'conditions' => array('User.id = Comment.user_id'),
            ),
            array(
                'table' => 'events',
                'alias' => 'Event',
                'type' => 'left',
                'conditions' => array('Event.id = Comment.event_id'),
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
            ), );
        $this->paginate['fields'] = array('User.username', 'User.slug', 'User.photo', 'Event.slug', 'Event.name',
            'Venue.slug', 'City.slug', 'Country.slug', 'Comment.id', 'Comment.comment', );
        $conditions = array();
        if ($eventId !== null) {
            $conditions['Comment.event_id'] = $eventId;
        }

        $this->set('title_for_layout', __('Manage comments'));
        $this->set('event', $eventId);
        $this->set('comments', $this->paginate('Comment', $conditions));
    }

    /**
     * Edit comment with id $commentId.
     *
     * @param int $commentId
     */
    public function admin_edit($commentId = null)
    {
        if ($commentId === null) {
            throw new NotFoundException();
        }
        $comment = $this->Comment->find('first', array('conditions' => array('Comment.id' => $commentId)));
        if (!$comment) {
            throw new NotFoundException();
        }

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        if (!empty($this->request->data)) {
            $this->request->data['Comment']['id'] = $commentId;
            if ($this->Comment->save($this->request->data)) {
                $action = 'index';
                if ($this->Session->read('Search.term')) {
                    $action = 'search';
                }
                $this->redirect(array('action' => $action, 'page' => $page));
            }
        }
        $this->set('page', $page);
        if (empty($this->request->data)) {
            $this->request->data = $comment;
        }
    }

    /**
     * Delete comment with id $commentId.
     *
     * @param int $commentId
     */
    public function admin_delete($commentId = null)
    {
        if ($commentId === null) {
            throw new NotFoundException();
        }
        $this->Comment->delete($commentId);

        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        $conditions = null;
        $action = 'index';
        if ($this->Session->read('Search.term')) {
            $action = 'search';
            $term = $this->Session->read('Search.term');
            $conditions = array('Comment.comment like' => '%'.$term['Search']['comment'].'%');
        }
        if ($page) {
            $count = $this->Comment->find('count', array('conditions' => $conditions));
            $lastPage = ceil($count / $this->paginate['limit']);
            if ($page > $lastPage) {
                $page = $lastPage;
            }
        }
        $this->redirect(array('action' => $action, 'page' => $page));
    }

    /**
     * allow admin to search comments.
     *
     * @param int $eventId
     */
    public function admin_search($eventId = null)
    {
        $this->set('title_for_layout', __('Search'));
        $data = $this->Session->read('Search.term');
        if (!empty($this->request->data) || !empty($data)) {
            if ($this->request->data) {
                $this->Session->write('Search.term', $this->request->data);
            } else {
                $this->request->data = $data;
            }
            $conditions = array('Comment.comment like' => '%'.$this->request->data['Search']['comment'].'%');
            $this->paginate['joins'] = array(
                array(
                    'table' => 'users',
                    'alias' => 'User',
                    'type' => 'left',
                    'conditions' => array('User.id = Comment.user_id'),
                ),
                array(
                    'table' => 'events',
                    'alias' => 'Event',
                    'type' => 'left',
                    'conditions' => array('Event.id = Comment.event_id'),
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
                ), );
            $this->paginate['fields'] = array('User.username', 'User.slug', 'User.photo', 'Event.slug', 'Event.name', 'Venue.slug', 'City.slug', 'Country.slug', 'Comment.id', 'Comment.comment');
            if ($eventId !== null) {
                $conditions['Comment.event_id'] = $eventId;
            }
            $this->set('comments', $this->paginate('Comment', $conditions));
        }
        $this->set('event', $eventId);
        $this->render('admin_index');
    }

    /**
     * bulk manage comments.
     */
    public function admin_bulk()
    {
        if (empty($this->request->data)) {
            throw new NotFoundException();
        }
        $ids = array();
        foreach ($this->request->data['Comment']['id'] as $key => $value) {
            if ($value != 0) {
                $ids[] = $key;
            }
        }
        $this->Comment->bulkDelete($ids);
        $this->redirect(array('action' => 'index'));
    }
}
