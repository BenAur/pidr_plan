<?php require_once('_const_fonc.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>import_individus</title>
</head>
<body>
<?php 
$executer=isset($_POST['executer'])?$_POST['executer']:"";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $_SERVER['PHP_SELF'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css" type="text/css">
<link rel="stylesheet" href="styles/tableau_bd.css" type="text/css">
</head>
<body>
<?php ini_set('display_errors',true);

?>
	Import des individus :<br>
  - les donnees de codification, supposées exister dans des tables, sont lues dans des tableaux separes pour insertion des lignes dans les tables :<br>
  &nbsp;&nbsp;&nbsp;- bap<br>
  &nbsp;&nbsp;&nbsp;- cat<br>
  &nbsp;&nbsp;&nbsp;- civilite<br>
  &nbsp;&nbsp;&nbsp;- corps<br>
  &nbsp;&nbsp;&nbsp;- corpsmodefinancement<br>
	&nbsp;&nbsp;&nbsp;- diplome<br>
  &nbsp;&nbsp;&nbsp;- ed<br>
  &nbsp;&nbsp;&nbsp;- etab<br>
  &nbsp;&nbsp;&nbsp;- grade<br>
  &nbsp;&nbsp;&nbsp;- lieu<br>
  &nbsp;&nbsp;&nbsp;- modefinancement<br>
  &nbsp;&nbsp;&nbsp;- ouinon<br>
  &nbsp;&nbsp;&nbsp;- pays<br>
  &nbsp;&nbsp;&nbsp;- prog<br>
  &nbsp;&nbsp;&nbsp;- sectioncnrs<br>
  &nbsp;&nbsp;&nbsp;- sectioncnu<br>
  &nbsp;&nbsp;&nbsp;- seq_number<br>
  &nbsp;&nbsp;&nbsp;- situationprofessionnelle<br>
  &nbsp;&nbsp;&nbsp;- statutpers<br>
  &nbsp;&nbsp;&nbsp;- statutsujet<br>
  &nbsp;&nbsp;&nbsp;- statutvisa<br>
  &nbsp;&nbsp;&nbsp;- structure<br>
  &nbsp;&nbsp;&nbsp;- typepjindividu<br>
  &nbsp;&nbsp;&nbsp;- typeprofession_postdoc<br>
  &nbsp;&nbsp;&nbsp;- typestage<br>
  &nbsp;&nbsp;&nbsp;- typesujet<br>
  
  
	<form name="_import_individus.php" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	<input type="submit" name="executer" value="executer"> 
	</form>
<?php 
if($executer=="executer")
{	$rep='___individus_imopa';
 	$fichier=$rep.'/effectifs31janv2017v2.xlsx';
	$objPHPExcel = PHPExcel_IOFactory::load($fichier) or die(mysql_error());
	$objWorksheet = $objPHPExcel-> getSheetByName('Liste des personnels') or die(mysql_error());
	
 	mysql_query("delete from individu where codeindividu<>''") or die(mysql_error());
	mysql_query("delete from individusejour") or die(mysql_error());
	mysql_query("delete from individued") or die(mysql_error());
	mysql_query("delete from individuemploi") or die(mysql_error());
	mysql_query("delete from individustatutvisa") or die(mysql_error());
	mysql_query("delete from individusujet") or die(mysql_error());
	mysql_query("delete from individutheme") or die(mysql_error());
	mysql_query("delete from individuthese") or die(mysql_error());
	mysql_query("delete from gesttheme") or die(mysql_error());
	mysql_query("delete from lieuagentprevention") or die(mysql_error());
	mysql_query("delete from secrsite where codesite<>'' and codesecrsite<>''") or die(mysql_error());
	mysql_query("delete from structureindividu") or die(mysql_error());
	mysql_query("delete from sujet where codesujet<>''") or die(mysql_error());
	mysql_query("delete from sujetdir") or die(mysql_error());
	mysql_query("delete from sujettheme") or die(mysql_error());
	mysql_query("delete from tracelogin") or die(mysql_error());  /**/
	
	?>
  <table border="1" width="100%" class="data" id="table_results">
  <?php
	$numsejour='01';$numemploi='01';
	$datedeb_defaut="1980/01/01";
	$tables=array('individu','individusejour','individuemploi','individutheme','individuthese');
	$tab_individu=array();
	$codeindividu="-1";
	$codesujet="-1";
	$liste_deux_theme=''; 
	for($l=2;$l<=124;$l++)
	{ $codeindividu=str_pad((string)((int)$codeindividu+1), 5, "0", STR_PAD_LEFT);
		$codecorps=str_pad((string)($objWorksheet->getCellByColumnAndRow(6,$l)->getValue()), 2, "0", STR_PAD_LEFT);
		if($codecorps=='51')
		{ $tables=array('individu','individusejour','individuemploi','individutheme','individuthese');
		}
		else
		{ $tables=array('individu','individusejour','individuemploi','individutheme');
		}
		$tab_individu[$codeindividu]=utf8_decode($objWorksheet->getCellByColumnAndRow(1,$l)->getValue());
		$adresse=utf8_decode($objWorksheet->getCellByColumnAndRow(14,$l)->getValue());
		if(trim($adresse)=='')
		{ $tab_adresse_pers['adresse_pers']='';
			$tab_adresse_pers['codepostal_pers']='';
			$tab_adresse_pers['ville_pers']='';
		}
		else
		{ $tab= explode(" - ", $adresse);
			$tab_adresse_pers['adresse_pers']=$tab[0];
			$tab= explode(" ", $tab[1]);
			$tab_adresse_pers['codepostal_pers']=$tab[0];
			$tab_adresse_pers['ville_pers']=$tab[1];
		}
		$tab_adresse_pers['codepays_pers']='079';
		foreach($tables as $table) 
		{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				$liste_champs.=($first?"":",").$Field;
				$liste_val.=($first?"":",");
				$first=false;
				if($Field=='codeindividu')
				{ $liste_val.=GetSQLValueString($codeindividu, "text");
				}
				else if(($table=='individusejour' || $table=='individuthese' || $table=='individutheme') && $Field=='numsejour')
				{	$liste_val.=GetSQLValueString($numsejour, "text");
				}
				else if($table=='individuemploi' && $Field=='numemploi')
				{	$liste_val.=GetSQLValueString($numemploi, "text");
				}
				else if($table=='individusejour' && $Field=='codecreateur')
				{ $liste_val.="''";
				}
				else if($table=='individusejour' && $Field=='codemodifieur')
				{ $liste_val.="''";
				}
				else if($table=='individusejour' && $Field=='date_creation')
				{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
				}
				else if($table=='individusejour' && $Field=='date_modif')
				{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
				}
				else if($table=='individu' && $Field=='codeciv') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(3,$l)->getValue(); 
					$liste_val.=GetSQLValueString($cell=='H'?'1':'2', "text");
				}
				else if($table=='individu' && $Field=='nom') 
				{ $cell=utf8_decode($objWorksheet->getCellByColumnAndRow(1,$l)->getValue())/*  */; 
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if($table=='individu' && $Field=='prenom') 
				{ $cell=utf8_decode($objWorksheet->getCellByColumnAndRow(2,$l)->getValue())/*  */; 
					$liste_val.=GetSQLValueString(ucfirst(strtolower($cell)), "text");
				}
				else if($table=='individu' && ($Field=='date_naiss')) 
				{ $cell=$objWorksheet->getCellByColumnAndRow(4,$l)->getValue();
					if($cell!='')
					{ $cell=PHPExcel_Style_NumberFormat::toFormattedString($cell, 'YYYY/MM/DD');
					}
					$liste_val.=GetSQLValueString($cell, "text");
				}					
				else if($table=='individu' && $Field=='num_insee') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(13,$l)->getValue(); 
					$liste_val.=GetSQLValueString(str_replace(' ','',$cell), "text");
				}
				else if($table=='individu' && ($Field=='adresse_pers' || $Field=='codepostal_pers' || $Field=='ville_pers' || $Field=='codepays_pers')) 
				{ $liste_val.=GetSQLValueString($tab_adresse_pers[$Field], "text");
				}
				else if($table=='individu' && ($Field=='codepays_naiss' || $Field=='codenat')) 
				{ $liste_val.=GetSQLValueString('079', "text");
				}
				else if($table=='individu' && ($Field=='codepostal_naiss' || $Field=='ville_naiss')) 
				{ $liste_val.=GetSQLValueString('-', "text");
				}
				else if($table=='individu' && $Field=='email') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(16,$l)->getValue(); 
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if($table=='individu' && $Field=='telport') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(15,$l)->getValue();
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if($table=='individu' && $Field=='num_bureau') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(19,$l)->getValue(); 
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if($table=='individu' && $Field=='tel') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(20,$l)->getValue(); 
					if($cell=='')
					{ $liste_val.="''";
					}
					else
					{ $liste_val.=GetSQLValueString('03836'.$cell, "text");
					}
				}
				else if($table=='individu' && $Field=='hdr') 
				{ $cell=$objWorksheet->getCellByColumnAndRow(8,$l)->getValue(); 
					$liste_val.=GetSQLValueString(strtolower($cell), "text");
				}
				else if($table=='individu' && ($Field=='login' || $Field=='passwd')) 
				{ $nomprenom=strtolower(utf8_decode($objWorksheet->getCellByColumnAndRow(1,$l)->getValue().$objWorksheet->getCellByColumnAndRow(2,$l)->getValue()));
					$nom=strtolower(utf8_decode($objWorksheet->getCellByColumnAndRow(1,$l)->getValue()));
					if($nomprenom=='gendpascal')
					{ if($Field=='login')
						{ $liste_val.=GetSQLValueString('gend', "text");
						}
						else
						{ $liste_val.=GetSQLValueString('15dec', "text");
						}
					}
					else if($nomprenom=='lorcinkarine' || $nomprenom=='chartierflorence'  || $nomprenom=='jouzeaujean-yves' || $nomprenom=='charpentierbruno')
					{ $liste_val.=GetSQLValueString($nom, "text");
					}
					else
					{ $liste_val.=GetSQLValueString($codeindividu."#sans login", "text");
					}
				}
				else if($table=='individu' && $Field=='codelieu') 
				{  $liste_val.=GetSQLValueString('01', "text");
				}
				else if($table=='individu' && $Field=='codeed') 
				{  $liste_val.=GetSQLValueString('01', "text");
				}
				else if($table=='individusejour' && $Field=='codecorps')
				{ $cell=$objWorksheet->getCellByColumnAndRow(6,$l)->getValue(); 
					$cell=str_pad((string)($cell), 2, "0", STR_PAD_LEFT);
					$liste_val.=GetSQLValueString($cell, "text");
					if((string)$cell=='51')
					{ $codesujet=str_pad((string)((int)$codesujet+1), 5, "0", STR_PAD_LEFT);
						$tab_sujet[$codesujet]['titre_fr']=utf8_decode($objWorksheet->getCellByColumnAndRow(23,$l)->getValue());
						$tab_sujet[$codesujet]['datedeb_sujet']=PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(11,$l)->getValue(), 'YYYY/MM/DD');
						$tab_sujet[$codesujet]['datefin_sujet']=PHPExcel_Style_NumberFormat::toFormattedString($objWorksheet->getCellByColumnAndRow(12,$l)->getValue(), 'YYYY/MM/DD');
						$tab_sujet[$codesujet]['codetypesujet']='03';						
						$tab_sujet[$codesujet]['codestatutsujet']='P';
						$tab_sujet[$codesujet]['NOMDIR']=utf8_decode($objWorksheet->getCellByColumnAndRow(24,$l)->getValue());
						$tab_sujet[$codesujet]['codeetudiant']=$codeindividu;
						$tab_sujet[$codesujet]['codetheme']=str_pad((string)($objWorksheet->getCellByColumnAndRow(10,$l)->getValue()), 2, "0", STR_PAD_LEFT);
					}
				}
				else if($table=='individusejour' && $Field=='codegrade')
				{ $cell=$objWorksheet->getCellByColumnAndRow(7,$l)->getValue(); 
					$cell=str_pad((string)($cell), 2, "0", STR_PAD_LEFT);
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if(($table=='individusejour' && ($Field=='datedeb_sejour' || $Field=='datedeb_sejour_prevu'))
							|| ($table=='individuthese' && $Field=='date_preminscr')
						  || ($table=='individuemploi' && $Field=='datedeb_emploi')
						  || ($table=='individutheme' && $Field=='datedeb_theme'))
				{ $cell=$objWorksheet->getCellByColumnAndRow(11,$l)->getValue(); 
					if($cell!='')
					{ $cell=PHPExcel_Style_NumberFormat::toFormattedString($cell, 'YYYY/MM/DD');
					}
					else
					{ $cell=$datedeb_defaut;
					}
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if($table=='individuthese' && $Field=="sujet_verrouille")
				{ $liste_val.=GetSQLValueString('oui', "text");
				}
				else if($table=='individuthese' && $Field=="codeed_these")
				{ $liste_val.=GetSQLValueString('01', "text");
				}
				else if(($table=='individusejour' && ($Field=='datefin_sejour' || $Field=='datefin_sejour_prevu')) || ($table=='individuemploi' && $Field=='datefin_emploi'))
				{ $cell=$objWorksheet->getCellByColumnAndRow(12,$l)->getValue(); 
					if($cell!='')
					{ $cell=PHPExcel_Style_NumberFormat::toFormattedString($cell, 'YYYY/MM/DD');
					}
					else
					{ $cell='';
					}
					$liste_val.=GetSQLValueString($cell, "text");
				}
				else if($table=='individuemploi' && $Field=='codeetab')
				{ $cell=$objWorksheet->getCellByColumnAndRow(9,$l)->getValue();
					if($cell=='U LORRAINE')
					{ $codeetab='07';
					}
					else if($cell=='CNRS')
					{ $codeetab='05';
					}
					else if($cell=='INSERM')
					{ $codeetab='09';
					}
					else if($cell=='CHU')
					{ $codeetab='06';
					}
					$liste_val.=GetSQLValueString($codeetab, "text");
				}
				else if($table=='individutheme' && $Field=='codetheme')
				{ $cell=$objWorksheet->getCellByColumnAndRow(10,$l)->getValue();
					$tab=explode("&", $cell);
					if(count($tab)>1)
					{ $liste_deux_theme.='<br><b>2 themes : </b>'.$codeindividu.' '.$cell; 
					}
					$codetheme=str_pad((string)($tab[0]), 2, "0", STR_PAD_LEFT);
					$liste_val.=GetSQLValueString($codetheme, "text");
				}
				else
				{ $liste_val.="''";
				}
			}
			$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
			echo '<br>'.$updateSQL;
			mysql_query($updateSQL) or  die(mysql_error());
		}
		$nomprenom=strtolower(utf8_decode($objWorksheet->getCellByColumnAndRow(1,$l)->getValue().$objWorksheet->getCellByColumnAndRow(2,$l)->getValue()));
		if($nomprenom=='lorcinkarine' || $nomprenom=='chartierflorence')
		{ $updateSQL = "insert into gesttheme (codegesttheme,codetheme) values (".GetSQLValueString($codeindividu, "text").",'00')";
			echo '<br>'.$updateSQL;
			mysql_query($updateSQL) or  die(mysql_error());
		}
		$codestructure="";
		if($nomprenom=='jouzeaujean-yves'){ $codestructure='00';$codeindividu_jouzeaujeanyves=$codeindividu;}
		if($nomprenom=='chartierflorence'){$codestructure='12';}
		if($nomprenom=='lorcinkarine'){$codestructure='13';$codeindividu_lorcinkarine=$codeindividu;}
		if($codestructure!='') 
		{ $updateSQL = "insert into structureindividu (codestructure,codeindividu,datedeb_struct,datefin_struct,estresp) values (".GetSQLValueString($codestructure, "text").",".GetSQLValueString($codeindividu, "text").",'1980/01/01','1980/01/01','oui')";
			echo '<br>'.$updateSQL;
			mysql_query($updateSQL) or  die(mysql_error());
		}
	}
	
	$updateSQL="update individusejour set codecreateur=".GetSQLValueString($codeindividu_lorcinkarine, "text").
							", codemodifieur=".GetSQLValueString($codeindividu_lorcinkarine, "text").
							", codegesttheme=".GetSQLValueString($codeindividu_lorcinkarine, "text").
							", codereferent=".GetSQLValueString($codeindividu_jouzeaujeanyves, "text");
	echo '<br>'.$updateSQL;
	mysql_query($updateSQL) or  die(mysql_error());
	$updateSQL="update seq_number set currentnumber=".GetSQLValueString($codeindividu, "text")." where nomtable='individu'";
	echo '<br>'.$updateSQL;
	mysql_query($updateSQL) or  die(mysql_error());
	for($i=-1;$i<=0+$codeindividu;$i++)
	{ $codeind=str_pad((string)($i+1), 5, "0", STR_PAD_LEFT);
		foreach(array('01','03') as $codestatutvisa)
		{ $updateSQL = "insert into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa)".
										" values (".GetSQLValueString($codeind, "text").",'01',".GetSQLValueString($codestatutvisa, "text").
										",".GetSQLValueString($codeindividu_lorcinkarine, "text").",".GetSQLValueString(date("Y/m/d"), "text").")";
			echo '<br>'.$updateSQL;
			mysql_query($updateSQL) or  die(mysql_error());
		}
	}
	echo $liste_deux_theme;
	
	foreach($tab_sujet as $codesujet=>$un_sujet)
	{ foreach(array('sujet','sujetdir','sujettheme') as $table) 
		{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
			$first=true;
			$liste_champs="";$liste_val="";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				$liste_champs.=($first?"":",").$Field;
				$liste_val.=($first?"":",");
				$first=false;
				if($Field=='codesujet')
				{ $liste_val.=GetSQLValueString($codesujet, "text");
				}
				else if($table=='sujet' && isset($tab_sujet[$codesujet][$Field]))
				{ $liste_val.=GetSQLValueString($tab_sujet[$codesujet][$Field], "text");
				}
				else if($table=='sujetdir' && $Field=='codedir')
				{ $codedir=array_search ($tab_sujet[$codesujet]['NOMDIR'],$tab_individu);
					$codeetudiant=$tab_sujet[$codesujet]['codeetudiant'];
					$liste_val.=GetSQLValueString($codedir, "text");
					$updateSQL="update individusejour set codereferent=".GetSQLValueString($codedir, "text")." where codeindividu=".GetSQLValueString($codeetudiant, "text");
					echo '<br>'.$updateSQL;
					mysql_query($updateSQL) or  die(mysql_error());
					$updateSQL="insert into individusujet (codeindividu, numsejour, codesujet) values (".GetSQLValueString($codeetudiant, "text").",".GetSQLValueString('01', "text").",".GetSQLValueString($codesujet, "text").")"; 
					echo '<br>'.$updateSQL;
					mysql_query($updateSQL) or  die(mysql_error());
				}
				else if($table=='sujetdir' && $Field=='numordre')
				{ $codedir=array_search ($tab_sujet[$codesujet]['NOMDIR'],$tab_individu);
					$liste_val.=GetSQLValueString('1', "text");
				}
				else if($table=='sujettheme' && $Field=='codetheme')
				{ $liste_val.=GetSQLValueString($tab_sujet[$codesujet][$Field], "text");
				}
				else
				{ $liste_val.="''";
				}
			}
			$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
			echo '<br>'.$updateSQL;
			mysql_query($updateSQL) or  die(mysql_error());
		}
	}
	$updateSQL="update seq_number set currentnumber=".GetSQLValueString($codesujet, "text")." where nomtable='sujet'";
	echo '<br>'.$updateSQL;
	mysql_query($updateSQL) or  die(mysql_error());
	?>
  </table>
  <?php 
}?>
</body>
</html>