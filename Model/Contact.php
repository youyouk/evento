<?php
class Contact extends AppModel
{
    /*
     * do not use any database table for this model
     */

    public $useTable = false;

    /*
     * validation
     */

    public $validate = array(
        'email' => array(
            'rule' => 'email',
            'required' => true,
            'message' => 'Please enter a valid email address.', ),
        'message' => array(
            'rule' => 'notBlank',
            'required' => true,
            'message' => 'Please write a message', ),
        'recaptcha' => array(
            'notEmpty'  => array(
                'rule' => 'notBlank',
                'message' => 'Incorrect captcha',
                'required' => true,
            ), ),
    );
}
