<!DOCTYPE html>
<html lang="en">

<head>
   <title>Upload Form</title>
</head>

<body>

   <form method="post" action="/upload/doUpload" enctype="multipart/form-data">
      <div class="form-group">
         <label><b>Select File:</b></label>
         <input type="file" name="userfile" class="form-control" required>
      </div>
      <div class="form-group">
         <input type="submit" class="btn btn-primary" name="submit" value="Upload">
      </div>
   </form>

</body>

</html>