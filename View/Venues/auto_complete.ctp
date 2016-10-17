<ul class="auto-complete">
<?php
foreach($venues as $venue) {
?>
	<li>
		<p class="venue-name" id="<?php echo $venue['Venue']['id'] ?>">
<?php echo h(ucfirst($venue['Venue']['name'])); ?>
		</p>
		<p class="venue-info">
<?php
	echo $venue['Venue']['address'] . ', ';
	echo $venue['City']['name'] . ', ' . __d('countries', $venue['City']['Country']['name']) . '.';
?>
		</p>
	</li>
<?php
}
if(!Configure::read('evento_settings.adminVenues')) {
?>
	<li id="add-venue">
		<p class="venue-name" id="0"><?php echo __('Add a new venue'); ?><p>
		<p class="venue-info"><?php echo __('Provide the address for a new venue.'); ?></p>
	</li>
<?php } ?>
</ul>

