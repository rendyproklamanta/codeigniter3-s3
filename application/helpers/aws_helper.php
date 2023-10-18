<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/aws/aws-autoloader.php';

use Aws\S3\S3Client;

/**
 * Function to upload file using S3 compatible API
 * @param string $type (image, doc, pdf, excel)
 * @param mixed $uploadFile (from upload form)
 * @param mixed $directory
 * @param mixed $source (from generated image like qr, etc.)
 * @param mixed $fileType is required if using source
 * @param mixed $fileExtension is required if using source
 * @param upload : s3Upload($type, $directory, $uploadFile)
 * @param generated : s3Upload($type, $directory, 0, $source, $fileType, $extension)
 * @return array
 */
function s3Upload($type, $directory = '', $uploadFile = '', $source = '', $fileType = '', $fileExtension = '')
{
   $CI = &get_instance();
   $CI->config->load('aws'); // load the configuration file

   if (empty($type)) {
      $res = [
         'success' => false,
         'message' => 'Please assign type file!'
      ];
      return $res;
      exit();
   }

   // File from generated image 
   if ($source) {
      if (empty($fileExtension) || empty($fileType)) {
         $res = [
            'success' => false,
            'message' => 'Please assign file type and extension!'
         ];
         return $res;
         exit();
      }

      $uploadFile = '';
      $fileContent = $source;
   }

   // File from upload 
   $fileTemp = '';
   if ($uploadFile) {
      $fileName = basename($_FILES[$uploadFile]["name"]);
      $fileType = $_FILES[$uploadFile]["type"];
      $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

      $fileTemp = $_FILES[$uploadFile]["tmp_name"];

      if (!is_uploaded_file($fileTemp)) {
         $res = [
            'success' => false,
            'message' => 'File upload failed!'
         ];
         return $res;
         exit();
      }

      $source = '';
      $fileContent = file_get_contents($fileTemp);
   }

   // Allow certain file formats 
   if ($type == 'image') {
      $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
   } else if ($type == 'doc') {
      $allowTypes = array('doc', 'docx');
   } else if ($type == 'pdf') {
      $allowTypes = array('pdf');
   } else if ($type == 'excel') {
      $allowTypes = array('xls', 'xlsx');
   } else {
      $res = [
         'success' => false,
         'message' => 'File type "' . $type . '" not found!'
      ];
      return $res;
      exit();
   }

   // validation file type
   if (!in_array($fileExtension, $allowTypes)) {
      $res = [
         'success' => false,
         'message' => 'Sorry, file type "' . $fileExtension . '" not allowed to upload.'
      ];
      return $res;
      exit();
   }

   try {
      // Instantiate an Amazon S3 client 
      $client = new S3Client([
         'credentials' => array(
            'key'    => config_item('aws_s3')['access_key'],
            'secret' => config_item('aws_s3')['secret_key'],
         ),
         'endpoint' => config_item('aws_s3')['endpoint'],
         'region' => config_item('aws_s3')['region'],
         'version' => 'latest',
         'http' => ['verify' => false]
      ]);

      if ($directory) {
         $directory = $directory . '/';
      }

      $keyName = $directory . uniqid() . '.' . $fileExtension;

      // Upload file to S3 bucket 
      $putObject = $client->putObject([
         'Bucket' => config_item('aws_s3')['bucket_name'],
         'Key' => $keyName,
         'Body' => $fileContent,
         'ContentType' => $fileType,
      ]);

      $result = $putObject->toArray();

      if (empty($result['ObjectURL'])) {
         $res = [
            'success' => false,
            'message' => 'Upload Failed! S3 Object URL not found.',
         ];
         return $res;
         exit();
      }

      $res = [
         'success' => true,
         'message' => 'File Upload Success!',
         'data' => $result['ObjectURL']
      ];
      return $res;
      exit();
   } catch (Exception $e) {
      $res = [
         'success' => false,
         'message' => $e->getMessage(),
      ];
      return $res;
      exit();
   }
}
