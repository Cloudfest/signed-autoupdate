<?php
/*
Plugin Name:  Signed Autoupdate
Plugin URI:   http://localhost
Description:  SignedAutoUpdate
Version:      0.0.1-prototype
Author:       cloudfest/signed-autoupdate group
Author URI:   https://github.com/Cloudfest/signed-autoupdate
License:      ?
License URI:  ?
Text Domain:  wporg
Domain Path:  /languages
*/

/**
 * @param bool $false
 * @param string $downloadUrl
 * @param WP_Upgrader $wpUpgrader
 *
 * @return bool|string
 */
function signed_autoupdate_upgrader_pre_download($false = false, $package = null, $wpUpgrader = null) {
    $folder = __DIR__ . '/temp/';
    $id = md5($package);

    if (file_exists($folder . '/' . $id)) {
        return $folder . '/' . $id;
    }
    $wpUpgrader->skin->feedback( 'downloading_package', $package );
    $download_file = download_url( $package );

    if ( is_wp_error( $download_file ) ) {
        return new WP_Error( 'download_failed', $this->strings['download_failed'], $download_file->get_error_message() );
    }

    //verification
    //@todo add real implementation here

    //md5 is the one from plugin: Shortcake (Shortcode UI)
    if (md5_file($download_file) != '041bc6c6097e709502c0d4855a069913') {
        unlink($download_file);
        return new WP_Error('download_failed', 'download package verification failed');
    }

    return $download_file;
}

add_filter('upgrader_pre_download', 'signed_autoupdate_upgrader_pre_download', 10, 3);