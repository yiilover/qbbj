<?php
error_reporting(0);
extract($_GET);
require_once(dirname(__FILE__)."/../data/config.php");
if(!eregi("^(hot|com|new|lastview|like|pic)$",$type)){
	die("��������");
}
$fid=intval($fid);
$aid=intval($aid);
$id=intval($id);
$id || $id=$aid;
$FileName=dirname(__FILE__)."/../cache/jsarticle_cache/";
if($type=='like'){
	$FileName.=floor($id/3000)."/";
}else{
	unset($id);
}

$FileName.="{$type}_{$fid}_{$id}.php";
//Ĭ�ϻ���3����.
if(!$webdb["cache_time_$type"]){
	$webdb["cache_time_$type"]=3;
}
if( (time()-filemtime($FileName))<($webdb["cache_time_$type"]*60) ){
	@include($FileName);
	$show=str_replace(array("\n","\r","'"),array("","","\'"),stripslashes($show));
	if($iframeID){	//��ܷ�ʽ����������ҳ����ٶ�,�Ƽ�
		//�����������
		if($webdb[cookieDomain]){
			echo "<SCRIPT LANGUAGE=\"JavaScript\">document.domain = \"$webdb[cookieDomain]\";</SCRIPT>";
		}
		echo "<SCRIPT LANGUAGE=\"JavaScript\">
		parent.document.getElementById('$iframeID').innerHTML='$show';
		</SCRIPT>";
	}else{			//JSʽ��������ҳ����ٶ�,���Ƽ�
		echo "document.write('$show');";
	}
	exit;
}

require_once(dirname(__FILE__)."/global.php");

//Ĭ�ϻ���3����.
if(!$webdb["cache_time_$type"]){
	$webdb["cache_time_$type"]=3;
}

$show = listpage_title($fid,$type,$rows,$leng,$id,$keyword);


//�澲̬
if($webdb[NewsMakeHtml]==1||$gethtmlurl){

	$show=make_html($show,$pagetype='N');

//α��̬
}elseif($webdb[NewsMakeHtml]==2){

	$show=fake_html($show);
}


if($webdb[RewriteUrl]==1){	//ȫվα��̬
	rewrite_url($show);
}

$show=str_replace(array("\n","\r","'"),array("","","\'"),$show);

if($webdb[www_url]=='/.'){
	$show=str_replace('/./','/',$show);
}

if(!is_dir(dirname($FileName))){
	makepath(dirname($FileName));
}
if( (time()-filemtime($FileName))>($webdb["cache_time_$type"]*60) ){
	write_file($FileName,"<?php \r\n\$show=stripslashes('".addslashes($show)."'); ?>");
}

if($iframeID){	//��ܷ�ʽ����������ҳ����ٶ�,�Ƽ�
	//�����������
	if($webdb[cookieDomain]){
		echo "<SCRIPT LANGUAGE=\"JavaScript\">document.domain = \"$webdb[cookieDomain]\";</SCRIPT>";
	}
	echo "<SCRIPT LANGUAGE=\"JavaScript\">
	parent.document.getElementById('$iframeID').innerHTML='$show';
	</SCRIPT>";
}else{			//JSʽ��������ҳ����ٶ�,���Ƽ�
	echo "document.write('$show');";
}

?>