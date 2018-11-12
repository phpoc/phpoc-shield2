<?php
include_once "config.php";
include_once "/lib/sc_envs.php";
$envs = envs_read();
if($wrs_width = envs_find($envs, ENV_CODE_APP_ANY, WRS_WIDTH))
	$wrs_width = bin2int($wrs_width, 0, 2);
else
	$wrs_width = 400;
if($wrs_length = envs_find($envs, ENV_CODE_APP_ANY, WRS_LEN))
	$wrs_length = bin2int($wrs_length, 0, 2);
else
	$wrs_length = 300;
if($wrs_value_max = envs_find($envs, ENV_CODE_APP_ANY, WRS_MAX))
	$wrs_value_max = bin2int($wrs_value_max, 0, 4);
else
	$wrs_value_max = 100;
if($wrs_value_min = envs_find($envs, ENV_CODE_APP_ANY, WRS_MIN))
{
	$wrs_value_min = bin2int($wrs_value_min, 0, 4);
	if($wrs_value_min > 0x7fffffff)
		$wrs_value_min -= 0x100000000;
}
else
	$wrs_value_min = -100;
?>
<!DOCTYPE html>
<html>
<head>
<title>PHPoC Shield - Web Remote Slide</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=0.7">
<style>
body {text-align: center; font-family: verdana, Helvetica, Arial, sans-serif, gulim; height: 750px; }
h1 {font-weight: bold; font-size: 20pt; padding-bottom: 5px; color: navy; }
h2 {font-weight: bold; font-size: 15pt; padding-bottom: 5px; }
button {font-weight: bold; font-size: 15pt; } 
#footer {width: 100%; margin: 0px;	padding: 0px 0px 10px 0px; bottom: 0px; }
.sub-footer {margin: 0 auto; position: relative; width:<?echo ($wrs_width>400? $wrs_width:400)?>px; }
.sub-footer a {position: absolute; font-size: 10pt; top: 3px; }
</style>
<script>
var SLIDE_WIDTH = <?echo $wrs_width/2?>;
var SLIDE_LENGTH = <?echo $wrs_length?>;
var VALUE_MIN = <?echo $wrs_value_min?>;
var VALUE_MAX = <?echo $wrs_value_max?>;
var BUTTON_WIDTH = parseInt(SLIDE_WIDTH * 0.8);
var BUTTON_HEIGHT = parseInt(BUTTON_WIDTH / 2);
var SLIDE_HEIGHT = parseInt(SLIDE_LENGTH + BUTTON_HEIGHT * 1.1);
var slide_info = [ null, null ];
var ws;
function init()
{
	var remote = document.getElementById("remote");

	remote.width = SLIDE_WIDTH * 2;
	remote.height = SLIDE_HEIGHT;
	remote.style = "border:1px solid black";

	slide_info[0] = {x:0, y:0, offset:0, state:false, identifier:null, ws_value:0};
	slide_info[1] = {x:0, y:0, offset:0, state:false, identifier:null, ws_value:0};

	slide_info[0].x = parseInt(SLIDE_WIDTH / 2);
	slide_info[0].y = parseInt(SLIDE_HEIGHT / 2);

	slide_info[1].x = parseInt(SLIDE_WIDTH + SLIDE_WIDTH / 2);
	slide_info[1].y = parseInt(SLIDE_HEIGHT / 2);

	update_slide(0, SLIDE_HEIGHT / 2);
	update_slide(1, SLIDE_HEIGHT / 2);

	remote.addEventListener("touchstart", mouse_down);
	remote.addEventListener("touchend", mouse_up);
	remote.addEventListener("touchmove", mouse_move);

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
		var debug = document.getElementById("debug");

		if((navigator.platform.indexOf("Win") != -1) && (ws_host_addr.charAt(0) == "["))
		{
			// network resource identifier to UNC path name conversion
			ws_host_addr = ws_host_addr.replace(/[\[\]]/g, '');
			ws_host_addr = ws_host_addr.replace(/:/g, "-");
			ws_host_addr += ".ipv6-literal.net";
		}

		//debug.innerHTML = "<br>" + navigator.platform + " " + ws_host_addr;
		ws = new WebSocket("ws://" + ws_host_addr + "/remote_slide", "text.phpoc");

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

	// draw active slide button
	for(slide_id = 0; slide_id < 2; slide_id++)
	{
		var slide = slide_info[slide_id];

		update_slide(slide_id, slide.y - slide.offset);
	}
}
function ws_onclose()
{
	document.getElementById("ws_state").innerHTML = "<span style='color: gray'>CLOSED</span>";
	document.getElementById("bt_connect").innerHTML = "Connect";

	ws.onopen = null;
	ws.onclose = null;
	ws.onmessage = null;
	ws = null;

	// draw inactive slide button
	for(slide_id = 0; slide_id < 2; slide_id++)
	{
		var slide = slide_info[slide_id];

		update_slide(slide_id, slide.y - slide.offset);
		slide.ws_value = 0;
	}
}
function ws_onmessage(e_msg)
{
	e_msg = e_msg || window.event; // MessageEvent

	alert("msg : " + e_msg.data);
}
function update_slide(slide_id, y)
{
	var debug = document.getElementById("debug");
	var remote = document.getElementById("remote");
	var ctx = remote.getContext("2d");
	var slide = slide_info[slide_id];
	var slide_top, slide_ratio, slide_value;

	slide_top = (SLIDE_HEIGHT - SLIDE_LENGTH) / 2;

	slide.y = y + slide.offset;

	if(slide.y < slide_top)
		slide.y = slide_top;

	if(slide.y > (slide_top + SLIDE_LENGTH))
		slide.y = slide_top + SLIDE_LENGTH;

	ctx.clearRect(SLIDE_WIDTH * slide_id, 0, SLIDE_WIDTH, SLIDE_HEIGHT);

	ctx.fillStyle = "silver";
	ctx.beginPath();
	ctx.rect(slide.x - 5, slide_top, 10, SLIDE_LENGTH);
	ctx.fill();

	if(ws && (ws.readyState == 1))
	{
		ctx.strokeStyle = "blue";
		if(slide.state)
			ctx.fillStyle = "blue";
		else
			ctx.fillStyle = "skyblue";
	}
	else
	{
		ctx.strokeStyle = "gray";
		if(slide.state)
			ctx.fillStyle = "gray";
		else
			ctx.fillStyle = "silver";
	}

	ctx.beginPath();
	ctx.rect(slide.x - BUTTON_WIDTH / 2, slide.y - BUTTON_HEIGHT / 2, BUTTON_WIDTH, BUTTON_HEIGHT);
	ctx.fill();
	ctx.stroke();

	ctx.font = "30px Arial";
	ctx.textBaseline = "top";
	ctx.fillStyle = "white";

	slide_ratio = (SLIDE_LENGTH - (slide.y - slide_top)) / SLIDE_LENGTH;       // 0 ~ 1
	slide_value = parseInt(slide_ratio * (VALUE_MAX - VALUE_MIN) + VALUE_MIN); // VALUE_MIN ~ VALUE_MAX

	if(slide_id == 0)
	{
		ctx.textAlign = "right";
		ctx.fillText(slide_value.toString(), slide.x + BUTTON_WIDTH / 2 - 5, slide.y - BUTTON_HEIGHT / 2);
	}
	else
	{
		ctx.textAlign = "left";
		ctx.fillText(slide_value.toString(), slide.x - BUTTON_WIDTH / 2 + 5, slide.y - BUTTON_HEIGHT / 2);
	}

	if(ws && (ws.readyState == 1))
	{
		//debug.innerHTML = slide.ws_value + "/" + slide_value;

		if(slide.ws_value != slide_value)
		{
			if(slide_id == 0)
				ws.send("A" + slide_value.toString() + "\r\n");
			else
				ws.send("B" + slide_value.toString() + "\r\n");

			slide.ws_value = slide_value;
		}
	}
}
function find_slide_id(x, y)
{
	var button_left, button_right, button_top, button_bottom;
	var slide_id, slide;

	if(x < SLIDE_WIDTH)
		slide_id = 0;
	else
		slide_id = 1;

	slide = slide_info[slide_id];

	button_left = slide.x - BUTTON_WIDTH / 2;
	button_right = slide.x + BUTTON_WIDTH / 2;
	button_top = slide.y - BUTTON_HEIGHT / 2;
	button_bottom = slide.y + BUTTON_HEIGHT / 2;

	if((x > button_left) && (x < button_right) && (y > button_top) && (y < button_bottom))
		return slide_id;
	else
		return 2;
}
function mouse_down(event)
{
	var debug = document.getElementById("debug");
	var x, y, slide_id;

	//debug.innerHTML = "";

	if(event.changedTouches)
	{
		for(var id = 0; id < event.changedTouches.length; id++)
		{
			var touch = event.changedTouches[id];

			x = touch.pageX - touch.target.offsetLeft;
			y = touch.pageY - touch.target.offsetTop;

			slide_id = find_slide_id(x, y);

			//debug.innerHTML += slide_id + "/" + x + "/" + y + " ";
			//debug.innerHTML += slide_id + "/" + touch.identifier + " ";

			if(slide_id < 2)
			{
				var slide = slide_info[slide_id];

			 	if(!slide.state)
				{
					slide.offset = slide.y - y;
					slide.identifier = touch.identifier;
					slide.state = true;

					update_slide(slide_id, y);
				}
			}
		}
	}
	else
	{
		x = event.offsetX;
		y = event.offsetY;

		slide_id = find_slide_id(x, y);

		if(slide_id < 2)
		{
			var slide = slide_info[slide_id];

			slide.offset = slide.y - y;
			slide.state = true;

			update_slide(slide_id, y);
		}
	}

	event.preventDefault();
}
function mouse_up(event)
{
	var debug = document.getElementById("debug");
	var slide_id;

	//debug.innerHTML = "";

	if(event.changedTouches)
	{
		for(var id = 0; id < event.changedTouches.length; id++)
		{
			var touch = event.changedTouches[id];

			if(touch.identifier == slide_info[0].identifier)
				slide_id = 0;
			else
			if(touch.identifier == slide_info[1].identifier)
				slide_id = 1;
			else
				slide_id = 2;

			if(slide_id < 2)
			{
				var slide = slide_info[slide_id];

				slide.state = false;
				slide.identifier = null;

				if(document.getElementById("bt_center").checked == true)
				{
					slide.offset = 0;
					update_slide(slide_id, SLIDE_HEIGHT / 2);
				}
				else
					update_slide(slide_id, slide.y - slide.offset);
			}
		}
	}
	else
	{
		if(slide_info[0].state)
			slide_id = 0;
		else
		if(slide_info[1].state)
			slide_id = 1;
		else
			slide_id = 2;

		if(slide_id < 2)
		{
			var slide = slide_info[slide_id];

			slide.state = false;

			if(document.getElementById("bt_center").checked == true)
			{
				slide.offset = 0;
				update_slide(slide_id, SLIDE_HEIGHT / 2);
			}
			else
				update_slide(slide_id, slide.y - slide.offset);
		}
	}

	event.preventDefault();
}
function mouse_move(event)
{
	var debug = document.getElementById("debug");
	var x, y, slide_id, offset;

	//debug.innerHTML = "";

	if(event.changedTouches)
	{
		for(var id = 0; id < event.changedTouches.length; id++)
		{
			var touch = event.changedTouches[id];

			if(touch.identifier == slide_info[0].identifier)
				slide_id = 0;
			else
			if(touch.identifier == slide_info[1].identifier)
				slide_id = 1;
			else
				slide_id = 2;

			if(slide_id < 2)
			{
				x = touch.pageX - touch.target.offsetLeft;
				y = touch.pageY - touch.target.offsetTop;

				update_slide(slide_id, y);
			}
		}
	}
	else
	{
		if(slide_info[0].state)
			slide_id = 0;
		else
		if(slide_info[1].state)
			slide_id = 1;
		else
			slide_id = 2;

		if(slide_id < 2)
		{
			x = event.offsetX;
			y = event.offsetY;

			update_slide(slide_id, y);
		}
	}

	event.preventDefault();
}
function bt_center_change()
{
	if(document.getElementById("bt_center").checked == true)
	{
		for(slide_id = 0; slide_id < 2; slide_id++)
		{
			var slide = slide_info[slide_id];

			slide.offset = 0;
			update_slide(slide_id, SLIDE_HEIGHT / 2);
		}
	}
}
window.onload = init;
</script>
</head>
<body>
<h1>Web Remote Slide</h1>
<canvas id="remote"></canvas>
<div class="sub-footer">
	<a href="index.php" style="left:0">HOME</a>
	<h2>WebSocket <span id="ws_state"><span style="color: gray">CLOSED</span></span></h2>
	<a href="setup_app.php#wrs" style="right:0">SETUP</a>
</div>
<button id="bt_connect" type="button" onclick="connect_onclick();">Connect</button>
&nbsp;&nbsp;&nbsp;Return to Center<input id="bt_center" type="checkbox" onchange="bt_center_change()">
<span id="debug"></span>

</body>
</html>
