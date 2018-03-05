<?php include_once('_const_fonc.php');
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
/* if(!array_key_exists('srh',$tab_roleuser) && !array_key_exists('du',$tab_roleuser)  && !array_key_exists('admingestfin',$tab_roleuser))
{ echo "Acces restreint";
	exit;
} */
$timedeb=microtime(true);

$type_fichier=isset($_GET['type_fichier'])?$_GET['type_fichier']:(isset($_POST['type_fichier'])?$_POST['type_fichier']:"csv");

$query_rs="select codeindividu, nom, prenom from individu,gesttheme where individu.codeindividu=gesttheme.codegesttheme";
$rs=mysql_query($query_rs) or  die(mysql_error());
$tab_gesttheme=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_gesttheme[$row_rs['codeindividu']]=substr($row_rs['prenom'],0,1).' '.$row_rs['nom'];
}
$tab_pays=array();
$rs=mysql_query("select * from pays");
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_pays[$row_rs['codepays']]=$row_rs;
}

// theme(s)
$query_rs="SELECT individusejour.codeindividu,individusejour.numsejour,individutheme.codetheme,structure.libcourt_fr as libtheme".
					" FROM individusejour,individutheme,structure".
					" WHERE individusejour.codeindividu=individutheme.codeindividu and individusejour.numsejour=individutheme.numsejour".
					" and individutheme.codetheme=structure.codestructure AND structure.esttheme='oui'";
$rs=mysql_query($query_rs) or die(mysql_error());
$listetheme="";//pas de theme par defaut
$tab_individutheme=array();
while($row_rs = mysql_fetch_assoc($rs))
{ $tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]][$row_rs["codetheme"]]=$row_rs['libtheme'];
}

$tab_individuemploi=array();
$query_rs="select individusejour.codeindividu,individusejour.numsejour,individuemploi.*,liblongmodefinancement as libmodefinancement,".
					" centrecout_pers.libcourt as libcentrecout,etab.libcourtetab_fr as libetab".
					" from individusejour,individuemploi,etab,modefinancement,centrecout_pers".
					" where individusejour.codeindividu=individuemploi.codeindividu and individuemploi.codemodefinancement=modefinancement.codemodefinancement".
					" and individuemploi.codeetab=etab.codeetab".
					" and individuemploi.codecentrecout=centrecout_pers.codecentrecout ".
					" and ".intersectionperiodes('datedeb_emploi','datefin_emploi','datedeb_sejour','datefin_sejour').
					" order by individusejour.codeindividu,individusejour.numsejour,datedeb_emploi desc";//seul l'emploi le plus recent sera retenu
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(!isset($tab_individuemploi[$row_rs["codeindividu"]][$row_rs["numsejour"]]))
	{ $tab_individuemploi[$row_rs["codeindividu"]][$row_rs["numsejour"]]=$row_rs;
	}
}

$tab_individuthese=array();
$query_rs="select individuthese.*,ed.libcourted_fr as libed, libcourttypeprofession_postdoc".
					" from individuthese,ed,typeprofession_postdoc ".
					" where individuthese.codeed_these=ed.codeed".
					" and individuthese.codetypeprofession_postdoc=typeprofession_postdoc.codetypeprofession_postdoc";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_individuthese[$row_rs["codeindividu"]][$row_rs["numsejour"]]=$row_rs;
	$tab_individuthese[$row_rs["codeindividu"]][$row_rs["numsejour"]]["libpays_cotutelle"]=$tab_pays[$row_rs["codepays_cotutelle"]]["libpays"];
	$tab_individuthese[$row_rs["codeindividu"]][$row_rs["numsejour"]]["libpays_postdoc"]=$tab_pays[$row_rs["codepays_postdoc"]]["libpays"];
}

$tab_individusujet=array();
$query_rs="select individusejour.codeindividu,individusejour.numsejour,sujet.codesujet,sujet.titre_fr as titresujet,".
					" if(individusejour.codemaster_obtenu<>'',diplome.libcourtdiplome_fr,autremaster_obtenu_lib) as libmaster_obtenu,diplome_dernier_lib,".
					" autredir1,autredir2".
					" from diplome,individusujet,sujet,individusejour ".
					" where individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
					" and individusejour.codemaster_obtenu=diplome.codediplome".
					" and individusujet.codesujet=sujet.codesujet";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_individusujet[$row_rs["codeindividu"]][$row_rs["numsejour"]]=$row_rs;
}

// encadrants
$query_rs="select codesujet,sujetdir.numordre, nom, prenom,taux_encadrement from sujetdir,individu".
					" where sujetdir.codedir=individu.codeindividu".
					" order by codesujet,sujetdir.numordre";
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_sujetdir=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_sujetdir[$row_rs["codesujet"]][$row_rs["numordre"]]=$row_rs;
}

$query_rs = "SELECT codecommission, libcourtcommission_fr as libcommission FROM commission ORDER BY libcommission";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_commission=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_commission[$row_rs['codecommission']]=$row_rs;
}

$query_rs = "SELECT codesection, codecommission, numsection, liblongsection_fr as libsection FROM commissionsection ORDER BY numsection";
$rs = mysql_query($query_rs) or die(mysql_error());
$tab_commissionsection=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_commissionsection[$row_rs['codesection']]=$row_rs;
}

$query_rs_individu= "(select individu.*, etab_orig, adresse_etab_orig, ville_etab_orig, codepays_etab_orig, date_demande_fsd,date_autorisation,numdossierzrr, liblongcorps_fr,libstatutpers_fr,codelibcat, libcat_fr,codelibtypestage, libcourtlieu,ed.libcourted_fr as libed,numsejour,codegesttheme,datedeb_sejour,datefin_sejour,corps.codecollege,".
											"if(".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').",'1.present','') as partipresentfutur,if(hdr = 'oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='10','HDR','') as esthdr".
											" from individu,corps,cat,statutpers,lieu,individusejour,ed,typestage".
											" where individu.codeindividu<>'' and individu.codeindividu=individusejour.codeindividu".
											" and individu.codelieu=lieu.codelieu ".
											" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat and corps.codestatutpers=statutpers.codestatutpers".
											" and individu.codeed=ed.codeed ".
											" and individusejour.codetypestage=typestage.codetypestage ".
											" and ".periodeencours('individusejour.datedeb_sejour','individusejour.datefin_sejour').
											" and (datefin_sejour='' or DATEDIFF(replace(datefin_sejour,'/','-'),replace(datedeb_sejour,'/','-'))>5)".
											" and codelibcat<>'PRESTATAIRE'".
											")".
											" UNION ". 
											" (select individu.*,etab_orig, adresse_etab_orig, ville_etab_orig, codepays_etab_orig, date_demande_fsd,date_autorisation,numdossierzrr, liblongcorps_fr,libstatutpers_fr,codelibcat, libcat_fr,codelibtypestage,libcourtlieu,ed.libcourted_fr as libed,numsejour,codegesttheme,datedeb_sejour,datefin_sejour,corps.codecollege,'2.parti' as partipresentfutur,".
											"	if(hdr = 'oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='10','HDR','') as esthdr".
											" from individu,corps,cat,statutpers,lieu,individusejour,ed,typestage".
											" where individu.codeindividu<>'' and individu.codeindividu=individusejour.codeindividu".
											" and individu.codelieu=lieu.codelieu ".
											" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat and corps.codestatutpers=statutpers.codestatutpers".
											" and individu.codeed=ed.codeed ".
											" and individusejour.codetypestage=typestage.codetypestage ".
											" and ".periodepassee('individusejour.datefin_sejour').
											" and (datefin_sejour='' or DATEDIFF(replace(datefin_sejour,'/','-'),replace(datedeb_sejour,'/','-'))>5)".
											" and codelibcat<>'PRESTATAIRE'".
											")" .
											" UNION ".
											" (select individu.*,etab_orig, adresse_etab_orig, ville_etab_orig, codepays_etab_orig, date_demande_fsd,date_autorisation,numdossierzrr, liblongcorps_fr,libstatutpers_fr,codelibcat, libcat_fr,codelibtypestage,libcourtlieu,ed.libcourted_fr as libed,numsejour,codegesttheme,datedeb_sejour,datefin_sejour,corps.codecollege,'3.futur' as partipresentfutur,".
											"	if(hdr = 'oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='10','HDR','') as esthdr".
											" from individu,corps,cat,statutpers,lieu,individusejour,ed,typestage".
											" where individu.codeindividu<>'' and individu.codeindividu=individusejour.codeindividu".
											" and individu.codelieu=lieu.codelieu ".
											" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat and corps.codestatutpers=statutpers.codestatutpers".
											" and individu.codeed=ed.codeed ".
											" and individusejour.codetypestage=typestage.codetypestage ".
											" and ".periodefuture('individusejour.datedeb_sejour').
											" and (datefin_sejour='' or DATEDIFF(replace(datefin_sejour,'/','-'),replace(datedeb_sejour,'/','-'))>5)".
											" and codelibcat<>'PRESTATAIRE'".
											")"/*.
											" order by partipresentfutur,nom,prenom,individusejour.datedeb_sejour desc"*/ ;
//echo $query_rs_individu;

$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());

$ligne="college"."\t"."HE"."\t"."codeindividu"."\t"."numsejour"."\t"."codelabintel"."\t"."Etat"."\t"."Suivi par"."\t"."debut"."\t"."fin"."\t"."Nom"."\t"."Prenom"."\t"."Nom JF"."\t".
			"Date naiss"."\t"."Ville naiss"."\t"."Pays naiss"."\t"."Nationalite"."\t".
			"Mail"."\t"."Mail parti"."\t"."Tel"."\t"."Fax"."\t"."Mobile"."\t"."page web perso"."\t".
			"etab_orig"."\t"."adresse_etab_orig"."\t"."ville_etab_orig"."\t"."pays_etab_orig"."\t".
			"Lieu de travail"."\t"."Autre lieu"."\t"."Dept."."\t"."Dem. FSD"."\t"."Avis FSD"."\t"."Num dossier zrr"."\t".
			"Statut"."\t"."Categorie"."\t"."Corps"."\t"."Type stage"."\t"."Associe"."\t"."HDR"."\t"."ED"."\t"."Date"."\t"."commission"."\t"."numsection"."\t"."section"."\t".
			"debut emploi"."\t"."fin emploi"."\t"."employeur"."\t"."autre employeur"."\t"."mode financement"."\t"."autre mode financmt"."\t"."detail financmt"."\t".
			"centre cout"."\t"."autre cc"."\t"."eotp"."\t"."contrat"."\t"."autre mission comp".
			"\t"."sujet"."\t"."Encadrant1"."\t"."Taux"."\t"."Encadrant2"."\t"."Taux"."\t"."Encadrant3"."\t"."Taux"."\t"."Autre encadrant1"."\t"."Autre encadrant2".
			"\t"."Co-tutelle"."\t"."Pays co-tutelle"."\t"."Master, Etab., annee"."\t"."Dipl. ant. Master"."\t"."Etat"."\t"."ED these"."\t"."Annee"."\t"."Date inscr.".
			"\t"."Suivi CS 12 mois"."\t"."Suivi CS 30 mois"."\t"."\t"."Soutenance"."\t"."duree_mois"."\t"."Etablisst".
			"\t"."titre these"."\t"."rapp1 these"."\t"."rapp2 these"."\t"."jury autres".
			"\t"."profession_postdoc"."\t"."employeur_postdoc"."\t"."ville_postdoc"."\t"."pays_postdoc"."\t"."dateemploi_postdoc"."\t"."note_doct"."\n";
	$objPHPExcel = new PHPExcel();
	$tab=explode("\t",$ligne);
	$objWorksheet = $objPHPExcel-> getActiveSheet();
	$l=1;$c=0;
	foreach($tab as $val)
	{	$objWorksheet->setCellValueByColumnAndRow( $c, $l, $val);//utf8_encode(conv_car_speciaux())
		$c++;
		//echo $val;
	}

//echo $query_rs_individu;
while($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ $row_rs_individu["libpays_naiss"]=$tab_pays[$row_rs_individu["codepays_naiss"]]["libpays"];
	$row_rs_individu["libnat"]=$tab_pays[$row_rs_individu["codenat"]]["libnat"];
	$row_rs_individu["libpays_etab_orig"]=$tab_pays[$row_rs_individu["codepays_etab_orig"]]["libpays"];

	$codeindividu=$row_rs_individu["codeindividu"];
	$numsejour=$row_rs_individu["numsejour"];
	$listetheme="";//pas de theme par defaut
  foreach($tab_individutheme[$codeindividu][$numsejour] as $codetheme=>$libtheme)
  { $listetheme.=$libtheme.", ";
  }
	$listetheme=rtrim($listetheme,', ');

	$ligne=$row_rs_individu["codecollege"]."\t".($row_rs_individu["hors_effectif"]=='oui'?'oui':'')."\t".$codeindividu."\t".$numsejour."\t".txt2type($row_rs_individu["codelabintel"],$type_fichier)."\t".$row_rs_individu["partipresentfutur"]."\t".
				txt2type($tab_gesttheme[$row_rs_individu["codegesttheme"]],$type_fichier)."\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour"],'-'),$type_fichier)."\t".
				txt2type(aaaammjj2jjmmaaaa($row_rs_individu["datefin_sejour"],'-'),$type_fichier)."\t".
				txt2type($row_rs_individu["nom"],$type_fichier)."\t".txt2type($row_rs_individu["prenom"],$type_fichier)."\t".txt2type($row_rs_individu["nomjf"],$type_fichier)."\t".
				txt2type($row_rs_individu["date_naiss"],$type_fichier)."\t".txt2type($row_rs_individu["ville_naiss"],$type_fichier)."\t".txt2type($row_rs_individu["libpays_naiss"],$type_fichier)."\t".
				txt2type($row_rs_individu["libnat"],$type_fichier)."\t".
				txt2type($row_rs_individu["email"],$type_fichier)."\t".
				txt2type($row_rs_individu["email_parti"],$type_fichier)."\t".txt2type($row_rs_individu["tel"],$type_fichier)."\t".txt2type($row_rs_individu["fax"],$type_fichier)."\t".txt2type($row_rs_individu["telport"],$type_fichier)."\t".txt2type($row_rs_individu["lienpghttp"],$type_fichier)."\t".
				txt2type($row_rs_individu["etab_orig"],$type_fichier)."\t".txt2type($row_rs_individu["adresse_etab_orig"],$type_fichier)."\t".txt2type($row_rs_individu["ville_etab_orig"],$type_fichier)."\t".txt2type($row_rs_individu["libpays_etab_orig"],$type_fichier)."\t".
				$row_rs_individu["libcourtlieu"]."\t".txt2type($row_rs_individu["autrelieu"],$type_fichier)."\t".$listetheme."\t".
				txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_demande_fsd"],'-'),$type_fichier)."\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_autorisation"],'-'),$type_fichier)."\t".txt2type($row_rs_individu["numdossierzrr"],$type_fichier)."\t".
				$row_rs_individu["libstatutpers_fr"]."\t".$row_rs_individu["libcat_fr"]."\t".$row_rs_individu["liblongcorps_fr"]."\t".$row_rs_individu["codelibtypestage"]."\t".
				($row_rs_individu['associe']=='oui'?"Associe":"")."\t".$row_rs_individu['esthdr']."\t".$row_rs_individu['libed']."\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individu["date_hdr"],'-'),$type_fichier)."\t".
				$tab_commission[$row_rs_individu['codecommission']]['libcommission']."\t".$tab_commissionsection[$row_rs_individu['codesection']]['numsection']."\t".$tab_commissionsection[$row_rs_individu['codesection']]['libsection']."\t";

	$a_un_emploi=false;
	if(isset($tab_individuemploi[$codeindividu][$numsejour]))
  { $row_rs_individuemploi=$tab_individuemploi[$codeindividu][$numsejour];
		$a_un_emploi=true;
		if(in_array($tab_individuemploi[$codeindividu][$numsejour]["libetab"],array("UHP","INPL","NANCY II")))
		{ $row_rs_individuemploi["libetab"]="UL";
		}
  }
	if($a_un_emploi)
	{ $ligne.=txt2type($row_rs_individuemploi["datedeb_emploi"],$type_fichier)."\t".txt2type($row_rs_individuemploi["datefin_emploi"],$type_fichier)."\t".txt2type($row_rs_individuemploi["libetab"],$type_fichier)."\t".
	  txt2type($row_rs_individuemploi["autreetab"],$type_fichier)."\t".txt2type($row_rs_individuemploi["libmodefinancement"],$type_fichier)."\t".txt2type($row_rs_individuemploi["autremodefinancement"],$type_fichier)."\t".txt2type($row_rs_individuemploi["detailmodefinancement"],$type_fichier)."\t".
	  $row_rs_individuemploi["libcentrecout"]."\t".txt2type($row_rs_individuemploi["autrecentrecout"],$type_fichier)."\t".txt2type($row_rs_individuemploi["eotp"],$type_fichier)."\t".txt2type($row_rs_individuemploi["contrat"],$type_fichier)."\t".$row_rs_individuemploi["missioncomp"];
	}
	else
	{ for($i=1;$i<=11;$i++)
		{ $ligne.="\t";
		}
	}
	if(isset($tab_individusujet[$codeindividu][$numsejour]))
	{ $row_rs_individusujet=$tab_individusujet[$codeindividu][$numsejour];
		$ligne.="\t".txt2type($row_rs_individusujet["titresujet"],$type_fichier);
		$codesujet=$tab_individusujet[$codeindividu][$numsejour]["codesujet"];
		if(isset($tab_sujetdir[$codesujet]))
		{ $tab_dir=$tab_sujetdir[$codesujet];
		}
		for($i=1;$i<=3;$i++)
		{ if(isset($tab_dir[''.$i]))
			{ $ligne.="\t".txt2type($tab_dir[''.$i]['prenom']." ".$tab_dir[''.$i]['nom'],$type_fichier)."\t".$tab_dir[''.$i]['taux_encadrement'];
			}
			else
			{ $ligne.="\t"."\t";
			}
		}
		$ligne.="\t".txt2type($row_rs_individusujet['autredir1'],$type_fichier)."\t".txt2type($row_rs_individusujet['autredir2'],$type_fichier);
		
		if($row_rs_individu["codelibcat"]=='DOCTORANT')
		{ $row_rs_individuthese=$tab_individuthese[$codeindividu][$numsejour];
			$annee_these=calcule_annee_these($row_rs_individuthese['date_preminscr'],$row_rs_individuthese['date_soutenance'],$row_rs_individuthese['num_inscr_ajuste']).'A';
			if($row_rs_individu['datefin_sejour']<$aujourdhui && $row_rs_individu['datefin_sejour']!='')
			{ if($row_rs_individuthese["date_soutenance"]=='')
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
			$ligne.="\t".(strtolower($row_rs_individuthese["cotutelle"])=='oui'?txt2type($row_rs_individuthese["cotutelle"],$type_fichier)."\t".txt2type($row_rs_individuthese["libpays_cotutelle"],$type_fichier):"\t"); 
			$ligne.="\t".txt2type($row_rs_individusujet["libmaster_obtenu"],$type_fichier).
					 "\t".txt2type($row_rs_individusujet["diplome_dernier_lib"],$type_fichier);
			$ligne.="\t".$etat_these."\t".txt2type($row_rs_individuthese["libed"],$type_fichier)."\t".$annee_these;
			$ligne.="\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individuthese["date_preminscr"],"/"),$type_fichier);
			$ligne.="\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individuthese["date_suivi_comite_selection_12_mois"],"/"),$type_fichier);
			$ligne.="\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individuthese["date_suivi_comite_selection_30_mois"],"/"),$type_fichier);
			$ligne.="\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individuthese["date_soutenance"],"/"),$type_fichier)."\t".txt2type($row_rs_individuthese["duree_mois_these"],$type_fichier)."\t".txt2type($row_rs_individuthese["etab_these"],$type_fichier);
			$ligne.="\t".txt2type($row_rs_individuthese["titre_these"],$type_fichier)."\t".txt2type($row_rs_individuthese["jury_rapp1_these"],$type_fichier)."\t".txt2type($row_rs_individuthese["jury_rapp2_these"],$type_fichier).
					 "\t".txt2type($row_rs_individuthese["jury_autres_membres_these"],$type_fichier);
			$ligne.="\t".txt2type($row_rs_individuthese["profession_postdoc"],$type_fichier)."\t".txt2type($row_rs_individuthese["employeur_postdoc"],$type_fichier);
			$ligne.="\t".txt2type($row_rs_individuthese["ville_postdoc"],$type_fichier)."\t".txt2type($row_rs_individuthese["libpays_postdoc"],$type_fichier).
					 "\t".txt2type(aaaammjj2jjmmaaaa($row_rs_individuthese["dateemploi_postdoc"],"/"),$type_fichier)."\t".txt2type($row_rs_individuthese["note_doct"],$type_fichier);
		}
	}
	$tab=explode("\t",$ligne);
	$l++;$c=0;
	foreach($tab as $val)
	{	$objWorksheet->setCellValueByColumnAndRow($c,$l , utf8_encode(conv_car_speciaux($val))); 
		$c++;
	}
}
$objPHPExcel->getActiveSheet()->setTitle('Personnel '.$GLOBALS['acronymelabo']);
$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
/*$objWriter->save(realpath( getcwd()).'\\temp\\test.xlsx');*/
header('Content-type: '.$GLOBALS['file_types_mime_array']['xlsx']);
header('Content-Disposition: attachment; filename="listepresentpartifutur.xlsx"');//
$objWriter->save('php://output');
$objPHPExcel->disconnectWorksheets(); 
unset($objPHPExcel); 
if(isset($rs_individu)) {mysql_free_result($rs_individu);}
if(isset($rs_individutheme)) {mysql_free_result($rs_individutheme);}
if(isset($rs_individuemploi)) {mysql_free_result($rs_individuemploi);}/*  */
exit;
?>




