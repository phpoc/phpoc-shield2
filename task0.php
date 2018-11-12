<?php

if(_SERVER("REQUEST_METHOD"))
	exit; // avoid php execution via http request

include_once "config.php";
include_once "/lib/sn_dns.php";
include_once "/lib/sn_esmtp.php";

define("EAGAIN", 11);
define("EINVAL", 22);

include_once "cmd_smtp.php";

$pid_uio1 = pid_open("/mmap/uio1");
if(pid_ioctl($pid_uio1, "get 11 mode") == "und")
{
	pid_ioctl($pid_uio1, "set 11 mode in_pd");
	if(pid_ioctl($pid_uio1, "get 11 input"))
		$shield2 = true;
	else
		$shield2 = false;
	pid_ioctl($pid_uio1, "set 11 mode und");
}
else
	$shield2 = true;
pid_close($pid_uio1);

$ver_major = PHP_VERSION_ID / 10000;
$ver_minor = (PHP_VERSION_ID % 10000) / 100;
$ver_rev   = PHP_VERSION_ID % 100;

echo system("uname -m"), $shield2 ? " 2" : "";
echo " / ", system("uname -i"), " $ver_major.$ver_minor.$ver_rev\r\n";

if(PHP_VERSION_ID < 10500)
	exit("unsupported firmware version $ver_major.$ver_minor.$ver_rev\r\n");

$pid_pipe_cmd = pid_open("/mmap/pipe0b");
$pid_pipe_data = pid_open("/mmap/pipe1b");
pid_ioctl($pid_pipe_cmd, "set rxlen 0");
pid_ioctl($pid_pipe_data, "set rxlen 0");

$pid_uio0 = pid_open("/mmap/uio0");
pid_ioctl($pid_uio0, "set 30 mode led_net1_act");
pid_ioctl($pid_uio0, "set 31 mode led_net0_act");
pid_close($pid_uio0);

$rbuf = "";

while(1)
{
	global $pid_pipe_cmd;

	$len = pid_ioctl($pid_pipe_cmd, "get rxlen \r\n");

	if($len)
	{
		pid_read($pid_pipe_cmd, $rbuf, $len);

		$cmd = explode(" ", substr($rbuf, 0, -2));

		if(!count($cmd))
			pid_write($pid_pipe_cmd, int2bin(-EINVAL, 8));
		else
		{
			switch($cmd[0])
			{
				case "smtp":
					$retval = cmd_smtp($cmd);
					break;

				default:
					$retval = -EINVAL;
					break;
			}

			pid_write($pid_pipe_cmd, int2bin($retval, 8));
		}
	}

	usleep(1000);
}

?>
