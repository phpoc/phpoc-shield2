<?php
include_once "config.php";
include_once "/lib/sc_envs.php";

$envs = envs_read();

if($wrp_width = envs_find($envs, ENV_CODE_APP_ANY, WRP_WIDTH))
	$wrp_width = bin2int($wrp_width, 0, 2);
else
	$wrp_width = 400;

if($wrp_button = envs_find($envs, ENV_CODE_APP_ANY, WRP_BUTTON))
	$wrp_button = rtrim($wrp_button);
else
	$wrp_button = "A,B,C,D,E,F,G,H,I,,,";

$push_text = explode(",", $wrp_button);
$push_text_len = count($push_text);
?>
<!DOCTYPE html>
<html>
<head>
<title>PHPoC Shield - Web Remote Push</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=0.7">
<style>
body {text-align: center; font-family: verdana, Helvetica, Arial, sans-serif, gulim; height: 750px; }
h1 {font-weight: bold; font-size: 20pt; padding-bottom: 5px; color: navy; }
h2 {font-weight: bold; font-size: 15pt; padding-bottom: 5px; }
button {font-weight: bold; font-size: 15pt; } 
.sub-footer {margin: 0 auto; position: relative; width:<?echo ($wrp_width>400? $wrp_width:400)?>px; }
.sub-footer a {position: absolute; font-size: 10pt; top: 3px; }
</style>
<script>

var push_info = [];
var wrp_button = "<?echo $wrp_button?>";
var push_text = wrp_button.split(",");
var push_length = push_text.length;
var push_font = "bold 20px Arial";
var ws;

var BOX_SIZE = <?echo(int)$wrp_width/3?>;
var KEY_SIZE = BOX_SIZE * 0.9;

var COL_NUM = 3;
var ROW_NUM = 4;

for (var i = push_length-1; i > COL_NUM-1; i-=COL_NUM)
{
	if (!(push_text[i]||push_text[i-1]||push_text[i-2])){
		ROW_NUM--;
		continue;
	}
	break;
}

function init()
{
	var remote = document.getElementById("remote");

	remote.width = BOX_SIZE * COL_NUM;
	remote.height = BOX_SIZE * ROW_NUM;
	
	var ctx = remote.getContext("2d");
	ctx.strokeStyle = "white";
	ctx.lineJoin = "round";

	for(var push_id = 0; push_id < push_length; push_id++)
	{
		push_info[push_id] = {state:false, identifier:null, font:push_font, text:push_text[push_id]};
		update_push(push_id, false);
	}

	remote.addEventListener("touchstart", mouse_down);
	remote.addEventListener("touchend", mouse_up);
	remote.addEventListener("touchmove", mouse_move);
	//remote.addEventListener("touchout", mouse_move);

	remote.addEventListener("mousedown", mouse_down);
	remote.addEventListener("mouseup", mouse_up);
	remote.addEventListener("mousemove", mouse_move);
	remote.addEventListener("mouseout", mouse_up);
}
function connect_onclick()
{
	if(ws == null)
	{
		var ws_host_addr = "<?echo _SERVER("HTTP_HOST")?>";

		if((navigator.platform.indexOf("Win") != -1) && (ws_host_addr.charAt(0) == "["))
		{
			// network resource identifier to UNC path name conversion
			ws_host_addr = ws_host_addr.replace(/[\[\]]/g, '');
			ws_host_addr = ws_host_addr.replace(/:/g, "-");
			ws_host_addr += ".ipv6-literal.net";
		}

		ws = new WebSocket("ws://" + ws_host_addr + "/remote_push", "text.phpoc");

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

	for(var push_id = 0; push_id < push_length; push_id++)
		update_push(push_id, false);
}
function ws_onclose()
{
	document.getElementById("ws_state").innerHTML = "<span style='color: gray'>CLOSED</span>";
	document.getElementById("bt_connect").innerHTML = "Connect";

	ws.onopen = null;
	ws.onclose = null;
	ws.onmessage = null;
	ws = null;

	for(var push_id = 0; push_id < push_length; push_id++)
		update_push(push_id, false);
}
function ws_onmessage(e_msg)
{
	e_msg = e_msg || window.event; // MessageEvent

	alert("msg : " + e_msg.data);
}
function update_push(push_id, state)
{
	var remote = document.getElementById("remote");
	var ctx = remote.getContext("2d");
	var push = push_info[push_id];
	var cx, cy;
	if (!push.text)
		return;
	if(ws && (ws.readyState == 1))
	{
		if(state)
			ctx.fillStyle = "royalblue";
		else
			ctx.fillStyle = "skyblue";
	}
	else
	{
		if(state)
			ctx.fillStyle = "gray";
		else
			ctx.fillStyle = "silver";
	}
	
	cx = BOX_SIZE * (push_id % COL_NUM) + BOX_SIZE / 2;
	cy = BOX_SIZE * parseInt(push_id / COL_NUM) + BOX_SIZE / 2;
	
	ctx.beginPath();
	
	ctx.lineWidth = 1;
	x = cx - KEY_SIZE / 2;
	y = cy - KEY_SIZE / 2;

	draw_curvedRect(ctx, x, y, KEY_SIZE);
	ctx.fill();
	ctx.stroke();

	ctx.font = push.font;
	ctx.textAlign = "center";
	ctx.textBaseline = "middle";
	ctx.fillStyle = "whitesmoke";
	ctx.fillText(push.text, cx, cy);

	push.state = state;

	if(!state)
		push.identifier = null;

	if(ws && (ws.readyState == 1))
	{
		if(state)
			ws.send(String.fromCharCode(0x41 + push_id)); // 'A' ~ 'I'
		else
			ws.send(String.fromCharCode(0x61 + push_id)); // 'a' ~ 'i'
	}
}

function draw_curvedRect(ctx, x, y, sz)
{
	var curved = sz/4;
	ctx.translate(x, y);
	ctx.moveTo(curved, 0);
	ctx.lineTo(sz - curved, 0);
	ctx.quadraticCurveTo(sz, 0,  sz, curved);
	ctx.lineTo(sz, sz - curved);
	ctx.quadraticCurveTo(sz, sz, sz - curved, sz);
	ctx.lineTo(curved, sz);
	ctx.quadraticCurveTo(0, sz, 0, sz - curved);
	ctx.lineTo(0, curved);
	ctx.quadraticCurveTo(0, 0, curved, 0);
	ctx.translate(-x, -y);
}

function find_push_id(x, y)
{
	var cx, cy, push_id;
	var ctx = remote.getContext("2d");
	var colors = ctx.getImageData(x, y, 1, 1).data;

	if ((colors[0] == 255) && (colors[1] == 255) && (colors[2] == 255))
		return push_length;

	if ((colors[0] == 0) && (colors[1] == 0) && (colors[2] == 0))
		return push_length;

	if((x < 0) || (x >= BOX_SIZE * COL_NUM))
		return push_length;

	if((y < 0) || (y >= BOX_SIZE * ROW_NUM))
		return push_length;

	push_id = parseInt(x / BOX_SIZE);
	push_id += COL_NUM * parseInt(y / BOX_SIZE);

	cx = BOX_SIZE * (push_id % COL_NUM) + BOX_SIZE / 2;
	cy = BOX_SIZE * parseInt(push_id / COL_NUM) + BOX_SIZE / 2;
	
	if( Math.abs(x - cx) < KEY_SIZE / 2 && Math.abs(y - cy) < KEY_SIZE / 2)
		return push_id;
	else
		return push_length;
}

function mouse_down(event)
{
	var x, y, push_id;

	if(event.changedTouches)
	{
		for(var touch_id = 0; touch_id < event.changedTouches.length; touch_id++)
		{
			var touch = event.changedTouches[touch_id];

			x = touch.pageX - touch.target.offsetLeft;
			y = touch.pageY - touch.target.offsetTop;

			push_id = find_push_id(x, y);

			if(push_id < push_length)
			{
				var push = push_info[push_id];

				if(push.state == false)
				{
					update_push(push_id, true);
					push.identifier = touch.identifier;

				}
			}
		}
	}
	else
	{
		x = event.offsetX;
		y = event.offsetY;

		push_id = find_push_id(x, y);

		if(push_id < push_length)
		{
			update_push(push_id, true);
		}
	}

	event.preventDefault();
}
function mouse_up(event)
{
	var push_id;

	if(event.changedTouches)
	{
		for(var touch_id = 0; touch_id < event.changedTouches.length; touch_id++)
		{
			var touch = event.changedTouches[touch_id];

			for(var push_id = 0; push_id < push_length; push_id++)
			{
				if(touch.identifier == push_info[push_id].identifier)
					break;
			}

			if(push_id < push_length)
			{
				update_push(push_id, false);
			}
		}
	}
	else
	{
		for(var push_id = 0; push_id < push_length; push_id++)
		{
			if(push_info[push_id].state)
			{
				update_push(push_id, false);
				break;
			}
		}
	}

	event.preventDefault();
}
function mouse_move(event)
{
	event.preventDefault();
}
window.onload = init;
</script>
</head>
<body>
	<h1>Web Remote Push</h1>
	<canvas id="remote"></canvas>
	<div class="sub-footer">
		<a href="index.php" style="left:0">HOME</a>
		<h2>WebSocket <span id="ws_state"><span style="color: gray">CLOSED</span></span></h2>
		<a href="setup_app.php#wrp" style="right:0">SETUP</a>
	</div>
	<button id="bt_connect" type="button" onclick="connect_onclick();">Connect</button>
</body>
</html>