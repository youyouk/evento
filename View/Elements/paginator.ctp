<?php
if($this->Paginator->counter(array('format'=>"%pages%")) > 1) {
	echo '<div id="paginator">';
	echo $this->Paginator->prev('« ' . __('Previous') . ' ');
	echo $this->Paginator->numbers(array('separator'=>null));
	echo $this->Paginator->next(' ' . __('Next') . ' »');
	echo '</div>';
}
?>