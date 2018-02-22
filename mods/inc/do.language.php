<?php
if (!defined("NFK_LIVE")) die(); 

if (in_array($PARAMSTR[3],$lang_arr)) {
	setCookie("_nllang", $PARAMSTR[3] , 0, "/"); 
}
header("Location: /");

?>