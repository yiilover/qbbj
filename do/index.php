<?php
if(file_exists(dirname(__FILE__)."/../".'install.php')){
	header("location:install.php");exit;
}elseif(file_exists(dirname(__FILE__)."/../".'upgrade.php')){
	header("location:upgrade.php");exit;
}
require(dirname(__FILE__)."/"."global.php");

$Cache_FileName=ROOT_PATH."cache/list_cache/index.php";
if(!$jobs&&!$MakeIndex&&$ch<2&&$webdb[index_cache_time]&&(time()-filemtime($Cache_FileName))<($webdb[index_cache_time]*60)){
	echo read_file($Cache_FileName);
	exit;
}

require(ROOT_PATH."data/friendlink.php");

/*Ĭ��Ϊ��ҳƵ��*/
$ch || $ch=1;

$chdb=$db->get_one(" SELECT * FROM {$pre}channel WHERE id='$ch' ");
$head_tpl=$chdb[head_tpl];
$foot_tpl=$chdb[foot_tpl];
$chdb[style] && $STYLE=$chdb[style];		//Ƶ�������˷����Ƶ��Ϊ��

//����Ƶ����SEO
if($chdb[id]!=1)
{
	$titleDB[title]="$chdb[name] - $titleDB[title]";
	$titleDB[keywords]=$titleDB[description]="$titleDB[title] - $titleDB[keywords]";
}

/**
*�Զ����ʾ�̬��ҳ,�����еķ�����,index.php�ķ���������index.htm,��index.html
**/
if($webdb[MakeIndexHtmlTime]>0&&!$MakeIndex&&!$jobs&&is_file(ROOT_PATH.$chdb[htmlname])){
	header("location:$webdb[www_url]/$chdb[htmlname]");
	exit;
}

/**
*fid��ĿFID��Ϊ0,pagetypeҳ�����Ͷ���0(��ʵΪ1��,ʡ�Է���Щ),module����Ϊ0
**/
$ch_fid	= $ch_pagetype = $ch_module = 0;
require(ROOT_PATH."inc/label_module.php");


/**
*��ҳ��ʾ�Ƽ���Ŀ
**/
if($chdb[fids])
{
	$chdb[config]=unserialize($chdb[config]);
	$fiddb_article=fiddb_article($chdb[fids],$chdb[config][rows]>0?$chdb[config][rows]:10,$chdb[config][leng]>0?$chdb[config][leng]:40,$chdb[config][order]?$chdb[config][order]:'list');
	$fiddb_article || $fiddb_article=array();
}

//require(ROOT_PATH."inc/head.php");
require(html("index",$chdb[main_tpl]));
//require(ROOT_PATH."inc/foot.php");

//α��̬
if($webdb[NewsMakeHtml]==2)
{
	$content=ob_get_contents();
	ob_end_clean();
	ob_start();
	$content=fake_html($content);
	echo "$content";
}
//�澲̬
elseif($webdb[NewsMakeHtml]==1)
{
	$content=ob_get_contents();
	ob_end_clean();
	ob_start();			//����
	$content=make_html($content,'N');
	echo "$content";
}

if(!$jobs&&!$MakeIndex&&$ch<2&&$webdb[index_cache_time]&&(time()-filemtime($Cache_FileName))>($webdb[index_cache_time]*60)){
	
	if(!is_dir(dirname($Cache_FileName))){
		makepath(dirname($Cache_FileName));
	}
	write_file($Cache_FileName,$content);
}elseif($jobs=='show'){
	@unlink($Cache_FileName);
}

/*��ҳ����̬*/
if( $MakeIndex ){
	write_file(ROOT_PATH.str_replace("/do","do/",$webdb[path]).$chdb[htmlname],$content);
	ob_end_clean();
	echo "<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$webdb[www_url]$webdb[path]/$chdb[htmlname]'>";
}

?>