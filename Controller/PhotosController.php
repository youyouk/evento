<?php
class PhotosController extends AppController
{
    public $name = 'Photos';
    public $uses = array('Photo','Event');
    public $helpers = array('Html', 'Time', 'Js');
    public $paginate = array('limit' => 30, 'order' => array('Event.created' => 'desc'));

    /**
     * overload beforeFilter
     * throw 404 error if photos are disabled in admin panel.
     */
    public function beforeFilter()
    {
        $this->Auth->allow('view');
        parent::beforeFilter();
        if (Configure::read('evento_settings.disablePhotos') == 1) {
            throw new NotFoundException();
        }
    }

     /**
      * Manage an event gallery.
      */
     public function manage($eventSlug = null)
     {
         if (!$eventSlug) {
             throw new NotFoundException();
         }
         $this->Event->contain(array('Category', 'Venue' => array('City' => array('Country'))));
         $event = $this->Event->find('first', array(
            'conditions' => array('Event.published' => true,
            'Event.slug' => $eventSlug, 'Event.user_id' => $this->Auth->user('id'), ), ));
         if (!$event) {
             throw new NotFoundException();
         }
         $photos = $this->Photo->find('all', array('conditions' => array('Photo.event_id' => $event['Event']['id'])));
         $this->set('photos', $photos);
         $this->set('event', $event);
     }

    /**
     * Only registered users can upload photos.
     *
     * @param string $$eventSlug
     */
    public function upload($eventSlug = null)
    {
        if (!$eventSlug) {
            throw new NotFoundException();
        }
        $this->Event->contain(array('Venue' => array('City' => array('Country'))));
        $event = $this->Event->find('first', array(
            'conditions' => array('Event.slug' => $eventSlug, 'Event.user_id' => $this->Auth->user('id')), ));
        if (!$event) {
            throw new NotFoundException();
        }
        if (!empty($this->request->data)) {
            $this->request->data['Photo']['event_id'] = $event['Event']['id'];
            $this->request->data['Photo']['user_id'] = $this->Auth->user('id');
            if ($this->Photo->save($this->request->data)) {
                $this->redirect(array('controller' => 'photos', 'action' => 'manage', $event['Event']['slug']));
            }
        }
        $this->set('event', $event);
    }

    /**
     * Only the user who uploaded the photo can delete it.
     *
     * @param int $photoId
     */
    public function delete($photoId = null)
    {
        if (!$photoId) {
            throw new NotFoundException();
        }
        if ($photo = $this->Photo->deletePhoto($photoId, $this->Auth->user('id'))) {
            $this->redirect(array('controller' => 'photos', 'action' => 'manage', $photo['Event']['slug']));
        } else {
            throw new NotFoundException();
        }
    }

    /**
     * get a page of photo thumbnails for an event
     * used via ajax.
     *
     * @param int $eventId
     * @param int $page
     */
    public function page($eventId, $page)
    {
        $this->Event->recursive = -1;
        $event = $this->Event->find('first', array('conditions' => array('id' => $eventId)));
        if (empty($event)) {
            throw new NotFoundException();
        }

        $next = false;
        $prev = (($page-1) >= 0) ? ($page-1) : false;
        $this->layout = 'ajax';
        $offset = 20 * $page;

        $photos = $this->Photo->find('all',
            array('conditions' => array('event_id' => $eventId), 'order' => 'Photo.created DESC',
            'limit' => 21, 'offset' => $offset, ));
        if (count($photos) > 20) {
            $photos = array_slice($photos, 0, 20);
            $next = $page + 1;
        }
        $this->set('next', $next);
        $this->set('prev', $prev);
        $this->set('photos', $photos);
        $this->set('event', $event);
    }
    ///////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////// ADMIN
    ///////////////////////////////////////////////////////////////////

    /**
     * Show all photos to admin if $eventId is null, otherwise show photos for that specific event.
     *
     * @param int $eventId
     */
    public function admin_index($eventId = null)
    {
        $this->set('title_for_layout', __('Manage photos'));
        $this->paginate['contain'] = array(
            'Event' => array(
                'name',
                'slug',
                'Venue' => array(
                    'slug',
                    'City' => array(
                        'slug',
                        'Country' => array('slug'),
        ), ), ), );
//		$this->paginate['fields'] = array('Photo.id', 'Photo.file', 'Event.slug', 'Event.Venue.slug', 'City.slug', 'Country.slug');
        $conditions = array();
        if ($eventId !== null) {
            $conditions['Photo.event_id'] = $eventId;
        }
        $this->set('event', $eventId);
        $this->set('photos', $this->paginate('Photo', $conditions));
    }

    /**
     * admin can delete photos uploaded by users.
     *
     * @param int $photoId
     */
    public function admin_delete($photoId = null)
    {
        if (!$photoId) {
            throw new NotFoundException();
        }
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : null;
        $this->Photo->delete($photoId);
        if ($page) {
            $count = $this->Photo->find('count');
            $lastPage = ceil($count / $this->paginate['limit']);
            if ($page > $lastPage) {
                $page = $lastPage;
            }
        }
        $this->redirect(array('action' => 'index', 'page' => $page));
    }
}
