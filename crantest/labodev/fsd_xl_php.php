<?php  require_once('_const_fonc.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
</head>
<body>
<?php

/* $codeuser=deconnecte_ou_connecte();
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
$peut_etre_admin=estrole('sif',$tab_roleuser) || estrole('admingestfin',$tab_roleuser) || estrole('du',$tab_roleuser || $admin_bd);
if($peut_etre_admin==false)
{?> acc&eacute;s restreint !!!
<?php
exit;
} */
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
 
$rs=mysql_query("select * from pays") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_pays[$row_rs["codepays"]]=$row_rs;
}//nat.libpays as libnat, pays.libpays as libpays_naiss, pays_etab_orig.libpays as libpays_etab_orig,
$query_rs_individu=	"select if(civilite.codeciv='','',civilite.lib_fr) as libciv,if(nomjf='',nom,nomjf) as nompatronymique, prenom,if(nomjf<>'',nom,'') as nommarital,".
										" concat(' ',SUBSTRING(date_naiss,9,2),'/',SUBSTRING(date_naiss,6,2),'/',SUBSTRING(date_naiss,1,4)) as date_naiss, ".
										" adresse_pers, concat(codepostal_pers,' - ',ville_pers) as codepostal_ville_pers, codepays_pers, email as email_accueilli,".
										" if(individu.codeciv='1','Masculin (male)','Feminin (female)') as sexe,concat(codepostal_naiss,' - ',ville_naiss) as codepostal_ville_naiss,codepays_naiss, codenat,codepays_etab_orig,  ".
										" libcourttypepieceidentite as libtypepieceidentite,autretypepieceidentite, numeropieceidentite,".
										" etab_orig, adresse_etab_orig,concat(codepostal_etab_orig,' - ',ville_etab_orig) as codepostal_ville_etab_orig, libsituationprofessionnelle, autresituationprofessionnelle,".
										" libtypeacceszrr,libphysiquevirtuelzrr,codetypeacceszrrglobal,numdossierzrr,".
										" intituleposte as titresujet, descriptionmission as missionsujet,codestageformationremunere,libnaturefinancement,autrenaturefinancement,".
										" montantfinancement,liboriginefinancement,libsourcefinancement,".
										" cat.codelibcat, corps.codecorps, libcat_fr as libcat,codelieu,".
										" datedeb_sejour,datefin_sejour,codelibtypestage, libcourttypestage as libtypestage,codereferent".// titre_prog_rech as titresujet, prog_rech as sujet,
										" from individu,individusejour,civilite, typepieceidentite,situationprofessionnelle, typeacceszrr, zrr_physiquevirtuel,".
										" corps,cat, typestage,zrr_naturefinancement,zrr_originefinancement,zrr_sourcefinancement".
										" where individu.codeciv=civilite.codeciv".
										" and individu.codeindividu=individusejour.codeindividu".
										" and individusejour.codetypepieceidentite=typepieceidentite.codetypepieceidentite".
										" and individusejour.codesituationprofessionnelle=situationprofessionnelle.codesituationprofessionnelle".
										" and individusejour.codetypeacceszrr=typeacceszrr.codetypeacceszrr".
										" and individusejour.codephysiquevirtuelzrr= zrr_physiquevirtuel.codephysiquevirtuelzrr".
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
	$row_rs_individu["libpays_pers"]=$tab_pays[$row_rs_individu["codepays_pers"]]["libpayszrr"];
	$row_rs_individu["libpays_etab_orig"]=$tab_pays[$row_rs_individu["codepays_etab_orig"]]["libpayszrr"];
	// situation professionnelle et type acces
	$codelibcat=$row_rs_individu["codelibcat"];
	$codecorps=$row_rs_individu["codecorps"];
	$tab_duree=duree_aaaammjj($row_rs_individu["datedeb_sejour"],$row_rs_individu["datefin_sejour"]);
	if($tab_duree)//pas d'erreur dans les dates et datedeb <= datefin		
	{ $row_rs_individu["duree"]=($tab_duree["a"]!=0?$tab_duree["a"].'a ':'').($tab_duree["m"]!=0?$tab_duree["m"].'m ':'').($tab_duree["j"]!=0?$tab_duree["j"].'j ':'');
	}
	else
	{ $row_rs_individu["duree"]='';
	}
	//generation du numero de dossier et zrr
	//$row_rs_individu["numdossier"]=date('Y-m').'-';
	$row_rs_individu["libzrr"]="";
	$query_rs=("select libzrr,adresselieu from zrr,lieu".
									" where zrr.codelieu=lieu.codelieu and lieu.codelieu=".GetSQLValueString($row_rs_individu["codelieu"],"text").
									" order by numordre");
	$rs=mysql_query($query_rs);
	if($row_rs=mysql_fetch_assoc($rs))
	{ $row_rs_individu["libzrr"]=$row_rs["libzrr"];
		$row_rs_individu["adressezrr"]=$row_rs["adresselieu"];
	}
	// par defaut on ne remplit pas le labo accueil recherche : uniquement si sujet
	$row_rs_individu["liblaboaccueil"]="";
	// sujet
	$a_un_resp_scientifique=false;
	if($codelibcat=='DOCTORANT' || $codelibcat=='POSTDOC' || $codelibcat=='STAGIAIRE' || $codelibcat=='EXTERIEUR' )
	{ $row_rs_individu["liblaboaccueil"]=construitliblabo(array('appel'=>'fsd_detailindividu'));
		if($row_rs_individu["codelibtypestage"]=='COLLEGE' || $row_rs_individu["codelibtypestage"]=='LYCEE')
		{ $row_rs_individu["missionsujet"]="Premiers contacts avec un laboratoire de recherche";
		}
		$query_rs_sujet="select * from sujet,individusujet".
										" where sujet.codesujet=individusujet.codesujet".
										" and individusujet.codeindividu=".GetSQLValueString($codeindividu,"text").
										" and individusujet.numsejour=".GetSQLValueString($numsejour,"text");
		$rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
		if($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
		{ $row_rs_individu["titresujet"]=$row_rs_sujet["titre_fr"];
			$row_rs_individu["missionsujet"]=$row_rs_sujet["descr_fr"];
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
										" and ".intersectionperiodes('individusejour.datedeb_sejour','individusejour.datefin_sejour',GetSQLValueString($row_rs_individu["datedeb_sejour"],"text"),GetSQLValueString($row_rs_individu["datefin_sejour"],"text"));
			$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
			if($row_rs_dir=mysql_fetch_assoc($rs_dir))
			{ $row_rs_individu["nomprenomrespscientifique"]=$row_rs_dir["prenom"].' '.$row_rs_dir["nom"];
				$row_rs_individu["emailrespscientifique"]=$row_rs_dir["email"];
				$row_rs_individu["telrespscientifique"]=$row_rs_dir["tel"];
				$row_rs_individu["fonctionrespscientifique"]=$row_rs_dir["libcorps"];
				$a_un_resp_scientifique=true;
			}
			// domaine scientifique, discipline scientifique, Objectif d'étude/ Secteur industriel d'activité
			$rs=mysql_query("select libfsddomainescientifique as libdomainescientifique from sujet_domainescientifique".
										" where codedomainescientifique=".GetSQLValueString($row_rs_sujet["codedomainescientifique1"],"text"));
			$row_rs=mysql_fetch_assoc($rs);
			$row_rs_individu["libdomainescientifique"]=$row_rs["libdomainescientifique"];
				
			$rs=mysql_query("select libfsddisciplinescientifique as libdisciplinescientifique from sujet_disciplinescientifique".
											" where codedisciplinescientifique=".GetSQLValueString($row_rs_sujet["codedisciplinescientifique1"],"text"));
			$row_rs=mysql_fetch_assoc($rs);
			$row_rs_individu["libdisciplinescientifique"]=$row_rs["libdisciplinescientifique"];
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
		elseif($codelibcat=='POSTDOC' && isset($row_rs_individu["titresujet"]))
		{ if($row_rs_individu["titresujet"]!='')
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
												" and ".intersectionperiodes('individusejour.datedeb_sejour','individusejour.datefin_sejour',GetSQLValueString($row_rs_individu["datedeb_sejour"],"text"),GetSQLValueString($row_rs_individu["datefin_sejour"],"text")));
		if($row_rs_referent=mysql_fetch_assoc($rs_referent))
		{ $row_rs_individu["nomprenomrespscientifique"]=$row_rs_referent["prenom"].' '.$row_rs_referent["nom"];
			$row_rs_individu["emailrespscientifique"]=$row_rs_referent["email"];
			$row_rs_individu["telrespscientifique"]=$row_rs_referent["tel"];
			$row_rs_individu["fonctionrespscientifique"]=$row_rs_referent["libcorps"];
		}
	}
	// espace devant les dates = excel inverse jj et mm !!!!
	$row_rs_individu["datedeb_sejour"]=' '.aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour"],"/");
	$row_rs_individu["datefin_sejour"]=' '.aaaammjj2jjmmaaaa($row_rs_individu["datefin_sejour"],"/");
	$row_rs_individu["libetablissement"]=html_entity_decode("Universit&eacute; de Lorraine");
	$row_rs_individu["umr"]="UMR".$GLOBALS['num_umr'];
	$row_rs_individu["ministere"]=html_entity_decode("F - Minist&egrave;re de l'enseignement sup&eacute;rieur et de la recherche");
	$row_rs_individu["liblabo"]=$GLOBALS['liblonglabo'];
	$row_rs_individu["libcodeuniteacceuil"]="UMR".$GLOBALS['num_umr'];
	$rs=mysql_query("select nom, prenom, tel, email from individu,structureindividu, structure".
									" where individu.codeindividu=structureindividu.codeindividu and structureindividu.codestructure=structure.codestructure and structure.codelib='du'");
	$row_rs_individu["respzrr"]="";
	if($row_rs=mysql_fetch_assoc($rs))
	{ $row_rs_individu["respzrr"]=$row_rs["prenom"].' '.$row_rs["nom"].' - Directeur';
		$row_rs_individu["respzrrtel"]=$row_rs["tel"];
		$row_rs_individu["respzrrmail"]=$row_rs["email"];
	}
	$row_rs_individu["avisrespzrr"]="FAVORABLE";
	$row_rs_individu["avisfsdchefetab"]="";
	$row_rs_individu["autredemandeencours"]="non (no)";
	$row_rs_individu["autorisationdejaobtenue"]="non (no)";
	$row_rs_individu["habilitedefense"]="non (no)";
	$tab_fsd=array(utf8_encode(html_entity_decode("Formulaire demande d'acc&egrave;s ZRR"))=>	
										array('contenu_cellule'=>	
														array("numdossierzrr"=>"M2",
																	"nompatronymique"=>"C6","prenom"=>"E6","nommarital"=>"G6","sexe"=>"I6","libetablissement"=>"M6",
																	"libtypepieceidentite"=>"C9","numeropieceidentite"=>"E9","date_naiss"=>"G9","codepostal_ville_naiss"=>"I9","umr"=>"M9",
																	"libpays_naiss"=>"C11","libnat"=>"E11","email_accueilli"=>"I11","libzrr"=>"M11",
																	"adresse_pers"=>"D13","codepostal_ville_pers"=>"G13","libpays_pers"=>"I13","adressezrr"=>"M13",
																	"libsituationprofessionnelle"=>"D15","etab_orig"=>"H15","ministere"=>"M15",
																	"adresse_etab_orig"=>"D17","codepostal_ville_etab_orig"=>"G17","libpays_etab_orig"=>"I17","respzrr"=>"M17",
																	"respzrrtel"=>"M19",
																	"respzrrmail"=>"M22",
																	"libtypeacceszrr"=>"C23","liboriginefinancement"=>"F23","montantfinancement"=>"I23",
																	"libphysiquevirtuelzrr"=>"C27","datedeb_sejour"=>"F27","datefin_sejour"=>"I27",
																	"libdomainescientifique"=>"D29","libdisciplinescientifique"=>"H29",
																	"titresujet"=>"D31",
																	"missionsujet"=>"C34",
																	"autredemandeencours"=>"C42","autorisationdejaobtenue"=>"E42",
																	"habilitedefense"=>"D47",
																	"avisrespzrr"=>"M25",
																	"avisfsdchefetab"=>"M32"
																	)));
	$rep='__fsd';
	$objPHPExcel = PHPExcel_IOFactory::load($rep.'/formulaire_2017.xls');//enregistré au préalable au format 97-2003 a partir de l'original xlsx avec excel 2013
	//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
	//$validLocale = PHPExcel_Settings::setLocale('fr');
	/* $objPHPExcel->setActiveSheetIndex("0");
	$objWorksheet = $objPHPExcel->getActiveSheet(); //Demande */
	foreach($tab_fsd as $nomonglet=>$tab_onglet)
	{	$objWorksheet = $objPHPExcel-> getSheetByName($nomonglet);
		foreach($tab_onglet as $contenu_couleur=>$tab_contenu_couleur)
		{	if($contenu_couleur=='contenu_cellule')
			{ foreach($tab_contenu_couleur as $champ=>$cellule)
				{ if(isset($row_rs_individu[$champ]))
					{ echo $champ.' '.$row_rs_individu[$champ].'<br>';$objWorksheet->getCell($cellule)->setValue(utf8_encode(conv_car_speciaux($row_rs_individu[$champ])));// 
					}
				}
			}
		}
	}


$gdImage = imagecreatefromgif($rep.'/filigrane.gif');
// Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
$objDrawing->setName('image');
$objDrawing->setDescription('image');
$objDrawing->setImageResource($gdImage);
$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_GIF);
$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_GIF);
$objDrawing->setOffsetX(1);                       //setOffsetX works properly
$objDrawing->setOffsetY(0);                       //setOffsetY works properly
$objDrawing->setHeight(185);
$objDrawing->setCoordinates('B1');
$objDrawing->setWorksheet($objPHPExcel-> getSheetByName(utf8_encode(html_entity_decode("Formulaire demande d'acc&egrave;s ZRR"))));

$gdImage = imagecreatefromgif($rep.'/filigrane.gif');
// Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
$objDrawing->setName('image');
$objDrawing->setDescription('image');
$objDrawing->setImageResource($gdImage);
$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_GIF);
$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_GIF);
$objDrawing->setOffsetX(1);                       //setOffsetX works properly
$objDrawing->setOffsetY(0);                       //setOffsetY works properly
$objDrawing->setHeight(185);
$objDrawing->setCoordinates('B53');
$objDrawing->setWorksheet($objPHPExcel-> getSheetByName(utf8_encode(html_entity_decode("Formulaire demande d'acc&egrave;s ZRR"))));

$gdImage = imagecreatefromjpeg($rep.'/logo_france.jpg');
// Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
$objDrawing->setName('Sample image');
$objDrawing->setDescription('Sample image');
$objDrawing->setImageResource($gdImage);
$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
$objDrawing->setOffsetX(10);                       //setOffsetX works properly
$objDrawing->setOffsetY(10);                       //setOffsetY works properly
$objDrawing->setHeight(75);
$objDrawing->setCoordinates('B1');
$objDrawing->setWorksheet($objPHPExcel-> getSheetByName(utf8_encode(html_entity_decode("Formulaire demande d'acc&egrave;s ZRR"))));

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($rep.'/formulaire_res.xlsx');		
$objPHPExcel->disconnectWorksheets(); 
unset($objPHPExcel);/**/

}?>
</body>
</html>
