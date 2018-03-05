<?php include_once('_const_fonc.php');?>
<?php 						 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
/* if(!array_key_exists('srh',$tab_roleuser) && !array_key_exists('du',$tab_roleuser) && !array_key_exists('admingestfin',$tab_roleuser))
{ echo "Acces restreint";
	exit;
} */
$type_fichier=$_GET['type_fichier'];
$car_debut_ligne_entete="";$car_debut_ligne_paire="";$car_debut_ligne_impaire="";$car_cellule="";
if($type_fichier=="html")
{ $car_debut_ligne_entete='<tr class="head"><td>';
	$car_debut_ligne_paire='<tr class="even"><td>';
	$car_debut_ligne_impaire='<tr class="odd"><td>';
	$car_fin_ligne="</td></tr>";
	$car_cellule="</td><td>";
	$html=true;
}
else if($type_fichier=="csv")
{ $car_fin_ligne="\n";
	$car_cellule="\t";
	$html=false;
}
if($html)
{?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Listes des personnels <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<style>
table tr.head th, table tr.head {
	background-color: #D3DCE3;
	text-align: center;
}

/* odd table rows 1,3,5,7,... */
table tr.odd th,
table tr.odd {
    background-color: #E5E5E5;
    text-align: left;
}

/* even table rows 2,4,6,8,... */
table tr.even th,
table tr.even {
    background-color: #D5D5D5;
    text-align: left;
}

/* marked tbale rows */
table tr.marked th,
table tr.marked {
    background-color: #FFCC99;
}

/* hovered table rows */
table tr.odd:hover,
table tr.even:hover,
table tr.odd:hover th,
table tr.even:hover th,
table tr.hover th,
table tr.hover {
    background-color: #CCFFCC;
}
</style>
</head>
<body class="noircalibri10">
<table border="0" align="center" cellpadding="2" cellspacing="1">
<?php 
}
else
{ header('Content-type: text/plain');
}
echo 	$car_debut_ligne_entete."numdossier".$car_cellule."Debut".$car_cellule."Fin".$car_cellule."Etat".$car_cellule."civilite".$car_cellule."Nom".$car_cellule."Prenom".
			$car_cellule."email".$car_cellule."Date naiss".$car_cellule."Nationalité".$car_cellule."date autorisation".$car_cellule."sexe".$car_cellule."Labo d'accueil".
			$car_cellule.$GLOBALS['libcourt_theme_fr']."1".$car_cellule.$GLOBALS['libcourt_theme_fr']."2".
			$car_cellule."Financement initial".$car_cellule."Detail fin. initial".$car_cellule."Montant mensuel brut initial".$car_cellule."Financement actuel".$car_cellule."Detail Fin. actuel".$car_cellule."Montant mensuel brut actuel"./* $car_cellule."Monitorat". */
			$car_cellule."Master, Etab., année".$car_cellule."Dipl. ant. Master".
			$car_cellule."Sujet de these initial".$car_cellule."Directeur de these".$car_cellule."Tx encadrement".$car_cellule."Co-directeur".$car_cellule."Tx encadrement".$car_cellule."Co-directeur".$car_cellule."Tx encadrement".$car_cellule."Autres encadrants".
			$car_cellule."Co-tutelle".
			$car_cellule."Sujet de these actualisé".$car_cellule."Date 1ere inscr.".$car_cellule."N° Inscr.".$car_cellule."Date de soutenance".$car_cellule."Durée en mois".$car_cellule."Etablisst".$car_cellule."ED".
			$car_cellule."Titre de la these".$car_cellule."Rapporteur 1".$car_cellule."Rapporteur 2".$car_cellule."reste du Jury".
			$car_cellule."Profession exercee".$car_cellule."Etablissement employeur".
			$car_cellule."Ville".$car_cellule."Pays".$car_cellule."Depuis le".$car_cellule."Notes suivi doctorant".
			$car_cellule."Adresse".$car_cellule."Courriel apres départ ".$GLOBALS['acronymelabo'].$car_cellule."Notes suivi individu".$car_cellule."Procedure depart".
			$car_cellule.'POSTDOC'.$car_cellule.'Date debut postdoc'.$car_cellule.'Date fin postdoc'.
			$car_fin_ligne;

$nb_membres=0;
$codeindividu="";
/* $query_rs="select sujet.codesujet,sujet.titre_fr as sujetinitial,autredir1,autredir2 from sujet where codetypesujet='03'";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_sujet['codesujet']=$row_rs;
} */
$query_rs_individu= "select individu.nom,individu.prenom,email,date_naiss,individu.codeciv,email_parti,civilite.libcourt_fr as libciv,pays_naiss.libpays as libpays_naiss,".
										" diplome.libcourtdiplome_fr,ed.libcourted_fr as libed,individusejour.*,individuthese.*,sujet.titre_fr as titresujet,".
										" libcourttypeprofession_postdoc, pays_cotutelle.libpays as libpays_cotutelle, pays_postdoc.libpays as libpays_postdoc,".
										" sujet.codesujet,sujet.titre_fr as titresujet,autredir1,autredir2".
										" from individu,civilite,pays as pays_naiss,diplome,corps,cat,individusujet,sujet,individusejour".
										" left join individuthese on individusejour.codeindividu=individuthese.codeindividu and individusejour.numsejour=individuthese.numsejour".
										" left join ed on individuthese.codeed_these=ed.codeed".
										" left join pays as pays_cotutelle on individuthese.codepays_cotutelle=pays_cotutelle.codepays".
										" left join pays as pays_postdoc on individuthese.codepays_postdoc=pays_postdoc.codepays".
										" left join typeprofession_postdoc on individuthese.codetypeprofession_postdoc=typeprofession_postdoc.codetypeprofession_postdoc".
										" where individu.codeindividu<>'' and individu.codeindividu=individusejour.codeindividu".
										" and individu.codeciv=civilite.codeciv".
										" and individu.codepays_naiss=pays_naiss.codepays".
										" and individusejour.codemaster_obtenu=diplome.codediplome".
										" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
										" and codelibcat='DOCTORANT'".
										" and individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
										" and individusujet.codesujet=sujet.codesujet".
										" order by ed.libcourted_fr,SUBSTRING(datedeb_sejour,1,4),nom,prenom";
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$pair=true;
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ if($codeindividu!=$row_rs_individu["codeindividu"])
  { $nb_membres++;
	}
	$codeindividu=$row_rs_individu["codeindividu"];
	$numsejour=$row_rs_individu["numsejour"];
	$codesujet=$row_rs_individu["codesujet"];
	if($row_rs_individu['datefin_sejour']<$aujourdhui && $row_rs_individu['datefin_sejour']!='')
	{ if($row_rs_individu["date_soutenance"]=='')
		{ $etat_these='ARRET';
		}
		else
		{ $etat_these='SOUTENU';
		}
	}
	else
	{ if($row_rs_individu['datedeb_sejour']>$aujourdhui)
		{ $etat_these='FUTUR';
		}
		else
		{ $etat_these='EN COURS';
		}
	} 
	
	if($pair)
	{ echo $car_debut_ligne_impaire;
	}
	else
	{ echo $car_debut_ligne_paire;
	}
	$pair=!$pair;
	echo 	$codeindividu.".".$numsejour.$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour"],"/"),$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datefin_sejour"],"/"),$type_fichier).$car_cellule.$etat_these.$car_cellule.txt2type($row_rs_individu["libciv"],$type_fichier).$car_cellule.txt2type($row_rs_individu["nom"],$type_fichier).$car_cellule.txt2type($row_rs_individu["prenom"],$type_fichier).
					$car_cellule.txt2type($row_rs_individu["email"],$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_naiss"],"/"),$type_fichier).$car_cellule.txt2type($row_rs_individu["libpays_naiss"],$type_fichier).$car_cellule.
					($row_rs_individu["date_autorisation"]==""?"":txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_autorisation"],"/"),$type_fichier)).$car_cellule.($row_rs_individu["codeciv"]=="1"?"H":"F").$car_cellule.txt2type($row_rs_individu["labo_accueil"],$type_fichier);
	// themes
	$query_rs_theme="select structure.libcourt_fr as libtheme from individutheme,structure".
									" where individutheme.codetheme=structure.codestructure and individutheme.codeindividu=".GetSQLValueString($codeindividu, "text").
									" and individutheme.numsejour=".GetSQLValueString($numsejour, "text");
	$rs_theme=mysql_query($query_rs_theme) or die(mysql_error());
	for($i=1;$i<=2;$i++)
	{ echo $car_cellule;
		if($row_rs_theme=mysql_fetch_assoc($rs_theme))
		{ echo txt2type($row_rs_theme['libtheme'],$type_fichier);
		}
	}

	$query_rs_emploi="select individuemploi.*,libcourtmodefinancement, liblongmodefinancement from individuemploi,modefinancement".
									" where individuemploi.codemodefinancement=modefinancement.codemodefinancement".	
									" and individuemploi.codeindividu=".GetSQLValueString($codeindividu, "text").
									" and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individu["datedeb_sejour"]."'","'".$row_rs_individu["datefin_sejour"]."'").
									" order by datedeb_emploi asc";
	$rs_emploi=mysql_query($query_rs_emploi) or die(mysql_error());
	// financement initial=1er emploi
	// financement actuel=dernier emploi
	$first=true;
	$financement_initial="";
	$detail_financement_initial="";
	$financement_actuel="";
	$detail_financement_actuel="";
	$montant_mensuel_brut_actuel="";
	$monitorat="";// = M si libcourtmodefinancement=A (Contrat doctoral université (ex. Allocation recherche)) 
	$missioncomp="";
	while($row_rs_emploi=mysql_fetch_assoc($rs_emploi))
	{ if($first)//1er emploi
		{ $financement_initial=$row_rs_emploi['codemodefinancement']==''?$row_rs_emploi['autremodefinancement']:$row_rs_emploi['liblongmodefinancement'];
			$detail_financement_initial=$row_rs_emploi['detailmodefinancement'];
			$montant_mensuel_brut_initial=$row_rs_emploi['montant_mensuel_brut'];
			/* if($row_rs_emploi['missioncomp']=='oui' && $row_rs_emploi['libcourtmodefinancement']=='A')
			{ $monitorat='M';
				$missioncomp='OUI';
			} */
		}
		else//on arrive au dernier emploi
		{ $financement_actuel=$row_rs_emploi['codemodefinancement']==''?$row_rs_emploi['autremodefinancement']:$row_rs_emploi['libcourtmodefinancement'];
			$detail_financement_actuel=$row_rs_emploi['detailmodefinancement'];
			$montant_mensuel_brut_actuel=$row_rs_emploi['montant_mensuel_brut'];
		}
		$first=false;
	}
	
	echo 	$car_cellule.txt2type($financement_initial,$type_fichier).$car_cellule.txt2type($detail_financement_initial,$type_fichier).$car_cellule.txt2type($montant_mensuel_brut_initial,$type_fichier).$car_cellule.txt2type($financement_actuel,$type_fichier).$car_cellule.txt2type($detail_financement_actuel,$type_fichier).$car_cellule.txt2type($montant_mensuel_brut_actuel,$type_fichier)/*. $car_cellule.txt2type($row_rs_emploi['libcourtmodefinancement'].$monitorat.$row_rs_emploi['missioncomp'],$type_fichier).$car_cellule.$missioncomp */;
	echo 	$car_cellule.($row_rs_individu["codemaster_obtenu"]!=""?txt2type($row_rs_individu["libcourtdiplome_fr"],$type_fichier):txt2type($row_rs_individu["autremaster_obtenu_lib"],$type_fichier)).$car_cellule.txt2type($row_rs_individu["diplome_dernier_lib"],$type_fichier);
	echo 	$car_cellule.($row_rs_individu["sujet_initial"]==''?txt2type($row_rs_individu["titresujet"],$type_fichier):txt2type($row_rs_individu["sujet_initial"],$type_fichier));
	// encadrants
	$query_rs_dir="select codeindividu, nom, prenom,taux_encadrement from sujetdir,individu".
								" where sujetdir.codedir=individu.codeindividu and sujetdir.codesujet=".GetSQLValueString($codesujet, "text").
								" order by sujetdir.numordre";
	$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
	for($i=1;$i<=3;$i++)
	{ $taux_encadrement="";
		echo $car_cellule;
		if($row_rs_dir=mysql_fetch_assoc($rs_dir))
		{ echo txt2type($row_rs_dir['prenom']." ".$row_rs_dir['nom'],$type_fichier);
			$taux_encadrement=$row_rs_dir['taux_encadrement'];
		}
		echo $car_cellule.txt2type($taux_encadrement,$type_fichier);
	}
	echo 	$car_cellule.txt2type($row_rs_individu['autredir1'].' '.$row_rs_individu['autredir2'],$type_fichier);
	echo 	$car_cellule.txt2type($row_rs_individu["cotutelle_etab"]." ".$row_rs_individu["libpays_cotutelle"],$type_fichier);
	/* //if($etat_these=='SOUTENU')
	{ */ echo $car_cellule.txt2type($row_rs_individu["sujet_actualise"],$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_preminscr"],"/"),$type_fichier).($html?'</td><td align="center">'.txt2type($row_rs_individu["num_inscr"]+$row_rs_individu["num_inscr_ajuste"],$type_fichier):$car_cellule.txt2type($row_rs_individu["num_inscr"],$type_fichier));
		echo 	$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_soutenance"],"/"),$type_fichier).($html?'</td><td align="center">'.txt2type($row_rs_individu["duree_mois_these"],$type_fichier):$car_cellule.txt2type($row_rs_individu["duree_mois_these"],$type_fichier)).$car_cellule.txt2type($row_rs_individu["etab_these"],$type_fichier).$car_cellule.txt2type($row_rs_individu["libed"],$type_fichier);
		echo 	$car_cellule.txt2type($row_rs_individu["titre_these"],$type_fichier).$car_cellule.txt2type($row_rs_individu["jury_rapp1_these"],$type_fichier).$car_cellule.txt2type($row_rs_individu["jury_rapp2_these"],$type_fichier).$car_cellule.($html?nl2br($row_rs_individu["jury_autres_membres_these"]):txt2type($row_rs_individu["jury_autres_membres_these"],$type_fichier));
		echo 	$car_cellule.txt2type($row_rs_individu["profession_postdoc"],$type_fichier).$car_cellule.txt2type($row_rs_individu["employeur_postdoc"],$type_fichier);
		echo 	$car_cellule.txt2type($row_rs_individu["ville_postdoc"],$type_fichier).$car_cellule.txt2type($row_rs_individu["libpays_postdoc"],$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["dateemploi_postdoc"],"/"),$type_fichier).$car_cellule.txt2type($row_rs_individu["note_doct"],$type_fichier);
		echo 	$car_cellule.txt2type($row_rs_individu["adresse_postdoc"],$type_fichier).$car_cellule.txt2type($row_rs_individu["email_parti"],$type_fichier);
		echo 	$car_cellule.txt2type($row_rs_individu["note_doct"],$type_fichier).$car_cellule.txt2type($row_rs_individu["proc_depart"],$type_fichier);
		$query_rs_postdoc= "select individusejour.* from individusejour,corps,cat".
												" where individusejour.codeindividu=".GetSQLValueString($codeindividu, "text").
												" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
												" and codelibcat='POSTDOC'";
		$rs_postdoc=mysql_query($query_rs_postdoc) or die(mysql_error());
		if($row_rs_postdoc=mysql_fetch_assoc($rs_postdoc))
		{ echo 	$car_cellule.'POSTDOC '.$GLOBALS['acronymelabo'].$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_postdoc["datedeb_sejour"],"/"),$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_postdoc["datefin_sejour"],"/"),$type_fichier);
		}
		else 
		{ echo $car_cellule.$car_cellule.$car_cellule;
		}
	/* }
	else
	{ for($i=1;$i<=29;$i++)
		{ echo $car_cellule;
		}
	} */
	echo $car_fin_ligne;
}
?>
<?php 
if(isset($rs_postdoc)) {mysql_free_result($rs_postdoc);}
if(isset($rs_theme)) {mysql_free_result($rs_theme);}
if(isset($rs_emploi)) {mysql_free_result($rs_emploi);}
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
if(isset($rs_dir)) {mysql_free_result($rs_dir);}
?>




