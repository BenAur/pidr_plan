<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,true,true);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$type='csv';
if(!array_key_exists('admingestfin',$tab_roleuser) && !array_key_exists('du',$tab_roleuser) && !array_key_exists('sif',$tab_roleuser) /*20170420 && !array_key_exists('gestul',$tab_roleuser) && !array_key_exists('gestcnrs',$tab_roleuser) */)
{ echo "Acces restreint";
	exit;
}
if(!isset($_SESSION['b_contrats_voir_encours'])) { $_SESSION['b_contrats_voir_encours']=true;}
if(!isset($_SESSION['b_contrats_voir_futurs'])) { $_SESSION['b_contrats_voir_futurs']=true;}
if(!isset($_SESSION['b_contrats_voir_passes'])) { $_SESSION['b_contrats_voir_passes']=true;}
if(!isset($_SESSION['b_contrats_voir_signes'])) { $_SESSION['b_contrats_voir_signes']=true;}

// ----------------------- liste des contrats a afficher 
$clause_from='';
$clause_where='';
$clause_group_by='';
$query_contrat =	"SELECT contrat.*, libcourtprojet as libprojet,libcourtorggest as liborggest, libcourttype  as libtype,".
									" libcourtorgfinanceur as liborgfinanceur,libcourtsecteur  as libsecteur, numclassif, libcourtclassif as libclassif,structure.libcourt_fr as libtheme,".
									" concat(substring(respscientifique.prenom,1,1),' ',respscientifique.nom) as nomprenomrespscientifique".
									" FROM contrat, cont_orggest,cont_projet,cont_secteur, cont_type, cont_orgfinanceur, cont_classif,structure, individu as respscientifique".
									$clause_from.
									" WHERE contrat.codeorggest=cont_orggest.codeorggest ". 
									" and contrat.codetype=cont_type.codetype".
									" and contrat.codeprojet=cont_projet.codeprojet".
									" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
									" and contrat.codesecteur=cont_secteur.codesecteur".
									" and contrat.codeclassif=cont_classif.codeclassif and contrat.codecontrat<>''".
									" and contrat.codetheme=structure.codestructure".
									" and contrat.coderespscientifique=respscientifique.codeindividu".
 									" and (".($_SESSION['b_contrats_voir_encours']?periodeencours('datedeb_contrat','datefin_contrat')." or ":"").
													($_SESSION['b_contrats_voir_futurs']?periodefuture('datedeb_contrat')." or ":"").
													($_SESSION['b_contrats_voir_passes']?periodepassee('datefin_contrat')." or ":"")." false)". 
									($_SESSION['b_contrats_voir_signes']?" and date_signature_contrat<>'' ":"").
 									$clause_where.
									($clause_group_by==""?"":" GROUP BY ".$clause_group_by).
									" ORDER BY datedeb_contrat desc";// desc = du plus récent au plus ancien
$rs_contrat = mysql_query($query_contrat) or die(mysql_error());

//header('Content-type: text/plain');
$ligne="Numero"."\t"."Date d'effet"."\t"."Duree mois"."\t"."Fin"."\t"."Signature"."\t"."Gestionnaire"."\t"."Projet"."\t"."Reference du contrat"."\t"."Acronyme"."\t".
			"Ref. programme long"."\t"."Type"."\t"."Financeur"."\t"."Secteur"."\t".$GLOBALS['libcourt_theme_fr']."\t"."Responsable scientifique"."\t"."Partenaires"."\t".
			"Code Classification"."\t"."Classification"."\t"."Sujet"."\t"."Perm mois"."\t"."Pers mois"."\t"."HT TTC"."\t"."Montant euro";
// complete par annees de ventilation
$rs=mysql_query("SELECT min(annee) as annee_premiere,max(annee) as annee_derniere from contratmontantannee") or die(mysql_error());
$row_rs=mysql_fetch_assoc($rs);
$annee_premiere=$row_rs['annee_premiere'];
$annee_derniere=$row_rs['annee_derniere'];
for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
{ $ligne.="\t".$annee;
}
$objPHPExcel = new PHPExcel();
$tab=explode("\t",$ligne);
$objWorksheet = $objPHPExcel-> getActiveSheet();
$l=1;$c=0;
foreach($tab as $val)
{	$objWorksheet->setCellValueByColumnAndRow( $c, $l, $val);//utf8_encode(conv_car_speciaux())
	$c++;
}

while($row_rs_contrat=mysql_fetch_assoc($rs_contrat))
{	$codecontrat=$row_rs_contrat['codecontrat'];
	$rs=mysql_query("SELECT libcourtpart as libpart".
									" from contratpart,cont_part". 
									" where contratpart.codepart=cont_part.codepart and contratpart.codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	$listepartenaire='';
	while($row_rs=mysql_fetch_assoc($rs))
	{ $listepartenaire.=$row_rs['libpart'].' ';
	}
	if($row_rs_contrat['codedoctorant']!='')
	{ // distinct ne devrait pas etre utilise mais pour l'instant un individu peut avoir deux s&eacute;jours en cours, 0 (et 1) permet de mettre l'enr vide en 1ere pos
		$query_doctorant_sujet="SELECT sujet.titre_fr as titre_these". 
													 " FROM individu,individusejour ".
													 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
													 " left join sujet on individusujet.codesujet=sujet.codesujet".
													 " WHERE individu.codeindividu=individusejour.codeindividu".
													 " and individu.codeindividu=".GetSQLValueString($row_rs_contrat['codedoctorant'], "text");
		$rs_doctorant_sujet=mysql_query($query_doctorant_sujet) or die(mysql_error());
		if($row_rs_doctorant_sujet=mysql_fetch_assoc($rs_doctorant_sujet))
		{ $row_rs_contrat['sujet']=$row_rs_doctorant_sujet['titre_these'];
		}
	}
	$ligne=$codecontrat."\t".txt2type(aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/'),$type)."\t".txt2type($row_rs_contrat['duree_mois'],$type)."\t".txt2type(aaaammjj2jjmmaaaa($row_rs_contrat['datefin_contrat'],'/'),$type)."\t".txt2type(aaaammjj2jjmmaaaa($row_rs_contrat['date_signature_contrat'],'/'),$type)."\t".
				txt2type($row_rs_contrat['liborggest'],$type)."\t".txt2type($row_rs_contrat['libprojet'],$type)."\t".txt2type($row_rs_contrat['ref_contrat'],$type)."\t".txt2type($row_rs_contrat['acronyme'],$type)."\t".txt2type($row_rs_contrat['ref_prog_long'],$type)."\t".
				txt2type($row_rs_contrat['libtype'],$type)."\t".txt2type($row_rs_contrat['liborgfinanceur'],$type)."\t".txt2type($row_rs_contrat['libsecteur'],$type)."\t".txt2type($row_rs_contrat['libtheme'],$type)."\t".txt2type($row_rs_contrat['nomprenomrespscientifique'],$type);
	$ligne.="\t".$listepartenaire."\t".txt2type($row_rs_contrat['numclassif'],$type)."\t".txt2type($row_rs_contrat['libclassif'],$type)."\t".
				txt2type($row_rs_contrat['sujet'],$type)."\t".txt2type(str_replace('.',',',$row_rs_contrat['permanent_mois']),$type)."\t".txt2type(str_replace('.',',',$row_rs_contrat['personnel_mois']),$type)."\t".txt2type($row_rs_contrat['ht_ttc'],$type)."\t".
				txt2type(str_replace('.',',',$row_rs_contrat['montant_ht']),$type);
	$rs=mysql_query("SELECT annee,montant from contratmontantannee".
									" where codecontrat=".GetSQLValueString($codecontrat, "text").
									" order by numordre") or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);
	for($annee=$annee_premiere;$annee<=$annee_derniere;$annee++)
	{ $ligne.="\t";
		if(isset($row_rs['montant']) && $row_rs['annee']==$annee)
		{ $ligne.=txt2type(str_replace('.',',',$row_rs['montant']),$type);
			$row_rs=mysql_fetch_assoc($rs);
		}
		
	}
	$tab=explode("\t",$ligne);
	$l++;$c=0;
	foreach($tab as $val)
	{	$objWorksheet->setCellValueByColumnAndRow($c,$l , utf8_encode(conv_car_speciaux($val))); 
		$c++;
	}

}
$objPHPExcel->getActiveSheet()->setTitle("Contrats ".($_SESSION['b_contrats_voir_encours']?"En cours ":"").($_SESSION['b_contrats_voir_futurs']?"Futurs ":"").($_SESSION['b_contrats_voir_passes']?"Passes":""));
$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
header('Content-type: '.$GLOBALS['file_types_mime_array']['xlsx']);
header('Content-Disposition: attachment; filename="Contrats.xlsx"');//
$objWriter->save('php://output');
$objPHPExcel->disconnectWorksheets(); 
unset($objPHPExcel); 

if(isset($rs)) mysql_free_result($rs);
if(isset($rs_contrat))mysql_free_result($rs_contrat);
exit;
?>
