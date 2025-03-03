<?php

use Aws\S3\S3Client;

/**
 * Function to upload file using S3 compatible API
 * @param string $type (image, doc, pdf, excel)
 * @param mixed $uploadFile (from upload form)
 * @param mixed $directory
 * @param mixed $source (from generated image like qr, etc.)
 * @param mixed $mimeType is required if using source
 * @param mixed $extension is required if using source
 * @param upload : s3Upload($type, $directory, $uploadFile)
 * @return array
 */
function s3Upload($type, $directory = '', $uploadFile = '', $source = '', $mimeType = '', $extension = '')
{
   $CI = &get_instance();
   $CI->config->load('aws'); // load the configuration file

   if (empty($type)) {
      $res = [
         'success' => false,
         'message' => 'Please assign type file!'
      ];
      return $res;
   }

   // Instantiate an Amazon S3 client 
   $client = new S3Client([
      'credentials' => array(
         'key' => config_item('aws_s3')['access_key'],
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

   // File from generated image 
   if ($source) {
      $resFile = "";

      if (empty($extension) || empty($mimeType)) {
         $res = [
            'success' => false,
            'message' => 'Please assign mime type and extension!'
         ];
         return $res;

      }

      $uploadFile = '';
      $fileContent = $source;

      try {

         $keyName = $directory . uniqid() . '.' . $extension;

         // Upload file to S3 bucket 
         $putObject = $client->putObject([
            'Bucket' => config_item('aws_s3')['bucket_name'],
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

         }

         $resFile = $result['ObjectURL'];

      } catch (Exception $e) {
         $res = [
            'success' => false,
            'message' => $e->getMessage(),
         ];
         return $res;
      }
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

      $resFile = array();

      foreach ($_FILES[$uploadFile]['name'] as $key => $name) {
         $fileTemp = $_FILES[$uploadFile]['tmp_name'][$key];
         $fileName = basename($name);
         $mimeType = $_FILES[$uploadFile]["type"][$key];
         $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

         if (!is_uploaded_file($fileTemp)) {
            $res = [
               'success' => false,
               'message' => 'File upload failed!'
            ];
            return $res;

         }
         $source = '';
         $fileContent = file_get_contents($fileTemp);

         // Allow certain file formats 
         $typeImage = array('jpg', 'png', 'jpeg', 'gif', 'webp', 'svg', 'tiff');
         $typeDoc = array('doc', 'docx');
         $typePdf = array('pdf');
         $typeExcel = array('xls', 'xlsx');
         $typeVideo = array('flv', 'mp4', 'mpg', 'mpeg', 'm3u8', 'ts', '3gp', 'mov', 'avi', 'wmv');
         $typeAudio = array('wav', 'aifc', 'aiff', 'mp3', 'm4a', 'mp2', 'ogg');

         switch ($type) {
            case 'image':
               $allowTypes = $typeImage;
               break;
            case 'doc':
               $allowTypes = $typeDoc;
               break;
            case 'pdf':
               $allowTypes = $typePdf;
               break;
            case 'excel':
               $allowTypes = $typeExcel;
               break;
            case 'video':
               $allowTypes = $typeVideo;
               break;
            case 'audio':
               $allowTypes = $typeAudio;
               break;
            case 'video|audio':
               $allowTypes = array_merge($typeVideo, $typeAudio);
               break;
            default:
               $res = [
                  'success' => false,
                  'message' => 'File type "' . $type . '" not found!'
               ];
               return $res;
         }

         // validation file type
         if (!in_array($extension, $allowTypes)) {
            $res = [
               'success' => false,
               'message' => 'Sorry, file type "' . $extension . '" not allowed to upload.'
            ];
            return $res;
         }

         try {

            $keyName = $directory . uniqid() . '.' . $extension;

            // Upload file to S3 bucket 
            $putObject = $client->putObject([
               'Bucket' => config_item('aws_s3')['bucket_name'],
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

            }

            $resFile[] = $result['ObjectURL'];

         } catch (Exception $e) {
            $res = [
               'success' => false,
               'message' => $e->getMessage(),
            ];
            return $res;
         }
      }
   }

   $res = [
      'success' => true,
      'message' => 'File Upload Success!',
      'data' => $resFile
   ];
   return $res;

}
