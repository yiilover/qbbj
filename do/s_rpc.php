<?php
require_once(dirname(__FILE__)."/"."global.php");

header('Content-Type: text/html; charset=gb2312');
require_once(ROOT_PATH."inc/class.chinese.php");
$cnvert = new Chinese("UTF8","GB2312",$_POST['queryString'],ROOT_PATH."./inc/gbkcode/");
$_POST['queryString'] = $cnvert->ConvertIT();
$queryString = $_POST['queryString']; 
  if(strlen($queryString) >0) {		
	  $query = $db->query("SELECT title FROM {$pre}article WHERE title LIKE '$queryString%' ORDER BY hits DESC LIMIT 10");
		  while ($result = $db->fetch_array($query)) {
		
			  echo '<li onClick="fill(\''.$result[title].'\');">'.$result[title].'</li>';
		  }
  }

?>