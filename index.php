<html>
<head>
<title>PHPoC Shield</title>
<meta name="viewport" content="width=device-width, initial-scale=0.7, maximum-scale=0.7">
<style type="text/css">
body {text-align: center; font-family: verdana, Helvetica, Arial, sans-serif, gulim; height:600px; }
img {vertical-align:middle; }
article {width: 75%; margin:0 auto; max-width: 1200px; position:absolute; left: 50%; }

section {width:100%;}
section:after {content:""; clear:both; display:block; }
section a {color: #000; }
section div {float:left; text-align: center; height: 150px; }
a div {margin-bottom: 20px; }

@media screen and (min-width:1600px) {
article {top: 100px; margin-left: -600px; }
section div {font-size: 15px; width:33.3%; }
}

@media screen and (min-width:800px) and (max-width:1599px) {
article {top: 100px; margin-left: -40%; }
section div {font-size: 15px; width:33.3%; }
}

@media screen and (max-width:799px){
article {top: 80px; margin-left: -40%; }
section div {font-size: 12px; width:50%; }
}
</style>
</head>
<body>
<article>
    <section>
        <a href="setup_info.php"><div><img src="icon_setup.png"><br><br>Setup</div></a>
<?if((int)ini_get("init_bcfg")){?>

<span style="color:red">
PHPoC Shield is running in SETUP mode.<br>
Web service is not available except SETUP.
</span>

<?}else{?>
        <a href="serial_monitor.php"><div><img src="icon_ws_monitor.png"><br><br>Web Serial <b>Monitor</b></div></a>
        <a href="serial_plotter.php"><div><img src="icon_ws_plotter.png"><br><br>Web Serial <b>Plotter</b></div></a>
        <a href="remote_push.php"><div><img src="icon_wrc_push.png"><br><br>Web Remote <b>Push</b></div></a>
        <a href="remote_slide.php"><div><img src="icon_wrc_slide.png"><br><br>Web Remote <b>Slide</b></div></a>
        <a href="remote_pad.php"><div><img src="icon_wrc_pad.png"><br><br>Web Remote <b>Pad</b></div></a>

<?}?>
    </section>
</article>
</body>
</html>