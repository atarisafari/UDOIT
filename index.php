<?php
include_once('config/localConfig.php');
include_once('app/curlClass.php');
include_once('app/ims-blti/blti.php');

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
session_start();
header('Content-Type: text/html; charset=utf-8');

if( !isset($_SESSION['valid']) ){
	$_SESSION['valid'] = false;
}

if( $_SESSION['valid'] === false ){
	// Initialize, all secrets are 'secret', do not set session, and do not redirect
	$context = new BLTI($consumer_key, $shared_secret, false, false);
	//if the oauth is valid
	if($context->valid) {
		$_SESSION['launch_params']['custom_canvas_user_id'] = $_POST['custom_canvas_user_id'];
		$_SESSION['launch_params']['custom_canvas_course_id'] = $_POST['custom_canvas_course_id'];
		$_SESSION['launch_params']['context_label'] = $_POST['context_label'];
		$_SESSION['launch_params']['context_title'] = $_POST['context_title'];
		$_SESSION['valid'] = true;
	} else {
		echo '
			<!DOCTYPE html>
			<html lang="en">
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
					<title>UDOIT Accessibility Checker</title>
					<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
					<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />
				</head>
				<body>
					<div style="padding: 12px;">
						<div class="alert alert-danger">
							<span class="glyphicon glyphicon-exclamation-sign"></span> Configuration problem, please ensure that your instance of UDOIT is configured correctly.
						</div>
					</div>
				</body>
			</html>
		';
		die();
	}
}

$redirect = true;

// Pull the API key from the database
$dsn = "mysql:dbname=$db_name;host=$db_host";

try {
	$dbh = new PDO($dsn, $db_user, $db_password);
} catch (PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
}

$sth = $dbh->prepare("SELECT * FROM $db_user_table WHERE id=:userid LIMIT 1");
$sth->bindParam(':userid', $_SESSION['launch_params']['custom_canvas_user_id'], PDO::PARAM_INT);
$sth->execute();

$result = $sth->fetchAll();

if( isset($result[0]) ){
	$_SESSION['api_key'] = $result[0]['api_key'];
}

// Do we have an API key?
if(isset($_SESSION['api_key'])){
	//If we do, test it out
	$url = $base_url.'/api/v1/users/'.$_SESSION['launch_params']['custom_canvas_user_id'].'/profile?access_token='.$_SESSION['api_key'];
	$resp = CURL::get($url, true, null, true);
	$redirect = !isset($resp['response']->id);
}else{
	//Otherwise, redirect to the oauth2 process
	$redirect = true;
}

// if the api key was invalid, or we didn't have an api key, start the oauth2 process
if( $redirect ){
	//Redirect user to oauth2 endpoint on the Canvas end
	session_write_close();
	header('Location: '.$base_url.'/login/oauth2/auth/?client_id='.$oauth2_id.'&response_type=code&redirect_uri='.$oauth2_uri);
}

// Invalidate the session so we start from scratch
$_SESSION['valid'] = false;

// print_r($_SESSION);
session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>UDOIT Accessibility Checker</title>
		<link rel="icon" type="image/png" href="favicon.ico">
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
		<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css" />
		<link href='https://fonts.googleapis.com/css?family=Sonsie+One' rel='stylesheet' type='text/css'>
		<link href="assets/css/main.css" type="text/css" rel="stylesheet" media="screen" />
		<link href="assets/css/print.css" type="text/css" rel="stylesheet" media="print"/>
	</head>
	<body>
		<div class="container">
			<header id="mainHeader" class="navbar navbar-default center">
				<h1 class="logo">UDOIT</h1>
			</header>
			<main id="contentWrapper" role="main">
				<form id="udoitForm" method="post" class="form-horizontal no-print" action="app/checker.php" role="form">
					<div class="form-group">
						<span class="col-sm-2 control-label"><strong>Select content:</strong></span>
						<div class="col-sm-10">
							<div class="checkbox">
								<label><input type="checkbox" value="all" id="allContent" class="content" name="content[]" checked> All</label>
							</div>
							<hr />
							<div class="checkbox">
								<label><input type="checkbox" value="announcements" class="content" name="content[]" checked> Announcements</label>
							</div>
							<div class="checkbox">
								<label><input type="checkbox" value="assignments" class="content" name="content[]" checked> Assignments</label>
							</div>
							<div class="checkbox">
								<label><input type="checkbox" value="discussions" class="content" class="content" name="content[]" checked> Discussions</label>
							</div>
							<div class="checkbox">
								<label><input type="checkbox" value="files" class="content" name="content[]" checked> Files</label>
							</div>
							<div class="checkbox">
								<label><input type="checkbox" value="pages" class="content" name="content[]" checked> Pages</label>
							</div>
						</div>
					</div>
					<hr />
					<div id="waitMsg" class="alert alert-warning" style="display: none;">
						<p><span class="glyphicon glyphicon-warning-sign"></span> Please stay on this page while UDOIT scans your course content.</p>
					</div>
					<button id="submit" type="submit" name="course_submit"  class="btn btn-block btn-lg btn-success">Run scanner</button>
				</form>
			</main>
		</div>
		<script type="text/javascript" src="assets/js/jquery.js"></script>
		<script type="text/javascript" src="assets/js/default.js"></script>
		<script type="text/javascript" src="assets/js/chooser.js"></script>
	</body>
</html>
