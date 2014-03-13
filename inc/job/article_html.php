<?php
!function_exists('html') && exit('ERR');

//本文件给发布文章的时候生成静态调用

$bfid=$fid;
if(is_array($bfid_array)){
	foreach($bfid_array AS $key=>$value){
		if(!is_numeric($value)){
			unset($bfid_array[$key]);
		}
	}
}
if($webdb[NewsMakeHtml]==1){
	if(is_numeric($bfid)&&is_numeric($aid)){	
		require_once(ROOT_PATH."inc/crontab/bencandy_html_crontab.php");
		require_once(ROOT_PATH."inc/crontab/list_html_crontab.php");
	}elseif(is_numeric($bfid)||is_array($bfid_array)){
		require_once(ROOT_PATH."inc/crontab/list_html_crontab.php");
	}
}

?>