<?php

//
//create link to utkgraph/stackedGraphMy
//

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Group Surveys</title>
</head>

<body>
<img src="../loading.gif" alt="Graph" name="refresh"/>
<div id="countDownTime">
&nbsp;
</div>
<div id="debugMsg">
&nbsp;
</div>
<SCRIPT>
var timer;
var imageSwap=true;
var imageLoaded=false;
var refreshCount=4;
var myImage=new Image();

var image="../utkgraph/stackedGraphMy.php?<?php
$title1=$_GET['survey1'];
$title2=$_GET['survey2'];
echo "survey1=$title1&survey2=$title2";
?>";

var xmlhttp;
var tryLoadURLAgain=false;

function loadXMLDoc(url)
{
	// code for Mozilla, etc.
	if (window.XMLHttpRequest)
	{
		xmlhttp=new XMLHttpRequest()
		xmlhttp.onreadystatechange=xmlhttpChange
		xmlhttp.open("GET",url,true)
		xmlhttp.send(null)
	}
	// code for IE
	else if (window.ActiveXObject)
	{
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP")
		if (xmlhttp)
		{
			xmlhttp.onreadystatechange=xmlhttpChange
			xmlhttp.open("GET",url,true)
			xmlhttp.send()
		}
	}
}

function xmlhttpChange()
{
	// if xmlhttp shows "loaded"
	if (xmlhttp.readyState==4)
	{
		// if "OK"
		if (xmlhttp.status==200)
		{
			//the response may sometimes contain a warning message
			var url=xmlhttp.responseText;
			var begin=url.indexOf("../utkgraph");
			alert(xmlhttp.getResponseHeader("Content-Type"))
			if(begin==-1)
			{
				tryLoadURLAgain=true;
				return;
			}
			myImage.src = url.substring(begin, 5000);//return the rest of the string.
			imageSwap=false;
			tryLoadURLAgain=false;
			refreshCount=0;
		}
		else
		{
			tryLoadURLAgain=true;
		}
	}
}

function update()
{
	if(imageLoaded==false)
	{
		timer=setTimeout("update()",500);
		refreshCount+=1;
	}
	else
	{
		return;
	}
	
	if(refreshCount==5 || tryLoadURLAgain==true)
	{
		tmp = new Date();
		tmp = "&time="+tmp.getTime();
		tryLoadURLAgain=false;
		loadXMLDoc(image+tmp);
	}
		
	if(imageSwap==false)
	if(myImage.complete)
	{
		document.images["refresh"].src=myImage.src;
		myImage=new Image();
		imageSwap=true;
		imageLoaded=true;
	}
}
update();
</SCRIPT>
<p>
Original Surveys:<br />
<?php
$decodedTitle1=urldecode($title1);
$decodedTitle2=urldecode($title2);
echo '<a href="../index.php?title='.$title1.'">'.$decodedTitle1.'</a><br /><a href="../index.php?title='.$title2.'">'.$decodedTitle2.'</a>';
?>
</p>
</body>
</html>
