<?php
require(dirname(__FILE__)."/"."global.php");
require_once(ROOT_PATH."inc/artic_function.php");

if(!$lfjuid){
	showerr("���ȵ�¼");
}

/*************************
*�����ύ��Ĳ���
*************************/
if($step==2){
	if(!$aidDB){
		showerr("������ѡ��һƪ����");
	}elseif(!$Type){
		showerr("��ѡ�����Ŀ��,��ɾ��������˵�...");
	}	
	if($Type=='yz'){
		if($T_yz<1){
			$Type=='unyz';
		}
	}elseif($Type=='leavels'){
		if($levels<1){
			$Type='uncom';
		}else{
			$levels=1;
			$Type='com';
		}
	}
	//if($Type=='delete'){
	//	make_more_article_html("$FROMURL","del_0",$aidDB);
	//}
	$fid_str ='';
	foreach( $aidDB AS $key=>$value){		

		if($webdb[NewsMakeHtml]==1&&$Type=='delete'){	//ɾ����Ϣ��,�Ͷ�����������
			$erp=get_id_table($value);
			$rs=$db->get_one("SELECT fid FROM {$pre}article$erp WHERE aid='$value'");
			$fid_str.="&bfid_array[]=$rs[fid]";
		}

		do_work($value,$Type,1);

		if($webdb[NewsMakeHtml]==1&&$Type!='delete'){	//�ƶ���Ŀ��,Ҫ���ƶ����Ϊ��׼
			$erp=get_id_table($value);
			$rs=$db->get_one("SELECT fid FROM {$pre}article$erp WHERE aid='$value'");
			$fid_str.="&bfid_array[]=$rs[fid]";
		}
		
	}
	//if($Type=='delete'){
	//	make_more_article_html("$FROMURL","del_1",$aidDB);
	//}else{
	//	make_more_article_html("$FROMURL",$Type,$aidDB);
	//}	
	if($webdb[NewsMakeHtml]==1){
		refreshto("$FROMURL","�����ɹ�<div style='display:none;'><iframe src='$webdb[www_url]/do/job.php?job=article_html$fid_str' width=0 height=0></iframe></div>",3);
	}

	refreshto("$FROMURL","�����ɹ�",1);
}


$linkdb=array(
			"ȫ������"=>"?",
			"����˵�����"=>"?Type=yz&fid=$fid",
			"δ��˵�����"=>"?Type=unyz&fid=$fid",
			"����վ"=>"?Type=rubbish&fid=$fid",
			"��������"=>"?Type=levels&fid=$fid",
			"������ͼ������"=>"?Type=pic&fid=$fid"
			);

$fid=intval($fid);
unset($fiddb);

//��������Ա
if($web_admin)
{
	require(ROOT_PATH."inc/class.inc.php");
	$Guidedb=new Guide_DB;
	$sort_fid=$Guidedb->Select("{$pre}sort","fid",$fid);
	if($fid){
		$_SQL=" WHERE fid=$fid ";
	}else{
		$_SQL=" WHERE 1 ";
	}
}
//����Ȩ�޼��
else
{
	$sort_fid="<select name='fid'><option value=''>������Ŀ</option>";
	$query = $db->query("SELECT * FROM {$pre}sort WHERE admin!=''");
	while($rs = $db->fetch_array($query)){
		$detail=explode(",",$rs[admin]);
		if(in_array($lfjid,$detail)){
			$fiddb[]=$rs[fid];
			if($fid==$rs[fid]){
				$_selected=" selected ";
			}else{
				$_selected="";
			}
			$sort_fid.="<option value='$rs[fid]' $_selected>$rs[name]</option>";
		}
	}
	$sort_fid.="</select>";
	if(!$fiddb){
		showerr("����Ȩ����");
	}

	if($fid&&in_array($fid,$fiddb)){
		$_SQL=" WHERE fid=$fid ";
	}else{
		$fids=implode(",",$fiddb);
		$_SQL="WHERE fid IN ($fids)";
	}
}

if($page<1){
	$page=1;
}
$rows=20;
$min=($page-1)*$rows;

if($Type=="yz"){
	$_SQL.=" AND yz=1 ";
}elseif($Type=="unyz"){
	$_SQL.=" AND yz=0 ";
}elseif($Type=="rubbish"){
	$_SQL.=" AND yz=2 ";
}elseif($Type=="levels"){
	$_SQL.=" AND levels=1 ";
}elseif($Type=="pic"){
	$_SQL.=" AND ispic=1 ";
}

$SQL="$_SQL ORDER BY list DESC LIMIT $min,$rows";
$which='*';
$listdb=list_article($SQL,$which,40);

$showpage=getpage("{$pre}article","$_SQL","?fid=$fid",$rows);

foreach( $listdb AS $key=>$rs){
	if($rs[yz]==2){
		$rs[state]="<A style='color:red;' onclick=\"return confirm('��ȷ��Ҫ�ӻ���վȡ������?')\" href='?Type=return&aidDB[]=$rs[aid]&step=2'>����վ</A>";
	}elseif($rs[yz]==1){
		$rs[state]="<A style='color:;' onclick=\"return confirm('��ȷ��Ҫȡ����֤��?')\" href='?Type=unyz&aidDB[]=$rs[aid]&step=2'>����</a>";
	}elseif(!$rs[yz]){
		$rs[state]="<A style='color:blue;' href='?Type=yz&aidDB[]=$rs[aid]&step=2'>����</A>";
	}
	if($rs[levels]){
		$rs[levels]="<A style='color:red;' onclick=\"return confirm('��ȷ��Ҫȡ���Ƽ���?')\" href='?Type=uncom&aidDB[]=$rs[aid]&step=2'>���Ƽ�</A>";
	}else{
		$rs[levels]="<A style='color:blue;' href='?Type=com&aidDB[]=$rs[aid]&step=2'>δ�Ƽ�</a>";
	}
	$listdb[$key]=$rs;
}

$choose_fid=str_replace("<select name='fid'","<select onchange=\"window.location=('?fid='+this.options[this.selectedIndex].value+'')\"",$sort_fid);

if($webdb[sortNUM]>500) unset($choose_fid);

require(dirname(__FILE__)."/"."head.php");
require(dirname(__FILE__)."/"."template/list.htm");
require(dirname(__FILE__)."/"."foot.php");
 
?>