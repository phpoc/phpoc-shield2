<?php  
if((int)ini_get("init_net0"))
	$pid_net = pid_open("/mmap/net0");
else
	$pid_net = pid_open("/mmap/net1");
$hwaddr = pid_ioctl($pid_net, "get hwaddr");
$ipaddr = pid_ioctl($pid_net, "get ipaddr");
$netmask = pid_ioctl($pid_net, "get netmask");
$gwaddr = pid_ioctl($pid_net, "get gwaddr");
$nsaddr = pid_ioctl($pid_net, "get nsaddr");

$ip6linklocal = pid_ioctl($pid_net, "get ipaddr6 0");
$ip6global = pid_ioctl($pid_net, "get ipaddr6 1");
$prefix6 = pid_ioctl($pid_net, "get prefix6");
$gw6addr = pid_ioctl($pid_net, "get gwaddr6");
$ns6addr = pid_ioctl($pid_net, "get nsaddr6");
pid_close($pid_net);

$pid_net1 = pid_open("/mmap/net1");
$wmode = pid_ioctl($pid_net1, "get mode");
$ssid = pid_ioctl($pid_net1, "get ssid");
$rssi = pid_ioctl($pid_net1, "get rssi");
$rsna = pid_ioctl($pid_net1, "get rsna");
$akm = pid_ioctl($pid_net1, "get akm");
$cipher = pid_ioctl($pid_net1, "get cipher");
pid_close($pid_net1);

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

?>
<!DOCTYPE html>
<html>
<head>
	<title>PHPoC Shield - Setup</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=1.0, minimum-scale=0.5, user-scalable=yes">
	<link href="shield.css" rel="stylesheet" type="text/css">
</head>
<body>
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
						<a class="selected" href="setup_info.php">INFO</a>
						<a href="setup_net.php">NETWORK</a>
						<a href="setup_time.php">TIME</a>
						<a href="setup_app.php">APP</a>
					</div>
				</div>
			</center>
		</div>
	</div>
	<br><br><br><br>
	<form name="phpoc_info">		
	<center>	
		<hr>
		<h1>System Information</h1>
		<table class="info-table">
			<tr class="zebra">
				<td class="theader wdh-40"><?php echo "Product name";?></td>
				<td>
					<?php echo system("uname -m"); if($shield2) echo " 2"; echo "\r\n"; ?>
				</td>
			</tr>
			<tr>
				<td class="theader"><?php echo "MAC address";?></td>
				<td>
					<?php echo $hwaddr;?>
				</td>
			</tr>
			<tr class="zebra">
				<td class="theader"><?php echo "Firmware name";?></td>
				<td>
					<?php echo system("uname -f") . "\r\n";?>
				</td>
			</tr>
			<tr>
				<td class="theader"><?php echo "Firmware version";?></td>
				<td>
					<?php echo system("uname -v") . "\r\n";?>
				</td>
			</tr>
		</table>

		<hr>
		<h1>Network Information</h1>
		<table class="info-table">
			<tr class="zebra">
				<td rowspan="4" class="theader wdh-10">IPv4</td>
				<td class="theader wdh-35"><?php echo "IP address";?></td>
				<td><?php echo $ipaddr;?></td>
			</tr>
			<tr>
				<td class="theader"><?php echo "Subnet mask";?></td>
				<td><?php echo $netmask;?></td>
			</tr>
			<tr class="zebra">
				<td class="theader"><?php echo "Gateway";?></td>
				<td><?php echo $gwaddr;?></td>
			</tr>
			<tr>
				<td class="theader"><?php echo "DNS Server";?></td>
				<td><?php echo $nsaddr;?></td>
			</tr>			
		</table>
		<?php 
		if(ini_get("init_ip6") == "1")	
		{
		?>
		<br>
		<table class="info-table">
			<tr class="zebra">
				<td rowspan="4" class="theader wdh-10">IPv6</td>
				<td class="theader wdh-35"><?php echo "Link Local";?></td>
				<td><?php echo $ip6linklocal;?></td>
			</tr>
			<tr>
				<td class="theader"><?php echo "Global";?></td>
				<td><?php echo $ip6global , " / " , $prefix6;?></td>
			</tr>
			<tr class="zebra">
				<td class="theader"><?php echo "Gateway";?></td>
				<td><?php echo $gw6addr;?></td>
			</tr>
			<tr>
				<td class="theader"><?php echo "DNS Server";?></td>
				<td><?php echo $ns6addr;?></td>
			</tr>
		</table>
		<?php
		}
		?>

		<?php  	
		if ($wmode != "")
		{
		?>
		<hr>
		<h1>Wireless LAN Information</h1>
		<table class="info-table">
			<tr class="zebra">
				<td class="theader wdh-40"><?php echo "WLAN mode";?></td>
				<td>
					<?php
					switch($wmode)
					{
						case "INFRA":
							$wmode = "Infrastructure";
							break;
						case "IBSS":
							$wmode = "Ad-hoc";
							break;
						case "AP":
							$wmode = "Soft AP";
							break;
					}

					echo $wmode;
					?>
				</td>
			</tr>
			<tr>
				<td class="theader"><?php echo "SSID";?></td>
				<td>
					<?php echo $ssid;?>
				</td>
			</tr>
			<tr class="zebra">
				<td class="theader"><?php echo "Signal strength";?></td>
				<td>
					<?php echo "-",$rssi,"dbm";?>
				</td>
			</tr>
			<tr>
				<td class="theader"><?php echo "Security";?></td>
				<td>
					<?php
					if($rsna == "")
						echo "NONE";
					else
						echo $rsna;
					?>
				</td>
			</tr>
			<tr class="zebra">
				<td class="theader"><?php echo "Key Management";?></td>
				<td>
					<?php
					if($akm == "")
						echo "-";
					else
						echo $akm;
					?>
				</td>
			</tr>
			<tr>
				<td class="theader"><?php echo "Encryption";?></td>
				<td>
					<?php  
					if($cipher == "")
						echo "-";
					else
						echo $cipher;
					?>
				</td>
			</tr>
		</table>
		<?php 
		}
		?>		
		<br><br><br>
	</center>
	</form>
	<div class="footer">
		<div class="super-footer"></div>
	</div>
</body>
</html>
