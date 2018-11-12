<?php
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

function rtc_set_date($date)
{
	$pid_rtc = pid_open("/mmap/rtc0");
	pid_ioctl($pid_rtc, "set date $date");
	pid_close($pid_rtc);
}

$host_time         = _POST("host_time_txt");

rtc_set_date($host_time);

?>
<script type="text/javascript">
	window.self.location.replace("setup_time.php");	
</script>
