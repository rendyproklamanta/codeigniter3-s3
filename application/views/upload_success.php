<!DOCTYPE html>
<html lang="en">

<head>
   <title>Upload Form</title>
</head>

<body>

   <h3>Your file was successfully uploaded!</h3>

   <ul>
      <li>url file: <?= $result['ObjectURL'] ?></li>
   </ul>

   <p><?= anchor('upload', 'Upload Another File!') ?></p>

</body>

</html>