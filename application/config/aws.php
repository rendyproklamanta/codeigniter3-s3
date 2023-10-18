<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['aws_s3'] = array(
   'access_key' => 'YOUR_ACCESS_KEY', // if using B2 > keyID
   'secret_key' => 'YOUR_SECRET_KEY', // if using B2 > applicationKey
   'region' => 'YOUR_REGION', // us-west-00X
   'endpoint' => 'YOUR_ENDPOINT', // https://s3.xxxxxx.com
   'bucket_name' => 'YOUR_BUCKET_NAME', // mybucket
);