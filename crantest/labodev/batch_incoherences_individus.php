<?php require_once('_const_fonc.php'); ?>
<?php
//Verif. user autorise a lancer le batch
$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
$tab_destinataires=array();
$tab_individu=array();
$tab_individuparti=array();
$envoyer_message=false;
$message_pg="";
$message="";
/* if(isset($_SERVER['argv']['1']) && $_SERVER['argv']['1']=='phrasemagique')
{ $message_pg='Exécution de '.$_SERVER['argv']['0']." ".$_SERVER['argv']['1']."\n";
}
else
{ exit;
} */ 

// Pas de message les samedis et dimanches
/**/
if(strtolower(date("l"))=="saturday" || strtolower(date("l"))=="sunday")//
{ $message_pg.="Pas de traitement le samedi/dimanche";
}
else 
{	//messages ts les jours pour debut sejour et depart pour les individus qui n'ont pas codelibaction='msgsejourtermine'
	// Séjours débutés
	$query_rs="select codeindividu, nom, prenom, email from individu where codeindividu<>''";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs = mysql_fetch_assoc($rs))
	{ $tab_individu[$row_rs['codeindividu']]=$row_rs;
	}
	
	$query_rs_individuarrive= "SELECT individu.codeindividu, individusejour.numsejour,nom,prenom,codelibcat,libcat_fr as libcat,datedeb_sejour,datefin_sejour, ".
														" codereferent,codegesttheme".
														" from individu,individusejour,corps,cat".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codecorps=corps.codecorps".
														" and corps.codecat=cat.codecat".
														" and datedeb_sejour=".GetSQLValueString(date('Y/m/d'), "text").
														" order by datefin_sejour desc,nom,prenom";
	$rs_individuarrive=mysql_query($query_rs_individuarrive) or die(mysql_error()); 
	$message.='<p align="center"><b>Etat r&eacute;capitulatif journalier des s&eacute;jours d&eacute;but&eacute;s, termin&eacute;s et des anomalies (emplois, double s&eacute;jour)</b></p>';
	if(mysql_num_rows($rs_individuarrive)>=1)
	{	$envoyer_message=true;
		$message.='<table border="1" align="center"><tr><td colspan="6" align="center"><b>S&eacute;jour(s) d&eacute;but&eacute;(s)</b></td></tr>';
		$message.='<tr><td align="center">N&deg; dossier</td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Cat&eacute;gorie</b></td><td align="center"><b>Dates du s&eacute;jour</b></td><td align="center"><b>R&eacute;f&eacute;rent</b></td><td align="center"><b>Suivi par</b></td></tr>';
		while($row_rs_individuarrive = mysql_fetch_assoc($rs_individuarrive))
		{ $message.="<tr><td>".$row_rs_individuarrive['codeindividu'].'.'.$row_rs_individuarrive['numsejour'].'</td>'.
											'<td>'.$row_rs_individuarrive['nom'].' '.$row_rs_individuarrive['prenom'].'</td><td>'.$row_rs_individuarrive['libcat'].'</td>'.
											'<td>'.aaaammjj2jjmmaaaa($row_rs_individuarrive['datedeb_sejour'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individuarrive['datefin_sejour'],'/').'</td>';
			$un_referent=$tab_individu[$row_rs_individuarrive['codereferent']];
			$un_gesttheme=$tab_individu[$row_rs_individuarrive['codegesttheme']];
			$message.='<td>'.$un_referent['nom'].' '.$un_referent['prenom'].'</td><td>'.$un_gesttheme['nom'].' '.$un_gesttheme['prenom'].'</td>';
			$message.="</tr>";
			$tab_mail_unique[$un_gesttheme['email']]=$un_gesttheme;
		}
		$message.="</table>"; 
	}
	// Suppression des sejours de presents qui ont eventuellement ete notes 'msgsejourtermine' mais dont le sejour est en cours car modif de date fin
	mysql_query(" delete from individuaction".
							" where codelibaction='msgsejourtermine'".
							" and  (codeindividu,numsejour) in (select codeindividu,numsejour from individusejour".
																		 						" where ".periodeencours('datedeb_sejour','datefin_sejour').")")  or die(mysql_error());
	// Séjours terminés
	$query_rs_individuparti= 	"SELECT individu.codeindividu, individusejour.numsejour,nom,prenom,codelibcat,libcat_fr as libcat,datedeb_sejour,datefin_sejour, ".
														" codereferent,codegesttheme".
														" from individu,individusejour,corps,cat".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codecorps=corps.codecorps".
														" and corps.codecat=cat.codecat".
														" and ".periodepassee('datefin_sejour').
														" and (individusejour.codeindividu,individusejour.numsejour) not in".
														" (select codeindividu,numsejour from individuaction where codelibaction='msgsejourtermine')".
														" order by datefin_sejour desc,nom,prenom";
	$rs_individuparti=mysql_query($query_rs_individuparti) or die(mysql_error()); 
	if(mysql_num_rows($rs_individuparti)>=1)
	{	$envoyer_message=true;
		$message.="<p></p>";
		$message.='<table border="1" align="center"><tr><td colspan="7" align="center"><b>S&eacute;jour(s) termin&eacute;(s)</b></td></tr>';
		$message.='<tr><td align="center"><b>N&deg; dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Cat&eacute;gorie</b></td><td align="center"><b>Dates du s&eacute;jour</b></td><td></td><td align="center"><b>R&eacute;f&eacute;rent</b></td><td align="center"><b>Suivi par</b></td></tr>';
		while($row_rs_individuparti = mysql_fetch_assoc($rs_individuparti))
		{ $tab_individuparti[$row_rs_individuparti['codeindividu']][$row_rs_individuparti['numsejour']]['msgsejourtermine']=$row_rs_individuparti;
			$message.="<tr><td>".$row_rs_individuparti['codeindividu'].'.'.$row_rs_individuparti['numsejour'].'</td>'.
											'<td>'.$row_rs_individuparti['nom'].' '.$row_rs_individuparti['prenom'].'</td><td>'.$row_rs_individuparti['libcat'].'</td>'.
											'<td>'.aaaammjj2jjmmaaaa($row_rs_individuparti['datedeb_sejour'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individuparti['datefin_sejour'],'/').'</td>';
			$message.="<td>";
			if($row_rs_individuparti['codelibcat']=='DOCTORANT')
			{ $query_rs_individuthese="select date_soutenance from individuthese".
																" where codeindividu=".GetSQLValueString($row_rs_individuparti['codeindividu'], "text").
																" and numsejour=".GetSQLValueString($row_rs_individuparti['numsejour'], "text");
				$rs_individuthese=mysql_query($query_rs_individuthese) or die(mysql_error());
				if($row_rs_individuthese = mysql_fetch_assoc($rs_individuthese))
				{ if($row_rs_individuthese['date_soutenance']=='')
					{ $message.="<b>Pas de date de soutenance</b>";
					}
				}
			}
			$message.='</td>';
			$un_referent=$tab_individu[$row_rs_individuparti['codereferent']];
			$un_gesttheme=$tab_individu[$row_rs_individuparti['codegesttheme']];
			$message.='<td>'.$un_referent['nom'].' '.$un_referent['prenom'].'</td><td>'.$un_gesttheme['nom'].' '.$un_gesttheme['prenom'].'</td>';
			$message.="</tr>";
			//$tab_mail_unique[$un_referent['email']]=$un_referent;
			$tab_mail_unique[$un_gesttheme['email']]=$un_gesttheme;
		}
		
		$message.="</table>"; 
	}
	
	$query_rs_individusejour= "SELECT individu.codeindividu, individusejour.numsejour,nom,prenom,codelibcat,libcat_fr as libcat,datedeb_sejour,datefin_sejour,codereferent,codegesttheme ".
														" from individu,individusejour,corps,cat".
														" where individu.codeindividu=individusejour.codeindividu".
														" and individusejour.codecorps=corps.codecorps".
														" and corps.codecat=cat.codecat and cat.codelibcat<>'EXTERIEUR' and cat.codelibcat<>'PATP' and cat.codelibcat<>'STAGIAIRE' ".
														" and codestatutpers='02'".//non permanents
														" and ".periodeencours('datedeb_sejour','datefin_sejour').
														/* " and (individusejour.codeindividu,individusejour.numsejour) not in".
														" (select codeindividu,numsejour from individuaction	where codelibaction='msgsansemploi')". */
														" order by nom,prenom";
														/*.Rajouter periodefuture ?*/
	$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error()); 
	if(mysql_num_rows($rs_individusejour)>=1)
	{	$envoyer_message=true;
		$message.="<br><br>";
		$message.='<table border="1" cellpadding="3" align="center">';
		$message.='<tr><td align="center" colspan="8"><b>Personnes sans emploi dans moins d&rsquo;un mois (CDD, DOCTORANT, POSTDOC)</b></td></tr>';
		$message.='<tr><td align="center"><b>Num dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Cat&eacute;gorie</b></td>'.
							'<td align="center"><b>Fin s&eacute;jour - Fin emploi</b></td><td align="center"></td><td align="center"><b>R&eacute;f&eacute;rent</b></td><td align="center"><b>Suivi par</b></td></tr>'; 
		while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
		{ $query_rs_individuemploi="select codeindividu,datedeb_emploi,datefin_emploi from individuemploi".
																" where codeindividu=".GetSQLValueString($row_rs_individusejour['codeindividu'], "text").
																" and ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individusejour['datedeb_sejour']."'","'".$row_rs_individusejour['datefin_sejour']."'").
																" order by datefin_emploi desc";
			$rs_individuemploi=mysql_query($query_rs_individuemploi) or die(mysql_error());
			// l'emploi qui se termine le plus tard est en tete de liste (tri desc)
			$codeindividu_prec='-1';
			while($row_rs_individuemploi = mysql_fetch_assoc($rs_individuemploi))
			{ if($row_rs_individuemploi['codeindividu']!=$codeindividu_prec)
				{ $codeindividu_prec=$row_rs_individuemploi['codeindividu'];
					$message_emploi="";
					$tab_amj=duree_aaaammjj(date('Y/m/d'), $row_rs_individuemploi['datefin_emploi']);
					if($tab_amj['a']==0 && $tab_amj['m']==0)
					{ $message_emploi=($row_rs_individuemploi['datefin_emploi']==''?'Pas de date fin emploi':("Plus d&rsquo;emploi".($tab_amj['j']==''?"":" dans ".$tab_amj['j']." jour".($tab_amj['j']==1?"":"s"))));
					}
					if($message_emploi!='')
					{ $tab_individu[$row_rs_individusejour['codeindividu']][$row_rs_individusejour['numsejour']]['msgsansemploi']=$row_rs_individusejour;
						$message.="<tr><td>".$row_rs_individusejour['codeindividu'].'.'.$row_rs_individusejour['numsejour'].'</td>';
						$comp_fin_sejour_emloi='=';
						if($row_rs_individusejour['datefin_sejour']<$row_rs_individuemploi['datefin_emploi'])
						{ $comp_fin_sejour_emloi='&lt;';
						}
						else if($row_rs_individusejour['datefin_sejour']>$row_rs_individuemploi['datefin_emploi'])
						{ $comp_fin_sejour_emloi='&gt;';
						}
						$message.='<td>'.$row_rs_individusejour['nom'].' '.$row_rs_individusejour['prenom'].'</td><td>'.$row_rs_individusejour['libcat'].'</td><td nowrap>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour'],'/').' <b>'.$comp_fin_sejour_emloi.'</b> '.aaaammjj2jjmmaaaa($row_rs_individuemploi['datefin_emploi'],'/').'</td>';
						$message.="<td>".$message_emploi.'</td>';
						$un_referent=$tab_individu[$row_rs_individusejour['codereferent']];
						$un_gesttheme=$tab_individu[$row_rs_individusejour['codegesttheme']];
						$message.='<td>'.$un_referent['nom'].' '.$un_referent['prenom'].'</td><td nowrap>'.$un_gesttheme['nom'].' '.$un_gesttheme['prenom'].'</td>';
						$message.="</tr>";
						//$tab_mail_unique[$un_referent['email']]=$un_referent;
						$tab_mail_unique[$un_gesttheme['email']]=$un_gesttheme;
						$message.="</tr>";
					}
				}
			}
		}
		$message.="</table>";
		
		mysql_data_seek($rs_individusejour,0);
		$message.="<br><br>";
		$message.='<table border="1" cellpadding="3" align="center">';
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
		$message.='<table border="1" cellpadding="3" align="center">';
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
		$message.='<table border="1" cellpadding="3" align="center">';
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
														" and ".periodeencours('datedeb_sejour','datefin_sejour').
														" and date_preminscr=''".
														" order by nom,prenom";
	$rs_individusejour=mysql_query($query_rs_individusejour) or die(mysql_error());
	if(mysql_num_rows($rs_individusejour)>=1)
	{ $message.="<br><br>";
		$message.='<table border="1" cellpadding="3" align="center">';
		$message.='<tr><td align="center" colspan="5"><b>Doctorants sans date d&rsquo;inscription</b>';
		$message.='<tr><td align="center"><b>Num dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Dates du s&eacute;jour</b></td>'; 
		while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
		{ $message.='<tr><td>'.$row_rs_individusejour['codeindividu'].'.'.$row_rs_individusejour['numsejour'].'</td><td>'.$row_rs_individusejour['nom'].' '.$row_rs_individusejour['prenom'].'</td><td>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datedeb_sejour'],'/').' - '.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour'],'/').'</td></tr>'; 
		}
		$message.="</table>"; 
	}
	
	$message.="<br><br>";
	$message.="Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
	
	if($envoyer_message)
	{	$subject="12+ Pour information et suite &agrave; donner : s&eacute;jour(s) d&eacute;but&eacute;(s) et termin&eacute;(s)";
		$subject=	html_entity_decode($subject);
		
		$from = $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
		$replyto= $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
		
		$rs_structureindividu=mysql_query("select distinct nom,prenom,email from individu,structureindividu,structure".
																			" where individu.codeindividu=structureindividu.codeindividu".
																			" and structureindividu.codestructure=structure.codestructure".
																			" and ".periodeencours('datedeb_struct','datefin_struct').
																			" and (codelib=".GetSQLValueString('srh', "text").
																			" or codelib=".GetSQLValueString('admingestfin', "text")." or codelib=".GetSQLValueString('sii', "text").")") or die(mysql_error()); //and estresp='oui'
		while($row_rs_structureindividu = mysql_fetch_assoc($rs_structureindividu))
		{ $tab_mail_unique[$row_rs_structureindividu['email']]=$row_rs_structureindividu;
		}
		
		$to="";
		$first=true;
		foreach($tab_mail_unique as $email=>$un_tab_mail_unique)
		{ $to.=($first?"":", ").$un_tab_mail_unique['prenom'].' '.$un_tab_mail_unique['nom'].' <'.$un_tab_mail_unique['email'].'>';
			$first=false;
		}
		
		if($GLOBALS['mode_exploit']=='test')
		{ $message.='<br>En test, destinataires en fin de message : '.$to;
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
		$erreur_envoi_mail=""; 
		if($GLOBALS['mode_avec_envoi_mail'])
		{ $erreur_envoi_mail=envoimail($headers, $message_html_txt);//envoimail($tab_destinataires, $headers, $message);
		}
		else
		{ $erreur_envoi_mail=$message;
		}	
		//envoimail($tab_destinataires, $headers, $message);
		//$erreur_envoi_mail='';
		if($erreur_envoi_mail=="")
		{ foreach($tab_individuparti as $codeindividu=>$tab_un_individuparti)
			{ foreach($tab_un_individuparti as $numsejour=>$tab_un_individusejourparti)
				{ if(isset($tab_un_individusejourparti['msgsejourtermine']))
					{ $updateSQL=	"delete from individuaction where codeindividu=".GetSQLValueString($codeindividu, "text").
												" and numsejour=".GetSQLValueString($numsejour, "text")." and codelibaction='msgsejourtermine'"; 
						mysql_query($updateSQL);
						$updateSQL=	"insert into individuaction (codeindividu, numsejour, codelibaction, datedeb_action,datefin_action,codeacteur)".
												" values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($numsejour, "text").",".
															"'msgsejourtermine',".GetSQLValueString($aujourdhui, "text").",'','')";
						$message_pg.="\n".$updateSQL;
						mysql_query($updateSQL);
					}
				}
			}
		}
		else
		{ $message_pg.="<br>"."Erreur envoi mail : "."<br>".$erreur_envoi_mail;
		}
	}
}
//Verif. user autorise a lancer le batch
// 'batch_de_nuit_copie_upload';
$nb_fichiers=0;

copierep($GLOBALS['path_to_rep_upload'],$GLOBALS['path_to_rep_upload_sauve']);

$message_pg.="\n".$nb_fichiers.' copies';

function copierep($nomreporig,$nomrepdest)
{ global $nb_fichiers,$message_pg;
	$contenurep = dir($nomreporig);
	while (false !== ($entry = $contenurep->read()))  
	{	clearstatcache();
		if(!is_dir($nomreporig.'/'.$entry))
		{	$nb_fichiers++;
			$message_pg.="\n". date ("F d Y H:i:s.", filemtime($nomreporig.'/'.$entry)).' '.$nomreporig.'/'.$entry.' -> '.$nomrepdest.'/'.$entry;
			copy($nomreporig.'/'.$entry, $nomrepdest.'/'.$entry);
		}
		else 
		{ if($entry!="." && $entry!="..")
			{ if(!is_dir($nomrepdest.'/'.$entry))
				{ mkdir($nomrepdest.'/'.$entry);
				}
				copierep($nomreporig.'/'.$entry,$nomrepdest.'/'.$entry);
			}
		}
	} 
}

echo $message;
$subject="Pour information admin : script batch_de_nuit.bat";
$message_pg.="Bonjour,\n\n";
$headers = array ('From' => "Serveur 12+ <".$GLOBALS['webMaster']['email'].">",'To' => $GLOBALS['webMaster']['email'],'Reply-To' => "Serveur 12+<".$GLOBALS['webMaster']['email'].">",'Subject' => $subject);
if($GLOBALS['mode_avec_envoi_mail'])
{ envoimail($headers, $message_pg);
}

if(isset($rs))mysql_free_result($rs);
if(isset($rs_individustatutvisa))mysql_free_result($rs_individustatutvisa);


?>