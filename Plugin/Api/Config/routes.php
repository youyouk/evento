<?php

Router::connect('/api/events', array('plugin' => 'api' ,'controller' => 'events', 'action'=>'index'));
Router::connect('/api/events/:id', array('plugin' => 'api' ,'controller' => 'events', 'action'=>'view'), 
array('pass' => array('id'), 'id' => '[0-9]+'));

Router::connect('/api/categories', array('plugin' => 'api', 'controller' => 'categories', 'action' => 'index'));
Router::connect('/api/countries', array('plugin' => 'api', 'controller' => 'countries', 'action' => 'index'));
Router::connect('/api/cities', array('plugin' => 'api', 'controller' => 'cities', 'action' => 'index'));
Router::connect('/api/venues', array('plugin' => 'api', 'controller' => 'venues', 'action' => 'index'));