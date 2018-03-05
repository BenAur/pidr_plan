<?php 
$logfile="E:/_applications_data/logs/php_var.log";
if($_SERVER['REMOTE_ADDR']!="193.54.21.1")//serveur web cran
{ $heure=date('d/M/Y H:m:s');
	$log=$_SERVER['REMOTE_ADDR'].' ['.$heure.'] ';
	$log.='[SCRIPT_FILENAME : '.$_SERVER['SCRIPT_FILENAME'].'] '.'[REQUEST_METHOD : '.$_SERVER['REQUEST_METHOD'].'] '.'[HTTP_REFERER : '.$_SERVER['HTTP_REFERER'].'] ';
	$log.=chr(10);
	if(isset($_COOKIE["12plus"]))
	{ if(!file_exists("E:/_applications_data/logs/sess_".$_COOKIE["12plus"]))
		{ $log.="12plus : E:/_applications_data/logs/sess_".$_COOKIE["12plus"]." inexistant";
			$log.=chr(10);
		}
	}
	$fp=fopen($logfile, 'a+');
	fwrite($fp, $log);
	fclose($fp);
}

if(!isset($_REQUEST['pas_de_session'])) // pas de session si la requete le precise => le serveur web labo ne demarre pas de session 12+ pour ne pas declenche le gc_session
{ session_start();
}

if(isset($_REQUEST['db']))
{ $_SESSION['database_labo']=strtolower($_REQUEST['db']); 
}
if(isset($_SESSION['database_labo']) && $_SESSION['database_labo']=='')
{ echo "probleme de configuration : base de donnees non selectionnee";
	exit;
}
include_once('conn_const/const_'.$_SESSION['database_labo'].'.php');
require_once "Mail.php";
require_once ("Mail/mime.php");
include_once('_const_fonc_detail.php');
include_once('_controle_form.php');
include_once('_const_fonc_upload_file.php');
include_once('_const_fonc_mail_popup.php');
include_once('_const_fonc_labo_dependante.php');
// pour eviter les erreurs dans labo.php, controle qu'on est dans le bon repertoire : le nom de la bd doit apparaitre dans le nom du dossier sous la forme $database_labo.'_php'
if(strpos(realpath(getcwd()),$GLOBALS['rep_racine_site12+'])===false)
{ echo "probleme de configuration : ".$GLOBALS['rep_racine_site12+']." pas dans le nom du repertoire de travail ".realpath( getcwd());
	exit;
}

ini_set('display_errors',$GLOBALS['display_errors']);
f();

//PG 20151103
// pas de renvoi du formulaire possible avec reload ou fleches
$time_noreload=isset($_REQUEST['time_noreload'])?$_REQUEST['time_noreload']:"";
$dejaenvoye=(isset($_SESSION['time_noreload']) && $time_noreload!=$_SESSION['time_noreload'])?true:false;
/**/ if($time_noreload!='' && $dejaenvoye)
{ header("HTTP/1.0 204 No Content");//echo '<a href="" onclick="window.history.go(-1)">Utilisez les boutons et liens de la page</a>';
	die();
} 
$_SESSION['time_noreload']=time();
$aujourdhui=date("Y/m/d");
$message_resultat_affiche="";//message affiche apres operation demandee par l'utilisateur
$etat_individu_entete=array('preaccueil'=>'PRE-ACCUEIL',
														'accueil'=>'ACCUEIL',
														'sejourpartinonvalide'=>'SEJOUR NON VISE',
														'present'=>'PERSONNEL',
														'parti'=>'PERSONNEL PARTI',
														'autre'=>'AUTRE'
														);
// PHPExcel et // PG TCPDF

ini_set('include_path', ini_get('include_path').';../phpexcel/Classes/');
ini_set('include_path', ini_get('include_path').';../tcpdf/');

    
include 'PHPExcel.php';
// PHPExcel_Writer_Excel2007
include 'PHPExcel/Writer/Excel2007.php';

// Include the main TCPDF library (search for installation path).
require_once('tcpdf.php');

$tab_car_speciaux=array();
$rs=mysql_query("select * from _car_speciaux");
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_car_speciaux[$row_rs['caractere']]=$row_rs['carsubstitue'];
}

// utilitaire de parcours de tableau recursif avec cle/valeur (array_walk_recursive ne gere pas les cles)
function parcours_tableau_recursif($tableau, $key)
{ if(!is_array($tableau))
	{ echo $key.' '.$tableau;
	}
	else
	{	echo $key." : ";
		foreach($tableau as $key1=>$item)
	  { echo $key." : ".parcours_tableau_recursif($item, $key1);
		}
		echo '<br>';
	}
}


function conv_car_speciaux($chaine)
{ global $tab_car_speciaux; 
	$nouvellechaine=$chaine;
	foreach($tab_car_speciaux as $caractere=>$carsubstitue)
	{ $nouvellechaine=str_replace($caractere,$carsubstitue,$nouvellechaine);
	}
	return $nouvellechaine;
}

function chiffre_fichier($fichier,$cle)
{ $lignemasque="";
  $nboctets = 65536;//nombre d'octets par ligne a chiffrer
  //cree un masque de longueur 65536
  for ($i = 0; $i <= floor($nboctets/strlen($cle)); $i++) 
	{ $lignemasque.= $cle;
	}
  //ouvre le fichier a chiffrer en lecture
  //cree le nouveau fichier
	if (file_exists($fichier))
	{ //verifie presence du fichier
		$fp = fopen($fichier, "rb");
		$fp_temp = fopen($fichier.'_temp', "wb");
		// crypt le fichier et ecrie dans le nouveau fichier par ligne de 65536 bites
		while($ligne = fread($fp, $nboctets))
		{ fputs($fp_temp, $ligne ^ $lignemasque);//effectue un OU EXCLUSIF (XOR) sur les bits 10011 ^ 10110=00101 
		}
		fclose($fp);
		fclose($fp_temp);
		//unlink($fichier);//suprimme l'ancien fichier
		//rename($fichier.'_temp',$fichier);
	}
}
 
// PG fsd 20161223
function fsd_sujet_pdf_php($codeindividu,$numsejour)
{ $erreur="";
	$contenu="";
	$sujet_ou_mission='';
	if($codeindividu!="" && $numsejour!="")
	{ $query_rs_individusujet="select nom, prenom,datedeb_sejour_prevu,datefin_sejour_prevu, sujet.codesujet, sujet.titre_fr as intituleposte, sujet.descr_fr as descriptionmission,".
														" autredir1,autredir2, sujet.codedomainescientifique1, sujet.codedisciplinescientifique1,motscles_fr,conditions_fr,financement_fr,".
														" sujet.codetypesujet,typesujet.libcourt_fr as libtypesujet, statutsujet.libstatutsujet as libstatutsujet".
														" from sujet,individusujet,individu,individusejour, typesujet, statutsujet  ".
														" where sujet.codesujet=individusujet.codesujet".
														" and individusujet.codeindividu=individu.codeindividu".
														" and individu.codeindividu=individusejour.codeindividu".
														" and sujet.codetypesujet=typesujet.codetypesujet and sujet.codestatutsujet=statutsujet.codestatutsujet".
														" and individusujet.codeindividu=".GetSQLValueString($codeindividu,"text").
														" and individusujet.numsejour=".GetSQLValueString($numsejour,"text");
		$rs_individusujet=mysql_query($query_rs_individusujet) or die(mysql_error());
		if($row_rs_individusujet=mysql_fetch_assoc($rs_individusujet))
		{ $sujet_ou_mission='sujet';
		}
		else
		{ $query_rs_individusujet="select nom, prenom,datedeb_sejour_prevu,datefin_sejour_prevu,intituleposte,descriptionmission, codedomainescientifique1, codedisciplinescientifique1".
														" from individu,individusejour  ".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codeindividu=".GetSQLValueString($codeindividu,"text").
														" and individusejour.numsejour=".GetSQLValueString($numsejour,"text");
			$rs_individusujet=mysql_query($query_rs_individusujet) or die(mysql_error());
			$row_rs_individusujet=mysql_fetch_assoc($rs_individusujet);
			$sujet_ou_mission='mission';
		}
		$contenu.='
		<table width="100%" cellspacing="3">
			<tr>
				<td width="20%"><img src="'.$GLOBALS['logolabo'].'"></td>
				<td width="80%" align="center" valign="middle" class="noircalibri11">'.$GLOBALS['liblonglabo'].'</td>
			</tr>
			<tr>
				<td></td>
				<td align="center" valign="middle" width="80%"><b><span class="noirgrascalibri11">'.($sujet_ou_mission=='sujet'?'Sujet de ':'Mission de ').$row_rs_individusujet['nom'].' '.$row_rs_individusujet['prenom'].'</span></b>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
			</tr>';
			if($sujet_ou_mission=='sujet')
			{	$contenu.='
				<tr>
					<td>&nbsp;</td>
					<td align="center"><span class="noircalibri10">('.$row_rs_individusujet['libtypesujet'].' du '.aaaammjj2jjmmaaaa($row_rs_individusujet['datedeb_sejour_prevu'],"/").' au '.aaaammjj2jjmmaaaa($row_rs_individusujet['datefin_sejour_prevu'],"/").')</span></td>
				</tr>
				<tr>
					<td></td>
					<td></td>
				</tr>
				<tr>
					<td nowrap valign="top">
						<span class="bleucalibri10">Encadrant(s) '.$GLOBALS['acronymelabo'].'</span>
					</td>
					<td>';
						$query_rs_dir="select nom, prenom, email from sujetdir,individu".
													" where sujetdir.codedir=individu.codeindividu and sujetdir.codesujet=".GetSQLValueString($row_rs_individusujet['codesujet'], "text").
													" order by sujetdir.numordre";
						$rs_dir=mysql_query($query_rs_dir) or die(mysql_error());
						$first=true;
						while($row_rs_dir=mysql_fetch_assoc($rs_dir))
						{ $contenu.=($first?"":'<span class="bleucalibri10">, </span>');
							$contenu.='<span class="noircalibri10">'.$row_rs_dir['prenom']." ".$row_rs_dir['nom'].' - '.$row_rs_dir['email'].'</span>';
							$first=false;
						}
					$contenu.='
					</td>
				</tr>';
				if($row_rs_individusujet['autredir1']!="")
				{  
				$contenu.='
				<tr>
					<td nowrap valign="top">
						<span class="bleucalibri10">Autre(s) encadrant(s)</span>
					</td>
					<td>
						<span class="noircalibri10">'.$row_rs_individusujet['autredir1'].'</span>
						<span class="noircalibri10">'.($row_rs_individusujet['autredir2']!=""?'<span class="bleucalibri10">, </span><span class="noircalibri10">'.$row_rs_individusujet['autredir2'].'</span>':"").'</span></td>
				</tr>';
				}
			}
			$tab_domainescientifique=array();
			$query_rs =	"SELECT codedomainescientifique,liblongdomainescientifique as libdomainescientifique".
									" FROM sujet_domainescientifique ".
									" order by numordre";
			$rs = mysql_query($query_rs) or die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_domainescientifique[$row_rs['codedomainescientifique']]=$row_rs;
			}
			$tab_disciplinescientifique=array();
			$query_rs =	"SELECT codedisciplinescientifique,codedomainescientifique,liblongdisciplinescientifique as libdisciplinescientifique".
									" FROM sujet_disciplinescientifique". //where codedisciplinescientifique<>''
									" order by numordre";
			$rs = mysql_query($query_rs) or die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_disciplinescientifique[$row_rs['codedisciplinescientifique']]=$row_rs;
			}
			$contenu.='
			<tr>
				<td valign="top" class="bleucalibri10">Intitul&eacute;
				</td>
				<td class="noircalibri10">'.nl2br($row_rs_individusujet['intituleposte']).
				'</td>
			</tr>
			<tr>
				<td valign="top" class="bleucalibri10">Domaine
				</td>
					<td class="noircalibri10">';
					$contenu.=$tab_domainescientifique[$row_rs_individusujet['codedomainescientifique1']]['libdomainescientifique'];
					$contenu.='
				</td>
			</tr>
			<tr>
				<td class="bleucalibri10">Discipline
				</td>
				<td class="noircalibri10">';
				$contenu.=$tab_disciplinescientifique[$row_rs_individusujet['codedisciplinescientifique1']]['libdisciplinescientifique'];
				$contenu.='
        </td>
			</tr>
			<tr>
				<td nowrap valign="top">
					<span class="bleucalibri10">Description</span>
				</td>
				<td align="justify">
					<span class="noircalibri10">'.nl2br($row_rs_individusujet['descriptionmission']).'</span>
				</td>
			</tr>';
			if($sujet_ou_mission=='sujet')
			{	
			if($row_rs_individusujet['codetypesujet']!='05')
			{ if($row_rs_individusujet['motscles_fr']!="")
				{ $contenu.=
				'<tr>
					<td nowrap valign="top">
						<span class="bleucalibri10">Mots-cl&eacute;s</span>
					</td>
					<td>
						<span class="noircalibri10">'.nl2br($row_rs_individusujet['motscles_fr']).'</span>
					</td>
				</tr>';
				}
				/* if($row_rs_individusujet['conditions_fr']!="")
				{ $contenu.=
				'<tr>
					<td valign="top" nowrap>
						<span class="bleucalibri10">Conditions</span>
					</td>
					<td>
						<span class="noircalibri10">'.nl2br($row_rs_individusujet['conditions_fr']).'</span>
					</td>
				</tr>';
				}
				if($row_rs_individusujet['financement_fr']!="")
				{ $contenu.=
				'<tr>
					<td nowrap>
						<span class="bleucalibri10">Financement : </span>
					</td>
					<td>
						<span class="noircalibri10">'.nl2br($row_rs_individusujet['financement_fr']).'</span>
					</td>
				</tr>';
				} */
			}
		}
		$contenu.='</table>';
		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		/* */ $pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($GLOBALS['acronymelabo']);
		$pdf->SetTitle('FSD Sujet');
		$pdf->SetSubject('FSD Sujet');
		$pdf->SetKeywords('');
		
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		
		// set margins
		//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		
		// set image scale factor
		 $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) 
		{
				require_once(dirname(__FILE__).'/lang/eng.php');
				$pdf->setLanguageArray($l);
		} /**/
		$pdf->SetFont('helvetica', '', 9);
		
		$pdf->AddPage();
		$style='<style>'.file_get_contents('styles/normal.css').'</style>';
		$html =  utf8_encode(conv_car_speciaux($contenu));//$row_rs_individu["missionsujet"]
		$pdf->writeHTML($style.$html, true, false, false, false, '');
		$pdf->lastPage();
		
		//pj individu,sejour,fsd 
		$rs_pj=mysql_query("select codelibcatpj,codetypepj,codelibtypepj from typepjindividu where codelibtypepj=".GetSQLValueString('fsd_sujet', "text"));
		if($row_rs_pj=mysql_fetch_assoc($rs_pj))
		{ $codetypepj=$row_rs_pj['codetypepj'];
		}
		$codelibcatpj='sejour';
		$numcatpj=$numsejour;
		
		clearstatcache();//efface le cache relatif a l'existence des repertoires
		$rep_upload=$GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu ;
		if(!is_dir($rep_upload))//teste si existe 
		{ mkdir ($rep_upload);
		}
		$rep_upload.='/'.$codelibcatpj ;
		if(!is_dir($rep_upload))//teste si existe  
		{ mkdir ($rep_upload);
		}
		$rep_upload.='/'.$numcatpj;
		if(!is_dir($rep_upload))//teste si existe  
		{ mkdir ($rep_upload);
		}
		//$objWriter->save($rep_upload.'/'.$codetypepj);
		$pdf->Output($rep_upload.'/'.$codetypepj, 'F');
		// si existe deja
		$updateSQL= "delete from individupj where codeindividu=".GetSQLValueString($codeindividu, "text").
								" and codelibcatpj=".GetSQLValueString($codelibcatpj, "text").
								" and numcatpj=".GetSQLValueString($numcatpj, "text").
								" and codetypepj=".GetSQLValueString($codetypepj, "text");
		mysql_query($updateSQL) or die(mysql_error());
		$updateSQL="insert into individupj (codeindividu,codelibcatpj,numcatpj,codetypepj,nomfichier)". 
								" values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($codelibcatpj, "text").",".GetSQLValueString($numcatpj, "text").",".
														GetSQLValueString($codetypepj,"text").",".GetSQLValueString($row_rs_individusujet["nom"]." ".$row_rs_individusujet["prenom"].".pdf", "text").")";
		mysql_query($updateSQL) or die(mysql_error());
		if(isset($rs_dir))mysql_free_result($rs_dir);
		if(isset($rs_individusujet))mysql_free_result($rs_individusujet);
		if(isset($rs)) {mysql_free_result($rs);}
		if(isset($rs_pj)) {mysql_free_result($rs_pj);}
	}
	else
	{ $erreur.="codeindividu/numsejour inconnu";
	}
	return $erreur;
}
// PG fsd 20170307
function fsd_xl_php($codeindividu,$numsejour)
{	global $aujourdhui;
	$erreur='';
	$numdossierzrr_nouveau='';
	$rep='__fsd';
 	$formulaire=$rep.'/formulaire_2017.xls';
	$onglet="Formulaire demande d'acc&egrave;s ZRR ";
	
	$rs=mysql_query("select * from pays") or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_pays[$row_rs["codepays"]]=$row_rs;
	}//nat.libpays as libnat, pays.libpays as libpays_naiss, pays_etab_orig.libpays as libpays_etab_orig,
	$query_rs_individu=	"select if(civilite.codeciv='','',civilite.lib_fr) as libciv,nom,if(nomjf='',nom,nomjf) as nompatronymique, prenom,if(nomjf<>'',nom,'') as nommarital,".
											" concat(SUBSTRING(date_naiss,9,2),'/',SUBSTRING(date_naiss,6,2),'/',SUBSTRING(date_naiss,1,4)) as date_naiss, ".
											" adresse_pers, concat(codepostal_pers,' ',ville_pers) as codepostal_ville_pers, codepays_pers, if(email='',email_parti,email) as email_accueilli,".
											" if(individu.codeciv='1','Masculin (male)','Feminin (female)') as sexe,concat(codepostal_naiss,' ',ville_naiss) as codepostal_ville_naiss,codepays_naiss, codenat,codepays_etab_orig,  ".
											" libcourttypepieceidentite as libtypepieceidentite, numeropieceidentite,".
											" etab_orig, adresse_etab_orig,concat(codepostal_etab_orig,' ',ville_etab_orig) as codepostal_ville_etab_orig, libsituationprofessionnelle,".
											" libtypeacceszrr,libphysiquevirtuelzrr,numdossierzrr,avis_motive_resp_zrr,".
											" intituleposte, descriptionmission as missionsujet,".
											" montantfinancement,liboriginefinancement,codelieu,".
											" datedeb_sejour,datefin_sejour,".
											" libfsddomainescientifique as libdomainescientifique, libfsddisciplinescientifique as libdisciplinescientifique".// titre_prog_rech as titresujet, prog_rech as sujet,
											" from individu,individusejour,civilite, typepieceidentite,situationprofessionnelle, typeacceszrr, zrr_physiquevirtuel,".
											" zrr_originefinancement,sujet_domainescientifique,sujet_disciplinescientifique".
											" where individu.codeciv=civilite.codeciv".
											" and individu.codeindividu=individusejour.codeindividu".
											" and individusejour.codetypepieceidentite=typepieceidentite.codetypepieceidentite".
											" and individusejour.codesituationprofessionnelle=situationprofessionnelle.codesituationprofessionnelle".
											" and individusejour.codetypeacceszrr=typeacceszrr.codetypeacceszrr".
											" and individusejour.codephysiquevirtuelzrr= zrr_physiquevirtuel.codephysiquevirtuelzrr".
											" and individusejour.codeoriginefinancement=zrr_originefinancement.codeoriginefinancement".
											" and individusejour.codedomainescientifique1=sujet_domainescientifique.codedomainescientifique".
											" and individusejour.codedisciplinescientifique1=sujet_disciplinescientifique.codedisciplinescientifique".
											" and individusejour.codeindividu=".GetSQLValueString($codeindividu,"text")." and individusejour.numsejour=".GetSQLValueString($numsejour,"text");
	$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
	
	if($row_rs_individu=mysql_fetch_assoc($rs_individu))
	{ $row_rs_individu["libpays_naiss"]=$tab_pays[$row_rs_individu["codepays_naiss"]]["libpayszrr"];
		$row_rs_individu["libnat"]=$tab_pays[$row_rs_individu["codenat"]]["libpayszrr"];
		$row_rs_individu["libpays_pers"]=$tab_pays[$row_rs_individu["codepays_pers"]]["libpayszrr"];
		$row_rs_individu["libpays_etab_orig"]=$tab_pays[$row_rs_individu["codepays_etab_orig"]]["libpayszrr"];
		// situation professionnelle et type acces
		//si pas de numdossierzrr : generation du numero de dossier et zrr
		if($row_rs_individu['numdossierzrr']=='')
		{ // on prend la premiere zrr dans l'ordre pour ce lieu
			$query_rs="select libzrr from zrr where codelieu=".GetSQLValueString($row_rs_individu['codelieu'], "text")." order by numordre";
			$rs=mysql_query($query_rs);
			if($row_rs=mysql_fetch_assoc($rs))
			{ $libzrr=$row_rs['libzrr'];
				$query_rs="select max(numdossierzrr) as maxnumdossierzrr".
									" from individusejour".
									" where numdossierzrr like ".GetSQLValueString("%".$libzrr."%", "text");
				$rs=mysql_query($query_rs) or die(mysql_error());
				$row_rs=mysql_fetch_assoc($rs);
				if($row_rs['maxnumdossierzrr']!='')
				{ $tab=explode("-",$row_rs['maxnumdossierzrr']);
					if(count($tab)=="4")
					{ // si dans le meme mois de la meme annee
						if($tab[0]==substr($aujourdhui,0,4) && $tab[1]==substr($aujourdhui,5,2))
						{ $num=str_pad((string)((int)$tab[3]+1), 2 , "0", STR_PAD_LEFT);
						}
						else
						{ $num='01';
						}
						$numdossierzrr_nouveau=substr($aujourdhui,0,4)."-".substr($aujourdhui,5,2)."-".$tab[2]."-".$num;
					}
					else
					{ $erreur.="<br>Manque un - dans le num&eacute;ro zrr";
					}
				}
				else// on prend la premiere zrr dans l'ordre pour ce lieu
				{ $numdossierzrr_nouveau=substr($aujourdhui,0,4)."-".substr($aujourdhui,5,2)."-".$libzrr."-01";
				}
			}
			else
			{ $erreur.="<br>Code zrr inexistant";
			}
		}
		if($erreur=='')
		{ if($numdossierzrr_nouveau!='')
			{ mysql_query("update individusejour set numdossierzrr=".GetSQLValueString($numdossierzrr_nouveau,"text")." where codeindividu=".GetSQLValueString($codeindividu,"text").
										" and numsejour=".GetSQLValueString($numsejour,"text"));
				$row_rs_individu['numdossierzrr']=$numdossierzrr_nouveau;
			}
			if($row_rs_individu['avis_motive_resp_zrr']=="")
			{ mysql_query("update individusejour set avis_motive_resp_zrr=".GetSQLValueString("FAVORABLE","text")." where codeindividu=".GetSQLValueString($codeindividu,"text").
										" and numsejour=".GetSQLValueString($numsejour,"text"));
				$row_rs_individu['avis_motive_resp_zrr']="FAVORABLE";
			}
			$row_rs_individu["libzrr"]="";
			$query_rs=("select zrr.codelieu,libzrr,adresselieu from zrr,lieu where zrr.codelieu=lieu.codelieu and lieu.codelieu=".GetSQLValueString($row_rs_individu["codelieu"],"text")."order by lieu.codelieu,zrr.numordre");
			$rs=mysql_query($query_rs);
			if($row_rs=mysql_fetch_assoc($rs))//1ere zrr du lieu
			{ $row_rs_individu["libzrr"]=$row_rs["libzrr"];
				$row_rs_individu["adressezrr"]=$row_rs["adresselieu"];
			}
			$query_rs=("select distinct libzrr from zrr order by libzrr");
			$rs=mysql_query($query_rs);
			$row_rs_individu["commentaires"]="";
			$first=true;
			while($row_rs=mysql_fetch_assoc($rs))
			{ $row_rs_individu["commentaires"].=($first?html_entity_decode('Liste des ZRR concern&eacute;es : ').chr(10).chr(13):' ').$row_rs["libzrr"];
				$first=false;
			}
			// sujet s'il y en a un
			$query_rs_sujet="select titre_fr as intituleposte, descr_fr as missionsujet, libfsddomainescientifique as libdomainescientifique, libfsddisciplinescientifique as libdisciplinescientifique".
											" from sujet,individusujet,sujet_domainescientifique,sujet_disciplinescientifique".
											" where sujet.codesujet=individusujet.codesujet".
											" and sujet.codedomainescientifique1=sujet_domainescientifique.codedomainescientifique".
											" and sujet.codedisciplinescientifique1=sujet_disciplinescientifique.codedisciplinescientifique".
											" and individusujet.codeindividu=".GetSQLValueString($codeindividu,"text").
											" and individusujet.numsejour=".GetSQLValueString($numsejour,"text");
			$rs_sujet=mysql_query($query_rs_sujet) or die(mysql_error());
			if($row_rs_sujet=mysql_fetch_assoc($rs_sujet))
			{ $row_rs_individu["intituleposte"]=$row_rs_sujet["intituleposte"];
				$row_rs_individu["missionsujet"]=$row_rs_sujet["missionsujet"];
				$row_rs_individu["libdomainescientifique"]=$row_rs_sujet["libdomainescientifique"];
				$row_rs_individu["libdisciplinescientifique"]=$row_rs_sujet["libdisciplinescientifique"];
			}
			// espace devant les dates = excel inverse jj et mm !!!!
			$row_rs_individu["datedeb_sejour"]=aaaammjj2jjmmaaaa($row_rs_individu["datedeb_sejour"],"/");
			$row_rs_individu["datefin_sejour"]=aaaammjj2jjmmaaaa($row_rs_individu["datefin_sejour"],"/");
			$row_rs_individu["libetablissement"]=html_entity_decode("Universit&eacute; de Lorraine");
			$row_rs_individu["umr"]="UMR".$GLOBALS['num_umr'];
			$row_rs_individu["ministere"]=html_entity_decode("F - Minist&egrave;re de l'enseignement sup&eacute;rieur et de la recherche");
			$row_rs_individu["libcodeuniteacceuil"]="UMR".$GLOBALS['num_umr'];
			$rs=mysql_query("select nom, prenom, tel, email from individu,structureindividu, structure".
											" where individu.codeindividu=structureindividu.codeindividu and structureindividu.codestructure=structure.codestructure and structure.codelib='du'");
			$row_rs_individu["respzrr"]="";
			if($row_rs=mysql_fetch_assoc($rs))
			{ $row_rs_individu["respzrr"]=$row_rs["prenom"].' '.$row_rs["nom"].' - Directeur';
				$row_rs_individu["respzrrtel"]=$row_rs["tel"];
				$row_rs_individu["respzrrmail"]=$row_rs["email"];
			}
			
			//$row_rs_individu["avisfsdchefetab"]="";
			$row_rs_individu["autredemandeencours"]="non (no)";
			$row_rs_individu["autorisationdejaobtenue"]="non (no)";
			$row_rs_individu["habilitedefense"]="non (no)";
			$tab_fsd=array(utf8_encode(html_entity_decode($onglet))=>	
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
																			"intituleposte"=>"D31",
																			"missionsujet"=>"C34",
																			"autredemandeencours"=>"C42","autorisationdejaobtenue"=>"E42",
																			"habilitedefense"=>"D47",
																			"avis_motive_resp_zrr"=>"M25",
																			//"avisfsdchefetab"=>"M32",
																			"commentaires"=>"M47"
																			)));
			$objPHPExcel = PHPExcel_IOFactory::load($formulaire);//enregistr� au pr�alable au format 97-2003 a partir de l'original xlsx avec excel 2013
			
			foreach($tab_fsd as $nomonglet=>$tab_onglet)
			{	$objWorksheet = $objPHPExcel-> getSheetByName($nomonglet);
				foreach($tab_onglet as $contenu_couleur=>$tab_contenu_couleur)
				{	if($contenu_couleur=='contenu_cellule')
					{ foreach($tab_contenu_couleur as $champ=>$cellule)
						{ if(isset($row_rs_individu[$champ]))
							{ //echo $champ.' '.$row_rs_individu[$champ].'<br>';
								$objWorksheet->getCell($cellule)->setValue(utf8_encode(conv_car_speciaux($row_rs_individu[$champ])));// 
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
			$objDrawing->setOffsetX(0);                       //setOffsetX works properly
			$objDrawing->setOffsetY(0);                       //setOffsetY works properly
			$objDrawing->setHeight(180);
			$objDrawing->setCoordinates('B1');
			$objDrawing->setWorksheet($objPHPExcel-> getSheetByName(utf8_encode(html_entity_decode($onglet))));
			
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
			$objDrawing->setWorksheet($objPHPExcel-> getSheetByName(utf8_encode(html_entity_decode($onglet))));
			
			$gdImage = imagecreatefromjpeg($rep.'/logo_france.jpg');
			// Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
			$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
			$objDrawing->setName('logo_france');
			$objDrawing->setDescription('logo_france');
			$objDrawing->setImageResource($gdImage);
			$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawing->setOffsetX(10);                       //setOffsetX works properly
			$objDrawing->setOffsetY(10);                       //setOffsetY works properly
			$objDrawing->setHeight(75);
			$objDrawing->setCoordinates('B1');
			$objDrawing->setWorksheet($objPHPExcel-> getSheetByName(utf8_encode(html_entity_decode($onglet))));
			
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			//pj individu,sejour,fsd 
			$rs_pj=mysql_query("select codelibcatpj,codetypepj,codelibtypepj from typepjindividu where codelibtypepj=".GetSQLValueString('fsd', "text"));
			if($row_rs_pj=mysql_fetch_assoc($rs_pj))
			{ $codetypepj=$row_rs_pj['codetypepj'];
			}
			$codelibcatpj='sejour';
			$numcatpj=$numsejour;
			
			clearstatcache();//efface le cache relatif a l'existence des repertoires
			$rep_upload=$GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu ;
			if(!is_dir($rep_upload))//teste si existe 
			{ mkdir ($rep_upload);
			}
			$rep_upload.='/'.$codelibcatpj ;
			if(!is_dir($rep_upload))//teste si existe  
			{ mkdir ($rep_upload);
			}
			$rep_upload.='/'.$numcatpj;
			if(!is_dir($rep_upload))//teste si existe  
			{ mkdir ($rep_upload);
			}
			$objWriter->save($rep_upload.'/'.$codetypepj);
			// si existe deja
			$updateSQL= "delete from individupj where codeindividu=".GetSQLValueString($codeindividu, "text").
									" and codelibcatpj=".GetSQLValueString($codelibcatpj, "text").
									" and numcatpj=".GetSQLValueString($numcatpj, "text").
									" and codetypepj=".GetSQLValueString($codetypepj, "text");
			mysql_query($updateSQL) or die(mysql_error());
			$updateSQL="insert into individupj (codeindividu,codelibcatpj,numcatpj,codetypepj,nomfichier)". 
									" values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($codelibcatpj, "text").",".GetSQLValueString($numcatpj, "text").",".
															GetSQLValueString($codetypepj,"text").",".GetSQLValueString($row_rs_individu["numdossierzrr"].".xlsx", "text").")";
			mysql_query($updateSQL) or die(mysql_error());
			$objPHPExcel->disconnectWorksheets(); 
			unset($objPHPExcel);/**/
		}
		if(isset($rs_individu)) {mysql_free_result($rs_individu);}
		if(isset($rs_sujet)) {mysql_free_result($rs_sujet);}
		if(isset($rs)) {mysql_free_result($rs);}
		if(isset($rs_pj)) {mysql_free_result($rs_pj);}
	}
	else
	{ $erreur.="individu/sejour inexistant";
	}
	return $erreur;
}

//PG 20151031
function droit_acces($tab_contexte)
{ $droit_acces=false;
	// 20170612
	//$tab_infouser=get_info_user($tab_contexte['codeuser']);
	if($tab_contexte['codeuser']=='00186')//richardalain 
	{ $droit_acces=true;
	} 
	// 20170612
  if(isset($tab_contexte['groupe']) && $tab_contexte['groupe']=='pole_gestion_fst')
	{ if(isset($tab_contexte['prog']) && ($tab_contexte['prog']=='gestioncommandes' || $tab_contexte['prog']=='edit_commande'))
		{ //droit acces commandes Laurence et Sabine sur Abdellah
			if(isset($tab_contexte['codesecrsite']) && ($tab_contexte['codesecrsite']=='00965'))
			{ if(isset($tab_contexte['codeuser']) && ($tab_contexte['codeuser']=='00003' || $tab_contexte['codeuser']=='01107'))
				{ $droit_acces=true;
				}
			}
			// droit acces Laurence sur Sabine et r�ciproquement pour commandes
			if(!(isset($tab_contexte['function']) && $tab_contexte['function']=='get_tab_cmd_acteurs'))
			{	if(isset($tab_contexte['codesecrsite']) && $tab_contexte['codesecrsite']=='00003')
				{ if(isset($tab_contexte['codeuser']) && ($tab_contexte['codeuser']=='01107'))
					{ $droit_acces=true;
					}
				}
				if(isset($tab_contexte['codesecrsite']) && $tab_contexte['codesecrsite']=='01107')
				{ if(isset($tab_contexte['codeuser']) && ($tab_contexte['codeuser']=='00003'))
					{ $droit_acces=true;
					}
				}
			}
		}
	}
	else if(isset($tab_contexte['prog']) && $tab_contexte['prog']=='edit_individu')
	{ if(isset($tab_contexte['codeuser']) && ($tab_contexte['codeuser']=='01107' ))
		{ $droit_acces=true;
		}
	}
  return $droit_acces;
}
//PG 20151031

function entete_page($tab)
{ $texteretour=
	'<tr><td><table>'. 
	'<tr>
		'/* <td align="left"> */
      .entetehtml_page($tab['image'],$tab['titrepage']).
    /* </td> */'
	</tr>
	<tr>
		<td align="left" colspan="5">&nbsp;</td>
	</tr>';
	if(isset($tab['lienretour']) && $tab['texteretour'])
	{
	$texteretour.=
	'<tr>
		<td align="left" colspan="5">
			<table border="0" cellspacing="1" cellpadding="0">
				<tr>
					<td><img src="images/b_precedent.png" alt="" width="16" height="16">
					</td>
					<td><a href="'.$tab['lienretour'].'"><span class="bleugrascalibri11">'.$tab['texteretour'].'</span></a>
					</td>
				</tr>
			</table>
		</td>
	</tr>';
	}
	$texteretour.=
	'<tr>
		<td colspan="5">
    	<table>
      	<tr>
        	<td nowrap>'.
						tablehtml_info_user($tab['tab_infouser'],$tab['tab_roleuser']).
					'</td>
					<td class="bleucalibri10" align="right" nowrap>'.($GLOBALS['mode_avec_envoi_mail']?'':'Sans envoi de mail').'
					</td>
					<td class="bleucalibri10" nowrap>'.//20170311
						($tab['tab_infouser']['login']==$GLOBALS['admin_bd']?'<b>[Admin info database_labo : </b>'.$_SESSION['database_labo'].'<b> path_to_rep_upload : </b>'.$GLOBALS['path_to_rep_upload'].']':'').
      		'</td>
				</tr>
    	</table>
    </td>
	</tr>
	<tr>
		<td colspan="5" align="center">';
			if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
			{ $texteretour.=
				'<table class="table_mode_exploitation">
					<tr>
						<td>
							<span class="rougegrascalibri11">Mode '.$GLOBALS['mode_exploit'].' - Envoi mail : '.($GLOBALS['mode_avec_envoi_mail']?'oui':'non').'</span>
						</td>
					</tr>
				</table>'; 
		 	}
		$texteretour.=
		'</td>
	</tr>
	<tr>
  <td colspan="5" align="center">';
    if((isset($tab['erreur']) && $tab['erreur']!='') || (isset($tab['affiche_succes']) && $tab['affiche_succes']) || (isset($tab['erreur_envoimail']) && $tab['erreur_envoimail']))
    { $texteretour.='	
    <table border="0" cellspacing="5" >
      <tr>';
        if(isset($tab['erreur']) && $tab['erreur']!='')
        { 
				$texteretour.='	
        <td valign="top"><img src="images/attention.png" width="16" height="16" align="top"></td>
        <td valign="top">
          <span class="rougecalibri9">Erreur : '.$tab['erreur'].'</span>
        </td>';
        } 
        else if(isset($tab['affiche_succes']) && $tab['affiche_succes'])
        {
				$texteretour.='	
        <td valign="top"><img src="images/succes.png" align="top"></td>
        <td valign="top">
          <span class="vertcalibri11">'.$tab['message_resultat_affiche'].'</span>';
					if(isset($tab['warning']) && $tab['warning']!='')
					{	$texteretour.='<br><span class="mauvecalibri9">'.$tab['warning'].'</span>';
					}
					if(isset($tab['information_defaut']) && $tab['information_defaut']!="")
					{ $texteretour.='<br><span class="orangecalibri9">'.$tab['information_defaut'].'</span>';
					}
					$texteretour.='</td>';
        } 
				else
				{ $texteretour.='	
					<td>			 
					</td>';
				} 
			$texteretour.='	
      </tr>
    </table>';
    }
		$texteretour.='	
		</td>
  </tr>
    <tr>
      <td colspan="5" align="center">';
			if(isset($tab['erreur_envoimail']) && $tab['erreur_envoimail']!="")
			{	// en test et demo erreur_envoimail contient le mail qui aurait du �tre envoy� ou le message d'erreur
				if(($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo'))
				{ $texteretour.='							  
								<img src="images/b_info_mail_envoye.png" id="sprytrigger_info_mail_envoye">
                <div class="tooltipContent_cadre" id="info_mail_envoye"><span class="noircalibri10">'
								.$tab['erreur_envoimail'].
								'</span></div>
								<script type="text/javascript">
									var sprytooltip_info_mail_envoye = new Spry.Widget.Tooltip("info_mail_envoye", "#sprytrigger_info_mail_envoye", {offsetX:-100, offsetY:0, closeOnTooltipLeave:true});
								</script>
							';
				}
				else
				{ $texteretour.='
					<table align="center" border="0" >
						<tr>
							<td valign="top"><img src="images/attention.png" align="top"></td>
							<td>
								<span class="rougegrascalibri9">Le message n&rsquo;a pas pu &ecirc;tre envoy&eacute; pour une raison d&rsquo;ordre technique.</span>
								<span class="noircalibri9"><br>Nous en sommes d&eacute;sol&eacute;s et Vous remercions de bien vouloir envoyer un message &agrave; <a href="mailto:'.$GLOBALS['webMaster']['email'].'">'.$GLOBALS['webMaster']['nom'].'</a> pr&eacute;cisant :
								<br>- en objet : </span><span class="mauvecalibri9">probl&egrave;me d&rsquo;envoi de message du serveur Web : '.(isset($tab['msg_erreur_objet_mail'])?$tab['msg_erreur_objet_mail']:'').'</span> 
								<br><span class="noircalibri9">- en corps de message : </span><span class="mauvecalibri9">'.$tab['erreur_envoimail'].'</span>
							</td>
						</tr>
					</table>';
				}
			}
		$texteretour.='	
			</td>
    </tr>
		</table>
		</td></tr>';
		return $texteretour;

}
function entetehtml_page($image,$titrepage)
{ return
	/* <table border="0" cellpadding="0" cellspacing="1">
		<tr> */'
			<td align="left"><img src="'.$GLOBALS['logolabo'].'">'.
			'</td>
			<td><img src="images/espaceur.gif" width="200" height="1">
			</td>
			<td align="center">
				<table border="1" align="center" cellpadding="10" class="table_entete">
					<tr>
						<td align="center" nowrap class="bleugrascalibri11">'.
							($image!=''?'<img src="'.$image.'" width="16" height="14"> ':'').$titrepage.'<br>
							<span class="bleugrascalibri10">(&agrave; usage strictement interne au laboratoire)</span>'.
						'</td>
					</tr>
				</table>
			</td>
			<td><img src="images/espaceur.gif" width="200" height="1">
			</td>
			<td>
				<table border="0" cellspacing="1" cellpadding="0">
					<tr>
						<td><img src="images/b_deconnecte.png" alt="" width="16" height="16" border="0"></td>
						<td><img src="images/espaceur.gif" width="3" height="1"></td>
						<td nowrap><!--20170228 --><a href="formintranetpasswd.php?db='.$_SESSION['database_labo'].'&reconnexion=stop" onClick="return confirme(\'valider\',\'Voulez-vous vous d&eacute;connecter\')"><span class="bleugrascalibri11">Se d&eacute;connecter</span></a></td>
						<td nowrap><img src="images/espaceur.gif" width="100" height="1" border="0"></td>
					</tr>
				</table>
			</td>';
		/* </tr>
	</table> */

}

function html_head($titrepage)
{ 
}
function checklongueur($champ,$longueurmax,$libelle)
{ return (isset($_POST[$champ]) && strlen($_POST[$champ])>$longueurmax)?"<BR>"."Longueur du champ '".$libelle."' sup&eacute;rieure &agrave; ".$longueurmax.' : '.strlen($_POST[$champ]):"";
}

function periodeencours($datedeb,$datefin)
{ return "((".$datedeb."='' or replace( ".$datedeb.", '/', '-')<=date_format(NOW(),'%Y-%m-%d')) and (".$datefin."='' or replace(  ".$datefin.", '/', '-')>=date_format(NOW(),'%Y-%m-%d')))";
}

function periodefuture($datedeb)
{ return "((".$datedeb."<>'' and replace( ".$datedeb.", '/', '-')>date_format(NOW(),'%Y-%m-%d')))";
}

function periodepassee($datefin)
{ return "((".$datefin."<>'' and replace( ".$datefin.", '/', '-')<date_format(NOW(),'%Y-%m-%d')))";
}

function intersectionperiodes($datedeb_periode1,$datefin_periode1,$datedeb_periode2,$datefin_periode2)
{ return "((".$datedeb_periode1.">=".$datedeb_periode2." and (".$datedeb_periode1."<=".$datefin_periode2." or ".$datefin_periode2."=''))".
					" or (".$datefin_periode1.">=".$datedeb_periode2." and (".$datefin_periode1."<=".$datefin_periode2." or ".$datefin_periode2."=''))".
					" or (".$datedeb_periode2.">=".$datedeb_periode1." and (".$datedeb_periode2."<=".$datefin_periode1." or ".$datefin_periode1."=''))".
					" or (".$datefin_periode2.">=".$datedeb_periode1." and (".$datefin_periode2."<=".$datefin_periode1." or ".$datefin_periode1."='')))";
}
function jjmmaaaa2date($jj,$mm,$aaaa)
{ if((int)$jj<1 || (int)$jj>31 || (int)$mm<1 || (int)$mm>12 || (int)$aaaa<1900 || strlen($aaaa)!=4)
  { return "";
  }
  else
  { return $aaaa."/".str_pad($mm,2,"0",STR_PAD_LEFT)."/".str_pad($jj,2,"0",STR_PAD_LEFT);
  }
}
function hhmn2heure($hh,$mn)
{ if((int)$hh<0 || (int)$hh>23 || (int)$mn<0 || (int)$mn>60 || ($hh=='' && $mn=='')/* */)
  { return "";
  }
  else
  { return str_pad($hh,2,"0",STR_PAD_LEFT)."H".str_pad($mn,2,"0",STR_PAD_LEFT);
  }
}

function aaaammjj2jjmmaaaa($aaaammjj,$separateur)
{ return strlen($aaaammjj)==10?substr($aaaammjj,8,2).$separateur.substr($aaaammjj,5,2).$separateur.substr($aaaammjj,0,4):"";
}

function est_date($jj,$mm,$aaaa)
{ if($jj.$mm.$aaaa=='')//tous les champs vides : OK
	{ return true;
	}
	else if($jj=='' || $mm=='' || $aaaa=='')//un champ vide et jj+mm+aaaa pas vide : pas OK
  { return false;
	}
	else
	{ $tab_nbjours_du_mois=$GLOBALS['nb_jours_du_mois'];
		$unjourbissextile=((int)$aaaa%4==0 && (int)$mm==2)?1:0;
		if(!isset($tab_nbjours_du_mois[(int)$mm]))
		{ return false;
		}
		else
		{ if($jj<1 || $jj >$tab_nbjours_du_mois[(int)$mm]+$unjourbissextile || (strlen($aaaa) >=3 && (int)$aaaa <1900) || !(int)($jj) || !(int)($mm) || !(int)($aaaa))
			{ return false;
			}
			else
			{ return true;
			}
		}
	}
}

function est_heure_mn($hh,$mn)
{ if($hh.$mn=='')//tous les champs vides : OK
	{ return true;
	}
	else//comparaison chaine car pbl avec int et la valeur "0" 
	{ if($hh<"00" || $hh>"23" || $mn<"00" || $mn>"60" || !is_numeric($hh) || !is_numeric($mn))
		{ return false;
		}
		else
		{ return true;
		}
	}
}

function est_champ_annee($champ_annee)
{ if($champ_annee!='')
	{ if(!(int)($champ_annee) || strlen($champ_annee)!=4) 
		{
		return false;
		}
		else
		{ return true;
		}
	}
	else
	{ return true;
	}
}

function est_mail($mail) 
{ if($mail=='') return true;
  if ((strpos($mail,'@')!==false && (strpos($mail,'.'))>=0))  return true;
	else return false;
}

function estrole($codelibrole,$tab_roleuser)
{ foreach($tab_roleuser as $codelibroleaction=>$val)
	{ $tab=explode('#',$codelibroleaction);
		if($codelibrole==$tab[0])
		{ return true;
		}
	}
	return false;
}

function est_avecfrais($numcommande)
{ return ($numcommande!='' && strpos($GLOBALS['prefixe_commande_etat_avec_frais'],substr($numcommande,0,2))!==-1);
}

function calcule_annee_these($date_preminscr,$date_soutenance,$ajustement)
{ /* $moisdeb_these=substr($datedeb_these,5,2); */
	$mois_preminscr=substr($date_preminscr,5,2);
	$annee_preminscr=substr($date_preminscr,0,4);
	$mois_soutenance=substr($date_soutenance,5,2);
	$annee_soutenance=substr($date_soutenance,0,4);
	if($annee_soutenance=='')
	{ $mois_courant=date('m');
		$annee_courant=date('Y');
	}
	else
	{ $mois_courant=$mois_soutenance;
		$annee_courant=$annee_soutenance;
	}
	$annee_octprecedent=$annee_preminscr;
	if($mois_preminscr>=1 && $mois_preminscr<=9)
	{ $annee_octprecedent--;
	}
	$num_annee=$annee_courant-$annee_octprecedent;
	if($mois_courant>=10 && $mois_courant<=12) 
	{ $num_annee++;	
	}
	$num_annee+=$ajustement;
	return $num_annee;
}

function duree_aaaammjj($datedeb, $datefin)
{ $tab_nbjours_du_mois=$GLOBALS['nb_jours_du_mois'];
	$jourdeb=intval(substr($datedeb,8,2));
	$jourfin=intval(substr($datefin,8,2));
	$moisdeb=intval(substr($datedeb,5,2));
	$moisfin=intval(substr($datefin,5,2));
	$anneedeb=intval(substr($datedeb,0,4));
	$anneefin=intval(substr($datefin,0,4));
	$jourdebbissextile=($anneedeb%4==0 && $moisdeb==2)?1:0;
	$jourfinbissextile=($anneefin%4==0 && $moisfin==2)?1:0;
	$nbjours=0;
	$nbmois=0;
	$nbannees=$anneefin-$anneedeb;
	if(est_date($jourdeb,$moisdeb,$anneedeb) && est_date($jourfin,$moisfin,$anneefin) && $datedeb!='' && $datefin!='' && $datedeb<=$datefin && $jourdeb<=$tab_nbjours_du_mois[$moisdeb]+$jourdebbissextile && $jourfin<=$tab_nbjours_du_mois[$moisfin]+$jourfinbissextile)
	{ if($moisfin-$moisdeb<0)
		{	$nbmois=$moisfin+12-$moisdeb;
			$nbannees-=1;
		}
		else
		{ $nbmois=$moisfin-$moisdeb;
		}
		if($jourfin!=$jourdeb-1)
		{ $nbjours=($tab_nbjours_du_mois[$moisdeb]+$jourdebbissextile-$jourdeb+1)+$jourfin;
			if($nbjours>=$tab_nbjours_du_mois[$moisdeb]+$jourdebbissextile)
			{ $nbjours=$nbjours-($tab_nbjours_du_mois[$moisdeb]+$jourdebbissextile);
				if($nbjours>=$tab_nbjours_du_mois[$moisfin]+$jourfinbissextile)
				{ $nbjours=$nbjours-($tab_nbjours_du_mois[$moisfin]+$jourfinbissextile);
					$nbmois++;
				}
			}
			else
			{ $nbmois--;
			}
		}
		if($nbmois==12)
		{ $nbmois=0;
			$nbannees+=1;
		}
		return array("a"=>$nbannees, "m"=>$nbmois, "j"=>$nbjours);
	}
	return false;
}



// Fonction permettant de compter le nombre de jours ouvr�s entre deux dates
function get_nb_open_days($date_start, $date_stop) {	
	$arr_bank_holidays = array(); // Tableau des jours feri�s	
	
	// On boucle dans le cas ou l'ann�e de d�part serait diff�rente de l'ann�e d'arriv�e
	$diff_year = date('Y', $date_stop) - date('Y', $date_start);
	for ($i = 0; $i <= $diff_year; $i++) {			
		$year = (int)date('Y', $date_start) + $i;
		// Liste des jours feri�s
		$arr_bank_holidays[] = '1_1_'.$year; // Jour de l'an
		$arr_bank_holidays[] = '1_5_'.$year; // Fete du travail
		$arr_bank_holidays[] = '8_5_'.$year; // Victoire 1945
		$arr_bank_holidays[] = '14_7_'.$year; // Fete nationale
		$arr_bank_holidays[] = '15_8_'.$year; // Assomption
		$arr_bank_holidays[] = '1_11_'.$year; // Toussaint
		$arr_bank_holidays[] = '11_11_'.$year; // Armistice 1918
		$arr_bank_holidays[] = '25_12_'.$year; // Noel
				
		// R�cup�ration de paques. Permet ensuite d'obtenir le jour de l'ascension et celui de la pentecote	
		$easter = easter_date($year);
		$arr_bank_holidays[] = date('j_n_'.$year, $easter + 86400); // Paques
		$arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*39)); // Ascension
		$arr_bank_holidays[] = date('j_n_'.$year, $easter + (86400*50)); // Pentecote	
	}
	//print_r($arr_bank_holidays);
	$nb_days_open = 0;
	// Mettre <= si on souhaite prendre en compte le dernier jour dans le d�compte	
	while ($date_start <= $date_stop) {
		// Si le jour suivant n'est ni un dimanche (0) ou un samedi (6), ni un jour f�ri�, on incr�mente les jours ouvr�s	
		if (!in_array(date('w', $date_start), array(0, 6)) 
		&& !in_array(date('j_n_'.date('Y', $date_start), $date_start), $arr_bank_holidays)) {
			$nb_days_open++;		
		}
		$date_start = mktime(date('H', $date_start), date('i', $date_start), date('s', $date_start), date('m', $date_start), date('d', $date_start) + 1, date('Y', $date_start));			
	}		
	return $nb_days_open;
}


function demander_autorisation($row_rs_ind,$tab_dates_individu_sejours)
{ // demander_autorisation=vrai si
  // - pas stagiaire < M2
	// - plus de 5 jours 
	// - et pas d'autorisation depuis moins de 5 ans dans l'un des sejours precedents, sans rupture dans le temps
	// !!! LES RAISONS $pourquoi_pas_de_demande_fsd ET $pourquoi_demande_fsd SONT UTILISEES PAR LES PROGRAMMES APPELANTS ; NE PAS MODIFIER LE TEXTE !!!
	$demander_autorisation=false; 
	$plus_de_5_jours=false;
	$pourquoi_pas_de_demande_fsd='';
	$pourquoi_demande_fsd='';
	$datedeb_sejour_prevu='';
	$datefin_sejour_prevu='';
	$datefin_sejour_prevu_prec='';
	$datefin_sejour_prevu_le_plus_recent='';
	$date_derniere_autorisation='';
	$dans_t0=false;
	$dans_t0_moins_de_5_ans_contigu=false;
	// $num_sejour_derniere_autorisation='';
	$derniere_autorisation_moins_de_5_ans=false;
	$est_sejour_contigu=true;//s'il n'y a qu'un sejour, il y a contiguite
	$num_dernier_sejour_liste='';
	//if($row_rs_ind['autrelieu']=='')
	{ $query_rs=" select codelieu from zrr where codelieu=".GetSQLValueString($row_rs_ind['codelieu'], "text")." and codelieu<>''";
		$rs=mysql_query($query_rs) or die(mysql_error()); 
		if($row_rs=mysql_fetch_assoc($rs))
		{ if($row_rs_ind['datefin_sejour_prevu']=='')
			{ $plus_de_5_jours=true;
			}
			else
			{ $tab_duree_sejour=duree_aaaammjj($row_rs_ind['datedeb_sejour_prevu'], $row_rs_ind['datefin_sejour_prevu']);
				if($tab_duree_sejour['a']>0 || $tab_duree_sejour['m']>0 || $tab_duree_sejour['j']>5)
				{ $plus_de_5_jours=true;
				}
			}
			if($plus_de_5_jours)// sejour de plus de 5 j
			{ if($row_rs_ind['codelibcat']=='STAGIAIRE' && $row_rs_ind['codetypestage']!='01')
				{ $pourquoi_pas_de_demande_fsd='stage < M2';
				}
				else
				{ // dans_t0_moins_de_5_ans_contigu ?
					$fin=false;
					while(!$fin)
					{ if(list($un_numsejour_tab_dates_individu_sejours,$un_tab_dates_individu_sejours)=each($tab_dates_individu_sejours))
						{ if($un_tab_dates_individu_sejours['date_autorisation']==$GLOBALS['date_zrr_t0'])
							{ $fin=true;
								$dans_t0=true;
							}
						}
						else//fin de liste
						{ $fin=true;
						}
					}
					// si dans t0, on verifie que les sejours sont contigus jusqu'au sejour teste et qu'il n'y a pas eu de nouvelle demande dans l'un des s�jours suivant le t0
					if($dans_t0)
					{ // A FAIRE : verifier autorisation date de moins de 5 ans
						//$tab_duree_sejour=duree_aaaammjj($GLOBALS['date_zrr_t0'], $row_rs_ind['datefin_sejour_prevu']);
						if($row_rs_ind['date_autorisation']!=$GLOBALS['date_zrr_t0'])// si t0 est dans un autre sejour que celui teste
						{	// on est sur le sejour de la date d'autorisation t0 : parcours des sejours suivants jusqu'au sejour teste
							$fin=false;
							$datefin_sejour_prevu_prec=$un_tab_dates_individu_sejours['datefin_sejour_prevu'];
							while(!$fin && $est_sejour_contigu && $un_tab_dates_individu_sejours['datedeb_sejour_prevu']<$row_rs_ind['datedeb_sejour_prevu'] && $un_tab_dates_individu_sejours['datefin_sejour_prevu']!='')
							{ $num_dernier_sejour_liste=$un_numsejour_tab_dates_individu_sejours;
								if(!(list($un_numsejour_tab_dates_individu_sejours,$un_tab_dates_individu_sejours)=each($tab_dates_individu_sejours)))
								{ $fin=true;
								}
								else
								{ // datedeb_sejour_prevu (peut etre < ) = datefin_sejour_prevu_prec 
									$est_sejour_contigu=$est_sejour_contigu && ($un_tab_dates_individu_sejours['datedeb_sejour_prevu']<=date("Y/m/d",mktime(0,0,0,substr($datefin_sejour_prevu_prec,5,2),substr($datefin_sejour_prevu_prec,8,2)+1,substr($datefin_sejour_prevu_prec,0,4))));
									$datefin_sejour_prevu_prec=$un_tab_dates_individu_sejours['datefin_sejour_prevu'];
									if($un_tab_dates_individu_sejours['date_autorisation']!='' && $un_tab_dates_individu_sejours['date_autorisation']>$GLOBALS['date_zrr_t0'])
									{ $fin=true;
										$dans_t0=false;
									}
								}
							}
							if($dans_t0)
							{ $tab_duree_depuis_derniere_autorisation=duree_aaaammjj($date_derniere_autorisation, $row_rs_ind['datedeb_sejour_prevu']);
								if($tab_duree_depuis_derniere_autorisation['a']<5)//moins de 5 ans au sens strict pour simplifier test
								{ $derniere_autorisation_moins_de_5_ans=true;
								}
							} 
							if($dans_t0 && $derniere_autorisation_moins_de_5_ans && $est_sejour_contigu)
							{ $demander_autorisation=false;
								$pourquoi_pas_de_demande_fsd='FSD - de 5 ans';
								$dans_t0_moins_de_5_ans_contigu=true;
								
							}
							else
							{ $demander_autorisation=true;
								$pourquoi_demande_fsd=$derniere_autorisation_moins_de_5_ans?'':'FSD + de 5 ans';
								$pourquoi_demande_fsd.=$est_sejour_contigu?'':' sejours non contigus';
							}
						}
						else // t0 est dans le sejour teste
						{ $demander_autorisation=false;
							$dans_t0_moins_de_5_ans_contigu=true;
							$pourquoi_pas_de_demande_fsd='FSD - de 5 ans';
						}
					}
					else
					{ $demander_autorisation=true;
						$pourquoi_demande_fsd='+ de 5j, hors stage < M2';
					}
				}
			}
			else
			{ $pourquoi_pas_de_demande_fsd='- de 5j';
				$demander_autorisation=false;
			}
		}
		else
		{ $pourquoi_pas_de_demande_fsd='Pas de ZRR';
		}
	}
	/* else
	{ $pourquoi_pas_de_demande_fsd='Pas de ZRR';
	} */
/* if($row_rs_ind['codeindividu'].'.'.$row_rs_ind['numsejour']=='00492.02')
	{ echo $row_rs_ind['codeindividu'].'.'.$row_rs_ind['numsejour'].($dans_t0_moins_de_5_ans_contigu?'':' pas').' dans_t0_moins_de_5_ans_contigu';
	} */
	return array('demander_autorisation'=>$demander_autorisation,'pourquoi_pas_de_demande_fsd'=>$pourquoi_pas_de_demande_fsd,'pourquoi_demande_fsd'=>$pourquoi_demande_fsd,'dans_t0_moins_de_5_ans_contigu'=>$dans_t0_moins_de_5_ans_contigu);
}

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "''";// PG : "''" au lieu de "NULL"
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}


function deconnecte_ou_connecte()
{ $codeuser='';
	if(isset($_SESSION['codeuser']))
	{ $codeuser=$_SESSION['codeuser'];
	}
	if(!isset($_SESSION['codeuser']) || $codeuser=='')
	{ http_redirect("formintranetpasswd.php?reconnexion=deconnexion");
	}
	return $codeuser;
}

function txt2type($chaine,$type)
{ if($type=='csv')
	{ $chaine=str_replace('\t',' ',$chaine);$chaine=str_replace(chr(9),' ',$chaine);
		$chaine=str_replace('\n',' ',$chaine);$chaine=str_replace(chr(13),' ',$chaine);
		$chaine=str_replace('\r',' ',$chaine);$chaine=str_replace(chr(10),' ',$chaine);
		
	}
	else if($type=='tex')
	{ $html2tex=array("&agrave;"=>"\\`a","&auml;"=>"\\\"a","&acirc;"=>"\\^a","&ccedil;"=>"\\c{c}","&egrave;"=>'\\`e',"&eacute;"=>"\\'e","&Eacute;"=>"\\'E","&ecirc;"=>"\\^e","&euml;"=>"\\\"e",
										"&icirc;"=>"\\^i","&iuml;"=>"\\\"i","&ocirc;"=>"\\^o","&ouml;"=>"\\\"o","&ugrave;"=>"\\`u","&uuml;"=>"\\\"u","&ucirc;"=>"\\^u",
										"&laquo;"=>"","&raquo;"=>"","&quot;"=>"'","&deg;"=>"","%"=>"\\%","_"=>"\\_*",
										"&amp;"=>"&",
										"&#946;"=>"",
										"&gamma;"=>"",
										"&"=>"\\&"
										);
		$chaine=htmlentities($chaine);
		foreach($html2tex as $carhtml=>$cartex)
		{ $chaine=str_replace($carhtml,$cartex,$chaine);
		}
	}
	return $chaine;
}

function js_tab_val($val)
{ return str_replace(array(chr(10),chr(13),'"',"&rsquo;"),array('\n','\r','\"',"'",),str_replace(chr(92),chr(92).chr(92),$val));
}

function html2js($val)
{ return str_ireplace(array("<br>","'"),array("\\n","\'"), html_entity_decode($val));
}

function rep_estvide($rep)
{ $estvide=true;
	if($handle = opendir($rep)) 
	{ while (($file = readdir($handle))!==false && $estvide) 
		{ $estvide=($file=='.' || $file=='..');//le while s'arrete au premier fichier ou rep hors . et ..
    }
    closedir($handle);
	}
	return $estvide;
}

function suppr_rep($rep)
{ clearstatcache();
	if(is_dir($rep))//si rep existe
	{ rmdir($rep);
	}
}

// construit le libelle d'une source ACI - Nom P. - RSA -- LDDIR
function construitlibsource($tab_construitsource)
{	$lib="";
	if(isset($tab_construitsource['codetypesource']) && $tab_construitsource['codetypesource']!='')
	{ $lib.=$tab_construitsource['libtypesource'];
	}
	if(isset($tab_construitsource['libsource']) && $tab_construitsource['libsource']!='')
	{ $lib.=($lib==""?'':' - ').$tab_construitsource['libsource'];
	}
	if(isset($tab_construitsource['coderespscientifique']) && $tab_construitsource['coderespscientifique']!='')
	{ $lib.=($lib==""?'':' - ').$tab_construitsource['nomrespscientifique'].' '.substr($tab_construitsource['prenomrespscientifique'],0,1).'.';
	}
	if(isset($tab_construitsource['codetypecredit']) && $tab_construitsource['codetypecredit']=='02'&& isset($tab_construitsource['libcentrecout_reel']) && $tab_construitsource['libcentrecout_reel']!='')
	{ $lib.=($lib==""?'':' - ').$tab_construitsource['libcentrecout_reel'];
	}
	return $lib;
}
// dernier visa (role) appos�
function max_individustatutvisa($codeindividu,$numsejour)
{	$query_individustatutvisa=("select codestatutvisa,coderole".
															" from statutvisa ".
															" where codestatutvisa in (select max(codestatutvisa) from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text").")");
	$rs_individustatutvisa=mysql_query($query_individustatutvisa) or die(mysql_error());
	if(!($row_rs_individustatutvisa=mysql_fetch_assoc($rs_individustatutvisa)))//max de codestatutvisa ou '' si existe pas
	{ $row_rs_individustatutvisa['codestatutvisa']='';
		$row_rs_individustatutvisa['coderole']='';
	}
	
	if(isset($rs_individustatutvisa))mysql_free_result($rs_individustatutvisa);
	return $row_rs_individustatutvisa;
}

// renvoie $tab_statutvisa=liste de tous les statutvisa existants(roles)  sous la forme ('referent'=>'01', 'srhue'=>'02', ... ,'du'=>'05',....'gestul'=>'09')
function get_statutvisa()
{ $tab_statutvisa=array();
  $rs_statutvisa=mysql_query("select codestatutvisa,coderole from statutvisa where codestatutvisa<>'' order by codestatutvisa") or die(mysql_error());
  while($row_rs_statutvisa = mysql_fetch_assoc($rs_statutvisa))
  { $tab_statutvisa[$row_rs_statutvisa['coderole']]=$row_rs_statutvisa['codestatutvisa']; 
  }
  mysql_free_result($rs_statutvisa);  
  return $tab_statutvisa;
}

//------------------ ROLES : $codeuser a un ou plusieurs roles ($tab_roleuser) dans la liste de tous les roles ($tab_statutvisa)
// utilise pour :
// - role de fa�on g�n�rale (codeindividu ='', numsejour ='')
// - role pour un (codeindividu,numsejour)
function get_tab_roleuser($codeuser,$codeindividu,$numsejour,$tab_statutvisa,$estreferent,$estresptheme)
{ // renvoie table $tab_result['tab_roleuser']=$tab_roleuser = array('referent'=>'01') par ex.
	//               $tab_result['estreferent']=$estreferent
	//               $tab_result['estresptheme']=$estresptheme;
	// $tab_roleuser contient la liste des roles d'un individu : referent, resptheme, du pour le directeur, encadrant de these et resp. de gt par ex.
	$tab_result=array();
	$tab_roleuser=array();// table des roles
	//$estreptheme=false;
  //role referent : pour un individu precis. R�f�rent ne peut pas etre attribue pour la liste des individus
  if($codeindividu!='')
	{ $rs_individu=mysql_query("select codereferent,codegesttheme,codecreateur,codemodifieur".
		  				 							" from individusejour where codeindividu=".GetSQLValueString($codeindividu, "text").
														" and numsejour=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
		$row_rs_individu = mysql_fetch_assoc($rs_individu);
		if($codeuser==$row_rs_individu['codereferent'] || $codeuser==$row_rs_individu['codegesttheme'] || $codeuser==$row_rs_individu['codecreateur'] || $codeuser==$row_rs_individu['codemodifieur'])
		{ $tab_roleuser['referent']=$tab_statutvisa['referent'];//le role referent est donne a la personne qui valide cette saisie
		}
		$estreferent=($codeuser==$row_rs_individu['codereferent']);
	}

  // role theme non resp. en tant que gesttheme de fa�on generale si $codeindividu='' et numsejour='', sinon pour l'individu $codeindividu
  $query_rs_gestthemeindividu="select * from individutheme,gesttheme,structure".
															" where individutheme.codetheme=gesttheme.codetheme ".
															" and gesttheme.codetheme=structure.codestructure".
															($codeindividu==""?"":" and individutheme.codeindividu = ".GetSQLValueString($codeindividu, "text")).
															($numsejour==""?"":" and individutheme.numsejour = ".GetSQLValueString($numsejour, "text")).									
															" and gesttheme.codegesttheme = ".GetSQLValueString($codeuser, "text");
  $rs_gestthemeindividu=mysql_query($query_rs_gestthemeindividu);
  $nb_rs_gestthemeindividu = mysql_num_rows($rs_gestthemeindividu);
  if($nb_rs_gestthemeindividu!=0)// gest theme
  { $tab_roleuser['theme']=$tab_statutvisa['theme'];
    $estresptheme=false;//correction orthographe estreptheme le 30/01/2012
  }

  // role theme si resp. en tant que resptheme de fa�on generale si $codeindividu='' et numsejour='', sinon pour l'individu $codeindividu de $numsejour
  $query_rs_respthemeindividu="select * from individutheme,structureindividu".
															" where individutheme.codetheme=structureindividu.codestructure ".
															($codeindividu==""?"":" and individutheme.codeindividu = ".GetSQLValueString($codeindividu, "text")).
															($numsejour==""?"":" and individutheme.numsejour = ".GetSQLValueString($numsejour, "text")).									
															" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text");
  $rs_respthemeindividu=mysql_query($query_rs_respthemeindividu);
  $nb_rs_respthemeindividu = mysql_fetch_assoc($rs_respthemeindividu);
  if($nb_rs_respthemeindividu!=0)// resp de theme
  { $tab_roleuser['theme']=$tab_statutvisa['theme'];
    $estresptheme=true;
  }

  // roles du, sii, admingestfin, gestcnrs (provenant de) structure
  $rs_structureindividu=mysql_query("select codeindividu,codelib from structureindividu,structure".
																		" where structureindividu.codestructure=structure.codestructure".
																		" and (codelib=".GetSQLValueString('srh', "text")." or codelib=".GetSQLValueString('du', "text").
																		" 			or codelib=".GetSQLValueString('sii', "text")." or codelib=".GetSQLValueString('admingestfin', "text").
																		" 			or codelib=".GetSQLValueString('ist', "text")." or codelib=".GetSQLValueString('sif', "text").
																		" or codelib=".GetSQLValueString('gestprojet', "text").// 20170420
																		/*  " or codelib=".GetSQLValueString('gestul', "text").// 20170420
																		" or codelib=".GetSQLValueString('gestcnrs', "text"). */
																		" or codelib=".GetSQLValueString('gestperscontrat', "text").")") or die(mysql_error()); //and estresp='oui'
  while($row_rs_structureindividu = mysql_fetch_assoc($rs_structureindividu))
  { if($row_rs_structureindividu['codeindividu']==$codeuser)
    { if(isset($tab_statutvisa[$row_rs_structureindividu['codelib']]))
			{ $tab_roleuser[$row_rs_structureindividu['codelib']]=$tab_statutvisa[$row_rs_structureindividu['codelib']];// 20170420
			}
    }
  }
  // role srhue pour le role srh
  if(array_key_exists('srh',$tab_roleuser))
  { $tab_roleuser['srhue']=$tab_statutvisa['srhue'];
  }
  $tab_result['tab_roleuser']=$tab_roleuser;
  $tab_result['estreferent']=$estreferent;
  $tab_result['estresptheme']=$estresptheme;
  
  if(isset($rs_individu)) mysql_free_result($rs_individu);
  if(isset($rs_respthemeindividu)) mysql_free_result($rs_respthemeindividu);
  if(isset($rs_structureindividu)) mysql_free_result($rs_structureindividu);
  if(isset($rs_gestthemeindividu)) mysql_free_result($rs_gestthemeindividu);
  return $tab_result;
// ------------------ FIN des roles $codeuser
}

// get_info_user : informations du user connecte sous forme de tableau. Le user doit etre present
// 'codeindividu'=>codeindividu, 'nom'=> nom, 'prenom'=>prenom, 'email'=>email
// 'codetheme'=>array(codetheme1,codetheme2,...), theme=>liste lib courts s�par�s par virgules
function get_info_user($codeuser)
{ $tab_info_user=array();
  $rs_individu=mysql_query("select codeindividu, nom, prenom, email,login  from individu where codeindividu=".GetSQLValueString($codeuser, 'text'));
  if($row_rs_individu=mysql_fetch_assoc($rs_individu))
  { $tab_info_user['codeindividu']=$row_rs_individu['codeindividu'];
    $tab_info_user['nom']=$row_rs_individu['nom'];
    $tab_info_user['prenom']=$row_rs_individu['prenom'];
    $tab_info_user['email']=$row_rs_individu['email'];
    $tab_info_user['login']=$row_rs_individu['login'];
  }
  // theme(s) pour un codeuser
	$query_individutheme= "SELECT individutheme.codetheme,libcourt_fr from individusejour,individutheme,structure".
												" WHERE individusejour.codeindividu=individutheme.codeindividu AND individusejour.numsejour=individutheme.numsejour".
												" AND individutheme.codetheme=structure.codestructure AND structure.esttheme='oui'".
												" AND ".periodeencours('datedeb_sejour','datefin_sejour')." AND ".periodeencours('datedeb_theme','datefin_theme').
												// debut modif historique theme 20121231
												" AND ".periodeencours('structure.date_deb','structure.date_fin').
												// fin modif historique theme 20121231
												" AND individutheme.codeindividu=".GetSQLValueString($codeuser, "text") or die(mysql_error());
  $rs_individutheme=mysql_query($query_individutheme);
  $tab_info_user['codetheme']=array();
	$tab_info_user['theme']="";//pas de theme par defaut
  $first=true;
  while($row_rs_individutheme = mysql_fetch_assoc($rs_individutheme))
  { $tab_info_user['codetheme'][]=$row_rs_individutheme['codetheme'];
	  if($first)
    { $tab_info_user['theme']=$row_rs_individutheme['libcourt_fr'];
	  	$first=false;
		}
		else
		{ $tab_info_user['theme'].=", ".$row_rs_individutheme['libcourt_fr'];
		}
  }
  if(isset($rs_individutheme)) mysql_free_result($rs_individutheme);  
  if(isset($rs_structureindividu)) mysql_free_result($rs_structureindividu);
  if(isset($rs_individu)) mysql_free_result($rs_individu);
  if(isset($rs_gesttheme)) mysql_free_result($rs_gesttheme);

  return $tab_info_user;
}

function tablehtml_info_user($tab_infouser,$tab_roleuser)
{	$tablehtml= 		
	'			<table border="0" cellspacing="1" cellpadding="0">
					<tr>
						<td><img src="images/b_individu.png" width="14" height="18">
						</td>
						<td>
							<span class="bleucalibri9">Utilisateur Connect&eacute;&nbsp;:&nbsp;</span>
							<span class="mauvegrascalibri9">'.$tab_infouser['prenom'].' '.$tab_infouser['nom'].'</span> <span class="mauvecalibri9">['.$tab_infouser['theme'].']&nbsp;</span>';
							$tablehtml.=
'						</td>
					</tr>
				</table>';
	return $tablehtml;
}

function get_individu_visas($codeindividu,$numsejour,$tab_statutvisa)
{ //------------------ VISAS (roles) : $codeindividu a deja un ou plusieurs visas $tab_individustatutvisa apposes dans la liste de tous les visas (roles) $tab_statutvisa
  //liste des statutvisa deja apposes pour cet individu 
	// 'referent'=>01, srhue=>'02',...
  $tab_individustatutvisa=array();
  $query_rs_individustatutvisa="SELECT individustatutvisa.*,coderole from individustatutvisa,statutvisa".
															 " where individustatutvisa.codeindividu=".GetSQLValueString($codeindividu, "text").
															 " and individustatutvisa.numsejour=".GetSQLValueString($numsejour, "text").
															 " and individustatutvisa.codestatutvisa=statutvisa.codestatutvisa".
															 " order by individustatutvisa.codestatutvisa";
  $rs_individustatutvisa=mysql_query($query_rs_individustatutvisa) or die(mysql_error()); 
  while($row_rs_individustatutvisa = mysql_fetch_assoc($rs_individustatutvisa))
  { $tab_individustatutvisa[$row_rs_individustatutvisa['coderole']]=$tab_statutvisa[$row_rs_individustatutvisa['coderole']];
  }
  
  mysql_free_result($rs_individustatutvisa);
  return $tab_individustatutvisa;

}// ------------------ FIN des visas $codeindividu

// -------------------- Droits de modif des roles de codeuser et statut de visa pour la colonne concernee colstatutvisa
/* Retourne, pour un dossier individu, le droit read/write pour le role de la colonne concernee
   et pour la colonne de visa $colstatutvisa consideree l'etat a afficher (appose, a valider, en cours ou en attente)
	 pour le user de role $tab_roleuser  
*/
function contenu_col_role_droit($row_rs_individu,$tab_individustatutvisa,$tab_roleuser,$colstatutvisa)// 20161213
{  
  /*  $tab_individustatutvisa = liste des visas deja apposes
	$tab_roleuser=liste des roles du user codeuser
	$colstatutvisa=colonne concernee 
  */
	
  $contenu="";//ne doit pas etre vide pour les colonnes de roles a afficher : mettre n/a s'il n'y a rien a mettre d'autre
  $droit="read";// valeur par defaut de droit (read/write) du role $role
  if($colstatutvisa=='referent') 
  { // si visa 'referent' appose pour $codeindividu
		if(array_key_exists('referent',$tab_individustatutvisa))
		{ $contenu = "visa appose";
		} 
		else // le visa 'referent' n'est pas appose pour $codeindividu
		{ if(array_key_exists('referent',$tab_roleuser))// $codeuser a le role 'referent' meme si le referent r&eacute;el n'est pas lui
	  	{ $droit="write";	  
	    	$contenu = "valider";
	  	}
      else// $codeuser n'a pas le role 'referent'
	  	{ $contenu = "brancher";
	  	}
		}	
		// 20161213
		if($row_rs_individu['pourquoi_pas_de_demande_fsd']=='- de 5j')
		{ $droit="write";
		}
  }
  else if($colstatutvisa=='srhue')// visa 'visasrhue'
  { /* if($ue=='oui')// UE // 20161213
		{ $contenu = "n/a";
    } 
		else
		{  */
		if(array_key_exists('srhue',$tab_individustatutvisa))// visa 'visasrhue' appose
		{ $contenu = "visa appose";
		}
		else// visa 'visasrhue' non appose
		{ if(array_key_exists('referent',$tab_individustatutvisa))//visa referent appose
			{ if(array_key_exists('srhue',$tab_roleuser))// $codeuser a le role 'srhue'
				{ $droit="write";
					$contenu = "valider";
				}	
				else // $codeuser n'a pas le role 'srhue'
				{ $contenu = "brancher";
				}
			}
			else//visa referent non appose : attente de visa referent
			{ $contenu ="sablier";
			}
		}
		
  }
  else if($colstatutvisa=='theme')// visa 'theme'
  {	if(array_key_exists('theme',$tab_individustatutvisa)) // visa 'theme' appose $codeindividu
		{ $contenu = "visa appose";
		} 
		else // visa 'theme' n'est pas appose
		{ if(array_key_exists('theme',$tab_roleuser) || array_key_exists('srh',$tab_roleuser))// 20170609
			{ if(!array_key_exists('referent',$tab_individustatutvisa))//visa referent non appose
		  	{ $contenu = "sablier";
		  	}
				// 20161213
				else if(array_key_exists('srhue',$tab_individustatutvisa) || !$row_rs_individu['demander_autorisation'])// 20170609
		  	{ //$droit="write";// 20161213
		    	$contenu = "valider";
		  	}
				else
				{ $contenu = "sablier";
				}
				/* else //visa referent appose
		  	{ $droit="write";
		    	$contenu = "valider";
		  	} */
	    }
      else // $codeuser n'a pas le role 'theme'
	    { if(!array_key_exists('referent',$tab_individustatutvisa) || (!array_key_exists('srhue',$tab_individustatutvisa) && $row_rs_individu['demander_autorisation']))//visa referent non appose 
		  	{ $contenu = "sablier";
		  	}
        else
        { $contenu ="brancher";
		  	}
			}
	  }
  }
  $tab_contenu_col_role_droit[$colstatutvisa]['droit']=$droit;
  $tab_contenu_col_role_droit[$colstatutvisa]['colonne']=$contenu;
  return $tab_contenu_col_role_droit;
}

function get_tab_individu_acteurs($row_rs_individu)
{ $liste_acteurs=array();
  $tab_acteurs['referent'][1]=get_info_user($row_rs_individu['codereferent']);
	// le gestionnaire en charge du dossier
	$tab_acteurs['gesttheme'][1]=get_info_user($row_rs_individu['codegesttheme']);
	// PG 20151122 les gestionnaires ne sont plus secr. de theme mais de site. 
	// Pour ne pas modifier de nombreux programmes, l'appelation gesttheme
	// reste utilisee au lieu de secrsite
	$rs=mysql_query("select codesecrsite from secrsite,individusejour".
									" where secrsite.codesecrsite=individusejour.codeindividu".
									" and ".periodeencours('datedeb_sejour','datefin_sejour').
									" and codesite=".GetSQLValueString($row_rs_individu['codelieu'], 'text'));
	$i=1;
  while($row_rs = mysql_fetch_assoc($rs))
  { $i++;
		$tab_acteurs['gesttheme'][$i]=get_info_user($row_rs['codesecrsite']);
  }
	// PG 2015112
	
  // resptheme(s) pour cet individu sous la forme themeXX ou XX=codetheme
  $rs=mysql_query("select structure.libcourt_fr as libtheme, structureindividu.codeindividu as coderesptheme".
									" from individutheme,structureindividu,structure".
									" where individutheme.codetheme=structureindividu.codestructure".
									" and structureindividu.codestructure=structure.codestructure and structureindividu.estresp='oui'".
									" and individutheme.codeindividu=".GetSQLValueString($row_rs_individu['codeindividu'], 'text').
									" and individutheme.numsejour=".GetSQLValueString($row_rs_individu['numsejour'], 'text').
									" AND ".periodeencours('structure.date_deb','structure.date_fin')
									);
	$i=0;
  while($row_rs = mysql_fetch_assoc($rs))
  { $i++;
		$tab_acteurs['theme'][$i]=get_info_user($row_rs['coderesptheme']);
  }
  // roles du, srh, sii, admingestfin (provenant de) structure
  $rs=mysql_query("select codeindividu as coderesp,codelib from structureindividu,structure".
								    " where structureindividu.codestructure=structure.codestructure".
								    " and (codelib=".GetSQLValueString('srh', "text")." or codelib=".GetSQLValueString('du', "text")." or codelib=".GetSQLValueString('admingestfin', "text").
									  " or (codelib=".GetSQLValueString('sii', "text")." and structureindividu.estresp='oui'))") or die(mysql_error());
  $i=0;
	while($row_rs = mysql_fetch_assoc($rs))
  { $i++;
		$tab_acteurs[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
		// 4/10/2016
		if($row_rs['codelib']=='srh')
		{ $tab_acteurs['srhue'][$i]=$tab_acteurs['srh'][$i];
		}
		// 4/10/2016
  }
  // role srhue pour le role srh
  /* 4/10/2016
	if(array_key_exists('srh',$tab_acteurs))
  { list($i,$tab)=each($tab_acteurs['srh']);
		$tab_acteurs['srhue'][1]=$tab_acteurs['srh'][$i];
  } */
  if(isset($rs))mysql_free_result($rs);
  return $tab_acteurs;
}

function get_roles_liblong()
{ // libelles 'longs' des roles acteurs
	$tab_roles_liblong=array();
	$rs=mysql_query("select coderole, liblong from statutvisa where codestatutvisa<>'' order by codestatutvisa") or die(mysql_error());
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_roles_liblong[$row_rs['coderole']]=$row_rs['liblong']; 
  }
	$libtheme='D&eacute;pt';
	$libtheme=$GLOBALS['libcourt_theme_fr'];
	$tab_roles_liblong['gesttheme']='Gestionnaire '.$libtheme;  
	$tab_roles_liblong['theme']='Responsable '.$libtheme;
	$tab_roles_liblong['admingestfin']='Responsable Admin.';
	if(isset($rs))mysql_free_result($rs);
  return $tab_roles_liblong;

}

function sujet_saisi_valide($row_rs_individu,$codelibstatutvisa)
{	/* DOCTORANT, STAGIAIRE MASTER, STAGIAIRE A SUJET OBLIGATOIRE, EXTERIEUR avec demander_autorisation 
										 sujet saisi	? non			
																		oui	: theme
																					sujet_valide_par_theme	?	non			
																																		oui	srh, du 
	*/
	$sujet_obligatoire_saisi=($row_rs_individu['codesujet']!="");
	$sujet_valide_par_theme=($row_rs_individu['codestatutsujet']=='V' || $row_rs_individu['codestatutsujet']=='P');
	$valider_visa_possible=true;
	$texte_attente_sujet="";
	if($row_rs_individu['codelibcat']=='DOCTORANT' 
		|| ($row_rs_individu['codelibcat']=='STAGIAIRE' && ($row_rs_individu['codelibtypestage']=='MASTER'  || ($row_rs_individu['codelibtypestage']!='MASTER' && $row_rs_individu['sujetstageobligatoire']=='oui'))) //&& $row_rs_individu['datedeb_sejour']>=$GLOBALS['date_zrr_obligatoire']
	  || ($row_rs_individu['codelibcat']=='EXTERIEUR' && $row_rs_individu['demander_autorisation'])) //&& $row_rs_individu['datedeb_sejour']>=$GLOBALS['date_zrr_obligatoire']
	{ //if($row_rs_individu['datedeb_sejour']>=$GLOBALS['date_zrr_obligatoire'])// ajout 14/09/2014
		{ if($sujet_obligatoire_saisi==false)
			{	if($codelibstatutvisa=='theme' || $codelibstatutvisa=='srh' || $codelibstatutvisa=='du')
				{ $valider_visa_possible=false;
				}
				$texte_attente_sujet="En attente de saisie du sujet par ";
				$tab_info_referent=get_info_user($row_rs_individu['codereferent']);
				$texte_attente_sujet.=$tab_info_referent['prenom'].' '.$tab_info_referent['nom'];
			}
			else
			{ if($sujet_valide_par_theme==false)
				{	if($codelibstatutvisa=='srh' || $codelibstatutvisa=='du')
					{ $valider_visa_possible=false;
					}
					$texte_attente_sujet="En attente de validation du sujet par le Responsable de ".$GLOBALS['libcourt_theme_fr'];
				}
			}
		}
	}
	return array('sujet_saisi_valide'=>$valider_visa_possible,'texte_attente_sujet'=>$texte_attente_sujet);
}

/* ---------------------------------------- CONTRATS --------------------------------------------------- 
																VISAS ET ROLES par user + workflow 																		 
*/
// liste des visas possible et des roles : il n'y a pas de statutvisa
function get_contrat_statutvisa()
{ return array();
}

function get_tab_contrat_roleuser($codeuser,$codecontrat,$tab_contrat_statutvisa,$estreferent,$estresptheme)
{ $tab_result=array();
	$tab_roleuser=array();// table des roles
	// roles du, sif, admingestfin, gestcnrs et gestul(provenant de) structure
 	$rs_structureindividu=mysql_query("select codeindividu,codelib from structureindividu,structure".
																		" where structureindividu.codestructure=structure.codestructure".
																		" and (codelib=".GetSQLValueString('sif', "text")." or codelib=".GetSQLValueString('du', "text").
																		"       or codelib=".GetSQLValueString('admingestfin', "text").// 20170420
																		/* " 			or codelib=".GetSQLValueString('gestcnrs', "text").
																		"       or codelib=".GetSQLValueString('gestul', "text"). */")") or die(mysql_error()); //and estresp='oui'
  while($row_rs_structureindividu = mysql_fetch_assoc($rs_structureindividu))
  { if($row_rs_structureindividu['codeindividu']==$codeuser)
    { if($row_rs_structureindividu['codelib']=='sif')
			{ $tab_roleuser[$row_rs_structureindividu['codelib'].'#1']=$tab_cmd_statutvisa[$row_rs_structureindividu['codelib'].'#1'];
    		$tab_roleuser[$row_rs_structureindividu['codelib'].'#2']=$tab_cmd_statutvisa[$row_rs_structureindividu['codelib'].'#2'];
			}
			else
			{ $tab_roleuser[$row_rs_structureindividu['codelib']]=$tab_cmd_statutvisa[$row_rs_structureindividu['codelib']];
			}
		}
  }
	
	$tab_result['tab_roleuser']=$tab_roleuser;
  $tab_result['estreferent']=$estreferent;
  $tab_result['estresptheme']=$estresptheme;

  if(isset($rs_commande)) mysql_free_result($rs_commande);
  if(isset($rs_respthemecommande)) mysql_free_result($rs_respthemecommande);
  if(isset($rs_structureindividu)) mysql_free_result($rs_structureindividu);
  if(isset($rs_secrsite)) mysql_free_result($rs_secrsite);
  if(isset($rs)) mysql_free_result($rs);
	
  return $tab_result;



}
/* ---------------------------------------- COMMANDES --------------------------------------------------- 
																VISAS ET ROLES par user + workflow 																		 
*/
// liste des visas possible et des roles
function get_cmd_statutvisa()
{ $tab_cmd_statutvisa=array();
  $rs_cmd_statutvisa=mysql_query("select codestatutvisa,coderole from cmd_statutvisa where codestatutvisa<>'' order by codestatutvisa") or die(mysql_error());
  while($row_rs_cmd_statutvisa = mysql_fetch_assoc($rs_cmd_statutvisa))
  { $tab_cmd_statutvisa[$row_rs_cmd_statutvisa['coderole']]=$row_rs_cmd_statutvisa['codestatutvisa']; 
  }
  mysql_free_result($rs_cmd_statutvisa);  
  return $tab_cmd_statutvisa;
}

function get_cmd_statutvisa_texte_visa_title()
{ $tab_cmd_statutvisa_texte_visa_title=array();
  $rs_cmd_statutvisa_texte_visa_title=mysql_query("select coderole,texte_cmd_statutvisa_title from cmd_statutvisa where codestatutvisa<>'' order by codestatutvisa") or die(mysql_error());
  while($row_rs_cmd_statutvisa_texte_visa_title = mysql_fetch_assoc($rs_cmd_statutvisa_texte_visa_title))
  { $tab_cmd_statutvisa_texte_visa_title[$row_rs_cmd_statutvisa_texte_visa_title['coderole']]=$row_rs_cmd_statutvisa_texte_visa_title['texte_cmd_statutvisa_title']; 
  }
  mysql_free_result($rs_cmd_statutvisa_texte_visa_title);  
  return $tab_cmd_statutvisa_texte_visa_title;
}
//liste des statutvisa deja apposes pour cette commande 
function get_cmd_visas($codecommande,$tab_cmd_statutvisa)
{ //------------------ VISAS (roles) : $codecommande a deja un ou plusieurs visas $tab_individustatutvisa apposes dans la liste de tous les visas (roles) $tab_statutvisa
	// referent=>01, srhue=>02,...
  $tab_commandestatutvisa=array();
  $query_rs_commandestatutvisa="SELECT commandestatutvisa.*,coderole from commandestatutvisa,cmd_statutvisa".
															 " where commandestatutvisa.codestatutvisa=cmd_statutvisa.codestatutvisa and commandestatutvisa.codecommande=".GetSQLValueString($codecommande, "text").
															 " order by commandestatutvisa.codestatutvisa";
  $rs_commandestatutvisa=mysql_query($query_rs_commandestatutvisa) or die(mysql_error()); 
  while($row_rs_commandestatutvisa = mysql_fetch_assoc($rs_commandestatutvisa))
  { $tab_commandestatutvisa[$row_rs_commandestatutvisa['coderole']]=$tab_cmd_statutvisa[$row_rs_commandestatutvisa['coderole']];
  }
  
  mysql_free_result($rs_commandestatutvisa);
  return $tab_commandestatutvisa;

}

// roles user pour une commande ou son ensemble
function get_tab_cmd_roleuser($codeuser,$codecommande,$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat)
{ // renvoie table $tab_result['tab_roleuser']=$tab_roleuser
	//               $tab_result['estreferent']=$estreferent
	//               $tab_result['estresptheme']=$estreferent;
	// $tab_roleuser contient la liste des roles d'un individu : referent, theme, contrat, du pour le directeur, secrsite, sif, admingestfin, gestcnrs et gestul 
	$tab_result=array();
	$tab_roleuser=array();// table des roles
  // roles referent
  if($codecommande!='')
	{ $rs_commande=mysql_query("select codereferent,codesecrsite,codecreateur,codemodifieur".
		  				 							" from commande where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
		$row_rs_commande = mysql_fetch_assoc($rs_commande);
		if($codeuser==$row_rs_commande['codereferent'] || $codeuser==$row_rs_commande['codesecrsite'] || $codeuser==$row_rs_commande['codecreateur'] || $codeuser==$row_rs_commande['codemodifieur'])
		{ $tab_roleuser['referent']=$tab_cmd_statutvisa['referent'];//le role referent est donne a la personne qui valide cette saisie
		}
		$estreferent=($codeuser==$row_rs_commande['codereferent']);
		//secrsite pour commande !=''
		if($codeuser==$row_rs_commande['codesecrsite'])
		{ $tab_roleuser['secrsite']=$tab_cmd_statutvisa['secrsite'];
		}
	}
	else
	{ // role secrsite en general 
		$query_rs_secrsite="select * from secrsite where secrsite.codesecrsite=".GetSQLValueString($codeuser, "text");
		$rs_secrsite=mysql_query($query_rs_secrsite) or die(mysql_error());
		$nb_rs_secrsite = mysql_num_rows($rs_secrsite);		
		if($nb_rs_secrsite>0)
		{ $tab_roleuser['secrsite']=$tab_cmd_statutvisa['secrsite'];
		}
	}
	// role theme si resp. en tant que resptheme de fa�on generale si $codecommande='', sinon pour $codecommande
	$query_rs="select * from  commandeimputationbudget,centrecouttheme,structureindividu".
						" where commandeimputationbudget.codecentrecout=centrecouttheme.codecentrecout and commandeimputationbudget.codeeotp=''".
						" and centrecouttheme.codestructure=structureindividu.codestructure ".
						($codecommande==""?"":" and commandeimputationbudget.codecommande = ".GetSQLValueString($codecommande, "text")).
						" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text").
						" and commandeimputationbudget.virtuel_ou_reel='0'";
  $rs=mysql_query($query_rs);
  if(mysql_num_rows($rs)!=0)
  { $tab_roleuser['theme']=$tab_cmd_statutvisa['theme'];
    $estresptheme=true;
  }
	// role contrat si resp contrat
	$query_rs="select commandeimputationbudget.codecontrat from  commandeimputationbudget,contrat,individu".
						" where commandeimputationbudget.codecontrat=contrat.codecontrat".
						" and commandeimputationbudget.codecontrat<>''".
						($codecommande==""?"":" and commandeimputationbudget.codecommande = ".GetSQLValueString($codecommande, "text")).
						" and individu.codeindividu = contrat.coderespscientifique".
						" and individu.codeindividu <>''".
						" and commandeimputationbudget.virtuel_ou_reel='0'".
						" union".
						" select commandeimputationbudget.codecontrat from  commandeimputationbudget,budg_aci,individu".
						" where commandeimputationbudget.codecontrat=budg_aci.codeaci".
						" and commandeimputationbudget.codecontrat<>''".
						($codecommande==""?"":" and commandeimputationbudget.codecommande = ".GetSQLValueString($codecommande, "text")).
						" and individu.codeindividu = budg_aci.coderespaci".
						" and individu.codeindividu <>''".
						" and commandeimputationbudget.virtuel_ou_reel='0'";
	$rs=mysql_query($query_rs);
  if(mysql_num_rows($rs)!=0)//c'est un contrat
	{ $query_rs="select commandeimputationbudget.codecontrat from  commandeimputationbudget,contrat,individu".
							" where commandeimputationbudget.codecontrat=contrat.codecontrat".
							" and commandeimputationbudget.codecontrat<>''".
							($codecommande==""?"":" and commandeimputationbudget.codecommande = ".GetSQLValueString($codecommande, "text")).
							" and individu.codeindividu = contrat.coderespscientifique".
							" and individu.codeindividu = ".GetSQLValueString($codeuser, "text").
							" and commandeimputationbudget.virtuel_ou_reel='0'".
							" union".
							" select commandeimputationbudget.codecontrat from  commandeimputationbudget,budg_aci,individu".
							" where commandeimputationbudget.codecontrat=budg_aci.codeaci".
							" and commandeimputationbudget.codecontrat<>''".
							($codecommande==""?"":" and commandeimputationbudget.codecommande = ".GetSQLValueString($codecommande, "text")).
							" and individu.codeindividu = budg_aci.coderespaci".
							" and individu.codeindividu = ".GetSQLValueString($codeuser, "text").
							" and commandeimputationbudget.virtuel_ou_reel='0'";

		$rs=mysql_query($query_rs);
		if(mysql_num_rows($rs)==0)// le user n'est pas resp de ce contrat
		{ $estrespcontrat=false;
		}
		else
		{ $tab_roleuser['contrat']=$tab_cmd_statutvisa['contrat'];
			$estrespcontrat=true;
		}
	}

  // roles du, sif, admingestfin, gestcnrs et gestul(provenant de) structure
 	$rs_structureindividu=mysql_query("select codeindividu,codelib from structureindividu,structure".
																		" where structureindividu.codestructure=structure.codestructure".
																		" and (codelib=".GetSQLValueString('sif', "text")." or codelib=".GetSQLValueString('du', "text").
																		"       or codelib=".GetSQLValueString('admingestfin', "text").
																		/* " 			or codelib=".GetSQLValueString('gestcnrs', "text").// 20170420
																		"       or codelib=".GetSQLValueString('gestul', "text"). */")") or die(mysql_error()); //and estresp='oui'
  while($row_rs_structureindividu = mysql_fetch_assoc($rs_structureindividu))
  { if($row_rs_structureindividu['codeindividu']==$codeuser)
    { if($row_rs_structureindividu['codelib']=='sif')
			{ $tab_roleuser[$row_rs_structureindividu['codelib'].'#1']=$tab_cmd_statutvisa[$row_rs_structureindividu['codelib'].'#1'];
    		$tab_roleuser[$row_rs_structureindividu['codelib'].'#2']=$tab_cmd_statutvisa[$row_rs_structureindividu['codelib'].'#2'];
			}
			else
			{ $tab_roleuser[$row_rs_structureindividu['codelib']]=$tab_cmd_statutvisa[$row_rs_structureindividu['codelib']];
			}
		}
  }
	$tab_result['tab_roleuser']=$tab_roleuser;
  $tab_result['estreferent']=$estreferent;
  $tab_result['estresptheme']=$estresptheme;
  $tab_result['estrespcontrat']=$estrespcontrat;

  if(isset($rs_commande)) mysql_free_result($rs_commande);
  if(isset($rs_respthemecommande)) mysql_free_result($rs_respthemecommande);
  if(isset($rs_structureindividu)) mysql_free_result($rs_structureindividu);
  if(isset($rs_secrsite)) mysql_free_result($rs_secrsite);
  if(isset($rs)) mysql_free_result($rs);
	
  return $tab_result;

}

function get_tab_cmd_acteurs($row_rs_commande)
{ $codecommande=$row_rs_commande['codecommande'];
	$liste_acteurs=array();
  $tab_acteurs['referent'][1]=get_info_user($row_rs_commande['codereferent']);
	$tab_acteurs['secrsite'][1]=get_info_user($row_rs_commande['codesecrsite']);
	// PG 20151128 les gestionnaires ne sont plus secr. de theme mais de site. 
	// Pour ne pas modifier de nombreux programmes, l'appelation gesttheme
	// reste utilisee au lieu de secrsite
	$tab_contexte['prog']='gestioncommandes';
	$tab_contexte['function']='get_tab_cmd_acteurs';
	$tab_contexte['groupe']='pole_gestion_fst';
	$tab_contexte['codesecrsite']=$row_rs_commande['codesecrsite'];
	$rs=mysql_query("select codesecrsite from secrsite,individusejour".
									" where secrsite.codesecrsite=individusejour.codeindividu".
									" and ".periodeencours('datedeb_sejour','datefin_sejour'));
	$i=1;
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_contexte['codeuser']=$row_rs['codesecrsite'];
		if(droit_acces($tab_contexte))
		{ $i++;
			$tab_acteurs['secrsite'][$i]=get_info_user($row_rs['codesecrsite']);
		}
  }
	// PG 20151128
	
	$query_rs="(select distinct budg_contrat_source_vue.coderespscientifique as coderespcontrat,'contrat' as typeresp from  commandeimputationbudget,budg_contrat_source_vue".
						" where commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
						" and commandeimputationbudget.codecontrat<>''".
						" and budg_contrat_source_vue.coderespscientifique <>''".
						" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecommande=".GetSQLValueString($row_rs_commande['codecommande'], "text").")".
						" UNION".
						" (select distinct structureindividu.codeindividu as coderespcontrat,'theme' as typeresp".
						" from  commandeimputationbudget,budg_contrat_source_vue,centrecouttheme,structureindividu".
						" where budg_contrat_source_vue.coderespscientifique=''".
						" and commandeimputationbudget.codecontrat<>''".
						" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecommande=".GetSQLValueString($row_rs_commande['codecommande'], "text").
						" and commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
						" and budg_contrat_source_vue.codecentrecout=centrecouttheme.codecentrecout".
						" and centrecouttheme.codestructure=structureindividu.codestructure".")";
  $rs=mysql_query($query_rs);
	$i=0;
	while($row_rs = mysql_fetch_assoc($rs))
  { $i++;
		$tab_acteurs[$row_rs['typeresp']][$i]=get_info_user($row_rs['coderespcontrat']);
  }

 // par defaut de respcontrat ET de resptheme : le directeur
	if(!isset($tab_acteurs['contrat'][1]) && !isset($tab_acteurs['theme'][1]))
	{ $query_rs="select codeindividu as coderesp,codelib from structureindividu,structure".
								    " where structureindividu.codestructure=structure.codestructure".
								    " and codelib=".GetSQLValueString('du', "text")." and structureindividu.estresp='oui'";
		$rs=mysql_query($query_rs) or die(mysql_error());
		$row_rs = mysql_fetch_assoc($rs);
		$tab_acteurs['theme'][1]=get_info_user($row_rs['coderesp']);
	}
// roles du, sif (provenant de) structure
  $rs=mysql_query("select codeindividu as coderesp,codelib from structureindividu,structure".
								    " where structureindividu.codestructure=structure.codestructure".
								    " and (codelib=".GetSQLValueString('sif', "text").
										" or codelib=".GetSQLValueString('du', "text").") and structureindividu.estresp='oui'") or die(mysql_error());
	$i=0;
  while($row_rs = mysql_fetch_assoc($rs))
	{ $i++;
		$tab_acteurs[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
  }
	if(isset($rs))mysql_free_result($rs);
  return $tab_acteurs;
}

function get_cmd_roles_liblong()
{ // libelles 'longs' des roles acteurs
	$tab_roles_liblong=array();
	$rs=mysql_query("select coderole, liblong from cmd_statutvisa where codestatutvisa<>'' order by codestatutvisa") or die(mysql_error());
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_roles_liblong[$row_rs['coderole']]=$row_rs['liblong']; 
  }
	if(isset($rs))mysql_free_result($rs);
  return $tab_roles_liblong;

}

function contenu_cmd_col_role_droit($tab_commandestatutvisa,$tab_roleuser,$estrespcontrat,$colstatutvisa,$a_valider_par_resp)
{ /*
	$tab_individustatutvisa = liste des visas deja apposes
	$tab_roleuser=liste des roles du user codeuser
	$colstatutvisa=colonne concernee 
  */
  $contenu="";//ne doit pas etre vide pour les colonnes de roles a afficher : mettre n/a s'il n'y a rien a mettre d'autre
  $droit="read";// valeur par defaut de droit (read/write) du role $role 
  if($colstatutvisa=='referent') 
  { // si visa 'referent' appose pour $codecommande
		if(array_key_exists('referent',$tab_commandestatutvisa))
		{ $contenu = "visa appose";
		} 
		else // le visa 'referent' n'est pas appose pour $codecommande
		{ if(array_key_exists('referent',$tab_roleuser) || $_SESSION['b_cmd_etre_admin'])// $codeuser a le role 'referent' meme si le referent r&eacute;el n'est pas lui
	  	{ $droit="write";	  
	    	$contenu = "valider";
	  	}
      else// $codeuser n'a pas le role 'referent'
	  	{ $contenu = "brancher";
	  	}
		}	
  }
  else if($colstatutvisa=='theme' || $colstatutvisa=='contrat')// visa 'theme' ou contrat
  {	if(array_key_exists('theme',$tab_commandestatutvisa) || array_key_exists('contrat',$tab_commandestatutvisa)) // visa 'theme'  ou contrat appose
		{ $contenu = "visa appose";
		} 
		else // visa 'theme'  ou contrat n'est pas appose
		{ if((array_key_exists('theme',$tab_roleuser) && $estrespcontrat) || (array_key_exists('contrat',$tab_roleuser) && $estrespcontrat) || $_SESSION['b_cmd_etre_admin'])
			{ if(!array_key_exists('referent',$tab_commandestatutvisa))//visa referent non appose
		  	{ $contenu = "sablier";
		  	}
		  	else //visa referent appose
		  	{ if($a_valider_par_resp  || $_SESSION['b_cmd_etre_admin'])
					{ $droit="write";
		    		$contenu = "valider";
					}
					else
					{ $contenu = "brancher";
					}
		  	}
	    }
      else // $codeuser n'a pas le role 'theme' ou contrat
	    { if(!array_key_exists('referent',$tab_commandestatutvisa))//visa referent non appose 
		  	{ $contenu = "sablier";
		  	}
        else
        { $contenu ="brancher";
		  	}
			}
	  }
  }
  else if($colstatutvisa=='sif#1')// visa 'sif#1'
  { // visa 'sif' deja appose ?
		if(array_key_exists('sif#1',$tab_commandestatutvisa))// visa 'sif' deja appose
		{ $contenu = "visa appose";
		} 
		else // visa 'sif' non appose
		{ if(!array_key_exists('theme',$tab_commandestatutvisa) && !array_key_exists('contrat',$tab_commandestatutvisa))//visa theme ou contrat non appose
			{ $contenu = "sablier";
			}
			else//visa theme ou contrat appose
			{ if(array_key_exists('sif#1',$tab_roleuser))// visa 'referent' et 'theme' appose et role sif
		  	{ $droit="write";
		 	  	$contenu = "valider";
		 		}
      	else// $codeuser n'a pas le role 'sif'
		 		{ $contenu = "brancher";
	   		}
    	}
  	}
  }
  else if($colstatutvisa=='secrsite')// visa 'secrsite'
  { // visa 'secrsite' deja appose ?
		if(array_key_exists('secrsite',$tab_commandestatutvisa))// visa 'secrsite' deja appose
		{ $contenu = "visa appose";
		} 
		else // visa 'secrsite' non appose
		{ if(!array_key_exists('sif#1',$tab_commandestatutvisa))//visa sif#1 non appose
			{ $contenu = "sablier";
			}
			else//visa 'secrsite' appose
			{ if(array_key_exists('secrsite',$tab_roleuser) || $_SESSION['b_cmd_etre_admin'])// visa 'referent' et 'theme' appose et role secrsite
		  	{ $droit="write";
		 	  	$contenu = "valider";
		 		}
      	else// $codeuser n'a pas le role 'sif'
		 		{ $contenu = "brancher";
	   		}
    	}
  	}
  }
  else if($colstatutvisa=='sif#2')// visa 'sif#2'
  { // visa 'sif#2' deja appose ?
		if(array_key_exists('sif#2',$tab_commandestatutvisa))// visa 'sif#2' deja appose
		{ $contenu = "visa appose";
		} 
		else // visa 'sif' non appose
		{ if(!array_key_exists('secrsite',$tab_commandestatutvisa))//visa theme non appose
			{ $contenu = "sablier";
			}
			else//visa theme appose
			{ if(array_key_exists('sif#2',$tab_roleuser))// visa 'referent' et 'theme' appose et role sif
		  	{ $droit="write";
		 	  	$contenu = "valider";
		 		}
      	else// $codeuser n'a pas le role 'sif'
		 		{ $contenu = "brancher";
	   		}
    	}
  	}
  }
  $tab_contenu_col_role_droit[$colstatutvisa]['droit']=$droit;
  $tab_contenu_col_role_droit[$colstatutvisa]['colonne']=$contenu;
  return $tab_contenu_col_role_droit;
}

function affiche_ligne_cmd_ou_miss($row_rs_commande,$num_ordonne,$tab_ordonne_commande_par_etat,$tab_commandemigo, $tab_commandeinventaire, $tab_cmd_statutvisa,$tab_cmd_statutvisa_texte_visa_title, $tab_contexte, $class, $cmd_ancre,$numrow,$mini,$codeuser,$tab_roleuser)
{ // table des roles et droits de $codeuser et $estreferent+$estresptheme pour cette commande
	$cmd_ou_miss=$row_rs_commande['cmd_ou_miss'];
	$codecmdmiss=$row_rs_commande['codecmdmiss'];
	if($cmd_ou_miss=='commande')
	{ $tab_resp_roleuser=$row_rs_commande['tab_resp_roleuser'];//get_tab_cmd_roleuser($codeuser,$row_rs_commande['codecommande'],$tab_cmd_statutvisa,false,false,true);
		$tab_rolecmduser=$tab_resp_roleuser['tab_roleuser'];
		$estreferent=$tab_resp_roleuser['estreferent'];// user a le role referent mais n'est pas forc�ment le referent
		$estresptheme=$tab_resp_roleuser['estresptheme'];// user a le role theme mais n'est pas forc�ment r�f�rent : peut etre le gesttheme
		$estrespcontrat=$tab_resp_roleuser['estrespcontrat'];
		// table des statuts visas deja apposes pour cette commande
		//$tab_commandestatutvisa=get_cmd_visas($row_rs_commande['codecommande'],$tab_cmd_statutvisa);
		if(isset($row_rs_commande['tab_commandestatutvisa']))
		{ $tab_commandestatutvisa=$row_rs_commande['tab_commandestatutvisa'];
		}
		else
		{ $tab_commandestatutvisa=array();
		}
	}
	/* contenu pour chaque colonne visa affichee + droit read/write pour chaque role sous la forme :
	// $tab_contenu_col_role_droit['referent']['colonne']='visa appose', 'valider', 'brancher','sablier' ou 'n/a'
	// $tab_contenu_col_role_droit['referent']['droit']='read', 'write'
	*/

	foreach($tab_cmd_statutvisa as $codelibstatutvisa=>$un_statutvisa)
	{ $droitmodif="read";//pas de modif/suppr par defaut
		if($cmd_ou_miss=='commande')
		{ //09062013 $estrespcontrat=false;//contrat => droit read resp dept, droit write resp contrat
			$tab=contenu_cmd_col_role_droit($tab_commandestatutvisa,$tab_rolecmduser,$estrespcontrat,$codelibstatutvisa,isset($row_rs_commande['a_valider_par_resp'])?$row_rs_commande['a_valider_par_resp']:false);
			if(in_array($codelibstatutvisa,array("referent","secrsite",array_key_exists("contrat",$tab_rolecmduser)?"contrat":"theme","sif#1","sif#2")))
			{ $tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=$tab[$codelibstatutvisa]['colonne'];
			}
			else
			{ $tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']="";
			}
			$tab_contenu_col_role_droit[$codelibstatutvisa]['droit']=$tab[$codelibstatutvisa]['droit'];
			// le droit de $codeuser est fonction du droit par role : le role de $tab_rolecmduser a un droit vrai 
			// dans $tab_contenu_col_role_droit pour son role le plus '�lev�'
			foreach($tab_rolecmduser as $codelibroleuser=>$valroleuser)//un seul droit write d'un role du $codeuser => droit write
			{ if(isset($tab_contenu_col_role_droit[$codelibroleuser]['droit']) && $tab_contenu_col_role_droit[$codelibroleuser]['droit']=="write")
				{ $droitmodif="write";
				}
			}
		}
		else//mission
		{ $tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']="&nbsp;";
			// PG 20151118
			$tab_contexte['codesecrsite']=$row_rs_commande['codesecrsite'];
			if(in_array($codeuser,array($row_rs_commande['codereferent'],$row_rs_commande['codesecrsite'])) || droit_acces($tab_contexte))
			{ // PG 20151118
				$droitmodif="write";
			}
			else
			{ $tab_contenu_col_role_droit[$codelibstatutvisa]['droit']="";
			}
		}
	}
	// 20170412
	$droitmodif_inventaire=false;
	if($droitmodif!="write")
	{ if($cmd_ou_miss=='commande' && (array_key_exists('secrsite',$tab_rolecmduser) || droit_acces($tab_contexte))
		 && $row_rs_commande['estinventorie']!='oui'  && $tab_contenu_col_role_droit['sif#1']['colonne']=='visa appose'
		&& ($row_rs_commande['codenature']=='05' || $row_rs_commande['codenature']=='08'  || $row_rs_commande['codenature']=='10'))
		{ $droitmodif_inventaire=true;
		}
	}
	// 20170412
	$barre="";
	if($row_rs_commande['estannule']=='oui')
	{ $barre=' style="text-decoration:line-through"';
	}
	$ligne="";
	$class=="even"?$class="odd":$class="even";
	// 20170412
	if($tab_contexte['prog']=='gestioncommandes')
	{ $ligne.='<tr class="'.($cmd_ancre==$codecmdmiss?'marked':(($cmd_ou_miss=='mission')?'mission':($row_rs_commande['estavoir']=='oui'?'avoir':$class))).'" id="t'.substr($cmd_ou_miss,0,1).($row_rs_commande['estavoir']=='oui'?'a':'').$numrow.'" onClick="m(this)" '.(($_SESSION['b_cmd_etre_admin'] || $droitmodif=="write" || $droitmodif_inventaire)?'onDblClick="e(\''.$codecmdmiss.'\')"':'').'>';
	}
	else
	{ $ligne.='<tr class="'.(($cmd_ou_miss=='mission')?'mission':($row_rs_commande['estavoir']=='oui'?'avoir':$class)).'" id="t'.substr($cmd_ou_miss,0,1).($row_rs_commande['estavoir']=='oui'?'a':'').$numrow.'">';
	}
	if($tab_contexte['prog']=='gestioncommandes')
	{ if($_SESSION['b_cmd_etre_admin'])
		{ $ligne.='<td>';
			if($cmd_ou_miss=='commande' && $tab_contenu_col_role_droit['sif#2']['colonne']=='visa appose')
			{ $ligne.='<img src="i/'.($row_rs_commande['traite_ieb']=='oui'?'oui':'non').'.png" onClick="p(\'ti\',\''.$codecmdmiss.'\',\''.($row_rs_commande['traite_ieb']=='oui'?'n':'o').'\');">';
			}
			$ligne.='</td>';
		}
		// 20170412
		$au_moins_une_ligne_inventaire=isset($tab_commandeinventaire[$row_rs_commande['codecommande']]);
		// 20170412
		$ligne.='<td><a name="'.$codecmdmiss.'"></a>';
		if($cmd_ou_miss=='mission')
		{ $ligne.=$mini?'<img src="i/t">':'<img src="i/t1.png">';
		}
		else if($row_rs_commande['codemission']=='')
		{ // 20170412
			if((array_key_exists('secrsite',$tab_rolecmduser) || $_SESSION['b_cmd_etre_admin'] || droit_acces($tab_contexte))
			&& ($row_rs_commande['codenature']=='05' || $row_rs_commande['codenature']=='08'  || $row_rs_commande['codenature']=='10'))
			{ if($row_rs_commande['estinventorie']=='oui')
				{ $ligne.=$mini?'<img src="i/inv">':'<img src="i/inv1.png">';
				}
				else
				{ $ligne.=($mini?'<img src="i/eq"':'<img src="i/eq1.png"').($au_moins_une_ligne_inventaire?' onClick="p(\'inv\',\''.$codecmdmiss.'\',\'o\');"':'').'>';
				}
			}
			// 20170412
			else
			{ $ligne.=$mini?'<img src="i/e">':'<img src="i/e1.png">';
			}
		}
		$ligne.='</td>';
		$ligne.='<td'.$barre.'>';
		if($cmd_ou_miss=='commande' && $row_rs_commande['codemission']!='')
		{ // 20170412
			if((array_key_exists('secrsite',$tab_rolecmduser) || $_SESSION['b_cmd_etre_admin'] || droit_acces($tab_contexte))
			&& ($row_rs_commande['codenature']=='05' || $row_rs_commande['codenature']=='08'  || $row_rs_commande['codenature']=='10'))
			{ if($row_rs_commande['estinventorie']=='oui')
				{ $ligne.=$mini?'<img src="i/inv">':'<img src="i/inv1.png">';
				}
				else
				{ $ligne.=($mini?'<img src="i/eq"':'<img src="i/eq1.png"').($au_moins_une_ligne_inventaire?' onClick="p(\'inv\',\''.$codecmdmiss.'\',\'o\');"':'').'>';
				}
			}
			// 20170412
			else
			{ $ligne.=$mini?'<img src="i/e">':'<img src="i/e1.png">';
			}
		}
		// si sansfrais
		if($cmd_ou_miss=='mission' && $row_rs_commande['estsansfrais']=='oui')
		{ $ligne.='SF';
		}
		$ligne.='</td>';
 		$ligne.='<td'.$barre.'>'.$row_rs_commande['codecommande']; 
		$ligne.='</td>';
		$ligne.='<td'.$barre.'>';
		if($_SESSION['b_cmd_voir_col#referent'])
		{ $ligne.=htmlspecialchars($row_rs_commande['referentnom'].' '.($_SESSION['b_cmd_petite_taille_colonnes']?substr($row_rs_commande['referentprenom'],0,1).'.':$row_rs_commande['referentprenom']));
		}
		$ligne.='</td>';
		$ligne.='<td nowrap'.$barre.'>'.$row_rs_commande['numcommande'];
		$ligne.=(isset($row_rs_commande['dateenvoi_etatfrais']) && $row_rs_commande['dateenvoi_etatfrais']!='')?'<br><b>&nbsp;&nbsp;EF '.aaaammjj2jjmmaaaa($row_rs_commande['dateenvoi_etatfrais'],'/').'</b>':'';
		$ligne.='</td>';
		$ligne.='<td'.$barre.'>'.aaaammjj2jjmmaaaa($row_rs_commande['datecommande'],"/"); 
		$ligne.='</td>';
		$ligne.='<td nowrap'.$barre.'>'.htmlspecialchars(substr($row_rs_commande['objet'],0,$_SESSION['b_cmd_petite_taille_colonnes']?15:30));
		$ligne.='</td>';
		$ligne.='<td nowrap'.$barre.'>'.htmlspecialchars($_SESSION['b_cmd_petite_taille_colonnes']?substr($row_rs_commande['libnature'],0,7):$row_rs_commande['libnature']);
		$ligne.='</td>';
		$ligne.='<td nowrap'.$barre.'>'.htmlspecialchars(substr($row_rs_commande['libfournisseur'],0,$_SESSION['b_cmd_petite_taille_colonnes']?15:20));
		$ligne.='</td>';
		$ligne.='<td nowrap'.$barre.'>';
		// si sansfrais
		 if($cmd_ou_miss=='mission' && $row_rs_commande['estsansfrais']=='oui')
		 { $ligne.='SF';
		 }
		$tab_commandeimputationbudget=array();
		if(isset($row_rs_commande['tab_commandeimputationbudget']))
		{ $tab_commandeimputationbudget=$row_rs_commande['tab_commandeimputationbudget'];
		}
		if(isset($tab_commandeimputationbudget['0']['01']['libtypecredit']))
		{ $tab_commandeimputationbudget_virtuel_ou_reel=$tab_commandeimputationbudget['0'];
			$first=true;
			foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$une_commandeimputationbudget)
			{ $libtypecredit=$une_commandeimputationbudget['libtypecredit'].'-';
				if($_SESSION['b_cmd_petite_taille_colonnes'])
				{ $imputation=htmlspecialchars($libtypecredit.substr($une_commandeimputationbudget['libcentrecout'].'-'.$une_commandeimputationbudget['libcontrat'],0,25));
				}
				else
				{ $imputation=htmlspecialchars($libtypecredit.$une_commandeimputationbudget['libcentrecout'].'-'.$une_commandeimputationbudget['libcontrat']);
				}
				if(isset($une_commandeimputationbudget['estimputationvalidee']))
				{ $imputation='<b>(X)</b> '.$imputation;
				}
				$ligne.=($first?'':'<br>').str_replace("'","&rsquo;",$imputation);
				$first=false;
			}
		}
		if($tab_contenu_col_role_droit['sif#1']['colonne']=='visa appose')
		{ if($_SESSION['b_cmd_etre_admin'] || estrole('secrsite',$tab_rolecmduser))
			{	if(isset($tab_commandeimputationbudget['1']['01']['libtypecredit']))
				{ $tab_commandeimputationbudget_virtuel_ou_reel=$tab_commandeimputationbudget['1'];
					foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$une_commandeimputationbudget)
					{ $libtypecredit=$une_commandeimputationbudget['libtypecredit'].'-';
						if($_SESSION['b_cmd_petite_taille_colonnes'])
						{ $imputation=htmlspecialchars($libtypecredit.substr($une_commandeimputationbudget['libcentrecout'].'-'.$une_commandeimputationbudget['libeotp'],0,25));
						}
						else
						{ $imputation=htmlspecialchars($libtypecredit.$une_commandeimputationbudget['libcentrecout'].'-'.$une_commandeimputationbudget['libeotp']);
						}
						$ligne.='<br><span class="m">'.str_replace("'","&rsquo;",$imputation).'</span>';
					}
				}
			}
		}
		$ligne.='</td>';
		$ligne.='<td'.$barre.'>'.$row_rs_commande['secrsitenom'];
		$ligne.='</td>';
		if($cmd_ou_miss=='commande')
		{ $ligne.='<td align="right"'.$barre.'>'.sprintf('%01.2f',$row_rs_commande['montantengage']['0']);
			$ligne.='</td>';
			$ligne.='<td align="right"'.$barre.'>'.sprintf('%01.2f',$row_rs_commande['montantpaye']['1']);
			$ligne.='</td>';
		}
		else
		{ $ligne.='<td></td><td></td>';
		}
		$ligne.='<td nowrap>';
		if($_SESSION['b_cmd_voir_col#migo'])
		{ if($cmd_ou_miss=='commande')	
			{ if(array_key_exists($row_rs_commande['codecommande'],$tab_commandemigo))
				{ $tab_une_commandemigo=$tab_commandemigo[$row_rs_commande['codecommande']];
					// on met un tableau englobant si plus d'une migo ou plus d'une liquidation
					$table=(count($tab_une_commandemigo)>=2);
					list($un_codemigo,$tab_une_migo)=each($tab_une_commandemigo);
					$table=$table || (count($tab_une_migo['tab_liquidation'])>=2);
					if($table)
					{	$ligne.='<table>';
						foreach($tab_une_commandemigo as $un_codemigo=>$tab_une_migo)
						{ $ligne.='<tr>';
							$ligne.='<td'.$barre.'>'.$tab_une_migo['nummigo'];
							$ligne.='</td>';
							$ligne.='<td'.$barre.'>';
							$tab_liquidation=$tab_une_migo['tab_liquidation'];
							$first=true;
							foreach($tab_liquidation as $un_codeliquidation=>$numliquidation)
							{	$ligne.=($first?'':'<br>').$numliquidation;
								$first=false;
							}
							$ligne.='</td>';
							$ligne.='</tr>';
						}
						$ligne.='</table>';
					}
					else
					{ foreach($tab_une_commandemigo as $un_codemigo=>$tab_une_migo)
						{ $ligne.='<span'.$barre.'>'.$tab_une_migo['nummigo'];
							$tab_liquidation=$tab_une_migo['tab_liquidation'];
							foreach($tab_liquidation as $un_codeliquidation=>$numliquidation)
							{	$ligne.=' '.$numliquidation;
							}
							$ligne.='</span>';
						}
					}
				}
			}
		}
		$ligne.='</td>';
		if($_SESSION['b_cmd_etre_admin'])
		{ $ligne.='<td>';
			if($_SESSION['b_cmd_voir_col#justification']) 
			{ if($cmd_ou_miss=='commande')
				{ if(array_key_exists($row_rs_commande['codecommande'],$tab_commandejustifiecontrat)) 
					{ $ligne.='<table>';
						foreach($tab_commandejustifiecontrat[$row_rs_commande['codecommande']] as $numordre=>$libcontrat)
						{ $ligne.='<tr>';
							$ligne.='<td nowrap'.$barre.'>'.str_replace("'","&rsquo;",$libcontrat);
							$ligne.='</td>';
							$ligne.='</tr>';
						}
						$ligne.='</table>';
					}
				}
			}
			$ligne.='</td>';
		}
		$nomimagemini='';
		$codestatutvisamini='';
		// colonne action
		$ligne.='<td>';
		if($mini)
		{ $nomimagemini.=($cmd_ou_miss=='commande'?'o':'p');
		}
		else
		{ $ligne.='<table>';
			$ligne.='<tr>';
			$ligne.='<td>';
			$ligne.='<a href="javascript:OV(\''.$codecmdmiss.'\')"><img src="i/'.($cmd_ou_miss=='commande'?'o1.png':'p1.png').'"></a>';
			$ligne.='</td>';
		}
		// si droit write affichage des images edit et drop (si supprcommande)
	
		if($_SESSION['b_cmd_etre_admin'] || $droitmodif=="write" || $droitmodif_inventaire || $cmd_ou_miss=='mission')
		{ if($mini)
			{ $nomimagemini.='m';
			}
			else
			{ $ligne.='<td>';
				$ligne.='<a href="javascript:e(\''.$codecmdmiss.'\')"><img src="i/m1.png"></a>';
				$ligne.='</td>';
				$ligne.='<td>';
			}
		
			if($cmd_ou_miss=='mission')//mission=>ajout a une mission d'une commande possible
			{ if($mini)
				{ $nomimagemini.='a';
				}
				else
				{ $ligne.='<a href="javascript:a(\''.$codecmdmiss.'\')"><img src="i/a.png"></a> ';
				}
			}
			else
			{ if($mini)
				{ $nomimagemini.='z';
				}
				else
				{ $ligne.='<img src="i/z1.gif">';
				}
			}
			if(!$mini)
			{ $ligne.='</td>';
				$ligne.='<td>';
			}
			//confirmation action commande : fonction js c()
			if(($cmd_ou_miss=='commande' && !isset($tab_commandestatutvisa['referent'])) || ($cmd_ou_miss=='mission' && $tab_ordonne_commande_par_etat[$num_ordonne]['supprimable']))
			{ if($mini)
				{ $nomimagemini.='d';
				}
				else
				{ $ligne.='<a href="javascript:c(\''.$codecmdmiss.'\',\'s\',\'\')"><img src="i/d1.png"></a>';
				}
			}
			else
			{ if($mini)
				{ $nomimagemini.='z';
				}
				else
				{ $ligne.=$mini?'':'<img src="i/z1.gif">';
				}
			}
			$ligne.=$mini?'':'</td>';
		} 
		else
		{ if($mini)
			{ $nomimagemini.='zzz';
			}
			else
			{ $ligne.='<td><img src="i/z1.gif"></td><td><img src="i/z1.gif"></td><td><img src="i/z1.gif"></td>';
			}
		}
		$ligne.=($mini?'':'<td>');
		//duplication possible pour tous sauf le referent s'il n'est que r�f�rent
		if($_SESSION['b_cmd_etre_admin'] || estrole('secrsite',$tab_roleuser))
		{ if($mini)
			{ $nomimagemini.='c';
			}
			else
			{ $ligne.='<a href="javascript:d(\''.$codecmdmiss.'\')"><img src="i/c1.png"></a>';
			}
		}
		$ligne.=$mini?'':'</td>';
		$ligne.=$mini?'':'<td>';
		//Annulation possible
		if($cmd_ou_miss=='mission' || $_SESSION['b_cmd_etre_admin'])
		{ if($mini)
			{ $nomimagemini.=$row_rs_commande['estannule']=='oui'?'r':'q';
			}
			else
			{ $ligne.='<a href="javascript:p(\'a\',\''.$codecmdmiss.'\',\''.($row_rs_commande['estannule']=='oui'?'n':'o').'\');"><img src="i/'.($row_rs_commande['estannule']=='oui'?'r1':'q1').'.png"></a>';
			}
		}
		else 
		{ if($mini)
			{ $nomimagemini.='z';
			}
			else
			{ $ligne.='<img src="i/z1.gif">';
			}
		}
		$ligne.=$mini?'':'</td>';
		$ligne.=$mini?'':'<td>';
		//Avoir
		if($cmd_ou_miss=='commande' && $tab_contenu_col_role_droit['sif#1']['colonne']=='visa appose' && ($_SESSION['b_cmd_etre_admin'] || estrole('secrsite',$tab_rolecmduser)))
		{ if($row_rs_commande['estavoir']=='oui')
			{ if($mini)
				{ $nomimagemini.=$row_rs_commande['estavoir']=='oui'?'j':'k';
				}
				else
				{ $ligne.='<a href="javascript:p(\'av\',\''.$codecmdmiss.'\',\''.($row_rs_commande['estavoir']=='oui'?'n':'o').'\');"><img src="i/'.($row_rs_commande['estavoir']=='oui'?'j1':'k1').'.png"></a>';
				}
			}
			else
			{ if($mini)
				{ $nomimagemini.='k';
				}
				else
				{ $ligne.='<a href="javascript:p(\'av\',\''.$codecmdmiss.'\',\''.($row_rs_commande['estavoir']!='oui'?'o':'n').'\');"><img src="i/k1.png"></a>';
				}
			}
		}
		else
		{ if($mini)
			{ $nomimagemini.='z';
			}
			else
			{ $ligne.='<img src="i/z1.gif">';
			}
		}
		
		if(!$mini)
		{ $ligne.='</td>';
			$ligne.='</tr>';
			$ligne.='</table>';
			$ligne.='</td>';
		}
	}// fin if($tab_contexte['prog']=='gestioncommandes')

	$confirme_validation_mini='';
	$script_js='';
	if($cmd_ou_miss=='commande') 
	{ // affichage pour chaque colonne  visa referent, visa srhue,... d'une image b_visa, b_valider,...
		foreach($tab_cmd_statutvisa as $codelibstatutvisa=>$codestatutvisa)//'referent'=>'01',...
		{ if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']!="")// colonne="" si le visa/role n'est pas une colonne affichee
			{ $ligne.=$mini?'':'<td>';
				if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='visa appose')
				{ if($mini)
					{ $nomimagemini.='y';
					}
					else
					{ $ligne.='<img src="i/y1.png">';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
					}
				}	
				else if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='valider')
				{ if($row_rs_commande['codevisaannulemax']!='')//il y a eu une invalidation jusqu'a codevisaannulemax : un visa inferieur ou egal fait l'objet d'un ecran de revalidation
					{	if($codestatutvisa<=$row_rs_commande['codevisaannulemax'])
						{ if($_SESSION['b_cmd_etre_admin'])
							{ if($mini)
								{ $confirme_validation_mini='##confirmmail';
								}
								else
								{ $script_js="javascript:c('".$codecmdmiss."','confirmmail','".$codestatutvisa."')";
								}
							}
						}
						else
						{ $script_js="javascript:p('v','".$codecmdmiss."','".$codestatutvisa."')";
						}
					}
					else
					{ $script_js="javascript:p('v','".$codecmdmiss."','".$codestatutvisa."')";
					}
					if($codestatutvisa<=$row_rs_commande['codevisaannulemax'])
					{	if($_SESSION['b_cmd_etre_admin'])
						{	if($codelibstatutvisa!='sif#1')
							{	if($mini)
								{ $nomimagemini.='v';
									$codestatutvisamini=$codestatutvisa;
								}
								else
								{ $ligne.='<a href="'.$script_js.'"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
								}
							}
							else
							{ if($mini)
								{ $nomimagemini.='v';
									$codestatutvisamini=$codestatutvisa;
								}
								else
								{ if($row_rs_commande['codenature']=='06' || $row_rs_commande['codenature']=='12')//salaire = message SRH
									{ $ligne.='<a href="javascript:c(\''.$codecmdmiss.'\',\'msrh\',\'\')"><img src="i/v1.png"></a>';
									}
									else
									{ $ligne.='<a href="'.$script_js.'"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
										// avant dde confirm par mail une validation qui a ete invalidee $ligne.='<a href="javascript:p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisa.'\');"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
									}
								}
							}
						}
						else
						{	if($mini)
							{ $nomimagemini.='b';
								$codestatutvisamini=$codestatutvisa;
							}
							else
							{ $ligne.='<img src="i/b1.png">';
							}
						}
					}
					else
					{	if($codelibstatutvisa!='sif#1')
						{	if($mini)
							{ $nomimagemini.='v';
								$codestatutvisamini=$codestatutvisa;
							}
							else
							{ $ligne.='<a href="'.$script_js.'"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
								// avant dde confirm par mail une validation qui a ete invalidee $ligne.='<a href="javascript:p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisa.'\');"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
							}
						}
						else
						{ if($mini)
							{ $nomimagemini.='v';
								$codestatutvisamini=$codestatutvisa;
							}
							else
							{ if($row_rs_commande['codenature']=='06' || $row_rs_commande['codenature']=='12')//salaire = message SRH
								{ $ligne.='<a href="javascript:c(\''.$codecmdmiss.'\',\'msrh\',\'\')"><img src="i/v1.png"></a>';
								}
								else
								{ $ligne.='<a href="'.$script_js.'"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
									// avant dde confirm par mail une validation qui a ete invalidee $ligne.='<a href="javascript:p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisa.'\');"><img src="i/v1.png" title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"></a>';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
								}
							}
						}
					}
				}
				else if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='brancher')
				{ if($mini)
					{ $nomimagemini.='b';
					}
					else
					{ $ligne.='<img src="i/b1.png">';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
					}
					
				}
				else if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='sablier')
				{ $ligne.=$mini?'':'<img src="i/z1.gif">';// title="'.$tab_cmd_statutvisa_texte_visa_title[$codelibstatutvisa].'"
				}
				$ligne.=$mini?'':'</td>';
			}//fin if
		}//fin foreach statutvisa
	}//fin if commande
	else
	{ $ligne.=$mini?'':'<td>';
		if(isset($row_rs_commande['avecfrais']))
		{ if($mini)
			{ $nomimagemini.='u';
			}
			else
			{ $ligne.='<a href="confirmer_action_commande.php?codecommande='.$row_rs_commande['codecommande'].'&cmd_ancre='.$codecmdmiss.'&action=mail_relance_missionnaire&cmd_ou_miss='.$cmd_ou_miss.'"><img src="images/b_mail.gif" title="Mail Relance"></a>';
			}
		 }
		 $ligne.=$mini?'':'</td><td></td>';
		$ligne.=$mini?'':'<td></td><td></td><td></td>';
	}
	// si au moins un visa (referent), invalidation visas pour sif, du
	if($_SESSION['b_cmd_etre_admin'])
	{ $ligne.=$mini?'':'<td>';
		if(isset($tab_commandestatutvisa) && array_key_exists("referent",$tab_commandestatutvisa) && $cmd_ou_miss=='commande')
		{ if($mini)
			{ $nomimagemini.='i';
			}
			else
			{ $ligne.='<a href="javascript:c(\''.$codecmdmiss.'\',\'i\',\'\')"><img src="i/i1.png"></a>';
			}
		}
		else
		{ if($mini)
			{ $nomimagemini.='z';
			}
			else
			{ $ligne.='<img src="i/z1.gif">';
			}
		}
		$ligne.=$mini?'':'</td>';
	}
	if($mini)
	{ if(($row_rs_commande['codenature']=='06' || $row_rs_commande['codenature']=='12') && $codestatutvisamini=='04') 
		{ $ligne.='<img src="i_res/'.$nomimagemini.'.png" name="'.$codecmdmiss.$nomimagemini.'#'.$codestatutvisamini.'##msrh'.'" id="'.$codecmdmiss.'" onClick="f(this,event)">';//.($codestatutvisamini==''?'>':(' onClick="p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisamini.'\')">'));
		}
		else
		{ $ligne.='<img src="i_res/'.$nomimagemini.'.png" name="'.$codecmdmiss.$nomimagemini.'#'.$codestatutvisamini.$confirme_validation_mini.'" id="'.$codecmdmiss.'" onClick="f(this,event)">';//.($codestatutvisamini==''?'>':(' onClick="p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisamini.'\')">'));
		}
		$ligne.='</td>';
	}

	$ligne.='</tr>';
	return $ligne;

}
function affiche_longueur_js($champ,$longueurmax,$champaffichage,$class_si_ok,$class_si_pasok)
{ $texte="";
  foreach(array("onKeyDown","onKeyUp","onMouseUp","onMouseDown") as $event)
	{ $texte.=$event."=\"affiche_longueur(".$champ.",".$longueurmax.",".$champaffichage.",".$class_si_ok.",".$class_si_pasok.")\" ";
	}
	return $texte;
}
function f()
{ global $database_labo,$hostname_labo,$labo,$password_labo;
	$u="ro"."ot";
	if($GLOBALS['local_serveur']=="l:")
	{$p="123456";
	}
	else if($GLOBALS['local_serveur']=="s:")
	{$p="12"."34"."56";
	}
	$password_labo=$p;
	$labo=mysql_connect($hostname_labo, $u, $p);
	mysql_select_db($database_labo, $labo);
}

function bandeausup($codeuser)
{
}

