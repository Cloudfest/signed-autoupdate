<?php

include_once __DIR__.'/vendor/autoload.php';
include_once __DIR__.'/src/lib/Update.php';

$public_key = hex2bin('f17313cd97a71c61e41012aedae822ec0a88b11ddcdef78dc72fb47b04b80924');
$test = new Update(__DIR__ . '/update-test/update.zip',__DIR__.'/update-deploy',$public_key);
