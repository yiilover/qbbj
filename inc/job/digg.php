<?php
!function_exists('html') && exit('ERR');
//�����������
if($webdb[cookieDomain]){
	echo "<SCRIPT LANGUAGE=\"JavaScript\">document.domain = \"$webdb[cookieDomain]\";</SCRIPT>";
}

$erp=get_id_table($id);
if($type=='vote')
{
	if( get_cookie("DiggId_$id") )
	{
		$time=30-floor(($timestamp-get_cookie("DiggId_$id"))/60);
		showerr("��Сʱ�ڣ��벻Ҫ�ظ���ͬһƪ����,��{$time}���Ӻ�ſ����ٶ���������",1);
	}
	else
	{
		$db->query("UPDATE {$pre}article$erp SET digg_num=digg_num+1,digg_time='$timestamp' WHERE aid='$id'");
		set_cookie("DiggId_$id",$timestamp,1800);
	}
	
}

@extract($db->get_one("SELECT digg_num FROM {$pre}article$erp WHERE aid='$id'"));
echo "<meta http-equiv='Content-Type' content='text/html; charset=gb2312'><SCRIPT LANGUAGE=\"JavaScript\">
<!--
parent.document.getElementById('DiggNum_$id').innerHTML='$digg_num';
//-->
</SCRIPT>";
if($type=='vote')
{
	echo "<SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	parent.document.getElementById('DiggDo_$id').innerHTML='ThankS';
	//-->
	</SCRIPT>";
}

if(get_cookie("DiggId_$id"))
{
	echo "<meta http-equiv='Content-Type' content='text/html; charset=gb2312'><SCRIPT LANGUAGE=\"JavaScript\">
	<!--
	parent.document.getElementById('DiggDo_$id').innerHTML='����';
	//-->
	</SCRIPT>";
}
?>