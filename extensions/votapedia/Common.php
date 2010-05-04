<?php
/**
 * Convert page title into a friendly form, shorter and trimmed
 * 
 * @param $mytitle
 */
function vfGetPageTitle($mytitle)
{
	$mytitle = trim(stripslashes($mytitle));
	if(strlen($mytitle)>50)
	{
		$mytitle=substr($mytitle,0,50);
		$mytitle.='...';
	}
	return $mytitle;
}
/**
 * Return a message in error box, will show as red in HTML
 * 
 * @param $message
 */
function vfErrorBox($message)
{
	return '<div class="errorbox"><strong>'.$message.'</strong></div><div class="visualClear"></div>';
}
?>