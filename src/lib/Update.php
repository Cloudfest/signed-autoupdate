<?php
/**
 * Created by PhpStorm.
 * User: Omegatcu
 * Date: 10.03.2018
 * Time: 17:41
 */

class Update
{
    public $algorithm = 'sha1';
    public $updateDir = '';
    public $tempDir = '';
    public $publicKey = '';


    public function __construct($updateURL, $updateDir, $publicKey)
    {
        $this->setTempDir($updateDir.'/tmp');
        $this->setUpdateDir($updateDir);
        $this->setPublicKey($publicKey);
        $this->ProcessUpdate($updateURL);
    }

    /**
     * @param string $algorithm
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * Set Public Key for decryption
     * @param string $publicKey
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * @param string $updateDir
     */
    public function setUpdateDir($updateDir)
    {
        $this->updateDir = $updateDir;
    }

    /**
     * @param string $tempDir
     */
    public function setTempDir($tempDir)
    {
        $this->tempDir = $tempDir;
    }

    /**
     * Get the update file
     * @param string/url $download_url
     * @return int|bool The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function download($download_url)
    {
        $filepath = $this->tempDir.'/update.zip';
        file_put_contents($filepath,file_get_contents($download_url));
        return $filepath;
    }

    public function extract($zipfile,$destination)
    {
        $zip = new ZipArchive;
        $res = $zip->open($zipfile);
        if ($res === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load the verification file from the temp dir to decrypt
     * @param $file
     * @return bool|string
     */
    public function load_verification_file($file)
    {
        if(file_exists($file)){
            echo 'file does not exist';
        }
        return $this->verify();
    }

    /**
     * @return bool if signature was valid
     */
    public function verify(){
        $signature = hex2bin(file_get_contents($this->tempDir.'/signature.txt'));
        $listfile = file_get_contents($this->tempDir.'/list.json');
        $publicKey = $this->publicKey;

        if(function_exists('sodium_crypto_sign_verify_detached')){
            if (!sodium_crypto_sign_verify_detached($signature, $listfile, $publicKey)) {
                $this->failed_signature();
            }
        }elseif(class_exists('ParagonIE_Sodium_Compat')){
            //require the libsodium-compat would also be possible because libsodium-compat uses php libsodium if installed
            if (!ParagonIE_Sodium_Compat::crypto_sign_verify_detached($signature, $listfile, $publicKey)) {
                $this->failed_signature();
            }
        }else{
            //no verification possible so clean up and throw error.
            $this->cleanup();
            die('Libsoddium not loaded');
        }
        return $listfile;
    }

    /**
     * gets triggered when the verification file couldn't get decrypted or a signature fails
     * @return string
     */
    public function failed_signature()
    {
        $this->cleanup();
        die("Invalid signature");
    }

    /**
     * gets triggered when the file count is not the same as in verification file
     * @return string
     */
    public function failed_count()
    {
        $this->cleanup();
        die("Invalid count of files");
    }

    /**
     * Remove all data from update
     */
    public function cleanup()
    {
        unlink($this->tempDir);
    }


    /**
     * Check the integrity of the Zipfile
     * @param $signed_file_list
     * @return bool
     */
    public function check_integrety($signed_file_list)
    {
        $signatures = json_decode($signed_file_list);
        $this->setAlgorithm($signatures['algorithm']);

        if ($this->countFiles($this->tempDir) !== count($signatures['signatures'])){
            $this->failed_count();
        }

        foreach($signatures['signatures'] as $fileinfo){
            $this->compare($fileinfo['file'],$fileinfo['hash']);
        }

        return true;
    }

    /**
     * Count files in a given directory
     * @param $dir
     * @return int file count
     */
    public function countFiles($dir)
    {
        $dir = opendir($dir);
        $i = 0;
        while (false !== ($file = readdir($dir))) {
            if (!in_array($file, array('.', '..')) && !is_dir($file) ){ $i++;}
        }
        return $i;
    }

    /**
     * Compare signatures of verification file with hashes
     * @param $file
     * @param $signature
     * @return bool|string
     */
    public function compare($file,$signature)
    {
        if(hash_file($this->algorithm,$file) !== $signature){
            return $this->failed_signature();
        }
        return true;
    }

    /**
     * Process the update from a given URL
     * @param $url
     * @return bool|string
     */
    public function ProcessUpdate($url)
    {
        $zipfilePath = $this->download($url);
        $this->extract($zipfilePath,$this->tempDir);
        $verificationFile = $this->verify();
        $this->check_integrety($verificationFile);
        $this->extract($zipfilePath,$this->updateDir);
        $this->cleanup();
        return true;
    }
}