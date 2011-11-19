<?php

Class Uploader {

    private $actual_dir;    //actual directory
    private $config;
    private $hashed_file_name;  //hashed timestamp of uploaded file's original name
    private $user_home_dir; //home dir of user
    private $uid; //user id
    public $image;
    public $s3;

    public function __construct($uid) {
        //constructor imports config and sets users dir to his id in upload dir
        $this->config = Config::getInstance();
        $this->s3 = new AmazonS3();
        $this->user_home_dir = $this->config->getUploadDir() . "/" . $uid;
        $this->actual_dir = "";
        $this->uid = $uid;
    }

    private function _checkUpload() {
        try {
            if (empty($_FILES)) {
                throw new Exception("This method should be called after a file upload.");
            }
        } catch (Exception $e) {
            echo $e->getMessage() . " in file " . __FILE__ . " at line " . __LINE__;
            die();
        }

        try {
            if (!in_array(strtolower($this->generateExtension()), $this->config->getMimeTypes())) {
                throw new Exception("This file extension is not allowed.");
            }
        } catch (Exception $e) {
            echo $e->getMessage() . " in file " . __FILE__ . " at line " . __LINE__;
            die();
        }
    }

    private function generateExtension() {
        //return the extension of uploaded file
        return pathinfo($_FILES["Filedata"]["name"], PATHINFO_EXTENSION);
    }

    private function hashFilename() {
        $this->_checkUpload(); //check if a file was uploaded
        $this->hashed_file_name = MD5(date("F/d/Y H:i:s")) . "." . $this->generateExtension();
    }

    private function filter_file_list($arr) {
        return array_values(array_filter(array_map(array($this, 'file_path'), $arr)));
    }

    function file_path($file) {
        return!is_dir($file) ? realpath($file) : null;
    }

    private function deleteAll($directory, $empty = false) {
        //function for deleting a folder an it's contents
        if (substr($directory, -1) == "/") {
            $directory = substr($directory, 0, -1);
        }

        if (!file_exists($directory) || !is_dir($directory)) {
            return false;
        } elseif (!is_readable($directory)) {
            return false;
        } else {
            $directoryHandle = opendir($directory);

            while ($contents = readdir($directoryHandle)) {
                if ($contents != '.' && $contents != '..') {
                    $path = $directory . "/" . $contents;

                    if (is_dir($path)) {
                        deleteAll($path);
                    } else {
                        unlink($path);
                    }
                }
            }

            closedir($directoryHandle);

            if ($empty == false) {
                if (!rmdir($directory)) {
                    return false;
                }
            }

            return true;
        }
    }
    
    private function determineDirs( $dir )
    	{
    		if ( $this->actual_dir == "" ) {
    			$dirs = explode("/",$dir);
    			if (array_key_exists('1', $dirs)) {
    				$dirs[0] .= " (dir)";
				}
    			return $dirs[0];
    		} else {
    			$dir = str_replace($this->actual_dir, "", $dir);
    			$dirs = explode("/",$dir);
    			if (array_key_exists('2', $dirs)) {
    				$dirs[1] .= " (dir)";
				}
    			return $dirs[1];
    		}
    	}

    public function upload() {
        $this->_checkUpload();  //check if a file was uploaded
        $this->hashFilename();  //hash actual timestamp to be new file name
        $this->image = new Image();
        if ($_FILES["Filedata"]["error"] > 0) {
            //check for error
            echo "Return Code: " . $_FILES["Filedata"]["error"] . "<br />";
        } else {
            //create necessary directories
            if (!is_dir($this->user_home_dir)) {
                mkdir($this->user_home_dir);
            }
            if (!is_dir($this->user_home_dir . "/" . $this->hashed_file_name)) {
                mkdir($this->user_home_dir . "/" . $this->hashed_file_name);
            }
            //resize image for desired dimensions
            foreach ($this->config->getDimensions() as $dimensions) {
                $dimensions = explode("x", $dimensions);
                $this->image->load($_FILES['Filedata']['tmp_name']);
                $this->image->resizeToWidth($dimensions[0]);
                $this->image->save($this->user_home_dir . "/" . $this->hashed_file_name . "/" . $dimensions[0] . ".jpg");
                chmod($this->user_home_dir . "/" . $this->hashed_file_name . "/" . $dimensions[0] . ".jpg", 0775);
            }
            //upload the image to dir
            move_uploaded_file($_FILES["Filedata"]["tmp_name"], $this->user_home_dir . "/" . $this->hashed_file_name . "/original.jpg");
            chmod($this->user_home_dir . "/" . $this->hashed_file_name . "/original.jpg", 0775);

            //uploading the files to the machine is needed, as image class
            //resizes images and can only save them to the machine.
            //then all files are moved in a batch to CDN
            //and deleted from the machine.
            //move the newly created files to CDN
            $list_of_files = $this->filter_file_list(glob($this->user_home_dir . "/" . $this->hashed_file_name . '/*'));
            // Prepare to hold the individual filenames
            $individual_filenames = array();

            // Loop over the list, referring to a single file at a time
            foreach ($list_of_files as $file) {
                // Grab only the filename part of the path
                $filename = explode(DIRECTORY_SEPARATOR, $file);
                $filename = array_pop($filename);

                // Store the filename for later use
                $individual_filenames[] = $filename;

                /* Prepare to upload the file to our new S3 bucket. Add this
                  request to a queue that we won't execute quite yet. */
                $this->s3->batch()->create_object($this->config->getBucketName(), $this->uid . "/" . $this->hashed_file_name . "/" . $filename, array(
                    'fileUpload' => $file,
                    'acl' => AmazonS3::ACL_PUBLIC
                ));
            }

            /* Execute our queue of batched requests. This may take a few seconds to a
              few minutes depending on the size of the files and how fast your upload
              speeds are. */
            $file_upload_response = $this->s3->batch()->send();

            /* Since a batch of requests will return multiple responses, let's
              make sure they ALL came back successfully using `areOK()` (singular
              responses use `isOK()`). */
            if ($file_upload_response->areOK()) {
                // Loop through the individual filenames
                foreach ($individual_filenames as $filename) {
                    /* Display a URL for each of the files we uploaded. */
                    echo $this->s3->get_object_url($this->config->getBucketName(), $this->uid . "/" . $this->hashed_file_name . "/" . $filename) . PHP_EOL . PHP_EOL;
                    echo "<br/>";
                }
            }

            //delete files on machine
            $this->deleteAll($this->user_home_dir . "/" . $this->hashed_file_name);
        }
    }

    public function setActualDir($dir) {
        $this->actual_dir = $dir;
    }

    public function ls() {
        $bucket = $this->config->getBucketName();
        $response = $this->s3->get_object_list($bucket, array(
                    'prefix' => $this->actual_dir
                ));
        
        echo "List of " . $this->actual_dir . "/<br/><br/>";
        $dirs = array_map(array($this, 'determineDirs'), $response);
        $dirs = array_unique($dirs);
        //var_dump($dirs);
        foreach ($dirs as $entry) {
        	echo $entry . "<br/>";
        }
    }

    public function cd($dirname) {
        if ($dirname == "..") {
            $dirs = explode("/", $this->actual_dir);
            array_pop($dirs);
            $this->actual_dir = implode("/", $dirs);
        } else {
            if ($this->actual_dir == "") {
                $this->actual_dir = $dirname;
            } else {
                $this->actual_dir = $this->actual_dir . "/" . $dirname;
            }
        }
        setcookie("Uploader_dir", $this->actual_dir);
        echo $this->actual_dir;
    }

    public function mkdir($dirname) {
    if ($this->actual_dir == "") {
    		$dirname = $dirname;
    	} else {
    		$dirname = $this->actual_dir . "/" . $dirname;
    	}
        
        
        $response = $this->s3->create_object($this->config->getBucketName(), $dirname . '/', array(
    		'body' => '',
        	'length' => 0,
        	'acl' => AmazonS3::ACL_PUBLIC
		));
        
        if ($response->isOK()) {
            echo "Directory $dirname created";
        } else {
            echo "Directory $dirname already exists!";
        }
    }

    public function rm($name) {
    	if ($this->actual_dir == "") {
    		$path = $name;
    	} else {
    		$path = $this->actual_dir . "/" . $name;
    	}
	    $response = $this->s3->delete_object($this->config->getBucketName(), $path); 
	    
	    echo ($response->isOK()) ?  "Delete of $path successful" :  "Delete of $path failed";
        
    }
    
    public function getLink ($name) {
        if ($this->actual_dir == "") {
    		$path = $name;
    	} else {
    		$path = $this->actual_dir . "/" . $name;
    	}
            echo $this->s3->get_object_url($this->config->getBucketName(), $path);
    }
    

}

?>