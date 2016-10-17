<?php

class RecaptchaEventoComponent extends Component
{
  /**
   * @var Controller
   */
  var $Controller = null;

  /**
   * @var int
   */
  var $max = 5;

  /**
   * Initialize the component and setup the paypal plugin.
   *
   * @param Controller $controller
   */
  public function initialize(Controller $controller)
  {
    $this->Controller = $controller;
    $this->Event = ClassRegistry::init('Event');
  }

  /**
   * check if recaptcha should be enabled when adding events
   */
  public function getEventsRecaptchaStatus()
  {
    $recentEvents = $this->Event->find('count', array(
        'conditions' => array('user_id' => $this->Controller->Auth->user('id'),
        'Event.created >=' => date('Y-m-d H:i', strtotime(date('Y-m-d H:i').' -5 minutes')), ),
        'recursive' => -1, ));

    return $this->checkrecaptchaStatus($recentEvents);
  }

  /**
   * check if recaptcha should be enabled when adding comments
   */
  public function getCommentsRecaptchaStatus()
  {
    $recentComments = $this->Event->Comment->find('count', array(
        'conditions' => array('user_id' => $this->Controller->Auth->user('id'),
        'created >=' => date('Y-m-d H:i', strtotime(date('Y-m-d H:i').' -5 minutes')), ),
        'recursive' => -1
      ));

    return $this->checkrecaptchaStatus($recentComments);
  }

  /**
   * Verify recaptcha
   */
  public function verify()
  {
    return $this->recaptcha->verify();
  }

  /**
   * check if recaptcha should be enabled depending on settings and recently added items
   *
   * @param int $recent
   */
  private function checkrecaptchaStatus($recent)
  {
    $useRecaptcha = false;
    if (Configure::read('evento_settings.recaptchaPublicKey')
    && Configure::read('evento_settings.recaptchaPrivateKey')
    && ($recent >= $this->max)) {
        $this->recaptcha = $this->Controller->Components->load('Recaptcha.Recaptcha');
        $this->recaptcha->enabled = true;
        $this->recaptcha->initialize($this->Controller);
        $this->recaptcha->startup($this->Controller);
        $useRecaptcha = true;
    }

    return $useRecaptcha;
  }
}
