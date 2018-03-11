<?php
/*
Plugin Name:  Signed Autoupdate
Plugin URI:   http://localhost
Description:  SignedAutoUpdate
Version:      0.0.2-prototype
Author:       cloudfest/signed-autoupdate group
Author URI:   https://github.com/Cloudfest/signed-autoupdate
License:      ?
License URI:  ?
Text Domain:  wporg
Domain Path:  /languages
*/


require_once 'class.signed-autoupdate-helpers.php';
require_once 'class.signed-autoupdate.minitemplate.php';
require_once 'class.signed-autoupdate.trusted-store.php';
require_once 'class.signed-autoupdate.package-info.php';
require_once 'class.signed-autoupdate.php';

require_once 'vendor/paragonie/sodium_compat/autoload.php';

$signedAutoupdate = SignedAutoUpdate::getInstance();
$signedAutoupdate->admin_init();