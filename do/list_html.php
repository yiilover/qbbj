<?php
require(dirname(__FILE__)."/"."global.php");

//��Ŀ����
get_guide($fid);


if(!is_writable(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist.php"))
{
	showerr("/cache/htm_cache/{$cacheid}_makelist.php�ļ�������,���ļ�����д");
}

set_time_limit(0);

$fidDB=$db->get_one("SELECT S.*,M.alias AS M_alias,M.config AS M_config FROM {$pre}sort S LEFT JOIN {$pre}article_module M ON S.fmid=M.id WHERE S.fid='$fid'");
$fidDB[M_alias] || $fidDB[M_alias]='����';
$fidDB[M_config]=unserialize($fidDB[M_config]);
$fidDB[config]=unserialize($fidDB[config]);
$fidDB[descrip]=En_TruePath($fidDB[descrip],0);
if($fidDB[type]==2){
	$rsdb[content]=$fidDB[descrip];
}

$fupId=intval($fidDB[type]?$fid:$fidDB[fup]);

//�����Ŀ����ģ��
if(is_file(html("$webdb[SideSortStyle]"))){
	$sortnameTPL=html("$webdb[SideSortStyle]");
}else{
	$sortnameTPL=html("side_sort/0");
}

//��Ŀ����ģ��
$aboutsortTPL=html("aboutsort_tpl/0");

//�������ʾ��ʽ
$fidDB[config][ListShowBigType] || $fidDB[config][ListShowBigType]=0;
unset($bigsortTPL);
if($fidDB[fmid]&&!$fidDB[config][ListShowBigType]){
	$bigsortTPL=html("bigsort_tpl/mod_$fidDB[fmid]");
}
if(!$bigsortTPL){
	$bigsortTPL=html("bigsort_tpl/0",ROOT_PATH."template/default/{$fidDB[config][ListShowBigType]}.htm");
}

//�����б���ʾ��ʽ.
$fidDB[config][ListShowType] || $fidDB[config][ListShowType]=0;
unset($listTPL);
if($fidDB[fmid]&&!$fidDB[config][ListShowType]){
	$listTPL=html("list_tpl/mod_$fidDB[fmid]");
}

if(!$listTPL){
	$listTPL=html("list_tpl/0",ROOT_PATH."template/default/{$fidDB[config][ListShowType]}.htm");
}

$atc_content='';


//��ת���ⲿ��ַ
if( $fidDB[jumpurl] ){
	$atc_content="<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$fidDB[jumpurl]'>";
	$atc_content=str_replace("?","?&",$atc_content);
}



//SEO
$titleDB[title]			= filtrate("$fidDB[name] - $webdb[webname]");
$titleDB[keywords]		= filtrate("$fidDB[metakeywords]  $webdb[metakeywords]");
$titleDB[description]	= filtrate("$fidDB[descrip]");

$fidDB[style] && $STYLE=$fidDB[style];

/*ģ��*/
$FidTpl=unserialize($fidDB[template]);
$head_tpl=$FidTpl['head'];
$foot_tpl=$FidTpl['foot'];

/**
*��ȡ��ǩ����,����ģ����б������$ch='2';$chtype=2,3,4,5,6,7,8,;
**/
$chdb[main_tpl]=html("list",$FidTpl['list']);

/**
*��ǩ
**/
$ch_fid	= intval($fidDB[config][label_list]);		//�Ƿ�������Ŀר�ñ�ǩ
$ch_pagetype = 2;									//2,Ϊlistҳ,3,Ϊbencandyҳ
$ch_module = 0;										//����ģ��,Ĭ��Ϊ0
$ch = 0;											//�������κ�ר��
require(ROOT_PATH."inc/label_module.php");





//��ʾ�ӷ���
$listdb_moresort=ListMoreSort();

//�б�ҳ����������
$Lrows=$fidDB[maxperpage]?$fidDB[maxperpage]:($webdb[list_row]?$webdb[list_row]:20);



$erp=$fidDB[iftable]?$fidDB[iftable]:"";
@extract($db->get_one("SELECT COUNT(aid) AS NUM FROM {$pre}article$erp WHERE fid=$fid AND yz=1"));














if($Ppage<1){
	$Ppage=1;
}
$Rows=20;
$Min=($Ppage-1)*$Rows;

//��ģ����չ�ӿ�
@include(ROOT_PATH."inc/list_{$fidDB[fmid]}.php");

require(ROOT_PATH."inc/head.php");
$content_head=ob_get_contents();

ob_end_clean();
ob_start();
require(ROOT_PATH."inc/foot.php");
$content_foot=ob_get_contents();
ob_end_clean();
ob_start();
unset($ckk);
unset($lfjuid,$web_admin,$lfjid,$lfjdb);
for($I=$Min;$I<$Min+$Rows;$I++){
	$page=$I+1;
	if($page!=1&&$page>ceil($NUM/$Lrows)){
		break;
	}

	if( $fidDB[passwd] ){//��Ŀ����
		$atc_content="<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$webdb[www_url]$webdb[path]/list.php?page=$page&fid=$fid&NeedCheck=1'>";
	}elseif( $fidDB[allowviewtitle] || $fidDB[allowviewcontent] ){//���Ȩ��
		$atc_content="<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$webdb[www_url]$webdb[path]/list.php?page=$page&fid=$fid&NeedCheck=1'>";
	}else{
		$listdb=ListThisSort($Lrows,$webdb[ListLeng]?$webdb[ListLeng]:50);	//����Ŀ�����б�
		$listdb || $hide_listnews='none';				//����Ǵ����Ļ�,�Ͳ����ڱ���,�Ͱѱ��������
		$showpage=getpage("","WHERE fid=$fid","list.php?fid=$fid",$Lrows,$NUM);
	}

	ob_end_clean();
	ob_start();
	require(html("list",$FidTpl['list']));


	
	$content=$atc_content?$atc_content:($content_head.ob_get_contents().$content_foot);
	$content=preg_replace("/<!--include(.*?)include-->/is","\\1",$content);
	
	make_html($content,'list');
	$ckk++;
}
ob_end_clean();

require_once(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist.php");
if($ckk)
{
	$Ppage++;
	//���������ɾ�̬,����Ҫ������״��
	if($JumpUrl){
		//�����һ����Ŀ�Ļ�,ֻ����ǰ��ҳ������
		if(is_numeric($allfid)){
			unlink(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist.php");
			header("location:$JumpUrl");exit;
		}
		//header("location:?fid=$fid&Ppage=$Ppage&III=$III");exit;
	}
	write_file(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist_record.php","?fid=$fid&Ppage=$Ppage&III=$III&cacheid=$cacheid");
	echo "���Ժ�,���������б�ҳ��̬...$Ppage<META HTTP-EQUIV=REFRESH CONTENT='0;URL=?fid=$fid&Ppage=$Ppage&III=$III&cacheid=$cacheid'>";
	exit;
}
else
{
	$III++;
	$Ppage=0;
	$fiddb=explode(",", $allfid);
	if($fid=$fiddb[$III]){
		//���������ɾ�̬,����Ҫ��״��
		if($JumpUrl){
			//header("location:?fid=$fid&Ppage=$Ppage&III=$III");exit;
		}
		write_file(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist_record.php","?fid=$fid&Ppage=$Ppage&III=$III&cacheid=$cacheid");
		echo '<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />';
		echo "���Ժ�,���������б�ҳ��̬...$fid<META HTTP-EQUIV=REFRESH CONTENT='0;URL=?fid=$fid&Ppage=$Ppage&III=$III&cacheid=$cacheid'>";
		exit;
	}else{
		unlink(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist_record.php");
		unlink(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist.php");
		//���������ɾ�̬,����Ҫ��״��
		if($JumpUrl){
			header("location:$JumpUrl");exit;
		}
		echo '<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />';
		echo "�б�̬ҳ�������,������������ҳ<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$weburl'>";
		exit;
	}
}
?>