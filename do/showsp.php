<?php
require(dirname(__FILE__)."/"."global.php");

if(!$id&&$webdb[NewsMakeHtml]==2){
	//α��̬����
	Explain_HtmlUrl();
}

$Cache_FileName=ROOT_PATH."cache/showsp_cache/$id.php";
if(!$jobs&&$webdb[showsp_cache_time]&&(time()-filemtime($Cache_FileName))<($webdb[showsp_cache_time]*60)){
	echo read_file($Cache_FileName);
	exit;
}

@include(ROOT_PATH."data/guideSP_fid.php");		//ר����Ŀ�����ļ�




//������
$GuideFid[$fid]=str_replace("list.php?fid=","listsp.php?fid=",$GuideFid[$fid]);
if(!$GuideFid[$fid]){
	$GuideFid[$fid]="<A HREF='$webdb[www_url]'>&gt;&gt;������ҳ</A>";
}


$rsdb=$db->get_one("SELECT * FROM {$pre}special WHERE id='$id'");
$fid=$rsdb[fid];
if(!$rsdb){
	showerr("���ݲ�����!");
}elseif($rsdb[yz]!=1){
	if(!$web_admin&&$lfjuid!=$rsdb[uid]){
		showerr("��û��ͨ�����");
	}
}

/**
*��Ŀ�����ļ�
**/
$fidDB=$db->get_one("SELECT * FROM {$pre}spsort WHERE fid='$fid'");
$fidDB[config]=unserialize($fidDB[config]);
$FidTpl=unserialize($fidDB[template]);

//ǿ����ת����̬ҳ
if($webdb[ForbidShowPhpPage]&&!$NeedCheck){
	$detail=get_SPhtml_url($fidDB,$id,$rsdb[posttime]);
	if(is_file(ROOT_PATH.$detail[showurl])){
		header("location:$detail[showurl]");
		exit;
	}
}

//���
$STYLE = $rsdb[style] ? $rsdb[style] : ($fidDB[style] ? $fidDB[style] : $STYLE);

/**
*ģ��ѡ��
**/
$showTpl=unserialize($rsdb[template]);
$head_tpl=$showTpl[head]?$showTpl[head]:$FidTpl['head'];
$main_tpl=$showTpl[bencandy]?$showTpl[bencandy]:$FidTpl['bencandy'];
$foot_tpl=$showTpl[foot]?$showTpl[foot]:$FidTpl['foot'];

$chdb[main_tpl]=html("showsp",$main_tpl);			//��ȡ��ǩ����

$ch_fid	= intval($id);								//ÿ��ר��ı�ǩ��һ��
$ch_pagetype = 11;									//2,Ϊlistҳ,3,Ϊbencandyҳ
$ch_module = 0;										//����ģ��,Ĭ��Ϊ0
$ch = 0;											//�������κ�ר��

require(ROOT_PATH."inc/label_module.php");

unset($_listdb,$_picdb,$listdb,$picdb);
//ר���������
if($rsdb[aids])
{
	/*
	$query = $db->query("SELECT A.*,D.aid FROM {$pre}article_db D LEFT JOIN {$pre}article A ON D.aid=A.aid WHERE D.aid IN ($rsdb[aids])");
	while($rs = $db->fetch_array($query)){
		if(!$rs[title]&&$_rs=get_one_article($rs[aid])){
			$rs=$_rs+$rs;
		}
		$rs[url]="bencandy.php?fid=$rs[fid]&id=$rs[aid]";
		$rs[subject]="<a href='$rs[url]' target=_blank>$rs[title]</a>";
		$_listdb[$rs[aid]]=$rs;
	}
	*/

	$query = $db->query("SELECT A.*,D.content FROM  {$pre}article A  LEFT JOIN {$pre}reply D ON D.aid=A.aid WHERE D.topic=1 AND D.aid IN ($rsdb[aids])");
	while($rs = $db->fetch_array($query)){
		$rs[content]=preg_replace('/<([^<]*)>/is',"",$rs[content]);	//��HTML������˵�
		$rs[content]=preg_replace('/ |��|&nbsp;/is',"",$rs[content]);	//�Ѷ���Ŀո�ȥ����
		$rs[url]="$webdb[www_url]$webdb[path]/bencandy.php?fid=$rs[fid]&id=$rs[aid]";
		$rs[subject]="<a href='$rs[url]' target=_blank>$rs[title]</a>";
		$_listdb[$rs[aid]]=$rs;
	}

	//ͼƬ����
	$query = $db->query("SELECT * FROM {$pre}article WHERE aid IN ($rsdb[aids]) AND ispic=1");
	while($rs = $db->fetch_array($query)){
		$rs[url]="$webdb[www_url]$webdb[path]/bencandy.php?fid=$rs[fid]&id=$rs[aid]";
		$rs[subject]="<a href='$rs[url]' target=_blank>$rs[title]</a>";
		$rs[picurl]=tempdir($rs[picurl]);
		$_picdb[$rs[aid]]=$rs;
	}
	$aidsdb=explode(",",$rsdb[aids]);
	foreach($aidsdb AS $key=>$value){
		if($_listdb[$value]){
			$listdb[]=$_listdb[$value];
		}
		if($_picdb[$value]){
			$picdb[]=$_picdb[$value];
		}
	}
}

if($rsdb[tids])
{
	unset($_listdb,$_picdb);
	if(ereg("^pwbbs",$webdb[passport_type]))
	{
		$query = $db->query("SELECT * FROM {$TB_pre}threads WHERE tid IN ($rsdb[tids])");
		while($rs = $db->fetch_array($query)){
			$rs[url]="$webdb[passport_url]/read.php?tid=$rs[tid]";
			$rs[title]=$rs[subject];
			$rs[subject]="<a href='$rs[url]' target=_blank>$rs[subject]</a>";
			$_listdb[$rs[tid]]=$rs;
		}
		//ͼƬ����
		$query = $db->query("SELECT A.*,Att.attachurl FROM {$TB_pre}attachs Att LEFT JOIN {$TB_pre}threads A ON Att.tid=A.tid WHERE A.tid IN ($rsdb[tids]) AND Att.type='img' GROUP BY tid");
		while($rs = $db->fetch_array($query)){
			$rs[url]="$webdb[passport_url]/read.php?tid=$rs[tid]";
			$rs[title]=$rs[subject];
			$rs[picurl]=tempdir($rs[attachurl],'pwbbs');
			$rs[subject]="<a href='$rs[url]' target=_blank>$rs[subject]</a>";
			$_picdb[$rs[tid]]=$rs;
		}
	}
	elseif(ereg("^dzbbs",$webdb[passport_type]))
	{
		$query = $db->query("SELECT * FROM {$TB_pre}threads WHERE tid IN ($rsdb[tids])");
		while($rs = $db->fetch_array($query)){
			$rs[url]="$webdb[passport_url]/viewthread.php?tid=$rs[tid]";
			$rs[title]=$rs[subject];
			$rs[subject]="<a href='$rs[url]' target='_blank'>$rs[subject]</a>";
			$_listdb[$rs[tid]]=$rs;
		}
		//ͼƬ����
		$query = $db->query("SELECT A.*,Att.attachment FROM {$TB_pre}attachments Att LEFT JOIN {$TB_pre}threads A ON Att.tid=A.tid WHERE A.tid IN ($rsdb[tids]) AND Att.isimage='1' GROUP BY Att.tid");
		while($rs = $db->fetch_array($query)){
			$rs[url]="$webdb[passport_url]/viewthread.php?tid=$rs[tid]";
			$rs[title]=$rs[subject];
			$rs[picurl]=tempdir($rs[attachment],'dzbbs');
			$rs[subject]="<a href='$rs[url]' target='_blank'>$rs[subject]</a>";
			$_picdb[$rs[tid]]=$rs;
		}
	}
	$tidsdb=explode(",",$rsdb[tids]);
	foreach($tidsdb AS $key=>$value){
		if($_listdb[$value]){
			$listdb[]=$_listdb[$value];
		}
		if($_picdb[$value]){
			$picdb[]=$_picdb[$value];
		}
	}
}

if(!$listdb){
	$listdb[]=array("subject"=>"��ר����������,�����Ա�ں�̨�������");
}

//ͳ�Ƶ������
$db->query("UPDATE {$pre}special SET hits=hits+1,lastview='$timestamp' WHERE id='$id'");


//SEO
$titleDB[title]			= filtrate(strip_tags("$rsdb[title] - $fidDB[name] - $webdb[webname]"));
$titleDB[keywords]		= filtrate("$rsdb[keywords] $webdb[metakeywords]");
$rsdb[description]		= get_word(preg_replace("/(<([^<]+)>|	|&nbsp;|\n)/is","",$rsdb[content]),250);
$titleDB[description]	= filtrate($rsdb[description]);




//������ʵ��ַ��ԭ
$rsdb[content] = En_TruePath($rsdb[content],0);
$rsdb[content] = str_replace("\n","<br>",$rsdb[content]);

$rsdb[posttime] = date("Y-m-d H:i:s",$rsdb[posttime]);
$rsdb[picurl] && $rsdb[picurl] = tempdir($rsdb[picurl]);

require(ROOT_PATH."inc/head.php");
require(html("showsp",$main_tpl));
require(ROOT_PATH."inc/foot.php");

$content=ob_get_contents();
ob_end_clean();
ob_start();
if($webdb[www_url]=='/.'){
	$content=str_replace('/./','/',$content);
}
echo $content;


//α��̬����
if($webdb[NewsMakeHtml]==2)
{
	$content=ob_get_contents();
	ob_end_clean();
	ob_start();
	$content=fake_html($content);
	echo "$content";
}

if(!$jobs&&$webdb[showsp_cache_time]&&(time()-filemtime($Cache_FileName))>($webdb[showsp_cache_time]*60)){
	$content=ob_get_contents();
	if(!is_dir(dirname($Cache_FileName))){
		makepath(dirname($Cache_FileName));
	}
	write_file($Cache_FileName,$content);
}elseif($jobs=='show'){
	@unlink($Cache_FileName);
}
?>