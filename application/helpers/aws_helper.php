<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/aws/aws-autoloader.php';

use Aws\S3\S3Client;

function s3Upload($uploadedFileName, $directory)
{
   $CI = &get_instance();
   $CI->config->load('aws'); // load the configuration file

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
   $allowTypes = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png', 'jpeg', 'gif', 'webp');

   if (!in_array($fileExtension, $allowTypes)) {
      $res = [
         'success' => false,
         'message' => 'Sorry, only Word/Excel/Image files are allowed to upload.'
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

   // Instantiate an Amazon S3 client 
   $client = new S3Client([
      'credentials' => array(
         'key'    => config_item('aws_s3')['access_key'],
         'secret' => config_item('aws_s3')['secret_key'],
      ),
      'endpoint' => config_item('aws_s3')['endpoint'],
      'region' => config_item('aws_s3')['region'],
      'version' => 'latest',
   ]);

   $keyName = $directory . '/' . uniqid() . '.' . $fileExtension;

   // Upload file to S3 bucket 
   try {
      $putObject = $client->putObject([
         'Bucket' => config_item('aws_s3')['bucket_name'],
         'Key'    => $keyName,
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
         'message' => 'File upload Success!',
         'data' => $result['ObjectURL']
      ];
      return $res;
      exit();
   } catch (Aws\S3\Exception\S3Exception $e) {

      $res = [
         'success' => false,
         'message' => $e->getMessage(),
      ];
      return $res;
      exit();
   }
}
