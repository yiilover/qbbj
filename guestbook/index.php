<?php
require(dirname(__FILE__)."/"."global.php");
require_once(ROOT_PATH."inc/encode.php");
include(ROOT_PATH."inc/class.uploadfiles.php");

if($step=="post"){
    if(!$webdb[ifOpenGuestBook]){
        showerr("�ܱ�Ǹ,����Ա�ر������Թ���");
    }
//	if( $webdb[yzImgGuestBook]&&!$web_admin ){
//		if(!check_imgnum($yzimg)){
//			showerr("��֤�벻����");
//		}else{
//			set_cookie("yzImgNum","");
//		}
//	}
//	if(!$postdb[content]){
//		showerr("���ݲ���Ϊ��");
//	}elseif(strlen($postdb[content])>50000){
//		showerr("���ݲ��ܴ���5���ַ�!");
//	}
//	if($postdb[oicq]&&!ereg("^[0-9]{5,11}$",$postdb[oicq])){
//		showerr("OICQ��ʽ�����Ϲ���");
//	}
//	if($postdb[mobphone]&&!ereg("^1[0-9]{10}$",$postdb[mobphone])){
//		showerr("�ֻ����벻���Ϲ���");
//	}
    if(!$postdb[email]){
        showerr("���䲻��Ϊ��");
    }
    if ($postdb[email]&&!ereg("^[-a-zA-Z0-9_\.]+\@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$",$postdb[email])) {
        showerr("���䲻���Ϲ���");
    }
//    if($postdb[weburl]&&!eregi(":\/\/",$postdb[weburl])){
//        $postdb[weburl]="http://$postdb[weburl]";
//    }
//    if($postdb[blogurl]&&!eregi(":\/\/",$postdb[blogurl])){
//        $postdb[blogurl]="http://$postdb[blogurl]";
//    }
    foreach($postdb AS $key=>$value){
        $postdb[$key]=filtrate($postdb[$key]);
    }
    $yz=0;
    if($web_admin){
        $yz=1;
    }elseif($webdb[groupPassPassGuestBook]){
        $webdb[groupPassPassGuestBook]=explode(",",$webdb[groupPassPassGuestBook]);
        if(in_array($groupdb[gid],$webdb[groupPassPassGuestBook])){
            $yz=1;
        }
    }

    //���˲���������
//    $postdb[content]=replace_bad_word($postdb[content]);
//    $postdb[username]=replace_bad_word($postdb[username]);

    //�������˶����������ʺ���������
//	if($postdb[username]){
//		$rs=$userDB->get_passport($postdb[username],'name');
//		if($rs && $rs[uid]!=$lfjuid){
//			showerr("���û���Ϊע���û����ʺ�,�뻻һ��");
//		}
//	}






	/*�ļ��ϴ��������*/
    $file = $_FILES['fileField'];
    $attachurl = '';
	if($file['size']>0){
		$fileArr['file'] = $file['tmp_name'];
		$fileArr['name'] = $file['name'];
		$fileArr['size'] = $file['size'];
		$fileArr['type'] = $file['type'];
		$file_name = 'f'.mt_rand(100,999).strtotime(date('Y-m-d H:i:s',time())).strtolower(strrchr($file['name'],"."));
        $filetypes = explode(' ',$webdb[upfileType]);
		$savepath = "../upload_files/guestbook/";
		$maxsize = 0;
		$overwrite = 0;
		$upload = new upload($fileArr, $file_name, $savepath, $filetypes, $overwrite, $maxsize);
		if (!$upload->run()) showerr($upload->errmsg());
		$attachurl = "guestbook/" . $file_name;
	}
    $db->query("INSERT INTO `{$_pre}content` ( `ico` , `email` , `oicq` , `weburl` , `blogurl` , `uid` , `username` , `ip` , `content` , `yz` , `posttime` , `list`, `fid`, `mobphone`, `companyname`, `truename`, `phone`, `deadline`, `attach1`, `attach2`, `attach3`, `attachurl`, `ofid`, `aid` , `goods_num`, `goods_spe`, `goods_remark`)
	VALUES (
	'$face','$postdb[email]','$postdb[oicq]','$postdb[weburl]','$postdb[blogurl]','$lfjuid','$postdb[username]','$onlineip','$postdb[content]','$yz','$timestamp','$timestamp','$fid','$postdb[mobphone]','$postdb[companyname]','$postdb[truename]','$postdb[phone]','$postdb[deadline]','$postdb[attach1]','$postdb[attach2]','$postdb[attach3]','$attachurl','$postdb[ofid]','$postdb[aid]','$postdb[goods_num]','$postdb[goods_spe]','$postdb[goods_remark]')
	");


    /*�����ʼ�����*/
    require_once('phpmailer/class.phpmailer.php');
    $content .= "��˾����:".$postdb[companyname]."<br>��ϵ��:".$postdb[truename]."<br>�����ʼ�:".$postdb[email]."<br>��ϵ�绰:".$postdb[phone]."<br>������:".$postdb[deadline]."<br>commodity code:".$postdb[goods_sn]."<br>name of commodity:".$postdb[title]."<br>number:".$postdb[goods_num]."<br>specification:".$postdb[specification]."<br>remarks:".$postdb[goods_remark]."<br>ѯ������Ҫ˵����Ҫ��1:".$postdb[attach1]."<br>ѯ������Ҫ˵����Ҫ��2:".$postdb[attach2]."<br>ѯ������Ҫ˵����Ҫ��3:".$postdb[attach3];
    $content = iconv('GBK','UTF-8',$content);
    send_email($content);

    $rurl = "?fid=$fid";
	if($postdb[ofid]) $rurl .= "&ofid=$ofid";
	if($postdb[aid]) $rurl .= "&aid=$aid";
	$rmsg = "�ύ�ɹ������ǻᾡ�������ϵ";
    refreshto($rurl,$rmsg,1);
}elseif($action=="delete"&&$lfjuid){
    if($web_admin){
        $db->query("DELETE FROM `{$_pre}content` WHERE id='$id'");
    }else{
        $db->query("DELETE FROM `{$_pre}content` WHERE id='$id' AND uid='$lfjuid' ");
    }
    refreshto("?fid=$fid","ɾ���ɹ�",1);
}elseif($job=="show"&&$lfjuid){
    $rsdb = $db->get_one("SELECT * FROM `{$_pre}content` WHERE id=$id");
    $goods = array();
    if($ofid){
        $goods = get_query_goods($ofid,$aid);
    }
//    print_r($rsdb);
//    die;
    require(ROOT_PATH."inc/head.php");
    require(getTpl("indexshow"));
    require(ROOT_PATH."inc/foot.php");
    exit();
}

$rows=$webdb[GuestBookNum]>0?$webdb[GuestBookNum]:10;
if($page<1){
    $page=1;
}
$min=($page-1)*$rows;

if(!$webdb[viewNoPassGuestBook]){
    $SQL=" WHERE G.yz=1 ";
}else{
    $SQL=" WHERE 1 ";
}
if($fid){
    $SQL .= " AND fid='$fid' ";
}

$query = $db->query("SELECT SQL_CALC_FOUND_ROWS G.*,D.icon FROM `{$_pre}content` G LEFT JOIN `{$pre}memberdata` D ON G.uid=D.uid $SQL ORDER BY G.id DESC LIMIT $min,$rows");
$RS=$db->get_one("SELECT FOUND_ROWS()");
$totalNum=$RS['FOUND_ROWS()'];
$showpage=getpage("","","?fid=$fid",$rows,$totalNum);

while($rs = $db->fetch_array($query)){
    $rs[content]=format_text($rs[content]);
    $rs[content]=replace_bad_word($rs[content]);	//���˲���������
    if($rs[reply]){
        $replydb=unserialize($rs[reply]);
        $replydb[content]=str_replace("\r\n","<br>",$replydb[content]);
        $replydb[content]=replace_bad_word($replydb[content]);	//���˲���������
        $replydb[posttime]=date("Y-m-d H:i:s",$replydb[posttime]);
        $rs[content] .= "<FIELDSET><LEGEND>���Իظ�</LEGEND>$replydb[content] (����:$replydb[username]/����:$replydb[posttime])</FIELDSET>";
    }
    $rs[posttime]=date("Y-m-d H:i:s",$rs[posttime]);
    $detail=explode(".",$rs[ip]);
    $rs[ip]="$detail[0].$detail[1].$detail[2].*";
    if($web_admin){
        $rs['delete']="[<A HREF='replyguestbook.php?fid=$fid&id=$rs[id]'>�ظ�</A>] [<A HREF='?action=delete&id=$rs[id]'>ɾ��</A>]";
    }elseif($lfjuid==$rs[uid]){
        $rs['delete']="[<A HREF='?action=delete&id=$rs[id]'>ɾ��</A>]";
    }
    if($rs[weburl]){
        $rs['_weburl']="<A HREF='$rs[weburl]' target='_blank' title='�鿴��ҳ'>".'<img src="'.$webdb[www_url].'/images/default/home.gif" width="16" height="16" border="0">'."</A>";
    }
    if($rs[blogurl]){
        $rs['_blogurl']="<A HREF='$rs[blogurl]' target=_blank title='�鿴BLOG'>".'<img src="'.$webdb[www_url].'/images/default/song_word.gif" width="16" height="16" border="0">'."</A>";
    }
    $rs[icon]&&$rs[icon]=tempdir($rs[icon]);
    if($rs[oicq]){
        $rs[oicq]="<a target=blank href=tencent://message/?uin=$rs[oicq]&Site=$VlogCfg[webname]&Menu=yes><img border='0' SRC=http://wpa.qq.com/pa?p=1:$rs[oicq]:9 alt='��������'></a>";
    }else{
        $rs[oicq]='';
    }
    $rs[onclick]="";
    if(!$rs[username]){
        $rs[username]='*�����ο�*';
        $rs[onclick]="onclick='return false;'";
    }
    if($rs[mobphone] && $web_admin){
        $rs[_mobphone]=" �ֻ�����:$rs[mobphone] ";
    }
    $listdb[]=$rs;
}
$ofid = $_GET[ofid]?$_GET[ofid]:'';
$aid = $_GET[aid]?$_GET[aid]:'';
$goods = array();
if($ofid){
    $goods = get_query_goods($ofid,$aid);
}
$chdb[main_tpl]=getTpl("index");
$ch_fid	= $ch_pagetype = 0;
$ch_module = $webdb[module_id]?$webdb[module_id]:7;
@include(ROOT_PATH."inc/label_module.php");

require(ROOT_PATH."inc/head.php");
require(getTpl("index"));
require(ROOT_PATH."inc/foot.php");

function get_query_goods($ofid='',$aid=''){
    global $db, $pre;
    if($ofid){
        if($aid){
            $query = $db->query("SELECT * FROM `{$pre}article` WHERE aid=$aid");
        }else{
            $query = $db->query("SELECT * FROM `{$pre}article` WHERE fid=$ofid");
        }
        while($rs = $db->fetch_array($query)){
            $goods[] = $rs;
        }
    }
    return $goods?$goods:false;
}

function send_email($content) {
    global $webdb;
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Host = $webdb[MailServer];        //��������ַ
    $mail->SMTPAuth = true;
    $mail->Username = $webdb[MailId];  //����������
    $mail->Password = $webdb[MailPw];  //��������������
    $mail->From = $webdb[MailId];  //����������
    $mail->FromName = "system";       //����������
    $mail->CharSet = "utf-8";
    $mail->Encoding = "base64";
    $mail->AddAddress($webdb[webmail], 'Dear');  // �ռ������������
    $mail->AddReplyTo($webdb[MailId], '163.com');       //����������,��������
    $mail->IsHTML(true);  // send as HTML
    $mail->Subject = $webdb[webname];        //�ʼ�����
    $mail->Body = $content;       //�ʼ�����
    $mail->AltBody = "text/html";
    return $mail->Send();
}
?>