<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/aws/aws-autoloader.php';

use Aws\S3\S3Client;

if (!function_exists('s3Upload')) {
   /**
    * Function to upload file using S3 compatible API
    * @param string $type (image, doc, pdf, excel)
    * @param mixed $uploadFile (from upload form)
    * @param mixed $directory is required
    * @param mixed $generatedImg (from generated image like qr, etc.)
    * @param mixed $mimeType is required if using generatedImg
    * @param mixed $extension is required if using generatedImg
    * @param int $maxSize is optional, default is 500 MB
    *
    * @param upload : s3Upload($type, $directory, $uploadFile)
    * @param generated : s3Upload($type, $directory, 0, $generatedImg, $mimeType, $extension)
    * @return array
    */
   function s3Upload($type, $directory, $uploadFile = '', $generatedImg = '', $mimeType = '', $extension = '',  $maxSize = '')
   {
      if (empty($type)) {
         $res = [
            'success' => false,
            'message' => 'Please assign type file!'
         ];
         return $res;
         exit();
      }

      if (empty($directory)) {
         $res = [
            'success' => false,
            'message' => 'Directory cannot be empty!'
         ];
         return $res;
         exit();
      }

      // File from generated image 
      if ($generatedImg) {
         if (empty($extension) || empty($mimeType)) {
            $res = [
               'success' => false,
               'message' => 'Please assign mime type and extension!'
            ];
            return $res;
            exit();
         }

         $uploadFile = '';
         $fileContent = $generatedImg;
      }

      // File from upload 
      if ($uploadFile) {
         // # Create directory
         // $relativePath = 'assets/upload/' . $directory;
         // $fullPath = FCPATH . $relativePath;

         // if (!is_dir($fullPath)) {
         //    if (mkdir($fullPath, 0755, true)) {
         //    } else {
         //       echo 'Directory ' . $fullPath . ' failed to created.';
         //    }
         // }

         $fileName = basename($_FILES[$uploadFile]["name"]);
         $mimeType = $_FILES[$uploadFile]["type"];
         $extension = pathinfo($fileName, PATHINFO_EXTENSION);
         $fileTemp = $_FILES[$uploadFile]["tmp_name"];
         $fileSize = $_FILES[$uploadFile]["size"];

         if (!is_uploaded_file($fileTemp)) {
            $res = [
               'success' => false,
               'message' => 'Temp upload file failed!'
            ];
            return $res;
            exit();
         }

         // Check file size
         if (!$maxSize) {
            $maxSize = 500; // n MB
         }
         $allowedSize = $maxSize * 1024 * 1024;
         if ($fileSize > $allowedSize) {
            $res = [
               'success' => false,
               'message' => 'File size exceeds the ' . $maxSize . ' MB limit!'
            ];
            return $res;
            exit();
         }

         $fileContent = file_get_contents($fileTemp);
      }

      // Define allowed file types
      $allowedTypes = [
         'image'       => ['jpg', 'png', 'jpeg', 'gif', 'webp', 'svg', 'tiff'],
         'doc'         => ['doc', 'docx'],
         'pdf'         => ['pdf'],
         'excel'       => ['xls', 'xlsx'],
         'video'       => ['flv', 'mp4', 'mpg', 'mpeg', 'm3u8', 'ts', '3gp', 'mov', 'avi', 'wmv'],
         'audio'       => ['wav', 'aifc', 'aiff', 'mp3', 'm4a', 'mp2', 'ogg'],
         'video|audio' => ['flv', 'mp4', 'mpg', 'mpeg', 'm3u8', 'ts', '3gp', 'mov', 'avi', 'wmv', 'wav', 'aifc', 'aiff', 'mp3', 'm4a', 'mp2', 'ogg']
      ];

      // Check if requested type exists
      if (!isset($allowedTypes[$type])) {
         return [
            'success' => false,
            'message' => 'Unsupported file type group: "' . $type . '"'
         ];
      }

      // Validate extension
      $allowedExt = $allowedTypes[$type];
      if (!in_array(strtolower($extension), $allowedExt)) {
         return [
            'success' => false,
            'message' => 'Sorry, the file extension "' . $extension . '" is not allowed'
         ];
      }

      try {
         $access_key = config_item('aws_access_key');
         $secret_key = config_item('aws_secret_key');
         $endpoint = config_item('aws_endpoint');
         $region = config_item('aws_region');
         $bucket_name = config_item('aws_bucket_name');

         // Instantiate an Amazon S3 client 
         $client = new S3Client([
            'credentials' => array(
               'key' => $access_key,
               'secret' => $secret_key,
            ),
            'endpoint' => $endpoint,
            'region' => $region,
            'version' => 'latest',
            'http' => ['verify' => false]
         ]);

         $keyName = $directory . '/' . uniqid() . '.' . $extension;

         // Upload file to S3 bucket 
         $putObject = $client->putObject([
            'Bucket' => $bucket_name,
            'Key' => $keyName,
            'Body' => $fileContent,
            'ContentType' => $mimeType,
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

         // Delete temp file after successful upload
         if ($uploadFile) {
            if (file_exists($fileTemp)) {
               unlink($fileTemp);
            }
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
}
