<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/aws/aws-autoloader.php';

use Aws\S3\S3Client;

/**
 * Function to upload file using S3 compatible API
 * @param string $type (image, doc, pdf, excel)
 * @param string $uploadedFileName
 * @param string $directory
 * @return array
 */
function s3Upload($type, $uploadedFileName, $directory = '')
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

   if (empty($_FILES[$uploadedFileName]["name"])) {
      $res = [
         'success' => false,
         'message' => 'Please select a file to upload.'
      ];
      return $res;
      exit();
   }

   // File info 
   $fileName = basename($_FILES[$uploadedFileName]["name"]);
   $fileType = $_FILES[$uploadedFileName]["type"];
   $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

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

   if (!in_array($fileExtension, $allowTypes)) {
      $res = [
         'success' => false,
         'message' => 'Sorry, file type "' . $fileExtension . '" not allowed to upload.'
      ];
      return $res;
      exit();
   }

   // File temp source 
   $fileTemp = $_FILES[$uploadedFileName]["tmp_name"];

   if (!is_uploaded_file($fileTemp)) {
      $res = [
         'success' => false,
         'message' => 'File upload failed!'
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
         'SourceFile' => $fileTemp,
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
