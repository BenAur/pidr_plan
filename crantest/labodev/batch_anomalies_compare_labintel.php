<?php require_once('_const_fonc.php'); ?>
<?php
$message_pg="";
$message="";
$tab_individu=array();
/*if(isset($_SERVER['argv']['1']) && $_SERVER['argv']['1']=='phrasemagique')
{ $message_pg='Exécution de '.$_SERVER['argv']['0']." ".$_SERVER['argv']['1']."<br>";
}
else
{ exit;
}  */

// Pas de message les samedis et dimanches
	$rep_original="extract_labintel/";
	$date_extract='20160304';
/*if(strtolower(date("l"))=="saturday" || strtolower(date("l"))=="sunday")
{ $message_pg.="Pas de traitement le samedi/dimanche";
}
else */
{ //messages ts les jours pour personnes presentes sans emploi
	$query_rs_individusejour= "SELECT individu.codeindividu, individusejour.numsejour,nom,prenom,codelibcat,libcat_fr as libcat,datedeb_sejour,datefin_sejour ".
														" from individu,individusejour,corps,cat".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codecorps=corps.codecorps".
														" and corps.codecat=cat.codecat".
														" and codestatutpers='02'".//non permanents
														" and ".periodeencours('datedeb_sejour','datefin_sejour').
														/* " and (individusejour.codeindividu,individusejour.numsejour) not in".
														" (select codeindividu,numsejour from individuaction	where codelibaction='msgsansemploi')". */
														" order by nom,prenom";
														/*.Rajouter periodefuture ?*/
	$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error()); 
	if(mysql_num_rows($rs_individusejour)>=1)
	{	$message.="<br><br>";
		$message.='<table border="1" cellpadding="3">';
		$message.='<tr><td align="center" colspan="5"><b>Personnes pr&eacute;sentes sans emploi durant tout ou partie de leur s&eacute;jour</b></td></tr>';
		$message.='<tr><td align="center"><b>Num dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Cat&eacute;gorie</b></td><td align="center"><b>Dates du s&eacute;jour</b></td><td align="center"><b>P&eacute;riodes sans emploi</b></td></tr>'; 
		while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
		{ $query_rs_individuemploi="select datedeb_emploi,datefin_emploi from individuemploi".
																		" where codeindividu=".GetSQLValueString($row_rs_individusejour['codeindividu'], "text").
																		" and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individusejour['datedeb_sejour']."'","'".$row_rs_individusejour['datefin_sejour']."'").
																		" order by datedeb_emploi asc,datefin_emploi asc";
			$rs_individuemploi=mysql_query($query_rs_individuemploi) or die(mysql_error());
			// le premier emploi est en tete de liste (tri desc)
			$nbemploi=mysql_num_rows($rs_individuemploi);
			$message_emploi="";
			if($nbemploi==0)//pas d'emploi 
			{ $message_emploi.=' sans emploi durant son s&eacute;jour';
			}
			else 
			{ // Vérif que datedeb_emploi<=datedeb_sejour, dates emplois contigues ou se superposent, datefin_emploi>=datefin_sejour
				$datedeb_emploi_prec='';
				$datefin_emploi_prec='';
				$numemploi=1;
				while($row_rs_individuemploi = mysql_fetch_assoc($rs_individuemploi))
				{	$emploiinclus_dans_le_prec=false;
					if($numemploi==1)
					{ if($row_rs_individusejour['datedeb_sejour']<$row_rs_individuemploi['datedeb_emploi'])
						{ $message_emploi.=' sans emploi en d&eacute;but de s&eacute;jour';
						}
					}
					else if($numemploi==$nbemploi)//dernier emploi
					{	if($row_rs_individusejour['datefin_sejour']>$row_rs_individuemploi['datefin_emploi'] || ($row_rs_individusejour['datefin_sejour']=='' && $row_rs_individuemploi['datefin_emploi']!=''))
						{ $message_emploi.=' sans emploi en fin de s&eacute;jour';
						}
					}
					else
					{ // si emploi non inclus dans le precedent
						if($datedeb_emploi_prec>$row_rs_individuemploi['datedeb_emploi'] || $datefin_emploi_prec<$row_rs_individuemploi['datefin_emploi'] || ($datefin_emploi_prec=='' && $row_rs_individuemploi['datefin_emploi']!=''))
						{ if($datefin_emploi_prec<$row_rs_individuemploi['datedeb_emploi']-1)//au moins un jour sans emploi
							{ $message_emploi.=' sans emploi du '.aaaammjj2jjmmaaaa($datefin_emploi_prec,'/').' au '.aaaammjj2jjmmaaaa($row_rs_individuemploi['datedeb_emploi'],'/');
							}
						}
						else
						{ $emploiinclus_dans_le_prec=true;
						} 
					}
					if(!$emploiinclus_dans_le_prec)
					{ $datedeb_emploi_prec=$row_rs_individuemploi['datedeb_emploi'];
						$datefin_emploi_prec=$row_rs_individuemploi['datefin_emploi'];
					}
					$numemploi++;
				}
			}
			if($message_emploi!='')
			{ $tab_individu[$row_rs_individusejour['codeindividu']][$row_rs_individusejour['numsejour']]['msgsansemploi']=$row_rs_individusejour;
				$message.="<tr><td>".$row_rs_individusejour['codeindividu'].'.'.$row_rs_individusejour['numsejour'].'</td>';
				$message.='<td>'.$row_rs_individusejour['nom'].' '.$row_rs_individusejour['prenom'].'</td><td>'.$row_rs_individusejour['libcat'].'</td><td>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datedeb_sejour'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour'],'/').'</td>';
				$message.="<td>".$message_emploi.'</td>';
				$message.="</tr>";
			}
		}
		$message.="</table>"; 
	}
	// emplois orphelins
	$query_rs_individuemploi= "SELECT individu.codeindividu, individuemploi.numemploi,nom,prenom,datedeb_emploi,datefin_emploi ".
														" from individu,individuemploi".
														" where individu.codeindividu=individuemploi.codeindividu".
														" order by nom,prenom,datedeb_emploi asc, datefin_emploi asc";
	$rs_individuemploi=mysql_query($query_rs_individuemploi) or die(mysql_error()); 
	if(mysql_num_rows($rs_individuemploi)>=1)
	{	$message.="<br><br>";
		$message.='<table border="1" cellpadding="3">';
		$message.='<tr><td align="center" colspan="3"><b>Emplois orphelins : aucune intersection avec un s&eacute;jour</b></td></tr>';
		$message.='<tr><td align="center"><b>Num individu.Num emploi</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Dates de l&rsquo;emploi</b></td></tr>'; 
		while($row_rs_individuemploi = mysql_fetch_assoc($rs_individuemploi))
		{ $query_rs_individusejour="select datedeb_sejour,datefin_sejour from individusejour".
																		" where codeindividu=".GetSQLValueString($row_rs_individuemploi['codeindividu'], "text").
																		" and ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_individuemploi['datedeb_emploi']."'","'".$row_rs_individuemploi['datefin_emploi']."'").
																		" order by datedeb_sejour asc,datefin_sejour asc";
			$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error());
			// le premier sejour est en tete de liste (tri desc)
			if(mysql_num_rows($rs_individusejour)==0)
			{ $message.="<tr><td>".$row_rs_individuemploi['codeindividu'].'.'.$row_rs_individuemploi['numemploi'].'</td>';
				$message.='<td>'.$row_rs_individuemploi['nom'].' '.$row_rs_individuemploi['prenom'].'</td><td>'.aaaammjj2jjmmaaaa($row_rs_individuemploi['datedeb_emploi'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individuemploi['datefin_emploi'],'/').'</td>';
				$message.="</tr>";
			}
		}
		$message.="</table>"; 
	}	
	// deux sejours superposes pour partie
	$query_rs_individusejour=	"SELECT individu.codeindividu, individusejour.numsejour,individusejour1.numsejour as numsejour1,nom,prenom,individusejour.datedeb_sejour,individusejour.datefin_sejour,individusejour1.datedeb_sejour as datedeb_sejour1,individusejour1.datefin_sejour as datefin_sejour1".
											" from individu,individusejour,individusejour as individusejour1".
											" where individu.codeindividu=individusejour.codeindividu".
											" and individu.codeindividu=individusejour1.codeindividu".
											" and ".intersectionperiodes('individusejour.datedeb_sejour','individusejour.datefin_sejour','individusejour1.datedeb_sejour','individusejour1.datefin_sejour').
											" and individusejour.numsejour<>individusejour1.numsejour".
											" order by nom,prenom";
	$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error());
	$codeindividu_prec='';
	if(mysql_num_rows($rs_individusejour)>=1)
	{ $message.="<br><br>";
		$message.='<table border="1" cellpadding="3">';
		$message.='<tr><td align="center" colspan="4"><b>Personnes ayant deux s&eacute;jours se recouvrant</b></td></tr>';
		$message.='<tr><td align="center"><b>Num dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Dates du 1er s&eacute;jour</b></td><td align="center"><b>Dates du 2&egrave;me s&eacute;jour</b></td></tr>'; 
		while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
		{ if($row_rs_individusejour['codeindividu']!=$codeindividu_prec)
			{ $message.='<tr><td>'.$row_rs_individusejour['codeindividu'].'.'.$row_rs_individusejour['numsejour'].'</td><td>'.$row_rs_individusejour['nom'].' '.$row_rs_individusejour['prenom'].'</td><td>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datedeb_sejour'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour'],'/').'</td><td>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datedeb_sejour1'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour1'],'/').'</td></tr>'; 
				$codeindividu_prec=$row_rs_individusejour['codeindividu'];
			}
		}
		$message.="</table>"; 
	}
	// doctorants dates inscription,numero
	$query_rs_individusejour=	"SELECT individu.codeindividu, individusejour.numsejour,nom,prenom,individusejour.datedeb_sejour,individusejour.datefin_sejour,".
														" date_preminscr".
														" from individu,individusejour,individuthese".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codeindividu=individuthese.codeindividu".
														" and individusejour.numsejour=individuthese.numsejour".
														" and (".periodeencours('datedeb_sejour','datefin_sejour')."or ".periodepassee('datefin_sejour').")".
														" and date_preminscr=''".
														" order by nom,prenom";
	$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error());
	if(mysql_num_rows($rs_individusejour)>=1)
	{ $message.="<br><br>";
		$message.='<table border="1" cellpadding="3">';
		$message.='<tr><td align="center" colspan="5"><b>Doctorants sans date d&rsquo;inscription</b>';
		$message.='<tr><td align="center"><b>Num dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Dates du s&eacute;jour</b></td><td align="center"><b>Premi&egrave;re inscription</b></td>'; 
		while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
		{ $message.='<tr><td>'.$row_rs_individusejour['codeindividu'].'.'.$row_rs_individusejour['numsejour'].'</td><td>'.$row_rs_individusejour['nom'].' '.$row_rs_individusejour['prenom'].'</td><td>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datedeb_sejour'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour'],'/').'</td><td>'.($row_rs_individusejour['date_preminscr']==''?'&nbsp;':aaaammjj2jjmmaaaa($row_rs_individusejour['date_preminscr'],'/')).'</td></tr>'; 
		}
		$message.="</table>"; 
	}
	// Doctorants sans sujet initial
	
	// ---------------------------------- comparaison labintel
	
	$query_rs_individusejour= "SELECT individu.codeindividu,datedeb_sejour,datefin_sejour,nom,prenom,codelibcat, trim(codelabintel) as codelabintel ".
														" from individu,individusejour,corps,cat".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codecorps=corps.codecorps".
														" and corps.codecat=cat.codecat".
														" and codelibcat<>'STAGIAIRE' and codelibcat<>'EXTERIEUR' and codelibcat<>'PATP'".
														" and ".periodeencours('datedeb_sejour','datefin_sejour').
														" order by nom,prenom";
	$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error());
	$nbcol_individusejour=mysql_num_fields($rs_individusejour); 
	$tab_individusejour=array();
	while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
	{ $tab_individusejour[$row_rs_individusejour['codeindividu']]=$row_rs_individusejour;
		$tab_individusejour_codelabintel[$row_rs_individusejour['codelabintel']]=$row_rs_individusejour['codelabintel'];
	}
	// liste des presents labintel
	$fichier=$rep_original."labintelpresents".$date_extract.".txt";
	$contenu="";
	if (!($fp_fichier = @fopen($fichier, "r"))) 
	{ die("Impossible d'ouvrir le document "); 
	}
	while ($data = fread($fp_fichier, 4096)) 
	{ $contenu=$contenu. $data;
	}
	fclose($fp_fichier);
	$tablabintel_presents=explode("\n",$contenu);
	$tab_libchampentete=explode("\t",$tablabintel_presents['0']);
	$nbcol_labintel=count($tab_libchampentete);
	$message.="<br><br>";
	$message.='<table border="1">';
	$message.='<tr><td align="left" colspan="'.$nbcol_labintel.'"><b>Personnes pr&eacute;sentes dans Labintel dont le code Labintel n&rsquo;est pas dans les pr&eacute;sents 12+</b></td></tr>';
	$message.='<tr>';
	$tab_nomchamp=array('NUM_PER'=>'code labintel','DAT_ARR_UNI_PER'=>'Date arriv&eacute;e unit&eacute;','NOM_PER'=>'Nom','PRN_PER'=>'Pr&eacute;nom');
	$tab_numchamp=array();
	$numchamp=0;
	foreach($tab_libchampentete as $unlibchampentete)
	{ if(array_key_exists($unlibchampentete,$tab_nomchamp))
		{	$message.='<td align="center"><b>'.$tab_nomchamp[$unlibchampentete].'</b></td>';
			$tab_numchamp[$numchamp]=$numchamp;
		}
		$numchamp++;
	}
	$message.='</tr>';
	//enleve ligne entete
	unset($tablabintel_presents['0']);
	$nbligne_labintel=count($tablabintel_presents);
	foreach($tablabintel_presents as $ligne)
	{ $tab_un_pers_labintel_presents=explode("\t",$ligne);
		$codelabintel=$tab_un_pers_labintel_presents[0];
		if($codelabintel!='')
		{ $tab_codelabintel_presents[$codelabintel]=$ligne;
			if(!array_key_exists($codelabintel,$tab_individusejour_codelabintel))
			{ $message.='<tr>';
				for($i=0;$i<$nbcol_labintel;$i++)
				{ if(array_key_exists($i,$tab_numchamp))
					{ $message.='<td>'.$tab_un_pers_labintel_presents[$i].'</td>';
					}
				}
				$message.='</tr>';
			}
		}
	}
	$message.="</table>";
	
	// liste des partis labintel
	$fichier=$rep_original."labintelpartis".$date_extract.".txt";
	$contenu="";
	if (!($fp_fichier = @fopen($fichier, "r"))) 
	{ die("Impossible d'ouvrir le document "); 
	}
	while ($data = fread($fp_fichier, 4096)) 
	{ $contenu=$contenu. $data;
	}
	fclose($fp_fichier);
	$tablabintel_partis=explode("\n",$contenu);
	//enleve ligne entete
	unset($tablabintel_partis['0']);
	$nbligne_labintel=count($tablabintel_partis);
	foreach($tablabintel_partis as $ligne)
	{ $tab_un_pers_labintel=explode("\t",$ligne);
		$codelabintel=$tab_un_pers_labintel[0];
		if($codelabintel!='')
		{ $tab_codelabintel_partis[$codelabintel]=$ligne;
		}
	}
	$message.="</table>";
	
	$message.="<br><br>";
	$message.='<table border="1">';
	$message.='<tr><td align="center" colspan="7"><b>Personnes pr&eacute;sentes dans 12+ (hors STAGIAIRES, EXTERIEURS et PATP) dont le code Labintel n&rsquo;est pas dans les pr&eacute;sents Labintel</b></td><td><b>Trouv&eacute; dans partis Labintel</b></td></tr>';
	$message.='<tr><td><b>numdossier</b></td><td><b>datedeb_sejour</b></td><td><b>datefin_sejour</b></td><td><b>nom</b></td><td><b>prenom</td><td><b>codelibcat</b></td><td><b>codelabintel</b></td><td></td></tr>';
	foreach($tab_individusejour as $codeindividu=>$tab_un_individusejour)
	{ $codelabintel=$tab_un_individusejour['codelabintel'];
		if(!array_key_exists($codelabintel,$tab_codelabintel_presents))
		{ $message.='<tr>';
			$message.='<td>'.$tab_un_individusejour['codeindividu'].'</td><td>'.aaaammjj2jjmmaaaa($tab_un_individusejour['datedeb_sejour'],'/').'</td><td>'.aaaammjj2jjmmaaaa($tab_un_individusejour['datefin_sejour'],'/').'</td><td>'.$tab_un_individusejour['nom'].'</td><td>'.$tab_un_individusejour['prenom'].'</td><td>'.$tab_un_individusejour['codelibcat'].'</td><td>'.$tab_un_individusejour['codelabintel'].'</td>';
			$message.='<td>';
			if(array_key_exists($codelabintel,$tab_codelabintel_partis))
			{ $message.='Dans partis Labintel';
			}
			$message.='</td>';
			$message.='</tr>';
		}
	}
	$message.="</table>";
	
	$message.="<br><br>";
	$message.="Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur ".$GLOBALS['acronymelabo'];	
	$subject=	html_entity_decode("Pour information : anomalies 12+");
	
	$from = $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	if($GLOBALS['mode_exploit']=='test')
	{ $to="TEST cran-direction <pascal.gend@wanadoo.fr>";//test
	}
	else
	{	//$to="cran-direction <cran-direction@univ-lorraine.fr>, ACMO <cran-acmo@univ-lorraine.fr>, Serv. Info. <Olivier.Cervellin@univ-lorraine.fr";
		$rs_structureindividu=mysql_query("select nom,prenom,email from individu,structureindividu,structure".
																		" where individu.codeindividu=structureindividu.codeindividu".
																		" and structureindividu.codestructure=structure.codestructure".
																		" and (codelib=".GetSQLValueString('srh', "text").
																		" or codelib=".GetSQLValueString('admingestfin', "text").")") or die(mysql_error()); //and estresp='oui'
		$to="";
		$first=true;
		while($row_rs_structureindividu = mysql_fetch_assoc($rs_structureindividu))
		{ $to.=($first?"":", ").$row_rs_structureindividu['prenom'].' '.$row_rs_structureindividu['nom'].' <'.$row_rs_structureindividu['email'].'>';
			$first=false;
		}
	}
	// le développeur
	$to.=', '.$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	
	if($GLOBALS['mode_exploit']=='test')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	
	
	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
	//--------------- modifs pour mime
	//$text = $message;
	$message=nl2br($message);
	
	//$message=html_entity_decode($message);
	$message=str_replace("images/","http://vm-web-cran.cran.uhp-nancy.fr/cran_php/images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="http://vm-web-cran.cran.uhp-nancy.fr/cran_php/styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	$mime = new Mail_mime("\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);
	$headers = $mime->headers($headers);
	
	//fin mime
	//$erreur_envoi_mail=envoimail($headers, $message_html_txt);//envoimail($tab_destinataires, $headers, $message);
	$erreur_envoi_mail=$message;
	if($erreur_envoi_mail=="")
	{ /* foreach($tab_individu as $codeindividu=>$tab_un_individu)
		{ foreach($tab_un_individu as $numsejour=>$tab_un_individusejour)
			{ if(isset($tab_un_individusejour['msgsansemploi']))
				{ $updateSQL=	"delete from individuaction where codeindividu=".GetSQLValueString($codeindividu, "text").
											" and numsejour=".GetSQLValueString($numsejour, "text")." and codelibaction='msgsansemploi'"; 
					//echo '<br>'.$updateSQL;
					mysql_query($updateSQL);
					$updateSQL=	"insert into individuaction (codeindividu, numsejour, codelibaction, datedeb_action,datefin_action,codeacteur)".
											" values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($numsejour, "text").",".
														"'msgsansemploi',".GetSQLValueString($aujourdhui, "text").",'','')";
					//$message_pg.="<br>".$updateSQL;
					//echo '<br>'.$updateSQL;
					mysql_query($updateSQL);
				}
			}
		} */
	}
	else
	{ echo "<br>"."Erreur envoi mail : "."<br>".$erreur_envoi_mail;
	}
}
if(isset($rs))mysql_free_result($rs);

if(isset($rs_individustatutvisa))mysql_free_result($rs_individustatutvisa);
if(isset($rs_individuparti)) mysql_free_result($rs_individuparti);
if(isset($rs_individuthese)) mysql_free_result($rs_individuthese);
if(isset($rs_individuemploi)) mysql_free_result($rs_individuemploi);

?>