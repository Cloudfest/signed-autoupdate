<?php

include_once 'src/lib/Update.php';

$public_key = '';
$test = new Update('http://loaclhost/update/update.zip','/update-test',$public_key);
