<?php

function rtc_get_date()
{
	$pid_rtc = pid_open("/mmap/rtc0");
	$date = pid_ioctl($pid_rtc, "get date");
	pid_close($pid_rtc);

	$rtc_time = substr($date,  0, 4) . "-" . substr($date,  4, 2) . "-" . substr($date,  6, 2) . " " . substr($date,  8, 2) . ":" . substr($date,  10, 2) . ":" . substr($date, 12, 2); 

	return $rtc_time;
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>PHPoC Shield - Setup</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=1.0, minimum-scale=0.5, user-scalable=yes">
	<link href="shield.css" rel="stylesheet" type="text/css">
	<script type="text/javascript">
	function get_time()
	{
		var now = new Date();

		var year = now.getFullYear();
		var month = now.getMonth() + 1;
		if (month < 10)
			month = "0" + month;
		var day = now.getDate();
		if (day < 10)
			day = "0" + day;
		var h = now.getHours();
		if (h < 10)
			h = "0" + h;
		var m = now.getMinutes();
		if (m < 10)
			m = "0" + m;
		var s = now.getSeconds();
		if (s < 10)
			s = "0" + s;

		var host_time = year + '-' + month + '-' + day + " " + h + ':' + m + ':' + s;
		document.getElementById('host_time').innerHTML = host_time;

		return year.toString() + month.toString() + day.toString() + h.toString() + m.toString() + s.toString();
	}

	function time_sync()
	{
		var host_time = get_time();

		phpoc_setup.host_time_txt.value = host_time;
		phpoc_setup.submit();
	}
	</script>
</head>
<body onload="get_time();">
    <div id="header">
		<div class="super-header">
			<div class="right">
				<a href="https://www.phpoc.com/" target="_blank">PHPoC</a>
			</div>
		</div>
		<div class="mid-header">
			<center>
				<h1 class="header-title">SETUP</h1>
				<div class="header-menu">
					<div class="left">
						<a href="index.php">HOME</a>
						<a href="setup_info.php">INFO</a>
						<a href="setup_net.php">NETWORK</a>
						<a class="selected" href="setup_time.php">TIME</a>
						<a href="setup_app.php">APP</a>
					</div>
					<div class="right">
						<a href="javascript:time_sync();">TIME SYNC.</a>
					</div>
				</div>
			</center>
		</div>
	</div>
	<br><br><br><br>
	<form name="phpoc_setup" action="setup_time_ok.php" method="post">
	<center>
	<br><br><br>
		<table class="info-table">
			<tr class="zebra">
				<td class="theader wdh-40"><?php echo system("uname -m");?> Time</td>
				<td><?echo rtc_get_date()?></td>
			</tr>
			<tr>
				<td class="theader">Host local Time</td>	
				<td><input type="hidden" name="host_time_txt"><div id="host_time"></div></td>
			</tr>
		</table>
		<br><br><br><br>
		<strong>Note</strong> : Depending on the network environment, <br>the synchronization may not match with host local time.
	</center>	
	</form>
	<div class="footer">
		<div class="super-footer"></div>
	</div>	
</body>
</html>