<p>404 - <?php echo __d('cake', 'Not Found') ?></p>
<p id="logo">
			<?php
			echo $this->Html->link(Configure::read('evento_settings.appName'), Router::url('/', true),
				array('rel'=>'home', 'escape'=>false));
		?>
</p>