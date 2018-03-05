<?php  require_once('_const_fonc.php'); ?>
<?php 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$pjdemo="pjdemo.pdf";
$pjfsddemo="Formulaire_vierge_zrr.xlsx";
if(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd'])
{ ?> Seul le user $GLOBALS['admin_bd'] est autorisé<br>
<?php
}
else
{ ?>User non autorisé
<?php
exit;
}
$executer=isset($_POST['executer'])?$_POST['executer']:"";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>modif_bd</title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css" type="text/css">
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
if($executer!="executer")
{?> 
	<form name="corrige_bd" method="post" action="_modif_bd_pour_demo.php">
  <br>modifie base et pj demo
  <br>
    <input type="submit" name="executer" value="executer"> 
	</form>
<?php 
}
else
{ $fichier="___divers/nom_prenom_sexe.csv";
	if (!($fp_fichier = @fopen($fichier, "r"))) 
	{ die("Impossible d'ouvrir le document "); 
	}
	$contenu='';
	while ($data = fread($fp_fichier, 4096)) 
	{ $contenu=$contenu. $data;
	}
	fclose($fp_fichier);
	$tab_nom_prenom_sexe=explode("\n",$contenu);
	foreach($tab as $num=>$ligne)
	{	$tab_champ=explode(";",$ligne);
		$nom=trim($tab_champ[0]);
		$prenom=trim($tab_champ[1]);
		$sexe=trim($tab_champ[2]);
		echo $sexe.' '.$nom.' '.$prenom.'<br>';
	}
	$query_rs="select * from individu where codeindividu <>'' order by nom";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab_individu=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_individu[$row_rs['codeindividu']]=$row_rs;
	}
	$query_rs="select structureindividu.codeindividu,structure.codelib".
						" from structure,structureindividu".
						" where structure.codestructure=structureindividu.codestructure".
						" and find_in_set(structure.codelib,'du,admingestfin,sif')";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_individurole[$row_rs['codeindividu']][$row_rs['codelib']]=true;
	}

	$num_nom_prenom_sexe=-1;
	foreach($tab_individu as $codeindividu=>$row_rs)
	{ $num_nom_prenom_sexe++;
		$un_nom_prenom_sexe=explode(";",$tab_nom_prenom_sexe[$num_nom_prenom_sexe]);
		$sexe=trim($un_nom_prenom_sexe[2]);
		$tab_individu[$codeindividu]['nom']=trim($un_nom_prenom_sexe[0]);
		$tab_individu[$codeindividu]['prenom']=trim($un_nom_prenom_sexe[1]);
		$codeciv=trim($tab_champ[2])=='M'?'1':'2';
		$nomjf='';
		$date_naiss=$row_rs['date_naiss'];
		$mois=rand (1, 12);
		$date_naiss=(substr($date_naiss,0,4)+rand(-2,2)).'/'.str_pad($mois,2,'0',STR_PAD_LEFT).'/'.str_pad(rand (1, $GLOBALS['nb_jours_du_mois'][$mois]),2,'0',STR_PAD_LEFT);
		if($tab_individu[$codeindividu]['login']=='gend')
		{ $login='gend';
		}
		else if(isset($tab_individurole[$codeindividu]['du']))
		{ $login='directeur';
			unset($tab_individurole[$codeindividu]);
		}
		else if(isset($tab_individurole[$codeindividu]['admingestfin']))
		{ $login='respadmin';
			unset($tab_individurole[$codeindividu]);
		}
		else if(isset($tab_individurole[$codeindividu]['sif']))
		{ $login='respfinance';
			foreach($tab_individurole as $un_codeindividu_de_ce_role=>$tabrole)
			{ if(isset($tab_individurole[$un_codeindividu_de_ce_role]['sif']))
				{ unset($tab_individurole[$un_codeindividu_de_ce_role]);
				}
			}
		}
		else
		{ $login=$codeindividu;
		}
		$nom=$tab_individu[$codeindividu]['nom'];
		$query_rs="update individu set nom=".GetSQLValueString($nom, "text").", nomjf=".GetSQLValueString($nomjf, "text").
							", prenom=".GetSQLValueString($tab_individu[$codeindividu]['prenom'], "text").", date_naiss=".GetSQLValueString($date_naiss, "text").
							", ville_naiss=".GetSQLValueString('ville de '.$nom, "text").
							", num_insee='', adresse_pers=".GetSQLValueString(' adresse pers de '.$nom, "text")." , adresse_admin=".GetSQLValueString(' adresse_admin de '.$nom, "text").
							", tel='', fax='', telport='',email=".GetSQLValueString(strtolower($nom).'@'.strtolower($GLOBALS['acronymelabo'].'.fr'), "text").
							", email_parti=".GetSQLValueString(strtolower($nom).'@mail.fr', "text").", codelabintel=".GetSQLValueString(str_pad(rand(10000000,99999999),8,'0',STR_PAD_LEFT), "text").
							", lienpghttp=".GetSQLValueString("http://www.".strtolower($GLOBALS['acronymelabo'].'.fr')."/".strtolower($nom), "text").
							", lienhttpenseigne=".GetSQLValueString("http://www.enseigne.fr"."/".strtolower($nom), "text").", lienlinkedin=''".
							", nom_hal=".GetSQLValueString($nom, "text").", login=".GetSQLValueString(strtolower($login), "text").", passwd=".GetSQLValueString(strtolower($GLOBALS['acronymelabo']), "text").
							" where codeindividu=".GetSQLValueString($codeindividu, "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from individu where codeindividu <>'' order by nom";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab_individu=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_individu[$row_rs['codeindividu']]=$row_rs;
	}

	$query_rs="select * from individusejour where codeindividu <>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
	}
	foreach($tab as $codeindividu=>$un_individu)
	{ $nom=$tab_individu[$codeindividu]['nom'];
		foreach($un_individu as $numsejour=>$row_rs)
		{	$query_rs="update individusejour set etab_orig=".GetSQLValueString("etab origine de ".$nom, "text").", etab_orig=".GetSQLValueString("adresse etab origine de ".$nom, "text").
								", ville_etab_orig=".GetSQLValueString("ville origine de ".$nom, "text").", note_demande_modification_fsd=''".
								", numeropieceidentite='numpi',descriptionmission=concat('description d&eacute;taill&eacute;e ',intituleposte), note=''".
								" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text");
			echo $query_rs.'<br>';
			if($database_labo=="demo12plus")
			{ mysql_query($query_rs) or die(mysql_error());
			}
		}
	}
	$query_rs="select * from individuthese where codeindividu <>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab[$row_rs['codeindividu']]=$row_rs;
	}
	foreach($tab as $codeindividu=>$row_rs)
	{ $query_rs="update individuthese set labo_accueil =".GetSQLValueString($GLOBALS['acronymelabo'], "text").", etab_these=".GetSQLValueString("UNIVERSITE DE ".$GLOBALS['acronymelabo'], "text").
								", sujet_initial=concat(SUBSTRING(sujet_initial,1,50),'...'), sujet_actualise=concat(SUBSTRING(sujet_actualise,1,50),'...')".
								", titre_these=concat(SUBSTRING(titre_these,1,50),'...'), resume_these=concat(SUBSTRING(sujet_actualise,1,50),'...')".
								", jury_rapp1_these='rapporteur 1', jury_rapp2_these='rapporteur 2', jury_autres_membres_these='autres membres du jury', note_doct='', adresse_postdoc=''".
								" where codeindividu=".GetSQLValueString($codeindividu, "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from individuemploi where codeindividu <>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab[$row_rs['codeindividu']][$row_rs['numemploi']]=$row_rs;
	}
	foreach($tab as $codeindividu=>$un_individu)
	{ $nom=$tab_individu[$codeindividu]['nom'];
		foreach($un_individu as $numemploi=>$row_rs)
		{	$query_rs="update individuemploi set eotp=concat(SUBSTRING(eotp,1,5),'...'), contrat=concat(SUBSTRING(contrat,1,6),'...')".
								", montant_mensuel_charge='0', montant_mensuel_brut='0', autreetab=if(codeetab='','autre...','')".
								" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numemploi=".GetSQLValueString($numemploi, "text");
			echo $query_rs.'<br>';
			if($database_labo=="demo12plus")
			{ mysql_query($query_rs) or die(mysql_error());
			}
		}
	}

	$query_rs="select * from sujet where codesujet <>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab[$row_rs['codesujet']]=$row_rs;
	}
	foreach($tab as $codesujet=>$row_rs)
	{ 
		$query_rs="update sujet set titre_fr =concat(SUBSTRING(titre_fr,1,10),'...'), titre_en =concat(SUBSTRING(titre_en,1,10),'...')".
								", descr_fr=concat(SUBSTRING(descr_fr,1,100),REPEAT('......... ',70)), descr_en=concat(SUBSTRING(descr_en,1,100),REPEAT('......... ',70))".
								", motscles_fr=concat(SUBSTRING(motscles_fr,1,10),'...'), motscles_en=concat(SUBSTRING(motscles_en,1,10),'...'),financement_fr='',financement_en=''".
								", ref_publis=concat(SUBSTRING(ref_publis,1,5),'...'),ref_publis_ext='', autredir2='', autredir1mail='', autredir2mail='',conditions_fr='',conditions_en=''".
								" where codesujet=".GetSQLValueString($codesujet, "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from commande where codecommande <>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab[$row_rs['codecommande']]=$row_rs;
	}
	foreach($tab as $codecommande=>$row_rs)
	{ $query_rs="update commande set libfournisseur=concat(SUBSTRING(libfournisseur,1,5),'...'), numcommande =concat(SUBSTRING(numcommande,1,5),'...')".
								", objet=concat(SUBSTRING(objet,1,10),'...'), description=concat(SUBSTRING(description,1,10),'...'), note=''".
								" where codecommande=".GetSQLValueString($codecommande, "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from commandemigo";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $query_rs="update commandemigo set nummigo=concat(SUBSTRING(nummigo,1,6),'...')".
								" where codecommande=".GetSQLValueString($row_rs['codecommande'], "text")." and codemigo=".GetSQLValueString($row_rs['codemigo'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from commandemigoliquidation";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab_cumul=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $montantliquidation=number_format($row_rs['montantliquidation']*(1+rand(-100,100)/100),2,'.','');
		if(isset($tab_cumul[$row_rs['codecommande']]))
		{ $tab_cumul[$row_rs['codecommande']]+=$montantliquidation;
		}
		else
		{ $tab_cumul[$row_rs['codecommande']]=$montantliquidation;
		}
		$query_rs="update commandemigoliquidation set numliquidation=concat(SUBSTRING(numliquidation,3,4),'...'),numfacture=concat(SUBSTRING(numfacture,1,4),'...')".
							", montantliquidation=".GetSQLValueString($montantliquidation, "text").
							" where codecommande=".GetSQLValueString($row_rs['codecommande'], "text")." and codemigo=".GetSQLValueString($row_rs['codemigo'], "text").
							" and codeliquidation=".GetSQLValueString($row_rs['codeliquidation'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from commandeimputationbudget";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ if(isset($tab_cumul[$row_rs['codecommande']]))
		{ $montantengage=number_format($tab_cumul[$row_rs['codecommande']]*(1+rand(-10,10)/100),2,'.','');
			$montantpaye=$tab_cumul[$row_rs['codecommande']];
		}
		else
		{ $montantengage=rand(0,1000);
			$montantpaye=0;
		}
		$query_rs="update commandeimputationbudget set montantengage=".GetSQLValueString($montantengage, "text").", montantpaye=".GetSQLValueString($montantpaye, "text").
							" where codecommande=".GetSQLValueString($row_rs['codecommande'], "text")." and numordre=".GetSQLValueString($row_rs['numordre'], "text")." and virtuel_ou_reel=".GetSQLValueString($row_rs['virtuel_ou_reel'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	$query_rs="select * from eotp";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $query_rs="update eotp set libcourteotp=".GetSQLValueString(substr($row_rs['libcourteotp'],0,3).'-'.$row_rs['codeeotp'], "text").",liblongeotp=".GetSQLValueString(substr($row_rs['liblongeotp'],0,3).'-'.$row_rs['codeeotp'], "text").",noteeotp=''".
								" where codeeotp=".GetSQLValueString($row_rs['codeeotp'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	

	$query_rs="select * from eotp_source_montant";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $montantfonctionnement=(int)$row_rs['montantfonctionnement']==0?'':rand(100,10000);
		$montantinvestissement=(int)$row_rs['montantinvestissement']==0?'':rand(100,10000);
		$montantsalaire=(int)$row_rs['montantsalaire']==0?'':rand(1000,10000);
		$query_rs="update eotp_source_montant set montantfonctionnement=".GetSQLValueString($montantfonctionnement, "text").
							", montantinvestissement=".GetSQLValueString($montantinvestissement, "text").
							", montantsalaire=".GetSQLValueString($montantsalaire, "text").", note=''".
							" where codeeotp=".GetSQLValueString($row_rs['codeeotp'], "text")." and codeoperation=".GetSQLValueString($row_rs['codeoperation'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}
	
	if($database_labo=="demo12plus")
	{ mysql_query("update centrecout set libcourt=REPLACE(libcourt,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")".
								", liblong=REPLACE(liblong,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")  where libcourt like '%CRAN%' or liblong like '%CRAN%'") or die(mysql_error());
		mysql_query("update centrefinancier set libcourt=REPLACE(libcourt,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")".
								", liblong=REPLACE(liblong,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")  where libcourt like '%CRAN%' or liblong like '%CRAN%'") or die(mysql_error());
		mysql_query("update centrefinancier_reel set libcourt=REPLACE(libcourt,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")".
								", liblong=REPLACE(liblong,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")  where libcourt like '%CRAN%' or liblong like '%CRAN%'") or die(mysql_error());
		mysql_query("update centrecout_reel set libcourt=concat('centre cout reel ',codecentrecout_reel)".
								", liblong=''  where codecentrecout_reel<>''") or die(mysql_error());
		mysql_query("update modefinancement set liblongmodefinancement=REPLACE(liblongmodefinancement,'CRAN',".GetSQLValueString($GLOBALS['acronymelabo'], "text").")".
								" where liblongmodefinancement like '%CRAN%'") or die(mysql_error());
		$i=0;
		foreach(array('CID','ISET','SBS') as $dept)
		{ $i++;
			mysql_query("update centrecout set libcourt=REPLACE(libcourt,'".$dept."',".GetSQLValueString($GLOBALS['acronymelabo'].' '.$GLOBALS['libcourt_theme_fr'].$i, "text").")".
								", liblong=REPLACE(liblong,'".$dept."',".GetSQLValueString($GLOBALS['acronymelabo'].' '.$GLOBALS['libcourt_theme_fr'].$i, "text").")  where libcourt like '%".$dept."%' or liblong like '%".$dept."%'") or die(mysql_error());
			mysql_query("update structure set libcourt_fr=REPLACE(libcourt_fr,'".$dept."',".GetSQLValueString($GLOBALS['acronymelabo'].' '.$GLOBALS['libcourt_theme_fr'].$i, "text").")".
								", liblong_fr=REPLACE(libcourt_fr,'".$dept."',".GetSQLValueString($GLOBALS['acronymelabo'].' '.$GLOBALS['libcourt_theme_fr'].$i, "text").")  where libcourt_fr like '%".$dept."%' or liblong_fr like '%".$dept."%'") or die(mysql_error());
		}
		mysql_query("update budg_aci set libcourt=concat(SUBSTRING(libcourt,1,3),'...')".
								", liblong=concat(SUBSTRING(liblong,1,3),'...')  where codeaci<>''") or die(mysql_error());
	}

	$query_rs="select * from mission where codemission <>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ if($row_rs['codeagent']!='')
		{ $nom=$tab_individu[$row_rs['codeagent']]['nom'];
			$prenom=$tab_individu[$row_rs['codeagent']]['prenom'];
			$date_naiss=$tab_individu[$row_rs['codeagent']]['date_naiss'];
		}
		else
		{ $nom=substr($row_rs['nom'],0,3);
			$nom.=(strpos('AEIOUY',$nom[2])!==-1?substr('AEIOU',rand(0,4),1):substr('BCDFGLMNPRST',rand(0,12),1)).'...';
			$date_naiss=$row_rs['date_naiss'];
			$mois=rand (1, 12);
			$date_naiss=substr($date_naiss,0,4).'/'.str_pad($mois,2,'0',STR_PAD_LEFT).'/'.str_pad(rand (1, $GLOBALS['nb_jours_du_mois'][$mois]),2,'0',STR_PAD_LEFT);
		}
		$query_rs="update mission set nom=".GetSQLValueString($nom, "text").", prenom=".GetSQLValueString($prenom, "text").", date_naiss=".GetSQLValueString($date_naiss, "text").
							", adresse_pers=".GetSQLValueString(' adresse_pers de '.$nom, "text")." , adresse_admin=".GetSQLValueString(' adresse_admin de '.$nom, "text").
							", tel='', telport='',email=".GetSQLValueString(strtolower($nom).'@'.strtolower($GLOBALS['acronymelabo'].'.fr'), "text").
							", motif=concat(SUBSTRING(motif,1,10),'...'),numcarteabonneairfrance='',numcartefideliteavion='',compagniecartefideliteavion=''".
							", numimmatriculation=concat(SUBSTRING(numimmatriculation,1,3),'...'), avecautredetail='',hotelmarchechoix1=concat(SUBSTRING(hotelmarchechoix1,1,8),'...'),hotelmarchechoix2=concat(SUBSTRING(hotelmarchechoix2,1,8),'...')".
							", preferencebillethotel='', note='', composanteenseignement=concat(SUBSTRING(composanteenseignement,1,3),'...')".
							", gradecongres=concat(SUBSTRING(gradecongres,1,5),'...'),emploicongres=concat(SUBSTRING(gradecongres,1,10),'...')".
							", intitulecongres=concat(SUBSTRING(intitulecongres,1,5),'...'),organisateurcongres=concat(SUBSTRING(organisateurcongres,1,5),'...')".
							", hotelcongres='',centrecoutcongres='',eotpcongres='',destinationcongres='',organismepriseenchargeautrecongres=concat(SUBSTRING(organismepriseenchargeautrecongres,1,5),'...')".
							", numcarteabonnetrain=' ',numcarteabonneavion=' ',numcartefidelitetrain='',vehiculepersonnelnomspersonnes='',infocompmission=''".
							", organismepriseencharge='',libpays='',montantestimemission='1000',centrecout='',eotp=''".
							" where codemission=".GetSQLValueString($row_rs['codemission'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}	
	
		
	$query_rs="select * from projet where codeprojet<>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $query_rs="update projet set titre=concat(SUBSTRING(titre,1,10),'...'),titrecourt=concat(SUBSTRING(titrecourt,1,3),'...')".
							", descr=concat(SUBSTRING(descr,1,10),'...')".
							", partenaires='partenaires', montant_total='',montant_labo='',note=''".
							" where codeprojet=".GetSQLValueString($row_rs['codeprojet'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}

	$query_rs="select * from contratmontantannee where codecontrat<>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab_cumul=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $montant=rand(5000,20000);
		if(isset($tab_cumul[$row_rs['codecontrat']]))
		{ $tab_cumul[$row_rs['codecontrat']]+=$montant;
		}
		else
		{ $tab_cumul[$row_rs['codecontrat']]=$montant;
		}
		$query_rs="update contratmontantannee set montant=".GetSQLValueString($montant, "text").
							" where codecontrat=".GetSQLValueString($row_rs['codecontrat'], "text")." and annee=".GetSQLValueString($row_rs['annee'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}

	$query_rs="select * from contrat where codecontrat<>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $query_rs="update contrat set acronyme=concat(SUBSTRING(acronyme,1,3),'...'),ref_contrat=concat(SUBSTRING(ref_contrat,1,5),'...'),ref_prog_long=concat(SUBSTRING(ref_prog_long,1,5),'...')".
							", sujet=concat(SUBSTRING(sujet,1,10),'...'), montant_ht=".GetSQLValueString(isset($tab_cumul[$row_rs['codecontrat']])?$tab_cumul[$row_rs['codecontrat']]:rand(10000,50000), "text").
							", note=''".
							" where codecontrat=".GetSQLValueString($row_rs['codecontrat'], "text");
		echo $query_rs.'<br>';
		if($database_labo=="demo12plus")
		{ mysql_query($query_rs) or die(mysql_error());
		}
	}

	if($database_labo=="demo12plus")
	{ mysql_query("update cont_part set libcourtpart=concat(SUBSTRING(libcourtpart,1,3),'...'), liblongpart=concat(SUBSTRING(liblongpart,1,5),'...')".
								", nomcontactpart=if(nomcontactpart='','',concat(SUBSTRING(nomcontactpart,1,3),'...')), adressecontactpart=''".
								", telcontactpart='', telportcontactpart='', emailcontactpart='', fonctioncontactpart='', notepart='' where codepart<>''") or die(mysql_error());
	}

	if($database_labo=="demo12plus")
	{ mysql_query("update cont_projet set libcourtprojet=concat(SUBSTRING(libcourtprojet,1,3),'...')".
								", liblongprojet=concat(SUBSTRING(liblongprojet,1,3),'...')  where codeprojet<>''") or die(mysql_error());
	}

	if($database_labo=="demo12plus")
	{ mysql_query("update cont_orgfinanceur set libcourtorgfinanceur=concat(SUBSTRING(libcourtorgfinanceur,1,3),'...')".
								", liblongorgfinanceur=concat(SUBSTRING(liblongorgfinanceur,1,3),'...')  where codeorgfinanceur<>''") or die(mysql_error());
	}

	if($database_labo=="demo12plus")
	{ mysql_query("delete from annonce") or die(mysql_error());
	}
	
	if($database_labo=="demo12plus")
	{ mysql_query("delete from individupostit") or die(mysql_error());
	}

	if($database_labo=="demo12plus")
	{ mysql_query("delete from registre_hs") or die(mysql_error());
	}
	if($database_labo=="demo12plus")
	{ mysql_query("update lieu set libcourtlieu=concat(".GetSQLValueString($GLOBALS['acronymelabo'], "text").",' site ',codelieu)".
								", liblonglieu=concat(".GetSQLValueString($GLOBALS['acronymelabo'], "text").",' site ',codelieu), lienlieuhttp=''  where codelieu<>''") or die(mysql_error());
	}
	
	
	//pieces jointes
	if($database_labo=="demo12plus")
	{ $query_rs="select * from individupj,typepjindividu where individupj.codelibcatpj=typepjindividu.codelibcatpj and individupj.codetypepj=typepjindividu.codetypepj";
		echo $query_rs;
		$rs=mysql_query($query_rs) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{	$rep=$GLOBALS['path_to_rep_upload'].'/individu/'.$row_rs['codeindividu'].'/'.$row_rs['codelibcatpj'].'/'.$row_rs['numcatpj'] ;
			echo $rep.'/'.$row_rs['codetypepj'].'<br>';
			unlink($rep.'/'.$row_rs['codetypepj']);			
			if($row_rs['codelibtypepj']<>'fsd')
			{ copy($pjdemo,$rep.'/'.$row_rs['codetypepj']);
			}
			else
			{ copy($pjfsddemo,$rep.'/'.$row_rs['codetypepj']);
			}
		}
		mysql_query("update individupj,typepjindividu set nomfichier=if(codelibtypepj='fsd',".GetSQLValueString($pjfsddemo, "text").",".GetSQLValueString($pjdemo, "text").")".
								" where individupj.codelibcatpj=typepjindividu.codelibcatpj and individupj.codetypepj=typepjindividu.codetypepj");
		foreach(array('commande','contrat','mission','projet') as $table)
		{ $query_rs="select * from ".$table."pj";
			$rs=mysql_query($query_rs) or die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{	$rep=$GLOBALS['path_to_rep_upload'].'/'.$table.'/'.$row_rs['code'.$table] ;
			echo $rep.'/'.$row_rs['codetypepj'].'<br>';
				unlink($rep.'/'.$row_rs['codetypepj']);			
				copy($pjdemo,$rep.'/'.$row_rs['codetypepj']);
			}
			mysql_query("update ".$table."pj set nomfichier=".GetSQLValueString($pjdemo, "text"));
		}
	}
	if(isset($rs))mysql_free_result($rs);
}?>

</body>
</html>