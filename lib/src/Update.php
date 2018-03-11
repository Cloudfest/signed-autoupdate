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

        try{
            $this->ProcessUpdate($updateURL);
        }catch(Exception $exception){

        }
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
     * Recursive deletes directory
     * @param $dir
     */
    public function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this->rrmdir($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Get the update file
     * @param string/url $download_url
     * @return int|bool The function returns the number of bytes that were written to the file, or
     * false on failure.
     */
    public function download($url)
    {
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }

        $filepath = $this->tempDir . '/' . basename($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);

        file_put_contents($filepath,$data);
        curl_close($ch);

        if(!file_exists($filepath)){
            return 'Download failed to retrieve the update file';
        }
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
     * Gather Signatures and the verifies that list.json is signed.
     * @return string/json content listfile
     * @throws SodiumException
     * @throws TypeError
     */
    public function verify(){
        $signature = hex2bin(file_get_contents($this->tempDir.'/.well-known/signature.txt'));
        $listfile = file_get_contents($this->tempDir.'/.well-known/list.json');
        $publicKey = $this->publicKey;

        if(function_exists('sodium_crypto_sign_verify_detached')){
            if (!sodium_crypto_sign_verify_detached($signature, $listfile, $publicKey)) {
                $this->failed_signature();
            }
        }elseif(class_exists('ParagonIE_Sodium_Compat')){
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
     * Gets triggered when the verification file couldn't get decrypted or a signature fails
     * @return string
     */
    public function failed_signature()
    {
        $this->cleanup();
        die("Invalid signature");
    }

    /**
     * Gets triggered when the file count is not the same as in verification file
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
        #$this->rrmdir($this->tempDir);
    }


    /**
     * Check the integrity of all files
     * First: Count number of files in update and number of files that have a signature.
     * Second: Hash every file and compare with given file Hash from the signed file list
     * @param $signed_file_list
     * @return bool
     */
    public function checkIntegrity($signed_file_list)
    {
        $signatures = json_decode($signed_file_list,true);
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
     * Count files in a given directory, disregard the list.json & signature.txt as well as directory traversal
     * @param $path
     * @return int file count
     */
    public function countFiles($path)
    {
        $size = 0;
        $ignore = array('.','..','list.json','signature.txt');
        $files = scandir($path);
        foreach($files as $t) {
            if(in_array($t, $ignore)) continue;
            if (is_dir(rtrim($path, '/') . '/' . $t)) {
                $size += $this->countFiles(rtrim($path, '/') . '/' . $t);
            } else {
                $size++;
            }
        }
        return $size;
    }

    /**
     * Compare signatures of signed file list with hashes
     * @param $file
     * @param $signature
     * @return bool|string
     */
    public function compare($file,$signature)
    {
        if(hash_file($this->algorithm,$this->tempDir.'/'.$file) !== $signature){
            return $this->failed_signature();
        }
        return true;
    }


    /**
     * Process the update from a given URL, download Zip and extract for check.
     * If check fails and after the update was successful clean up remaining files.
     * @param $url
     * @return bool|string
     * @throws SodiumException
     * @throws TypeError
     */
    public function ProcessUpdate($url)
    {
        $zipfilePath = $this->download($url);
        $response = $this->extract($zipfilePath,$this->tempDir);

        if($response){
            $verificationFile = $this->verify();
            $this->checkIntegrity($verificationFile);
        }

        $response = $this->extract($zipfilePath,$this->updateDir);
        if(!$response){
            $this->cleanup();
            return 'Update successful';
        }
        return 'Update failed';
    }
}