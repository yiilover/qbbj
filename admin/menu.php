<?php
!function_exists('html') && exit('ERR');
unset($menu_partDB,$menudb,$menu_partDB);
$base_menuName=array('base'=>'ϵͳ����','article'=>'���¹���','member'=>'��Ա����','module'=>'ģ������','other'=>'�������');

$menu_partDB = array(
	'base'=>array('��������','��վ���ù��ܹ���','���ݿ⹤��','�˵�����'),
	'article'=>array('����/��Ŀ/���۹���','��̬ҳ���ɹ���','���±�ǩ����','ר�����','����Ŀ����'),
	'member'=>array('�û�����','Ȩ�޹���'),
);

$menudb=array(
	'��������'=>array(
		'ȫ�ֲ�������' => array('power'=>'center_config','link'=>'index.php?lfj=center&job=config'),
		'��ҳ����' => array('power'=>'set_comsort_index','link'=>'index.php?lfj=channel&job=edit&id=1'),
		'��Աע������' => array('power'=>'user_reg','link'=>'index.php?lfj=center&job=user_reg'),
		'��������' => array('power'=>'center_cache','link'=>'index.php?lfj=center&job=cache'),
		'����ģ�ͻ�������' => array('power'=>'article_more_config','link'=>'index.php?lfj=article_more&job=config'),
		'���»���/�����޸�' => array('power'=>'cache_cache','link'=>'index.php?lfj=cache&job=cache'),
		'������������' => array('power'=>'comment_set','link'=>'index.php?lfj=comment&job=set'),
		'ȫ�־�̬ҳ����' => array('power'=>'setmakeALLhtml_set','link'=>'index.php?lfj=html&job=set'),
		'ϵͳģ�����' => array('power'=>'module_list','link'=>'index.php?lfj=module&job=list'),
		'ģ�͹���' => array('power'=>'article_module','link'=>'index.php?lfj=article_module&job=list'),		
		'�������' => array('power'=>'hack_list','link'=>'index.php?lfj=hack&job=list'),
		'�����ⲿϵͳ����' => array('power'=>'blend_set','link'=>'index.php?lfj=blend&job=set'),
	),
	'��վ���ù��ܹ���'=>array(
		'�������ӹ���' => array('power'=>'friendlink_mod','link'=>'index.php?lfj=friendlink&job=list'),
		'��ƪ���¶���ҳ�����' => array('power'=>'alonepage_list','link'=>'index.php?lfj=alonepage&job=list'),
		'Ƶ������ҳ����' => array('power'=>'channel_list','link'=>'index.php?lfj=channel&job=list'),
	),
	'���ݿ⹤��'=>array(
		'�������ݿ�' => array('power'=>'mysql_out','link'=>'index.php?lfj=mysql&job=out'),
		'���ݿ⻹ԭ' => array('power'=>'mysql_into','link'=>'index.php?lfj=mysql&job=into'),
		'ɾ����������' => array('power'=>'mysql_del','link'=>'index.php?lfj=mysql&job=del'),
		'���ݿ⹤��' => array('power'=>'mysql_sql','link'=>'index.php?lfj=mysql&job=sql'),
	),
	'�˵�����'=>array(
		'��վͷ�������˵�����' => array('power'=>'menu_list','link'=>'index.php?lfj=guidemenu&job=list'),
		'����Ա��̨�˵�����' => array('power'=>'adminmenu_list','link'=>'index.php?lfj=adminguidemenu&job=list'),
		'��Ա���Ĳ˵�����' => array('power'=>'membermenu_list','link'=>'index.php?lfj=memberguidemenu&job=list'),
	),
	'����/��Ŀ/���۹���'=>array(
		'</a><A HREF=\'index.php?lfj=sort&job=listsort&only=&mid=\' target=main>��Ŀ����</A> | <A HREF=\'index.php?lfj=sort&job=addsort&only=&mid=\' target=main>������Ŀ</A> <a>' => array('power'=>'sort_listsort','link'=>' '),
		'���ݹ���<font color=#959595>(�޸ġ�ɾ����)</font>' => array('power'=>'artic_listartic','link'=>'index.php?lfj=artic&job=listartic&only=1&mid='),
		'���۹���' => array('power'=>'comment_list','link'=>'index.php?lfj=comment&job=list'),
		'����<font color=#959595>(���¡�ͼƬ��)</font>' => array('power'=>'artic_postnew','link'=>'index.php?lfj=post&job=postnew&only=1&mid='),
		'���ٷ�ͼ' => array('power'=>'artic_addpic','link'=>'index.php?lfj=artic&job=addpic'),
	),
	'��̬ҳ���ɹ���'=>array(
		'</a><a href=\'../do/index.php?&ch=1&MakeIndex=1\' target=\'_blank\' onclick=\"return confirm(\'��ȷʵҪ����ҳ���ɾ�̬��?���ɾ�̬��,���и��������������,��Ҫ���µ������һ�ξ�̬.�ſ��Կ���Ч��.\')\");\">��ҳ��̬</a> | <A HREF=\'index.php?lfj=center&job=delindex\' target=main>ɾ��</A><a>' => array('power'=>'makeindexhtml_make','link'=>' '),
		'��Ŀ���ݾ�̬ҳ����' => array('power'=>'makehtml_make','link'=>'index.php?lfj=html&job=list'),
		'ר�⾲̬ҳ����' => array('power'=>'spmakehtml_make','link'=>'index.php?lfj=html&job=listsp'),
		'��̬��ҳ��ʽ����' => array('power'=>'setmakehtml_set','link'=>'index.php?lfj=html&job=set'),
	),
	'���±�ǩ����'=>array(
		'��ҳ��ǩ����' => array('power'=>'index_label','link'=>'../do/index.php?&ch=1&chtype=0&jobs=show'),
		'��Ŀ/����ҳ��ǩ����' => array('power'=>'s_list_fid','link'=>'index.php?lfj=channel&job=list_fid'),
		'ר��ҳ��ǩ����' => array('power'=>'up_splist_fid','link'=>'index.php?lfj=special&job=list&onlyshow=label'),
	),
	'ר�����'=>array(
		'ר�����' => array('power'=>'special_list','link'=>'index.php?lfj=special&job=list'),
		'ר��������' => array('power'=>'spsort_listsort','link'=>'index.php?lfj=spsort&job=listsort'),
	),
	'����Ŀ����'=>array(
		'����Ŀ����' => array('power'=>'fu_sort_power','link'=>'index.php?lfj=fu_sort&job=listsort'),
		'����Ŀ���ݹ���' => array('power'=>'fu_artic_power','link'=>'index.php?lfj=fu_artic&job=listartic'),
	),
	'�û�����'=>array(
		'�û����Ϲ���' => array('power'=>'member_list','link'=>'index.php?lfj=member&job=list'),
		'��ҵ���Ϲ���' => array('power'=>'company_list','link'=>'index.php?lfj=company&job=list'),
		'�û������ֶι���' => array('power'=>'regfield','link'=>'index.php?lfj=regfield&job=editsort'),
		'������û�' => array('power'=>'member_addmember','link'=>'index.php?lfj=member&job=addmember'),
	),
	'Ȩ�޹���'=>array(
		'����ģ��Ȩ������' => array('power'=>'article_group_config','link'=>'index.php?lfj=article_group&job=list'),
		'ǰ̨Ȩ�޹���' => array('power'=>'group_list','link'=>'index.php?lfj=group&job=list'),
		'��̨Ȩ�޹���' => array('power'=>'group_list_admin','link'=>'index.php?lfj=group&job=list_admin'),
		'����û���' => array('power'=>'group_add','link'=>'index.php?lfj=group&job=add'),
	),

);

if(!$ModuleDB['hy_']){	//ûװ��ҳ����û����ҵ����
	unset($menudb['�û�����']['��ҵ���Ϲ���']);
}

@include(ROOT_PATH."data/hack.php");

if($ForceEnter||$GLOBALS[ForceEnter]){

	//ǿ�ƽ���̨
	foreach( $menu_partDB AS $key1=>$value1){
		if($key1=='base'){
			continue;
		}
		foreach( $value1 AS $key2=>$value2){
			$menu_partDB['base'][]=$value2;
		}
	}
}else{

	if(!table_field("{$pre}module",'ifsys')){
		$db->query("ALTER TABLE `{$pre}module` ADD `ifsys` TINYINT( 1 ) NOT NULL");
	}
	//ģ��
	$query = $db->query("SELECT * FROM {$pre}module WHERE type=2 AND ifclose=0 ORDER BY list DESC");
	while($rs = $db->fetch_array($query)){
		if(!$rs['dirname']){
			continue;
		}
		if($rs['ifsys']){	//�����Ķ����˵�
			$base_menuName[$rs['pre']]=$rs['name'];
			$menu_partDB[$rs['pre']][]=$rs['name'];
		}else{
			$menu_partDB['module'][]=$rs['name'];
		}		
		$menudb[$rs['name']]=@include(ROOT_PATH."$rs[dirname]/admin/menu.php");
		foreach($menudb[$rs['name']] AS $key=>$value){
			if(eregi('^file=',$menudb[$rs['name']][$key]['link'])){
				$menudb[$rs['name']][$key]['link']="index.php?lfj=module_admin&dirname=$rs[dirname]&".$menudb[$rs['name']][$key]['link'];

				if($menudb[$rs['name']][$key]['power']!=1){
					$menudb[$rs['name']][$key]['power']="Module_".$rs[pre].$menudb[$rs['name']][$key]['power'];					
				}
			}
			if($rs['ifsys']&&$value['sort']){
				$keyname=get_word($rs['name'],4,0).">{$value['sort']}";
				$menu_partDB[$rs['pre']][$keyname]=$keyname;
				$menudb[$keyname][$key]=$menudb[$rs['name']][$key];
				unset($menudb[$rs['name']][$key]);
				
			}
		}
	}
}


?>