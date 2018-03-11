<?php

include_once __DIR__.'/vendor/autoload.php';

//Your Public Key you want to use to verify the package.
$public_key = hex2bin('f17313cd97a71c61e41012aedae822ec0a88b11ddcdef78dc72fb47b04b80924');


$test = new Update('https://example.com/update.zip',__DIR__.'/update-deploy',$public_key);
