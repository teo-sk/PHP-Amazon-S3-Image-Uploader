PHP Amazon S3 Image Uploader
============================

PHP Class that lets you integrate a simple, configurable image uploader to your Amazon S3 bucket


Configuration
-------------

First, you need to set up your Amazon S3 credentials in `lib/amazon_sdk/config.inc.php` file.
Then, you can configure the uploader in `inc/init.php` file. *@todo - split to config + init*


Usage
-----

### Uploading

This class is using jQuery Uploadify library for handling the front-end part of the uploading. It uses AJAX to communicate with server-side script, that actually uses the uploader class. Of course, you can use your own front-end solution as well.
So the whole code needed to use this class is in `ajax/upload.php` file, and is this simple:
    
    $uploader = new Uploader($uid);
    $uploader->upload();
    
Where the `$uid` variable comes from your application, and represents for example the ID of a user currently logged in and uploading. This will cause all the files go to a directory of this user.

### Browsing the S3 Bucket

The class also comes with a set of FTP-like methods that you can use to browse through the uploaded files in your Amazon S3 bucket. You can navigate your browser to the `ftp.php` file to see an example of usage.