<?php echo sprintf(__('Welcome to %s'), Configure::read('evento_settings.appName'));; ?>

<?php echo sprintf(__('You just signed up for a new account at %1$s with user name %2$s.'), Router::url('/', true), $user); ?>