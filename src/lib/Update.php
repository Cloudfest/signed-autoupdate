<?php
/**
 * Created by PhpStorm.
 * User: Omega
 * Date: 10.03.2018
 * Time: 17:41
 */

class Update
{
    public $algo = 'sha1';
    public $update_dir = '';
    public $temp_dir = '';
    public $public_key = '';


    public function __construct()
    {

    }

    /**
     * @param string $algo
     */
    public function setAlgo($algo)
    {
        $this->algo = $algo;
    }

    /**
     * Set Public Key for decryption
     * @param string $public_key
     */
    public function setPublicKey($public_key)
    {
        $this->public_key = $public_key;
    }

    /**
     * @param string $update_dir
     */
    public function setUpdateDir($update_dir)
    {
        $this->update_dir = $update_dir;
    }

    /**
     * @param string $temp_dir
     */
    public function setTempDir($temp_dir)
    {
        $this->temp_dir = $temp_dir;
    }

    /**
     * Get the update file
     * @param string/url $download_url
     * @return int|bool The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function download($download_url)
    {
        $filepath = $this->temp_dir.'/update.zip';
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
     * Load the veryfication file from the temp dir to decrypt
     * @param $file
     * @return bool|string
     */
    public function load_verification_file($file)
    {
        return $this->decrypt_file_content(file_get_contents($this->temp_dir.$file));
    }

    /**
     * Decrypt the veryfication file with libsodium
     * @param $file_content
     */
    public function decrypt_file_content($file_content)
    {
        $signed_file_list = sodium_crypto_sign_open(
            $file_content,
            $this->public_key
        );
        if ($signed_file_list === false) {
            $this->failed_signature();
        }
        return $signed_file_list;
    }

    /**
     * gets triggered when the veryfication file couldn't get decrypted or a signature fails
     * @return string
     */
    public function failed_signature()
    {
        $this->cleanup();
        die("Invalid signature");
    }

    /**
     * gets triggered when the filecount is not the same as in veryfication file
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
        unlink($this->temp_dir);
    }


    /**
     * Check the integrety of the Zipfile
     * @param $temp_dir
     * @param $signed_file_list
     */
    public function check_integrety($signed_file_list)
    {

        $signatures = json_decode($signed_file_list);
        if ($this->countfiles($this->temp_dir) !== count($signatures['signatures'])){
            $this->failed_count();
            exit();
        }

        foreach($signatures['signatures'] as $fileinfo){
            $this->compare($fileinfo['file'],$fileinfo['hash']);
        }
    }

    /**
     * Count files in a given directory
     * @param $dir
     * @return int
     */
    public function countfiles($dir)
    {
        $dir = opendir($dir);
        $i = 0;
        while (false !== ($file = readdir($dir))) {
            if (!in_array($file, array('.', '..')) && !is_dir($file) ){ $i++;}
        }
        return $i;
    }

    /**
     * Compare signatures of veryfication file with hashes
     * @param $file
     * @param $signature
     * @return bool|string
     */
    public function compare($file,$signature)
    {
        if(hash_file($this->algo,$file) !== $signature){
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
        $this->extract($zipfilePath,$this->temp_dir);
        $veryficationFile = $this->load_verification_file($this->temp_dir.'signatures.json');
        $this->check_integrety($veryficationFile);
        $this->extract($zipfilePath,$this->update_dir);
        $this->cleanup();
        return true;
    }
}