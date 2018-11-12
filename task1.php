<?php

include_once "config.php";
include_once "/lib/sc_envs.php";

function busy_wait()
{
	global $uart_pid, $ws_pid;

	$rbuf = "";

	sleep(1);

	pid_send($ws_pid, "\r\n");

	while(pid_read($uart_pid, $rbuf))
	{
		pid_send($ws_pid, "*");
		usleep(100000);
	}

	pid_send($ws_pid, "\r\n");
}

function error_wait()
{
	global $uart_pid, $ws_pid;

	$rbuf = "";

	sleep(1);

	pid_send($ws_pid, "\r\n");

	while(pid_ioctl($uart_pid, "get count fe") || pid_ioctl($uart_pid, "get count be"))
	{
		pid_ioctl($uart_pid, "set count fe 0");
		pid_ioctl($uart_pid, "set count be 0");

		pid_read($uart_pid, $rbuf);
		pid_send($ws_pid, "*");

		usleep(100000);
	}

	pid_send($ws_pid, "\r\n");

	//pid_ioctl($uart_pid, "set count fe 0");
}

$envs = envs_read();

if($wsm_baud = envs_find($envs, ENV_CODE_APP_ANY, WSM_BAUD))
	$wsm_baud = bin2int($wsm_baud, 0, 4);
else
	$wsm_baud = 9600;

$envs = "";

$uart_pid = pid_open("/mmap/uart0");
pid_ioctl($uart_pid, "set baud $wsm_baud");

$ws_pid = pid_open("/mmap/tcp6");
pid_ioctl($ws_pid, "set api ws");
pid_ioctl($ws_pid, "set ws path serial_monitor");
pid_ioctl($ws_pid, "set ws proto uint8.phpoc");
pid_ioctl($ws_pid, "set ws mode 1"); // binary mode
pid_bind($ws_pid, "", 0);

$rbuf = "";

while(1)
{
	$state = pid_ioctl($ws_pid, "get state");

	if($state == TCP_CONNECTED)
	{
		if(pid_ioctl($uart_pid, "get rxlen \x14\x10"))
		{
			busy_wait();
			continue;
		}

		if(pid_ioctl($uart_pid, "get count fe") || pid_ioctl($uart_pid, "get count be"))
		{
			error_wait();
			continue;
		}

		$rlen = pid_ioctl($uart_pid, "get rxlen");

		if($rlen && ($rlen > pid_ioctl($ws_pid, "get txfree")))
			$rlen = pid_ioctl($ws_pid, "get txfree");

		if($rlen)
		{
			pid_read($uart_pid, $rbuf, $rlen);
			pid_send($ws_pid, $rbuf);
		}
	}
	else
	{
		pid_read($uart_pid, $rbuf);

		//hexdump($rbuf);

		if($state == TCP_CLOSED)
			pid_listen($ws_pid);
	}

	usleep(10000); // long sleep causes uart0 rx buffer overflow
}

?>
