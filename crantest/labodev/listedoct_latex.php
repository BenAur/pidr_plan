<?php include_once('_const_fonc.php');?>
<?php 						 
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$type_fichier='tex';
if(!array_key_exists('srh',$tab_roleuser) && !array_key_exists('du',$tab_roleuser) && !array_key_exists('admingestfin',$tab_roleuser))
{ echo "Acces restreint";
	exit;
}
$car_debut_ligne_entete='\\hline ';
$car_debut_ligne_paire='\\hline ';
$car_debut_ligne_impaire='\\hline ';
$car_fin_ligne="\\\\"."\n";
$car_cellule=" & ";

header('Content-type: text/plain');
{ echo "\documentclass[a4paper,oneside, landscape,10pt]{article}\n
\usepackage[top=0pt, bottom=0pt, left=0pt , right=0pt]{geometry}\n
\usepackage{mathtools}
\usepackage[table]{xcolor}\n
\usepackage{longtable}\n
\begin{document}\n
\\rowcolors{2}{lightgray}{white}\n
";
}
$query_rs_individu= "select individu.nom,individu.prenom,email,date_naiss,civilite.libcourt_fr as libciv,pays_naiss.libpays as libpays_naiss,".
										" diplome.libcourtdiplome_fr,ed.libcourted_fr,ed.codeed,ed.libcourted_fr as libed,individusejour.*,individuthese.*,".
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
										" and (individusejour.codeindividu,individusejour.numsejour) in (select codeindividu,numsejour from individutheme where codetheme='05' or codetheme='08')".
										" and individusujet.codesujet=sujet.codesujet".
										" order by ed.libcourted_fr asc,nom,prenom";

$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
$pair=true;
$codeed="-1";
$firsttab=true;
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ 
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
	if($etat_these=='EN COURS')
	{ 
		if($row_rs_individu["codeed"]!=$codeed)
		{ if(!$firsttab)
			{ echo "\\hline\end{longtable}";
			}
			$firsttab=false;
			echo "\begin{longtable}{|p{5cm}|p{2cm}|p{8cm}|p{5cm}|p{5cm}|}\n";
			echo $car_debut_ligne_entete."Nom".' '."Pr\\'enom".$car_cellule."Date d\\'ebut".$car_cellule."Sujet".$car_cellule."Mode financement".$car_cellule."dir \\& co-dir".$car_fin_ligne;
			$codeed=$row_rs_individu["codeed"];
		}
		if($pair)
		{ echo $car_debut_ligne_impaire;
		}
		else
		{ echo $car_debut_ligne_paire;
		}
		$pair=!$pair;
		echo txt2type($row_rs_individu["nom"],$type_fichier).' '.txt2type($row_rs_individu["prenom"],$type_fichier).$car_cellule.txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour"],"/"),$type_fichier).$car_cellule.txt2type($row_rs_individu["titresujet"],$type_fichier);
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
		$financement_actuel="";
		$montant_mensuel_brut_actuel="";
		$monitorat="";// = M si libcourtmodefinancement=A (Contrat doctoral université (ex. Allocation recherche)) 
		$missioncomp="";
		while($row_rs_emploi=mysql_fetch_assoc($rs_emploi))
		{ if($first)//1er emploi
			{ $financement_initial=($row_rs_emploi['codemodefinancement']==''?$row_rs_emploi['autremodefinancement']:$row_rs_emploi['liblongmodefinancement'])." ".$row_rs_emploi['detailmodefinancement'];
				$montant_mensuel_brut_initial=$row_rs_emploi['montant_mensuel_brut'];
				if($row_rs_emploi['missioncomp']=='oui' && $row_rs_emploi['libcourtmodefinancement']=='A')
				{ $monitorat='M';
					$missioncomp='OUI';
				}
			}
			else//on arrive au dernier emploi
			{ $financement_actuel=($row_rs_emploi['codemodefinancement']==''?$row_rs_emploi['autremodefinancement']:$row_rs_emploi['libcourtmodefinancement'])." ".$row_rs_emploi['detailmodefinancement'];
				$montant_mensuel_brut_actuel=$row_rs_emploi['montant_mensuel_brut'];
			}
			$first=false;
		}
		
		echo $car_cellule.txt2type($financement_initial,$type_fichier);
		// encadrants
		$query_rs_dir="select codeindividu, nom, prenom,taux_encadrement from sujetdir,individu".
									" where sujetdir.codedir=individu.codeindividu and sujetdir.codesujet=".GetSQLValueString($codesujet, "text").
									" order by sujetdir.numordre";
		$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
		echo $car_cellule;
		for($i=1;$i<=3;$i++)
		{ if($row_rs_dir=mysql_fetch_assoc($rs_dir))
			{ echo txt2type($row_rs_dir['prenom']." ".$row_rs_dir['nom'],$type_fichier)."\\newline ";
			}
		}
		echo $car_fin_ligne;
	}
}
 echo "\\hline\end{longtable}";
echo "\end{document}";
?>
<?php 
if(isset($rs_postdoc)) {mysql_free_result($rs_postdoc);}
if(isset($rs_theme)) {mysql_free_result($rs_theme);}
if(isset($rs_emploi)) {mysql_free_result($rs_emploi);}
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
if(isset($rs_dir)) {mysql_free_result($rs_dir);}
?>




