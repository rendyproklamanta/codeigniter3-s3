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
```
$uploadedFileName = 'userfile'; // file name in upload form
$directory = 'avatar'; // set directory upload
$s3Upload = json_encode(s3Upload($uploadedFileName, $directory));

$res = json_decode($s3Upload); // convert to object
print_r($res);
```