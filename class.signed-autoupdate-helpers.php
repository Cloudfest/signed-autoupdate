<?php


if (!function_exists('signed_autoupdate_upgrader_pre_download')) {
    /**
     * hooked into: upgrader_pre_download via add_filter
     *
     * @param bool $false
     * @param string $downloadUrl
     * @param WP_Upgrader $wpUpgrader
     *
     * @return bool|string
     */
    function signed_autoupdate_upgrader_pre_download($false = false, $package = null, $wpUpgrader = null) {
        $workingFolder = signed_autoupdate_get_temp_directory();
        $info = pathinfo($package, PATHINFO_FILENAME);

        if (!preg_match('/([^.]+)\.([0-9\.]+)$/', $info, $res)) {
            throw new Exception('unsupported download format, can not parse resulting unzip folder from: ' . $info);
        }
        $pluginKey = $res[1];
        $workingFolder .=  $pluginKey;


        $wpUpgrader->skin->feedback( 'downloading_package', $package );
        $download_file = download_url( $package );

        if ( is_wp_error( $download_file ) ) {
            return new WP_Error( 'download_failed', $this->strings['download_failed'], $download_file->get_error_message() );
        }

        $store = new SignedAutoUpdate_TrustedStore();
        $publicKey = false;
        if ($packageInfo = $store->getPackageInfo($pluginKey)) {
            $publicKey = $packageInfo->getFingerPrint();
        }

        $result = unzip_file($download_file, $workingFolder);


        if ($result instanceof WP_Error) {
            return $result;
        }


        $publicKeyFromPackage = false;
        $signatureFromPackage = false;
        $filesFromPackage = false;

        $requiredFiles = array();
        $requiredFiles['signatureFromPackage'] = '/.well-known/signature.txt';
        $requiredFiles['publicKeyFromPackage'] = '/.well-known/publickey.txt';
        $requiredFiles['filesFromPackage'] = '/.well-known/list.json';

        foreach($requiredFiles as $var => $file) {
            if(file_exists($workingFolder . $file)) {
                $$var = file_get_contents($workingFolder . $file);
            } else {
                $wpUpgrader->skin->feedback('package has not all required files for using signed-autoupdate feature');
                return $download_file;
            }
        }


        if ($publicKey && $publicKeyFromPackage == $publicKey) {
            $wpUpgrader->skin->feedback('verification ok, public key: ' . $publicKey);
            try {
                //@todo check also the files list contents sha hashes
                ParagonIE_Sodium_Compat::crypto_sign_verify_detached(hex2bin($signatureFromPackage), $filesFromPackage, hex2bin($publicKeyFromPackage));
                $wpUpgrader->skin->feedback('verification ok, signature with public key matches files list');
            } catch(Exception $e) {
                $wpUpgrader->skin->feedback('could not verify signature, error: ' . $e->getMessage());
            }
            return $download_file;
        } else if ($publicKey) {
            $error = new WP_Error();
            $error->add('verification_failed','public key mismatch');
            return $error;
        }

        if (!$publicKey) {
            $wpUpgrader->skin->feedback('adding new public key to trust storage: ' . $publicKeyFromPackage);
            $store->trustPackage($pluginKey, $publicKeyFromPackage);
            $store->store();
        }

        return $download_file;
    }
}

if (!function_exists('signed_autoupdate_get_temp_directory')) {
    function signed_autoupdate_get_temp_directory() {
        $tempDirectory = sys_get_temp_dir() . '/signed-autoupdate-temp/';
        if (!is_dir($tempDirectory)) {
            mkdir($tempDirectory);
        }
        return $tempDirectory;
    }
}

if (!function_exists('signed_autoupdate_admin_menu')) {
    function signed_autoupdate_admin_menu() {
        add_menu_page(
            'SignedAutoUpdater Plugin Page',
            'SAU Signatures',
            'manage_options',
            'signed-autoupdate',
            'signed_autoupdate_menu_signatures' );
    }
}

if(!function_exists('signed_autoupdate_admin_revoke_handler')) {
    function signed_autoupdate_admin_revoke_handler()
    {
        $trustedStore = new SignedAutoUpdate_TrustedStore();
        $revoke = isset($_REQUEST['revoke']) ? $_REQUEST['revoke'] : false;

        if ($revoke) {
            $trustedStore->untrustPackage($revoke);
            $trustedStore->store();
            wp_redirect(admin_url('admin.php?page=signed-autoupdate'));
            exit;
        }
        $change = $revoke = isset($_REQUEST['edit']) ? $_REQUEST['edit'] : false;
        $newValue = $revoke = isset($_REQUEST['new']) ? $_REQUEST['new'] : false;
        if ($change) {
            $trustedStore->trustPackage($change, $newValue);
            $trustedStore->store();
            wp_redirect(admin_url('admin.php?page=signed-autoupdate'));
            exit;
        }
    }
}

if (!function_exists('signed_autoupdate_menu_signatures')) {
   function signed_autoupdate_menu_signatures() {
       $template = new SignedAutoUpdate_MiniTemplate(__DIR__.'/templates/list-packages-and-keys.php');
        $trustedStore = new SignedAutoUpdate_TrustedStore();

        $template->packageList = $trustedStore->getKnownPackages();;
        echo $template;

   }
}
