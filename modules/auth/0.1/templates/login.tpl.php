<?php 
// $Id: login.tpl.php 829 2010-11-05 04:07:09Z adam@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
<head>
	<title>Pyramid Power Flow</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel='stylesheet' href='<?=$webroot?>/css/login.css' type='text/css' media='all' />
    <link rel="icon" href="<?=$webroot?>/img/favicon.png" type="image/png"/>
</head>
<body class="login">
<div id="login">
<h1><a href="http://pyramidpower.com.au">Pyramid Power Flow</a></h1>
<form name="loginform" id="loginform" action="<?=$webroot."/auth/login"?>" method="post">
	<p>
		<label>Username<br />
		<input type="text" name="login" id="user_login" class="input" value="" size="20" tabindex="10" /></label>
	</p>
	<p>
		<label>Password<br />
		<input type="password" name="password" id="user_pass" class="input" value="" size="20" tabindex="20" /></label>

	</p>
	<p>
		<label style='display:none;'>Timezone<br/>
		<input type='text' name='user_timezone' id='timezone' value="" style='display:none;'/></label>
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" value="Log In to Flow" tabindex="100" />
	</p>
</form>
</div>
<center>
            		<b>IMPORTANT</b><br/><br/>
            		<b>Pyramid Power Flow</b> is optimised to work with the <a href="http://www.mozilla.com/en-US/firefox/">Firefox</a> web browser<br/>
            		and will not work correctly on other browsers, eg. Microsoft Internet Explorer or Google Chrome.<br/>
            		You can download it for free from the <a href="http://www.mozilla.com">Mozilla Foundation Website</a>.
            	</center> 
<script type="text/javascript">
var d = new Date();
d = d.getTimezoneOffset();
d = d/60*-1;   
d = d+"";
var temp_arr = new Array();
if (d.search('-') !== -1){
	d = d*-1;
	d = d+"";
	temp_arr = d.split(".");
	if (d.length == 1){			//Condition for whole hour < 10 i.e. -08:00
		d = "-0"+d+":00";
	} else if (d.length == 2) {	//Condition for whole hour >= 10 i.e. -11:00
		d = "-"+d+":00";		
	} else if (d.length == 3) {	//Condition for half hour < 10 i.e. -08:30		
		d = "-0" + temp_arr[0] + ":30";
	} else if (d.length == 4) { //Condition for half hour >= 10 i.e. -12:30		
		d = "-" + temp_arr[0] + ":30";
	}
} else {
	temp_arr = d.split(".");
	if (d.length == 1){			//Condition for whole hour < 10 i.e. +08:00
		d = "+0"+d+":00";
	} else if (d.length == 2) {	//Condition for whole hour >= 10 i.e. +11:00
		d = "+"+d+":00";		
	} else if (d.length == 3) {	//Condition for half hour < 10 i.e. +08:30
		d = "+0" + temp_arr[0] + ":30";
	} else if (d.length == 4) { //Condition for half hour >= 10 i.e. +12:30		
		d = "+" + temp_arr[0] + ":30";
	}
}
d = "GMT " + d;
//	d.replace('-',"");
//	if (d.length == 1){
//		d = "GMT -0"+d+"00";
//	} else if (d.length == 2) {
//		d = "GMT -"+d+"00";
//	} else {
//		d.replace(".5",":30");
//		d = "GMT -"+d;
//	}
//} else {
//    if (d.length == 1){
// 	  	d = "GMT +0" + d + "00";
//    } else if (d.length == 2) {
//		d = "GMT +" + d + "00";
//    } else {
//        d.replace(".5",":30");
//		d = "GMT +" + d;
//    }
      
try{document.getElementById("timezone").value = d;}catch(e){}

try{document.getElementById('user_login').focus();}catch(e){}

</script>
</body>
</html>
