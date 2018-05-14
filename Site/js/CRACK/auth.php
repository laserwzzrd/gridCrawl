<?php
	$userid = ip2long($_SERVER["REMOTE_ADDR"]).md5($_SERVER["HTTP_USER_AGENT"]);
	$userAgent = str_replace(" ", "_", $_SERVER["HTTP_USER_AGENT"]);
	$debug = "";
	if($_GET["debug00"] == "true")
	{
		$debug = true;
	}
	else
	{
		$debug = false;
	}
?>

var finishedInitializing = false;
var userid = "<?php echo($userid); ?>";
var userAgent = "<?php echo($userAgent); ?>";


