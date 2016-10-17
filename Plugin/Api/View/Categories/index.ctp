<?php

if ($jsonp) {
    echo $jsonp.'('.json_encode($data).')';
} else {
    echo json_encode($data);
}

?>