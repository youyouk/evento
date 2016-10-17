<?php
App::uses('CakeEmail', 'Network/Email');

class ContactController extends AppController
{
    public $name = 'Contact';

    /**
     * allow access to index method.
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index');
        parent::beforeFilter();
    }

    /**
     * send the message to the site admin email.
     */
    public function index()
    {
        $useRecaptcha = false;
        if (Configure::read('evento_settings.recaptchaPublicKey')
        && Configure::read('evento_settings.recaptchaPrivateKey')) {
            $recaptcha = $this->Components->load('Recaptcha.Recaptcha');
            $recaptcha->enabled = true;
            $recaptcha->initialize($this);
            $recaptcha->startup($this);
            $useRecaptcha = true;
        }

        if (!empty($this->request->data)) {
            if (!$useRecaptcha || ($useRecaptcha && $recaptcha->verify())) {
                $this->request->data['Contact']['recaptcha'] = 'correct';
            }
            $this->Contact->set($this->request->data);

            if ($this->Contact->validates()) {
                $email = new CakeEmail();
                $email->from(Configure::read('evento_settings.systemEmail'));
                $email->to(Configure::read('evento_settings.adminEmail'));
                $email->replyTo($this->request->data['Contact']['email']);
                $email->subject(__(sprintf('%s contact form', Configure::read('evento_settings.appName')), true));
                $email->send($this->request->data['Contact']['message']);
                $this->set('email_sent', true);
            }
        }
        $this->set('useRecaptcha', $useRecaptcha);
        $this->set('title_for_layout', __('Contact us'));
    }
}
