<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>
CKEDITOR.on('instanceReady',function(ev)
		{
			InitializeTimer();
		});

function getInputContent()
{	
	var oEditor = CKEDITOR.instances.page_body;

	return oEditor.getData();
}
</script>

<script>
var inverval;

var timerID = null;

var isRunning = false;

var delay = 1000;

function InitializeTimer()
{
    inverval = 60;
    
    StopTheClock();
    
    StartTheTimer();
}

function StopTheClock()
{
    if(isRunning)
    {
        clearTimeout(timerID);
    }
    isRunning = false;
}

function StartTheTimer()
{
    if (inverval == 0)
    {
        StopTheClock();

        autoSave();

        InitializeTimer();
    }
    else
    {
        self.status = inverval;
        
        inverval = inverval - 1;
        
        isRunning = true;
        
        timerID = self.setTimeout("StartTheTimer()", delay);
    }
}

function autoSave()
{
	var message = getInputContent();
	
	if (window.XMLHttpRequest)
  	{
  		xmlhttp = new XMLHttpRequest();
  	}
	else
  	{
  		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  	}

	xmlhttp.onreadystatechange = function()
  	{
  		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
	    {
	    	//alert(xmlhttp.responseText);
	    }
  	}
  	
	xmlhttp.open("POST","/pages/timer/" + <?=$id?>,true);
	
	xmlhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	
	xmlhttp.send("body=" + message);
}
</script>

<?=$editForm?>

<script type='text/javascript'>
    CKEDITOR.replace( 'page_body' ,
    {
        toolbar : 'Full'
    });
</script>

<script type="text/javascript" charset="utf-8">
$('#cancel').click(function(){

	history.back();
});
</script>