<?php

if ($jsonp) {
    echo $jsonp.'('.json_encode($event).')';
} else {
    echo json_encode($event);
}

?>