<?php
!function_exists('html') && exit('ERR');
if(!$id)
{
	showerr("ID������");
}
elseif(!$fid)
{
	showerr("FID������");
}
elseif(!$rid)
{
	showerr("RID������");
}
elseif(!$i_id)
{
	showerr("i_id������");
}

$midDB=$db->get_one("SELECT *,config AS m_config FROM {$pre}article_module WHERE id='$mid'");
if(!$midDB)
{
	showerr(" MID���� ");
}

$erp=get_id_table($id);
$rsdb=$db->get_one("SELECT A.*,B.* FROM {$pre}article$erp A LEFT JOIN `{$pre}article_content_$mid` B ON A.aid=B.aid WHERE B.aid='$id' AND B.rid='$rid'");
$fidDB=$db->get_one("SELECT * FROM {$pre}sort WHERE fid='$rsdb[fid]'");

if($fidDB[admin]&&$lfjid){
	$detail=explode(",",$fidDB[admin]);
	if( in_array($lfjid,$detail) ){
		$web_admin=1;
	}
}

if($fid!=$rsdb[fid])
{
	showerr("FID����,��һ��");
}

if($fidDB[allowdownload]&&!$web_admin&&$lfjuid!==$rsdb[uid]){
	$detail=explode(",",$fidDB[allowdownload]);
	if( !in_array($groupdb['gid'],$detail) ){
		showerr("�����ڵ��û����ڱ���Ŀ��Ȩ������");
	}
}
if($rsdb[allowdown]&&!$web_admin&&$lfjuid!==$rsdb[uid]){
	$detail=explode(",",$rsdb[allowdown]);
	if( !in_array($groupdb['gid'],$detail) ){
		showerr("�����ڵ��û��鱾��������Ȩ������");
	}
}

$m_config=unserialize($midDB[m_config]);

foreach( $m_config[field_db] AS $key=>$rs )
{
	if($rs[allowview]&&$key==$field)
	{
		$detail=explode(",",$rs[allowview]);
		if(!$web_admin&&$lfjuid!==$rsdb[uid]&&!in_array($groupdb['gid'],$detail))
		{
			if(!$lfjid){
				showerr("<font color=red>���ȵ�¼!</font>");
			}else{
				showerr("<font color=red>���Ȩ�޲���!</font>");
			}
		}
	}
}

$rsdb[$field]=str_replace("\r","",$rsdb[$field]);
$detail=explode("\n",$rsdb[$field]);
unset($rsdb[$field]);
list($url,$true_name,$fen)=explode("@@@",$detail[$ti]);

list($TI,$Aid)=explode("\t",mymd5($_COOKIE["down_$rsdb[aid]"],'DE'));

if( !$web_admin&&$lfjuid!==$rsdb[uid]&&$fen>0 )
{
	$fen=intval($fen);
	if($Aid!=$rsdb[aid]||$TI!=$ti){
		if(get_money($lfjuid)<$fen){
			showerr("���{$webdb[MoneyName]}����{$fen}{$webdb[MoneyDW]}");
		}else{
			add_user($lfjuid,-$fen,'�����ļ��۷�');
			//1Сʱ�ڲ��ظ��ۻ���
			setcookie("down_$rsdb[aid]",mymd5("$ti\t$rsdb[aid]"),$timestamp+3600);
		}
	}
}
elseif( !$web_admin&&$lfjuid!==$rsdb[uid]&&$rsdb[money]>0 )
{
	if($Aid!=$rsdb[aid]||$TI!=$ti){
		if(get_money($lfjuid)<$rsdb[money]){
			showerr("���{$webdb[MoneyName]}����{$rsdb[money]}{$webdb[MoneyDW]}");
		}else{
			add_user($lfjuid,-$rsdb[money],'�����ļ��۷�');
			//1Сʱ�ڲ��ظ��ۻ���
			setcookie("down_$rsdb[aid]",mymd5("$ti\t$rsdb[aid]"),$timestamp+3600);
		}
	}
}

//���µ����
update_hits($mid,$midDB[keywords],$id,$rid,$rsdb[hits_time]);

if(!$true_name){
	$true_name=str_replace(strrchr($url,'.'),'',basename($url));
}
$true_url=tempdir($url);
if(!$webdb[DownLoad_readfile]||eregi('.php',$true_url)){
	header("location:$true_url");
	exit;
}

if(file_exists(ROOT_PATH."$webdb[updir]/$url"))
{
	$filetype=substr(strrchr($url,'.'),1);
	ob_end_clean();
	header('Last-Modified: '.gmdate('D, d M Y H:i:s',time()).' GMT');
	header('Pragma: no-cache');
	header('Content-Encoding: none');
	header('Content-Disposition: attachment; filename='."$true_name.$filetype");
	header('Content-type: '.$filetype);
	header('Content-Length: '.filesize(ROOT_PATH."$webdb[updir]/$url"));
	readfile(ROOT_PATH."$webdb[updir]/$url");
	exit;
}else{
	echo "<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$true_url'>";
	exit;/*
	$filetype=substr(strrchr($url,'.'),1);
	ob_end_clean();
	header('Last-Modified: '.gmdate('D, d M Y H:i:s',time()).' GMT');
	header('Pragma: no-cache');
	header('Content-Encoding: none');
	header('Content-Disposition: attachment; filename='."$true_name.$filetype");
	header('Content-type: '.$filetype);
	readfile($true_url);
	exit;*/
}
//header("location:$true_url");exit;


//�����벥����Ƶ���µ����
function update_hits($mid,$keyword,$aid,$rid,$time){
	global $lfjid,$db,$pre,$timestamp,$lfjuid;
	if($keyword=='download'||$keyword=='mv'){
		if(date("W",$time)!=date("W",$timestamp)){
			$SQL.=",week_hits=1";
		}else{
			$SQL.=",week_hits=week_hits+1";
		}
		if(date("md",$time)!=date("md",$timestamp)){
			$SQL.=",day_hits=1";
		}else{
			$SQL.=",day_hits=day_hits+1";
		}
		if(date("m",$time)!=date("m",$timestamp)){
			$SQL.=",month_hits=1";
		}else{
			$SQL.=",month_hits=month_hits+1";
		}
		
		@extract($db->get_one("SELECT hits_user FROM `{$pre}article_content_{$mid}` WHERE aid='$aid' AND rid='$rid'"));
		if($lfjuid){
			$duserdb=explode(",",$hits_user);
			if(!in_array($lfjuid,$duserdb) ){
				$hits_user=$hits_user.",".$lfjuid;
			}
		}
		$db->query("UPDATE {$pre}article_content_{$mid} SET total_hits=total_hits+1,hits_time='$timestamp',hits_user='$hits_user'$SQL WHERE aid='$aid' AND rid='$rid'");
	}
}
?>