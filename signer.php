#!/usr/bin/env php
<?php
require __DIR__.'/vendor/autoload.php';

$app = new \Application\SignedAutoupdate("Signer");
$app->add(new \Command\SignedAutoupdate\Signer);
$app->add(new \Command\SignedAutoupdate\Generator);

$app->run();
