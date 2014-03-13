<?php
!function_exists('html') && exit('ERR');

@include_once(ROOT_PATH."data/label_hf.php");		//��ǩͷ����ײ����������ļ�
@include_once(ROOT_PATH."data/all_fid.php");		//ȫ����Ŀ�����ļ�
@include_once(ROOT_PATH."data/article_module.php");	//����ϵͳ����������ģ��

require_once(ROOT_PATH."inc/artic_function.php");		//�漰�����·���ĺ���

unset($lfjuid,$web_admin,$lfjid,$lfjdb,$groupdb,$bencandy_foot,$bencandy_head);
$groupdb=@include( ROOT_PATH."data/group/2.php");		//���ο���ݴ���

require_once(ROOT_PATH."inc/encode.php");
set_time_limit(0);

//�����Ŀ����ģ��
if(is_file(html("$webdb[SideSortStyle]"))){
	$sortnameTPL=html("$webdb[SideSortStyle]");
}else{
	$sortnameTPL=html("side_sort/0");
}

if($aid){
	$ASQL=" AND aid='$aid' ";
}else{
	$ASQL=" ";
}
if(is_array($bfid_array)){
	$SQL=" WHERE S.fid IN (".implode(',',$bfid_array).") ";
}elseif(is_numeric($bfid)){
	$SQL=" WHERE S.fid='$bfid' ";
}else{
	$SQL=" ";
}
$query_fid = $db->query("SELECT S.*,M.alias AS M_alias,M.keywords AS M_keyword,M.config AS M_config FROM {$pre}sort S LEFT JOIN {$pre}article_module M ON S.fmid=M.id $SQL");
while($fidDB = $db->fetch_array($query_fid)){

$fid = $fidDB[fid];
get_guide($fid);	//��Ŀ����
$fidDB[M_alias] || $fidDB[M_alias]='����';
$fidDB[M_config]=unserialize($fidDB[M_config]);
$fidDB[config]=unserialize($fidDB[config]);
$FidTpl=unserialize($fidDB[template]);
$fupId=intval($fidDB[type]?$fid:$fidDB[fup]);
$erp=$Fid_db[iftable][$fid]?$Fid_db[iftable][$fid]:'';

$Frows = 100;	//����ֵ����̫��,����������ڴ�֧�ֲ���
$steps = 0;

do{

$ifdo = false;
$steps++;
$min = ($steps-1)*$Frows;
$query_id = $db->query("SELECT * FROM {$pre}article$erp WHERE fid=$fid $ASQL ORDER BY aid DESC LIMIT $min,$Frows");
while($rsdb = $db->fetch_array($query_id)){
	$ifdo = true;
	$aid = $id = $rsdb[aid];
	$titleDB[title]		= filtrate(strip_tags("$rsdb[title] - $fidDB[name] - $webdb[webname]"));
	$titleDB[keywords]	= filtrate($rsdb[keywords]);
	

	if( $fidDB[allowviewcontent] || ($rsdb[begintime]&&$timestamp<$rsdb[begintime]) || ($rsdb[endtime]&&$timestamp>$rsdb[endtime]) || $rsdb[yz]!=1 || ($rsdb[passwd]||$fidDB[passwd]) || $rsdb[allowview] || $rsdb[jumpurl] || $rsdb[money] ){		
		$bencandy_content="<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$webdb[www_url]$webdb[path]/bencandy.php?&fid=$fid&id=$id&NeedCheck=1'>";
	}else{
		$bencandy_content='';
	}

	$STYLE = $rsdb[style] ? $rsdb[style] : ($fidDB[style] ? $fidDB[style] : $STYLE);

	if($rsdb[iframeurl]){	
		$head_tpl="template/default/none.htm";
		$main_tpl="template/default/none.htm";
		$foot_tpl="template/default/iframe.htm";
	}else{	
		$showTpl=unserialize($rsdb[template]);
		$head_tpl=$showTpl[head]?$showTpl[head]:$FidTpl['head'];
		$main_tpl=$showTpl[bencandy]?$showTpl[bencandy]:$FidTpl['bencandy'];
		$foot_tpl=$showTpl[foot]?$showTpl[foot]:$FidTpl['foot'];
	}
	$rsdb[posttime]=date("Y-m-d H:i:s",$rsdb[full_posttime]=$rsdb[posttime]);
	if($rsdb[copyfromurl]&&!strstr($rsdb[copyfromurl],"http://")){
		$rsdb[copyfromurl]="http://$rsdb[copyfromurl]";
	}

	//��һƪ����һƪ
	$nextdb=$db->get_one("SELECT title,aid,fid FROM {$pre}article$erp WHERE aid<'$id' AND fid='$fid' AND yz=1 ORDER BY aid DESC LIMIT 1");
	$nextdb[subject]=get_word($nextdb[title],34);
	$backdb=$db->get_one("SELECT title,aid,fid FROM {$pre}article$erp WHERE aid>'$id' AND fid='$fid' AND yz=1 ORDER BY aid ASC LIMIT 1");
	$backdb[subject]=get_word($backdb[title],34);

	//�����Զ���ģ��$fidDB[config]
	if($rsdb[mid]){
		if($rsdb[mid]!=$fidDB[fmid]){
			@extract($db->get_one("SELECT config AS m_config FROM {$pre}article_module WHERE id='$rsdb[mid]'"));
			$M_config=unserialize($m_config);
		}else{
			$M_config=$fidDB[M_config];
		}
		// AND rid='$rsdb[rid]'
		$_rsdb=$db->get_one("SELECT * FROM `{$pre}article_content_$rsdb[mid]` WHERE aid='$id'");
		if($_rsdb){
			$rsdb=$rsdb+$_rsdb;
			show_module_content($M_config);
		}
	}

	$rsdb[picurl]=tempdir($rsdb[picurl]);

	if($rsdb[keywords]){
		unset($array);
		$detail=explode(" ",$rsdb[keywords]);
		foreach( $detail AS $value){
			$_value=urlencode($value);
			$array[]="<A HREF='$webdb[www_url]/do/search.php?type=keyword&keyword=$_value' target=_blank>$value</A>";
		}
		$rsdb[keywords]=implode(" ",$array);
	}
	//���˲�������
	$rsdb[title]=replace_bad_word($rsdb[title]);

	//��ģ����չ�ӿ�
	//@include(ROOT_PATH."inc/bencandy_{$rsdb[mid]}.php");


	if($rsdb[mid]&&file_exists(html("bencandy_$rsdb[mid]",$main_tpl))){
		$chdb[main_tpl]=html("bencandy_$rsdb[mid]",$main_tpl);
	}else{
		$chdb[main_tpl]=html("bencandy",$main_tpl);
	}

	//��ȡ��ǩ����$chdb[main_tpl]
	$ch_fid	= intval($fidDB[config][label_bencandy]);	//�Ƿ�������Ŀר�ñ�ǩ
	$ch_pagetype = 3;									//2,Ϊlistҳ,3,Ϊbencandyҳ
	$ch_module = 0;										//����ģ��,Ĭ��Ϊ0
	$ch = 0;											//�������κ�ר��
	if($bencandy_tpl != $chdb[main_tpl]){
		require(ROOT_PATH."inc/label_module.php");
	}
	$bencandy_tpl = $chdb[main_tpl];
	ob_end_clean();

	if($foot_tpl!=$bencandy_foot||!isset($bencandy_foot)){
		ob_end_clean();
		ob_start();
		require(ROOT_PATH."inc/foot.php");
		$content_foot=ob_get_contents();
		ob_end_clean();
	}
	$bencandy_foot = $foot_tpl;

	ob_start();
	
	$_rsdb = $rsdb;
	$rsdb = '';
	$page = 1;
	do{
		$RMIN = $page-1;
		$reply = $db->get_one("SELECT * FROM {$pre}reply$erp WHERE aid=$aid ORDER BY topic DESC,orderid ASC LIMIT $RMIN,1");

		$rsdb=$_rsdb+$reply;

		$rsdb[description] || $rsdb[description]=get_word(preg_replace("/(<([^<]+)>|	|&nbsp;|\n)/is","",$rsdb[content]),250);
		$titleDB[description] = filtrate($rsdb[description]);
		
		//���˲�������
		$rsdb[content]=replace_bad_word($rsdb[content]);
		$rsdb[subhead]=replace_bad_word($rsdb[subhead]);
	
		$webdb[AutoTitleNum] && $rsdb[pages]>1 && $rsdb[title]=Set_Title_PageNum($rsdb[title],$page);
		$rsdb[content] = En_TruePath($rsdb[content],0,1);
		$rsdb[content]=preg_replace("/<IMG src=\"([^\"]+)\" border=0><A href=\"([^\"]+)\" target=_blank>([^<>]+)<\/A>/eis","encode_fileurl('\\1','\\2','\\3')",$rsdb[content]);
		$rsdb[content]=preg_replace("/href=\"([^\"]+)\"([^<>]+)p8name=\"p8download\">([^<>]+)<\/A>/eis","encode_fileurl('','\\1','\\3')",$rsdb[content]);


		$rsdb[content]=show_keyword($rsdb[content]);	//ͻ����ʾ�ؼ���
		$IS_BIZ && AvoidGather();	//���ɼ�����

		$showpage=getpage("","","bencandy.php?fid=$fid&aid=$aid",1,$rsdb[pages]);

		if(!$bencandy_content){
			ob_end_clean();
			ob_start();
			$MenuArray='';
			
			require(ROOT_PATH."inc/head.php");
			require($chdb[main_tpl]);

			$bencandy_content=ob_get_contents().$content_foot;
			$bencandy_content=preg_replace("/<!--include(.*?)include-->/is","\\1",$bencandy_content);
			$bencandy_content=str_replace("<!---->","",$bencandy_content);
		}

		make_html($bencandy_content,'bencandy');
		$bencandy_content = '';
		$page++;
		$ifpage=($page>$rsdb[pages])?false:true;
		$STEPS++;
		if($STEPS%100){
			sleep(1);	//ÿ����100ƪ��Ҫ��ͣһ��,��ֹ����������̫��
		}
	}
	while($ifpage);

}//��Ӧ�����������ȡ����query
}while($ifdo); //��Ӧ�����DO

/***********************��β***********************/

ob_end_clean();


}//��Ӧ�������Ŀquery
?>