<?php
set_time_limit(30);

function is_printable($ssid)
{
	$ssid_len = strlen($ssid);

	for($i = 0; $i < $ssid_len; $i++)
	{
		$code = bin2int($ssid, $i, 1);
		if($code == 0x00)
			return false;
	}

	return true;
}

$pid = pid_open("/mmap/net1");

pid_ioctl($pid, "scan qsize 64");

pid_ioctl($pid, "scan start");
while(pid_ioctl($pid, "scan state"))
	;

?>
<!DOCTYPE html>
<html>
<head>
	<title>PHPoC</title>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=1.0, minimum-scale=0.5, user-scalable=yes">
	<link href="shield.css" rel="stylesheet" type="text/css">
	<script type="text/javascript">

	function select(ch)
	{
		window.opener.parent.document.phpoc_setup.channel.value = ch;

		window.close();
	}

	function search()
	{
		window.location.reload();
	}
	</script>
</head>
<body>

	<form name="searchap" method="post">
	<center>	
		<div class="mid-header popup-mid-hdr">
			<div class="header-title popup-hdr-title">Channel List</div>
			<div class="header-menu">
				<div class="right">
					<a href="javascript:search();">SEARCH</a>
				</div>
			</div>
		</div>
		<table class="popup-table">

		<?php
		for($ch = 1; $ch <= 14; $ch++)
		{
			if ($ch % 2 == 1)
				$tr_class = "zebra";
			else
				$tr_class = "";

		?>			
			<tr class="<?php echo $tr_class;?>">
		<?
			$n = pid_ioctl($pid, "scan result $ch");

			$total_ssid = "";
			for($id = 0; $id < $n; $id++)
			{	

				$scan = pid_ioctl($pid, "scan result $ch $id");
				if($scan)
				{
					$scan = explode(" ", $scan);
					
					$ch    = (int)$scan[0];
					$ssid  = hex2bin($scan[3]);
					
					if(!is_printable($ssid))
						continue;
			
					$total_ssid = $ssid . ", " . $total_ssid;
				}
				else
					break;
			}

			$total_ssid = substr($total_ssid, 0, strlen($total_ssid)-2); //remove last comma.
		?>

				<td class="theader wdh-100p"><?php echo "Channel $ch";?></td>
				<td class="wdh-300p"><?php echo $total_ssid;?></td>
				<td class="btn-field"><button type="button" onclick="select('<?php echo $ch?>')";>Select</button></td>
			</tr>		
		<?

		}
		pid_close($pid);

		?>		
		</table>
		<br>
	</center>	
	</form>
</body>
</html>