<?php
require_once(dirname(__FILE__)."/".'./global.php');
get_guide($fid);	//��Ŀ����
$forum_ups=$GuideFid[$fid];
$forum_ups=str_replace("list.php?","$webdb[www_url]$webdb[path]/list.php?",$forum_ups);
require_once(html("foot_nav"));
?>