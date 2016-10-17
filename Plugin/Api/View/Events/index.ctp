<?php

if ($jsonp) {
    echo $jsonp.'('.json_encode($events).')';
} else {
    echo json_encode($events);
}

?>