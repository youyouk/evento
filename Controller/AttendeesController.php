<?php
class AttendeesController extends AppController
{
    public $name = 'Attendees';
    public $uses = array('Event', 'User');
    public $components = array('RequestHandler');

    /**
     * add attendees to the event.
     * throw 404 error if attendees is disabled in admin panel.
     *
     * @access public
     *
     * @param int  $eventId
     * @param bool $isGoing
     */
    public function attend($eventId = null, $isGoing = null)
    {
        if (Configure::read('evento_settings.disableAttendees') == 1
        || $eventId === null || $isGoing === null) {
            throw new NotFoundException();
        }

        $this->Event->contain(array('Venue' => array('City' => array('Country'))));
        $event = $this->Event->find('first', array('conditions' => array('Event.id' => $eventId)));
        if (empty($event)) {
            throw new NotFoundException();
        }

        if ($this->Event->attendee($eventId, $isGoing, $this->Auth->user('id'))) {
            $this->User->contain(array('City' => array('Country')));
            $user = $this->User->find('first', array('conditions' => array('User.id' => $this->Auth->user('id'))));
            if ($this->RequestHandler->isAjax()) {
                $this->response->type('json');
                $event['Event']['id'] = $eventId;
                $this->set('isAttendee', $isGoing);
                $this->set('event', $event);
                $this->set('attendee', $user);
                $this->layout = 'ajax';

                return;
            }
        }

        $this->redirect(array('controller' => 'events', 'action' => 'view',
            $event['Venue']['City']['Country']['slug'], $event['Venue']['City']['slug'],
            $event['Venue']['slug'], $event['Event']['slug'], ));
    }
}
