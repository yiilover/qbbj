<?php
require_once(dirname(__FILE__)."/global.php");
header('Content-Type: text/html; charset='.WEB_LANG);
$cid=intval($cid);
/**
*��������
**/
if($action=="post"){

	//��֤�봦��
	if(!$web_admin)
	{
		if(!check_imgnum($yzimg))
		{
			die("��֤�벻����,����ʧ��");
		}
	}

	if(!$content)
	{
		die("���ݲ���Ϊ��");
	}
	

	//Ȩ���ж��Ƿ�����������
	//��ֹȫ��������
	if($webdb[forbidComment])
	{
		$allow=0;
	}
	//��Ա��������,���οͲ�������
	elseif(!$webdb[allowGuestComment]&&!$lfjid)
	{
		$allow=0;
	}
	//ȫ���˿�������
	else
	{
		$allow=1;
	}


	//�����Զ�ͨ����˵��ж�
	//ȫ���˵������Զ�ͨ�����
	if($webdb[allowGuestCommentPass])
	{
		$yz=1;
	}
	//ֻ�л�Ա�Ĳ��Զ�ͨ�����
	elseif($webdb[allowMemberCommentPass]&&$lfjid)
	{
		$yz=1;
	}
	//�������Զ�ͨ�����
	else
	{
		$yz=0;
	}


	$username=filtrate($username);
	$content=filtrate($content);
	$content=str_replace("@@br@@","<br>",$content);

	//���˲���������
	$username=replace_bad_word($username);
	$content=replace_bad_word($content);

	//�������˶����������ʺ���������
	if($username)
	{
		$rs=$userDB->get_info($username,'name');
		if($rs && $rs[uid]!=$lfjuid)
		{
			$username="����";
		}
	}
	
	$rss=$db->get_one(" SELECT * FROM {$pre}special WHERE id='$cid' ");
	if(!$rss)
	{
		die("ԭ���ݲ�����");
	}

	$username || $username=$lfjid;

	/*���ϵͳ��������,��ô�е����۽������ύ�ɹ�,��û����ʾ����ʧ��*/
	if($allow)
	{
		if(is_utf8($content)||is_utf8($username)){
			$content=utf82gbk($content);
			$username=utf82gbk($username);
		}
		if(WEB_LANG=='utf-8'){
			$content=gbk2utf8($content);
			$username=gbk2utf8($username);
		}elseif(WEB_LANG=='big5'){
			require_once(ROOT_PATH."inc/class.chinese.php");
			$cnvert = new Chinese("GB2312","BIG5",$content,ROOT_PATH."./inc/gbkcode/");
			$content = $cnvert->ConvertIT();

			$cnvert = new Chinese("GB2312","BIG5",$username,ROOT_PATH."./inc/gbkcode/");
			$username = $cnvert->ConvertIT();
		}
		$db->query("INSERT INTO `{$pre}special_comment` (`cid` , `uid` , `username` , `posttime` , `content` , `ip` , `icon` , `yz` ) VALUES ('$cid', '$lfjuid', '$username', '$timestamp', '$content', '$onlineip', '$icon', '$yz')");
	}
}

/**
*ɾ������
**/
elseif($action=="del")
{
	$rs=$db->get_one("SELECT * FROM `{$pre}special_comment` WHERE id='$id'");
	if($web_admin||$lfjuid==$rs[uid])
	{
		$db->query("DELETE FROM `{$pre}special_comment` WHERE id='$id'");
	}
}

//�ж��Ƿ���ʾȫ������
if(!$webdb[showNoPassComment])
{
	$SQL=" AND A.yz=1 ";
}
else
{
	$SQL="";
}

$rows=$webdb[showCommentRows]?$webdb[showCommentRows]:8;

if($page<1)
{
	$page=1;
}
$min=($page-1)*$rows;

/*���������ٶ�Ҳֻ������ʾ1000����*/
$leng=10000;

$query=$db->query("SELECT A.*,B.icon FROM `{$pre}special_comment` A LEFT JOIN {$pre}memberdata B ON A.uid=B.uid WHERE A.cid='$cid' $SQL ORDER BY A.cid DESC LIMIT $min,$rows");
while( $rs=$db->fetch_array($query) )
{
	if(!$rs[username]){
		$detail=explode(".",$rs[ip]);
		$rs[username]="$detail[0].$detail[1].$detail[2].*";
	}
	if($rs[icon]){
		$rs[icon]=tempdir($rs[icon]);
	}
	$rs[posttime]=date("Y-m-d H:i:s",$rs[posttime]);
	$rs[content]=get_word($rs[full_content]=$rs[content],$leng);
	$rs[content]=str_replace("\n","<br>",$rs[content]);
	$rs[content]=kill_badword($rs[content]);
	$rs[username]=kill_badword($rs[username]);
	$listdb[]=$rs;
}

$showpage=getpage("`{$pre}special_comment` A"," WHERE A.cid='$cid' $SQL","?cid=$cid",$rows);
$showpage=preg_replace("/\?cid=([\d]+)&page=([\d]+)/is","javascript:getcomment('$webdb[www_url]/do/special_comment_ajax.php?cid=\\1&page=\\2')",$showpage);

require_once(html('special_comment_ajax'));

?>