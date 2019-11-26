<?php 
	
// UPDATED RATING CALCULATING BY KAIN 10.05.2019	
	
	//$resulturns elo changing ("+" for win team and "-" for lose team)
	function formulaElo($winElo, $loseElo, $isDraw)
	{
		$coefK = 20; //const
		$coefD = 200; //const
		$degree = ($loseElo-$winElo)/$coefD;
		$Ea = 1/(1+pow(10,$degree));
		$Sa = 1;
		if($isDraw) $Sa = 0.5;
		return round($coefK*($Sa-$Ea));
	}
	
	//rough elo change correct, depends on current elo
	function correctRatingChange($currentElo, $changeElo)
	{
		$result = 0;
		if($currentElo<=100)
		{
			if($changeElo<0) $result=$changeElo/2; else $result=$changeElo*2;
		}
		else if($currentElo<=200)
		{
			if($changeElo<0) $result=$changeElo/1.6; else $result=$changeElo*1.6;
		}
		else if($currentElo<=300)
		{
			if($changeElo<0) $result=$changeElo/1.4; else $result=$changeElo*1.4;
		}
		else if($currentElo<=400)
		{
			if($changeElo<0) $result=$changeElo/1.2; else $result=$changeElo*1.2;
		}
		else if($currentElo<=500)
		{
			if($changeElo<0) $result=$changeElo/1; else $result=$changeElo*1;
		}
		else
		{
			if($changeElo<0) $result=$changeElo/0.7; else $result=$changeElo*0.7;
		}
    if($result+$currentElo<0) $result = (-1)*$currentElo;
    return round($result);
	}
	
	$mID = $_GET['mid'];
	if (($mID == '') or ($mID == '0')) Die('ERROR'); 
	$Table[2][1]=90;$Table[2][2]=10;
	$Table[3][1]=65;$Table[3][2]=35;$Table[3][3]=0;
	$Table[4][1]=55;$Table[4][2]=30;$Table[4][3]=15;$Table[4][4]=0;
	$Table[5][1]=46;$Table[5][2]=27;$Table[5][3]=19;$Table[5][4]=8; $Table[5][5]=0;
	$Table[6][1]=41;$Table[6][2]=26;$Table[6][3]=15;$Table[6][4]=13;$Table[6][5]=5;$Table[6][6]=0;
	$Table[7][1]=40;$Table[7][2]=26;$Table[7][3]=15;$Table[7][4]=10;$Table[7][5]=7;$Table[7][6]=2;$Table[7][7]=0;
	$Table[8][1]=38;$Table[8][2]=23;$Table[8][3]=15;$Table[8][4]=10;$Table[8][5]=7;$Table[8][6]=5;$Table[8][7]=2;$Table[8][8]=0;
	$Result = mysqli_query($db->link,"SELECT * FROM nfkLive_matchList WHERE matchID=$mID") or die("Error ".mysqli_error($db->link));
	$Data = mysqli_fetch_assoc($Result);
	$Game = Array();
	If($Data['gameType'] == 'DM')
	{
	  $GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
	  for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
	  $Num[0]['DM']++;
	  mysqli_query($db->link,"UPDATE AltStat_NumGame SET DM='{$Num[0]['DM']}'") or die("Error".mysqli_error($db->link));
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);
	  $SumReiting2 = 0;
	  for($j=0;$j<$GameNumRows;$j++)
		{
		  $Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
		  $Game[$j+1]['frags'] = $GameData[$j]['frags'];
		  $Game[$j+1]['deaths'] = $GameData[$j]['deaths'];
		  $Game[$j+1]['time'] = ($GameData[$j]['time']-1)/60; 
		  $Game[$j+1]['timeCoef'] = ($Game[$j+1]['time'])/$GameTime;
		  $Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
		  $IsPlayer = mysqli_num_rows($Player);
		  If($IsPlayer == 0 )
			{
			  $Game[$j+1]['DmWin'] = 0;
			  $Game[$j+1]['DmReiting'] = 100;
			  $Game[$j+1]['DmGame'] = 0;
			  mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
			  CtfReiting=100, 
			  TdmReiting=100, 
			  DmReiting=100, 
			  DuelReiting=100, 
			  DomReiting=100, 
			  RailReiting=100,
			  PracReiting=100,
			  CtfGame=0, 
			  TdmGame=0,  
			  DmGame=0,  
			  DuelGame=0,  
			  DomGame=0, 
			  RailGame=0,
			  PracGame=0,
			  CtfWin=0, 
			  TdmWin=0,  
			  DmWin=0,  
			  DuelWin=0,  
			  DomWin=0, 
			  RailWin=0,
			  PracWin=0") or die("Error".mysqli_error($db->link));
			}
		   else
			{
			  for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
			  $Game[$j+1]['DmWin'] = $PlayerData[0]['DmWin'];
			  $Game[$j+1]['DmReiting'] = $PlayerData[0]['DmReiting'];
			  $Game[$j+1]['DmGame'] = $PlayerData[0]['DmGame'];
			}
		  $SumReiting2 = $SumReiting2 + (sqrt($Game[$j+1]['DmReiting']) + 5)*(sqrt($Game[$j+1]['DmReiting']) + 5);  
		}
	  $AddAll = 8+50*exp(1-9/($GameNumRows+1));
	  $AddWin = ($GameNumRows+1)*($GameNumRows+1)/($GameNumRows+5);
	  for($j=$GameNumRows;$j>1;$j--)
		{
		  for($k=1;$k<$j;$k++)
			{
			  If($Game[$k]['frags']<$Game[$k+1]['frags'])
				{
				  $tmp = $Game[$k];
				  $Game[$k] = $Game[$k+1];
				  $Game[$k+1] = $tmp;
				}
			}
		}
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['DmGame']++;
		  $ChangeReiting = $AddAll*$Table[$GameNumRows][$j]/100-$AddAll*(sqrt($Game[$j]['DmReiting'])+5)*(sqrt($Game[$j]['DmReiting'])+5)/$SumReiting2*$Game[$j]['timeCoef'];
		  If($j==1)
			{
			  $Game[$j]['DmWin']++;
			}
		  If($j<=floor($GameNumRows*0.5))
			{
			  $ChangeReiting = $ChangeReiting + $AddWin/floor($GameNumRows*0.5);
			}					
		  If($ChangeReiting<0)
			{
			  $ChangeReiting=$ChangeReiting*(1-exp((100-$Game[$j]['DmReiting'])/100));
			}
		  else
			{
			  $ChangeReiting=$ChangeReiting*(1-exp(($Game[$j]['DmReiting']-1500)/180));
			}
		  $ChangeReiting = floor($ChangeReiting);	
		  mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['DmReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
		  $Game[$j]['DmReiting']=$Game[$j]['DmReiting']+$ChangeReiting; 	
		  mysqli_query($db->link,"UPDATE AltStat_Players SET DmReiting='{$Game[$j]['DmReiting']}', DmWin='{$Game[$j]['DmWin']}', DmGame='{$Game[$j]['DmGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
												//Inc clan score
			$plr_id = $Game[$j][playerID];
			$clanID = mysqli_fetch_array(mysqli_query($db->link,"SELECT clanID, playerID FROM nfkLive_playerStats WHERE playerID='$plr_id'"));
			$clanID = $clanID[clanID];
			if ($clanID <> 0) {
				mysqli_query($db->link,"UPDATE `nfkLive_clanList` SET score=score+$ChangeReiting WHERE clanID='$clanID'");
				mysqli_query($db->link,"UPDATE `nfkLive_playerStats` SET clanScore=clanScore+$ChangeReiting, clanGames=clanGames+1 WHERE playerID='$plr_id'");
			}
		}	
	}



	If($Data['gameType'] == 'PRAC')
	{
	  $GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
	  for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
	  $Num[0]['PRAC']++;
	  mysqli_query($db->link,"UPDATE AltStat_NumGame SET PRAC='{$Num[0]['PRAC']}'") or die("Error".mysqli_error($db->link));
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);		 
	  $SumReiting2 = 0;
	  for($j=0;$j<$GameNumRows;$j++)
		{
		  $Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
		  $Game[$j+1]['frags'] = $GameData[$j]['frags'];
		  $Game[$j+1]['deaths'] = $GameData[$j]['deaths'];
		  $Game[$j+1]['time'] = ($GameData[$j]['time']-1)/60; 
		  $Game[$j+1]['timeCoef'] = ($Game[$j+1]['time'])/$GameTime;
		  $Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
		  $IsPlayer = mysqli_num_rows($Player);
		  If($IsPlayer == 0 )
			{
			  $Game[$j+1]['PracWin'] = 0;
			  $Game[$j+1]['PracReiting'] = 100;
			  $Game[$j+1]['PracGame'] = 0;
			  mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
			  CtfReiting=100, 
			  TdmReiting=100, 
			  DmReiting=100, 
			  DuelReiting=100, 
			  DomReiting=100, 
			  RailReiting=100,
			  PracReiting=100,
			  CtfGame=0, 
			  TdmGame=0,  
			  DmGame=0,  
			  DuelGame=0,  
			  DomGame=0, 
			  RailGame=0,
			  PracGame=0,
			  CtfWin=0, 
			  TdmWin=0,  
			  DmWin=0,  
			  DuelWin=0,  
			  DomWin=0, 
			  RailWin=0,
			  PracWin=0") or die("Error".mysqli_error($db->link));
			}
		   else
			{
			  for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
			  $Game[$j+1]['PracWin'] = $PlayerData[0]['PracWin'];
			  $Game[$j+1]['PracReiting'] = $PlayerData[0]['PracReiting'];
			  $Game[$j+1]['PracGame'] = $PlayerData[0]['PracGame'];
			}
		  $SumReiting2 = $SumReiting2 + ($Game[$j+1]['PracReiting'] + 100)*($Game[$j+1]['PracReiting'] + 100);  
		}
	  $AddAll = 8+50*exp(1-9/($GameNumRows+1));
	  $AddWin = ($GameNumRows+1)*($GameNumRows+1)/($GameNumRows+5);
	  for($j=$GameNumRows;$j>1;$j--)
		{
		  for($k=1;$k<$j;$k++)
			{
			  If($Game[$k]['frags']<$Game[$k+1]['frags'])
				{
				  $tmp = $Game[$k];
				  $Game[$k] = $Game[$k+1];
				  $Game[$k+1] = $tmp;
				}
			}
		}
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['PracGame']++;
		  $ChangeReiting = $AddAll*$Table[$GameNumRows][$j]/100-$AddAll*($Game[$j]['PracReiting']+100)*($Game[$j]['PracReiting']+100)/$SumReiting2*$Game[$j]['timeCoef'];
		  If($j==1)
			{
			  $Game[$j]['PracWin']++;
			}
		  If($j<=floor($GameNumRows*0.5))
			{
			  $ChangeReiting = $ChangeReiting + $AddWin/floor($GameNumRows*0.5);
			}					
		  If($ChangeReiting<0)
			{
			  $ChangeReiting=$ChangeReiting*(1-exp((100-$Game[$j]['PracReiting'])/100));
			}
		  else
			{
			  $ChangeReiting=$ChangeReiting*(1-exp(($Game[$j]['PracReiting']-1500)/180));
			}
		  $ChangeReiting = floor($ChangeReiting);	
		  mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['PracReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
		  $Game[$j]['PracReiting']=$Game[$j]['PracReiting']+$ChangeReiting; 	
		  mysqli_query($db->link,"UPDATE AltStat_Players SET PracReiting='{$Game[$j]['PracReiting']}', PracWin='{$Game[$j]['PracWin']}', PracGame='{$Game[$j]['PracGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
								//Inc clan score
			$plr_id = $Game[$j][playerID];
			$clanID = mysqli_fetch_array(mysqli_query($db->link,"SELECT clanID, playerID FROM nfkLive_playerStats WHERE playerID='$plr_id'"));
			$clanID = $clanID[clanID];
			if ($clanID <> 0) {
				mysqli_query($db->link,"UPDATE `nfkLive_clanList` SET score=score+$ChangeReiting WHERE clanID='$clanID'");
				mysqli_query($db->link,"UPDATE `nfkLive_playerStats` SET clanScore=clanScore+$ChangeReiting, clanGames=clanGames+1 WHERE playerID='$plr_id'");
			}
		}	
	}


	If($Data['gameType'] == 'RAIL')
	{
	  $GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
	  for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
	  $Num[0]['RAIL']++;
	  mysqli_query($db->link,"UPDATE AltStat_NumGame SET RAIL='{$Num[0]['RAIL']}'") or die("Error".mysqli_error($db->link));
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);		      
	  $SumReiting2 = 0;
	  for($j=0;$j<$GameNumRows;$j++)
		{
		  $Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
		  $Game[$j+1]['frags'] = $GameData[$j]['frags'];
		  $Game[$j+1]['deaths'] = $GameData[$j]['deaths'];
		  $Game[$j+1]['time'] = ($GameData[$j]['time']-1)/60; 
		  $Game[$j+1]['timeCoef'] = ($Game[$j+1]['time'])/$GameTime;
		  $Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
		  $IsPlayer = mysqli_num_rows($Player);
		  If($IsPlayer == 0 )
			{
			  $Game[$j+1]['RailWin'] = 0;
			  $Game[$j+1]['RailReiting'] = 100;
			  $Game[$j+1]['RailGame'] = 0;
			  mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
			  CtfReiting=100, 
			  TdmReiting=100, 
			  DmReiting=100, 
			  DuelReiting=100, 
			  DomReiting=100, 
			  RailReiting=100,
			  PracReiting=100,
			  CtfGame=0, 
			  TdmGame=0,  
			  DmGame=0,  
			  DuelGame=0,  
			  DomGame=0, 
			  RailGame=0,
			  PracGame=0,
			  CtfWin=0, 
			  TdmWin=0,  
			  DmWin=0,  
			  DuelWin=0,  
			  DomWin=0, 
			  RailWin=0,
			  PracWin=0") or die("Error".mysqli_error($db->link));
			}
		   else
			{
			  for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
			  $Game[$j+1]['RailWin'] = $PlayerData[0]['RailWin'];
			  $Game[$j+1]['RailReiting'] = $PlayerData[0]['RailReiting'];
			  $Game[$j+1]['RailGame'] = $PlayerData[0]['RailGame'];
			}
		  $SumReiting2 = $SumReiting2 + ($Game[$j+1]['RailReiting'] + 100)*($Game[$j+1]['RailReiting'] + 100);  
		}
	  $AddAll = 8+50*exp(1-9/($GameNumRows+1));
	  $AddWin = ($GameNumRows+1)*($GameNumRows+1)/($GameNumRows+5);
	  for($j=$GameNumRows;$j>1;$j--)
		{
		  for($k=1;$k<$j;$k++)
			{
			  If($Game[$k]['frags']<$Game[$k+1]['frags'])
				{
				  $tmp = $Game[$k];
				  $Game[$k] = $Game[$k+1];
				  $Game[$k+1] = $tmp;
				}
			}
		}
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['RailGame']++;
		  $ChangeReiting = $AddAll*$Table[$GameNumRows][$j]/100-$AddAll*($Game[$j]['RailReiting']+100)*($Game[$j]['RailReiting']+100)/$SumReiting2*$Game[$j]['timeCoef'];
		  If($j==1)
			{
			  $Game[$j]['RailWin']++;
			}
		  If($j<=floor($GameNumRows*0.5))
			{
			  $ChangeReiting = $ChangeReiting + $AddWin/floor($GameNumRows*0.5);
			}					
		  If($ChangeReiting<0)
			{
			  $ChangeReiting=$ChangeReiting*(1-exp((100-$Game[$j]['RailReiting'])/100));
			}
		  else
			{
			  $ChangeReiting=$ChangeReiting*(1-exp(($Game[$j]['RailReiting']-1500)/180));
			}
		  $ChangeReiting = floor($ChangeReiting);	
		  mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['RailReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
		  $Game[$j]['RailReiting']=$Game[$j]['RailReiting']+$ChangeReiting; 	
		  mysqli_query($db->link,"UPDATE AltStat_Players SET RailReiting='{$Game[$j]['RailReiting']}', RailWin='{$Game[$j]['RailWin']}', RailGame='{$Game[$j]['RailGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
								//Inc clan score
			$plr_id = $Game[$j][playerID];
			$clanID = mysqli_fetch_array(mysqli_query($db->link,"SELECT clanID, playerID FROM nfkLive_playerStats WHERE playerID='$plr_id'"));
			$clanID = $clanID[clanID];
			if ($clanID <> 0) {
				mysqli_query($db->link,"UPDATE `nfkLive_clanList` SET score=score+$ChangeReiting WHERE clanID='$clanID'");
				mysqli_query($db->link,"UPDATE `nfkLive_playerStats` SET clanScore=clanScore+$ChangeReiting, clanGames=clanGames+1 WHERE playerID='$plr_id'");
			}
		}	
	}



	If($Data['gameType'] == 'CTF')
	{
	  $GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
	  for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
	  $Num[0]['CTF']++;
	  mysqli_query($db->link,"UPDATE AltStat_NumGame SET CTF='{$Num[0]['CTF']}'") or die("Error".mysqli_error($db->link));
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);		      
	  $SumReiting2Win = 0;
	  $SumReiting2Lose = 0;
	  $SumRatingRed = 0;
	  $SumRatingBlue = 0;
	  $AverageRatingRed = 50;
	  $AverageRatingBlue = 50;
	  $cap = true;
	  $numPlayersInRedTeam = 0;
	  $numPlayersInBlueTeam = 0;
	  $WinTeam = 'yellow';
	  $isDraw = 0;
	  $maxTime = 0;
	  for($j=0;$j<$GameNumRows;$j++)
		{
		  $Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
		  $Game[$j+1]['frags'] = $GameData[$j]['frags'];
		  $Game[$j+1]['deaths'] = $GameData[$j]['deaths'];
		  $Game[$j+1]['time'] = ($GameData[$j]['time']-1)/60; 
		  if($Game[$j+1]['time'] >$maxTime)
			{
			  $maxTime = $Game[$j+1]['time'];
			}
		  $Game[$j+1]['timeCoef'] = ($Game[$j+1]['time'])/1;
		  $Game[$j+1]['team'] = $GameData[$j]['team'];
		  if($cap)
		  {
			if($GameData[$j]['win']==1)
			{
			  $cap = false;
			  $WinTeam = $GameData[$j]['team'];
			}
		  }
		  $Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
		  $IsPlayer = mysqli_num_rows($Player);
		  If($IsPlayer == 0 )
			{
			  $Game[$j+1]['CtfWin'] = 0;
			  $Game[$j+1]['CtfReiting'] = 100;
			  $Game[$j+1]['CtfGame'] = 0;
			  mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
			  CtfReiting=100, 
			  TdmReiting=100, 
			  DmReiting=100, 
			  DuelReiting=100, 
			  DomReiting=100, 
			  RailReiting=100,
			  PracReiting=100,
			  CtfGame=0, 
			  TdmGame=0,  
			  DmGame=0,  
			  DuelGame=0,  
			  DomGame=0, 
			  RailGame=0,
			  PracGame=0,
			  CtfWin=0, 
			  TdmWin=0,  
			  DmWin=0,  
			  DuelWin=0,  
			  DomWin=0, 
			  RailWin=0,
			  PracWin=0") or die("Error".mysqli_error($db->link));
			}
		   else
			{
			  for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
			  $Game[$j+1]['CtfWin'] = $PlayerData[0]['CtfWin'];
			  $Game[$j+1]['CtfReiting'] = $PlayerData[0]['CtfReiting'];
			  $Game[$j+1]['CtfGame'] = $PlayerData[0]['CtfGame'];
			}
			if($GameData[$j]['time']/$GameTime>0.5)
			{
				if($GameData[$j]['team']=='red')
				{
					$SumRatingRed += $Game[$j+1]['CtfReiting'];
					$numPlayersInRedTeam++;
				}
				if($GameData[$j]['team']=='blue')
				{
					$SumRatingBlue += $Game[$j+1]['CtfReiting'];
					$numPlayersInBlueTeam++;
				}
				$Game[$j+1]['leaver']=false;
			}
			else
				$Game[$j+1]['leaver']=true;
			
		}	
	  for($j=1;$j<=$GameNumRows;$j++)
		{
			$Game[$j]['timeCoef'] = ($Game[$j]['time'])/$maxTime;
			if($Game[$j]['timeCoef'] > 1) $Game[$j]['timeCoef'] = 1;
		}
	  if($numPlayersInRedTeam!==0)
		$AverageRatingRed = round($SumRatingRed/$numPlayersInRedTeam);
	  if($numPlayersInBlueTeam!==0)
		$AverageRatingBlue = round($SumRatingBlue/$numPlayersInBlueTeam);
	  $isDraw = ($Data['redScore'] == $Data['blueScore']);
	  if(($WinTeam=='red') or ($isDraw))
	  {
		$AddAllWin = formulaElo($AverageRatingRed, $AverageRatingBlue, $isDraw);
		$AddAllLose = $AddAllWin*(-1);
		if($numPlayersInRedTeam<$numPlayersInBlueTeam) $AddAllWin *= 1.25; //bonus
	  }
	  else
	  {
		$AddAllWin = formulaElo($AverageRatingBlue, $AverageRatingRed, false);
		$AddAllLose = $AddAllWin*(-1);
		if($numPlayersInBlueTeam<$numPlayersInRedTeam) $AddAllWin *= 1.25; //bonus
	  }
	  for($j=$GameNumRows;$j>1;$j--)
		{
		  for($k=1;$k<$j;$k++)
			{
			  If($Game[$k]['frags']<$Game[$k+1]['frags'])
				{
				  $tmp = $Game[$k];
				  $Game[$k] = $Game[$k+1];
				  $Game[$k+1] = $tmp;
				}
			}
		}
	  $j=1;	
	  $NumWin = 0;
	  While($j<=$GameNumRows)
		{
		  $k=$j;
		  While( isset($Game[$k]) and ($Game[$k]['team']!==$WinTeam) and ($k<=$GameNumRows))
			{
			  $k++;
			}  
		  $NumWin++;	
		  if($k<=$GameNumRows)
			{
			  if($j>=2*$NumWin-1)
				{
				  $tmp = $Game[$k];
				  for($s=$k;$s>=$j+1;$s--)
				  {
					$Game[$s] = $Game[$s-1];
				  }
				  $Game[$j] = $tmp;
				}  
			}
		  else
			{
			  break;
			}
		  $j++;	
		}
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['CtfGame']++;
		  if($Game[$j]['leaver']==true)
			$ChangeReiting = 0;
		  else
		  {
			  if($Game[$j]['team']==$WinTeam)
				{
				  $ChangeReiting = correctRatingChange($Game[$j]['CtfReiting'], $AddAllWin)*$Game[$j]['timeCoef'];
				  $Game[$j]['CtfWin']++;
				}
			  else
				{
				  $ChangeReiting = correctRatingChange($Game[$j]['CtfReiting'], $AddAllLose);
				}
		  }				
		  $ChangeReiting = round($ChangeReiting);
		  if (($ChangeReiting < 0) and ($Game[$j]['team']==$WinTeam)) $ChangeReiting = 1;
		  
		  mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['CtfReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
		  $Game[$j]['CtfReiting']=$Game[$j]['CtfReiting']+$ChangeReiting; 	
		  mysqli_query($db->link,"UPDATE AltStat_Players SET CtfReiting='{$Game[$j]['CtfReiting']}', RailWin='{$Game[$j]['CtfWin']}', CtfGame='{$Game[$j]['CtfGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
								//Inc clan score
			$plr_id = $Game[$j]['playerID'];
			$clanID = mysqli_fetch_array(mysqli_query($db->link,"SELECT clanID, playerID FROM nfkLive_playerStats WHERE playerID='$plr_id'"));
			$clanID = $clanID['clanID'];
			if ($clanID <> 0) {
				mysqli_query($db->link,"UPDATE `nfkLive_clanList` SET score=score+$ChangeReiting WHERE clanID='$clanID'");
				mysqli_query($db->link,"UPDATE `nfkLive_playerStats` SET clanScore=clanScore+$ChangeReiting, clanGames=clanGames+1 WHERE playerID='$plr_id'");
			}
		}	
	}


	If($Data['gameType'] == 'TDM')
	{
	  $GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
	  for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
	  $Num[0]['TDM']++;
	  mysqli_query($db->link,"UPDATE AltStat_NumGame SET TDM='{$Num[0]['TDM']}'") or die("Error".mysqli_error($db->link));
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);		      
	  $SumReiting2Win = 0;
	  $SumReiting2Lose = 0;
	  $SumRatingRed = 0;
	  $SumRatingBlue = 0;
	  $AverageRatingRed = 50;
	  $AverageRatingBlue = 50;
	  $cap = true;
	  $numPlayersInRedTeam = 0;
	  $numPlayersInBlueTeam = 0;
	  $WinTeam = 'yellow';
	  $maxTime = 0;
	  for($j=0;$j<$GameNumRows;$j++)
		{
		  $Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
		  $Game[$j+1]['frags'] = $GameData[$j]['frags'];
		  $Game[$j+1]['deaths'] = $GameData[$j]['deaths'];
		  $Game[$j+1]['time'] = ($GameData[$j]['time']-1)/60; 
		  if($Game[$j+1]['time'] >$maxTime)
			{
			  $maxTime = $Game[$j+1]['time'];
			}
		  $Game[$j+1]['timeCoef'] = ($Game[$j+1]['time'])/1;
		  $Game[$j+1]['team'] = $GameData[$j]['team'];
		  if($cap)
		  {
			if($GameData[$j]['win']==1)
			{
			  $cap = false;
			  $WinTeam = $GameData[$j]['team'];
			}
		  }
		  $Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
		  $IsPlayer = mysqli_num_rows($Player);
		  If($IsPlayer == 0 )
			{
			  $Game[$j+1]['TdmWin'] = 0;
			  $Game[$j+1]['TdmReiting'] = 100;
			  $Game[$j+1]['TdmGame'] = 0;
			  mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
			  CtfReiting=100, 
			  TdmReiting=100, 
			  DmReiting=100, 
			  DuelReiting=100, 
			  DomReiting=100, 
			  RailReiting=100,
			  PracReiting=100,
			  CtfGame=0, 
			  TdmGame=0,  
			  DmGame=0,  
			  DuelGame=0,  
			  DomGame=0, 
			  RailGame=0,
			  PracGame=0,
			  CtfWin=0, 
			  TdmWin=0,  
			  DmWin=0,  
			  DuelWin=0,  
			  DomWin=0, 
			  RailWin=0,
			  PracWin=0") or die("Error".mysqli_error($db->link));
			}
		   else
			{
			  for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
			  $Game[$j+1]['TdmWin'] = $PlayerData[0]['TdmWin'];
			  $Game[$j+1]['TdmReiting'] = $PlayerData[0]['TdmReiting'];
			  $Game[$j+1]['TdmGame'] = $PlayerData[0]['TdmGame'];
			}
			if($GameData[$j]['time']/$GameTime>0.5)
			{
				if($GameData[$j]['team']=='red')
				{
					$SumRatingRed += $Game[$j+1]['TdmReiting'];
					$numPlayersInRedTeam++;
				}
				if($GameData[$j]['team']=='blue')
				{
					$SumRatingBlue += $Game[$j+1]['TdmReiting'];
					$numPlayersInBlueTeam++;
				}
				$Game[$j]['leaver']=false;
			}
			else
				$Game[$j]['leaver']=true;
		}	
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['timeCoef'] = ($Game[$j]['time'])/$maxTime;
		  if($Game[$j]['timeCoef'] > 1) $Game[$j]['timeCoef'] = 1;
		}
	  if($numPlayersInRedTeam!==0)
		$AverageRatingRed = round($SumRatingRed/$numPlayersInRedTeam);
	  if($numPlayersInBlueTeam!==0)
		$AverageRatingBlue = round($SumRatingBlue/$numPlayersInBlueTeam);
	  if(($WinTeam=='red') or ($WinTeam=='yellow'))
	  {
		$AddAllWin = formulaElo($AverageRatingRed, $AverageRatingBlue, $WinTeam=='yellow');
		$AddAllLose = $AddAllWin*(-1);
		if($numPlayersInRedTeam<$numPlayersInBlueTeam) $AddAllWin *= 1.25; //bonus
	  }
	  else
	  {
		$AddAllWin = formulaElo($AverageRatingBlue, $AverageRatingRed, false);
		$AddAllLose = $AddAllWin*(-1);
		if($numPlayersInBlueTeam<$numPlayersInRedTeam) $AddAllWin *= 1.25; //bonus
	  }
	  for($j=$GameNumRows;$j>1;$j--)
		{
		  for($k=1;$k<$j;$k++)
			{
			  If($Game[$k]['frags']<$Game[$k+1]['frags'])
				{
				  $tmp = $Game[$k];
				  $Game[$k] = $Game[$k+1];
				  $Game[$k+1] = $tmp;
				}
			}
		}
	  $j=1;	
	  $NumWin = 0;
	  While($j<=$GameNumRows)
		{
		  $k=$j;
		  While( isset($Game[$k]) and ($Game[$k]['team']!==$WinTeam) and ($k<=$GameNumRows) )
			{
			  $k++;
			}  
		  $NumWin++;	
		  if($k<=$GameNumRows)
			{
			  if($j>=2*$NumWin-1)
				{
				  $tmp = $Game[$k];
				  for($s=$k;$s>=$j+1;$s--)
				  {
					$Game[$s] = $Game[$s-1];
				  }
				  $Game[$j] = $tmp;
				}  
			}
		  else
			{
			  break;
			}
		  $j++;	
		}
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['TdmGame']++;
		  if(isset($Game[$j]['leaver']) && $Game[$j]['leaver']==true)
			$ChangeReiting = 0;
		  else
		  {
			  if($Game[$j]['team']==$WinTeam)
				{
				  $ChangeReiting = correctRatingChange($Game[$j]['TdmReiting'], $AddAllWin)*$Game[$j]['timeCoef'];
				  $Game[$j]['TdmWin']++;
				}
			  else
				{
				  $ChangeReiting = correctRatingChange($Game[$j]['TdmReiting'], $AddAllLose);
				}
		  }				
		  $ChangeReiting = round($ChangeReiting);	
		 if (($ChangeReiting < 0) and ($Game[$j]['team']==$WinTeam)) $ChangeReiting = 1;
		  
		  mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['TdmReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
		  $Game[$j]['TdmReiting']=$Game[$j]['TdmReiting']+$ChangeReiting; 	
		  mysqli_query($db->link,"UPDATE AltStat_Players SET TdmReiting='{$Game[$j]['TdmReiting']}', TdmWin='{$Game[$j]['TdmWin']}', TdmGame='{$Game[$j]['TdmGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
								//Inc clan score
			$plr_id = $Game[$j]['playerID'];
			$clanID = mysqli_fetch_array(mysqli_query($db->link,"SELECT clanID, playerID FROM nfkLive_playerStats WHERE playerID='$plr_id'"));
			$clanID = $clanID['clanID'];
			if ($clanID <> 0) {
				mysqli_query($db->link,"UPDATE `nfkLive_clanList` SET score=score+$ChangeReiting WHERE clanID='$clanID'");
				mysqli_query($db->link,"UPDATE `nfkLive_playerStats` SET clanScore=clanScore+$ChangeReiting, clanGames=clanGames+1 WHERE playerID='$plr_id'");
			}
		}	
	}


	If($Data['gameType'] == 'DOM')
	{
	  $GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
	  for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
	  $Num[0]['DOM']++;
	  mysqli_query($db->link,"UPDATE AltStat_NumGame SET DOM='{$Num[0]['DOM']}'") or die("Error".mysqli_error($db->link));
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);		      
	  $SumReiting2Win = 0;
	  $SumReiting2Lose = 0;
	  $cap = true;
	  $numPlayersInWinTeam = 0;
	  $WinTeam = 'yellow';
	  $maxTime = 0;
	  for($j=0;$j<$GameNumRows;$j++)
		{
		  $Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
		  $Game[$j+1]['frags'] = $GameData[$j]['frags'];
		  $Game[$j+1]['deaths'] = $GameData[$j]['deaths'];
		  $Game[$j+1]['time'] = ($GameData[$j]['time']-1)/60; 
		  if($Game[$j+1]['time'] >$maxTime)
			{
			  $maxTime = $Game[$j+1]['time'];
			}
		  $Game[$j+1]['timeCoef'] = ($Game[$j+1]['time'])/1;
		  $Game[$j+1]['team'] = $GameData[$j]['team'];
		  if($cap)
		  {
			if($GameData[$j]['win']==1)
			{
			  $cap = false;
			  $WinTeam = $GameData[$j]['team'];
			}
		  }
		  $Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
		  $IsPlayer = mysqli_num_rows($Player);
		  If($IsPlayer == 0 )
			{
			  $Game[$j+1]['DomWin'] = 0;
			  $Game[$j+1]['DomReiting'] = 100;
			  $Game[$j+1]['DomGame'] = 0;
			  mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
			  CtfReiting=100, 
			  TdmReiting=100, 
			  DmReiting=100, 
			  DuelReiting=100, 
			  DomReiting=100, 
			  RailReiting=100,
			  PracReiting=100,
			  CtfGame=0, 
			  TdmGame=0,  
			  DmGame=0,  
			  DuelGame=0,  
			  DomGame=0, 
			  RailGame=0,
			  PracGame=0,
			  CtfWin=0, 
			  TdmWin=0,  
			  DmWin=0,  
			  DuelWin=0,  
			  DomWin=0, 
			  RailWin=0,
			  PracWin=0") or die("Error".mysqli_error($db->link));
			}
		   else
			{
			  for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
			  $Game[$j+1]['DomWin'] = $PlayerData[0]['DomWin'];
			  $Game[$j+1]['DomReiting'] = $PlayerData[0]['DomReiting'];
			  $Game[$j+1]['DomGame'] = $PlayerData[0]['DomGame'];
			}
			if($WinTeam!=='yellow')
			  {					
				if($GameData[$j]['team']==$WinTeam)
				  {
					$numPlayersInWinTeam++; 
					$SumReiting2Win = $SumReiting2Win + ($Game[$j+1]['DomReiting'] + 100)*($Game[$j+1]['DomReiting'] + 100);
				  }
				else
				  { 
					$SumReiting2Lose = $SumReiting2Lose + ($Game[$j+1]['DomReiting'] + 100)*($Game[$j+1]['DomReiting'] + 100);
				  }
			  }
			else
			  {
				$SumReiting2Lose = $SumReiting2Lose + ($Game[$j+1]['DomReiting'] + 100)*($Game[$j+1]['DomReiting'] + 100);
			  }  
		}	
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['timeCoef'] = ($Game[$j+1]['time'])/$maxTime;
		}
	  $AddAll = 8+50*exp(1-9/($GameNumRows+1));
	  $AddAllWin = $AddAll*sqrt($SumReiting2Win)/(sqrt($SumReiting2Win)+sqrt($SumReiting2Lose));
	  $AddAllLose = $AddAll*sqrt($SumReiting2Lose)/(sqrt($SumReiting2Win)+sqrt($SumReiting2Lose));
	  $AddWin = ($GameNumRows+1)*($GameNumRows+1)/($GameNumRows+5);
	  for($j=$GameNumRows;$j>1;$j--)
		{
		  for($k=1;$k<$j;$k++)
			{
			  If($Game[$k]['frags']<$Game[$k+1]['frags'])
				{
				  $tmp = $Game[$k];
				  $Game[$k] = $Game[$k+1];
				  $Game[$k+1] = $tmp;
				}
			}
		}
	  $j=1;	
	  $NumWin = 0;
	  While($j<=$GameNumRows)
		{
		  $k=$j;
		  While( isset($Game[$k]) and ($Game[$k]['team']!==$WinTeam) and ($k<=$GameNumRows))
			{
			  $k++;
			}  
		  $NumWin++;	
		  if($k<=$GameNumRows)
			{
			  if($j>=2*$NumWin-1)
				{
				  $tmp = $Game[$k];
				  for($s=$k;$s>=$j+1;$s--)
				  {
					$Game[$s] = $Game[$s-1];
				  }
				  $Game[$j] = $tmp;
				}  
			}
		  else
			{
			  break;
			}
		  $j++;	
		}
	  for($j=1;$j<=$GameNumRows;$j++)
		{
		  $Game[$j]['DomGame']++;
		  if($Game[$j]['team']==$WinTeam)
			{
			  $ChangeReiting = $AddAll*$Table[$GameNumRows][$j]/100-$AddAllWin*($Game[$j]['DomReiting']+100)*($Game[$j]['DomReiting']+100)/$SumReiting2Win*$Game[$j]['timeCoef'];
			  $Game[$j]['DomWin']++;
			  $ChangeReiting = $ChangeReiting + $AddWin/$numPlayersInWinTeam;
			}
		  else
			{
			  $ChangeReiting = $AddAll*$Table[$GameNumRows][$j]/100-$AddAllLose*($Game[$j]['DomReiting']+100)*($Game[$j]['DomReiting']+100)/$SumReiting2Lose*$Game[$j]['timeCoef'];
			}					
		  If($ChangeReiting<0)
			{
			  $ChangeReiting=$ChangeReiting*(1-exp((100-$Game[$j]['DomReiting'])/100));
			}
		  else
			{
			  $ChangeReiting=$ChangeReiting*(1-exp(($Game[$j]['DomReiting']-1500)/180));
			}
		  $ChangeReiting = floor($ChangeReiting);	
		  mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['DomReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
		  $Game[$j]['DomReiting']=$Game[$j]['DomReiting']+$ChangeReiting; 	
		  mysqli_query($db->link,"UPDATE AltStat_Players SET DomReiting='{$Game[$j]['DomReiting']}', DomWin='{$Game[$j]['DomWin']}', DomGame='{$Game[$j]['DomGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
								//Inc clan score
			$plr_id = $Game[$j][playerID];
			$clanID = mysqli_fetch_array(mysqli_query($db->link,"SELECT clanID, playerID FROM nfkLive_playerStats WHERE playerID='$plr_id'"));
			$clanID = $clanID[clanID];
			if ($clanID <> 0) {
				mysqli_query($db->link,"UPDATE `nfkLive_clanList` SET score=score+$ChangeReiting WHERE clanID='$clanID'");
				mysqli_query($db->link,"UPDATE `nfkLive_playerStats` SET clanScore=clanScore+$ChangeReiting, clanGames=clanGames+1 WHERE playerID='$plr_id'");
			}
		}	
	}


	If($Data['gameType'] == 'DUEL')
	{
	  $NumMatch = $Data['matchID'];
	  $GameTime = ($Data['gameTime'] <> NULL) ? $Data['gameTime'] : $Data['timeLimit'];
	  $GameResult = mysqli_query($db->link,"SELECT * FROM nfkLive_matchData WHERE matchID=$NumMatch") or die("Error".mysqli_error($db->link));
	  $GameNumRows = mysqli_num_rows($GameResult);
	  if($GameNumRows == 2)
	  {
		$GameNum = mysqli_query($db->link,"SELECT * FROM AltStat_NumGame") or die("Error".mysqli_error($db->link));
		for($Num=array(); $Numrow=mysqli_fetch_assoc($GameNum); $Num[]=$Numrow);
		$Num[0]['DUEL']++;
		mysqli_query($db->link,"UPDATE AltStat_NumGame SET DUEL='{$Num[0]['DUEL']}'") or die("Error".mysqli_error($db->link));
		for($GameData=array(); $Gamerow=mysqli_fetch_assoc($GameResult); $GameData[]=$Gamerow);
		$SumReiting2 = 0;
		for($j=0;$j<$GameNumRows;$j++)
		  {
			$Game[$j+1]['playerID'] = $GameData[$j]['playerID'];
			$Game[$j+1]['frags'] = $GameData[$j]['frags'];
			$Player = mysqli_query($db->link,"SELECT * FROM AltStat_Players WHERE PlayerId='{$Game[$j+1]['playerID']}'") or die("Error".mysqli_error($db->link));
			$IsPlayer = mysqli_num_rows($Player);
			If($IsPlayer == 0 )
			  {
				$Game[$j+1]['DuelWin'] = 0;
				$Game[$j+1]['DuelReiting'] = 100;
				$Game[$j+1]['DuelGame'] = 0;
				mysqli_query($db->link,"INSERT INTO AltStat_Players SET PlayerId='{$Game[$j+1]['playerID']}', 
				CtfReiting=100, 
				TdmReiting=100, 
				DmReiting=100, 
				DuelReiting=100, 
				DomReiting=100, 
				RailReiting=100,
				PracReiting=100,
				CtfGame=0, 
				TdmGame=0,  
				DmGame=0,  
				DuelGame=0,  
				DomGame=0, 
				RailGame=0,
				PracGame=0,
				CtfWin=0, 
				TdmWin=0,  
				DmWin=0,  
				DuelWin=0,  
				DomWin=0, 
				RailWin=0,
				PracWin=0") or die("Error".mysqli_error($db->link));
			  }
			else
			  {
				for($PlayerData=array(); $Playerrow=mysqli_fetch_assoc($Player); $PlayerData[]=$Playerrow);
				$Game[$j+1]['DuelWin'] = $PlayerData[0]['DuelWin'];
				$Game[$j+1]['DuelReiting'] = $PlayerData[0]['DuelReiting'];
				$Game[$j+1]['DuelGame'] = $PlayerData[0]['DuelGame'];
			  }
			$SumReiting2 = $SumReiting2 + ($Game[$j+1]['DuelReiting'] + 100)*($Game[$j+1]['DuelReiting'] + 100);  
		  }
		$AddAll = 40;
		$AddWin = 3;
		for($j=$GameNumRows;$j>1;$j--)
		  {
			for($k=1;$k<$j;$k++)
			  {
				If($Game[$k]['frags']<$Game[$k+1]['frags'])
				  {
					$tmp = $Game[$k];
					$Game[$k] = $Game[$k+1];
					$Game[$k+1] = $tmp;
				  }
			  }
		  }

			$Game[1]['DuelGame']++;
			$Game[2]['DuelGame']++;
				$Game[1]['DuelWin']++;
				$ChangeReiting = $AddAll*($Game[1]['frags']+abs($Game[1]['frags'])+1)/($Game[1]['frags']+abs($Game[1]['frags'])+$Game[2]['frags']+abs($Game[2]['frags'])+2)-$AddAll*($Game[1]['DuelReiting']+100)*($Game[1]['DuelReiting']+100)/$SumReiting2;
				$ChangeReiting = $ChangeReiting + $AddWin;
			$j=1;	
			If($ChangeReiting<0)
			  {
				$ChangeReiting=$ChangeReiting*(1-exp((100-$Game[$j]['DuelReiting'])/100));
			  }
			else
			  {
				$ChangeReiting=$ChangeReiting*(1-exp(($Game[$j]['DuelReiting']-1500)/180));
			  }
			$ChangeReiting = floor($ChangeReiting);  
			mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['DuelReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
			$Game[$j]['DuelReiting']=$Game[$j]['DuelReiting']+$ChangeReiting; 	
			mysqli_query($db->link,"UPDATE AltStat_Players SET DuelReiting='{$Game[$j]['DuelReiting']}', DuelWin='{$Game[$j]['DuelWin']}', DuelGame='{$Game[$j]['DuelGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
			

				$ChangeReiting = $AddAll*($Game[2]['frags']+abs($Game[2]['frags'])+1)/($Game[1]['frags']+abs($Game[1]['frags'])+$Game[2]['frags']+abs($Game[2]['frags'])+2)-$AddAll*($Game[2]['DuelReiting']+100)*($Game[2]['DuelReiting']+100)/$SumReiting2;
			$j=2;
			If($ChangeReiting<0)
			  {
				$ChangeReiting=$ChangeReiting*(1-exp((100-$Game[$j]['DuelReiting'])/100));
			  }
			else
			  {
				$ChangeReiting=$ChangeReiting*(1-exp(($Game[$j]['DuelReiting']-1500)/180));
			  }
			$ChangeReiting = floor($ChangeReiting);  
			mysqli_query($db->link,"INSERT INTO AltStat_GameRes SET MatchId='{$NumMatch}', PlayerId='{$Game[$j]['playerID']}', Place='{$j}', Reiting='{$Game[$j]['DuelReiting']}', Result='{$ChangeReiting}'")or die("Error".mysqli_error($db->link));	
			$Game[$j]['DuelReiting']=$Game[$j]['DuelReiting']+$ChangeReiting; 	
			mysqli_query($db->link,"UPDATE AltStat_Players SET DuelReiting='{$Game[$j]['DuelReiting']}', DuelWin='{$Game[$j]['DuelWin']}', DuelGame='{$Game[$j]['DuelGame']}' WHERE PlayerId='{$Game[$j]['playerID']}'") or die("Error".mysqli_error($db->link));
			
	  }
	}  
			
			
	 // mysqli_query($db->link,"UPDATE AltStat_Options SET LastNum='{$old}'") or die("Error".mysqli_error($db->link));
	 // mysqli_close();
		
		echo ('OK: Updated');
      //Header("Location: http://{$_SERVER['SERVER_NAME']}/Dm.php");	

?>
