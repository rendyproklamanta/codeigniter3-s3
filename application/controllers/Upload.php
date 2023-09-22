<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Upload extends CI_Controller
{

   function __construct()
   {
      parent::__construct();
      $this->config->load('aws'); // load the configuration file
      $this->load->helper('aws_helper'); // load the helper file
   }

   public function index()
   {
      $this->load->view('upload');
   }

   /**
    * doUpload
    */
   function doUpload()
   {
      $uploadedFileName = 'userfile'; // file name in upload form
      $type = 'image'; // set upload type : image,doc,pdf,excel
      $directory = 'avatar'; // set directory upload
      $s3Upload = json_encode(s3Upload($type, $uploadedFileName, $directory));
      $res = json_decode($s3Upload); // convert to object

      if ($res->success) {
         // print_r($res->data); // will return URL -> and then save the URL to database
         print_r($res);
      } else {
         print_r($res->message);
      }
   }
}
