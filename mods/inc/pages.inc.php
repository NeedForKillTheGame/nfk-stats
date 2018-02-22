<?php
if ( $pages_count > 1 ) {	
	$pages = '<div align="center"><div style="width:450">';
	if ($pages_count > 20) {
		$bpage = $cur_page - 10; 
		if ($bpage<1) $bpage = 1;
		if ($bpage == 1) $badd = ""; else $badd = "<a href='$CUR_ADDRES"."page/1'>1</a> <b>...</b> ";
		$epage = $cur_page + 10; 
		if ($epage>$pages_count) $epage = $pages_count;
		if ($epage == $pages_count) $eadd = ""; else $eadd = "<b>...</b> <a href='$CUR_ADDRES"."page/$pages_count'>$pages_count</a>";
		$pages .= $badd;
		for ( $i = $bpage; $i <= $epage; ++$i ) {
			if ($cur_page == $i) { 
				$pages .= "<b>$i</b> ";
			} else $pages .= "<a href='$CUR_ADDRES"."page/$i'>$i</a> ";
		}
		$pages .= $eadd;
	} else {
		for ( $i = 1; $i <= $pages_count; ++$i ) {
			if ($cur_page == $i) { 
				$pages .= "<b>$i</b> ";
			} else $pages .= "<a href='$CUR_ADDRES"."page/$i'>$i</a> ";
		}
	}
	$pages .= '</div></div>';
}