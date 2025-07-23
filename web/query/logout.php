<?php

session_start();

session_destroy();

header('Content-Type: application/json');
$return_data = json_encode(
"ok"
);

echo $return_data;
return;