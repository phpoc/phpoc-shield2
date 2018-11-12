<?php
include_once "enva.php";
include_once "/lib/sn_json_b1.php";

if(!_SERVER("HTTP_REFERER"))
{
	header('HTTP/1.1 403 Forbidden');
	$php_name = _SERVER("SCRIPT_NAME");
	echo "<html>\r\n",
		"<head><title>403 Forbidden</title></head>\r\n",
		"<body>\r\n",
		"<h1>Forbidden</h1>\r\n",
		"<p>You don't have permission to access /$php_name on this server.</p>\r\n",
		"</body></html>\r\n";
	return;
}

echo  "<html>",
		"<head><title>Processing</title></head>",
		"<body>",
		"<center><h3>Processing. Please wait!</h3>",
		"<a href=setup_app.php>Force reload</a><br><br>";

set_time_limit(10);
$list_env =	_POST("envs");
$list_key = _POST("list");
$req_app = _POST("app");
$app_url = "";
switch($req_app)
{
	case "wsm":
		$app_url = "serial_monitor.php";
		break;
	case "wsp":
		$app_url = "serial_plotter.php";
		break;
	case "wrp":
		$app_url = "remote_push.php";
		break;
	case "wrs":
		$app_url = "remote_slide.php";
		break;
	case "wpd":
		$app_url = "remote_pad.php";
		break;
	default:
		$app_url = "setup_app.php";
		break;
}

$list = explode(",", $list_key);
$len = count($list);
$envs = envs_read();

for ($i=0; $i<$len; $i++)
{
	$head = $list[$i];
	$key = explode(".", $head);
	set_enva($envs, (int)$key[0], (int)$key[1], json_text_value(json_search($list_env, $head)));
}

$wkey = envs_get_wkey(); 
envs_write($envs, $wkey);

echo "<h3>Setup completed. Please manually reload if this page is not automatically reloaded within 10 secs!</h3></body></html>";
system("reboot sys");

?>
<script type="text/javascript">
	setTimeout(function(){ window.self.location.replace('<? echo $app_url?>');}, 1000);
</script>