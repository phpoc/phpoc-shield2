<?php
include_once "config.php";
include_once "/lib/sc_envs.php";

$envs = envs_read();

if($wrp_width = envs_find($envs, ENV_CODE_APP_ANY, WPD_WIDTH))
	$wrp_width = bin2int($wrp_width, 0, 2);
else
	$wrp_width = 400;

if($wrp_height = envs_find($envs, ENV_CODE_APP_ANY, WPD_HEIGHT))
	$wrp_height = bin2int($wrp_height, 0, 2);
else
	$wrp_height = 400;

?>
<!DOCTYPE html>
<html>
<head>
<title>PHPoC Shield - Web Remote Pad</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=0.7">
<style>
body {text-align: center; font-family: verdana, Helvetica, Arial, sans-serif, gulim; height: 750px; }
h1 {font-weight: bold; font-size: 20pt; padding-bottom: 5px; color: navy; }
h2 {font-weight: bold; font-size: 15pt; padding-bottom: 5px; }
button {font-weight: bold; font-size: 15pt; } 
canvas {background: #000000; }
.sub-footer {margin: 0 auto; position: relative; width:<?echo ($wrp_width>400? $wrp_width:400)?>px; }
.sub-footer a {position: absolute; font-size: 10pt; top: 3px; }
</style>
<script>
var PAD_WIDTH = <?echo(int)$wrp_width?>;
var PAD_HEIGHT = <?echo(int)$wrp_height?>;
var STATE_UNTOUCH	= 'U';
var STATE_START		= 'S';
var STATE_MOVE		= 'M';
var touch_x = 0, touch_y = 0;
var touch_state = STATE_UNTOUCH;
var pre_x = -1, pre_y = -1;
var ws;

function init()
{
	var remote = document.getElementById("remote");

	remote.width = PAD_WIDTH;
	remote.height = PAD_HEIGHT;
	remote.style = "border:1px solid black";

	remote.addEventListener("touchstart", mouse_down);
	remote.addEventListener("touchmove", mouse_move);
	remote.addEventListener("touchend", mouse_up);
	remote.addEventListener("touchcancel", mouse_up);

	remote.addEventListener("mousedown", mouse_down);
	remote.addEventListener("mousemove", mouse_move);
	remote.addEventListener("mouseup", mouse_up);
	//remote.addEventListener("mouseout", mouse_up);
	//remote.addEventListener("mouseleave", mouse_up);
	document.body.addEventListener("mouseup", mouse_up);
	
	update_pad(touch_x, touch_y);
}
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

		ws = new WebSocket("ws://" + ws_host_addr + "/remote_pad", "text.phpoc");

		document.getElementById("ws_state").innerHTML = "CONNECTING";

		ws.onopen = ws_onopen;
		ws.onclose = ws_onclose;
		ws.onmessage = ws_onmessage;
	}
	else
		ws.close();
}
function ws_onopen()
{
	document.getElementById("ws_state").innerHTML = "<span style='color: blue'>CONNECTED</span>";
	document.getElementById("bt_connect").innerHTML = "Disconnect";
	document.getElementById("debug").style.color = "blue";
	update_pad(touch_x, touch_y);
}
function ws_onclose()
{
	document.getElementById("ws_state").innerHTML = "<span style='color: gray'>CLOSED</span>";
	document.getElementById("bt_connect").innerHTML = "Connect";
	document.getElementById("debug").style.color = "gray";
	update_pad(touch_x, touch_y);

	ws.onopen = null;
	ws.onclose = null;
	ws.onmessage = null;
	ws = null;
}
function ws_onmessage(e_msg)
{
	e_msg = e_msg || window.event; // MessageEvent

	alert("msg : " + e_msg.data);
}
function update_pad(x, y)
{
	var remote = document.getElementById("remote");
	var ctx = remote.getContext("2d");
	ctx.lineWidth = 1;
	
	/* plus 0.5 for thin line */
	x =  x + PAD_WIDTH / 2 + 0.5;
	y = -y + PAD_HEIGHT / 2 + 0.5;

	if(ws && (ws.readyState == 1))
		ctx.strokeStyle = "white";
	else
		ctx.strokeStyle = "gray";

	ctx.clearRect(0, 0, PAD_WIDTH, PAD_HEIGHT);

	ctx.beginPath();
	ctx.moveTo(x, 0);
	ctx.lineTo(x, PAD_HEIGHT);
	ctx.stroke();

	ctx.beginPath();
	ctx.moveTo(0, y);
	ctx.lineTo(PAD_WIDTH, y);
	ctx.stroke();
}
function touch_process(event)
{
	if(touch_state != STATE_UNTOUCH)
	{
		var x, y;
		if(event.changedTouches)
		{
			if( event.changedTouches.length > 1)
				return;

			var touch = event.changedTouches[0];

			x = Math.round(touch.pageX - touch.target.offsetLeft);
			y = Math.round(touch.pageY - touch.target.offsetTop);
		}
		else
		{
			x = Math.round(event.offsetX);
			y = Math.round(event.offsetY);
		}

		if(x < 0 || x > PAD_WIDTH || y < 0 || y > PAD_HEIGHT)
			return;

		if(pre_x == x && pre_y == y)
			return;

		pre_x = x;
		pre_y = y;

		touch_x = Math.round( x - PAD_WIDTH / 2);
		touch_y = Math.round(-y + PAD_HEIGHT / 2);
	}
	else
	{
		if(document.getElementById("bt_center").checked == true)
		{
			touch_x = 0;
			touch_y = 0;
		}
	}

	if(ws && (ws.readyState == 1))
		ws.send(touch_x + "," + touch_y + "," + touch_state + "\r\n");

	update_pad(touch_x, touch_y);
	document.getElementById("debug").innerHTML = "(" + touch_x + ", " + touch_y + ")";
}
function mouse_down(event)
{
	pre_x = -1;
	pre_y = -1;

	touch_state = STATE_START;
	touch_process(event);

	event.preventDefault();
}
function mouse_up(event)
{
	if(touch_state != STATE_UNTOUCH)
	{
		touch_state = STATE_UNTOUCH;
		touch_process(event);
	}

	event.preventDefault();
}
function mouse_move(event)
{
	if(touch_state != STATE_UNTOUCH)
	{
		touch_state = STATE_MOVE;
		touch_process(event);
	}

	event.preventDefault();
}
function bt_center_change()
{
	if(document.getElementById("bt_center").checked == true)
	{
		touch_x = 0;
		touch_y = 0;
		update_pad(touch_x, touch_y);
	}
}
window.onload = init;
</script>
</head>
<body>
	<h1>Web Remote Pad</h1>
	<canvas id="remote"></canvas>
	<div class="sub-footer">
		<a href="index.php" style="left:0">HOME</a>
		<h2>WebSocket <span id="ws_state"><span style="color: gray">CLOSED</span></span></h2>
		<a href="setup_app.php#wpd" style="right:0">SETUP</a>
	</div>
	<p>Coordinate: <span id="debug">(0, 0)</span></p>
	<button id="bt_connect" type="button" onclick="connect_onclick();">Connect</button>
	&nbsp;&nbsp;&nbsp;Return to Center<input id="bt_center" type="checkbox" onchange="bt_center_change()">
</body>
</html>