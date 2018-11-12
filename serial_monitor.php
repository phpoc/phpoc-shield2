<?php
include_once "config.php";
include_once "/lib/sc_envs.php";
$envs = envs_read();
if($wsm_width = envs_find($envs, ENV_CODE_APP_ANY, WSM_WIDTH))
	$wsm_width = bin2int($wsm_width, 0, 2);
else
	$wsm_width = 400;
if($wsm_height = envs_find($envs, ENV_CODE_APP_ANY, WSM_HEIGHT))
	$wsm_height = bin2int($wsm_height, 0, 2);
else
	$wsm_height = 400;
?>
<html>
<head>
<title>PHPoC Shield - Web Serial Monitor</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7">
<style>
body {text-align: center; font-family: verdana, Helvetica, Arial, sans-serif, gulim; height: 750px; }
h1 {font-weight: bold; font-size: 20pt; padding-bottom: 5px; color: navy; }
h2 {font-weight: bold; font-size: 15pt; padding-bottom: 5px; }
button {font-weight: bold; font-size: 15pt; } 
textarea {width:<?echo$wsm_width?>px; height:<?echo$wsm_height?>px; padding:10px; font-family:courier; font-size:14px; }
.sub-footer {margin: 0 auto; position: relative; width:<?echo ($wsm_width>400? $wsm_width:400)?>px; }
.sub-footer a {position: absolute; font-size: 10pt; top: 3px; }
 </style>
<script>
var ws;
var wsm_max_len = 4096; /* bigger length causes uart0 buffer overflow with low speed smart device */
function connect_onclick()
{
	if(ws == null)
	{
		var ws_host_addr = "<?echo _SERVER("HTTP_HOST")?>";
		var debug = document.getElementById("debug");

		if((navigator.platform.indexOf("Win") != -1) && (ws_host_addr.charAt(0) == "["))
		{
			// network resource identifier to UNC path name conversion
			ws_host_addr = ws_host_addr.replace(/[\[\]]/g, '');
			ws_host_addr = ws_host_addr.replace(/:/g, "-");
			ws_host_addr += ".ipv6-literal.net";
		}

		//debug.innerHTML = "<br>" + navigator.platform + " " + ws_host_addr;
		ws = new WebSocket("ws://" + ws_host_addr + "/serial_monitor", "uint8.phpoc");

		document.getElementById("ws_state").innerHTML = "CONNECTING";

		ws.onopen = ws_onopen;
		ws.onclose = ws_onclose;
		ws.onmessage = ws_onmessage;
		ws.binaryType = "arraybuffer";
	}
	else
		ws.close();
}
function ws_onopen()
{
	document.getElementById("ws_state").innerHTML = "<span style='color: blue'>CONNECTED</span>";
	document.getElementById("bt_connect").innerHTML = "Disconnect";
}
function ws_onclose()
{
	document.getElementById("ws_state").innerHTML = "<span style='color: gray'>CLOSED</span>";
	document.getElementById("bt_connect").innerHTML = "Connect";

	ws.onopen = null;
	ws.onclose = null;
	ws.onmessage = null;
	ws = null;
}
function ws_onmessage(e_msg)
{
	e_msg = e_msg || window.event; // MessageEvent

	var wsm_text = document.getElementById("wsm_text");
	var len = wsm_text.value.length;
	var u8view = new Uint8Array(e_msg.data);

	if(len > (wsm_max_len + wsm_max_len / 10))
		wsm_text.innerHTML = wsm_text.value.substring(wsm_max_len / 10);

	//for(i = 0; i < u8view.length; i++)
	//	wsm_text.innerHTML += String.fromCharCode(u8view[i]);

	if(u8view[0] == 0x0a)
		wsm_text.innerHTML += String.fromCharCode.apply(null, u8view.slice(1));
	else
		wsm_text.innerHTML += String.fromCharCode.apply(null, u8view);

	wsm_text.scrollTop = wsm_text.scrollHeight;
}
function wsm_clear()
{
	document.getElementById("wsm_text").innerHTML = "";
}
</script>
</head>
<body>
	<h1>Web Serial Monitor</h1>
	<textarea id="wsm_text" readonly="readonly"></textarea><br>
	<div class="sub-footer">
		<a href="index.php" style="left:0">HOME</a>
		<h2>WebSocket <span id="ws_state"><span style="color: gray">CLOSED</span></span></h2>
		<a href="setup_app.php#wsm" style="right:0">SETUP</a>
	</div>
	<button id="bt_connect" type="button" onclick="connect_onclick();">Connect</button>
	<button id="bt_clear" type="button" onclick="wsm_clear();">Clear</button>
	<span id="debug"></span>
</body>
</html>
