<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/jboesch/Gritter@1.7.4/css/jquery.gritter.css" />
	<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap.css">
	<link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
	<title>ConvertToCsv</title>
	<script type="text/javascript" src="assets/js/jquery.js"></script>
	<script type="text/javascript" src="assets/bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script>
	<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/jboesch/Gritter@1.7.4/js/jquery.gritter.js"></script>
	<script src="https://cdn.ckeditor.com/4.7.3/standard/ckeditor.js"></script>
	<script type="text/javascript" src="assets/js/jquery.validate.js"></script>
	<script type="text/javascript" src="assets/js/additional-methods.js"></script>
	<!-- <script type="text/javascript" src="assets/js/scripts.js"></script> -->
</head>
<body>
	<!-- MultiStep Form -->
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<form id="msform" method="post" action="ConvertAllToCsv.php" enctype="multipart/form-data">
				<!-- progressbar -->
				<ul id="progressbar">
				</ul>
				<!-- fieldsets -->
				<fieldset class="section-a">
					<h2 class="fs-title">QBO FILES</h2>
					<h3 class="fs-subtitle">This is the file(s) which will be used to convert into <strong>CSV</strong></h3>
					<input type="text" class="form-control" name="location" placeholder="Please Enter the name of output folder (if you want to use base location just ignore this field)" />
					<h3 class="fs-subtitle" style="text-align:left">&#8251;The output files will be stored in <strong>localhost/OFXParser/ofxtmp/</strong> folder in defalut.</h3>
					<input type="file" name="files[]" multiple />
					<input type="reset" name="previous" class="previous action-button-previous" value="Reset"/>
					<input type="submit" name="submit" class="submit action-button" data-section="b" value="Convert"/>


					<input type="submit" name="delete_all" class="btn" value="Delete All Files"/>

				</fieldset>			
			</form>
		</div>
	</div>
	<!-- /.MultiStep Form -->
</body>
</html>