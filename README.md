## Required files to copy to your project :

- application/libraries/aws
- application/config/aws.php
- application/helpers/aws_helper.php

## Supported S3 providers :

- https://aws.amazon.com/s3
- https://www.backblaze.com/cloud-storage
- https://www.cloudflare.com/lp/pg-r2/
- https://www.vultr.com/products/object-storage/
- https://www.digitalocean.com/products/spaces
- https://www.linode.com/products/object-storage/
- https://www.ovhcloud.com/public-cloud/object-storage/

## How To Use :
- File from upload
```
$uploadFile = 'userfile'; // file name in upload form

if (empty($_FILES[$uploadFile]["name"])) {
   print_r('Please select a file to upload.');
   exit();
}

$type = 'image'; // set upload type : image,doc,pdf,excel
$directory = 'test'; // set directory upload
$s3Upload = json_encode(s3Upload($type, $directory, $uploadFile'));
$res = json_decode($s3Upload); // convert to object

print_r($res);
```

- File from generated image
```
$qrCode = new QrCode('https://example.com');
$source = $qrCode->writeString(); // Generate the QR code in memory

$type = 'image'; // set upload type : image | doc | pdf | excel
$extension = 'png'; // set extension : jpeg | png | doc
$fileType = 'image/png'; // complete file type : image/jpeg | application/msword | application/pdf | application/vnd.ms-excel
$directory = 'qr'; // set directory upload
$s3Upload = json_encode(s3Upload($type, $directory, $uploadFile = '', $source, $fileType, $extension));

$res = json_decode($s3Upload); // convert to object

print_r($res);
```