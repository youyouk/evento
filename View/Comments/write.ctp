<?php
if(isset($comment)) {
	echo $this->Element('comment_post', array('comment'=>$comment, 'new'=>true));
}
echo $this->Element('comment_form');
?>