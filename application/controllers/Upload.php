<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/qrcode/autoload.php';

use Endroid\QrCode\QrCode;

class Upload extends CI_Controller
{

   function __construct()
   {
      parent::__construct();
      $this->load->helper('aws_helper'); // load the helper file
   }

   public function index()
   {
      $this->load->view('upload');
   }

   /**
    * upload image directly to S3
    */
   function doUpload()
   {
      $uploadFile = 'userfile'; // file name in upload form

      if (empty($_FILES[$uploadFile]["name"])) {
         print_r('Please select a file to upload.');
         exit();
      }

      $type = 'image'; // set upload type : image | doc | pdf | excel
      $directory = 'test'; // set directory upload
      $maxSize = 2; // in MB = 10MB
      $generatedImg = ''; // set empty
      $extension = ''; // set empty
      $mimeType = ''; // set empty
      $s3Upload = json_encode(s3Upload($type, $directory, $uploadFile, $generatedImg, $mimeType, $extension, $maxSize));
      $res = json_decode($s3Upload); // convert to object

      if ($res->success) {
         // print_r($res->data); // will return URL -> and then save the URL to database
         print_r($res);
      } else {
         print_r($res->message);
      }
   }

   /**
    * generate Qr code directly to S3
    */
   function generateQr()
   {
      $qrCode = new QrCode('https://example.com');
      $generatedImg = $qrCode->writeString(); // Generate the QR code in memory

      $type = 'image'; // set upload type : image | doc | pdf | excel
      $extension = 'png'; // set extension : jpeg | png | doc
      $mimeType = 'image/png'; // mime type : image/jpeg | application/msword | application/pdf | application/vnd.ms-excel
      $directory = 'qr'; // set directory upload
      $uploadFile = ''; // set to empty
      $maxSize = ''; //set empty
      $s3Upload = json_encode(s3Upload($type, $directory, $uploadFile, $generatedImg, $mimeType, $extension, $maxSize));
      $res = json_decode($s3Upload); // convert to object

      if ($res->success) {
         // print_r($res->data); // will return URL -> and then save the URL to database
         print_r($res);
      } else {
         print_r($res->message);
      }
   }
}
