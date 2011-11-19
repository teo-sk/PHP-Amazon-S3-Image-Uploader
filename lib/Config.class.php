<?php

Class Config {

    private $amount_of_files; //amount of files allowed to be uploaded per one upload
    private $image_dimensions; //array of image dimensions that uploaded images will be automatically resized to
    private $mime_types;  //array of allowed mime types of uploaded images
    private $s3_bucket_name; //Amazon S3 bucket name
    private $upload_dir;            //target directory for upload
    private static $instance;

    private function __construct() {
        
    }

    //private constructor and singleton pattern implemented for preventing config overwriting
    //usage: Config::getInstance() - either create new config once, or return existing

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    //getters and setters for variables

    public function setAmount($amount) {
        $this->amount_of_files = $amount;
    }

    public function getAmount() {
        return $this->amount_of_files;
    }

    public function setDimensions(Array $dimensions) {
        $this->image_dimensions = $dimensions;
    }

    public function getDimensions() {
        return $this->image_dimensions;
    }

    public function setMimeTypes(Array $mime_types) {
        $this->mime_types = $mime_types;
    }

    public function getMimeTypes() {
        return $this->mime_types;
    }

    public function setBucketName($name) {
        $this->s3_bucket_name = $name;
    }

    public function getBucketName() {
        return $this->s3_bucket_name;
    }

    public function setUploadDir($dir) {
        $this->upload_dir = $dir;
    }

    public function getUploadDir() {
        return $this->upload_dir;
    }

}

?>