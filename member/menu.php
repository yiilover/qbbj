<?php
!function_exists('html') && exit('ERR');

unset($base_menudb,$menudb);

$base_menudb['�鿴��������']['link']='homepage.php';
$base_menudb['�鿴��������']['power']='2';

$base_menudb['�޸ĸ�������']['link']='userinfo.php?job=edit';
$base_menudb['�޸ĸ�������']['power']='2';

$base_menudb['վ�ڶ���Ϣ����']['link']='pm.php?job=list';
$base_menudb['վ�ڶ���Ϣ����']['power']='2';

$base_menudb['�������']['link']='list.php';
$base_menudb['�������']['power']='2';

$base_menudb['���۹���']['link']='comment.php?job=work';
$base_menudb['���۹���']['power']='2';

$menudb['��������']['�޸ĸ�������']['link']='userinfo.php?job=edit';
$menudb['��������']['վ�ڶ���Ϣ']['link']='pm.php?job=list';
$menudb['��������']['���ֳ�ֵ']['link']='money.php?job=list';
$menudb['��������']['�����Ա�ȼ�']['link']='buygroup.php?job=list';
$ModuleDB['hy_'] && $menudb['��������']['��ҵ��Ϣ']['link']='company.php?job=edit';
$menudb['��������']['�����֤']['link']='yz.php?job=email';
$menudb['��������']['�������Ѽ�¼']['link']='moneylog.php?job=list';
$menudb['��������']['����ռ�']['link']='buyspace.php';
$menudb['��������']['��������']['link']='shoporder.php';

 

$menudb['CMS��������']['�ղؼй���']['link']='collection.php?job=myarticle';
$menudb['CMS��������']['ר�����']['link']='special.php?job=listsp';
$menudb['CMS��������']['���۹���']['link']='comment.php?job=list';



$menudb['CMSƵ��']['��������']['link']='post.php?job=postnew&only=1&mid=0';
$menudb['CMSƵ��']['��������']['link']='myarticle.php?job=myarticle&only=1&mid=0';

$menudb['CMSƵ��']['����ͼƬ']['link']='post.php?job=postnew&only=1&mid=100';
$menudb['CMSƵ��']['����ͼƬ']['link']='myarticle.php?job=myarticle&only=1&mid=100';

$menudb['CMSƵ��']['�������']['link']='post.php?job=postnew&only=1&mid=101';
$menudb['CMSƵ��']['�������']['link']='myarticle.php?job=myarticle&only=1&mid=101';

$menudb['CMSƵ��']['������Ƶ']['link']='post.php?job=postnew&only=1&mid=102';
$menudb['CMSƵ��']['������Ƶ']['link']='myarticle.php?job=myarticle&only=1&mid=102';

$menudb['CMSƵ��']['������Ʒ']['link']='post.php?job=postnew&only=1&mid=103';
$menudb['CMSƵ��']['������Ʒ']['link']='myarticle.php?job=myarticle&only=1&mid=103';

$menudb['CMSƵ��']['����FLASH']['link']='post.php?job=postnew&only=1&mid=104';
$menudb['CMSƵ��']['����FLASH']['link']='myarticle.php?job=myarticle&only=1&mid=104';


//��ȡ������ܵĲ˵�
$query = $db->query("SELECT * FROM {$pre}hack ORDER BY list DESC");
while($rs = $db->fetch_array($query)){
	if(is_file(ROOT_PATH."hack/$rs[keywords]/member_menu.php")){
		$array = include(ROOT_PATH."hack/$rs[keywords]/member_menu.php");
		$menudb['�������']["$array[name]"]['link']=$array['url'];
	}
}
?>