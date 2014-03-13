<?php
function fiddb_article($fiddb,$rows=8,$leng=50,$order='list'){
	global $db,$pre,$Fid_db,$webdb;
	if(!$webdb[viewNoPassArticle]){
		$SQL_yz=' AND yz=1 ';
	}
	$detail=explode(",",$fiddb);
	foreach($detail AS $key=>$fid){
		if(!$fid){
			continue;
		}
		$SQL="WHERE fid='$fid' $SQL_yz ORDER BY $order DESC LIMIT $rows";
		$which='*';
		$rs=list_article($SQL,$which,$leng);
		//�������Ŀ���ܻ�ȡ������,����ȡ����������Ŀ������
		if(!$rs)
		{
			$array_fid=Get_SonFid("{$pre}sort",$fid);
			if($array_fid)
			{
				$SQL="WHERE fid IN (".implode(',',$array_fid).") $SQL_yz ORDER BY $order DESC LIMIT $rows";
				$rs=list_article($SQL,$which,$leng);
			}
		}

		//Ϊ��ȡ��Ŀ����
		if($Fid_db[name][$fid]){
			$listdb[$fid][name]=$Fid_db[name][$fid];
		}else{
			$rss=$db->get_one("SELECT name FROM {$pre}sort WHERE fid='$fid' ");
			$listdb[$fid][name]=$rss[name];
		}
		
		$listdb[$fid][fid]=$fid;
		$listdb[$fid][article]=$rs;
	}

	//��������
	foreach( $detail AS $key=>$fid){

		//��Ҫ�Ǵ���ĳЩ��ɾ������Ŀ
		if(!$listdb[$fid][name])
		{
			unset($listdb[$fid]);
			continue;
		}

		$list_db[$fid][fid]=$fid;
		$list_db[$fid][name]=$listdb[$fid][name];
		$list_db[$fid][article]=$listdb[$fid][article];
	}
	unset($listdb);
	return $list_db;
}


function list_article($SQL,$which='*',$leng=40,$erp=''){
	global $db,$pre;
	$query=$db->query("SELECT $which FROM {$pre}article$erp $SQL");
	while( $rs=$db->fetch_array($query) ){
		if($rs[mid]){
			$_rss=$db->get_one("SELECT * FROM {$pre}article_content_{$rs[mid]} WHERE aid='$rs[aid]' LIMIT 1");
			$_rss && $rs=$rs+$_rss;
		}
		$rs[content]=@preg_replace('/<([^<]*)>/is',"",$rs[content]);	//��HTML������˵�
		$rs[content]=@preg_replace('/ |&nbsp;/is',"",$rs[content]);	//�ѿո���˵�
		//��������ж̱���,���Դ���ʾ�������б�
		if($rs[smalltitle]){
			$title=$rs[smalltitle];
		}else{
			$title=$rs[title];
		}
		$rs[title]=get_word($rs[full_title]=$title,$leng);
		if($rs[titlecolor]||$rs[fonttype]){
			$titlecolor=$rs[titlecolor]?"color:$rs[titlecolor];":'';
			$font_weight=$rs[fonttype]==1?'font-weight:bold;':'';
			$rs[title]="<font style='$titlecolor$font_weight'>$rs[title]</font>";
		}
		$rs[posttime]=date("Y-m-d",$rs[full_posttime]=$rs[posttime]);
		if($rs[picurl]){
			$rs[picurl]=filtrate($rs[picurl]);
			$rs[picurl]=tempdir($rs[picurl]);
		}
		$listdb[]=$rs;
	}
	return $listdb;
}


function list_special($SQL,$which='*',$leng=40,$cleng=180){
	global $db,$pre;
	$query=$db->query("SELECT $which FROM {$pre}special $SQL");
	while( $rs=$db->fetch_array($query) ){
		$rs[content]=@preg_replace('/<([^<]*)>/is',"",$rs[content]);	//��HTML������˵�
		$rs[about]=get_word($rs[content],$cleng);
		//���ר���ж̱���,���Դ���ʾ��ר���б�
		$title=$rs[title];
		$rs[title]=get_word($rs[full_title]=$title,$leng);
		$rs[posttime]=date("Y-m-d",$rs[posttime]);
		if($rs[picurl]){
			$rs[picurl]=tempdir($rs[picurl]);
		}
		$listdb[]=$rs;
	}
	return $listdb;
}


function do_work($id,$job,$check=0){
	global $db,$pre,$timestamp,$lfjid,$lfjdb,$webdb,$web_admin,$reason,$Fid_db;
	if(!$Fid_db){
		include(ROOT_PATH."data/all_fid.php");
	}
	if(!$lfjid){
		showerr("���ȵ�¼");
	}

	$erp=get_id_table($id);

	$rsdb=$db->get_one("SELECT A.*,B.admin FROM {$pre}article$erp A LEFT JOIN {$pre}sort B ON A.fid=B.fid WHERE A.aid='$id' ");
	if($check==1){
		if(!$web_admin&&!in_array($lfjid,explode(",",$rsdb[admin]))){
			showerr("��ûȨ�޲�������:$rsdb[title]");
		}
	}
	if($job=="delete")
	{
		global $forcedel;
		delete_article($rsdb[aid],'',$forcedel);

		make_article_html('','del',$rsdb);	//��̬����
		$array[title]="�㷢��ġ�{$rsdb[title]}����ɾ����";
	}
	elseif($job=="move"&&$rsdb[yz]!=2)
	{
		global $fid;
		if($fid){
			make_article_html('','del',$rsdb);	//��̬����,Ҫ����ǰ��,��ȻFID������,��û��ɾ��

			$rs=$db->get_one("SELECT name FROM {$pre}sort WHERE fid='$fid'");
			$db->query("UPDATE {$pre}article$erp SET fid='$fid',fname='$rs[name]',lastfid='$rsdb[fid]' WHERE aid='$id' ");
			$db->query("UPDATE {$pre}reply$erp SET fid='$fid' WHERE aid='$id' ");
			$rsdb[mid]&&$db->query("UPDATE {$pre}article_content_$rsdb[mid] SET fid='$fid' WHERE aid='$id' ");			
			$array[title]="�㷢��ġ�{$rsdb[title]}����ת����Ŀ��";
		}
	}
	elseif($job=="color")
	{
		global $Color;
		$db->query("UPDATE {$pre}article$erp SET titlecolor='$Color' WHERE aid='$id' ");
		$array[title]="�㷢��ġ�{$rsdb[title]}�������ñ�����ɫ��";
	}
	elseif($job=="yz"&&$rsdb[yz]!=2&&$rsdb[yz]!=1)
	{
		$db->query("UPDATE {$pre}article$erp SET yz='1',yzer='$lfjdb[username]',yztime='$timestamp' WHERE aid='$id' ");
		//�Ƹ�����
		Give_article_money($rsdb[uid],'yz',$rsdb);
		$array[title]="�㷢��ġ�{$rsdb[title]}��ͨ�������";
	}
	elseif($job=="unyz"&&$rsdb[yz]!=2&&$rsdb[yz]!=0)
	{
		$db->query("UPDATE {$pre}article$erp SET yz='0',yzer='$lfjdb[username]',yztime='$timestamp' WHERE aid='$id' ");
		//�Ƹ�����
		Give_article_money($rsdb[uid],'unyz',$rsdb);

		make_article_html('','del',$rsdb);	//��̬����
		$array[title]="�㷢��ġ�{$rsdb[title]}����ȡ�������";
	}
	elseif($job=="com"&&!$rsdb[levels])
	{
		global $levels;
		if($levels<1){
			$levels=1;
		}
		$db->query("UPDATE {$pre}article$erp SET levels='$levels',levelstime='$timestamp' WHERE aid='$id' ");
		//�Ƹ�����
		Give_article_money($rsdb[uid],'com',$rsdb);
		$array[title]="�㷢��ġ�{$rsdb[title]}�����Ƽ���";
	}
	elseif($job=="uncom"&&$rsdb[levels])
	{
		$db->query("UPDATE {$pre}article$erp SET levels='0',levelstime='0' WHERE aid='$id' ");
		//�Ƹ�����
		Give_article_money($rsdb[uid],'uncom',$rsdb);
		$array[title]="�㷢��ġ�{$rsdb[title]}����ȡ���Ƽ���";
	}
	elseif($job=="top")
	{
		global $toptime;
		$times=$timestamp+$toptime;
		$db->query("UPDATE {$pre}article$erp SET list='$times' WHERE aid='$id'");
		$array[title]="�㷢��ġ�{$rsdb[title]}�����ö���";
	}
	elseif($job=="untop")
	{
		$db->query("UPDATE {$pre}article$erp SET list=posttime WHERE aid='$id' ");
		$array[title]="�㷢��ġ�{$rsdb[title]}����ȡ���ö���";
	}
	elseif($job=="front")
	{
		global $topid;
		if($topid)
		{
			$rs=$db->get_one("SELECT list FROM {$pre}article$erp WHERE aid='$topid' ");
			$list=$rs["list"]+5;
			$db->query("UPDATE {$pre}article$erp SET list='$list' WHERE aid='$id' ");
		}
		else
		{
			$db->query("UPDATE {$pre}article$erp SET list='$timestamp' WHERE aid='$id' ");
		}
		$array[title]="�㷢��ġ�{$rsdb[title]}������ǰ��ʾ��";
	}
	elseif($job=="bottom")
	{
		global $bottomid;
		if($bottomid)
		{
			$rs=$db->get_one("SELECT list FROM {$pre}article$erp WHERE aid='$bottomid' ");
			$list=$rs["list"]-5;
			$db->query("UPDATE {$pre}article$erp SET list='$list' WHERE aid='$id' ");
		}
		else
		{
			$db->query("UPDATE {$pre}article$erp SET list='0' WHERE aid='$id' ");
		}
		$array[title]="�㷢��ġ�{$rsdb[title]}����������";
	}
	elseif($job=='return')
	{
		if($rsdb[yz]==2){
			$db->query("UPDATE {$pre}article$erp SET yz=1 WHERE aid='$id' ");
			$array[title]="�㷢��ġ�{$rsdb[title]}���ӻ���վ��ԭ��";
		}
	}
	elseif($job=='special')
	{
		global $spid;
		if(!$spid){
			showerr("��ѡ��һ��ר��");
		}
		$rssp=$db->get_one(" SELECT * FROM {$pre}special WHERE id='$spid' ");
		$detail=explode(",",$rssp[aids]);
		if( !in_array($id,$detail) ){
			if($rssp[aids]){
				$rssp[aids]="$id,$rssp[aids]";
			}else{
				$rssp[aids]="$id";
			}
			$db->query("UPDATE `{$pre}special` SET `aids`='$rssp[aids]' WHERE id='$spid'");
		}
		$array[title]="�㷢��ġ�{$rsdb[title]}��������ר����";
	}
	elseif($job=='fusort'){
		global $fid;
		if(!$fid){
			showerr("��ѡ��һ������Ŀ");
		}
		if(!$db->get_one("SELECT * FROM {$pre}fu_article WHERE fid='$fid' AND aid='$id'")){
			$db->query("INSERT INTO {$pre}fu_article SET fid='$fid',aid='$id'");
		}
		$array[title]="�㷢��ġ�{$rsdb[title]}�������ø���Ŀ��";
	}
	//����Ϣ֪ͨ
	if($reason){
		$array[fromer]=$lfjdb[username];
		$array[fromuid]=$lfjdb[uid];
		$array[touid]=$rsdb[uid];
		$array[content]=$reason;
		pm_msgbox($array);
	}

	//ɾ�������ļ�
	delete_cache_file($rsdb[fid],$rsdb[aid]);
}

//�Զ����ֶδ���
//$basedb=id,uid,fid,aid
function query_article_module($mid,$type,$post_db,$basedb){
	global $db,$pre;
	extract($basedb);
	if(!$fidDB=$db->get_one("SELECT * FROM {$pre}article_module WHERE id='$mid'"))
	{
		return ;
	}
	$m_config=unserialize($fidDB[config]);

	foreach( $m_config[field_db] AS $key=>$rs )
	{
		if( $rs[mustfill]==1  )
		{
			if(is_array($post_db[$rs[field_name]])){
				$ckk='';
				foreach($post_db[$rs[field_name]][url] AS $Url){
					if($Url){
						$ckk++;
					}
				}
				if(!$ckk&&!$post_db[$rs[field_name]][0]){
					showerr("{$rs[title]}����Ϊ��");
				}
			}elseif( !$post_db[$rs[field_name]] ){
				showerr("{$rs[title]}����Ϊ��");
			}			
		}

		if( ($rs[mustfill]==2||$rs[form_type]=='pingfen') && $post_db[$rs[field_name]] )
		{
			showerr("{$rs[title]}����˽���ύ����");
		}

		if($rs[field_type]=='int'&&$post_db[$rs[field_name]]&&!ereg("^[0-9]+$",$post_db[$rs[field_name]]))
		{
			showerr("{$rs[title]}ֻ��Ϊ����");
		}

		if($rs[field_type]=='varchar')
		{
			$rs[field_leng]=$rs[field_leng]?$rs[field_leng]:255;
			if(strlen( $post_db[$rs[field_name]] )>$rs[field_leng])
			{
				showerr("{$rs[title]}���ܳ���{$rs[field_leng]}���ַ�,һ�����ֵ��������ַ�");
			}
		}

		if($rs[field_type]=='int')
		{
			$rs[field_leng]=$rs[field_leng]?$rs[field_leng]:10;
			if(strlen( $post_db[$rs[field_name]] )>$rs[field_leng])
			{
				showerr("{$rs[title]}���ܳ���{$rs[field_leng]}���ַ�");
			}
		}
		
		if($rs[form_type]=='upmoremv')
		{
			unset($_array);
			foreach( $post_db[$rs[field_name]][url] AS $key=>$value)
			{
				if(!$value){
					continue;
				}
				$_array[]="$value@@@{$post_db[$rs[field_name]][name][$key]}@@@{$post_db[$rs[field_name]][fen][$key]}@@@{$post_db[$rs[field_name]][type][$key]}";
			}
			$post_db[$rs[field_name]]=implode("\n",$_array);
		}
		if($rs[form_type]=='upmorefile'||$rs[form_type]=='upmorepic')
		{
			unset($_array);
			foreach( $post_db[$rs[field_name]][url] AS $key=>$value)
			{
				if(!$value){
					continue;
				}
				$_array[]="$value@@@{$post_db[$rs[field_name]][name][$key]}@@@{$post_db[$rs[field_name]][fen][$key]}";
			}
			$post_db[$rs[field_name]]=implode("\n",$_array);
		}
		if($rs[form_type]=='upplay')
		{
			unset($_array);
			foreach( $post_db[$rs[field_name]][url] AS $key=>$value)
			{
				if(!$value){
					continue;
				}
				$_array[]="$value@@@{$post_db[$rs[field_name]][type][$key]}";
			}
			$post_db[$rs[field_name]]=implode("\n",$_array);
		}
	}
	
	if($type=='')
	{
		return ;
	}

	foreach( $m_config[is_html] AS $key=>$value)
	{
		$post_db[$key]=str_replace("<img ","<img onload=\'if(this.width>600)makesmallpic(this,600,800);\' ",$post_db[$key]);
		//ͼƬĿ¼ת��
		$post_db[$key]=move_attachment($uid,$post_db[$key],"article/$fid");
		//��ȡԶ��ͼƬ
		//$post_db[$key]=get_outpic($post_db[$key],$GetOutPic);
		$post_db[$key] = En_TruePath($post_db[$key]);
		$post_db[$key] = preg_replace('/javascript/i','java script',$post_db[$key]);//����js����
		$post_db[$key] = preg_replace('/<iframe ([^<>]+)>/i','&lt;iframe \\1>',$post_db[$key]);//���˿�ܴ���
	}
	
	$_array=array_flip($m_config[is_html]);
	
	foreach( $post_db AS $key=>$value)
	{
		if(is_array($value))
		{
			$post_db[$key]=implode("/",$value);
		}
		elseif(!@in_array($key,$_array))
		{
			$post_db[$key]=filtrate($value);
		}
	}

	unset($sqldb);
	if($type=='add')
	{
		$sqldb['aid']="aid='$aid'";
		$sqldb['rid']="rid='$rid'";
		$sqldb['fid']="fid='$fid'";
		$sqldb['uid']="uid='$uid'";

		$array = table_field("{$pre}article_content_$fidDB[id]");
		foreach( $array AS $key=>$value)
		{
			if(in_array($value,array('aid','rid','fid','uid','id')))
			{
				continue;
			}
			isset($post_db[$value]) && $sqldb["$value"]="`{$value}`='{$post_db[$value]}'";
		}
	
		$sql=implode(",",$sqldb);
		$sql && $db->query("INSERT INTO `{$pre}article_content_$mid` SET $sql");
	}
	elseif($type=='edit')
	{
		$array = table_field("{$pre}article_content_$mid");
		foreach( $array AS $key=>$value)
		{
			//if(in_array($value,array('aid','rid','fid','uid','id')))
			//{
			//	continue;
			//}
			if(!$m_config[field_db][$value]){
				continue;	//���û��Զ����ֶ�,��һЩ�����֮����ֶ�,�Ͳ��ܸ���
			}		
			//isset($post_db[$value]) && 
			$sqldb[]="`$value`='{$post_db[$value]}'";
		}
		$sql=implode(",",$sqldb);
		$sql && $db->query("UPDATE `{$pre}article_content_$fidDB[id]` SET fid='$basedb[fid]',$sql WHERE id='$i_id' ");
	}
}

function set_module_table_value($mid,$if_edit=1){
	global $rsdb,$db,$pre,$m_config;

	if(!$fidDB=$db->get_one("SELECT * FROM {$pre}article_module WHERE id='$mid'"))
	{
		return ;
	}
	$m_config=unserialize($fidDB[config]);

	if(!$if_edit)
	{
		foreach( $m_config[field_db] AS $key=>$rs)
		{
			if($rs[form_value])
			{
				$rsdb[$key]=$rs[form_value];
			}
		}
	}
	foreach( $m_config[is_html] AS $key=>$value)
	{
		$rsdb[$key]=editor_replace($rsdb[$key]);
		$rsdb[$key]=En_TruePath($rsdb[$key],0);
	}

	foreach( $m_config[field_db] AS $key=>$rs){
		if($rs[form_type]=='select'){
			$detail=explode("\r\n",$rs[form_set]);
			foreach( $detail AS $_key=>$value){
				list($v1,$v2)=explode("|",$value);
				if($rsdb[$key]==$v1){
					unset($rsdb[$key]);
					$rsdb[$key]["$v1"]=' selected ';
				}
			}
		}elseif($rs[form_type]=='radio'){
			$detail=explode("\r\n",$rs[form_set]);
			foreach( $detail AS $_key=>$value){
				list($v1,$v2)=explode("|",$value);
				if($rsdb[$key]==$v1){
					unset($rsdb[$key]);
					$rsdb[$key]["$v1"]=' checked ';
				}
			}
		}elseif($rs[form_type]=='checkbox'){
			$_d=explode("/",$rsdb[$key]);
			unset($rsdb[$key]);
			$detail=explode("\r\n",$rs[form_set]);
			foreach( $detail AS $_key=>$value){
				list($v1,$v2)=explode("|",$value);
				if( @in_array($v1,$_d) ){
					$rsdb[$key]["$v1"]=' checked ';
				}
			}
		}elseif($rs[form_type]=='upmorefile'||$rs[form_type]=='upmorepic'){
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($url,$name,$fen)=explode("@@@",$value);
				$rsdb[$key][name][]=$name;
				$rsdb[$key][url][]=$url;
				$rsdb[$key][fen][]=$fen;
			}
		}elseif($rs[form_type]=='upmoremv'){
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($url,$name,$fen,$type)=explode("@@@",$value);
				$rsdb[$key][name][]=$name;
				$rsdb[$key][url][]=$url;
				$rsdb[$key][fen][]=$fen;
				$rsdb[$key][type][]=$type;
			}
		}
		elseif($rs[form_type]=='upplay'){
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($url,$type)=explode("@@@",$value);
				$rsdb[$key][url][]=$url;
				$rsdb[$key][type][]=$type;
			}
		}
	}
}

function show_module_content($m_config){
	global $rsdb,$web_admin,$lfjuid,$groupdb,$webdb,$Mrsdb;
	$Mrsdb='';
	foreach( $m_config[field_db] AS $key=>$rs )
	{
		if($rs[form_type]=='textarea')
		{
			$rsdb[$key]=format_text($rsdb[$key]);
		}
		elseif($rs[form_type]=='ieedit')
		{
			$rsdb[$key]=En_TruePath($rsdb[$key],0);
		}
		elseif($rs[form_type]=='upfile')
		{
			$rsdb[$key]=tempdir($rsdb[$key]);
		}
		elseif($rs[form_type]=='upplay')
		{
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($_url,$_type)=explode("@@@",$value);
				$Mrsdb[$key][url][]=$_url=tempdir($_url);
				$rsdb[$key][show][]=player($_url,$width=400,$height=336,$autostart='false',$_type);
			}
			$rsdb[$key]=implode("<br>",$rsdb[$key][show]);
		}
		elseif($rs[form_type]=='upmoremv')
		{
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($_url,$_name,$_fen,$_type)=explode("@@@",$value);
				$_fen=intval($_fen);
				$_fen || $_fen=$rsdb[money];
				$Mrsdb[$key][name][]=$_name=$_name?$_name:"DownLoad$_key";
				$Mrsdb[$key][url][]=$_url=tempdir($_url);
				$Mrsdb[$key][type][]=$_type;
				$Mrsdb[$key][fen][]=$_fen;
				$_fen || $_fen=$rsdb[money];
				$_fen=$_fen?$_fen="(�շ�:{$_fen} {$webdb[MoneyName]})":"";
				if($webdb[allowDownMv]&&($web_admin||!$_fen)){
					$thunderUrl = eregi("^thunder:\/\/",$_url)?$_url:Thunder_Encode($_url);
					$flashgetUrl = eregi("^thunder:\/\/",$_url)?$_url:Flashget_Encode($_url,$webdb[FlashGet_ID]);
					$ohterdownurl="&nbsp;&nbsp;<img src='$webdb[www_url]/images/default/down_ico.gif'> <A HREF='$webdb[www_url]/do/job.php?job=down_encode&fid=$rsdb[fid]&id=$rsdb[aid]&rid=$rsdb[rid]&i_id=$rsdb[id]&mid=$rsdb[mid]&field=$key&ti=$_key' target=_blank>����$_name</A> <span class='xunlei_flashget' style='display:;'>&nbsp;&nbsp; <img src='$webdb[www_url]/images/default/down_ico.gif'> <a href=\"#\" thunderHref=\"$thunderUrl\" thunderPid=\"$webdb[XunLei_ID]\" thunderType=\"\" thunderResTitle=\"$filename\" onClick=\"return OnDownloadClick_Simple(this,2)\" oncontextmenu=\"ThunderNetwork_SetHref(this)\"  style='color:red;'>Ѹ�׸�������</a>
					&nbsp;&nbsp; <img src='$webdb[www_url]/images/default/down_ico.gif'> <a href=\"#\" onClick=\"ConvertURL2FG('$flashgetUrl','',$webdb[FlashGet_ID])\" oncontextmenu=\"Flashget_SetHref(this)\" fg=\"$flashgetUrl\" style='color:red;'>�쳵�������� $filename</a></span>";
				}
				$rsdb[$key][show][]="<img src='$webdb[www_url]/images/default/play.gif'> <A onclick=\"window.open('$webdb[www_url]/do/job.php?job=player&fid=$rsdb[fid]&id=$rsdb[aid]&rid=$rsdb[rid]&i_id=$rsdb[id]&mid=$rsdb[mid]&field=$key&ti=$_key','','scrollbars=no,toolbar=no,status=no,resizable=0,left=200,top=100,height=400,width=500,titleBar=no')\" href='javascript:'>�����տ�$_name</A> $_fen $ohterdownurl";
			}
			$rsdb[$key]=implode("<br>",$rsdb[$key][show]);
		}
		elseif($rs[form_type]=='upmorefile')
		{
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($_url,$_name,$_fen)=explode("@@@",$value);
				$_fen=intval($_fen);
				$Mrsdb[$key][name][]=$_name=$_name?$_name:"�����������";
				$Mrsdb[$key][url][]=$_url=tempdir($_url);
				$Mrsdb[$key][fen][]=$_fen;
				$_fen || $_fen=$rsdb[money];
				$_fen=$_fen?$_fen="(�շ�:{$_fen} {$webdb[MoneyName]})":"";
				$ohterdownurl='';
				if($web_admin||!$_fen){
					$thunderUrl = eregi("^thunder:\/\/",$_url)?$_url:Thunder_Encode($_url);
					$flashgetUrl = eregi("^thunder:\/\/",$_url)?$_url:Flashget_Encode($_url,$webdb[FlashGet_ID]);
					$ohterdownurl="&nbsp;&nbsp; <img src='$webdb[www_url]/images/default/down_ico.gif'> <a href=\"#\" thunderHref=\"$thunderUrl\" thunderPid=\"$webdb[XunLei_ID]\" thunderType=\"\" thunderResTitle=\"$filename\" onClick=\"return OnDownloadClick_Simple(this,2)\" oncontextmenu=\"ThunderNetwork_SetHref(this)\"  style='color:red;'>Ѹ�׸�������</a>
					&nbsp;&nbsp; <img src='$webdb[www_url]/images/default/down_ico.gif'> <a href=\"#\" onClick=\"ConvertURL2FG('$flashgetUrl','',$webdb[FlashGet_ID])\" oncontextmenu=\"Flashget_SetHref(this)\" fg=\"$flashgetUrl\" style='color:red;'>�쳵�������� $filename</a>";
				}
				$rsdb[$key][show][]="<img src='$webdb[www_url]/images/default/down_ico.gif'> <A HREF='$webdb[www_url]/do/job.php?job=down_encode&fid=$rsdb[fid]&id=$rsdb[aid]&rid=$rsdb[rid]&i_id=$rsdb[id]&mid=$rsdb[mid]&field=$key&ti=$_key' target=_blank>$_name</A> $_fen <span id='xunlei_flashget' style='display:;'>$ohterdownurl</span>";
			}
			$rsdb[$key]=implode("<br>",$rsdb[$key][show]);
		}
		elseif($rs[form_type]=='upmorepic')
		{
			$detail=explode("\n",$rsdb[$key]);
			unset($rsdb[$key]);
			foreach( $detail AS $_key=>$value){
				list($_url,$_name)=explode("@@@",$value);
				$Mrsdb[$key][name][]=$_name=addslashes($_name);
				$Mrsdb[$key][url][]=$_url=addslashes(tempdir($_url));
				$rsdb[$key][picurl][]="\"$_url\"";
				$rsdb[$key][picalt][]="\"$_name\"";
			}
			$ImgLinks=implode(",",$rsdb[$key][picurl]);
			$ImgTitle=implode(",",$rsdb[$key][picalt]);
			$rsdb[$key]="
			<table width=\"100%\" border=\"0\" cellspacing=\"5\" cellpadding=\"5\">
						  <tr>
							<td align=\"center\"><a name='LOOK'></a><A HREF=\"#LOOK\" onclick=\"showMorePic(1)\"><img border=\"0\" id=\"upfilePicUrl\"></A></td>
						  </tr>
						  <tr>
							<td align=\"center\"><div id=\"pictitle\"></div> <div>(<a href=\"#LOOK\" onclick=\"showMorePic('head')\">����</a>) (<a href=\"#LOOK\" onclick=\"showMorePic(-1)\">��һ��</a>) ��<span id=\"upfilePicNum\">1/2</span>��(<a href=\"#LOOK\" onclick=\"showMorePic(1)\">��һ��</a>) (<a href=\"#LOOK\" onclick=\"showMorePic('end')\">β��</a>)</div></td>
						  </tr>
						</table>
			<SCRIPT LANGUAGE=\"JavaScript\">
			<!--
			var upfilePicNumId=0;
			function showMorePic(todo){

			var ImgLinks= new Array($ImgLinks);
			var ImgTitle= new Array($ImgTitle);

	if(todo==1){
		upfilePicNumId++;
	}else if(todo==-1){
		upfilePicNumId--;
	}else if(todo=='head'){
		upfilePicNumId=0;
	}else if(todo=='end'){
		upfilePicNumId=ImgLinks.length-1;
	}
	if(upfilePicNumId<0){
		alert(\"�Ѿ��ǵ�һ����!\");
		upfilePicNumId=0;
	}
	if( upfilePicNumId>(ImgLinks.length-1) ){
		alert(\"�Ѿ������һ����!\");
		upfilePicNumId=ImgLinks.length-1;
	}

	document.getElementById(\"upfilePicNum\").innerHTML=\"<font color=red>\"+(upfilePicNumId+1)+\"</font>/\"+ImgLinks.length;
	document.getElementById(\"upfilePicUrl\").src=ImgLinks[upfilePicNumId];

	var srcImage = new Image();
	srcImage.src=ImgLinks[upfilePicNumId];
	
	srcImage.onload=function (){
		document.getElementById(\"upfilePicUrl\").width=srcImage.width
		if(srcImage.width>500){document.getElementById(\"upfilePicUrl\").width=500;}
	}

	document.getElementById(\"upfilePicUrl\").alt=ImgTitle[upfilePicNumId];
	document.getElementById(\"pictitle\").innerHTML=ImgTitle[upfilePicNumId]+\" (<A HREF='\"+ImgLinks[upfilePicNumId]+\"' target='blank'>ԭʼ�ߴ�</A>)\"
			}
			showMorePic()
			//-->
			</SCRIPT>
			";
		}
		if($rs[allowview])
		{
			$detail=explode(",",$rs[allowview]);
			if(!$web_admin&&$lfjuid!=$rsdb[uid]&&!in_array($groupdb['gid'],$detail))
			{
				$rsdb[$key]="<font color=red>Ȩ�޲���,�޷��鿴!</font>";
			}
		}
	}
}



//�ɼ��ⲿͼƬ
function get_module_outpic($str,$getpic=1){
	global $webdb;
	if(!$getpic){
		return $str;
	}
	preg_match_all("/http:\/\/([^ '\"<>]+)\.(gif|jpg|png)/is",$str,$array);
	$filedb=$array[0];
	foreach( $filedb AS $key=>$value){
		if( strstr($value,$webdb[www_url]) ){
			continue;
		}
		$listdb["$value"]=$value;
	}
	unset($filedb);
	foreach( $listdb AS $key=>$value){
		$filedb[]=$value;
		$name=rands(5)."__".basename($value);
		if(!is_dir(ROOT_PATH."$webdb[updir]/form")){
			makepath(ROOT_PATH."$webdb[updir]/form");
		}
		$ck=0;
		if( @copy($value,ROOT_PATH."$webdb[updir]/form/$name") ){
			$ck=1;
		}elseif($filestr=file_get_contents($value)){
			$ck=1;
			write_file(ROOT_PATH."$webdb[updir]/form/$name",$filestr);
		}
	
		/*��ˮӡ*/
		if($ck&&$webdb[is_waterimg]&&$webdb[if_gdimg])
		{
			include_once(ROOT_PATH."inc/waterimage.php");
			$uploadfile=ROOT_PATH."$webdb[updir]/form/$name";
			imageWaterMark($uploadfile,$webdb[waterpos],ROOT_PATH.$webdb[waterimg]);
		}

		if($ck){
			$str=str_replace("$value","http://www_qibosoft_com/Tmp_updir/form/$name",$str);
		}
	}
	return $str;
}



/*���ݴ���*/

function query_reply($aid,$rid,$type=''){
	global $ExplodePage,$PageNum,$postdb,$fid,$lfjdb,$rsdb,$db,$pre,$mid,$post_db,$i_id,$uid,$web_admin,$groupdb,$lfjuid,$timestamp;
	
	$erp=get_id_table($aid);

	//ר��,�ļ�����
	if($type=='edit'||($type=='postnew'&&$postdb[special])){
		$query = $db->query("SELECT * FROM {$pre}special LIMIT 1000");
		while($rs = $db->fetch_array($query)){
			if($type=='edit'){
				extract($db->get_one("SELECT topic FROM {$pre}reply$erp WHERE rid='$rid'"));
				if(!$topic){
					break ;
				}
			}
			$detail=explode(",",$rs[aids]);
			if(in_array($rs[id],$postdb[special])){
				//��ֹ˽���ύ����
				if($rs[allowpost]&&!$web_admin){
					if( !in_array($groupdb['gid'],explode(",",$rs[allowpost])) ){
						if(!$lfjuid||$rs[uid]!=$lfjuid ){
							continue;
						}				
					}
				}
				if(!in_array($aid,$detail)){
					if($detail[0]==''){unset($detail[0]);}
					$detail[]=$aid;
					$string=implode(",",$detail);
					$db->query("UPDATE {$pre}special SET aids='$string' WHERE id='$rs[id]'");
				}
			}else{
				if(in_array($aid,$detail)){
					foreach( $detail AS $key=>$value){
						if($value==$aid){
							unset($detail[$key]);
						}
					}
					$string=implode(",",$detail);
					$db->query("UPDATE {$pre}special SET aids='$string' WHERE id='$rs[id]'");
				}
			}
		}
	}

	if($ExplodePage==1&&$PageNum>0)
	{
		$contentDB=explode_content(stripslashes($postdb[content]),$PageNum);
	}
	elseif($ExplodePage!=1&&strstr($postdb[content],'[-page-]'))
	{
		$contentDB=explode("[-page-]",stripslashes($postdb[content]));
		foreach( $contentDB AS $key=>$value)
		{
			$contentDB[$key]=addslashes($value);
		}
	}
	else
	{
		$contentDB[]=$postdb[content];
	}
	
	foreach( $contentDB AS $key=>$content)
	{
		$j++;
		if($j==1&&($type=='postnew'||$type=='edit'))
		{
			if($type=='postnew')
			{
				$db->query("INSERT INTO `{$pre}reply$erp` ( `aid` ,`fid` ,`uid` , `content` ,`subhead`,`topic`) VALUES ( '$aid', '$fid','$lfjdb[uid]', '$content','$postdb[subhead]','1')");
			}
			elseif($type=='edit')
			{
				$db->query("UPDATE `{$pre}reply$erp` SET fid='$fid',content='$content',subhead='$postdb[subhead]' WHERE rid='$rid'");
			}
		}
		else
		{
			$db->query("INSERT INTO `{$pre}reply$erp` ( `aid` ,  `fid` ,`uid` ,  `content` ,`subhead`,`topic`) VALUES ( '$aid','$fid','$uid','$content','$postdb[subhead]','0')");
		}

		if($mid&&$j==1)
		{
			if($type=='edit')
			{
				$basedb=array(
						'uid'=>$lfjdb[uid],
						'fid'=>$fid,
						'aid'=>$aid,
						'rid'=>'',
						'i_id'=>$i_id
				);
				query_article_module($mid,'edit',$post_db,$basedb);
			}
			else
			{
				$rid=$db->insert_id();
				$basedb=array(
							'uid'=>$uid,
							'fid'=>$fid,
							'aid'=>$aid,
							'rid'=>$rid
						);
				query_article_module($mid,'add',$post_db,$basedb);
			}
		}
	}
	@extract($db->get_one("SELECT COUNT(*) AS NUM FROM `{$pre}reply$erp` WHERE `aid`='$aid'"));
	$db->query("UPDATE `{$pre}article$erp` SET pages='$NUM' WHERE aid='$aid'");
}


/**
*�����Զ�ȡ����
**/
function explode_content($content,$length){
	$i=0;
	$k=1;
	$j=0;
	$wn=0;
	$s='';
	$e=1;
	$yh=0;
	while($k){
		$d=$content[$i];
		if($d!==''){
			if(ord($d)>127){
				$j++;
				$num=2;
				$i++;
			}else{
				$num=1;
			}
			$j++;
		}else{
			if($s){
				$listdb[]=addslashes($s);
			}
			$k=0;
		}
		$v1=$j-$num;
		$w=substr($content,$v1,$num);
		if($w!==''){
			
			if($w=='<'){
				$e=0;
			}
			if(!$e&&$w=='"'){
				$yh++;
			}
			if($e&&$w!=' '&&$w!='��'){
				$wn++;
			}
			if($w=='>'&&$yh%2==0){
				$e=1;
			}
			$s.=$w;
		}
		if($wn>=$length&&$e){
			$listdb[]=addslashes($s);
			$s='';
			$wn=0;
		}
		$i++;
	}
	return $listdb;
}

//�ɼ��ⲿͼƬ
function get_outpic($str,$fid=0,$getpic=1){
	global $webdb,$lfjuid;
	if(!$getpic){
		return $str;
	}
	preg_match_all("/http:\/\/([^ '\"<>]+)\.(gif|jpg|png)/is",$str,$array);
	$filedb=$array[0];
	foreach( $filedb AS $key=>$value){
		if( strstr($value,$webdb[www_url]) ){
			continue;
		}
		$listdb["$value"]=$value;
	}
	unset($filedb);
	foreach( $listdb AS $key=>$value){
		$filedb[]=$value;
		$name=$lfjuid.'_'.rands(5)."__".basename($value);
		if(!is_dir(ROOT_PATH."$webdb[updir]/article/$fid")){
			makepath(ROOT_PATH."$webdb[updir]/article/$fid");
		}
		$ck=0;
		if( @copy($value,ROOT_PATH."$webdb[updir]/article/$fid/$name") ){
			$ck=1;
		}elseif($filestr=file_get_contents($value)){
			$ck=1;
			write_file(ROOT_PATH."$webdb[updir]/article/$fid/$name",$filestr);
		}
	
		/*��ˮӡ*/
		if($ck&&$webdb[is_waterimg]&&$webdb[if_gdimg])
		{
			include_once(ROOT_PATH."inc/waterimage.php");
			$uploadfile=ROOT_PATH."$webdb[updir]/article/$fid/$name";
			imageWaterMark($uploadfile,$webdb[waterpos],ROOT_PATH.$webdb[waterimg]);
		}

		if($ck){
			//$str=str_replace("$value","http://www_php168_com/Tmp_updir/article/$fid/$name",$str);
			$str=str_replace("$value","$webdb[www_url]/$webdb[updir]/article/$fid/$name",$str);
		}
	}
	return $str;
}

function article_more_set_ckecked($type='postnew'){
	global $postdb,$rsdb,$timestamp,$web_admin,$groupdb;

	foreach( $postdb[tpl] AS $key=>$value){
		if($value&&!eregi("(.htm|.html)$",$value)){
			showerr("ģ���׺��ֻ����htm��html��β�ſ���,��ģ��������:$value");
		}
	}
	$postdb[template]	=	@serialize($postdb[tpl]);
	$postdb[allowview]	=	@implode(",",$postdb[allowview]);
	$postdb[allowdown]	=	@implode(",",$postdb[allowdown]);

	$postdb[posttime]	&&	$postdb[posttime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$postdb[posttime]);
	$postdb[begintime]	&&	$postdb[begintime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$postdb[begintime]);
	$postdb[endtime]	&&	$postdb[endtime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$postdb[endtime]);


	if(!$web_admin&&!$groupdb[SetTileColor]){
		if($type=='postnew'){
			$postdb[titlecolor]=$postdb[fonttype]='';
		}else{
			$postdb[titlecolor]=$rsdb[titlecolor];
			$postdb[fonttype]=$rsdb[fonttype];
		}
	}
	if(!$web_admin&&!$groupdb[SetHtmlName]){
		$postdb[htmlname]='';
	}

	if(!$web_admin){
		$postdb[addcopyfrom]='';
	}

	if(!$web_admin&&!$groupdb[AddArticleKeywordNum]){
		$postdb[addkeyword]='';
	}
	if(!$web_admin&&!$groupdb[SetArticleTpl]&&!$groupdb[SelectArticleTpl]){
		if($type=='postnew'){
			$postdb[template]='';
		}else{
			$postdb[template]=$rsdb[template];
		}
	}
	if(!$web_admin&&!$groupdb[SelectArticleStyle]){
		if($type=='postnew'){
			$postdb[style]='';
		}else{
			$postdb[style]=$rsdb[style];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticlePosttime]){
		if($type=='postnew'){
			$postdb[posttime]=$timestamp;
		}else{
			$postdb[posttime]=$rsdb[posttime];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleViewtime]){
		if($type=='postnew'){
			$postdb[begintime]=$postdb[endtime]='';
		}else{
			$postdb[begintime]=$rsdb[begintime];
			$postdb[endtime]=$rsdb[endtime];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleHitNum]){
		if($type=='postnew'){
			$postdb[hits]='';
		}else{
			$postdb[hits]=$rsdb[hits];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticlePassword]){
		if($type=='postnew'){
			$postdb[passwd]='';
		}else{
			$postdb[passwd]=$rsdb[passwd];
		}
	}
	if(!$web_admin&&!$groupdb[SetSellArticle]){
		if($type=='postnew'){
			$postdb[money]='';
		}else{
			$postdb[money]=$rsdb[money];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleDownGroup]){
		if($type=='postnew'){
			$postdb[allowdown]='';
		}else{
			$postdb[allowdown]=$rsdb[allowdown];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleViewGroup]){
		if($type=='postnew'){
			$postdb[allowview]='';
		}else{
			$postdb[allowview]=$rsdb[allowview];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleJumpurl]){
		if($type=='postnew'){
			$postdb[jumpurl]='';
		}else{
			$postdb[jumpurl]=$rsdb[jumpurl];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleIframeurl]){
		if($type=='postnew'){
			$postdb[iframeurl]='';
		}else{
			$postdb[iframeurl]=$rsdb[iframeurl];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleDescription]){
		if($type=='postnew'){
			$postdb[description]='';
		}else{
			$postdb[description]=$rsdb[description];
		}
	}
	if(!$web_admin&&!$groupdb[PassContribute]){
		if($type=='postnew'){
			$postdb[yz]='';
		}else{
			$postdb[yz]=$rsdb[yz];
		}
	}
	if(!$web_admin&&!$groupdb[SetArticleTopCom]){
		if($type=='postnew'){
			$postdb[top]=$postdb[levels]='';
		}else{
			$postdb[top]=$rsdb[top];
			$postdb[levels]=$rsdb[levels];
		}
	}
}

//��ƪ���¾�̬��ҳ����
function make_article_html($comebackurl='/',$type='',$articleDB=''){	
	global $webdb,$db,$pre,$showHtml_Type;	
	if($webdb[NewsMakeHtml]!=1){	
		return ;
	}

	if($articleDB){
		$id=$aid=$articleDB[aid];
		$postdb[posttime]=$articleDB[posttime];
		$fid=$articleDB[fid];
		$fidDB=$db->get_one("SELECT * FROM `{$pre}sort` WHERE fid='$fid' ");
	}else{
		global $fid,$postdb,$aid,$fidDB;
		$id=$aid;
	}
	if($type=='del')
	{
		if($fidDB[bencandy_html])
		{
			$filename_b=$fidDB[bencandy_html];
		}
		else
		{
			$filename_b=$webdb[bencandy_filename];
		}
		$dirid=floor($aid/1000);
		if(strstr($filename_b,'$time_')){
			$time_Y=date("Y",$postdb[posttime]);
			$time_y=date("y",$postdb[posttime]);
			$time_m=date("m",$postdb[posttime]);
			$time_d=date("d",$postdb[posttime]);
			$time_W=date("W",$postdb[posttime]);
			$time_H=date("H",$postdb[posttime]);
			$time_i=date("i",$postdb[posttime]);
			$time_s=date("s",$postdb[posttime]);
		}
		if($type=='del'){
			$page=1;
			while($page){
				//��������ҳ����ҳ��$pageȥ��
				if($page==1){
					$filename_b=preg_replace("/(.*)(-{\\\$page}|_{\\\$page})(.*)/is","\\1\\3",$filename_b);
				}
				//��������ҳ����ĿС��1000ƪ����ʱ,��DIR��Ŀ¼ȥ��
				if($dirid==0){
					$filename_b=preg_replace("/(.*)(-{\\\$dirid}|_{\\\$dirid})(.*)/is","\\1\\3",$filename_b);
				}
				eval("\$showurl=\"$filename_b\";");
				if( is_file(ROOT_PATH."$showurl") ){
					unlink(ROOT_PATH."$showurl");
					$page++;
				}else{
					$page=0;
				}
			}
		}
	}
	if($comebackurl){
		echo "<META HTTP-EQUIV=REFRESH CONTENT='0;URL=$comebackurl'>";
		exit;
	}

	/*
	else
	{
		$erp=get_id_table($aid);
		extract($db->get_one("SELECT COUNT(*) AS PageNum FROM `{$pre}reply$erp` WHERE aid='$aid' "));
		for($page=1;$page<=$PageNum;$page++)
		{
			$ids.="$aid-$page,";
		}
	}

	$cacheid=time();

	//�޸��뷢���ҳ����ʱ,�б�ҳ�����봦��
	if($type=='reply'){
		$showJumpurl=$comebackurl;
	}else{
		$showJumpurl="$webdb[www_url]/do/list_html.php?fid=$fid&cacheid=$cacheid";
	}

	if(!is_dir('../cache/htm_cache')){
		mkdir('../cache/htm_cache');
		chmod('../cache/htm_cache',0777);
	}
	write_file(ROOT_PATH."cache/htm_cache/{$cacheid}_makeShow1.php","<?php\r\n\$JumpUrl='$showJumpurl';\$fiddb[]='$fidDB[fid]';\$iddb[]='$ids';");

	if($type!='reply'){
		//ֻ���������㸸����Ŀ
		$fupid = '';
		if($fidDB[fup]){
			$fupid=",$fidDB[fup]";
			extract($db->get_one("SELECT fup AS FUP FROM {$pre}sort WHERE fid='$fidDB[fup]'"));
			$FUP && $fupid.=",$FUP";
		}		
		write_file(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist.php","<?php\r\n\$allfid='$fidDB[fid]$fupid';\$JumpUrl='$comebackurl';");
	}
	header("location:$webdb[www_url]/do/bencandy_html.php?fid=$fid&cacheid=$cacheid");
	exit;
	*/
}

//��ƪ���¾�̬��ҳ����,��V7��ʼֹͣʹ�ô˺�����
function make_more_article_html($comebackurl='/',$type='',$aidDB=''){
	global $db,$pre,$webdb,$webdb,$showHtml_Type;
	if($webdb[NewsMakeHtml]!=1)
	{
		return ;
	}
	foreach($aidDB AS $key=>$value){
		if($value<1){
			unset($aidDB[$key]);
		}else{
			$aidDB[$key]=intval($aidDB[$key]);
		}
	}
	$string=implode(",",$aidDB);
	if(!$string){
		return ;
	}
	$query = $db->query("SELECT A.*,B.bencandy_html,B.list_html,D.aid FROM {$pre}article_db D LEFT JOIN  {$pre}article A ON D.aid=A.aid LEFT JOIN {$pre}sort B ON A.fid=B.fid WHERE D.aid IN ($string)");
	while($rs = $db->fetch_array($query)){
		if(!$rs[title]&&$_rs=get_one_article($rs[aid])){
			$rs=$_rs+$rs;
		}
		$PageNum=$rs[pages]?$rs[pages]:1;
		$aid=$id=$rs[aid];
		$fid=$rs[fid];

		//ɾ������ǰҪ�Ѿ�̬ҳ��ɾ��
		if($type=='del_0')
		{
			if($rs[bencandy_html])
			{
				$filename_b=$rs[bencandy_html];
			}
			else
			{
				$filename_b=$webdb[bencandy_filename];
			}
			$dirid=floor($aid/1000);
			if(strstr($filename_b,'$time_')){
				$time_Y=date("Y",$rs[posttime]);
				$time_y=date("y",$rs[posttime]);
				$time_m=date("m",$rs[posttime]);
				$time_d=date("d",$rs[posttime]);
				$time_W=date("W",$rs[posttime]);
				$time_H=date("H",$rs[posttime]);
				$time_i=date("i",$rs[posttime]);
				$time_s=date("s",$rs[posttime]);
			}
			if($type=='del_0'){
				$page=1;
				while($page){
					//��������ҳ����ҳ��$pageȥ��
					if($page==1){
						$filename_b=preg_replace("/(.*)(-{\\\$page}|_{\\\$page})(.*)/is","\\1\\3",$filename_b);
					}
					//��������ҳ����ĿС��1000ƪ����ʱ,��DIR��Ŀ¼ȥ��
					if($dirid==0){
						$filename_b=preg_replace("/(.*)(-{\\\$dirid}|_{\\\$dirid})(.*)/is","\\1\\3",$filename_b);
					}
					eval("\$showurl=\"$filename_b\";");
					if( is_file(ROOT_PATH."$showurl") ){
						unlink(ROOT_PATH."$showurl");
						$page++;
					}else{
						$page=0;
					}
				}
			}
		}
		else
		{
			$ids='';
			for($page=1;$page<=$PageNum;$page++)
			{
				$ids.="$aid-$page,";
			}
			$showcode.="\$fiddb[]='$rs[fid]';\$iddb[]='$ids';\r\n";
		}
			
		$f_db[$fid]=$fid;
		$star_fid || $star_fid=$fid;
	}
	
	//ɾ�����º�Ĳ���
	if($type=='del_1')
	{
		global $Jump_url;
		header("location:$Jump_url");
		exit;
	}
	else
	{
		if(!is_dir('../cache/htm_cache')){
			mkdir('../cache/htm_cache');
			chmod('../cache/htm_cache',0777);
		}
		$cacheid=time();

		//�޸��뷢���ҳ����ʱ,�б�ҳ�����봦��
		if($type=='reply'){
			$showJumpurl=$comebackurl;
		}else{
			$showJumpurl="$webdb[www_url]/do/list_html.php?fid=$star_fid&cacheid=$cacheid";
			$allfid=implode(",",$f_db);
			write_file(ROOT_PATH."cache/htm_cache/{$cacheid}_makelist.php","<?php\r\n\$allfid='$allfid';\$JumpUrl='$comebackurl';");
		}
		write_file(ROOT_PATH."cache/htm_cache/{$cacheid}_makeShow1.php","<?php\r\n\$JumpUrl='$showJumpurl';$showcode");

		//ɾ������ǰ������ת
		if($type=='del_0')
		{
			global $Jump_url;
			$Jump_url="$webdb[www_url]/do/bencandy_html.php?fid=$star_fid&cacheid=$cacheid";
		}
		else
		{
			header("location:$webdb[www_url]/do/bencandy_html.php?fid=$star_fid&cacheid=$cacheid");
			exit;
		}
	}
}

/*���û�ѡ�񷢱����µ���Ŀ�б�*/
function list_post_allsort($fid=0){
	global $db,$pre,$sortdb,$lfjid,$groupdb,$web_admin,$lfjuid,$allowpost,$Fid_db;
	$query=$db->query("SELECT * FROM {$pre}sort WHERE fup='$fid' ORDER BY list DESC");
	while( $rs=$db->fetch_array($query) ){
		$icon="";
		for($i=1;$i<$rs['class'];$i++){
			$icon.="&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		if($icon){
			$icon=substr($icon,0,-24);
			$icon.="--";
		}
		$rs[icon]=$icon;
	
		$rs[post]=$rs[NUM]=$rs[do_art]='';
		$detail_admin=@explode(",",$rs[admin]);
		$detail_allowpost=@explode(",",$rs[allowpost]);
		if(!$rs[type]&&( $web_admin||($lfjid&&@in_array($lfjid,$detail_admin))||@in_array($groupdb['gid'],$detail_allowpost) ))
		{	
			$erp=$Fid_db[iftable][$rs[fid]];
			$_rs=$db->get_one("SELECT COUNT(*) AS NUM FROM {$pre}article$erp WHERE fid='$rs[fid]' AND uid='$lfjuid'");
			if($_rs[NUM]&&$lfjid){
				$rs[NUM]="( <b>{$_rs[NUM]}</b> )";
				$rs[do_art]="<A HREF='myarticle.php?job=myarticle&fid=$rs[fid]' class='manage_article'>����</A>";
			}
			$rs[post]="<A HREF='?job=postnew&fid=$rs[fid]' class='post_article'>����</A>";
			$allowpost++;
		}

		$sortdb[]=$rs;
		list_post_allsort($rs[fid]);
	}
}
/*���û�ѡ�񷢱����µ���Ŀѡ�������˵�*/
function list_post_selectsort($fid=0,$cfid,$mid='',$only=''){/*
	global $db,$pre,$lfjid,$groupdb,$web_admin,$lfjuid,$webdb;
	$query=$db->query("SELECT * FROM {$pre}sort WHERE fup='$fid' ORDER BY list DESC");
	while( $rs=$db->fetch_array($query) ){
		$icon="";
		for($i=1;$i<$rs['class'];$i++){
			$icon.="&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		if($icon){
			$icon=substr($icon,0,-24);
			$icon.="--";
		}
		$detail_admin=@explode(",",$rs[admin]);
		$detail_allowpost=@explode(",",$rs[allowpost]);
		if(!$rs[type]&&( $web_admin||($lfjid&&@in_array($lfjid,$detail_admin))||@in_array($groupdb['gid'],$detail_allowpost) )){
			$color='#000000';
			$_f=$rs[fid];
		}elseif($rs[type]){
			$color='blue;font-weight:bold;';
			$_f='';
		}else{
			if($webdb[HideNopowerPost]){
				continue;
			}
			$color='#cccccc';
			$_f='0';
		}
		$ckk=$cfid==$rs[fid]?' selected ':'';
		
		if(!$only||$rs[fmid]==$mid){
			$sortdb.="<option value='$_f' style='color:$color;' $ckk>$icon$rs[name]</option>";
		}		
		$sortdb.=list_post_selectsort($rs[fid],$cfid,$mid,$only);
	}
	return $sortdb;*/
}

/*�·�������*/

function post_new(){
	global $db,$pre,$Fid_db,$postdb,$fid,$fidDB,$mid,$lfjuid,$lfjdb,$webdb,$timestamp,$FROMURL,$aid,$votesdb,$vote_db,$groupdb,$web_admin,$fu_fiddb,$onlineip;

	$postdb['list']=$postdb[top]?$timestamp*1.3:$timestamp;

	if(!$postdb[posttime])
	{
		$postdb[posttime]=$timestamp;
	}
	$erp=$Fid_db[iftable][$fid];
	$db->query("
	INSERT INTO `{$pre}article$erp` 
	( `title`, `smalltitle`,  `fid`,`fname`, `hits`, `pages`, `posttime`, `list`, `uid`, `username`, `author`, `copyfrom`, `copyfromurl`, `titlecolor`, `fonttype`, `picurl`, `ispic`, `yz`, `yzer`, `yztime`, `keywords`, `jumpurl`, `iframeurl`, `style`, `template`, `target`,`ip`, `lastfid`, `money`, `passwd`, `editer`, `edittime`, `begintime`, `endtime`, `description`, `levels`,allowdown,allowview,mid,htmlname,forbidcomment) 
	VALUES
	('$postdb[title]','$postdb[smalltitle]','$fid','$fidDB[name]','$postdb[hits]','1','$postdb[posttime]','$postdb[list]','$lfjdb[uid]','$lfjdb[username]','$postdb[author]','$postdb[copyfrom]','$postdb[copyfromurl]','$postdb[titlecolor]','$postdb[fonttype]','$postdb[picurl]','$postdb[ispic]','$postdb[yz]','$postdb[yzer]','$postdb[yztime]','$postdb[keywords]','$postdb[jumpurl]','$postdb[iframeurl]','$postdb[style]','$postdb[template]','$postdb[target]','$onlineip','0','$postdb[money]','$postdb[passwd]','$postdb[editer]','$postdb[edittime]','$postdb[begintime]','$postdb[endtime]','$postdb[description]','$postdb[levels]','$postdb[allowdown]','$postdb[allowview]','$mid','$postdb[htmlname]','$postdb[forbidcomment]')
	");
	
	if($postdb[htmlname]){
		//�Զ�������ҳ�ļ���
		get_showhtmltype();
	}

	$II=1;
	$aid=$db->insert_id();
	$db->query("INSERT INTO `{$pre}article_db` (`aid`) VALUES ('$aid')");
	
	//����Ŀ����
	query_fu_sort($fu_fiddb,$aid);

	//�������
	query_reply($aid,'','postnew');

	//�Ƹ�����
	$array = array('title'=>$postdb[title],'fid'=>$fid,'aid'=>$aid);
	if($postdb[yz]){
		Give_article_money($lfjuid,'yz',$array);
	}
	if($postdb[com]){
		Give_article_money($lfjuid,'com',$array);
	}

	//��ӹؼ���
	keyword_add($aid,$postdb[keywords],$lfjdb[uid]);

	//ͶƱ
	if($votesdb[1][title]||$votesdb[2][title]||$votesdb[3][title]){
		if(!$vote_db[name]){
			$vote_db[name]=$postdb[title];
		}
		if(!$vote_db[about]){
			$vote_db[about]=$postdb[title];
		}
		//��Щ�û�������Ȩ�޵�
		if($groupdb[SetVote]||$web_admin){
			add_vote($aid);
		}
	}
	//ɾ�������ļ�
	delete_cache_file($fid,$aid);
	
	//��ʱ����
	corntab_post('DE');
	corntab_post('EN',$aid);	
}


/*�޸�����*/

function post_edit(){
	global $db,$pre,$postdb,$fid,$fidDB,$Fid_db,$mid,$lfjuid,$rsdb,$lfjdb,$webdb,$timestamp,$aid,$FROMURL,$groupdb,$web_admin,$fu_fiddb;

	if( $rsdb[levels]&&$postdb[levels] )
	{
		$postdb[levels]=$rsdb[levels];	//������������2,3,4...�Է�����
	}
	if($postdb[top])
	{
		$postdb['list']=($rsdb['list']>$timestamp)?$rsdb['list']:$timestamp*1.3;
	}
	else
	{
		$postdb['list']=($rsdb['list']>$timestamp)?$timestamp:$rsdb['list'];
	}

	if(!$web_admin&&$groupdb[EditPassPower]==1){
		$postdb[yz]='';
	}
	$erp=$Fid_db[iftable][$fid];
	$db->query("UPDATE `{$pre}article$erp` SET title='$postdb[title]',smalltitle='$postdb[smalltitle]',fid='$fid',fname='$fidDB[name]',hits='$postdb[hits]',posttime='$postdb[posttime]',list='$postdb[list]',author='$postdb[author]',copyfrom='$postdb[copyfrom]',copyfromurl='$postdb[copyfromurl]',titlecolor='$postdb[titlecolor]',fonttype='$postdb[fonttype]',picurl='$postdb[picurl]',ispic='$postdb[ispic]',yz='$postdb[yz]',levels='$postdb[levels]',keywords='$postdb[keywords]',jumpurl='$postdb[jumpurl]',iframeurl='$postdb[iframeurl]',style='$postdb[style]',template='$postdb[template]',target='$postdb[target]',money='$postdb[money]',passwd='$postdb[passwd]',editer='$userdb[username]',edittime='$timestamp',begintime='$postdb[begintime]',endtime='$postdb[endtime]',description='$postdb[description]',allowview='$postdb[allowview]',allowdown='$postdb[allowdown]',htmlname='$postdb[htmlname]',forbidcomment='$postdb[forbidcomment]' WHERE aid='$aid' ");
	
	if($postdb[htmlname]!=$rsdb[htmlname]){
		//�Զ�������ҳ�ļ���
		get_showhtmltype();
	}
	
	//����Ŀ����
	query_fu_sort($fu_fiddb,$aid);

	//�޸�����
	query_reply($aid,$rsdb[rid],'edit');

	//�Ƹ�����
	if(!$postdb[yz]&&$rsdb[yz]){
		Give_article_money($lfjuid,'unyz',$rsdb);
	}
	if(!$postdb[levels]&&$rsdb[levels]){
		Give_article_money($lfjuid,'uncom',$rsdb);
	}

	//��ӹؼ���
	if($rsdb[keywords]!=$postdb[keywords]){
		keyword_del($aid,$rsdb[keywords]);
		keyword_add($aid,$postdb[keywords],$lfjdb[uid]);
	}

	if($rsdb[ifvote]){
		edit_vote($aid);
	}

	//ɾ�������ļ�
	delete_cache_file($fid,$aid);
	
	corntab_post('DE');
	corntab_post('EN',$aid);  //��ʱ����
}

//���û������������Ƽ����µĽ���yz,unyz,com,uncom,del

function Give_article_money($uid,$type='',$rsdb){
	global $db,$pre,$webdb;
	if($type=='yz'){
		$money	=	$webdb[postArticleMoney];
		$msg = '��������ͨ����˽���';
	}elseif($type=='unyz'){
		$money	=	-$webdb[postArticleMoney];
		$msg = '��������ȡ����˿۷�';
	}elseif($type=='com'){
		$money	=	$webdb[comArticleMoney];
		$msg = '���±��Ƽ�����';
	}elseif($type=='uncom'){
		$money	=	-$webdb[comArticleMoney];
		$msg = '���±�ȡ���Ƽ��۷�';
	}
	
	if($type=='del'){
		$money	=	$webdb[deleteArticleMoney];
		$msg = '���±�ɾ���۷�:'.$rsdb[title];
	}else{
		$msg .= "<A HREF='$webdb[www_url]$webdb[path]/bencandy.php?fid=$rsdb[fid]&aid=$rsdb[aid]' target=_blank>".get_word($rsdb[title],30)."</A>";
	}
	if(!$money||!$uid){
		return ;
	}
	add_user($uid,$money,$msg);
}

//��ӹؼ���
function keyword_add($aid,$keyword,$uid=0){
	global $db,$pre;
	$detail=explode(' ',str_replace(array('\\',"'",'"'),array('','',''),$keyword)); 
	foreach( $detail AS $key=>$value){
		if($value){
			$_rs=$db->get_one("SELECT * FROM `{$pre}keyword` WHERE keywords='$value'");
			$id=$_rs[id];
			if(!$id){
				$db->query("INSERT INTO `{$pre}keyword` ( `keywords`,`num`,`ifhide`,`uid` ) VALUES ('$value',1,1,'$uid' )");
				$id=$db->insert_id();
			}else{
				$db->query("UPDATE `{$pre}keyword` SET num=num+1 WHERE `keywords`='$value'");
			}
			$db->query("INSERT INTO `{$pre}keywordid` ( `id`,`aid` ) VALUES ('$id','$aid')");
		}
	}
}

//ɾ���ؼ���
function keyword_del($aid,$keyword){
	global $db,$pre;
	if(!$keyword){
		return ;
	}
	$detail2=explode(" ",$keyword);
	foreach( $detail2 AS $key=>$value){
		if($value){
			$db->query("UPDATE `{$pre}keyword` SET num=num-1 WHERE `keywords`='$value'");
			$_rs=$db->get_one("SELECT * FROM `{$pre}keyword` WHERE `keywords`='$value'");
			$id=$_rs[id];
			$db->query("DELETE FROM `{$pre}keywordid` WHERE `id`='$id' AND aid='$aid'");		
		}
	}
}

//�ؼ��ָ�ʽ��
function keyword_ck($keyword,$title){
	global $SPword;
	if($title){
		require_once(ROOT_PATH."inc/splitword.php");
		$de=splitword($title);
		$detail=explode(" ",$de);
		foreach( $detail AS $key=>$value){
			//С��3���ַ���.����Ϊ�ؼ���
			if(strlen($value)<3){
				continue;
			}
			$keyword.=" $value";
		}
	}
	if($keyword){
		$keyword=str_replace("��"," ",$keyword);
		$keyword=str_replace(","," ",$keyword);
		$keyword=str_replace("��"," ",$keyword);
		$keyword=str_replace("��"," ",$keyword);
		$detail=explode(' ',str_replace(array('\\',"'",'"'),array('','',''),$keyword)); 
		foreach( $detail AS $key=>$value){
			//����3���ֽڵ�,����Ϊ�ؼ���,һ�������൱�������ֽ�
			if(strlen($value)>2){
				 $array[$value]=$value;
			}
		}
		$keyword=implode(" ",$array);
		return $keyword;
	}
}

//����ͶƱ
function add_vote($aid){
	global $db,$pre,$timestamp,$votesdb,$vote_db,$lfjuid,$ModuleDB;

	//û��װͶƱģ��
	if(!$ModuleDB['vote_']){
		return ;
	}
	$vote_path = $ModuleDB['vote_']['dirname'] ? $ModuleDB['vote_']['dirname'] : 'vote';
	$vote_db[begintime]&&$vote_db[begintime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$vote_db[begintime]);
	$vote_db[endtime]&&$vote_db[endtime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$vote_db[endtime]);
	$vote_db[votetype]=intval($vote_db[votetype]);
	$tplcode=addslashes(read_file(ROOT_PATH."$vote_path/template/default/vote_js/$vote_db[votetype].htm"));

	$vote_db[name]=filtrate($vote_db[name]);
	$vote_db[about]=filtrate($vote_db[about]);

	$db->query("INSERT INTO `{$pre}vote_topic` ( `name` , `about` , `type` , `limittime` , `limitip` , `posttime` ,  `begintime` , `endtime` , `forbidguestvote` , `aid` , `tplcode` , `votetype` ,`ifcomment` , `uid`) 
		VALUES (
		'$vote_db[name]','$vote_db[about]','$vote_db[type]','$vote_db[limittime]','$vote_db[limitip]','$timestamp','$vote_db[begintime]','$vote_db[endtime]','$vote_db[forbidguestvote]','$aid','$tplcode','$vote_db[votetype]','1','$lfjuid'
		)");
	$rs=$db->get_one("SELECT * FROM `{$pre}vote_topic` ORDER BY cid DESC LIMIT 1");
	foreach($votesdb AS $key=>$value){
		$value[title]=filtrate($value[title]);
		$value[img]=filtrate($value[img]);
		$value[describes]=filtrate($value[describes]);
		$value[url]=filtrate($value[url]);
		$value[title]&&$db->query("INSERT INTO `{$pre}vote_element` (`cid` , `title` , `img` , `describes`, `url`) VALUES ('$rs[cid]', '$value[title]', '$value[img]', '$value[describes]', '$value[url]')");
	}
	$erp=get_id_table($aid);
	$db->query("UPDATE {$pre}article$erp SET ifvote=1 WHERE aid='$aid'");
	return 1;
}

//�޸�ͶƱ
function edit_vote($aid){
	global $db,$pre,$timestamp,$votesdb,$vote_db,$ModuleDB;

	//û��װͶƱģ��
	if(!$ModuleDB['vote_']){
		return ;
	}
	$vote_path = $ModuleDB['vote_']['dirname'] ? $ModuleDB['vote_']['dirname'] : 'vote';

	$vote_db[begintime]&&$vote_db[begintime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$vote_db[begintime]);
	$vote_db[endtime]&&$vote_db[endtime]=preg_replace("/([\d]+)-([\d]+)-([\d]+) ([\d]+):([\d]+):([\d]+)/eis","mk_time('\\4','\\5', '\\6', '\\2', '\\3', '\\1')",$vote_db[endtime]);
	$vote_db[votetype]=intval($vote_db[votetype]);
	$tplcode=addslashes(read_file(ROOT_PATH."$vote_path/template/default/vote_js/$vote_db[votetype].htm"));

	$vote_db[name]=filtrate($vote_db[name]);
	$vote_db[about]=filtrate($vote_db[about]);
	$db->query("UPDATE `{$pre}vote_topic` SET name='$vote_db[name]',about='$vote_db[about]',type='$vote_db[type]',limittime='$vote_db[limittime]',limitip='$vote_db[limitip]',begintime='$vote_db[begintime]',endtime='$vote_db[endtime]',forbidguestvote='$vote_db[forbidguestvote]',votetype='$vote_db[votetype]',tplcode='$tplcode' WHERE aid='$aid'");
	@extract($db->get_one("SELECT cid FROM `{$pre}vote_topic` WHERE aid='$aid'"));
	foreach($votesdb AS $key=>$v){
		$v[title]=filtrate($v[title]);
		$v[img]=filtrate($v[img]);
		$v[describes]=filtrate($v[describes]);
		$v[url]=filtrate($v[url]);
		if($v[id]){
			$db->query("UPDATE `{$pre}vote_element` SET title='$v[title]',list='$v[list]',img='$v[img]',describes='$v[describes]',url='$v[url]' WHERE id='$v[id]' AND cid='$cid'");
		}else{
			$v[title]&&$db->query("INSERT INTO `{$pre}vote_element` (`cid` , `title` , `img`, `describes`, `url` ) VALUES ('$cid', '$v[title]', '$v[img]', '$v[describes]', '$v[url]')");
		}
	}
}

//����Ŀ����
function query_fu_sort($fu_fiddb,$aid){
	global $db,$pre,$lfjid,$web_admin,$groupdb;
	if($fu_fiddb){
		$db->query("DELETE FROM `{$pre}fu_article` WHERE aid='$aid'");
		foreach($fu_fiddb AS $key=>$value){
			$rs=$db->get_one("SELECT * FROM `{$pre}fu_sort` WHERE fid='$value'");
			if(!$web_admin&&$rs[allowpost]){
				if( !in_array($groupdb[gid],explode(",",$rs[allowpost])) ){
					if(!$lfjid||!in_array($lfjid,explode(",",$rs[admin]))){
						continue;
					}					
				}
			}
			$db->query("INSERT INTO `{$pre}fu_article` (`fid`,`aid`) VALUES ('$value','$aid')");
		}
	}
}


//�б������,����,�Ƽ�,�����
function listpage_title($fid,$type,$rows,$leng,$id,$keyword){
	global $pre,$webdb,$db,$Fid_db;
	if( !ereg("^(hot|com|new|lastview|like|pic)$",$type) ){
		return ;
	}

	$rows>0 || $rows=7;
	$leng>0 || $leng=60;
	$fid=intval($fid);

	$erp=$Fid_db[iftable][$fid];
	if($fid)
	{		
		$F[]=" fid=$fid ";
		foreach((array)$Fid_db[$fid] AS $key=>$value){
			$F[]=" fid=$key ";
		}
		$SQL=" (".implode("OR",$F).") ";
	}
	else
	{
		$SQL=" 1 ";
	}

	if($type=='com')
	{
		$SQL.=" AND levels=1 ";
		$ORDER=' list ';
	}
	elseif($type=='pic')
	{
		$SQL.=" AND ispic=1 ";
		$ORDER=' list ';
	}
	elseif($type=='hot')
	{
		$ORDER=' hits ';
	}
	elseif($type=='new')
	{
		$ORDER=' list ';
	}
	elseif($type=='lastview')
	{
		$ORDER=' lastview ';
	}
	elseif($type=='like')
	{
		
		$SQL.=" AND aid!='$id' ";

		if(!$keyword)
		{
			$erp=get_id_table($id);
			extract($db->get_one("SELECT keywords AS keyword FROM {$pre}article$erp WHERE aid='$id'"));
		}

		if($keyword){
			$detail=explode(' ',str_replace(array('\\',"'",'"'),array('','',''),$keyword)); 
			unset($detail2,$ids);
			foreach( $detail AS $key=>$value){
				$value && $detail2[]=" B.keywords='$value' ";
			}
			$str=implode(" OR ",$detail2);
			if($str){
				unset($ids);
				$query = $db->query("SELECT A.aid FROM {$pre}keywordid A LEFT JOIN {$pre}keyword B ON A.id=B.id WHERE $str");
				while($rs = $db->fetch_array($query)){
					$ids[]=$rs[aid];
				}
				if($ids){
					$SQL.=" AND aid IN (".implode(",",$ids).") ";
				}else{
					$SQL.=" AND 0 ";
				}				
			}
		}else{
			$SQL.=" AND 0 ";
		}
		
		$ORDER=' list ';
	}

	if(!$webdb[viewNoPassArticle]){
		$SQL.=' AND yz=1 ';
	}
	
	$SQL=" WHERE $SQL ORDER BY $ORDER DESC LIMIT $rows";
	$which='*';
	$listdb=list_article($SQL,$which,$leng,$erp);
	
	if(is_file(ROOT_PATH."template/default/$webdb[SideTitleStyle].htm")){
		$tplcode=read_file(ROOT_PATH."template/default/$webdb[SideTitleStyle].htm");
	}else{
		$tplcode=read_file(ROOT_PATH."template/default/side_tpl/0.htm");
	}
	$tplcode=addslashes($tplcode);
	foreach($listdb AS $key=>$rs){	
		if( $webdb[path] && ($webdb[NewsMakeHtml]==1||$gethtmlurl) ){
			$webdb[path]='';
		}
		$target=$rs[target]?'_blank':'_self';
		if($type=='pic'){
			$show.="<div class='p' style='float:left;width:130px;padding-left:5px;padding-top:5px;'>  <a href='$webdb[www_url]$webdb[path]/bencandy.php?fid=$rs[fid]&id=$rs[aid]' style='display:block;width:120px;height:90px;border:1px solid #ccc;' target='$target'><img style='border:2px solid #fff;' width='120' height='90' src='$rs[picurl]' border='0'></a>  <A HREF='$webdb[www_url]$webdb[path]/bencandy.php?fid=$rs[fid]&id=$rs[aid]' title='$rs[full_title]' target='$target'>$rs[title]</A>  </div>";
		}else{

			eval("\$show.=\"$tplcode\";");
		}		
	}
	if(!$show){
		$show="����...";
	}
	$show=stripslashes($show);
	return $show;
}


//��ʱ�������¹���
function corntab_post($type='EN',$id=''){
	global $db,$pre,$timestamp;
	if($type=='EN'){
		$SQL="WHERE begintime>$timestamp";
		if($id){
			$SQL.=" AND aid='$id' ";
		}
		$query = $db->query("SELECT * FROM {$pre}article $SQL");
		while($rs = $db->fetch_array($query)){
			$db->query("UPDATE {$pre}article SET yz=3 WHERE aid='$rs[aid]'");
		}
	}elseif($type=='DE'){
		$SQL="WHERE yz=3 AND begintime<$timestamp";
		if($id){
			$SQL.=" AND aid='$id' ";
		}
		$query = $db->query("SELECT * FROM {$pre}article $SQL");
		while($rs = $db->fetch_array($query)){
			$db->query("UPDATE {$pre}article SET yz=1,posttime=list WHERE aid='$rs[aid]'");
		}
	}
}



//��Ŀ����,���һЩ��Ŀ��������
function get_guide($fid){
	global $GuideFid,$webdb;
	if($webdb[sortNUM]>500){
		$GuideFid[$fid]="<a href='$webdb[www_url]' class='guide_menu'>&gt;��ҳ</a>".get_guide_in($fid);
	}else{
		@include_once(ROOT_PATH."data/guide_fid.php");
	}
}
function get_guide_in($fid){
	global $db,$pre;
	$rs=$db->get_one("SELECT * FROM {$pre}sort WHERE fid='$fid'");
	$show=" -&gt; <a  href='list.php?fid=$fid' class='guide_menu'>$rs[name]</a>";
	if($rs[fup]){
		$show = get_guide_in($rs[fup]).$show;
	}
	return $show;
}

/**
*����Ŀ�����б��ܺ���
**/
function ListThisSort($rows,$leng=50){
	global $page,$fid,$fidDB,$webdb,$pre,$Fid_db;
	if($page<1){
		$page=1;
	}
	$min=($page-1)*$rows;
	if($fidDB[listorder]==1){
		$DESC='DESC';
		$ORDER='A.posttime';
	}elseif($fidDB[listorder]==2){
		$DESC='ASC';
		$ORDER='A.posttime';
	}elseif($fidDB[listorder]==3){
		$DESC='DESC';
		$ORDER='A.hits';
	}elseif($fidDB[listorder]==4){
		$DESC='ASC';
		$ORDER='A.hits';
	}elseif($fidDB[listorder]==5){
		$DESC='DESC';
		$ORDER='A.lastview';
	}elseif($fidDB[listorder]==7){
		$DESC='DESC';
		$ORDER='A.digg_num';
	}elseif($fidDB[listorder]==8){
		$DESC='DESC';
		$ORDER='A.digg_time';
	}elseif($fidDB[listorder]==6){
		$DESC='DESC';
		$ORDER='rand()';
	}else{
		$DESC='DESC';
		$ORDER='A.list';
	}
	if(!$webdb[viewNoPassArticle]){
		$SQL_yz=' AND A.yz=1 ';
	}
	if($fid){
		$_fid_sql=" AND A.fid=$fid ";
	}else{
		$_fid_sql=" AND 1 ";
	}
	$erp=$Fid_db[iftable][$fid]?$Fid_db[iftable][$fid]:"";

	$SQL="A LEFT JOIN {$pre}reply$erp R ON A.aid=R.aid WHERE R.topic=1 $_fid_sql $SQL_yz ORDER BY $ORDER $DESC LIMIT $min,$rows";
	$which='A.*,R.content';
	$listdb=list_article($SQL,$which,$leng,$erp);
	return $listdb;
}

/**
*����,���������ȵ���
**/
function Get_article($rows=10,$leng=30,$order='list'){
	global $fid,$webdb;
	if(!$webdb[viewNoPassArticle]){
		$SQL_yz=' AND yz=1 ';
	}
	if($fid){
		$SQL="WHERE fid=$fid $SQL_yz ORDER BY $order DESC LIMIT $rows";
	}else{
		$SQL="WHERE 1 $SQL_yz ORDER BY $order DESC LIMIT $rows";
	}
	
	$which='*';
	require_once(ROOT_PATH."inc/artic_function.php");
	$listdb=list_article($SQL,$which,$leng);
	return $listdb;
}

/**
*����ĿͼƬ�б�
**/
function ListPic($rows,$leng,$page=1,$order='list'){
	global $fid,$webdb;
	if($page<1){
		$page=1;
	}
	$min=($page-1)*$rows;
	if(!$webdb[viewNoPassArticle]){
		$SQL_yz=' AND yz=1 ';
	}
	$SQL="WHERE fid=$fid AND ispic=1 $SQL_yz ORDER BY $order DESC LIMIT $min,$rows";
	$which='*';
	$listdb=list_article($SQL,$which,$leng);
	return $listdb;
}

/**
*����������Ŀ
**/
function ListMoreSort(){
	global $db,$pre,$fid,$webdb,$fidDB,$Fid_db;
	//����
	if($fidDB[config][sonListorder]==1){
		$order='A.list';
	}elseif($fidDB[config][sonListorder]==2){
		$order='A.hits';
	}elseif($fidDB[config][sonListorder]==3){
		$order='A.lastview';
	}elseif($fidDB[config][sonListorder]==4){
		$order='rand()';
	}else{
		$order='A.list';
	}
	$_order=" ORDER BY $order DESC ";

	//��ʾ����
	if($fidDB[config][sonTitleRow]>0){
		$rows=$fidDB[config][sonTitleRow];
	}elseif($webdb[ListSonRows]>0){
		$rows=$webdb[ListSonRows];
	}else{
		$rows=10;
	}

	//ÿ��������ʾ������
	if($fidDB[config][sonTitleLeng]>0){
		$leng=$fidDB[config][sonTitleLeng];
	}elseif($webdb[ListSonLeng]>0){
		$leng=$webdb[ListSonLeng];
	}else{
		$leng=30;
	}

	if(!$webdb[viewNoPassArticle]){
		$SQL_yz=' AND A.yz=1 ';
	}
	$query=$db->query("SELECT * FROM {$pre}sort WHERE fup=$fid AND forbidshow!=1 ORDER BY list DESC LIMIT 50");
	while($rs=$db->fetch_array($query)){
		$erp=$Fid_db[iftable][$rs[fid]]?$Fid_db[iftable][$rs[fid]]:'';
		$SQL="A LEFT JOIN {$pre}reply$erp R ON A.aid=R.aid WHERE R.topic=1 AND A.fid=$rs[fid] $SQL_yz $_order LIMIT $rows";
		$which='A.*,R.content';
		$rs[article]=list_article($SQL,$which,$leng,$erp);

		//�������Ŀ���ܻ�ȡ������,����ȡ����������Ŀ������
		if(!$rs[article])
		{
			$array_fid=Get_SonFid("{$pre}sort",$rs[fid]);
			if($array_fid&&count($array_fid)<50)
			{
				//�ֱ��,�������Ŀ����ͬһģ�͵Ļ�.�����ܻ�ȡ����
				$SQL="A LEFT JOIN {$pre}reply$erp R ON A.aid=R.aid WHERE R.topic=1 AND A.fid IN (".implode(',',$array_fid).") $SQL_yz $_order LIMIT $rows";
				$rs[article]=list_article($SQL,$which,$leng,$erp);
			}
		}
		$rs[logo] && $rs[logo]=tempdir($rs[logo]);
		$listdb[]=$rs;
	}
	return $listdb;
}



/**
*������α��̬���ܺ���
**/
function fake_html_Function($filename,$fid,$id,$page=1,$P='',$linkcode){
	$linkcode=stripslashes($linkcode);
	eval("\$filename=\"$filename\";");
	return "$linkcode$P$filename";
}

/**
*������α��̬���ܺ���
**/
function fake_html($content){
	global $webdb;
	
	$listpath=$webdb[list_filename2];
	$bencandypath=$webdb[bencandy_filename2];
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)bencandy\.php\?fid=([\d]+)&(aid|id)=([\d]+)&page=([\d]+)/eis","fake_html_Function('$bencandypath','\\3','\\5','\\6','\\2',' href=\\1')",$content);	//�з�ҳ������ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)bencandy\.php\?fid=([\d]+)&(id|aid)=([\d]+)/eis","fake_html_Function('$bencandypath','\\3','\\5','1','\\2',' href=\\1')",$content);	//�޷�ҳ������ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)list\.php\?fid=([\d]+)&page=([\d]+)/eis","fake_html_Function('$listpath','\\3','','\\4','\\2',' href=\\1')",$content);	//�з�ҳ���б�ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)list\.php\?fid=([\d]+)/eis","fake_html_Function('$listpath','\\3','','1','\\2',' href=\\1')",$content);	//�޷�ҳ���б�ҳ
	
	//ר��
	$listpath=$webdb[SPlist_filename2];
	$bencandypath=$webdb[SPbencandy_filename2];
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)showsp\.php\?fid=([\d]+)&id=([\d]+)/eis","fake_html_Function('$bencandypath','\\3','\\4','1','\\2',' href=\\1')",$content);	//����ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)listsp\.php\?fid=([\d]+)&page=([\d]+)/eis","fake_html_Function('$listpath','\\3','','\\4','\\2',' href=\\1')",$content);	//�з�ҳ���б�ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)listsp\.php\?fid=([\d]+)/eis","fake_html_Function('$listpath','\\3','','1','\\2',' href=\\1')",$content);	//�޷�ҳ���б�ҳ

	return $content;
}

/**
*�澲̬ר��ҳ���ܺ���
**/
function make_sphtml_Function($fid,$id,$page=1,$P='',$linkcode=''){
	global $webdb,$Html_Type,$showHtml_Type,$WEBURL,$db,$pre;
	$P=preg_replace("/(.*)(do\/)$/is","\\1",$P);
	$linkcode=stripslashes($linkcode);
	if($id){
		if($showHtml_Type[SPbencandy][$id]){
			$filename=$showHtml_Type[SPbencandy][$id];
		}elseif($Html_Type['SPbencandy'][$fid]){
			$filename=$Html_Type['SPbencandy'][$fid];
		}else{
			$filename=$webdb[SPbencandy_filename];
		}
		$dirid=floor($id/1000);
		if(strstr($filename,'$time_')){
			$rs=$db->get_one("SELECT posttime FROM {$pre}special WHERE id='$id'");
			$time_Y=date("Y",$rs[posttime]);
			$time_y=date("y",$rs[posttime]);
			$time_m=date("m",$rs[posttime]);
			$time_d=date("d",$rs[posttime]);
			$time_W=date("W",$rs[posttime]);
			$time_H=date("H",$rs[posttime]);
			$time_i=date("i",$rs[posttime]);
			$time_s=date("s",$rs[posttime]);
		}
	}else{
		if($Html_Type['SPlist'][$fid]){
			$filename=$Html_Type['SPlist'][$fid];
		}else{
			$filename=$webdb[SPlist_filename];
		}
		//if($page==1){
		//	$filename=preg_replace("/(.*)\/([^\/]+)/is","\\1/",$filename);
		//}
	}

	$dirid=floor($id/1000);
	eval("\$filename=\"$filename\";");

	//ʹ�þ��Ե�ַ,�����ڶ���Ŀ¼����URL����
	if(!$P||$P=='./'){		
		if($webdb[www_url]=='/.'){
			$P="/";
		}else{
			$P="$webdb[www_url]/";
		}	
	}
	return "$linkcode$P$filename";
}

/**
*�澲̬���ܺ���
**/
function make_html_Function($fid,$id,$page=1,$P='',$linkcode=''){
	global $webdb,$Html_Type,$showHtml_Type,$WEBURL,$db,$pre;
	$linkcode=stripslashes($linkcode);
	if($id){
		if($showHtml_Type[bencandy][$id]){
			$filename=$showHtml_Type[bencandy][$id];
		}elseif($Html_Type['bencandy'][$fid]){
			$filename=$Html_Type['bencandy'][$fid];
		}else{
			$filename=$webdb[bencandy_filename];
		}
		//��������ҳ����ҳ��$pageȥ��
		if($page==1){
			$filename=preg_replace("/(.*)(-{\\\$page}|_{\\\$page})(.*)/is","\\1\\3",$filename);
		}
		$dirid=floor($id/1000);
		//��������ҳ����ĿС��1000ƪ����ʱ,��DIR��Ŀ¼ȥ��
		if($dirid==0){
			$filename=preg_replace("/(.*)(-{\\\$dirid}|_{\\\$dirid})(.*)/is","\\1\\3",$filename);
		}
		if(strstr($filename,'$time_')){
			$erp=get_id_table($id);
			$rs=$db->get_one("SELECT posttime FROM {$pre}article$erp WHERE aid='$id'");
			$time_Y=date("Y",$rs[posttime]);
			$time_y=date("y",$rs[posttime]);
			$time_m=date("m",$rs[posttime]);
			$time_d=date("d",$rs[posttime]);
			$time_W=date("W",$rs[posttime]);
			$time_H=date("H",$rs[posttime]);
			$time_i=date("i",$rs[posttime]);
			$time_s=date("s",$rs[posttime]);
		}
	}else{
		if($Html_Type['list'][$fid]){
			$filename=$Html_Type['list'][$fid];
		}else{
			$filename=$webdb[list_filename];
		}
		if($page==1)
		{
			$filename=preg_replace("/(.*)\/([^\/]+)/is","\\1/",$filename);
		}
	}
	
	/*
	if($P&&$P!='/'&&$P!="$webdb[www_url]/"){
		if($id){
			return "{$P}bencandy.php?fid=$fid&id=$id";
		}else{
			return "{$P}list.php?fid=$fid";
		}
	}
	*/
	//$dirid=floor($id/1000);
	eval("\$filename=\"$filename\";");
	
	//�Զ�������Ŀ����
	if($Html_Type[domain][$fid]&&$Html_Type[domain_dir][$fid]){
		$rule=str_replace("/","\/",$Html_Type[domain_dir][$fid]);
		$filename=preg_replace("/^$rule/is","{$Html_Type[domain][$fid]}/",$filename);
		//�ر���һ��Щ�Զ�������ҳ�ļ��������.
		if(!eregi("^http:\/\/",$filename)){
			$filename="$webdb[www_url]/$filename";
		}
		return "$linkcode$filename";
	}else{
		//ʹ�þ��Ե�ַ,�����ڶ���Ŀ¼����URL����
		if(!$P||$P=='./'){
			if($webdb[www_url]=='/.'){
				$P="/";
			}else{
				$P="$webdb[www_url]/";
			}			
		}
		return "$linkcode$P$filename";		
	}
}

/**
*�澲̬���ܺ���
**/
function make_html($content,$pagetype='N'){
	
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)bencandy\.php\?fid=([\d]+)&(aid|id)=([\d]+)&page=([\d]+)/eis","make_html_Function('\\3','\\5','\\6','\\2',' href=\\1')",$content);	//�з�ҳ������ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)bencandy\.php\?fid=([\d]+)&(id|aid)=([\d]+)/eis","make_html_Function('\\3','\\5','1','\\2',' href=\\1')",$content);	//�޷�ҳ������ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)list\.php\?fid=([\d]+)&page=([\d]+)/eis","make_html_Function('\\3','','\\4','\\2',' href=\\1')",$content);	//�з�ҳ���б�ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)list\.php\?fid=([\d]+)/eis","make_html_Function('\\3','','1','\\2',' href=\\1')",$content);	//�޷�ҳ���б�ҳ
	
	//ר�⾲̬
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)showsp\.php\?fid=([\d]+)&(id|aid)=([\d]+)/eis","make_sphtml_Function('\\3','\\5','1','\\2',' href=\\1')",$content);	//�޷�ҳ������ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)listsp\.php\?fid=([\d]+)&page=([\d]+)/eis","make_sphtml_Function('\\3','','\\4','\\2',' href=\\1')",$content);	//�з�ҳ���б�ҳ
	$content=preg_replace("/ href=('|\"|)([-_a-z0-9\.:\/]{0,}\/|)listsp\.php\?fid=([\d]+)/eis","make_sphtml_Function('\\3','','1','\\2',' href=\\1')",$content);	//�޷�ҳ���б�ҳ

	if($pagetype=='N'){
		return $content;
	}
	
	global $fid,$id,$fidDB,$webdb,$page,$rsdb,$Html_Type,$showHtml_Type;
	
	$content=str_replace("jsspecial.php?","jsspecial.php?gethtmlurl=1&",$content);
	$content=str_replace("jsarticle.php?","jsarticle.php?gethtmlurl=1&",$content);

	if($pagetype=='bencandy')
	{
		if($showHtml_Type[bencandy][$id]){
			$filename=$showHtml_Type[bencandy][$id];
		}elseif($fidDB[bencandy_html]){
			$filename=$fidDB[bencandy_html];
		}else{
			$filename=$webdb[bencandy_filename];
		}
		//��������ҳ����ҳ��$pageȥ��
		if($page==1){
			$filename=preg_replace("/(.*)(-{\\\$page}|_{\\\$page})(.*)/is","\\1\\3",$filename);
		}
		$dirid=floor($id/1000);
		//��������ҳ����ĿС��1000ƪ����ʱ,��DIR��Ŀ¼ȥ��
		if($dirid==0){
			$filename=preg_replace("/(.*)(-{\\\$dirid}|_{\\\$dirid})(.*)/is","\\1\\3",$filename);
		}
		if(strstr($filename,'$time_')){
			$time_Y=date("Y",$rsdb[full_posttime]);
			$time_y=date("y",$rsdb[full_posttime]);
			$time_m=date("m",$rsdb[full_posttime]);
			$time_d=date("d",$rsdb[full_posttime]);
			$time_W=date("W",$rsdb[full_posttime]);
			$time_H=date("H",$rsdb[full_posttime]);
			$time_i=date("i",$rsdb[full_posttime]);
			$time_s=date("s",$rsdb[full_posttime]);
		}
		$content.="<div style='display:none;'><iframe width=0 height=0 src='$webdb[www_url]/do/job.php?job=updatehits&aid=$id'></iframe></div>";
	}
	elseif($pagetype=='list')
	{
		if($fidDB[list_html]){
			$filename=$fidDB[list_html];
		}else{
			$filename=$webdb[list_filename];
		}
		if($page==1){
			if($webdb[DefaultIndexHtml]==2){
				$filename=preg_replace("/(.*)\/([^\/]+)/is","\\1/index.shtml",$filename);
			}elseif($webdb[DefaultIndexHtml]==1){
				$filename=preg_replace("/(.*)\/([^\/]+)/is","\\1/index.html",$filename);
			}else{
				$filename=preg_replace("/(.*)\/([^\/]+)/is","\\1/index.htm",$filename);
			}
		}
	}
	elseif($pagetype=='listsp')
	{
		if($fidDB[list_html]){
			$filename=$fidDB[list_html];
		}else{
			$filename=$webdb[SPlist_filename];
		}
	}
	elseif($pagetype=='showsp')
	{
		if($showHtml_Type[SPbencandy][$id]){
			$filename=$showHtml_Type[SPbencandy][$id];
		}elseif($fidDB[bencandy_html]){
			$filename=$fidDB[bencandy_html];
		}else{
			$filename=$webdb[SPbencandy_filename];
		}
		if(strstr($filename,'$time_')){
			$time_Y=date("Y",$rsdb[full_posttime]);
			$time_y=date("y",$rsdb[full_posttime]);
			$time_m=date("m",$rsdb[full_posttime]);
			$time_d=date("d",$rsdb[full_posttime]);
			$time_W=date("W",$rsdb[full_posttime]);
			$time_H=date("H",$rsdb[full_posttime]);
			$time_i=date("i",$rsdb[full_posttime]);
			$time_s=date("s",$rsdb[full_posttime]);
		}		
		$content.="<SCRIPT LANGUAGE='JavaScript' src='$webdb[www_url]/do/job.php?job=update_special_hits&id=$id'></SCRIPT>";
	}
	eval("\$filename=\"$filename\";");
	$HtmlDir=dirname($filename);
	if(!is_dir(ROOT_PATH.$HtmlDir)){
		makepath(ROOT_PATH.$HtmlDir);
	}
	write_file(ROOT_PATH.$filename,$content);
	return $content;
}

function show_keyword($content){
	global $Key_word,$webdb,$pre,$db;
	if(!$webdb[ifShowKeyword]){
		return $content;
	}
	require_once(ROOT_PATH."data/keyword.php");
	//��ͼƬ����ȥ��
	//$content=preg_replace("/ alt=([^ >]+)/is","",$content);
	foreach( $Key_word AS $key=>$value){
		if(!$value){
			$value="$webdb[www_url]/do/search.php?type=title&keyword=".urlencode($key);
		}
		//$rs = $db->get_one("SELECT num FROM {$pre}keyword WHERE `keywords` = '{$key}'");
		//$content=str_replace_limit($key,"<a href=$value style=text-decoration:underline;font-size:14px;color:{$webdb[ShowKeywordColor]}; target=_blank>$key</a>",$content,$rs[num]);
		//$content=str_replace_limit($key,"<a href=$value style=text-decoration:underline;font-size:14px;color:{$webdb[ShowKeywordColor]}; target=_blank>$key</a>",$content,2);	//�ظ��Ĺؼ���ֻ����ʾ����

		$search[]=$key;
		$replace[]="<a href=$value style=text-decoration:underline;font-size:14px;color:{$webdb[ShowKeywordColor]}; target=_blank>$key</a>";
	}
	$search && $content=str_replace_limit($search,$replace,$content,2);
	return $content;
}
function str_replace_limit($search, $replace, $subject, $limit=-1) {
    if (is_array($search)) {
         foreach ($search as $k=>$v) {
             $search[$k] = "/(?!<[^>]+)".preg_quote($search[$k],'/')."(?![^<]*>)/";
        }
    }else{
         $search = "/(?!<[^>]+)".preg_quote($search,'/')."(?![^<]*>)/";
    }
    return preg_replace($search, $replace, $subject, $limit);
} 
//Ϊ��ҳ�������Զ����ҳ��
function Set_Title_PageNum($title,$page){
	$page<1 && $page=1;
	if($page<100){
		if($page==10){
			$page='0';
		}elseif($page>10&&$page%10!=0){
			if($page>20){
				$page=floor($page/10).'0'.($page%10);
			}else{
				$page='0'.($page%10);
			}
			
		}
		$array_ch=array("ʮ","һ","��","��","��","��","��","��","��","��");
		$array_ali=array("/0/","/1/","/2/","/3/","/4/","/5/","/6/","/7/","/8/","/9/");
		$page=preg_replace($array_ali,$array_ch,$page);
	}
	return "{$title}({$page})";
}
?>