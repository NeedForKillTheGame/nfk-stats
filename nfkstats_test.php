<?php

$file = 'temp.txt';
if ($_GET['read']=='1') {
	echo '<pre>';
	echo file_get_contents($file);
	die ('</pre>');
} else {
	//$content = 'test '.stripslashes($_POST['jsonMatchStats'])."\n";
	$content =  print_r(json_decode(stripslashes($_POST['jsonMatchStats']),true),true);
	
	$content .=  print_r($_FILES,true);
	
	$uploaddir = 'temp/';
	$uploadfile = $uploaddir . basename($_FILES['demofile']['name']);
	if (move_uploaded_file($_FILES['demofile']['tmp_name'], $uploadfile)) {
		$content .=  "\n���� ��������� � ��� ������� ��������.\n";
	} else {
		$content .=  "��������� ����� � ������� �������� ��������!\n";
	}
	file_put_contents($file,$content);
}
