<?php
include_once "/lib/sc_envs.php";

function set_enva(&$envs, $req_code, $req_id, $env_val)
{
	switch($req_code)
	{
		case ENV_CODE_APP_BOOL:
		case ENV_CODE_APP_UINT16:
			$env_data = int2bin((int)$env_val, 2);
			break;
		case ENV_CODE_APP_INT32:
			$env_data = int2bin((int)$env_val, 4);
			break;
		case ENV_CODE_APP_ASC_STR:
		case ENV_CODE_APP_CSV_STR:
			$env_data = $env_val;
			break;
		case ENV_CODE_APP_IP4:
		case ENV_CODE_APP_IP6:
			$env_data = inet_pton($env_val);
			break;
		case ENV_CODE_APP_BIN_STR:
		case ENV_CODE_APP_FP32:
		case ENV_CODE_APP_MAC48:
		case ENV_CODE_APP_UART:
			$env_data = hex2bin($env_val);
			break;
		default:
			exit("set_enva: Currently not support env code $req_code\r\n");
	}

	envs_update($envs, $req_code, $req_id, $env_data);
}

function signed_int32($val) {
    $val = (int)$val;
    return ($val & 0x80000000)? $val - 0x100000000: $val;
} 


function get_enva(&$envs, $req_code, $req_id)
{
	$offset_end = strlen($envs);
	$offset = 0;

	while($offset < $offset_end)
	{
		$env_code = bin2int($envs, $offset + 0, 1);
		$env_id   = bin2int($envs, $offset + 1, 1);
		$blk_len  = bin2int($envs, $offset + 2, 2);
		$found = false;

		if($req_code == ENV_CODE_APP_ANY)
		{
			if(($env_code >= 0x80) && ($env_id == $req_id))
				$found = true;
		}
		else
		{
			if(($env_code == $req_code) && ($env_id == $req_id))
				$found = true;
		}

		if($found)
		{
			if(($blk_len <= 8) || ($blk_len & 3))
				exit("get_enva: $req_code/$req_id invalid blk_len $blk_len\r\n");

			$env_blk = substr($envs, $offset, $blk_len);

			$max_len = sc_envs_max_len($env_blk);

			if($max_len <= 0)
				exit("get_enva: $req_code/$req_id invalid max_len $max_len\r\n");

			$env_crc = bin2int($env_blk, $blk_len - 2, 2);

			if($env_crc != (int)system("crc 16 %1", substr($env_blk, 0, $blk_len - 2)))
				exit("get_enva: $req_code/$req_id crc error\r\n");

			$env_data = substr($env_blk, 4, $max_len);
			if (!$env_data)
				return "";
			switch($env_code)
			{
				case ENV_CODE_APP_BOOL:
				case ENV_CODE_APP_UINT16:
					return bin2int($env_data, 0, 2);
				case ENV_CODE_APP_INT32:
					return signed_int32(bin2int($env_data, 0, 4));
				case ENV_CODE_APP_ASC_STR:
				case ENV_CODE_APP_CSV_STR:
					$asc_len = bin2int($env_blk, $max_len+6, 2);
					return substr($env_data, 0, $asc_len);
				case ENV_CODE_APP_BIN_STR:
					$bin_len = bin2int($env_blk, $max_len+6, 2);
					return bin2hex(substr($env_data, 0, $bin_len));
				case ENV_CODE_APP_IP4:
				case ENV_CODE_APP_IP6:
					return inet_ntop($env_data);
				case ENV_CODE_APP_FP32:
					return bin2float($env_data, 0);
				case ENV_CODE_APP_ENV_DESC:
					if (($req_id == ENV_DESC_ID_POC_FILE)||($req_id == ENV_DESC_ID_INI_FILE)) {
						return rtrim(substr($env_data, 0, $max_len));
					}
				default:
					exit("get_enva: Currently not support env code $req_code ($env_code)\r\n");
			}
		}
		if($env_code == 0xff)
			break;
		$offset += $blk_len;
	}
	return "";
}


function get_uart_opt(&$envs, $req_code, $req_id, &$baud, &$opt)
{
	$uart_env = envs_find($envs, $req_code, $req_id);
	if (!$uart_env)
		return false;
	$baud = bin2int($uart_env,0,4);
	$opts = bin2int($uart_env,4,1);
	switch (($opts >>5)&0x7)
	{
		case 0:
			$parity = "N";
			break;
		case 1:
			$parity = "E";
			break;
		case 2:
			$parity = "O";
			break;
		case 3:
			$parity = "M";
			break;
		case 4:
			$parity = "S";
			break;
		default:
			$parity = "";
			break;
	} 
	switch (($opts >>2)&0x7)
	{
		case 2:
			$data_bit = "7";
			break;
		case 3:
			$data_bit = "8";
			break;
		default:
			$data_bit = "";
			break;
	}
	switch ($opts&0x3)
	{
		case 0:
			$stop_bit = "1";
			break;
		case 2:
			$stop_bit = "2";
			break;
		default:
			$stop_bit = "";
			break;
	}
	$fc = bin2int($uart_env,5,1);
	switch (($fc >>5)&0x7)
	{
		case 0:
			$flow_ctrl = "N";
			break;
		case 1:
			$flow_ctrl = "H";
			break;
		case 3:
			$flow_ctrl = "S";
			break;
		default:
			$flow_ctrl = "";
			break;
	}

	$opt = $parity.$data_bit.$stop_bit.$flow_ctrl;
}

?>