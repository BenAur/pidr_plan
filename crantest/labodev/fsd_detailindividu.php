<?php  require_once('_const_fonc.php'); ?>
<?php 
$passwd=isset($_GET['passwd'])?$_GET['passwd']:(isset($_POST['passwd'])?$_POST['passwd']:"");
echo $passwd;
if($passwd!='passwd_fsd')
{ exit;
}
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");

$tab_car_speciaux=array();
$rs=mysql_query("select * from _car_speciaux");
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_car_speciaux[$row_rs['caractere']]=$row_rs['carsubstitue'];
}

$rs=mysql_query("select * from pays");
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_pays[$row_rs["codepays"]]=$row_rs;
}//nat.libpays as libnat, pays.libpays as libpays_naiss, pays_etab_orig.libpays as libpays_etab_orig,
$query_rs_individu=	"select if(civilite.codeciv='','',civilite.lib_fr) as libciv,if(nomjf='',nom,nomjf) as nompatronymique, prenom,if(nomjf<>'',nom,'') as nommarital,".
										" concat(' ',SUBSTRING(date_naiss,9,2),'/',SUBSTRING(date_naiss,6,2),'/',SUBSTRING(date_naiss,1,4)) as date_naiss,".
										" if(individu.codeciv='1','Masculin','Feminin') as sexe,ville_naiss,codepays_naiss, codenat,codepays_etab_orig,  ".
										" libcourttypepieceidentite as libtypepieceidentite,autretypepieceidentite, numeropieceidentite,".
										" etab_orig, adresse_etab_orig,ville_etab_orig, libsituationprofessionnelle, autresituationprofessionnelle,".
										" libtypeacceszrr,codetypeaccesvirtuelzrr,codetypeacceszrrglobal,numdossierzrr,".
										" intituleposte, descriptionmission,codestageformationremunere,libnaturefinancement,autrenaturefinancement,".
										" montantfinancement,liboriginefinancement,libsourcefinancement,".
										" cat.codelibcat, corps.codecorps, libcat_fr as libcat,codelieu,".
										" datedeb_sejour_prevu,datefin_sejour_prevu,codelibtypestage, libcourttypestage as libtypestage,codereferent".// titre_prog_rech as titresujet, prog_rech as sujet,
										//" codedomainescientifique1, codedisciplinescientifique1, codeobjectifscientifique1, ".
										//" codedomainescientifique2, codedisciplinescientifique2, codeobjectifscientifique2 ".
										" from individu,individusejour,civilite, typepieceidentite,situationprofessionnelle, typeacceszrr,".
										" corps,cat, typestage,zrr_naturefinancement,zrr_originefinancement,zrr_sourcefinancement".
										" where individu.codeciv=civilite.codeciv".
										" and individu.codeindividu=individusejour.codeindividu".
										" and individusejour.codetypepieceidentite=typepieceidentite.codetypepieceidentite".
										" and individusejour.codesituationprofessionnelle=situationprofessionnelle.codesituationprofessionnelle".
										" and individusejour.codetypeacceszrr=typeacceszrr.codetypeacceszrr".
										" and individusejour.codenaturefinancement=zrr_naturefinancement.codenaturefinancement".
										" and individusejour.codeoriginefinancement=zrr_originefinancement.codeoriginefinancement".
										" and individusejour.codesourcefinancement=zrr_sourcefinancement.codesourcefinancement".
										" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
										" and individusejour.codetypestage=typestage.codetypestage".
										" and individusejour.codeindividu=".GetSQLValueString($codeindividu,"text")." and individusejour.numsejour=".GetSQLValueString($numsejour,"text");
$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());

if($row_rs_individu=mysql_fetch_assoc($rs_individu))
{ $row_rs_individu["libpays_naiss"]=$tab_pays[$row_rs_individu["codepays_naiss"]]["libpayszrr"];
	$row_rs_individu["libnat"]=$tab_pays[$row_rs_individu["codenat"]]["libpayszrr"];
	$row_rs_individu["libpays_etab_orig"]=$tab_pays[$row_rs_individu["codepays_etab_orig"]]["libpayszrr"];
	// situation professionnelle et type acces
	$codelibcat=$row_rs_individu["codelibcat"];
	$codecorps=$row_rs_individu["codecorps"];
	$tab_duree=duree_aaaammjj($row_rs_individu["datedeb_sejour_prevu"],$row_rs_individu["datefin_sejour_prevu"]);
	if($tab_duree)//pas d'erreur dans les dates et datedeb <= datefin		
	{ $row_rs_individu["duree"]=($tab_duree["a"]!=0?$tab_duree["a"].'a ':'').($tab_duree["m"]!=0?$tab_duree["m"].'m ':'').($tab_duree["j"]!=0?$tab_duree["j"].'j ':'');
	}
	else
	{ $row_rs_individu["duree"]='';
	}
	//generation du numero de dossier et zrr
	//$row_rs_individu["numdossier"]=date('Y-m').'-';
	for($i=1;$i<=4;$i++)
	{ $row_rs_individu["libzrr".$i]="";
	}
	$rs=mysql_query("select libzrr from zrr where codelieu=".GetSQLValueString($row_rs_individu["codelieu"],"text"));
	$i=1;
	while($row_rs=mysql_fetch_assoc($rs))
	{ /* if($i==1)
		{ $row_rs_individu["numdossier"].=$row_rs["libzrr"].'-';
		} */
		$row_rs_individu["libzrr".$i]=$row_rs["libzrr"];
		$i++;
	}
	// par defaut on ne remplit pas le labo accueil recherche : uniquement si sujet
	$row_rs_individu["liblaboaccueil"]="";
	// sujet
	$a_un_resp_scientifique=false;
	if($codelibcat=='DOCTORANT' || $codelibcat=='POSTDOC' || $codelibcat=='STAGIAIRE' || $codelibcat=='EXTERIEUR' )
	{ $row_rs_individu["liblaboaccueil"]=construitliblabo(array('appel'=>'fsd_detailindividu'));
		if($row_rs_individu["codelibtypestage"]=='COLLEGE' || $row_rs_individu["codelibtypestage"]=='LYCEE')
		{ $row_rs_individu["titresujet"]="Premiers contacts avec un laboratoire de recherche";
		}
		$query_rs_sujet="select * from sujet,individusujet".
										" where sujet.codesujet=individusujet.codesujet".
										" and individusujet.codeindividu=".GetSQLValueString($codeindividu,"text").
										" and individusujet.numsejour=".GetSQLValueString($numsejour,"text");
		$rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
		if($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
		{ // 29/10/2015 : il peut rester des donnees non effacees du fait de multiples modifs des programmes dans le temps
			$row_rs_individu["intituleposte"]="";
			$row_rs_individu["descriptionmission"]="";
			// fin 29/10/2015
			$row_rs_individu["titresujet"]=$row_rs_sujet["titre_fr"];
			$row_rs_individu["sujet"]=$row_rs_sujet["descr_fr"];
			$row_rs_individu["codedomainescientifique1"]=$row_rs_sujet["codedomainescientifique1"];
			$row_rs_individu["codedisciplinescientifique1"]=$row_rs_sujet["codedisciplinescientifique1"];
			$row_rs_individu["codeobjectifscientifique1"]=$row_rs_sujet["codeobjectifscientifique1"];
			$row_rs_individu["codeobjectifscientifiqueautre1"]=$row_rs_sujet["codeobjectifscientifiqueautre1"];
			$row_rs_individu["autreobjectifscientifique1"]=$row_rs_sujet["autreobjectifscientifique1"];
			$row_rs_individu["codedomainescientifique2"]=$row_rs_sujet["codedomainescientifique2"];
			$row_rs_individu["codedisciplinescientifique2"]=$row_rs_sujet["codedisciplinescientifique2"];
			$row_rs_individu["codeobjectifscientifique2"]=$row_rs_sujet["codeobjectifscientifique2"];
			$row_rs_individu["codeobjectifscientifiqueautre2"]=$row_rs_sujet["codeobjectifscientifiqueautre2"];
			$row_rs_individu["autreobjectifscientifique2"]=$row_rs_sujet["autreobjectifscientifique2"];
			$query_rs_dir="select nom,prenom,email,tel,liblongcorps_fr as libcorps from sujetdir,individu,individusejour,corps".
										" where sujetdir.codedir=individu.codeindividu and individu.codeindividu=individusejour.codeindividu".
										" and individusejour.codecorps=corps.codecorps".
										" and sujetdir.numordre='1' and sujetdir.codesujet=".GetSQLValueString($row_rs_sujet["codesujet"],"text").
										" and ".intersectionperiodes('individusejour.datedeb_sejour_prevu','individusejour.datefin_sejour_prevu',GetSQLValueString($row_rs_individu["datedeb_sejour_prevu"],"text"),GetSQLValueString($row_rs_individu["datefin_sejour_prevu"],"text"));
			$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
			if($row_rs_dir=mysql_fetch_assoc($rs_dir))
			{ $row_rs_individu["nomprenomrespscientifique"]=$row_rs_dir["prenom"].' '.$row_rs_dir["nom"];
				$row_rs_individu["emailrespscientifique"]=$row_rs_dir["email"];
				$row_rs_individu["telrespscientifique"]=$row_rs_dir["tel"];
				$row_rs_individu["fonctionrespscientifique"]=$row_rs_dir["libcorps"];
				$a_un_resp_scientifique=true;
			}
			// domaine scientifique, discipline scientifique, Objectif d'étude/ Secteur industriel d'activité
			for($i=1;$i<=2;$i++)
			{ $rs=mysql_query("select libfsddomainescientifique as libdomainescientifique from sujet_domainescientifique".
											" where codedomainescientifique=".GetSQLValueString($row_rs_sujet["codedomainescientifique".$i],"text"));
				$row_rs=mysql_fetch_assoc($rs);
				$row_rs_individu["libdomainescientifique".$i]=$row_rs["libdomainescientifique"];
					
				$rs=mysql_query("select libfsddisciplinescientifique as libdisciplinescientifique from sujet_disciplinescientifique".
												" where codedisciplinescientifique=".GetSQLValueString($row_rs_sujet["codedisciplinescientifique".$i],"text"));
				$row_rs=mysql_fetch_assoc($rs);
				$row_rs_individu["libdisciplinescientifique".$i]=$row_rs["libdisciplinescientifique"];
					
				$rs=mysql_query("select libfsdobjectifscientifique as libobjectifscientifique from sujet_objectifscientifique".
												" where codeobjectifscientifique=".GetSQLValueString($row_rs_sujet["codeobjectifscientifique".$i],"text"));
				$row_rs=mysql_fetch_assoc($rs);
				$row_rs_individu["libobjectifscientifique".$i]=$row_rs["libobjectifscientifique"];
					
				$rs=mysql_query("select libfsdobjectifscientifique as libobjectifscientifiqueautre from sujet_objectifscientifique".
												" where codeobjectifscientifique=".GetSQLValueString($row_rs_sujet["codeobjectifscientifiqueautre".$i],"text"));
				$row_rs=mysql_fetch_assoc($rs);
				$row_rs_individu["libobjectifscientifiqueautre".$i]=$row_rs["libobjectifscientifiqueautre"];	
			}
		}
		// niveau diplome prepare
		$row_rs_individu["niveau_diplome_prepare"]="";
		$row_rs_individu["autre_niveau_diplome_prepare"]="";
		$row_rs_individu["etab_delivrance_diplome"]="";
		if($codelibcat=='STAGIAIRE')
		{ if($row_rs_individu["codelibtypestage"]=="MASTER")
			{ $row_rs_individu["niveau_diplome_prepare"]="Master 2";
				$row_rs_individu["etab_delivrance_diplome"]="Université de Lorraine";
			}
			elseif($row_rs_individu["codelibtypestage"]=="MASTER1")
			{ $row_rs_individu["niveau_diplome_prepare"]="Master 1";
				$row_rs_individu["etab_delivrance_diplome"]="Université de Lorraine";
			}
			else
			{ $row_rs_individu["niveau_diplome_prepare"]="autre";
				$row_rs_individu["autre_niveau_diplome_prepare"]=$row_rs_individu["libtypestage"];
				$row_rs_individu["etab_delivrance_diplome"]=$row_rs_individu["etab_orig"];
			}
		}
		elseif($codelibcat=='DOCTORANT')
		{ $row_rs_individu["niveau_diplome_prepare"]="Doctorat";
			$row_rs_individu["etab_delivrance_diplome"]="Université de Lorraine";
		}
		// un postdoc peut avoir un intitule de poste/mission et pas de sujet
		elseif($codelibcat=='POSTDOC')
		{ if(isset($row_rs_individu["titresujet"]) && $row_rs_individu["titresujet"]!='')
			{ $row_rs_individu["niveau_diplome_prepare"]="Post-Doc";
				$row_rs_individu["etab_delivrance_diplome"]="Université de Lorraine";
			}
			else
			{	$row_rs_individu["liblaboaccueil"]="";
			}
		}
	}
	if(!$a_un_resp_scientifique)
	{ // par defaut de sujet, le referent est resp. d'accueil
		$rs_referent=mysql_query("select nom,prenom,email,tel,liblongcorps_fr as libcorps from individu,individusejour,corps".
												" where individu.codeindividu=individusejour.codeindividu".
												" and individusejour.codecorps=corps.codecorps".
												" and individu.codeindividu=".GetSQLValueString($row_rs_individu["codereferent"],"text").
												" and ".intersectionperiodes('individusejour.datedeb_sejour_prevu','individusejour.datefin_sejour_prevu',GetSQLValueString($row_rs_individu["datedeb_sejour_prevu"],"text"),GetSQLValueString($row_rs_individu["datefin_sejour_prevu"],"text")));
		if($row_rs_referent=mysql_fetch_assoc($rs_referent))
		{ $row_rs_individu["nomprenomrespscientifique"]=$row_rs_referent["prenom"].' '.$row_rs_referent["nom"];
			$row_rs_individu["emailrespscientifique"]=$row_rs_referent["email"];
			$row_rs_individu["telrespscientifique"]=$row_rs_referent["tel"];
			$row_rs_individu["fonctionrespscientifique"]=$row_rs_referent["libcorps"];
		}
	}
	// espace devant les dates = excel inverse jj et mm !!!!
	$row_rs_individu["datedeb_sejour_prevu"]=' '.aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour_prevu"],"/");
	$row_rs_individu["datefin_sejour_prevu"]=' '.aaaammjj2jjmmaaaa($row_rs_individu["datefin_sejour_prevu"],"/");
	$row_rs_individu["libetablissement"]=construitliblabo(array('appel'=>'fsd_detailindividu'));
	$row_rs_individu["libtutelle"]=html_entity_decode("F - Minist&egrave;re de l'enseignement sup&eacute;rieur et de la recherche");
	$row_rs_individu["avisdirecteur"]="AVIS FAVORABLE - Didier WOLF, le ".date("d/m/Y");
	
	$tab_champ_cell=array("numdossierzrr"=>"D2","libciv"=>"C6","nompatronymique"=>"E6","prenom"=>"H6","nommarital"=>"C7","date_naiss"=>"G7",
												"sexe"=>"I7","ville_naiss"=>"C8","libpays_naiss"=>"E8","libnat"=>"G8",
												"libtypepieceidentite"=>"D11","autretypepieceidentite"=>"F11","numeropieceidentite"=>"H11",
												"libzrr1"=>"C21", "libzrr2"=>"E21", "libzrr3"=>"G21", "libzrr4"=>"I21",
												"libtypeacceszrr"=>"C23",
												"libsituationprofessionnelle"=>"E16","autresituationprofessionnelle"=>"G16","typeacces"=>"C23","duree"=>"E23","datedeb_sejour_prevu"=>"G23","datefin_sejour_prevu"=>"I23",
												"niveau_diplome_prepare"=>"D26","autre_niveau_diplome_prepare"=>"G26","etab_delivrance_diplome"=>"E27",
												"titresujet"=>"D41", "sujet"=>"C43",
												"libdomainescientifique1"=>"C34","libdisciplinescientifique1"=>"C35","libobjectifscientifique1"=>"C36",
												"libobjectifscientifiqueautre1"=>"C37","autreobjectifscientifique1"=>"C39",
												"libdomainescientifique2"=>"F34","libdisciplinescientifique2"=>"F35","libobjectifscientifique2"=>"F36",
												"libobjectifscientifiqueautre2"=>"F37","autreobjectifscientifique2"=>"F39",
												"libetablissement"=>"C19","libtutelle"=>"F20","liblaboaccueil"=>"C29","avisdirecteur"=>"C46");
	if($row_rs_individu["etab_orig"]!='')
	{ $tab_champ_cell=array_merge($tab_champ_cell,array("etab_orig"=>"D13","adresse_etab_orig"=>"D14","ville_etab_orig"=>"G14","libpays_etab_orig"=>"I14"));											
	}
	echo 'feuille::==Demande'.'<br>';
	foreach($tab_champ_cell as $champ=>$cell)
	{ if(isset($row_rs_individu[$champ]))
		{ echo $cell.'::=='.utf8_encode(conv_car_speciaux($row_rs_individu[$champ])).'<br>';//
		}
	}
	echo 'feuille::=='.utf8_encode('Complément').'<br>';
	$tab_champ_cell=array("codetypeaccesvirtuelzrr"=>"C20" ,"codetypeacceszrrglobal"=>"D23","intituleposte"=>"C25","descriptionmission"=>"C26",
												"codestageformationremunere"=>"D28","libnaturefinancement"=>"G28","autrenaturefinancement"=>"I28",
												"montantfinancement"=>"C29","liboriginefinancement"=>"F29","libsourcefinancement"=>"I29",
												"nomprenomrespscientifique"=>"E32","fonctionrespscientifique"=>"H32","telrespscientifique"=>"C33","emailrespscientifique"=>"E33");
	foreach($tab_champ_cell as $champ=>$cell)
	{ if(isset($row_rs_individu[$champ]))
		{ echo $cell.'::=='.utf8_encode(conv_car_speciaux($row_rs_individu[$champ])).'<br>';
		}
	}
}
if(isset($rs_individu)) mysql_free_result($rs_individu);
if(isset($rs_sujet)) mysql_free_result($rs_sujet);
if(isset($rs_dir)) mysql_free_result($rs_dir);
if(isset($rs)) mysql_free_result($rs);

?>

