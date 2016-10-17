<?php
class TimeformatHelper extends Helper{
	var $helpers = array('Html', 'Text', 'Time');

	/**
	 * get default timeFormat
	 */
	function getTimeFormat() {
		if(Configure::read('evento_settings.timeFormat') == '12') {
			return 'g:ia';
		}
		else {
			return 'H:i';
		}
	}

	/**
	 * format events dates
	 *
	 * @param Date $indexDate
	 * @param bool $time
	 */
	function getFormattedDate($indexDate, $time = true) {
		$dateDay = __($this->Time->format('l', $indexDate));
		$dateMonth = __d('cake', $this->Time->format('F', $indexDate));
		$dateDayNumber = $this->Time->format('j', $indexDate);
		$dateYear = $this->Time->format('Y', $indexDate);
		$dateTime = $this->Time->format($this->getTimeFormat(), $indexDate);
		if($this->Time->format('Y', $indexDate) != date('Y')) {
			if($time) {
				return __('%1$s, %2$s %3$s of %4$s, %5$s.', $dateDay, $dateMonth, $dateDayNumber
					, $dateYear, $dateTime);
			} else {
				return __('%1$s, %2$s %3$s of %4$s.', $dateDay, $dateMonth, $dateDayNumber, $dateYear);
			}
		} else {
			if($time) {
				return __('%1$s, %2$s %3$s, %4$s.', $dateDay, $dateMonth, $dateDayNumber, $dateTime);
			} else {
				return __('%1$s, %2$s %3$s.', $dateDay, $dateMonth, $dateDayNumber);
			}
		}
	}
}
?>