<?php

class SignedAutoUpdate
{
    /**
     * @var string string
     */
    public $pluginSignatureCacheFile = 'trusted-signatures.json';
    /**
     * @var bool
     */
    protected $adminInitialized = false;
    /**
     * @var SignedAutoUpdate
     */
    protected static $instance = null;

    /**
     * SignedAutoUpdate constructor
     *
     * @return static
     */
    protected function __construct() {
    }

    /**
     * @return SignedAutoUpdate|static
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * register menu, predownload hook
     *
     * @return bool
     */
    public function admin_init()
    {
        if ($this->adminInitialized) {
            return $this->adminInitialized;
        }

        add_action('admin_menu', 'signed_autoupdate_admin_menu');
        add_filter('upgrader_pre_download', 'signed_autoupdate_upgrader_pre_download', 10, 3);
        add_filter('query_vars', 'signed_autoupdate_add_query_vars' );
        add_action('init', 'signed_autoupdate_admin_revoke_handler');
        $this->adminInitialized = true;

        return $this->adminInitialized;
    }
}