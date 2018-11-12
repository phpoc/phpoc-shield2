<?php
include_once "enva.php";

$envs = envs_read();
$env_info = get_enva($envs, ENV_CODE_APP_ENV_DESC, ENV_DESC_ID_POC_FILE);

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
						<a href="setup_info.php">INFO</a>
						<a href="setup_net.php">NETWORK</a>
						<a href="setup_time.php">TIME</a>
						<a class="selected" href="setup_app.php">APP</a>
					</div>
					<div class="right"><a href="javascript:env()">SAVE</a></div>
                </div>
            </center>
        </div>
    </div>
    <br><br><br><br>
    <center>
	<br><br><br>
		<form name="phpoc_setup" action="setup_app_ok.php" method="post">
  			<input type="hidden" name="envs">
  			<input type="hidden" name="list">
			<input type="hidden" name="app">
		</form>
        <table id="appEnvTbl"></table>
    </center>
    <br><br><br><br>
    <div class="footer">
        <div class="super-footer"></div>
    </div>
</body>
<script language="javascript">
    var envData = String('<? envs_echo();?>');	
    var envInfo = <? if ($env_info){?>String('<? envs_echo($env_info);?>'); <?} else {?>envData;<?}?>

</script>
<script type="text/javascript" src="enva-1.0.min.js"></script>
<script language="javascript">
	var reqApp = document.phpoc_setup.app;
	var appName = location.hash;
	var appId = [];
	switch(appName)
	{
		case "#wsm":
			appId.push("fd03");
			reqApp.value = "wsm";
			break;
		case "#wsp":
			appId.push("fd1a");
			reqApp.value = "wsp";
			break;
		case "#wrp":
			appId.push("fd22");
			appId.push("fb21");
			reqApp.value = "wrp";
			break;
		case "#wrs":
			appId.push("fd34");
			reqApp.value = "wrs";
			break;
		case "#wpd":
			appId.push("fd42");
			reqApp.value = "wpd";
			break;
	}
	for (var i = 0, len = appId.length; i<len; i++) {
		var selectedApp = document.getElementById(appId[i] + "_icon");
		if (selectedApp)
			selectedApp.click();
	}
</script>
</html>