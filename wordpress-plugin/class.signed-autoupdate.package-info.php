<?php

class SignedAutoUpdate_PackageInfo implements JsonSerializable
{
    /**
     * @var string
     */
    protected $pluginSlug;
    /**
     * @var string
     */
    protected $fingerPrint;

    /**
     * @return string
     */
    public function getPluginSlug()
    {
        return $this->pluginSlug;
    }

    /**
     * @param string $pluginSlug
     * @return SignedAutoUpdate_PackageInfo
     */
    public function setPluginSlug($pluginSlug)
    {
        $this->pluginSlug = $pluginSlug;
        return $this;
    }

    /**
     * @return string
     */
    public function getFingerPrint()
    {
        return $this->fingerPrint;
    }

    /**
     * @param string $fingerPrint
     * @return SignedAutoUpdate_PackageInfo
     */
    public function setFingerPrint($fingerPrint)
    {
        $this->fingerPrint = $fingerPrint;
        return $this;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return array(
            'pluginSlug' => $this->pluginSlug,
            'fingerPrint' => $this->fingerPrint
        );
    }




}