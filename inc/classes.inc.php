<?php
if (!defined("NFK_LIVE")) die();

// Debug class
class watch {
	public $vars = Array();
	
	public function add($var) {
		array_push($this->vars,$var);
	}
	
	public function clear() {
		$this->vars = Array();
	}
	
	public function flush() {
		$vars = $this->vars;
		foreach ($vars as $var)
		{
			print $var."<br>";
		}
		$this->clear();
	}
	
	public function show() {
		foreach ($this->vars as $var) {
			print $var."<br>";
		}
	}
}

// Database class
/**
 * Class db
 */
class db {
    public $link = '';
    public $prefix = '';
    /* @var self */
    public static $inst;
		
	public function connect($db_host, $db_login, $db_pass, $db_name, $db_prefix) {
		
		$this->prefix = $db_prefix;
		if (!($this->link = mysqli_connect($db_host, $db_login, $db_pass, $db_name)))
			throw new Exception("No connection to database");
		
		//$this->link = mysql_connect($db_host, $db_login, $db_pass);
        mysqli_set_charset($this->link, "utf8");
		mysqli_query($this->link,"SET NAMES UTF8");
		//mysql_query("SET CHARACTER_SET_CLIENT UTF8");
		//mysql_query("SET CHARACTER_SET_CONNECTION UTF8");
		//mysql_query("SET CHARACTER_SET_RESULTS UTF8");
		//mysql_select_db($db_name, $this->link);
        self::$inst = $this;
		return $this->link;
	}
	
	public function close() {
		mysqli_close($this->link);
		unset($this->link);
	}

    public function clean($string) {
        return $this->_escape($string);
    }

 	public function query($a) {
		$result = mysqli_query( $this->link,"$a") or print( strip_tags( mysqli_error($this->link) ) );
		return $result;
	}
 	public function loadModels($class, $a) {
        $result = $this->query($a);
        $models = array();
        while ($obj = mysqli_fetch_object($result, $class)) {
            $models[] = $obj;
        }
        mysqli_free_result($result);
        return $models;
    }
	
	public function select($select,$from,$condition, $prefix = true, $keyField = null) {
		// clean input
		//$a = clean('str', $a);
		//$b = clean('str', $b);
		//$c = clean('str', $c);
		$result = Array();
		$select = ($select <> NULL) ? ("SELECT $select") : "" ;
		if ($prefix) {
			$from = ($from <> NULL) ? ("FROM ".$this->prefix."_$from") : "";
		} else {
			$from = ($from <> NULL) ? ("FROM $from") : "";
		};
		//echo ("<br><br> $a $b $c");
		$q = mysqli_query($this->link,"$select $from $condition") or print( strip_tags( mysqli_error($this->link) ) );
		if ($q)
			while ($row = mysqli_fetch_assoc($q)) {
                if ($keyField) {
                    $result[$row[$keyField]] = $row;
                } else array_push($result,$row);
			}
		unset($q);
		return $result;
	}
	
	public function select2($a,$b,$c) {
		// clean input
		//$a = clean('str', $a);
		//$b = clean('str', $b);
		//$c = clean('str', $c);
		$result = Array();
		$a = ($a <> NULL) ? ("SELECT $a") : "" ;
		$b = ($b <> NULL) ? ("FROM `$b`") : "" ;
		//echo ("<br><br> $a $b $c");
		
		$q = mysqli_query( $this->link,"$a $b $c") or print( strip_tags( mysqli_error($this->link) ) );
		
		if ($q != '')
			while ($row = mysqli_fetch_assoc($q)) {
				array_push($result,$row);
			}
		unset($q);
		return $result;
	}
	
	public function insert($table,$cell_array, $prefix = true, $cleanString = false) {
		// clean input
		if ($prefix) $table = $this->prefix."_".$table;
		//$cell_array = clean('arr', $cell_array);
		//$value_array = clean('arr', $value_array);
		foreach ($cell_array as $key => $value ) {
			$keys[] = $key;
            if ($cleanString) {
                $values[] = '"'.$this->_escape($value).'"';
            } else {
                $values[] = $value;
            }

		}
		$keys = implode(",",$keys);
		$values = implode(",",$values);
		//echo("<pre>insert into $table ($keys) values ($values)</pre>");
		
		$this->_log("insert into $table ($keys) values ($values);");
		
		$q = mysqli_query($this->link,"insert into $table ($keys) values ($values)");
		if ($q) { return mysqli_insert_id($this->link); } else { 
			print( strip_tags( mysqli_error($this->link) ) );
			return -1;
		};//
		// return false;
	}
	
	public function insert2($table,$cell_array,$addParam = '') {
		// clean input
		//$table = $this->prefix."_".$table;
		//$cell_array = clean('arr', $cell_array);
		//$value_array = clean('arr', $value_array);
		foreach ($cell_array as $key => $value ) {
			$keys[] = $key;
			$values[] = $value;
		}
		$keys = implode(",",$keys);
		$values = implode(",",$values);
		
		$this->_log("insert2 into $table ($keys) values ($values) $addParam");
		$q = mysqli_query($this->link,"insert into $table ($keys) values ($values) $addParam");
		//echo "insert into $table ($keys) values ($values) $addParam";
		if ($q) { return mysqli_insert_id($this->link); } else print( strip_tags( mysqli_error($this->link) ) );
		// return false;
	}

	public function update($table, $cell_array, $condition, $prefix = true) {
		//$items = Array();
		// clean input
		//$table = $this->prefix."_".$table;
		if ($prefix) $table = $this->prefix."_".$table;

		/*
		foreach ($cell_array as $key => $value ) {
			$items[] = "$key = $value";
		}
		$items = implode(",",$items);*/
		//echo ("update $table SET $cell_array $condition");
		$this->_log("update $table SET $cell_array $condition");
		
		$q = mysqli_query($this->link,"update $table SET $cell_array $condition") or print( strip_tags( mysqli_error($this->link) ) );
		if ($q) { return true; } else return false;
	}
	
	public function update2($table, $cell_array, $condition) {
		//$items = Array();
		// clean input
		//$table = $this->prefix."_".$table;
		/*
		foreach ($cell_array as $key => $value ) {
			$items[] = "$key = $value";
		}
		$items = implode(",",$items);*/
		//echo ("update $table SET $cell_array $condition");
		$this->_log("update2 $table SET $cell_array $condition");
		
		$q = mysqli_query($this->link,"update $table SET $cell_array $condition") or print( strip_tags( mysqli_error($this->link) ) );
		if ($q) { return true; } else return false;
	}
	
	public function delete($table,$condition) {
		// clean input
		$table = $this->prefix."_".$table;
		//$cell_array = clean('arr', $cell_array);
		//$value_array = clean('arr', $value_array);
		$q = mysqli_query($this->link,"delete from $table WHERE $condition");
		if ($q) {return mysqli_affected_rows($this->link); } else print( strip_tags( mysqli_error($this->link) ) );
		
		// return false;
	}
	public function call($routine) {
		$result = Array();
		$q = mysqli_query($this->link,"CALL $routine") or print( strip_tags( mysqli_error($this->link) ) );
		if ($q)
			while ($row = mysqli_fetch_assoc($q)){
				array_push($result,$row);
			}
		if(mysqli_more_results($this->link)) //Catch 'OK'/'ERR'
			while(mysqli_next_result($this->link));
		unset($q);
		return $result;
	}
	
	// replacement of mysql_real_escape_string, cause it does not work on php7
	private function _escape($value)
	{
		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
		$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
		$str = str_replace($search, $replace, $value);
		$str = htmlentities($str);
		return $str;
	}
	
	
	private function _log($line)
	{
		if (LOG_QUERIES)
			file_put_contents(QUERY_LOG_FILE, date("[d.m.Y H:i:s] ") . $line . "\n", FILE_APPEND);
	}
}

// Templated class
class skin {
	private $MARKERS		= Array();
	private $TEMPLATE		= Array();
	private $excluded_tags	= Array ("else"," ");
    protected $_route;

    public function render($view, array $var_array) {
        if (!$this->_route) throw new Exception(sprintf('Route is not set', $view));
        if (strpos($view, '/') === false) $view = $this->_route . '/' . $view;
        if (!file_exists("views/$view.php")) throw new Exception(sprintf('View "%s.php" is not found', $view));
        extract($var_array);
        try {
            ob_start();
            include("views/$view.php");
            $content = ob_get_clean();
        } catch (Exception $e) {
            throw new Exception(sprintf('View "%s" has errors "%s"', $view, $e->getMessage()));
        }

        $this->assign_variables(array('CONTENT' => $content));
        return $content;
    }

    public function setRoute($route) {
        $this->_route = $route;
        $this->load_template('mod_nfkmap');
    }

	// Loading template and splitting to blocks
	public function load_template($template_name) {
		global $CONFIG_root,$CFG;
		$filename = $CONFIG_root.'themes/'.$CFG['theme'].'/'.$template_name.'.tmpl.html';
		$handle = fopen($filename, "rt");
		$buffer = fread($handle, filesize($filename));
		fclose($handle);
		
		
		$i = 1; 	// start search from the second symbol
		$block = Array();
		
		$x = 0; // detecting level
		$y = 0;
		
		// search for block markers and remember positions
		while (stripos($buffer, "<!-- GTW: ",$i) !== false ) {
			$a = stripos($buffer, "<!-- GTW: ",$i);
			$b = stripos($buffer, " -->", $a);
			
			// extract block name
			$block_name = substr($buffer,$a+10,$b-$a-10);
			$block_name = trim($block_name); // to be sure
			
			$excluded_tag = false;
			foreach ($this->excluded_tags as $tag) {
				if ($block_name == $tag) {
					$excluded_tag = true;
					break;
				}
			}
					
			if (!$excluded_tag) {				
				// insert block's marker begin/end
				$block[ $block_name ]['index'] = Array ($a-1,$b+4); // marker begins, marker ends			
				$block_indx[ $block_name ] = Array ($a-1,$b+4); // marker begins, marker ends
	
				// detect end of block
				if ($block_name[0] == "/") {
					// sub block are ending here
					$parent = $block[$parent]['parent'];
				} else {	
					// There are could be multiply includings, so we need an array
					// sub block are starting here						
					$block[ $parent ]['sub'][] = $block_name;
					$block[ $block_name ]['parent'] = $parent;
					$parent = $block_name;
				}
			}
			$i = $a+1; // to search next marker
		}
		// cut to blocks
		$block_name = "";
		foreach ($block_indx as $block_name => $index) {
			$block_name = strtolower($block_name);
			
			if ($block_name[0] != "/") {
				$block_begin	= $index[1];
				$block_end		= $block[ "/".$block_name ]['index'][1];				
				// Cut includings						
				if ($block[$block_name]['sub'][0] != "") {						
					// There are could be multiply includings
					$exsub = ""; // there were no subs yet
					foreach ($block[$block_name][sub] as $sub) {					
						$sub_begin 	= $block[ $sub ]['index'][0];
						$sub_end 	= $block[ "/".$sub ]['index'][1];
						if ($exsub == '') {
							// first sub / block_begin to sub_begin
							$buffer2 = substr($buffer,$block_begin,$sub_begin-$block_begin);
						} else {
							// never goes here o_O but it's working
							// exsub_end to sub_begin
							$buffer2 .= substr($buffer,$exsub_end,$sub_begin-$exsub_end);	
						}
						// we need to remember previous subblock					
						$exsub 			= $sub;
						$exsub_begin 	= $sub_begin;
						$exsub_end 		= $sub_end;
					}
					$buffer2 .= substr($buffer, $sub_end, $block_end - $sub_end - strlen("<!-- GTW: /".$block_name." -->"));
				} else {
					// Get whole block with possible includings
					// Block has no includings
					$buffer2 = substr($buffer,$index[1], $block_end - $block_begin - strlen("<!-- GTW: /".$block_name." -->"));
				}
			}
			// finalize block
			$skin_blocks[$block_name] = $buffer2;
		}
		// validate template, 'main' block must exist
		if ($skin_blocks['main'] == '') return false;
		$this->TEMPLATE = $skin_blocks;
		// should we clean $VARIABLES and $MARKERS now?
		$this->MARKERS = '';
	}
	
	// Assign variables
	public function assign_variables($input) {
		$input += Array("NULL" => NULL, "EMPTY" => NULL);
		$this->MARKERS = $input;
	}
	
	// Replace template markers with values
	public function build($block) {
		// if block exists
		if ($this->TEMPLATE[$block] == '') return false;
		// logical if
		if (stripos(" ".$block,'if_') == 1) {
			$logical = explode('<!-- GTW: else -->',$this->TEMPLATE[$block]);
			$TEMPLATE_formed = ($this->MARKERS['GTW_LOGIC'] == true) ? ($logical[0]) : ($logical[1]);
		} else $TEMPLATE_formed = $this->TEMPLATE[$block]; 
		// markers workout
		if (count($this->MARKERS) > 1) {			
			foreach ($this->MARKERS as $marker => $marker_value) {
				$TEMPLATE_formed = str_replace("{".$marker."}",$marker_value,$TEMPLATE_formed);
			}
		} else if (count($this->MARKERS) == 1) {
			// !!! still not working
			// one marker is not suitable for foreach cycle
			/*$marker = array_keys($this->MARKERS);
			$marker_value = $this->MARKERS[$marker];
			$TEMPLATE_formed = str_replace("{".$marker."}",$marker_value,$TEMPLATE_formed);*/
		}
		else return false;
		return $TEMPLATE_formed;
	}	
}

// User class
class user {
	public $data = Array();
	
	// find user by id
	public function fetchId($user_id) {
		global $db;
		
		$user_id = clean('int',$user_id);
		
		$table_users = $db->prefix . "_users";
		$q = mysqli_query($db->link,"select * from `$table_users` where `id` = $user_id");
		return mysqli_fetch_assoc($q);
	}
	
	// find user by name
	public function fetchName($user_name) {
		global $db;
		$user_name = $db->clean($user_name);
		$user_id = clean('str',$user_name);
		$table_users = $db->prefix . "_users";
		$q = mysqli_query($db->link,"select * from `$table_users` where `login` = '$user_name'");
		return mysqli_fetch_assoc($q);
	}
	
	// check user permission
	public function can($rule) {
		
	}
	
	// catch userdata from array
	public function assign($userdata) {
		$this->data = $userdata;
	}
	
	public function xdata() {
		return $this->data;
	}
}

// player class
class player
{
	public $pdata = Array();
	
	// find player by id
	public function fetchId($player_id) {
		global $db;
		if (!is_numeric($player_id)) $player_id=0;
		$table_players = $db->prefix . "_playerStats";
		$q = mysqli_query($db->link,"select * from `$table_players` where `playerID` = '$player_id'");
		return mysqli_fetch_assoc($q);
	}
	
	// find player by name
	public function fetchName($player_name) {
		global $db;
		$player_name = $db->clean($player_name);
		//$table_players = $db->prefix . "_";
		$res = $db->select('*','playerStats',"WHERE name = '$player_name'");
		//$q = mysqli_query($db->link,"select * from `$table_players` where `name` = '$player_name'");
		return $res[0];
	}
	
	
	// catch playerdata from array
	public function assign($playerdata) {
		$this->pdata = $playerdata;
	}
	
	public function pxdata() {
		return $this->pdata;
	}
}

// Session

// Dictionary
class dictionary {
	public $data = Array();
	// load language
	public function pick_language($lang) {
		$this->data = Array();
		// here we need dictionary loading
		include("./langs/$lang/dictionary.inc.php");
	}
}