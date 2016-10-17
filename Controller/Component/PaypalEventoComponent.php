<?php

App::uses('Paypal', 'Paypal.Lib');

class PaypalEventoComponent extends Component
{

  /**
   * @var Controller
   */
  var $controller = null;

  /**
   * @var Paypal
   */
  var $Paypal = null;

  /**
   * Initialize the component and setup the paypal plugin.
   *
   * @param Controller $controller
   */
  public function initialize(Controller $controller)
  {
    $this->controller = $controller;
  }

  /**
   * init the paypal plugin
   */
  public function initPaypal()
  {
    if ($this->Paypal == null) {
      $this->Paypal = new Paypal(array(
          'sandboxMode'  => Configure::read('evento_settings.paypalAPISandbox'),
          'nvpUsername'  => Configure::read('evento_settings.paypalAPIUsername'),
          'nvpPassword'  => Configure::read('evento_settings.paypalAPIPassword'),
          'nvpSignature' => Configure::read('evento_settings.paypalAPISignature')
      ));
    }
  }

  /**
   * Check if Paypal payments are enabled.
   *
   * @return bool
   */
  public function isPaypalEnabled()
  {
    $username  = Configure::read('evento_settings.paypalAPIUsername');
    $password  = Configure::read('evento_settings.paypalAPIPassword');
    $signature = Configure::read('evento_settings.paypalAPISignature');
    return !(empty($username) || empty($password) || empty($signature));
  }

  /**
   * Get the pay url to publish an event.
   * In case a photo has been added to the event it is persisted copying the file to the tmp folder
   * so it is available when the user comes back from Paypal.
   *
   * @param array $event
   * @return string
   */
  public function checkout($eventData)
  {
    if (!empty($eventData['Event']['filedata'])) {
      $persist = TMP.basename($eventData['Event']['filedata']['tmp_name']);
      copy($eventData['Event']['filedata']['tmp_name'], $persist);
      $eventData['Event']['filedata']['tmp_name'] = $persist;
    }
    $this->initPaypal();
    CakeSession::write('paypalEventData', $eventData);
    $order = $this->setOrder();
    $url = $this->getUrl($order);
    $this->controller->redirect($url);
  }

  /**
   * Do paypal payment
   */
  public function doPayment()
  {
    $this->initPaypal();
    if ($this->isPaypalRequest()) {
      $order = CakeSession::read('paypalOrder');
      try {
          $this->Paypal->doExpressCheckoutPayment($order, $this->getPaypalToken(), $this->getPaypalPayerId());
      } catch (PaypalRedirectException $e) {
          $this->controller->redirect($e->getMessage(), 'debug');
      } catch (Exception $e) {
          $this->log($e->getMessage(), 'debug');
          return false;
      }
    }

    return true;
  }

  /**
   * Get the paypal event data stored in session.
   *
   * @return array
   */
  public function getEventData()
  {
    return CakeSession::read('paypalEventData');
  }

  /**
   * Check if the request contains the paypal token and payerid parameters.
   *
   * @return bool
   */
  public function isPaypalRequest()
  {
    return ($this->getPaypalToken() && $this->getPaypalPayerId());
  }

  /**
   * Return the price of publishing an event
   *
   * @return int
   */
  public function getPublishPrice()
  {
    return Configure::read('evento_settings.paypalAddEventPrice');
  }

  /**
   * Return the Paypal currency being used
   *
   * @return string
   */
  public function getCurrency()
  {
    return Configure::read('evento_settings.paypalCurrency');
  }

  /**
   * Return the paypal token or false.
   *
   * @return string || bool
   */
  private function getPaypalToken()
  {
    return isset($this->controller->params->query['token']) ? $this->controller->params->query['token'] : false;
  }

  /**
   * Return paypal payid or null.
   *
   * @return string || bool
   */
  private function getPaypalPayerId()
  {
    return isset($this->controller->params->query['PayerID']) ? $this->controller->params->query['PayerID'] : false;
  }

  /**
   * Set an order saving it into the session.
   *
   * @return array
   */
  private function setOrder()
  {
    $order = array(
        'description' => __('Publish your event at %s', Configure::read('evento_settings.appName')),
        'currency' => Configure::read('evento_settings.paypalCurrency'),
        'return' => Router::url(array('controller'=>'events', 'action'=>'add'), true),
        'cancel' => Router::url('/', true),
        'shipping' => '0',
        'items' => array(
            0 => array(
                'name' => __('Publish your event at %s', Configure::read('evento_settings.appName')),
                'description' => __('Publish your event at %s', Configure::read('evento_settings.appName')),
                'tax' => 0,
                'subtotal' => Configure::read('evento_settings.paypalAddEventPrice'),
                'qty' => 1,
            ),
        )
    );
    CakeSession::write('paypalOrder', $order);

    return $order;
  }

  /**
   * get the paypal url
   */
  private function getUrl($order)
  {
    try {
      $url = $this->Paypal->setExpressCheckout($order);
    } catch (Exception $e) {
      $this->log($e->getMessage(), 'debug');
      return false;
    }

    return $url;
  }

}
