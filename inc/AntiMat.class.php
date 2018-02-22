<?
//setlocale (LC_ALL, "ru_RU.UTF-8");
class AntiMat {
     //latin equivalents for russian letters
     var $let_matches = array (
     "a" => "а",
     "c" => "с",
     "e" => "е",
     "k" => "к",
     "m" => "м",
     "o" => "о",
     "x" => "х",
     "y" => "у",
     "Є" => "е"
                              );
     //bad words array. Regexp's symbols are readable !
     var $bad_words = array (".*ху(й|и|€|е|л(и|е)).*", ".*пи(з|с)д.*", "бл€.*", ".*бл€(д|т|ц).*", "(с|сц)ук(а|о|и).*", "еб.*", ".*уеб.*", "заеб.*", ".*еб(а|и)(н|с|щ|ц).*", ".*ебу(ч|щ).*", ".*пид(о|е)р.*", "г(а|о)ндон", ".*залуп.*");

	function rand_replace (){
		 $output = " <font color=red>[beep]</font> ";
		 return $output;
	}
	function filter ($string){
		$string = iconv('utf-8','cp1251',$string);
		 $counter = 0;
		 $elems = explode (" ", $string); //here we explode string to words
		 $count_elems = count($elems);
		 for ($i=0; $i<$count_elems; $i++)
		 {
		 $blocked = 0;
		 /*formating word...*/
		 $str_rep = eregi_replace ("[^a-zA-Zа-€ј-яЄ]", "", strtolower($elems[$i]));
			 for ($j=0; $j<strlen($str_rep); $j++)
			 {
				 foreach ($this->let_matches as $key => $value)
				 {
					 if ($str_rep[$j] == $key)
					 $str_rep[$j] = $value;

				 }
			 }
		 /*done*/

		 /*here we are trying to find bad word*/
		 /*match in the special array*/
			 for ($k=0; $k<count($this->bad_words); $k++)
			 {
				 if (ereg("\*$", $this->bad_words[$k]))
				 {
					 if (ereg("^".$this->bad_words[$k], $str_rep))
					 {
					 $elems[$i] = $this->rand_replace();
					 $blocked = 1;
					 $counter++;
					 break;
					 }
				 
				 }
				 if ($str_rep == $this->bad_words[$k]){
				 $elems[$i] = $this->rand_replace();
				 $blocked = 1;
				 $counter++;
				 break;
				 }

			 }
		 }
		 if ($counter != 0)
		 $string = implode (" ", $elems); //here we implode words in the whole string
	return iconv('cp1251','utf-8',$string);
	}
}
?>