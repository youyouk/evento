<p class="event-when">
	<abbr class="dtstart" title="<?php echo $event['Event']['start_date']?>">
<?php 
	echo $this->Timeformat->getFormattedDate($event['Event']['start_date']);
?>
	</abbr>
	<abbr class="dtend" title="<?php echo $event['Event']['end_date']?>">
<?php
	if($this->Time->format('dmY',$event['Event']['end_date'])
	!= $this->Time->format('dmY',$event['Event']['start_date'])) {
		echo ' ' . __('Until %s', $this->Timeformat->getFormattedDate($event['Event']['end_date']));
	} else if($this->Time->format($this->Timeformat->getTimeFormat(),$event['Event']['end_date']) !=
			$this->Time->format($this->Timeformat->getTimeFormat(),$event['Event']['start_date'])) {
				echo ' ' . __('To %s', $this->Time->format($this->Timeformat->getTimeFormat(),
					$event['Event']['end_date']));
	}
?>
	</abbr>
</p>
