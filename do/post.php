<?php
require(dirname(__FILE__)."/"."global.php");
$listsorthtml = showsorts(0,0);
require(ROOT_PATH."inc/head.php");
require(html("post"));
require(ROOT_PATH."inc/foot.php");

function showsorts($fup,$step){
	global $db,$pre,$lfjuid,$webdb;
	$step++;
	if($step>1){
		for($i=0;$i<$step ;$i++ ){
			$icon.="--";
		}
	}
	$query = $db->query("SELECT * FROM `{$pre}sort` WHERE fup='$fup' ORDER BY list");
	while($rs = $db->fetch_array($query)){		
		if($rs['type']==1){
			$show .= "<tr class=\"tr1\">
            <td class=\"fid\">".$rs[fid]."</td>
            <td class=\"name\">".$icon.$rs['name']."</td>
            <td class=\"type\">�����</td>
            <td class=\"num\"><br/></td>
            <td class=\"post\"><br/></td>
          </tr>";
			//$show .= "<tr class=tr1>\n<td class=fid>".$rs[fid]."<td>\n<td class=name>".$rs['name']."</td>\n<td class=type>�����</td>\n<td class=num><br/></td>\n<td class=post><br/></td></tr>\n";
			$show .=showsorts($rs[fid],$step);
		}else{
			$_rs=$db->get_one("SELECT COUNT(*) AS NUM FROM {$pre}article WHERE fid='$rs[fid]' AND uid='$lfjuid'");
			$rs[NUM]=$_rs[NUM];
			$show .= "<tr class=\"tr2\">
            <td class=\"fid\">".$rs[fid]."</td>
            <td class=\"name\">".$icon.$rs['name']."</td>
            <td class=\"type\">��Ŀ</td>
            <td class=\"num\">".$rs[NUM]."</td>
            <td class=\"post\"><A HREF='$webdb[www_url]/member/post.php?job=postnew&fid=$rs[fid]'>����</A></td>
          </tr>";
		}		
	}	
	return $show;
}


?>