<?php
!function_exists('html') && exit('ERR');


//ȡ���̶�����̶�ģ�͹���
if($webdb[SortUseOtherModule]){
	unset($only);
}

$Guidedb->only=$only;
$Guidedb->mid=$mid;

if($job=="addsort"&&$Apower[fu_sort_power])
{
	if($fup){
		$rsdb=$db->get_one(" SELECT * FROM {$pre}fu_sort WHERE fid='$fup' ");
		$typedb[0]=' checked ';
	}else{
		$typedb[1]=' checked ';
	}
	
	$sort_fup=$Guidedb->Select("{$pre}fu_sort","fup",$fup);
	
	if($only){
		$readonly2=' onbeforeactivate="return false" onfocus="this.blur()" onmouseover="this.setCapture()" onmouseout="this.releaseCapture()" ';
	}
	/*
	$module_id="<select name='postdb[fmid]' $readonly2><option value='0'>����ģ��</option>";
	$query = $db->query("SELECT * FROM {$pre}article_module  WHERE ifclose=0 ORDER BY list DESC");
	while($rs = $db->fetch_array($query)){
		$ckk=$mid==$rs[id]?' selected ':'';
		$module_id.="<option value='$rs[id]' $ckk>$rs[name]</option>";
	}
	$module_id.=" </select>";*/

	

	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/addsort.htm");
	require(dirname(__FILE__)."/"."foot.php");
}
elseif($job=="listsort"&&$Apower[fu_sort_power])
{

	$fid=intval($fid);
	
	$sortdb=array();		
	list_allsort2($fid,'fu_sort',1);



	if($fid){
		$rsdb=$db->get_one(" SELECT * FROM {$pre}fu_sort WHERE fid='$fid' ");
	}
	$sort_fup=$Guidedb->Select("{$pre}fu_sort","fup",$fid);
	$article_show_step[$webdb[article_show_step]]='red;';
	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/sort.htm");
	require(dirname(__FILE__)."/"."foot.php");
}
elseif($job=='showsort'&&$Apower[fu_sort_power])
{
	$webdbs[article_show_step]=$step;
	write_config_cache($webdbs);
	jump("�޸ĳɹ�",$FROMURL,0);
}
elseif($action=="addsort"&&$Apower[fu_sort_power])
{
	if(!$name){
		showerr("���Ʋ���Ϊ��");
	}
	if($fup){
		$rs=$db->get_one("SELECT * FROM {$pre}fu_sort WHERE fid='$fup' ");
		if($rs[type]!=1){
			showerr("ֻ�д������,�ſɴ���");
		}
		$class=$rs['class'];
		$db->query("UPDATE {$pre}fu_sort SET sons=sons+1 WHERE fid='$fup'");
		//$type=0;
	}else{
		//$type=1;	/*�����־*/
		$class=0;
	}
	$class++;
	$detail=explode("\r\n",$name);
	foreach( $detail AS $key=>$name){
		if(!$name){
			continue;
		}
		$name=filtrate($name);
		$db->query("INSERT INTO `{$pre}fu_sort` (name,fup,class,type,allowcomment,fmid) VALUES ('$name','$fup','$class','$postdb[type]',1,'$postdb[fmid]') ");
	}
	@extract($db->get_one("SELECT fid FROM {$pre}fu_sort ORDER BY fid DESC LIMIT 0,1"));
	
	mod_sort_class("{$pre}fu_sort",0,0);		//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	jump("�����ɹ�","index.php?lfj=$lfj&job=editsort&fid=$fid&only=$only&mid=$mid");
}

//�޸���Ŀ��Ϣ
elseif($job=="editsort"&&$Apower[fu_sort_power])
{
	$postdb[fid] && $fid=$postdb[fid];
	$rsdb=$db->get_one("SELECT * FROM {$pre}fu_sort WHERE fid='$fid'");
	$rsdb[config]=unserialize($rsdb[config]);
	//$sort_fid=$Guidedb->Select("{$pre}fu_sort","postdb[fid]",$fid,"index.php?lfj=$lfj&job=$job");
	$Guidedb->getfup=1;
	$sort_fup=$Guidedb->Select("{$pre}fu_sort","postdb[fup]",$rsdb[fup]);

	$style_select=select_style('postdb[style]',$rsdb[style]);
	$group_post=group_box("postdb[allowpost]",explode(",",$rsdb[allowpost]));
	$group_viewtitle=group_box("postdb[allowviewtitle]",explode(",",$rsdb[allowviewtitle]));
	$group_viewcontent=group_box("postdb[allowviewcontent]",explode(",",$rsdb[allowviewcontent]));
	$group_download=group_box("postdb[allowdownload]",explode(",",$rsdb[allowdownload]));
	$typedb[$rsdb[type]]=" checked ";

	$forbidshow[intval($rsdb[forbidshow])]=" checked ";
	$allowcomment[intval($rsdb[allowcomment])]=" checked ";

	$tpl=unserialize($rsdb[template]);
	$tpl_head=select_template("",7,$tpl[head]);
	$tpl_head=str_replace("<select","<select onChange='get_obj(\"tpl_head\").value=this.options[this.selectedIndex].value;'",$tpl_head);

	$tpl_foot=select_template("",8,$tpl[foot]);
	$tpl_foot=str_replace("<select","<select onChange='get_obj(\"tpl_foot\").value=this.options[this.selectedIndex].value;'",$tpl_foot);

	$tpl_type=$rsdb[type]==2?9:2;
	$tpl_list=select_template("",$tpl_type,$tpl['list']);
	$tpl_list=str_replace("<select","<select onChange='get_obj(\"tpl_list\").value=this.options[this.selectedIndex].value;'",$tpl_list);

	$tpl_bencandy=select_template("",3,$tpl[bencandy]);
	$tpl_bencandy=str_replace("<select","<select onChange='get_obj(\"tpl_bencandy\").value=this.options[this.selectedIndex].value;'",$tpl_bencandy);

	$listorder[$rsdb[listorder]]=" selected ";


	$sonListorder[$rsdb[config][sonListorder]]=" selected ";
	$ListShowType[$rsdb[config][ListShowType]]=" selected ";
	$ListShowBigType[$rsdb[config][ListShowBigType]]=" selected ";



	
	$rsdb[descrip]=En_TruePath($rsdb[descrip],0);

	require_once(ROOT_PATH."inc/pinyin.php");
	$htmldirname=change2pinyin($rsdb[name],1);
	
	if($only){
		$readonly2=' onbeforeactivate="return false" onfocus="this.blur()" onmouseover="this.setCapture()" onmouseout="this.releaseCapture()" ';
	}
	$module_id="<select name='postdb[fmid]' $readonly2><option value='0'>����ģ��</option>";
	$query = $db->query("SELECT * FROM {$pre}article_module  WHERE ifclose=0 ORDER BY list DESC");
	while($rs = $db->fetch_array($query)){
		$ckk=$rsdb[fmid]==$rs[id]?' selected ':'';
		$module_id.="<option value='$rs[id]' $ckk>$rs[name]</option>";
	}
	$module_id.=" </select>";

	if($rsdb[type]==1){
		$getLabelTpl=getLabelTpl('template/default/fu_bigsort_tpl');
	}elseif($rsdb[type]==0){
		$getLabelTpl=getLabelTpl('template/default/list_tpl');
	}
	

	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	if($rsdb[type]==2){
		$rsdb[descrip]=editor_replace($rsdb[descrip]);
		
		$tpl['list'] || $tpl['list']="template/default/alonepage.htm";
		require(dirname(__FILE__)."/"."template/fu_sort/editsort2.htm");
	}else{
		$rsdb[descrip]=str_replace("<","&lt;",$rsdb[descrip]);
		$rsdb[descrip]=str_replace(">","&gt;",$rsdb[descrip]);
		require(dirname(__FILE__)."/"."template/fu_sort/editsort.htm");
	}
	require(dirname(__FILE__)."/"."foot.php");
}
elseif($action=="editsort"&&$Apower[fu_sort_power])
{
	if($postdb[type]!=2&&$postdb[tpl]['list']=='template/default/alonepage.htm'){
		$postdb[tpl]['list']='';
	}
	//��鸸��Ŀ�Ƿ�������
	check_fup("{$pre}fu_sort",$postdb[fid],$postdb[fup]);
	$postdb[allowpost]=@implode(",",$postdb[allowpost]);
	$postdb[allowviewtitle]=@implode(",",$postdb[allowviewtitle]);
	$postdb[allowviewcontent]=@implode(",",$postdb[allowviewcontent]);
	$postdb[allowdownload]=@implode(",",$postdb[allowdownload]);
	$postdb[template]=@serialize($postdb[tpl]);
	unset($SQL);

	$rs_fid=$db->get_one("SELECT * FROM {$pre}fu_sort WHERE fid='$postdb[fid]'");
	//���������������ط�Ҳ�޸Ĺ����ֵ.�����ǩ��
	$rs_fid[config]=unserialize($rs_fid[config]);
	$rs_fid[config][sonTitleRow]=$sonTitleRow;
	$rs_fid[config][sonTitleLeng]=$sonTitleLeng;
	$rs_fid[config][cachetime]=$cachetime;
	$rs_fid[config][sonListorder]=$sonListorder;
	$rs_fid[config][listContentNum]=$listContentNum;
	$rs_fid[config][ListShowType]=$ListShowType;
	$rs_fid[config][ListShowBigType]=$ListShowBigType;
	$postdb[config]=addslashes( serialize($rs_fid[config]) );

	if($rs_fid[fup]!=$postdb[fup])
	{
		$rs_fup=$db->get_one("SELECT class FROM {$pre}fu_sort WHERE fup='$postdb[fup]' ");
		$newclass=$rs_fup['class']+1;
		$db->query("UPDATE {$pre}fu_sort SET sons=sons+1 WHERE fup='$postdb[fup]' ");
		$db->query("UPDATE {$pre}fu_sort SET sons=sons-1 WHERE fup='$rs_fid[fup]' ");
		$SQL=",class=$newclass";
	}
	/*ȱ�ٶ԰�����Ч�û����ļ��*/
	$postdb[admin]=str_Replace("��",",",$postdb[admin]);

	if($postdb[admin])
	{
		$detail=explode(",",$postdb[admin]);

		foreach( $detail AS $key=>$value)
		{
			if(!$value)
			{
				unset($detail[$key]);
			}
			else
			{
				$rs=$db->get_one("SELECT groupid,uid FROM {$pre}memberdata WHERE username='$value'");

				if(!$rs)
				{
					showmsg("�����õİ���:$value,�ʺŲ�����,���߻�û�����ʺ�.����֮");
				}
				elseif($rs[groupid]!=3&&$rs[groupid]!=5&&$rs[groupid]!=4)
				{
					//$db->query("UPDATE {$pre}memberdata SET groupid='5' WHERE uid='$rs[uid]' ");
				}
			}
		}

		$detail && $postdb[admin]=','.implode(',',$detail).',';
	}

	
	$postdb[descrip]=En_TruePath($postdb[descrip]);

	$postdb[name]=filtrate($postdb[name]);

	$db->query("UPDATE {$pre}fu_sort SET fup='$postdb[fup]',name='$postdb[name]',type='$postdb[type]',admin='$postdb[admin]',passwd='$postdb[passwd]',logo='$postdb[logo]',descrip='$postdb[descrip]',style='$postdb[style]',template='$postdb[template]',jumpurl='$postdb[jumpurl]',listorder='$postdb[listorder]',maxperpage='$postdb[maxperpage]',allowcomment='$postdb[allowcomment]',allowpost='$postdb[allowpost]',allowviewtitle='$postdb[allowviewtitle]',allowviewcontent='$postdb[allowviewcontent]',allowdownload='$postdb[allowdownload]',forbidshow='$postdb[forbidshow]',config='$postdb[config]',list_html='$postdb[list_html]',bencandy_html='$postdb[bencandy_html]',fmid='$postdb[fmid]',domain='$postdb[domain]',metakeywords='$postdb[metakeywords]',domain_dir='$postdb[domain_dir]'$SQL WHERE fid='$postdb[fid]' ");

	
	mod_sort_class("{$pre}fu_sort",0,0);		//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	//get_htmltype();
	jump("�޸ĳɹ�","$FROMURL");
}
elseif($job=='batch_edit'&&$Apower[fu_sort_power])
{
	if(!$fiddb){
		showmsg("��ѡ��һ����Ŀ");
	}
	$sort_fup=$Guidedb->Select("{$pre}fu_sort","postdb[fup]",$rsdb[fup]);
	$style_select=select_style('postdb[style]',$rsdb[style]);
	$group_post=group_box("postdb[allowpost]",explode(",",$rsdb[allowpost]));
	$group_viewtitle=group_box("postdb[allowviewtitle]",explode(",",$rsdb[allowviewtitle]));
	$group_viewcontent=group_box("postdb[allowviewcontent]",explode(",",$rsdb[allowviewcontent]));
	$group_download=group_box("postdb[allowdownload]",explode(",",$rsdb[allowdownload]));
	$typedb[$rsdb[type]]=" checked ";

	$forbidshow[intval($rsdb[forbidshow])]=" checked ";

	$tpl=unserialize($rsdb[template]);
	//$tpl_head=select_template("postdb[tpl][head]",7,$tpl[head]);
	//$tpl_foot=select_template("postdb[tpl][foot]",8,$tpl[foot]);
	//$tpl_list=select_template("postdb[tpl][list]",2,$tpl['list']);
	//$tpl_bencandy=select_template("postdb[tpl][bencandy]",3,$tpl[bencandy]);
	$tpl_head=select_template("",7,$tpl[head]);
	$tpl_head=str_replace("<select","<select onChange='get_obj(\"tpl_head\").value=this.options[this.selectedIndex].value;'",$tpl_head);

	$tpl_foot=select_template("",8,$tpl[foot]);
	$tpl_foot=str_replace("<select","<select onChange='get_obj(\"tpl_foot\").value=this.options[this.selectedIndex].value;'",$tpl_foot);

	$tpl_list=select_template("",2,$tpl['list']);
	$tpl_list=str_replace("<select","<select onChange='get_obj(\"tpl_list\").value=this.options[this.selectedIndex].value;'",$tpl_list);

	$tpl_bencandy=select_template("",3,$tpl[bencandy]);
	$tpl_bencandy=str_replace("<select","<select onChange='get_obj(\"tpl_bencandy\").value=this.options[this.selectedIndex].value;'",$tpl_bencandy);

	$listorder[$rsdb[listorder]]=" selected ";

	$module_id="<select name='postdb[fmid]'><option value='0'>����ģ��</option>";
	$query = $db->query("SELECT * FROM {$pre}article_module  WHERE ifclose=0 ORDER BY list DESC");
	while($rs = $db->fetch_array($query)){
		$ckk=$rsdb[fmid]==$rs[id]?' selected ':'';
		$module_id.="<option value='$rs[id]' $ckk>$rs[name]</option>";
	}
	$module_id.=" </select>";

	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/batch_edit.htm");
	require(dirname(__FILE__)."/"."foot.php");	
}
elseif($action=='batch_edit'&&$Apower[fu_sort_power])
{
	if(!$ifchang&&!$db_index_showtitle&&!$db_sonTitleRow&&!$db_sonTitleLeng&&!$db_cachetime){
		showmsg("��ѡ��Ҫ�޸��ĸ�����");
	}
	$postdb[allowpost]=@implode(",",$postdb[allowpost]);
	$postdb[allowviewtitle]=@implode(",",$postdb[allowviewtitle]);
	$postdb[allowviewcontent]=@implode(",",$postdb[allowviewcontent]);
	$postdb[allowdownload]=@implode(",",$postdb[allowdownload]);
	$postdb[template]=@serialize($postdb[tpl]);

	/*ȱ�ٶ԰�����Ч�û����ļ��*/
	$postdb[admin]=str_Replace("��",",",$postdb[admin]);
	
	foreach( $fiddb AS $fid=>$name){
		
		unset($SQL);
		$postdb[fid]=$fid;
		//��鸸��Ŀ�Ƿ�������
		$ifchang[fup] && check_fup("{$pre}fu_sort",$postdb[fid],$postdb[fup]);
		$ifchang[fup] && $rs_fid=$db->get_one("SELECT * FROM {$pre}fu_sort WHERE fid='$postdb[fid]'");
		if($ifchang[fup] && $rs_fid[fup]!=$postdb[fup])
		{
			$rs_fup=$db->get_one("SELECT class FROM {$pre}fu_sort WHERE fup='$postdb[fup]' ");
			$newclass=$rs_fup['class']+1;
			$db->query("UPDATE {$pre}fu_sort SET sons=sons+1 WHERE fup='$postdb[fup]' ");
			$db->query("UPDATE {$pre}fu_sort SET sons=sons-1 WHERE fup='$rs_fid[fup]' ");
			$SQL=",class=$newclass";
		}

		if($ifchang[admin]&&$postdb[admin])
		{
			$detail=explode(",",$postdb[admin]);
			foreach( $detail AS $key=>$value)
			{
				if(!$value)
				{
					unset($detail[$key]);
				}
				else
				{
					$rs=$db->get_one("SELECT groupid,uid FROM {$pre}memberdata WHERE username='$value'");

					if(!$rs)
					{
						showmsg("�����õİ���:$value,�ʺŲ�����,���߻�û�����ʺ�.����֮");
					}
					elseif($rs[groupid]!=3&&$rs[groupid]!=5&&$rs[groupid]!=4)
					{
						//$db->query("UPDATE {$pre}memberdata SET groupid='5' WHERE uid='$rs[uid]' ");
					}
				}
			}
			$detail && $postdb[admin]=','.implode(',',$detail).',';
		}

		//������ҳ��ʾ����Ŀ
		if($db_index_showtitle)
		{
			$rsC=$db->get_one("SELECT * FROM {$pre}channel WHERE id=1 ");
			$detail=explode(",","$rsC[fids],$postdb[fid]");
			foreach( $detail AS $key=>$value){
				//�����ظ���FID
				if($ckarray["$value"])
				{
					unset($detail[$key]);
				}
				if(!$index_showtitle&&$value==$postdb[fid])
				{
					unset($detail[$key]);
				}
				$ckarray["$value"]=1;
			}
			$fids=implode(',',$detail);
			$db->query("UPDATE {$pre}channel SET fids='$fids' WHERE id='1' ");
		}

		if($db_sonTitleRow||$db_sonTitleLeng||$db_cachetime){
			$rs_fid=$db->get_one("SELECT config FROM {$pre}fu_sort WHERE fid='$postdb[fid]'");

			//���������������ط�Ҳ�޸Ĺ����ֵ.�����ǩ��
			$rs_fid[config]=unserialize($rs_fid[config]);
			$db_sonTitleRow && $rs_fid[config][sonTitleRow]=$sonTitleRow;
			$db_sonTitleLeng && $rs_fid[config][sonTitleLeng]=$sonTitleLeng;
			$db_cachetime && $rs_fid[config][cachetime]=$cachetime;
			$postdb[config]=addslashes( serialize($rs_fid[config]) );
			$ifchang[config]=1;
		}
		
		foreach( $ifchang AS $key=>$value){
			$SQL.=",$key='{$postdb[$key]}'";
		}
		$SQL && $db->query("UPDATE {$pre}fu_sort SET fid='$postdb[fid]'$SQL WHERE fid='$postdb[fid]' ");
	
		//�޸Ĺ���Ա��,����Ƿ�ȥ������Ա.Ҫ�������û��鴦���
		if($ifchang[admin])
		{
			$rs_fid=$db->get_one("SELECT admin FROM {$pre}fu_sort WHERE fid='$postdb[fid]'");
			$old_admin=$rs_fid[admin];
			$detail=explode(",",$old_admin);
			$detail_new=explode(",",$postdb[admin]);
		
			//���ύǰ��һһ���
			foreach( $detail AS $key=>$value)
			{
				if( $value&&!@in_array($value,$detail_new) )
				{
					$rs=$db->get_one("SELECT groupid,uid FROM {$pre}memberdata WHERE username='$value'");
					if( $rs[groupid]!=3&&$rs[groupid]!=4 )
					{
						$rss=$db->get_one("SELECT admin FROM {$pre}fu_sort WHERE BINARY admin LIKE '%,$value,%' ");
						if(!$rss){
							//$db->query("UPDATE {$pre}memberdata SET groupid='8' WHERE uid='$rs[uid]' ");
						}
					}
				}
			}
		}
	}
	mod_sort_class("{$pre}fu_sort",0,0);		//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	jump("�޸ĳɹ�","index.php?lfj=$lfj&job=listsort");
}
//������ĿƵ��
elseif($job=='creat_channel'&&$Apower[fu_sort_power])
{
	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/creat_channel.htm");
	require(dirname(__FILE__)."/"."foot.php");
}
elseif($action=='creat_channel'&&$Apower[fu_sort_power])
{
	if(!eregi("^([a-z0-9_/]+)$",$channelDir)){
		showmsg("Ƶ��Ŀ¼�ַ�ֻ����:a-z0-9_/");
	}
	if(is_dir(ROOT_PATH.$channelDir)){
		showmsg("$channelDir,��Ŀ¼�Ѿ�������.�뻻һ����");
	}
	makepath(ROOT_PATH.$channelDir);
	$paths='';
	$detail=explode("/",$channelDir);
	foreach( $detail AS $key=>$value){
		if($value){
			$paths.='../';
		}
	}
	write_file(ROOT_PATH."$channelDir/index.php","<?php
\$fid='$fid';
if(is_file('index.htm')){header('location:index.htm');exit;}
require_once(\"list.php\");
");

	write_file(ROOT_PATH."$channelDir/list.php","<?php
require_once(\"global.php\");
require_once(THIS_PATH.\"list.php\");
	");

	write_file(ROOT_PATH."$channelDir/bencandy.php","<?php
require_once(\"global.php\");
require_once(THIS_PATH.\"bencandy.php\");
	");

	write_file(ROOT_PATH."$channelDir/global.php","<?php
defined(\"THIS_PATH\") || define(\"THIS_PATH\",\"$paths\");
require_once(THIS_PATH.\"global.php\");	
	");

	$rs_fid=$db->get_one("SELECT config FROM {$pre}fu_sort WHERE fid='$fid'");

	$rs_fid[config]=unserialize($rs_fid[config]);
	$rs_fid[config][channelDir]=$channelDir;
	$rs_fid[config][channelDomain]=$channelDomain;
	$config=addslashes( serialize($rs_fid[config]) );
	$db->query("UPDATE {$pre}fu_sort SET config='$config' WHERE fid='$fid'");
	jump("[�����ɹ�] [<A HREF='$webdb[www_url]/$channelDir' target=_blank>�����Ƶ��</A>]","index.php?lfj=$lfj&job=listsort",20);
}
elseif($job=="label"&&$Apower[fu_sort_power])
{
	$erp=$Fid_db[iftable][$fid];
	$rsdb=$db->get_one("SELECT * FROM {$pre}article$erp WHERE fid='$fid' LIMIT 1");
	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/label.htm");
	require(dirname(__FILE__)."/"."foot.php");
}
//�޸���ĿƵ��
elseif($job=='edit_channel'&&$Apower[fu_sort_power])
{
	$rs_fid=$db->get_one("SELECT config FROM {$pre}fu_sort WHERE fid='$fid'");
	@extract(unserialize($rs_fid[config]));
	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/edit_channel.htm");
	require(dirname(__FILE__)."/"."foot.php");
}
elseif($action=='edit_channel'&&$Apower[fu_sort_power])
{
	$rs_fid=$db->get_one("SELECT config FROM {$pre}fu_sort WHERE fid='$fid'");
	$rs_fid[config]=unserialize($rs_fid[config]);

	if( $channelDir && !eregi("^([a-z0-9_/]+)$",$channelDir) ){
		showmsg("Ƶ��Ŀ¼�ַ�ֻ����:a-z0-9_/");
	}

	//���û���������
	if( $channelDir && ($channelDir!=$rs_fid[config][channelDir]) ){
		if(is_dir(ROOT_PATH.$channelDir)){
			showmsg("$channelDir,��Ŀ¼�Ѿ�������.�뻻һ����");
		}
		makepath(ROOT_PATH.$channelDir);

		$paths='';
		$detail=explode("/",$channelDir);
		foreach( $detail AS $key=>$value){
			if($value){
				$paths.='../';
			}
		}
		write_file(ROOT_PATH."$channelDir/index.php","<?php
\$fid='$fid';
if(is_file('index.htm')){header('location:index.htm');exit;}
require_once(\"list.php\");
		");

		write_file(ROOT_PATH."$channelDir/list.php","<?php
require_once(\"global.php\");
require_once(THIS_PATH.\"list.php\");
		");

		write_file(ROOT_PATH."$channelDir/bencandy.php","<?php
require_once(\"global.php\");
require_once(THIS_PATH.\"bencandy.php\");
		");

		write_file(ROOT_PATH."$channelDir/global.php","<?php
defined(\"THIS_PATH\") || define(\"THIS_PATH\",\"$paths\");
require_once(THIS_PATH.\"global.php\");	
		");
		
	}

	$rs_fid[config][channelDir]=$channelDir;
	$rs_fid[config][channelDomain]=$channelDomain;
	$config=addslashes( serialize($rs_fid[config]) );
	$db->query("UPDATE {$pre}fu_sort SET config='$config' WHERE fid='$fid'");
	jump("[�޸ĳɹ�] [<A HREF='$webdb[www_url]/$channelDir' target=_blank>�����Ƶ��</A>]","$FROMURL",20);
}
elseif($action=="delete"&&$Apower[fu_sort_power])
{
	$db->query("DELETE FROM {$pre}fu_sort WHERE fid='$fid'");
	$db->query("DELETE FROM {$pre}fu_article WHERE fid='$fid'");
	
	mod_sort_class("{$pre}fu_sort",0,0);		//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	jump("ɾ���ɹ�","index.php?lfj=$lfj&job=listsort&only=$only&mid=$mid");
}
elseif($action=="editlist"&&$Apower[fu_sort_power])
{
	foreach( $order AS $key=>$value){
		$db->query("UPDATE {$pre}fu_sort SET list='$value' WHERE fid='$key' ");
	}
	mod_sort_class("{$pre}fu_sort",0,0);		//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	jump("�޸ĳɹ�","$FROMURL",1);
}
/**
*�޸���վ��Ŀ
**/
elseif($job=='save'&&$Apower[fu_sort_power])
{
	$errsort=sort_error("{$pre}fu_sort",'fid');
 	$sort_fup=$Guidedb->Select("{$pre}fu_sort","fup",$rsdb[fup]);
	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/save.htm");
	require(dirname(__FILE__)."/"."foot.php");
}

/**
*�����޸�������Ŀ
**/
elseif($action=='save'&&$Apower[fu_sort_power]){
	if(!$fid){
		showmsg("��ѡ��һ����Ŀ");
	}
	$db->query("UPDATE {$pre}fu_sort SET fup='$fup' WHERE fid='$fid' ");
	mod_sort_class("{$pre}fu_sort",0,0);			//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	jump("����Ŀ�����ɹ�","$FROMURL",1);
}

/**
*��ƴ��վ��Ŀ
**/
elseif($job=='toget'&&$Apower[fu_sort_power])
{
	$selectname_1=$Guidedb->Select("{$pre}fu_sort",'ofid');
	$selectname_2=$Guidedb->Select("{$pre}fu_sort",'nfid');
	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/menu.htm");
	require(dirname(__FILE__)."/"."template/fu_sort/toget.htm");
	require(dirname(__FILE__)."/"."foot.php");
}

/**
*��ƴ��վ��Ŀ
**/
elseif($action=='toget'&&$Apower[fu_sort_power]){
	if(!$ofid){
		showmsg("��ѡ��һ��Դ��Ŀ");
	}elseif(!$nfid){
		showmsg("��ѡ��һ��Ŀ����Ŀ");
	}
	if($ofid==$nfid){
		showmsg("�����ˣ���Ŀ�����ܺϲ�Ϊ�Լ�,��ѡ��ϲ���������Ŀȥ��");
	}
	$erp=$Fid_db[iftable][$ofid];
	$db->query("UPDATE {$pre}article$erp SET fid='$nfid',fname='{$Fid_db[name][$nfid]}' WHERE fid='$ofid'");
	$db->query("UPDATE {$pre}reply$erp SET fid='$nfid' WHERE fid='$ofid'");
	$db->query("UPDATE {$pre}comment SET fid='$nfid' WHERE fid='$ofid'");
	$db->query("DELETE FROM {$pre}fu_sort WHERE fid='$ofid'");
	mod_sort_class("{$pre}fu_sort",0,0);		//����class
	mod_sort_sons("{$pre}fu_sort",0);			//����sons
	/*���µ�������*/
	cache_guide();
	jump("�������","$FROMURL",1);
}/*
elseif($job=="config"&&$Apower[sort_config])
{
	$webdb[viewNoPassArticle]==='0' || $webdb[viewNoPassArticle]=1;
	$viewNoPassArticle[$webdb[viewNoPassArticle]]=" checked ";

	$webdb[ifContribute]==='0' || $webdb[ifContribute]=1;
	$ifContribute[$webdb[ifContribute]]=" checked ";

	$webdb[autoGetSmallPic]=(int)$webdb[autoGetSmallPic];
	$autoGetSmallPic[$webdb[autoGetSmallPic]]=" checked ";

	$allowGuestSearch[$webdb[allowGuestSearch]]=" checked ";

	$adminPostEditType[$webdb[adminPostEditType]]=" checked ";
	$ListShowIcon[intval($webdb[ListShowIcon])]=" checked ";
	$webdb[newArticleTime] || $webdb[newArticleTime]=24;
	$webdb[hotArticleNum] || $webdb[hotArticleNum]=100;

	require(dirname(__FILE__)."/"."head.php");
	require(dirname(__FILE__)."/"."template/fu_sort/config.htm");
	require(dirname(__FILE__)."/"."foot.php");
}
elseif($action=="config"&&$Apower[sort_config])
{
	setcookie("editType",'',$timestamp-9999999);
	write_config_cache($webdbs);
	jump("�޸ĳɹ�",$FROMURL);
}*/



/**
*���µ�������
**/
function cache_guide(){
	global $Guidedb,$pre;
	//$Guidedb->FidSonCache("{$pre}fu_sort");
	$Guidedb->GuideFidCache("{$pre}fu_sort",'fu_guide_fid.php');
	All_fid_cache2();
}


//��ȡ��ǩģ��
function getLabelTpl($path='template/default/list_tpl'){
	global $webdb,$rsdb;
	$pictitledb[]=$f1="Ĭ��ģ��";
	if($rsdb[fmid]&&is_file(ROOT_PATH."$path/mod_{$rsdb[fmid]}.htm")){
		$picurldb[]=$f2="$webdb[www_url]/$path/mod_{$rsdb[fmid]}.jpg";
	}else{
		$picurldb[]=$f2="$webdb[www_url]/$path/0.jpg";
	}	
	$select="<option value='$f2'>$f1</option>";
	$dir=opendir(ROOT_PATH.$path);
	while($file=readdir($dir)){
		if(eregi("\.htm$",$file)&&!eregi("^mod_([0-9]+)\.htm$",$file)&&$file!='0.htm'){
			$pictitledb[]=str_replace(".htm","",$file);
			$picurldb[]=$f2="$webdb[www_url]/$path/".str_replace(".htm",".jpg",$file);
			$select.="<option value='$f2'>".str_replace(".htm","",$file)."</option>";
		}
	}

	$picurldb=implode('","',$picurldb);
	$pictitledb=implode('","',$pictitledb);
	$myurl=str_replace(array(".","/"),array("\.","\/"),$webdb[www_url]);
$show=<<<EOT
<table  border="0" cellspacing="0" cellpadding="0">
<tr><td style="padding-left:20px;padding-bottom:10px;"><select id="selectTyls" onChange="selectTpl(this)">
    $select<option value='-2' style='color:red;'>�½�һ��</option>
  </select> [<a href="#LOOK" onclick="show_MorePic(-1)">��һ��</a>] 
      ��<span id="upfile_PicNum">1/2</span>��[<a href="#LOOK" onclick="show_MorePic(1)">��һ��</a>]  
       


	
</td></tr>
  <tr>
    <td height="30" style="padding-left:20px;"><div id="showpicdiv" class="showpicdiv" style="width:10px;height:3px;"><A style="border:2px solid #fff;display:block;" HREF="javascript::" id="showPicID" target="_blank"><img border="0" onerror="this.src=replace_img(this.src);" onload="this.height='200'" id="upfile_PicUrl"></A></div></td>

    

  </tr>
</table>

	
<SCRIPT LANGUAGE="JavaScript">
var ImgLinks= new Array("$picurldb");
var ImgTitle= new Array("$pictitledb");
function replace_img(url){
	//���ͼƬ������,��ȥ�ٷ���ȡͼƬ,������ǲ�����,��ʹ��Ĭ�ϵ���ͼƬ.
	reg=/http:\/\/down\.qibosoft\.com/g
	if(reg.test(url)){
		return "$webdb[www_url]/images/default/nopic.jpg";
	}
	re   = /$myurl/g;
	links = url.replace(re, "http://down.qibosoft.com");
	return links;
}
</SCRIPT>
EOT;
	return $show;
}


function list_sort_guide($fup){
	global $db,$pre,$mid,$only,$job,$lfj;
	$rs=$db->get_one("SELECT fup,name FROM {$pre}fu_sort WHERE fid='$fup'");
	if($rs){
		$show=" -> <A HREF='index.php?lfj=$lfj&job=$job&only=$only&mid=$mid&fid=$fup'>$rs[name]</A> ";
		$show=list_sort_guide($rs[fup]).$show;
	}
	return $show;
}


/*��Ŀ�б�*/
function list_allsort2($fid,$table='sort',$getnum=''){
	global $db,$pre,$sortdb,$Fid_db;
	$query=$db->query("SELECT * FROM {$pre}$table where fup='$fid' ORDER BY list DESC");
	while( $rs=$db->fetch_array($query) ){
		$icon="";
		for($i=1;$i<$rs['class'];$i++){
			$icon.="&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		if($icon){
			$icon=substr($icon,0,-24);
			$icon.="--";
		}
		$rs[config]=unserialize($rs[config]);
		$rs[icon]=$icon;
		$NUM=0;
		if($getnum&&!$rs[type]){
			@extract($db->get_one("SELECT COUNT(*) AS NUM FROM {$pre}fu_article WHERE fid='$rs[fid]'"));
			$rs[NUM]=intval($NUM);
		}
		$sortdb[]=$rs;

		list_allsort2($rs[fid],$table,$getnum);
	}
}

function All_fid_cache2(){
	global $db,$pre,$webdb;
	//������Ŀ
	//$detail=explode(",",$webdb['hideFid']);
	$show="<?php\r\nunset(\$Fid_db);\r\n";
	$query = $db->query("SELECT S.fid,S.fup,S.name,M.iftable,M.id AS Mid FROM {$pre}fu_sort S LEFT JOIN {$pre}article_module M ON S.fmid=M.id ORDER BY S.list DESC");
	while($rs = $db->fetch_array($query)){
		if(in_array($rs[fid],$detail)){
			//continue;
		}
		//$_s=$rs[iftable]?"\$Fid_db[iftable][{$rs[fid]}]='$rs[iftable]';":'';
		$rs[name]=addslashes($rs[name]);
		$show.="\$Fu_Fid_db[{$rs[fup]}][{$rs[fid]}]='$rs[name]';
		\$Fu_Fid_db[name][{$rs[fid]}]='$rs[name]';
		$_s";
	}
	write_file("../data/fu_all_fid.php",$show.'?>');
}

?>