<?php
require_once(dirname(__FILE__)."/"."../inc/common.inc.php");	//�����ļ�
require_once(ROOT_PATH."inc/artic_function.php");		//�漰�����·���ĺ���

@include_once(ROOT_PATH."data/ad_cache.php");		//�����������ļ�
@include_once(ROOT_PATH."data/label_hf.php");		//��ǩͷ����ײ����������ļ�
@include_once(ROOT_PATH."data/all_fid.php");		//ȫ����Ŀ�����ļ�
@include_once(ROOT_PATH."data/article_module.php");	//����ϵͳ����������ģ��

if(!$webdb[web_open])
{
	$webdb[close_why] = str_replace("\n","<br>",$webdb[close_why]);
	showerr("��վ��ʱ�ر�:$webdb[close_why]");
}

/**
*������ЩIP����
**/
$IS_BIZ && Limt_IP('AllowVisitIp');

$rows=intval($rows);
$ch=intval($ch);
unset($listdb,$rs);

//����JSʱ����ʾ��,����Ի���ͼƬ,'��Ҫ��\
$Load_Msg="<img alt=\"���ݼ�����,���Ժ�...\" src=\"$webdb[www_url]/images/default/ico_loading3.gif\">";

?>