<?php
require(dirname(__FILE__)."/"."global.php");
require_once(ROOT_PATH."inc/artic_function.php");

if(!$lfjid){
	showerr("�㻹û��¼");
}

//ɾ������
if($do=='del'){
	if($step==2){
		refreshto("?job=myarticle&only=1&mid=0","ɾ���ɹ�<div style='display:none;'><iframe src='$webdb[www_url]/do/job.php?job=article_html&fid=$fid' width=0 height=0></iframe></div>",2);
	}
	$erp=get_id_table($id);
	$rs=$db->get_one("SELECT * FROM {$pre}article$erp WHERE aid='$id' AND uid='$lfjuid' ");
	if(!$rs){
		showerr("���²�����");
	}
	delete_article($id,$rid);

	//��̬ҳ����
	$aid=$id;
	$fid=$rs[fid];
	$postdb=array('posttime'=>$rs['posttime']);
	$fidDB=$db->get_one("SELECT * FROM `{$pre}sort` WHERE fid='$rs[fid]' ");
	make_article_html("?do=$do&step=2&fid=$fid",'del');

	refreshto("$FROMURL","ɾ���ɹ�",0);
}

if($page<1){
	$page=1;
}
$rows=20;
$min=($page-1)*$rows;

$_sql="";
if($fid>0){
	$_sql=" AND fid='$fid' ";
	$erp=$Fid_db[iftable][$fid];
}elseif($mid>0){
	$_sql=" AND mid='$mid' ";
	$erp=$article_moduleDB[$mid][iftable]?$article_moduleDB[$mid][iftable]:'';
}elseif($mid==-1){
	$_sql=" AND mid='0' ";
}
if($only){
	$_sql.=" AND mid='$mid' ";
}



$SQL="WHERE uid=$lfjuid AND yz!=2 $_sql ORDER BY aid DESC LIMIT $min,$rows";
$which='*';
$showpage=getpage("{$pre}article$erp","WHERE uid=$lfjuid AND yz!=2 $_sql","?job=$job&fid=$fid&mid=$mid&only=$only",$rows);
$listdb=list_article($SQL,$which,50,$erp);
$listdb || $listdb=array();
foreach( $listdb AS $key=>$rs){
	if($rs[pages]<1){
		$rs[pages]=1;
		$erp=get_id_table($rs[aid]);
		$db->query("UPDATE {$pre}article$erp SET pages=1 WHERE aid='$rs[aid]'");
	}
	$rs[state]="";
	if($rs[yz]==2){
		$rs[state]="����";
	}elseif($rs[yz]==1){
		$rs[state]="<A style='color:red;'>����</A>";
	}elseif(!$rs[yz]){
		$rs[state]="<A style='color:blue;'>����</A>";
	}
	if($rs[levels]){
		$rs[levels]="<A style='color:red;'>���Ƽ�</A>";
	}else{
		$rs[levels]="δ�Ƽ�";
	}
	
	$listdb[$key]=$rs;
}
$_MSG='�ҵĸ��';
require(dirname(__FILE__)."/"."head.php");
require(dirname(__FILE__)."/"."template/myarticle.htm");
require(dirname(__FILE__)."/"."foot.php");
?>