<?php

class SignedAutoUpdate_TrustedStore
{
    /**
     * @var SignedAutoUpdate_PackageInfo[]
     */
    protected $packages = array();

    /**
     * SignedAutoUpdate_TrustedStore constructor.
     */
    public function __construct()
    {
        if (!file_exists($this->getPath())) {
            $this->store();
        }
        $this->read();
    }

    /**
     *
     */
    public function getKnownPackages()
    {
        return $this->packages;
    }

    /**
     * @param $pluginSlug
     * @param $publicKey
     */
    public function trustPackage($pluginSlug, $publicKey)
    {
        $info = new SignedAutoUpdate_PackageInfo();
        $info->setPluginSlug($pluginSlug)->setFingerPrint($publicKey);
        $this->packages[$info->getPluginSlug()] = $info;
    }

    /**
     * @param $pluginSlug
     */
    public function untrustPackage($pluginSlug) {
        unset($this->packages[$pluginSlug]);
    }

    /**
     * @param $pluginSlug
     * @return bool|SignedAutoUpdate_PackageInfo
     */
    public function getPackageInfo($pluginSlug) {
        return isset($this->packages[$pluginSlug]) ? $this->packages[$pluginSlug] : false;
    }

    /**
     *
     */
    public function store()
    {
        $path = $this->getPath();
        file_put_contents($path, json_encode($this->packages, JSON_PRETTY_PRINT));
    }

    public function getPath() {
        return SignedAutoUpdate::getInstance()->pluginSignatureCacheFile;
    }

    /**
     *
     */
    public function read()
    {
        $content = file_get_contents($this->getPath());
        $data = json_decode($content, true);
        foreach($data as $info) {
            $this->trustPackage($info['pluginSlug'], $info['fingerPrint']);
        }
    }
}