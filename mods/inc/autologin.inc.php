<?php
// ���� ������������ �� �����������
if ($_SESSION['me_data'] == "") {
    // �� ��������� ��� ����
    // ����� ��� ���� ����� � ������ � ������ �������
    if (($_COOKIE['_nll_data']<>'') && ($_COOKIE['_nlp_data']<>'')) {
        // ���� �� ����� �������
        // �� ������� ������������ ������������ �� ���� ������ � ������
		$temp_user['login'] = mysqli_real_escape_string($db->link,$_COOKIE['_nll_data']);
		$temp_user['password'] = mysqli_real_escape_string($db->link,$_COOKIE['_nlp_data']);
        // � �� �������� � ������������ ����� �����:
        // ������ ������ � ��
        // � ���� ����� � ����� ������� � �������
		if ($this_user = $user->fetchName($temp_user["login"])) {
			if ($this_user["password"] == $temp_user["password"]) {

				$me->assign($this_user);
				$_SESSION['me_data'] = $me->data;
				
				$_SESSION['me_data']['psid'] = newSID();
				$xdata = $_SESSION['me_data'];
				/*$db->insert('sessions', Array(
					'sessionIP'	=> "'".$_SERVER['REMOTE_ADDR']."'",
					'playerID'	=> $_SESSION['me_data']['id'],
					'ttl'		=> "'".date("Y-m-d H:i:s",strtotime('+30 seconds'))."'",
					'psid'		=> "'".$_SESSION['me_data']['psid']."'",
				));*/
				setCookie("_nll_data", $temp_user['login'] , time()+(60*60*24*7), "/"); 
				setCookie("_nlp_data", $temp_user['password'], time()+(60*60*24*7), "/"); 
				$db->update('users',"loginDate = NOW(), loginIP = '$_SERVER[REMOTE_ADDR]'","WHERE `id` = $xdata[id] LIMIT 1");
			}
		}
		// ���� ����� ������������ �������
		// �� �� ������ �� ���� ����� � ������ (�������� �� ����� ������� ID ������������)
		// �� ��������, ��� ��� ������ � ����������� �������, � ��� � ������ ������� ������ �������������� session_start();
		// ������ �� �� ����� ����� ������ �� ����� �����������
		// ����� ������� � �� ����� ��� �������������
		// � ������ ������ ��������� �� ���� �������� ��� �����
    }
}