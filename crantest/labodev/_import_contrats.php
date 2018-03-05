<?php require_once('_const_fonc.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>import_contrats</title>
</head>
<body>
<?php 
$codeuser=deconnecte_ou_connecte();
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
<?php 
if($executer!="executer")
{?>
	Import des contrats :<br>
  - les donnees de codification, supposées exister dans des tables, sont lues dans des tableaux separes pour insertion des lignes de contrats dans les tables :<br>
  &nbsp;&nbsp;&nbsp;- $tab_rs_contrat_org_gest<br>
  &nbsp;&nbsp;&nbsp;- $tab_rs_contrat_type<br>
  &nbsp;&nbsp;&nbsp;- $tab_rs_contrat_org_financeur<br>
  &nbsp;&nbsp;&nbsp;- $tab_rs_contrat_classif<br>
  - l'enregistrement contrat doit exister dans seq_number<br><br>
	<form name="import_contrats.php" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
	Lancer l'import des contrats<input type="submit" name="executer" value="executer"> 
	</form>
<?php 
}
else
{	
 	mysql_query("delete from contrat where codecontrat<>''") or die(mysql_error());
	mysql_query("delete from contratmontantannee") or die(mysql_error());
	mysql_query("delete from contratmontantdetail") or die(mysql_error());
	mysql_query("delete from contratpart") or die(mysql_error());
	mysql_query("delete from contratpj") or die(mysql_error());
	mysql_query("delete from cont_part where codepart<>''") or die(mysql_error());
	$tab_cont_part=array();
	$tab_cont_part['']['codepart']='';
	$rep_original="";
	$fichier=$rep_original."Contrats_CRAN_2012_09_21_corrige_pour_export.txt";
	$contenu="";
	$nbcol_hors_montantdetail=22; 
	// lecture du fichier
	if (!($fp_fichier = @fopen($fichier, "r"))) 
	{ die("Impossible d'ouvrir le document "); 
	}
	while ($data = fread($fp_fichier, 4096)) 
	{ $contenu=$contenu. $data;
	}
	fclose($fp_fichier);
	
	?>
  <table border="1" width="100%" class="data" id="table_results">
  <tr class="head">
  <td>erreurs</td>
	<td>codecontrat<br>0</td>
	<?php $tab=explode("\n",$contenu);
	//Extrait libellés de colonnes de la ligne entete
	$tab_libchampentete=explode("\t",$tab['0']);
	$i=0;
	foreach($tab_libchampentete as $unlibchampentete)
	{ $i++;
		echo '<td align="center">'.$unlibchampentete.'<br>'.$i.'</td>';
	}?>
  </tr>
  <tr>
  	<td>erreurs</td>
		<td>codecontrat</td>
		<td>datedeb_contrat<br><?php echo $tab_libchampentete[0]; ?></td>
		<td>duree_mois<br><?php echo $tab_libchampentete[1]; ?></td>
		<td>datefin_contrat<br><?php echo $tab_libchampentete[2]; ?></td>
		<td>codeorggest<br><?php echo $tab_libchampentete[3]; ?></td>
		<td>ref_contrat<br><?php echo $tab_libchampentete[4]; ?></td>
		<td>eotp<br><?php echo $tab_libchampentete[5]; ?></td>
		<td>ref_prog_long<br><?php echo $tab_libchampentete[6]; ?></td>
		<td>codetype<br><?php echo $tab_libchampentete[7]; ?></td>
		<td>codeorgfinanceur<br><?php echo $tab_libchampentete[8]; ?></td>
		<td>codetheme<br><?php echo $tab_libchampentete[9]; ?></td>
		<td>coderespscientifique<br><?php echo $tab_libchampentete[10]; ?></td>
		<td>note=Partenaires<br><?php echo $tab_libchampentete[11]; ?></td>
		<td><br><?php echo $tab_libchampentete[12]; ?></td>
		<td><br><?php echo $tab_libchampentete[13]; ?></td>
		<td>codeclassif<br><?php echo $tab_libchampentete[14]; ?></td>
		<td><br><?php echo $tab_libchampentete[15]; ?></td>
		<td>sujet<br><?php echo $tab_libchampentete[16]; ?></td>
		<td>permanent_mois<br><?php echo $tab_libchampentete[17]; ?></td>
		<td>personnel_mois<br><?php echo $tab_libchampentete[18]; ?></td>
		<td>ht_ttc<br><?php echo $tab_libchampentete[19]; ?></td>
		<td>montant_ht<br><?php echo $tab_libchampentete[20]; ?></td>
		<td>note_pg<br><?php echo $tab_libchampentete[21]; ?></td>

		<?php		
		for($i=$nbcol_hors_montantdetail;$i<count($tab_libchampentete)-1;$i++)
		{?> <td><?php echo $tab_libchampentete[$i] ?></td>
		<?php		
		}?>
  </tr>
	
	<?php		
	unset($tab['0']);//suppr ligne entete
	unset($tab[count($tab)]);//suppr ligne fin
	// tableaux de codification
	$tab_individu=array();
	// commentaire dans la requête pour accepter PIC
	$query_individu= "SELECT individu.codeindividu,nom,prenom,datedeb_sejour,datefin_sejour". 
									 " FROM individu,individusejour where individu.codeindividu=individusejour.codeindividu"./* ,individusejour,corps,statutpers".
									 " WHERE individu.codeindividu=individusejour.codeindividu and individusejour.codecorps=corps.codecorps".
									 " and corps.codestatutpers=statutpers.codestatutpers".
									 " and corps.codestatutpers='01'".
									 " and individu.codeindividu<>'' and (corps.codecat='01' or corps.codecat='06')".
									 " UNION SELECT codeindividu,'' as nom,'' as prenom from individu WHERE individu.codeindividu=''". */
									 " ORDER BY nom,prenom";
	$rs_individu=mysql_query($query_individu) or die(mysql_error());
	while($row_rs_individu=mysql_fetch_assoc($rs_individu))
	{ $tab_individu[strtolower($row_rs_individu['nom']).' '.strtolower(substr($row_rs_individu['prenom'],0,1))]=$row_rs_individu['codeindividu'];
	}
	
	$query_rs_cont_orggest =	"SELECT codeorggest,libcourtorggest as liborggest FROM cont_orggest";
	$rs_cont_orggest = mysql_query($query_rs_cont_orggest);
	while($row_rs_cont_orggest=mysql_fetch_assoc($rs_cont_orggest))
	{ $tab_rs_cont_orggest[$row_rs_cont_orggest['liborggest']]=$row_rs_cont_orggest['codeorggest'];
	}
	
	$query_rs_cont_type =	"SELECT codetype,libcourttype as libtype FROM cont_type";
	$rs_cont_type = mysql_query($query_rs_cont_type);
	while($row_rs_cont_type=mysql_fetch_assoc($rs_cont_type))
	{ $tab_rs_cont_type[$row_rs_cont_type['libtype']]=$row_rs_cont_type['codetype'];
	}
	
	$query_rs_cont_secteur =	"SELECT codesecteur,libcourtsecteur as libsecteur FROM cont_secteur";
	$rs_cont_secteur = mysql_query($query_rs_cont_secteur);
	while($row_rs_cont_secteur=mysql_fetch_assoc($rs_cont_secteur))
	{ $tab_rs_cont_secteur[$row_rs_cont_secteur['libsecteur']]=$row_rs_cont_secteur['codesecteur'];
	}
	
	$query_rs_cont_orgfinanceur =	"SELECT codeorgfinanceur,libcourtorgfinanceur as liborgfinanceur FROM cont_orgfinanceur";
	$rs_cont_orgfinanceur = mysql_query($query_rs_cont_orgfinanceur);
	while($row_rs_cont_orgfinanceur=mysql_fetch_assoc($rs_cont_orgfinanceur))
	{ $tab_rs_cont_orgfinanceur[$row_rs_cont_orgfinanceur['liborgfinanceur']]=$row_rs_cont_orgfinanceur['codeorgfinanceur'];
	}
	
	$query_rs_cont_classif =	"SELECT codeclassif,numclassif FROM cont_classif";
	$rs_cont_classif = mysql_query($query_rs_cont_classif);
	while($row_rs_cont_classif=mysql_fetch_assoc($rs_cont_classif))
	{ $tab_rs_cont_classif[$row_rs_cont_classif['numclassif']]=$row_rs_cont_classif['codeclassif'];
	}
	
	$query_rs_theme =	"SELECT codestructure as codetheme,libcourt_fr as libtheme FROM structure where esttheme='oui'";
	$rs_theme = mysql_query($query_rs_theme);
	while($row_rs_theme=mysql_fetch_assoc($rs_theme))
	{ $tab_rs_theme[$row_rs_theme['libtheme']]=$row_rs_theme['codetheme'];
	}
	
	$class='even';
	$nb_cont_part=0;
	foreach($tab as $num=>$ligne)
	{ $class=($class=='odd'?'even':'odd');
		echo '<tr class="'.$class.'">';
		echo '<td>';
		$tab_champ=explode("\t",$ligne);
		$codecontrat=str_pad($num,5,"0",STR_PAD_LEFT);
		$tab_un_contrat[0]=array('codecontrat'=>$codecontrat);
		$tab_un_contrat[1]=array('datedeb_contrat'=>jjmmaaaa2date(substr($tab_champ['0'],0,2),substr($tab_champ['0'],3,2),substr($tab_champ['0'],6,4)));
		$tab_un_contrat[2]=array('duree_mois'=>str_replace(',','.',$tab_champ['1']));
		$tab_un_contrat[3]=array('datefin_contrat'=>jjmmaaaa2date(substr($tab_champ['2'],0,2),substr($tab_champ['2'],3,2),substr($tab_champ['2'],6,4)));
		$tab_un_contrat[4]=array('codeorggest'=>$tab_rs_cont_orggest[$tab_champ['3']]);//code du libelle
		$tab_un_contrat[5]=array('ref_contrat'=>$tab_champ['4']);
		$tab_un_contrat[6]=array('eotp'=>$tab_champ['5']);
		$tab_un_contrat[7]=array('ref_prog_long'=>$tab_champ['6']);
		$tab_un_contrat[8]=array('codetype'=>$tab_rs_cont_type[$tab_champ['7']]);//code du libelle
		$tab_un_contrat[9]=array('codeorgfinanceur'=>$tab_rs_cont_orgfinanceur[$tab_champ['8']]);//code du libelle
		$tab_un_contrat[10]=array('codetheme'=>$tab_rs_theme[$tab_champ['9']]);
		$tab_unresp=explode(" ",$tab_champ['10']);$nomresp=$tab_unresp[0];$initialeprenom=substr($tab_unresp[1],0,1);
		if(!array_key_exists(strtolower($nomresp).' '.strtolower($initialeprenom),$tab_individu))
		{ echo '<span class="rougecalibri10">resp '.$nomresp.' non trouve</span>';
		}
		$tab_un_contrat[11]=array('coderespscientifique'=>(array_key_exists(strtolower($nomresp).' '.strtolower($initialeprenom),$tab_individu)?$tab_individu[strtolower($nomresp).' '.strtolower($initialeprenom)]:''));
		$tab_un_contrat[12]=array('note'=>$tab_champ['21']);
		$tab_un_contrat[13]=array(''=>'cp');
		$tab_un_contrat[14]=array('libclassif'=>$tab_champ['13']);//libelle
		$tab_un_contrat[15]=array('codeclassif'=>$tab_rs_cont_classif[$tab_champ['14']]);//code du libelle
		$tab_un_contrat[16]=array('codesecteur'=>$tab_rs_cont_secteur[$tab_champ['15']]);
		$tab_un_contrat[17]=array('sujet'=>$tab_champ['16']);
		$tab_un_contrat[18]=array('permanent_mois'=>str_replace(',','.',$tab_champ['17']));
		$tab_un_contrat[19]=array('personnel_mois'=>str_replace(',','.',$tab_champ['18']));
		$tab_un_contrat[20]=array('ht_ttc'=>$tab_champ['19']);
		$tab_un_contrat[21]=array('montant_ht'=>str_replace(' ','',str_replace(',','.',$tab_champ['20'])));
		echo '</td>';
		for($numchamp=0;$numchamp<count($tab_un_contrat);$numchamp++)
		{ list($champ,$valchamp)=each($tab_un_contrat[$numchamp]);
			echo '<td>'./* $champ.'=>'. */$valchamp.'</td>';
			//echo '<td></td>';
		}
		$rs_fields = mysql_query('SHOW COLUMNS FROM contrat');
		$first=true;
		$liste_champs="";$liste_val="";
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
			$liste_val.=($first?"":",");
			$first=false;
			$numchamp=0;
			$trouve=false;
			while($numchamp<count($tab_un_contrat) && !$trouve)
			{ reset($tab_un_contrat[$numchamp]);
				list($champ,$valchamp)=each($tab_un_contrat[$numchamp]);
				if($row_rs_fields['Field']==$champ)
				{ $trouve=true;
				}
				else
				{ $numchamp++;
				}
			}
			if(!$trouve)
			{ if($row_rs_fields['Field']=='traite_admingestfin')
				{ $valchamp="oui";
				}
				else
				{ $valchamp="";
				}
			}
			$liste_val.=GetSQLValueString($valchamp, "text");
		}//fin while
		$query_rs_contrat = "insert into contrat (".$liste_champs.") values (".$liste_val.")";

		for($i=$nbcol_hors_montantdetail;$i<count($tab_champ);$i++)
		{ echo '<td>'.str_replace(' ','',str_replace(',','.',$tab_champ[$i])).' '.($tab_champ[$i]!=''?'('.$i.')':'').'</td>';
		}
		echo '</tr>';
		echo '<tr class="'.$class.'">';
		echo '<td colspan="'.(count($tab_champ)+1).'">';
		echo $query_rs_contrat;
		mysql_query($query_rs_contrat) or die(mysql_error());
		
		//partenaires
		$tab_nom_part_du_contrat=explode('#',$tab_champ['11']);
		echo '<br>'.$tab_champ['11'];
		$numordre=0;
		foreach($tab_nom_part_du_contrat as $nom_part)
		{ $nouveaupart=false;
			if(!array_key_exists($nom_part,$tab_cont_part))
			{ $nouveaupart=true;
				$nb_cont_part++;
				$codepart=str_pad($nb_cont_part,5,0,STR_PAD_LEFT);
				$tab_cont_part[$nom_part]['codepart']=$codepart;
			}
			else
			{ $codepart=$tab_cont_part[$nom_part]['codepart'];
			}
			echo '<br>'.$codepart;
			if($nouveaupart)
			{	$rs_fields = mysql_query('SHOW COLUMNS FROM cont_part');
				$first=true;
				$liste_champs="";$liste_val="";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ $liste_champs.=($first?"":",").$row_rs_fields['Field'];
					$liste_val.=($first?"":",");
					$first=false;
					if($row_rs_fields['Field']=='codepart')
					{ $valchamp=$codepart;
					}
					else if($row_rs_fields['Field']=='adressecontactpart')
					{ $valchamp=$tab_champ['12'];
					}
					else if($row_rs_fields['Field']=='libcourtpart')
					{ $valchamp=$nom_part;
					}
					else if($row_rs_fields['Field']=='liblongpart')
					{ $valchamp=$nom_part;
					}
					else
					{ $valchamp="";
					}
					$liste_val.=GetSQLValueString($valchamp, "text");
				}
				$query_rs_cont_part = "insert into cont_part (".$liste_champs.") values (".$liste_val.")";
				mysql_query($query_rs_cont_part) or die(mysql_error());
				echo '<br>Nouveau partenaire. '.$query_rs_cont_part;
			}
			$numordre++;
			$query_rs_contratpart = "insert into contratpart (codecontrat,codepart,numordre) values ('".$codecontrat."','".$codepart."','".str_pad($numordre,2,'0',STR_PAD_LEFT)."')";
			mysql_query($query_rs_contratpart) or die(mysql_error());
			echo '<br>'.$query_rs_contratpart;
		}
		// annees, montants
		$numordre=0;
		for($i=$nbcol_hors_montantdetail;$i<count($tab_champ);$i++)
		{ //echo str_replace(' ','',str_replace(',','.',$tab_champ[$i])).' '.($tab_champ[$i]!=''?'('.$i.')':'');
			if($tab_champ[$i]!='' && strlen($tab_champ[$i])>2)//test longueur : la derniere valeur n'est pas vide mais n'est pas un montant
			{ $numordre++;
				$query_rs="insert into contratmontantannee (codecontrat, annee, montant, numordre) ".
								" values (".GetSQLValueString($codecontrat, "text").
								" ,".GetSQLValueString($tab_libchampentete[$i], "text").
								" ,".GetSQLValueString(str_replace(' ','',str_replace(',','.',$tab_champ[$i])), "text").
								" ,".GetSQLValueString($numordre, "text").
								")";
echo '<br>'.$query_rs;
				mysql_query($query_rs);
			}
		}
		echo '</td>';
		echo '</tr>';
	}
	//codecontrat dans seq_number
	$codecontrat=str_pad((string)((int)$codecontrat+1), 5, "0", STR_PAD_LEFT);  
	$rs_seq_number=mysql_query("update seq_number set currentnumber=".GetSQLValueString($codecontrat, "text")." where nomtable='contrat'") or  die(mysql_error());
	echo '</table>';
	
	if(isset($rs)) mysql_free_result($rs);
	if(isset($rs_cont_orggest)) mysql_free_result($rs_cont_orggest);
	if(isset($rs_cont_type)) mysql_free_result($rs_cont_type);
	if(isset($rs_cont_orgfinanceur)) mysql_free_result($rs_cont_orgfinanceur);
	if(isset($rs_cont_classif)) mysql_free_result($rs_cont_classif);
	 
}?>
</body>
</html>