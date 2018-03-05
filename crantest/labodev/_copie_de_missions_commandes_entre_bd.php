<?php  require_once('_const_fonc.php'); ?>
<?php 
ini_set('display_errors',true);
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
if(strtolower($tab_infouser['login'])!='parant' && strtolower($tab_infouser['login'])!='kondratow')
{ ?>User non autorisé
<?php
exit;
}
$action=isset($_POST['action'])?$_POST['action']:"";
$liste_missions_commandes=isset($_POST['liste_missions_commandes'])?$_POST['liste_missions_commandes']:"";
$test_sans_copie=false;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>_copie_de_commandes_missions_entre_bd</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css" type="text/css">
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css">
</head>
<body>

<?php 
$annee_orig=date('Y')-1;
$annee_dest=date('Y');
$codesubstitution='10003';
$warning='';
$erreur='';
if($test_sans_copie)
{ echo '<b>Mode test sans copie</b><br>';
}?>
Copie de commandes de la base de l'exercice <?php echo $annee_orig ?> vers la base de l'exercice en cours <?php echo $annee_dest ?>, avec codemission, sans commandeimputationbudget_statutvisa,commandejustifiecontrat
	<form name="copie_commandes_missions" method="post" action="_copie_de_missions_commandes_entre_bd.php">
  <br>N&deg; internes des missions-commandes <?php echo $annee_orig ?> s&eacute;par&eacute;s par des virgules : 
  	<textarea name="liste_missions_commandes" cols="100" rows="3"><?php echo $liste_missions_commandes ?></textarea>
   <br>Si une imputation de <?php echo $annee_orig ?> n'est pas trouv&eacute;e en <?php echo $annee_dest ?> elle sera substitu&eacute;e par l'imputation de dotation UL <?php echo $codesubstitution ?>
    <input type="submit" name="action" value="executer"> 
	</form>
<?php 
if($action=="executer")
{ $hostname = "localhost";
	$db_orig = $database_labo.$annee_orig;
	$db_dest = $database_labo;
	$username = $username_labo;
	$password = $password_labo; /* */
	$conn = mysql_connect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR); 
	$liste_missions_commandes=str_replace(' ','',$_POST['liste_missions_commandes']); //suppression des espaces
	$tab_liste_missions_commandes=split(',',$liste_missions_commandes);
	
	// formate $listecommandes et rejette commande si existe deja en dest ou n'existe pas en orig
	$listemissions='';
	$listecommandes='';
	foreach($tab_liste_missions_commandes as $un_commandemission)
	{ $tab_commandemission=split('-',$un_commandemission);
		if(count($tab_commandemission)==1)
		{ $tab_listecommandes[$tab_commandemission[0]]=false;
		}
		else if(count($tab_commandemission)==2)
		{ $tab_listemissions[$tab_commandemission[0]]='';
			$tab_listecommandes[$tab_commandemission[1]]=true;
		}
	}
	$first=true;
	foreach($tab_listemissions as $un_codemission=>$val)
	{ $un_codemission=str_pad(''.intval($un_codemission), 5, "0", STR_PAD_LEFT);
		mysql_select_db($db_dest, $conn);
		$query_rs="select * from mission where codemission=".GetSQLValueString($un_codemission, 'text');
		$rs=mysql_query($query_rs) or  die(mysql_error()); 
		if(mysql_num_rows($rs)>0)
		{ $erreur.='<br>la mission '.$un_codemission.' existe d&eacute;j&agrave; dans la base '.$annee_dest;
		}
		mysql_select_db($db_orig, $conn);
		$query_rs="select * from mission where codemission=".GetSQLValueString($un_codemission, 'text');
		$rs=mysql_query($query_rs); 
		if(mysql_num_rows($rs)==0)
		{ $erreur.='<br>la mission '.$un_codemission.' n&rsquo;existe pas dans la base '.$annee_orig.'';
		}
		$listemissions.=($first?'':',').$un_codemission;
		$first=false;
	}
	$first=true;
	foreach($tab_listecommandes as $un_codecommande=>$estlie)
	{ $un_codecommande=str_pad(''.intval($un_codecommande), 5, "0", STR_PAD_LEFT);
		mysql_select_db($db_dest, $conn);
		$query_rs="select * from commande where codecommande=".GetSQLValueString($un_codecommande, 'text');
		$rs=mysql_query($query_rs) or  die(mysql_error()); 
		if(mysql_num_rows($rs)>0)
		{ $erreur.='<br>la commande '.$un_codecommande.' existe d&eacute;j&agrave; dans la base '.$annee_dest;
		}
		mysql_select_db($db_orig, $conn);
		$query_rs="select * from commande where codecommande=".GetSQLValueString($un_codecommande, 'text');
		$rs=mysql_query($query_rs); 
		if(mysql_num_rows($rs)==0)
		{ $erreur.='<br>la commande '.$un_codecommande.' n&rsquo;existe pas dans la base '.$annee_orig.'';
		}
		$listecommandes.=($first?'':',').$un_codecommande;
		$tab_commande_lie[$un_codecommande]=$estlie;
		$first=false;
	}

	if($erreur!='')
	{ echo $erreur.'<br><b>Veuillez corriger la liste des commandes &agrave; copier</b>';
	}
	else
	{ // base origine
		mysql_select_db($db_orig, $conn);
		$tab_tables=array('mission','missionetape'/* ,'commande','commandeimputationbudget','commandestatutvisa',
									'commandeimputationbudget_statutvisa','commandejustifiecontrat','commandemigo','commandemigoliquidation' */);
		foreach($tab_tables as $nomtable)
		{ $rs=mysql_query("show columns from ".$nomtable) or  die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_champs[$nomtable][]=$row_rs['Field'];
			}
		}
		$query_rs_mission="select * from mission where FIND_IN_SET(codemission,".GetSQLValueString($listemissions, 'text').")";
		$rs_mission=mysql_query($query_rs_mission);
		while($row_rs_mission=mysql_fetch_assoc($rs_mission))
		{ $codemission=$row_rs_mission['codemission'];
			$tab_val['mission'][]=$row_rs_mission;
			$query_rs="select * from missionetape where codemission=".GetSQLValueString($codemission, 'text');
			$rs=mysql_query($query_rs); 
			echo '<br>'.$query_rs;
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_val['missionetape'][]=$row_rs;
			}
		}
		// base destination
		echo '<br>Missions : '.$listemissions;
		mysql_select_db($db_dest, $conn);
		foreach($tab_tables as $nomtable)
		{	if(isset($tab_val[$nomtable]))
			{ foreach($tab_val[$nomtable] as $i=>$un_tab_val)
				{ $first=true;
					$liste_champs="";$liste_val="";
					foreach($tab_champs[$nomtable] as $champ)
					{	$liste_champs.=($first?"":",").$champ;
						$liste_val.=($first?"":",");
						$first=false;
						if(isset($un_tab_val[$champ]))
						{ $liste_val.=GetSQLValueString($un_tab_val[$champ], "text");
						}
						else
						{ $liste_val.="''";
						}
					}
					$updateSQL = "insert into  ".$nomtable." (".$liste_champs.") values (".$liste_val.")";
					if($test_sans_copie==false)
					{ mysql_query($updateSQL);
					}
					echo '<br>'.$updateSQL;
				}
			}
		}
		if(isset($rs))mysql_free_result($rs);
		// COMMANDES
		echo '<br>Commandes : '.$listecommandes;
		// base origine
		mysql_select_db($db_orig, $conn);
		$query_rs="select * from commande where FIND_IN_SET(codecommande,".GetSQLValueString($listecommandes, 'text').")";
		echo $query_rs;
		$rs=mysql_query($query_rs); 
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commande[$row_rs['codecommande']]=$row_rs;
		}
		
		$query_rs="select * from commandeimputationbudget where FIND_IN_SET(codecommande,".GetSQLValueString($listecommandes, 'text').")";
		$rs=mysql_query($query_rs); 
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandeimputationbudget[$row_rs['codecommande']][$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
		}
		$query_rs="select * from commandestatutvisa where FIND_IN_SET(codecommande,".GetSQLValueString($listecommandes, 'text').")";
		$rs=mysql_query($query_rs); 
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandestatutvisa[$row_rs['codecommande']][$row_rs['codestatutvisa']]=$row_rs;
		}
		$query_rs="select * from commandemigo where FIND_IN_SET(codecommande,".GetSQLValueString($listecommandes, 'text').")";
		$rs=mysql_query($query_rs); 
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandemigo[$row_rs['codecommande']][$row_rs['codemigo']]=$row_rs;
		}
		$query_rs="select * from commandemigoliquidation where FIND_IN_SET(codecommande,".GetSQLValueString($listecommandes, 'text').")";
		$rs=mysql_query($query_rs); 
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandemigoliquidation[$row_rs['codecommande']][$row_rs['codemigo']][$row_rs['codeliquidation']]=$row_rs;
		}
		$query_rs="select * from commandepj where FIND_IN_SET(codecommande,".GetSQLValueString($listecommandes, 'text').")";
		$rs=mysql_query($query_rs); 
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_commandepj[$row_rs['codecommande']][$row_rs['codetypepj']]=$row_rs;
		}
		
		// base destination
		mysql_select_db($db_dest, $conn);
		// verif que imputation d'origine a son imputation correspondante en dest
		$query_rs="SELECT typecredit.codetypecredit,centrefinancier.codecentrefinancier,centrecout.codecentrecout,codecontrat,'' as codeeotp,'0' as virtuel_ou_reel".
							" from typecredit,centrefinancier,centrecout,budg_contrat_source_vue".
							" where typecredit.codetypecredit=centrefinancier.codetypecredit".
							" and centrefinancier.codecentrefinancier=centrecout.codecentrefinancier".
							" and centrecout.codecentrecout=budg_contrat_source_vue.codecentrecout".
							" UNION".
							" SELECT typecredit.codetypecredit,centrefinancier.codecentrefinancier,centrecout.codecentrecout,'' as codecontrat,codeeotp,'1' as virtuel_ou_reel".
							" from typecredit,centrefinancier,centrecout,budg_eotp_source_vue".
							" where typecredit.codetypecredit=centrefinancier.codetypecredit".
							" and centrefinancier.codecentrefinancier=centrecout.codecentrefinancier".
							" and centrecout.codecentrecout=budg_eotp_source_vue.codecentrecout";
		$rs=mysql_query($query_rs);
		$firstul=true;
		$firstcnrs=true;
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_creditfinanciercoutcontrateotp[$row_rs['virtuel_ou_reel']][$row_rs['codetypecredit']][$row_rs['codecentrefinancier']][$row_rs['codecentrecout']][$row_rs['codecontrat']][$row_rs['codeeotp']]=true;
			if($firstul && $row_rs['codecontrat']==$codesubstitution)// substitution ul dotation pour toute source de l'annee passee n'existant pas dans l'annee en cours
			{ $tab_substitutionimputation[$codesubstitution]=array('codetypecredit'=>$row_rs['codetypecredit'],'codecentrefinancier'=>$row_rs['codecentrefinancier'],'codecentrecout'=>$row_rs['codecentrecout'],'codecontrat'=>$row_rs['codecontrat'],'codeeotp'=>$row_rs['codecontrat']);
				$firstul=false;
			}
		}
		
		$rs=mysql_query("show columns from commande");
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_champs_commande[]=$row_rs['Field'];
		}
		$rs=mysql_query("show columns from commandeimputationbudget");
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_champs_commandeimputationbudget[]=$row_rs['Field'];
		}
		$rs=mysql_query("show columns from commandestatutvisa");
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_champs_commandestatutvisa[]=$row_rs['Field'];
		}
		$rs=mysql_query("show columns from commandemigo");
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_champs_commandemigo[]=$row_rs['Field'];
		}
		$rs=mysql_query("show columns from commandemigoliquidation");
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_champs_commandemigoliquidation[]=$row_rs['Field'];
		}
		$rs=mysql_query("show columns from commandepj");
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_champs_commandepj[]=$row_rs['Field'];
		}
		
		foreach($tab_commande as $codecommande=>$un_tab_commande)
		{ echo '<br>-----------------<b>'.$codecommande.'</b>---------------';
			$first=true;
			$liste_champs="";$liste_val="";
			foreach($tab_champs_commande as $champ)
			{	$liste_champs.=($first?"":",").$champ;
				$liste_val.=($first?"":",");
				$first=false;
				if($champ=='codecommande')
				{ $liste_val.=GetSQLValueString($codecommande, "text");
				}
				/*else if($champ=='date_creation' || $champ=='date_modif')
				{ $liste_val.=GetSQLValueString($aujourdhui, "text");
				}
				else if($champ=='datecommande')
				{ $liste_val.=GetSQLValueString($aujourdhui, "text");
				}*/
				else if($champ=='codemission')
				{ if($tab_commande_lie[$codecommande])
					{ $liste_val.=GetSQLValueString($un_tab_commande[$champ], "text");
					  echo '<br><b>'.$codecommande.' li&eacute;e a '.$un_tab_commande[$champ].'</b>';
					}
					else
					{ $liste_val.="''";
					}
				} 
				else if(isset($un_tab_commande[$champ]))
				{ $liste_val.=GetSQLValueString($un_tab_commande[$champ], "text");
				}
				else
				{ $liste_val.="''";
				}
			}
			$updateSQL = "insert into commande (".$liste_champs.") values (".$liste_val.")";
			if($test_sans_copie==false)
			{ mysql_query($updateSQL) or  die(mysql_error());
			}
			echo '<br>'.$updateSQL;
			if(isset($tab_commandeimputationbudget[$codecommande]))
			{ foreach($tab_commandeimputationbudget[$codecommande] as $virtuel_ou_reel=>$tab_commandeimputationbudget_virtuel_ou_reel)
				{ foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$un_tab_commandeimputationbudget)
					{ if(!isset($tab_creditfinanciercoutcontrateotp[$virtuel_ou_reel][$un_tab_commandeimputationbudget['codetypecredit']][$un_tab_commandeimputationbudget['codecentrefinancier']][$un_tab_commandeimputationbudget['codecentrecout']][$un_tab_commandeimputationbudget['codecontrat']][$un_tab_commandeimputationbudget['codeeotp']]))
						{ echo '<br><b>imputation orig non trouvee en dest : ';
							$un_tab_commandeimputationbudget['codetypecredit']=$tab_substitutionimputation[$codesubstitution]['codetypecredit'];
							$un_tab_commandeimputationbudget['codecentrefinancier']=$tab_substitutionimputation[$codesubstitution]['codecentrefinancier'];
							$un_tab_commandeimputationbudget['codecentrecout']=$tab_substitutionimputation[$codesubstitution]['codecentrecout'];
							if($virtuel_ou_reel=='0')
							{ echo 'contrat '.$un_tab_commandeimputationbudget['codecontrat'].' substitu&eacute; par dotation  UL : '.$tab_substitutionimputation[$codesubstitution]['codecontrat'];
								$un_tab_commandeimputationbudget['codecontrat']=$tab_substitutionimputation[$codesubstitution]['codecontrat'];
							}
							else
							{ echo 'eotp '.$un_tab_commandeimputationbudget['codeeotp'].' substitu&eacute; par dotation UL : '.$tab_substitutionimputation[$codesubstitution]['codeeotp'];;
								$un_tab_commandeimputationbudget['codeeotp']=$tab_substitutionimputation[$codesubstitution]['codeeotp'];
							}
							echo '</b>';
						}
						echo '<br>'.$numordre.' : ';
						$first=true;
						$liste_champs="";$liste_val="";
						foreach($tab_champs_commandeimputationbudget as $champ)
						{	$liste_champs.=($first?"":",").$champ;
							$liste_val.=($first?"":",");
							$first=false;
							if($champ=='codecommande')
							{ $liste_val.=GetSQLValueString($codecommande, "text");
							}
							else if(isset($un_tab_commandeimputationbudget[$champ]))
							{ $liste_val.=GetSQLValueString($un_tab_commandeimputationbudget[$champ], "text");
							}
							else
							{ $liste_val.="''";echo '<br>autre champ : <b>'.$champ.'</b>';
							}
						}
						$updateSQL = "insert into commandeimputationbudget (".$liste_champs.") values (".$liste_val.")";
						if($test_sans_copie==false)
						{ mysql_query($updateSQL);
						}
						echo '<br>'.$updateSQL;
					}
				}
			}
			if(isset($tab_commandestatutvisa[$codecommande]))
			{ foreach($tab_commandestatutvisa[$codecommande] as $codestatutvisa=>$un_tab_commandestatutvisa)
				{ echo '<br> '.$codestatutvisa.' : ';
					$first=true;
					$liste_champs="";$liste_val="";
					foreach($tab_champs_commandestatutvisa as $champ)
					{	$liste_champs.=($first?"":",").$champ;
						$liste_val.=($first?"":",");
						$first=false;
						if($champ=='codecommande')
						{ $liste_val.=GetSQLValueString($codecommande, "text");
						}
						else if(isset($un_tab_commandestatutvisa[$champ]))
						{ $liste_val.=GetSQLValueString($un_tab_commandestatutvisa[$champ], "text");
						}
						else
						{ $liste_val.="''";echo '<br>autre champ : <b>'.$champ.'</b>';
						}
					}
					$updateSQL = "insert into commandestatutvisa (".$liste_champs.") values (".$liste_val.")";
					if($test_sans_copie==false)
					{ mysql_query($updateSQL);
					}
					echo '<br>'.$updateSQL;
				}
			}
			// 07/03/2016 ajout commandemigo et commandemigoliquidation
			if(isset($tab_commandemigo[$codecommande]))
			{ foreach($tab_commandemigo[$codecommande] as $codemigo=>$un_tab_commandemigo)
				{ echo '<br> '.$codemigo.' : ';
					$first=true;
					$liste_champs="";$liste_val="";
					foreach($tab_champs_commandemigo as $champ)
					{	$liste_champs.=($first?"":",").$champ;
						$liste_val.=($first?"":",");
						$first=false;
						if($champ=='codecommande')
						{ $liste_val.=GetSQLValueString($codecommande, "text");
						}
						else if(isset($un_tab_commandemigo[$champ]))
						{ $liste_val.=GetSQLValueString($un_tab_commandemigo[$champ], "text");
						}
						else
						{ $liste_val.="''";echo '<br>autre champ : <b>'.$champ.'</b>';
						}
					}
					$updateSQL = "insert into commandemigo (".$liste_champs.") values (".$liste_val.")";
					if($test_sans_copie==false)
					{ mysql_query($updateSQL);
					}
					echo '<br>'.$updateSQL;
					
					if(isset($tab_commandemigoliquidation[$codecommande][$codemigo]))
					{ foreach($tab_commandemigoliquidation[$codecommande][$codemigo] as $codemigoliquidation=>$un_tab_commandemigoliquidation)
						{ echo '<br> '.$codemigoliquidation.' : ';
							$first=true;
							$liste_champs="";$liste_val="";
							foreach($tab_champs_commandemigoliquidation as $champ)
							{	$liste_champs.=($first?"":",").$champ;
								$liste_val.=($first?"":",");
								$first=false;
								if($champ=='codecommande')
								{ $liste_val.=GetSQLValueString($codecommande, "text");
								}
								else if(isset($un_tab_commandemigoliquidation[$champ]))
								{ $liste_val.=GetSQLValueString($un_tab_commandemigoliquidation[$champ], "text");
								}
								else
								{ $liste_val.="''";echo '<br>autre champ : <b>'.$champ.'</b>';
								}
							}
							$updateSQL = "insert into commandemigoliquidation (".$liste_champs.") values (".$liste_val.")";
							if($test_sans_copie==false)
							{ mysql_query($updateSQL);
							}
							echo '<br>'.$updateSQL;
						}
					}
				}
			}
		}
		if(isset($tab_commandepj[$codecommande]))
		{ foreach($tab_commandepj[$codecommande] as $codetypepj=>$un_tab_commandepj)
			{ echo '<br><b>pj  : '.$codetypepj.'</b>';
				$first=true;
				$liste_champs="";$liste_val="";
				foreach($tab_champs_commandepj as $champ)
				{	$liste_champs.=($first?"":",").$champ;
					$liste_val.=($first?"":",");
					$first=false;
					if($champ=='codecommande')
					{ $liste_val.=GetSQLValueString($codecommande, "text");
					}
					else if(isset($un_tab_commandepj[$champ]))
					{ $liste_val.=GetSQLValueString($un_tab_commandepj[$champ], "text");
					}
					else
					{ $liste_val.="''";
					}
				}
				$updateSQL = "insert into commandepj (".$liste_champs.") values (".$liste_val.")";
				if($test_sans_copie==false)
				{ mysql_query($updateSQL) or  die(mysql_error());
				}
				echo '<br>'.$updateSQL;
			}
		}
		if(isset($rs))mysql_free_result($rs);
		if(isset($rs_fields_commande))mysql_free_result($rs_fields_commande);
		if(isset($rs_fields_commandeimputationbudget))mysql_free_result($rs_fields_commandeimputationbudget);
	}
}	
?>

</body>
</html>