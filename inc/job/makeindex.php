<?php
!function_exists('html') && exit('ERR');
if($webdb[MakeIndexHtmlTime]>0){
	$time=$webdb[MakeIndexHtmlTime]*60;
	$htmlname || $htmlname='index.htm';
	if((time()-@filemtime(ROOT_PATH.str_replace("/do","do/",$webdb[path])."$htmlname"))>$time){
		$phpname || $phpname='index.php';
		echo "<div style='display:none'><iframe src=$webdb[www_url]$webdb[path]/$phpname?ch=$ch&MakeIndex=1></iframe></div>";
	}
}
?>