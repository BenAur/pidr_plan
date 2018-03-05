<?php /*----------------------------------------- CONTROLES DES FORMULAIRES ENVOYES--------------------------------*/

// Passage de &$tab_post par adresse : modif du $_POST pour corriger les dates 1/1/12 en 01/01/2012 
function controle_form_fiche_dossier_pers_choix_corps(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	//if(isset($tab_post['codelieu']) && $tab_post['codelieu']!='' && isset($tab_post['autrelieu']) && $tab_post['autrelieu']!='') {$erreur.="<br>"."Pas plus d'un lieu pour l&rsquo;activit&eacute; de recherche";}
	if(isset($tab_post['codelieu']) && $tab_post['codelieu']=='') {$erreur.="<br>"."Lieu pour l&rsquo;activit&eacute; de recherche obligatoire";}
	foreach($tab_champs_date as $champ_date=>$tab)
	{ if(isset($tab_post[$champ_date.'_jj']))
	  { if(strlen($tab_post[$champ_date.'_jj'])==1)
			{ $tab_post[$champ_date.'_jj']='0'.$tab_post[$champ_date.'_jj'];
			}
			if(strlen($tab_post[$champ_date.'_mm'])==1)
			{ $tab_post[$champ_date.'_mm']='0'.$tab_post[$champ_date.'_mm'];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'])==2)
			{ if($champ_date=="date_naiss")
				{ $tab_post[$champ_date.'_aaaa']='19'.$tab_post[$champ_date.'_aaaa'];
				}
				else
				{ $tab_post[$champ_date.'_aaaa']='20'.$tab_post[$champ_date.'_aaaa'];
				}
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'];
			$tab_post[$champ_date]='';
			if($tab_champs_date[$champ_date]['jj'].$tab_champs_date[$champ_date]['mm'].$tab_champs_date[$champ_date]['aaaa']!="")
			{ $tab_post[$champ_date]=$tab_champs_date[$champ_date]['aaaa'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['jj'];
			}
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	// datedeb doit etre inf&eacute;rieures a datefin 
	$datedeb="datedeb_sejour";$datefin="datefin_sejour";
	if(isset($tab_post[$datedeb.'_jj']) && isset($tab_post[$datefin.'_jj'])) 
	{ if($tab_champs_date[$datefin]['jj'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['aaaa']!="" && $tab_champs_date[$datedeb]['aaaa'].$tab_champs_date[$datedeb]['mm'].$tab_champs_date[$datedeb]['jj']>$tab_champs_date[$datefin]['aaaa'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['jj'])
		{ $erreur.="<br>".$tab_champs_date[$datedeb]['lib'].' post&eacute;rieure &agrave; '.$tab_champs_date[$datefin]['lib'];
		}
	}

	// La date d'arriv&eacute;e doit etre renseign&eacute;e si elle est dans le formulaire, sinon la date d'arriv&eacute;e doit etre renseign&eacute;e
	$datedeb_sejour_vide=false;
	$datedeb="datedeb_sejour";
	if(isset($tab_post[$datedeb.'_jj']))
	{	if($tab_champs_date[$datedeb]['jj'].$tab_champs_date[$datedeb]['mm'].$tab_champs_date[$datedeb]['aaaa']=="")
		{ $datedeb_sejour_vide=true;
		}
	}
	else
	{ $datedeb_sejour_vide=true;
	}
	if($datedeb_sejour_vide)
	{ $erreur.="<br>".$tab_champs_date[$datedeb]['lib'].' obligatoire';
	}

	if(isset($tab_post['codestatutpers']) && $tab_post['codestatutpers']=='02')
	{ // Si non permanent, la date de fin doit etre renseignee
		$datefin_sejour_vide=false;
		$datefin="datefin_sejour";
		if(isset($tab_post[$datefin.'_jj']))
		{	if($tab_champs_date[$datefin]['jj'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['aaaa']=="")
			{ $datefin_sejour_vide=true;
			}
		}
		else
		{ $datefin_sejour_vide=true;
		}
		if($datefin_sejour_vide)
		{ $erreur.="<br>Non permanent : ".$tab_champs_date[$datefin]['lib'].' obligatoire';
		}
	}
	// 20161213 si limitation a sejour de 5 jours max.
	$datedeb="datedeb_sejour";$datefin="datefin_sejour";
  $tab_duree=duree_aaaammjj($tab_post[$datedeb.'_aaaa']."/".$tab_post[$datedeb.'_mm']."/".$tab_post[$datedeb.'_jj'],$tab_post[$datefin.'_aaaa']."/".$tab_post[$datefin.'_mm']."/".$tab_post[$datefin.'_jj']);
	if(isset($_POST['limitation5joursmax']))
	{ if($tab_duree["a"]+$tab_duree["m"]>=1 || (($tab_duree["a"]+$tab_duree["m"]==0) && $tab_duree["j"]>5))
		{ $erreur.="Vous n'&ecirc;tes pas autoris&eacute; &agrave; saisir des s&eacute;jours de plus de 5 jours !";
		}
	}
	

	if($erreur=='' && isset($tab_post['codeindividu']) && $tab_post['codeindividu']!='')
	{ // verif intersection sejour
		$query_rs="select * from individusejour".
							" where codeindividu=".GetSQLValueString($tab_post['codeindividu'], "text").
							" and ".intersectionperiodes('datedeb_sejour','datefin_sejour',GetSQLValueString($tab_post['datedeb_sejour'], "text"),GetSQLValueString($tab_post['datefin_sejour'], "text"));
		$rs=mysql_query($query_rs);
		if($row_rs=mysql_fetch_assoc($rs))
		{ $erreur.='<br>Intersection avec le s&eacute;jour du '.aaaammjj2jjmmaaaa($row_rs['datedeb_sejour'],'/').' au '.aaaammjj2jjmmaaaa($row_rs['datefin_sejour'],'/');
		}
		// verif dates non contigues dans un meme corps
		$query_rs="select * from individusejour".
								" where codeindividu=".GetSQLValueString($tab_post['codeindividu'], "text").
								" and codecorps=".GetSQLValueString($tab_post['codecorps'], "text").
								" and (DATEDIFF(replace(datedeb_sejour,'/','-'),replace('".$tab_post['datefin_sejour']."','/','-'))=1".
								"      or DATEDIFF(replace(datefin_sejour,'/','-'),replace('".$tab_post['datedeb_sejour']."','/','-'))=-1)";
		$rs=mysql_query($query_rs);
		if($row_rs=mysql_fetch_assoc($rs))
		{ $erreur.='<br>S&eacute;jours contigus dans le m&ecirc;me corps : il n&rsquo;y a pas lieu de cr&eacute;er un nouveau s&eacute;jour';
		}
	}

	return $erreur;
}

function controle_form_fiche_dossier_pers(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	$tab_champs_heure_mn=$tab_controle_et_format['tab_champs_heure_mn'];
	$tab_champs_numerique=$tab_controle_et_format['tab_champs_numerique'];	
	$invite=(isset($tab_post['codecorps']) && ($tab_post['codecorps']=='54' || $tab_post['codecorps']=='56'));
	$horsue=(isset($tab_post['ue']) && $tab_post['ue']=='non');

	// Controle de l'ensemble des champs varchar de longueur fixe : maxlength ne suffit pas a cause de car. codés en #hhh
	/* $tables=array('individu','individusejour','individuemploi','individupj','individuthese');
	foreach($tables as $table)
	{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
		while($row_rs_fields = mysql_fetch_assoc($rs_fields))
		{ echo '<br>'.$row_rs_fields['Field'].' '.$row_rs_fields['Type'];
		}
	}
	$erreur.=checklongueur('nom',30,'Nom');$erreur.=checklongueur('prenom',30,'Pr&eacute;nom');$erreur.=checklongueur('nomjf',30,'Nom de jeune fille');
	$erreur.=checklongueur('ville_naiss',50,'Ville de naissance');$erreur.=checklongueur('pays_naiss',50,'Pays de naissance');
	 */
	// Controle longueur des champs de type text
	$erreur.=checklongueur('adresse_pers',200,'Adresse personnelle');
	$erreur.=checklongueur('titre_prog_rech',200,'Sujet ou programme de recherche');
	$erreur.=checklongueur('prog_rech',6000,'Sujet ou programme de recherche');
	$erreur.=checklongueur('note',6000,'Notes partag&eacute;es');	
	$erreur.=checklongueur('postit',200,'Postit');
	$erreur.=checklongueur('resume_these',6000,'R&eacute;sum&eacute;');
	$erreur.=checklongueur('jury_autres_membres_these',1000,'R&eacute;sum&eacute;');
	$erreur.=checklongueur('descriptionmission',6000,'Description de la mission');
	$erreur.=checklongueur('avis_motive_resp_zrr',300,'Avis motiv&eacute;');	
	if(isset($tab_post['nom'])) $tab_post['nom']=trim($tab_post['nom']);
	if(isset($tab_post['prenom'])) $tab_post['prenom']=trim($tab_post['prenom']);
	
	if(isset($tab_post['codereferent']) && $tab_post['codereferent']=='') $erreur.="<br>"."R&eacute;f&eacute;rent obligatoire.";
	if(isset($tab_post['codeciv']) && $tab_post['codeciv']=='') $erreur.="<br>"."Civilit&eacute; obligatoire !";
	if(isset($tab_post['nom']) && $tab_post['nom']=='') $erreur.="<br>"."Nom obligatoire !";
	if(isset($tab_post['prenom']) && $tab_post['prenom']=='') $erreur.="<br>"."Pr&eacute;nom obligatoire !";
	if(isset($tab_post['date_naiss_jj']) && $tab_post['date_naiss_jj']=='')
	{ $erreur.="<br>"."Date de naissance obligatoire.";
	}
	if(isset($tab_post['ville_naiss']) && $tab_post['ville_naiss']=='')
	{ $erreur.="<br>"."Ville de naissance obligatoire.";
	}
	if(isset($tab_post['codepays_naiss']) && $tab_post['codepays_naiss']=='')
	{ $erreur.="<br>"."Pays de naissance obligatoire.";
	}
	if(isset($tab_post['codenat']) && $tab_post['codenat']=='')
	{ $erreur.="<br>"."Nationalit&eacute; obligatoire.";
	}
	
	if(isset($tab_post['tel']))
	{ if(strlen($tab_post['tel'])>0 && strlen($tab_post['tel'])<10)
		{ $erreur.="<br>".'Le num&eacute;ro de t&eacute;l&eacute;phone doit comporter au moins 10 chiffres ou &ecirc;tre vide';
		}
	}
	
	if(isset($tab_post['fax']))
	{ if(strlen($tab_post['fax'])>0 && strlen($tab_post['fax'])<10)
		{ $erreur.="<br>"."Le num&eacute;ro de fax doit comporter aucun ou au moins 10 chiffres.";
		}
	}
	if(isset($tab_post['telport']))
	{ if(strlen($tab_post['telport'])>0 && strlen($tab_post['telport'])<10)
		{ $erreur.="<br>"."Le num&eacute;ro de t&eacute;l&eacute;phone portable doit comporter au moins 10 chiffres ou &ecirc;tre vide";
		}
	}
	//if(isset($tab_post["email"]) && $tab_post["email"]=='' && isset($tab_post["email_parti"]) && $tab_post["email_parti"]=='') $erreur.="<br>".'Mail obligatoire';
	
	if(isset($tab_post['num_insee']) && $tab_post['num_insee']!='' && strlen($tab_post['num_insee'])!=13 && strlen($tab_post['num_insee'])!=15)
	{ $erreur.="<br>Le n° INSEE doit comporter 13 chiffres ou 15 avec la cl&eacute; : il en comporte ".strlen($tab_post['num_insee']);  
	}
	
	//if(isset($tab_post['codeetab']) && $tab_post['codeetab']!='' && isset($tab_post['autreetab']) && $tab_post['autreetab']!='') {$erreur.="<br>"."Pas plus d'un employeur";}
	if(isset($tab_post['codeetab']) && $tab_post['codeetab']=='' && isset($tab_post['autreetab']) && $tab_post['autreetab']=='') {$erreur.="<br>"."Employeur obligatoire";}
	//if(isset($tab_post['codelieu']) && $tab_post['codelieu']!='' && isset($tab_post['autrelieu']) && $tab_post['autrelieu']!='') {$erreur.="<br>"."Pas plus d'un lieu pour l&rsquo;activit&eacute; de recherche";}
	if(isset($tab_post['codelieu']) && $tab_post['codelieu']=='') {$erreur.="<br>"."Lieu pour l&rsquo;activit&eacute; de recherche obligatoire";}
	if(isset($tab_post['codediplome_prep']) && $tab_post['codediplome_prep']!='' && isset($tab_post['autrediplome_prep']) && $tab_post['autrediplome_prep']!='') {$erreur.="<br>"."Pas plus d&rsquo;un dipl&ocirc;me pr&eacute;par&eacute;";}
	if(isset($tab_post['codediplome_prep']) && $tab_post['codediplome_prep']=='' && isset($tab_post['autrediplome_prep']) && $tab_post['autrediplome_prep']=='') {$erreur.="<br>"."Dipl&ocirc;me pr&eacute;par&eacute; obligatoire";}
	if(isset($tab_post['codecentrecout']) && isset($tab_post['autrecentrecout']) && $tab_post['codecentrecout']!='' && $tab_post['autrecentrecout']!='') {$erreur.="<br>"."Pas plus d'un centre de co&ucirc;t.";}
	
	if(isset($tab_post['cotutelle']) || (isset($tab_post['cotutelle_etab']) && $tab_post['cotutelle_etab']!='') || (isset($tab_post['codepays_cotutelle']) && $tab_post['codepays_cotutelle']!=''))
	{ $tab_post['cotutelle']="On";//initialisation du champ avec la valeur On pour qu'elle soit bien prise en compte en tant que valeur "cheked"
		if(isset($tab_post['cotutelle_etab']) && $tab_post['cotutelle_etab']=='')
		{ $erreur.="<br>"."Etablissement de cotutelle obligatoire.";
		}
		if(isset($tab_post['codepays_cotutelle']) && $tab_post['codepays_cotutelle']=='')
		{ $erreur.="<br>"."Pays de cotutelle obligatoire.";
		}
	}
	
	if(isset($tab_post['date_preminscr_jj']) && $tab_post['date_preminscr_jj']!='')
	{ if(isset($tab_post['num_inscr']) && $tab_post['num_inscr']=='')
		{ $erreur.="<br>"."Date d&rsquo;inscription renseign&eacute;e : num&eacute;ro d&rsquo;inscription obligatoire";
		}
	}
	
	if(isset($tab_post['codetypeprofession_postdoc']) && $tab_post['codetypeprofession_postdoc']=='')
	{ $erreur.="<br>"."Type profession obligatoire.";
	}

	// champs ann&eacute;es : si pas num&eacute;rique ou (pas vide et longueur!=4)
	if(isset($tab_post['master_prep_annee'])){if(!est_champ_annee($tab_post['master_prep_annee'])) $erreur.="<br>"."Ann&eacute;e master erron&eacute;e : ".$tab_post['master_prep_annee'];}
	if(isset($tab_post['master_obtenu_annee'])){if(!est_champ_annee($tab_post['master_obtenu_annee'])) $erreur.="<br>"."Ann&eacute;e master obtenu erron&eacute;e : ".$tab_post['master_obtenu_annee'];}
	if(isset($tab_post['master_obtenu_annee'])){if(!est_champ_annee($tab_post['diplome_dernier_annee'])) $erreur.="<br>"."Ann&eacute;e dernier dipl&ocirc;me erron&eacute;e : ".$tab_post['diplome_dernier_annee'];}

	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $tab_post[$champ_numerique]=str_replace(chr(9),'',str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique])));
			if($tab_post[$champ_numerique]!='' && !is_numeric($tab_post[$champ_numerique]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']) && $tab_post[$champ_numerique]!='')
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$champ_numerique."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else
					{ $tab_post[$champ_numerique]=sprintf($tab['string_format'],$tab_post[$champ_numerique]);
					}
				}
			}
		}
	}
	
	foreach($tab_champs_date as $champ_date=>$tab)
	{ if(isset($tab_post[$champ_date.'_jj']))
	  { if(strlen($tab_post[$champ_date.'_jj'])==1)
			{ $tab_post[$champ_date.'_jj']='0'.$tab_post[$champ_date.'_jj'];
			}
			if(strlen($tab_post[$champ_date.'_mm'])==1)
			{ $tab_post[$champ_date.'_mm']='0'.$tab_post[$champ_date.'_mm'];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'])==2)
			{ if($champ_date=="date_naiss")
				{ $tab_post[$champ_date.'_aaaa']='19'.$tab_post[$champ_date.'_aaaa'];
				}
				else
				{ $tab_post[$champ_date.'_aaaa']='20'.$tab_post[$champ_date.'_aaaa'];
				}
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'];
			$tab_post[$champ_date]='';
			if($tab_champs_date[$champ_date]['jj'].$tab_champs_date[$champ_date]['mm'].$tab_champs_date[$champ_date]['aaaa']!="")
			{ $tab_post[$champ_date]=$tab_champs_date[$champ_date]['aaaa'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['jj'];
			}
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	foreach($tab_champs_heure_mn as $champ_heure_mn=>$tab)
	{ if(isset($tab_post[$champ_heure_mn.'_hh']) && isset($tab_post[$champ_heure_mn.'_mn']))
		{ if(strlen($tab_post[$champ_heure_mn.'_hh'])==1)
			{ $tab_post[$champ_heure_mn.'_hh']='0'.$tab_post[$champ_heure_mn.'_hh'];
			}
			if(strlen($tab_post[$champ_heure_mn.'_mn'])==1)
			{ $tab_post[$champ_heure_mn.'_mn']='0'.$tab_post[$champ_heure_mn.'_mn'];
			}
			else if(strlen($tab_post[$champ_heure_mn.'_mn'])==0 && strlen($tab_post[$champ_heure_mn.'_hh'])!=0)
			{ $tab_post[$champ_heure_mn.'_mn']='00';
			}
			$tab_champs_heure_mn[$champ_heure_mn]['hh']=$tab_post[$champ_heure_mn.'_hh'];
			$tab_champs_heure_mn[$champ_heure_mn]['mn']=$tab_post[$champ_heure_mn.'_mn'];
			if(!est_heure_mn($tab_champs_heure_mn[$champ_heure_mn]['hh'],$tab_champs_heure_mn[$champ_heure_mn]['mn']))
			{ $erreur.="<br>".$tab_champs_heure_mn[$champ_heure_mn]['lib'].' mal form&eacute;e : '.$tab_champs_heure_mn[$champ_heure_mn]['hh'].'H'.$tab_champs_heure_mn[$champ_heure_mn]['mn'];
			}
		}
	}

	// datedeb doit etre inf&eacute;rieures a datefin 
	$datedeb="datedeb_sejour_prevu";$datefin="datefin_sejour_prevu";
	if(isset($tab_post[$datedeb.'_jj']) && isset($tab_post[$datefin.'_jj'])) 
	{ if($tab_champs_date[$datefin]['jj'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['aaaa']!="" && $tab_champs_date[$datedeb]['aaaa'].$tab_champs_date[$datedeb]['mm'].$tab_champs_date[$datedeb]['jj']>$tab_champs_date[$datefin]['aaaa'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['jj'])
		{ $erreur.="<br>".$tab_champs_date[$datedeb]['lib'].' post&eacute;rieure &agrave; '.$tab_champs_date[$datefin]['lib'];
		}
	}
	$datedeb="datedeb_sejour";$datefin="datefin_sejour";
	if(isset($tab_post[$datedeb.'_jj']) && isset($tab_post[$datefin.'_jj']))//si datedeb et datefin dans le form  
	{ if($tab_champs_date[$datefin]['jj'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['aaaa']!="" && $tab_champs_date[$datedeb]['aaaa'].$tab_champs_date[$datedeb]['mm'].$tab_champs_date[$datedeb]['jj']>$tab_champs_date[$datefin]['aaaa'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['jj'])
		{ $erreur.="<br>".$tab_champs_date[$datedeb]['lib'].' post&eacute;rieure &agrave '.$tab_champs_date[$datefin]['lib'];
		}
		// 20161213 si limitation a sejour de 5 jours max.
		$tab_duree=duree_aaaammjj($tab_post[$datedeb.'_aaaa']."/".$tab_post[$datedeb.'_mm']."/".$tab_post[$datedeb.'_jj'],$tab_post[$datefin.'_aaaa']."/".$tab_post[$datefin.'_mm']."/".$tab_post[$datefin.'_jj']);
		if($tab_duree["a"]+$tab_duree["m"]>=1 || (($tab_duree["a"]+$tab_duree["m"]==0) && $tab_duree["j"]>5))
		{ if(isset($_POST['limitation5joursmax']))
			{ $erreur.="Vous n'&ecirc;tes pas autoris&eacute; &agrave; saisir des s&eacute;jours de plus de 5 jours.";
			}
			else
			{ if(isset($tab_post['adresse_pers']) && $tab_post['adresse_pers']=='')
				{ $erreur.="<br>"."Adresse pers. obligatoire.";
				}
				else
				{ if(isset($tab_post['ville_pers']) && $tab_post['ville_pers']==''){ $erreur.="<br>"."Ville adresse pers. obligatoire.";}
					if(isset($tab_post['codepays_pers']) && $tab_post['codepays_pers']==''){ $erreur.="<br>"."Pays adresse pers. obligatoire";}
				}
			}
		}
		if($erreur=='' && isset($tab_post['codeindividu']))
		{ $query_rs="select * from individusejour".
								" where codeindividu=".GetSQLValueString($tab_post['codeindividu'], "text")." and numsejour<>".GetSQLValueString($tab_post['numsejour'], "text").
								" and ".intersectionperiodes('datedeb_sejour','datefin_sejour',GetSQLValueString($tab_post['datedeb_sejour'], "text"),GetSQLValueString($tab_post['datefin_sejour'], "text")).
								" and codeindividu<>''";
			$rs=mysql_query($query_rs);
			if($row_rs=mysql_fetch_assoc($rs))
			{ $erreur.='<br>Intersection avec le s&eacute;jour du '.aaaammjj2jjmmaaaa($row_rs['datedeb_sejour'],'/').' au '.aaaammjj2jjmmaaaa($row_rs['datefin_sejour'],'/');
			}
		}
	}

	$datedeb="datedeb_emploi";$datefin="datefin_emploi";
	if(isset($tab_post[$datedeb.'_jj']) && isset($tab_post[$datefin.'_jj']))//si datedeb et datefin dans le form  
	{ if($tab_champs_date[$datefin]['jj'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['aaaa']!="" && $tab_champs_date[$datedeb]['aaaa'].$tab_champs_date[$datedeb]['mm'].$tab_champs_date[$datedeb]['jj']>$tab_champs_date[$datefin]['aaaa'].$tab_champs_date[$datefin]['mm'].$tab_champs_date[$datefin]['jj'])
		{ $erreur.="<br>".$tab_champs_date[$datedeb]['lib'].' post&eacute;rieure &agrave '.$tab_champs_date[$datefin]['lib'];
		}
	}
		
	foreach(array("datedeb_sejour","datedeb_sejour_prevu") as $datedeb)	
	{ if(isset($tab_post[$datedeb]) && $tab_post[$datedeb]=="")
		{ $erreur.="<br>".$tab_champs_date[$datedeb]['lib'].' doit &ecirc;tre renseign&eacute;e';
		}
	}
	
	if(isset($tab_post['permanent']) && $tab_post['permanent']=='non')
	{ foreach(array("datefin_sejour","datefin_sejour_prevu") as $datefin)	
		{ if(isset($tab_post[$datefin]) && $tab_post[$datefin]=="")
		  { $erreur.="<br>".$tab_champs_date[$datefin]['lib'].' doit &ecirc;tre renseign&eacute;e';
			}
		}
	}
	
	
	// controles numdossierzrr
	if(isset($tab_post['numdossierzrr']) && $tab_post['numdossierzrr']!='')
	{ $tab=explode('-',$tab_post['numdossierzrr']);
		if(count($tab)!=4)
		{ $erreur.="num&eacute;ro zrr mal forme : aaaa-mm-codezrr-numordre"."<br>";
		}
		else
		{ if(!est_date('01',$tab[1],$tab[0]) || $tab[2]=='' || !(int)$tab[3])
			{ $erreur.="numero zrr mal forme : annee, mois ou numordre pas conforme a aaaa-mm-codezrr-numordre"."<br>";
			}
			$query_rs="select libzrr from zrr where libzrr=".GetSQLValueString($tab['2'], "text");
			$rs=mysql_query($query_rs);
			if(!($row_rs=mysql_fetch_assoc($rs)))
			{ $erreur.="code zrr ".$tab['2']." inexistant";
			}
			$rs=mysql_query("select codeindividu,numsejour from individusejour".
											" where numdossierzrr=".GetSQLValueString($tab_post['numdossierzrr'], "text")." and codeindividu<>''".
											" and concat(codeindividu,'.',numsejour)<>".GetSQLValueString($tab_post['codeindividu'].'.'.$tab_post['numsejour'], "text"));

			if($row_rs=mysql_fetch_assoc($rs))
			{ $erreur.="Numero zrr deja affecte au dossier ".$row_rs['codeindividu'].".".$row_rs['numsejour'];
			}
		}
	}
	// PG fsd 20160120
	// ces controles ne sont effectues que s'il y a generation de classeur fsd afin d'eviter que les anciens dossiers ou les dossiers mal renseignes ne soient bloques
	if(isset($_POST['submit_generer_classeur_fsd_x']))
	{ //if(isset($tab_post['codepostal_naiss']) && $tab_post['codepostal_naiss']==''){ $erreur.="<br>"."Code postal du lieu de naissance obligatoire.";}
		if(isset($tab_post['email']) && $tab_post['email']=='' && isset($tab_post['email_parti']) && $tab_post['email_parti']==''){ $erreur.="<br>"."Email obligatoire.";}
		if(isset($tab_post['adresse_pers']) && $tab_post['adresse_pers']==''){ $erreur.="<br>"."Adresse pers. obligatoire.";}
		if(isset($tab_post['ville_pers']) && $tab_post['ville_pers']==''){ $erreur.="<br>"."Ville adresse pers. obligatoire.";}
		if(isset($tab_post['codepays_pers']) && $tab_post['codepays_pers']==''){ $erreur.="<br>"."Pays adresse pers. obligatoire";}
		if(isset($tab_post['etab_orig']) && $tab_post['etab_orig']==''){ $erreur.="<br>"."Organisme d'appartenance obligatoire.";}
		if(isset($tab_post['ville_etab_orig']) && $tab_post['ville_etab_orig']==''){ $erreur.="<br>"."Ville de l'organisme d'appartenance obligatoire.";}
		if(isset($tab_post['codepays_etab_orig']) && $tab_post['codepays_etab_orig']==''){ $erreur.="<br>"."Pays de l'organisme d'appartenance obligatoire";}
		if(isset($tab_post['adresse_etab_orig']) && $tab_post['adresse_etab_orig']==''){ $erreur.="<br>"."Adresse de l'organisme d'appartenance obligatoire.";}

 		if(isset($tab_post['codetypepieceidentite']) && $tab_post['codetypepieceidentite']=='') {$erreur.="<br>"."Pi&egrave;ce d'identit&eacute; obligatoire";}
		if(isset($tab_post['codenat']) && $tab_post['codenat']!='079' && isset($tab_post['codetypepieceidentite']) && $tab_post['codetypepieceidentite']!='02')//hors france
		{ $erreur.="<br>"."Passeport obligatoire";
		}
		if(isset($tab_post['numeropieceidentite']) && $tab_post['numeropieceidentite']==''){ $erreur.="<br>"."Num&eacute;ro de pi&egrave;ce d'identit&eacute; obligatoire";}
		//if(isset($tab_post['codesituationprofessionnelle']) && $tab_post['codesituationprofessionnelle']=='' && isset($tab_post['autresituationprofessionnelle']) && $tab_post['autresituationprofessionnelle']=='') {$erreur.="<br>"."Situation professionnelle obligatoire";}
		if(isset($tab_post['codetypeacceszrr']) && $tab_post['codetypeacceszrr']==''){ $erreur.="<br>"."Statut au sein de la ZRR obligatoire";}
		if(isset($tab_post['codephysiquevirtuelzrr']) && $tab_post['codephysiquevirtuelzrr']==''){ $erreur.="<br>"."Acc&egrave;s physique/virtuel obligatoire";}
		//if(isset($tab_post['montantfinancement']) && $tab_post['montantfinancement']=='') {$erreur.="<br>"."Montant financement obligatoire";}
		if(isset($tab_post['codeoriginefinancement']) && $tab_post['codeoriginefinancement']=='') {$erreur.="<br>"."Origine du financement obligatoire";}
		if(isset($tab_post['intituleposte']) && $tab_post['intituleposte']!='' && isset($tab_post['codesujet']) && $tab_post['codesujet']!='')
		{ $erreur.="<br>"."Soit un poste, soit un sujet mais pas les deux";
		}
		if((isset($tab_post['codesujet']) && $tab_post['codesujet']=='') || !isset($tab_post['codesujet']))
		{ if(isset($tab_post['intituleposte']) && $tab_post['intituleposte']=='') {$erreur.="<br>"."Intitul&eacute; du poste obligatoire";}
		  if(isset($tab_post['descriptionmission']) && $tab_post['descriptionmission']=='') {$erreur.="<br>"."Description de la mission obligatoire";}
		  if(isset($tab_post['codedomainescientifique1']) && $tab_post['codedomainescientifique1']=='') {$erreur.="<br>"."Domaine scientifique obligatoire";}
		  if(isset($tab_post['codedisciplinescientifique1']) && $tab_post['codedisciplinescientifique1']=='') {$erreur.="<br>"."Discipline scientifique obligatoire";}
		}
	}
	// PG fsd 20160120
	return $erreur;
}

function controle_form_confirmer_mail_validation_demande_fsd(&$tab_post,$tab_controle_et_format)
{	$erreur="";
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	foreach($tab_champs_date as $champ_date=>$tab)
	{ if(isset($tab_post[$champ_date.'_jj']))
	  { if(strlen($tab_post[$champ_date.'_jj'])==1)
			{ $tab_post[$champ_date.'_jj']='0'.$tab_post[$champ_date.'_jj'];
			}
			if(strlen($tab_post[$champ_date.'_mm'])==1)
			{ $tab_post[$champ_date.'_mm']='0'.$tab_post[$champ_date.'_mm'];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'])==2)
			{ $tab_post[$champ_date.'_aaaa']='20'.$tab_post[$champ_date.'_aaaa'];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'];
			$tab_post[$champ_date]='';
			if($tab_champs_date[$champ_date]['jj'].$tab_champs_date[$champ_date]['mm'].$tab_champs_date[$champ_date]['aaaa']!="")
			{ $tab_post[$champ_date]=$tab_champs_date[$champ_date]['aaaa'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['jj'];
			}
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	if($tab_post["date_demande_fsd"]=='')
	{ $erreur.="<br>".$tab_champs_date[$champ_date]['lib']." obligatoire";
	}
	return $erreur;
}

function controle_form_confirmer_mail_validation_autorisation_fsd(&$tab_post,$tab_controle_et_format)
{	$erreur='';
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	foreach($tab_champs_date as $champ_date=>$tab)
	{ if(isset($tab_post[$champ_date.'_jj']))
	  { if(strlen($tab_post[$champ_date.'_jj'])==1)
			{ $tab_post[$champ_date.'_jj']='0'.$tab_post[$champ_date.'_jj'];
			}
			if(strlen($tab_post[$champ_date.'_mm'])==1)
			{ $tab_post[$champ_date.'_mm']='0'.$tab_post[$champ_date.'_mm'];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'])==2)
			{ $tab_post[$champ_date.'_aaaa']='20'.$tab_post[$champ_date.'_aaaa'];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'];
			$tab_post[$champ_date]='';
			if($tab_champs_date[$champ_date]['jj'].$tab_champs_date[$champ_date]['mm'].$tab_champs_date[$champ_date]['aaaa']!="")
			{ $tab_post[$champ_date]=$tab_champs_date[$champ_date]['aaaa'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['jj'];
			}
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	if($tab_post["date_autorisation"]=='')
	{ $erreur.="<br>".$tab_champs_date[$champ_date]['lib']." obligatoire";
	}
	return $erreur;
}

function controle_form_contrat(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	$tab_champs_numerique=$tab_controle_et_format['tab_champs_numerique'];
	// chaines de caracteres
	$erreur.=checklongueur('ref_contrat',100,'R&eacute;f&eacute;rence du contrat');
	//$erreur.=checklongueur('eotp',30,'EOTP');
	$erreur.=checklongueur('ref_prog_long',200,'R&eacute;f&eacute;rence programme long');
	$erreur.=checklongueur('sujet',400,'Objet');
	$erreur.=checklongueur('note',1000,'Note');
	// controle champs nouvel org gest
	$erreur.=checklongueur('nouveau_libcourtorggest',50,'Libellé court org. gest.');
	$erreur.=checklongueur('nouveau_liblongorggest',100,'Libellé long org. gest.');
	// controle champs nouveau projet
	$erreur.=checklongueur('nouveau_libcourtprojet',50,'Libellé court projet');
	$erreur.=checklongueur('nouveau_liblongprojet',100,'Libellé long projet');
	// controle champs nouvel org financeur
	$erreur.=checklongueur('nouveau_libcourtorgfinanceur',50,'Libellé court org. financeur');
	$erreur.=checklongueur('nouveau_liblongorgfinanceur',100,'Libellé long org. financeur');
	// controle champs nouveau partenaire
	$erreur.=checklongueur('libcourtpart',100,'Libellé court partenaire');
	$erreur.=checklongueur('liblongpart',100,'Libellé long partenaire');
	$erreur.=checklongueur('nomcontactpart',30,'Nom contact partenaire');
	$erreur.=checklongueur('prenomcontactpart',20,'Prénom contact partenaire');
	$erreur.=checklongueur('adressecontactpart',200,'Adresse contact partenaire');
	$erreur.=checklongueur('telcontactpart',20,'Tél. contact partenaire');
	$erreur.=checklongueur('telportcontactpart',20,'Tél. port. contact partenaire');
	$erreur.=checklongueur('emailcontactpart',100,'Email partenaire');
	if(!est_mail($tab_post['emailcontactpart'])) $erreur.='<br>Email mal form&eacute;';
	$erreur.=checklongueur('fonctioncontactpart',50,'Fonction partenaire');
	$erreur.=checklongueur('notepart',200,'Note partenaire');
	
	// rattachement GT ou dept
	if($_POST['codetheme']=='')
	{ $erreur.="GT ou dept. obligatoire!";
	}
	// resp obligatoire
	if($_POST['coderespscientifique']=='')
	{ $erreur.="Resp. scientifique obligatoire!";
	}
	// nombres
	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $tab_post[$champ_numerique]=str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique]));
			if($tab_post[$champ_numerique]!='' && !is_numeric($tab_post[$champ_numerique]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']))
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else
					{ $tab_post[$champ_numerique]=sprintf($tab['string_format'],$tab_post[$champ_numerique]);
					}
				}
				else
				{ if(isset($tab['max_length']) && strlen($tab_post[$champ_numerique])>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
				}
			}
		}
	}
	// Dates y compris datemontant_jj#$col##$ligne
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(substr($champ_date,0,strlen('datemontant#'))=='datemontant#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='datemontant';
		}
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}

	return $erreur;	
}

function controle_form_commande(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=isset($tab_controle_et_format['tab_champs_date'])?$tab_controle_et_format['tab_champs_date']:array();
	$tab_champs_numerique=isset($tab_controle_et_format['tab_champs_numerique'])?$tab_controle_et_format['tab_champs_numerique']:array();
	// chaines de caracteres
	$erreur.=checklongueur('objet',200,'Objet');
	$erreur.=checklongueur('description',400,'Description');
	$erreur.=checklongueur('libfournisseur',200,'Fournisseur');
	$erreur.=checklongueur('numcommande',20,'Num. commande');
	$erreur.=checklongueur('nummigo',20,'MIGO');
	$erreur.=checklongueur('rubriquecomptable',6,'Rubrique comptable');
	$erreur.=checklongueur('note',200,'Note');
 	//champs obligatoires
	if(isset($tab_post['codereferent']) && $tab_post['codereferent']=='') $erreur.="Demandeur obligatoire"."<br>";
	if(isset($tab_post['codesecrsite']) && $tab_post['codesecrsite']=='') $erreur.="Secr. site obligatoire"."<br>";
	if(isset($tab_post['objet']) && $tab_post['objet']=='') $erreur.="Objet obligatoire"."<br>";
	if(isset($tab_post['libfournisseur']) && $tab_post['libfournisseur']=='') $erreur.="Fournisseur obligatoire"."<br>";
	if(isset($tab_post['codetypecredit#0']) && $tab_post['codetypecredit#0']=='') $erreur.="Imputation obligatoire"."<br>";
	// au moins un champ montant engage ligne virtuel (code #0) obligatoire (non vide)
	$nbimputationvirtuel_montantengage=0;
	foreach($tab_post as $postkey=>$postval)
	{ if(substr($postkey,0,strlen('montantengage#0'))=='montantengage#0' && ($postval!=''))
		{ $nbimputationvirtuel_montantengage++;
		}
	}
	if($nbimputationvirtuel_montantengage==0)
	{ $erreur.="Montant engag&eacute; obligatoire (la valeur 0 convient)"."<br>";
	}
	// nombres
	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $suffixe='';
			if(substr($champ_numerique,0,strlen('montantengage#'))=='montantengage#')
			{ $suffixe=substr($champ_numerique,strpos($champ_numerique,'#'));
				$champ_numerique='montantengage';
			}
			if(substr($champ_numerique,0,strlen('montantpaye#'))=='montantpaye#')
			{ $suffixe=substr($champ_numerique,strpos($champ_numerique,'#'));
				$champ_numerique='montantpaye';
			}
			if(substr($champ_numerique,0,strlen('montantliquidation#'))=='montantliquidation#')
			{ $suffixe=substr($champ_numerique,strpos($champ_numerique,'#'));
				$champ_numerique='montantliquidation';
			}
			
			$tab_post[$champ_numerique.$suffixe]=str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique.$suffixe]));
			if($tab_post[$champ_numerique.$suffixe]!='' && !is_numeric($tab_post[$champ_numerique.$suffixe]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']))
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique.$suffixe]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else 
					{ $tab_post[$champ_numerique.$suffixe]=$tab_post[$champ_numerique.$suffixe];
					}
				}
				else
				{ if(isset($tab['max_length']) && strlen($tab_post[$champ_numerique.$suffixe])>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
				}
			}
		}
	}
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(substr($champ_date,0,strlen('datemigo#'))=='datemigo#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='datemigo';
		}
		else if(substr($champ_date,0,strlen('dateliquidation#'))=='dateliquidation#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='dateliquidation';
		}
		else if(substr($champ_date,0,strlen('datefacture#'))=='datefacture#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='datefacture';
		}
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_post[$champ_date.$suffixe]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_post[$champ_date.$suffixe]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_post[$champ_date.$suffixe]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_post[$champ_date.$suffixe]['jj'],$tab_post[$champ_date.$suffixe]['mm'],$tab_post[$champ_date.$suffixe]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_post[$champ_date.$suffixe]['jj'].'/'.$tab_post[$champ_date.$suffixe]['mm'].'/'.$tab_post[$champ_date.$suffixe]['aaaa'];
			}
		}
	}
	return $erreur;	
}

function controle_form_mission(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	$tab_champs_heure_mn=$tab_controle_et_format['tab_champs_heure_mn'];
	$tab_champs_numerique=$tab_controle_et_format['tab_champs_numerique'];
	// chaines de caracteres
	$erreur.=checklongueur('nom',30,'Nom');
	$erreur.=checklongueur('prenom',20,'Prenom');
	$erreur.=checklongueur('adresse_pers',200,'Adresse personnelle');
	$erreur.=checklongueur('email',100,'Adresse mail');
	$erreur.=checklongueur(isset($tab_post['tel'])?'tel':'telport',20,'T&eacute;l.');
	$erreur.=checklongueur('adresse_admin',200,'Adresse administrative');
	$erreur.=checklongueur('note',200,'Note');
	
	//champs obligatoires
	if(isset($tab_post['nom']) && $tab_post['nom']==''){ $erreur.="<br>"."Nom obligatoire.";}
	if(isset($tab_post['prenom']) && $tab_post['prenom']==''){ $erreur.="<br>"."Pr&eacute;nom obligatoire.";}
	if(isset($tab_post['motif']) && $tab_post['motif']==''){ $erreur.="<br>"."Motif obligatoire.";}
	if(isset($tab_post['date_naiss_jj']) && $tab_post['date_naiss_jj']==''){ $erreur.="<br>"."Date de naissance obligatoire.";}
	if(isset($tab_post['codecatmissionnaire']) && $tab_post['codecatmissionnaire']==''){ $erreur.="<br>"."Cat&eacute;gorie obligatoire.";}
	if(isset($tab_post['codesecrsite']) && $tab_post['codesecrsite']==''){ $erreur.="<br>"."Secr&eacute;taire obligatoire.";}
	if(isset($tab_post['adresse_pers']) && $tab_post['adresse_pers']=='' && isset($tab_post['adresse_admin']) && $tab_post['adresse_admin']==''){ $erreur.="<br>"."Au moins une adresse obligatoire.";}
	if(isset($tab_post['adresse_pers']) && $tab_post['adresse_pers']!='')
	{ if(isset($tab_post['ville_pers']) && $tab_post['ville_pers']==''){ $erreur.="<br>"."Ville adresse pers. obligatoire";}
		if(isset($tab_post['codepays_pers']) && $tab_post['codepays_pers']==''){ $erreur.="<br>"."Pays adresse pers. obligatoire";}
	}
	// etapes
	$nbetape=0;
	foreach($tab_post as $postkey=>$postval)
	{ if(substr($postkey,0,strlen('departlieu#'))=='departlieu#')
		{ $suffixe=substr($postkey,strpos($postkey,'#'));
			$departlieu=$postval;
			if($departlieu!='')
			{ $nbetape++;
				if($tab_post['arriveelieu'.$suffixe]=='' || $tab_post['arriveelieu'.$suffixe]=='' || $tab_post['departdate_jj'.$suffixe]=='' || $tab_post['arriveedate_jj'.$suffixe]=='' )
				{ $erreur.="<br>".'Au moins une &eacute;tape est incompl&egrave;te.';
					break;
				}
			}
		}
	}
	if($nbetape<2)
	{ $erreur.="<br>"."Il faut au moins deux lignes d&eacute;part-arriv&eacute;e (A/R).";
	}
	// nombres
	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $tab_post[$champ_numerique]=str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique]));
			if($tab_post[$champ_numerique]!='' && !is_numeric($tab_post[$champ_numerique]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']))
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else
					{ $tab_post[$champ_numerique]=sprintf($tab['string_format'],$tab_post[$champ_numerique]);
					}
				}
				else
				{ if(isset($tab['max_length']) && strlen($tab_post[$champ_numerique])>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
				}
			}
		}
	}
	// Dates y compris departdate_jj#$numetape,arriveedate_jj#$numetape 
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(substr($champ_date,0,strlen('departdate#'))=='departdate#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='departdate';
		}
		if(substr($champ_date,0,strlen('arriveedate#'))=='arriveedate#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='arriveedate';
		}
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	 
	foreach($tab_champs_heure_mn as $champ_heure_mn=>$tab)
	{ if(substr($champ_heure_mn,0,strlen('departheure#'))=='departheure#')
		{ $suffixe=substr($champ_date,strpos($champ_heure_mn,'#'));
			$champ_heure_mn=='departheure';
		}
		if(substr($champ_heure_mn,0,strlen('arriveeheure#'))=='arriveeheure#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_heure_mn='arriveeheure';
		}

		if(isset($tab_post[$champ_heure_mn.'_hh'.$suffixe]) && isset($tab_post[$champ_heure_mn.'_mn'.$suffixe]))
		{ if(strlen($tab_post[$champ_heure_mn.'_hh'.$suffixe])==1)
			{ $tab_post[$champ_heure_mn.'_hh'.$suffixe]='0'.$tab_post[$champ_heure_mn.'_hh'.$suffixe];
			}
			if(strlen($tab_post[$champ_heure_mn.'_mn'.$suffixe])==1)
			{ $tab_post[$champ_heure_mn.'_mn'.$suffixe]='0'.$tab_post[$champ_heure_mn.'_mn'.$suffixe];
			}
			else if(strlen($tab_post[$champ_heure_mn.'_hh'.$suffixe])!=0 && strlen($tab_post[$champ_heure_mn.'_mn'.$suffixe])==0)
			{ $tab_post[$champ_heure_mn.'_mn'.$suffixe]='00';
			}
			$tab_champs_heure_mn[$champ_heure_mn]['hh']=$tab_post[$champ_heure_mn.'_hh'.$suffixe];
			$tab_champs_heure_mn[$champ_heure_mn]['mn']=$tab_post[$champ_heure_mn.'_mn'.$suffixe];
			if(!est_heure_mn($tab_champs_heure_mn[$champ_heure_mn]['hh'],$tab_champs_heure_mn[$champ_heure_mn]['mn']))
			{ $erreur.="<br>".$tab_champs_heure_mn[$champ_heure_mn]['lib'].' mal form&eacute;e : '.$tab_champs_heure_mn[$champ_heure_mn]['hh'].'H'.$tab_champs_heure_mn[$champ_heure_mn]['mn'];
			}
		}
	}
	return $erreur;	
}

function controle_form_dupliquer_mission(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_commandes_a_dupliquer=array();
	// chaines de caracteres
	$erreur.=checklongueur('nom',30,'Nom');
	$erreur.=checklongueur('prenom',20,'Prenom');
	$erreur.=checklongueur('adresse_pers',200,'Adresse personnelle');
	$erreur.=checklongueur('email',100,'Adresse mail');
	$erreur.=checklongueur('tel',20,'T&eacute;l.');
	$erreur.=checklongueur('adresse_admin',200,'Adresse administrative');
	
	if($tab_post['nom']==''){ $erreur.="<br>"."Nom obligatoire.";}
	if($tab_post['prenom']==''){ $erreur.="<br>"."Pr&eacute;nom obligatoire.";}
	if($tab_post['date_naiss_jj']==''){ $erreur.="<br>"."Date de naissance obligatoire.";}
	if($tab_post['codecatmissionnaire']==''){ $erreur.="<br>"."Cat&eacute;gorie obligatoire.";}
	if(isset($tab_post['adresse_pers']) && $tab_post['adresse_pers']=='' && isset($tab_post['adresse_admin']) && $tab_post['adresse_admin']==''){ $erreur.="<br>"."Au moins une adresse obligatoire.";}
	if(isset($tab_post['adresse_pers']) && $tab_post['adresse_pers']!='')
	{ if(isset($tab_post['ville_pers']) && $tab_post['ville_pers']==''){ $erreur.="<br>"."Ville adresse pers. obligatoire";}
		if(isset($tab_post['codepays_pers']) && $tab_post['codepays_pers']==''){ $erreur.="<br>"."Pays adresse pers. obligatoire";}
	}
	foreach($tab_post as $postkey=>$postval)
	{ $posdiese=strpos($postkey,'#');
		if($posdiese!==false)
		{ if(substr($postkey,0,$posdiese)=="codecommande")
			{ $tab_commandes_a_dupliquer[substr($postkey,$posdiese+1)]['codecommande']=substr($postkey,$posdiese+1);
			}
			else if(substr($postkey,0,$posdiese)=="libfournisseur")
			{ $tab_commandes_a_dupliquer[substr($postkey,$posdiese+1)]['libfournisseur']=$postval;
			}
		}
	}
	foreach($tab_commandes_a_dupliquer as $codecommande=>$une_commande_a_dupliquer)
	{ if(isset($une_commande_a_dupliquer['codecommande']))
		{	if(isset($une_commande_a_dupliquer['libfournisseur']) && $une_commande_a_dupliquer['libfournisseur']=='')
			{ $erreur.="<br>Commande ".$codecommande." fournisseur obligatoire";
			}
		}
	}
	$tab_post['tab_commandes_a_dupliquer']=$tab_commandes_a_dupliquer;
	return $erreur;	
}

function controle_form_dupliquer_commande(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
	// chaines de caracteres
	$erreur.=checklongueur('libfournisseur',100,'Fournisseur');
	
	if($tab_post['codereferent']==''){ $erreur.="<br>"."Demandeur obligatoire.";}
	if($tab_post['libfournisseur']==''){ $erreur.="<br>"."Fournisseur obligatoire.";}
	
	// Dates
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}

	return $erreur;	
}

function controle_form_contrateotp(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$erreur.=checklongueur('noteeotp',200,'Note eotp');
	$tab_champs_date=$tab_controle_et_format['tab_champs_date'];
  // acronyme obligatoire 
	$tab_post['acronyme']=trim($tab_post['acronyme']);
	if($tab_post['acronyme']=='')
	{ $erreur.="<br>L'acronyme ne peut pas etre vide";
	}
  // libelle court eotp obligatoire 
	$tab_post['libcourteotp']=trim($tab_post['libcourteotp']);
	if($tab_post['codeeotp_a_creer_ou_modifier']!='' && $tab_post['libcourteotp']=='')
	{ $erreur.="<br>Le libelle eotp ne peut pas etre vide";
	}
	// verif pas de doublon sur libcourteotp
	$query="select * from eotp where libcourteotp=".GetSQLValueString($tab_post['libcourteotp'], "text").
				" and codeeotp<>".GetSQLValueString($tab_post['codeeotp_a_creer_ou_modifier'], "text");
	$rs=mysql_query($query);
	if($row_rs=mysql_fetch_assoc($rs))
	{ $erreur.='<br>Doublon libelle eotp !';
	}
	// Dates
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}

	return $erreur;
}

function controle_form_eotp_source_masse(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_numerique=isset($tab_controle_et_format['tab_champs_numerique'])?$tab_controle_et_format['tab_champs_numerique']:array();
	$tab_champs_date=isset($tab_controle_et_format['tab_champs_date'])?$tab_controle_et_format['tab_champs_date']:array();
	$erreur.=(($tab_post['eotp_ou_source']=='source' && $tab_post['libcourt']=='' && $tab_post['codetypesource']=='')?'Type de source et libell&eacute; vides':'');
	$erreur.=checklongueur('noteeotp',200,'Note');
	// nombres
	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $tab_post[$champ_numerique]=str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique]));
			if($tab_post[$champ_numerique]!='' && !is_numeric($tab_post[$champ_numerique]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']))
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else
					{ $tab_post[$champ_numerique]=sprintf($tab['string_format'],$tab_post[$champ_numerique]);
					}
				}
				else
				{ if(isset($tab['max_length']) && strlen($tab_post[$champ_numerique])>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
				}
			}
		}
	}
	// Dates
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(substr($champ_date,0,strlen('dateoperation#'))=='dateoperation#')
		{ $suffixe=substr($champ_date,strpos($champ_date,'#'));
			$champ_date='dateoperation';
		}
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_post[$champ_date.$suffixe]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_post[$champ_date.$suffixe]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_post[$champ_date.$suffixe]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_post[$champ_date.$suffixe]['jj'],$tab_post[$champ_date.$suffixe]['mm'],$tab_post[$champ_date.$suffixe]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_post[$champ_date.$suffixe]['jj'].'/'.$tab_post[$champ_date.$suffixe]['mm'].'/'.$tab_post[$champ_date.$suffixe]['aaaa'];
			}
		}
	}

	return $erreur;
}


function controle_form_sujet(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_numerique=isset($tab_controle_et_format['tab_champs_numerique'])?$tab_controle_et_format['tab_champs_numerique']:array();
	$tab_champs_date=isset($tab_controle_et_format['tab_champs_date'])?$tab_controle_et_format['tab_champs_date']:array();
	
	$erreur.=checklongueur('autredir1',50,'Autre directeur (1er)');
	$erreur.=checklongueur('autredir1mail',100,'Autre directeur mail (1er)');
	$erreur.=checklongueur('autredir2',50,'Autre directeur (2&egrave;me)');
	$erreur.=checklongueur('autredir2mail',100,'Autre directeur mail (2&egrave;me)');
	$erreur.=checklongueur('titre_fr',300,'Titre fran&ccedil;ais');
	$erreur.=checklongueur('titre_en',300,'Titre anglais');
	$erreur.=checklongueur('descr_fr',6400,'Description fran&ccedil;ais');
	$erreur.=checklongueur('descr_en',6400,'Description anglais');
	$erreur.=checklongueur('motscles_fr',100,'Mots cles fran&ccedil;ais');
	$erreur.=checklongueur('motscles_en',100,'Mots cles fran&ccedil;ais');
	$erreur.=checklongueur('financement_fr',100,'Financement fran&ccedil;ais');
	$erreur.=checklongueur('financement_en',100,'Financement fran&ccedil;ais');
	$erreur.=checklongueur('ref_publis',100,'Ref publis');
	$erreur.=checklongueur('ref_publis_ext',100,'Ref publis ext');
	$erreur.=checklongueur('conditions_fr',2000,'Conditions fran&ccedil;ais');
	$erreur.=checklongueur('conditions_en',2000,'Conditions anglais');	
	$erreur.=checklongueur('avis_motive_encadrant_zrr',300,'Avis motiv&eacute;');	
	if(isset($tab_post['descr_fr']) && strlen($tab_post['descr_fr'])<800)
	{ $erreur.='Description : 800 caract&egrave;res minimum';
	}
	
 	//champs obligatoires
	if(isset($tab_post['codedir#1']) && $tab_post['codedir#1']==''){ $erreur.="<br>"."1er encadrant obligatoire";}
	if(isset($tab_post['datedeb_sujet_jj']) && $tab_post['datedeb_sujet_jj']==''){ $erreur.="<br>"."Date debut obligatoire";}
	if(isset($tab_post['datefin_sujet_jj']) && $tab_post['datefin_sujet_jj']==''){ $erreur.="<br>"."Date fin obligatoire";}
	if(isset($tab_post['codetypestage']) && $tab_post['codetypestage']==''){ $erreur.="<br>"."Type de stage obligatoire";}
	if(isset($tab_post['titre_fr']) && $tab_post['titre_fr']==''){ $erreur.="<br>"."Titre francais obligatoire";}
	if(isset($tab_post['financement_fr']) && $tab_post['financement_fr']=='' && isset($tab_post['codetypesujet']) && ($tab_post['codetypesujet']=='03')){ $erreur.="<br>"."Financement francais obligatoire";}
	if(isset($tab_post['codetypesujet']) && ($tab_post['codetypesujet']!='02' || ($tab_post['codetypesujet']=='02' && isset($tab_post['codetypestage']) && $tab_post['codetypestage']=='01')))
	{ if(isset($tab_post['titre_en']) && $tab_post['titre_en']==''){ $erreur.="<br>"."Titre anglais obligatoire";}
	}
	
	// nombres
	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $tab_post[$champ_numerique]=str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique]));
			if($tab_post[$champ_numerique]!='' && !is_numeric($tab_post[$champ_numerique]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']))
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else
					{ $tab_post[$champ_numerique]=sprintf($tab['string_format'],$tab_post[$champ_numerique]);
					}
				}
				else
				{ if(isset($tab['max_length']) && strlen($tab_post[$champ_numerique])>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
				}
			}
		}
	}
	// Dates
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	$champ_zrr_obligatoire=false;//05/09/2014
	if($tab_post['datedeb_sujet_aaaa'].'/'.$tab_post['datedeb_sujet_mm'].'/'.$tab_post['datedeb_sujet_jj']>=$GLOBALS['date_zrr_obligatoire'])
	{ if(isset($tab_post['codedomainescientifique1']) && $tab_post['codedomainescientifique1']==''){ $erreur.="<br>"."Domaine scientifique obligatoire";}
		if(isset($tab_post['codedisciplinescientifique1']) && $tab_post['codedisciplinescientifique1']==''){ $erreur.="<br>"."Discipline scientifique obligatoire";}
	}

	return $erreur;
}

function controle_form_projet(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_numerique=isset($tab_controle_et_format['tab_champs_numerique'])?$tab_controle_et_format['tab_champs_numerique']:array();
	$tab_champs_date=isset($tab_controle_et_format['tab_champs_date'])?$tab_controle_et_format['tab_champs_date']:array();
	
	$erreur.=checklongueur('titrecourt',50,'Description');
	$erreur.=checklongueur('titre',200,'Intitul&eacute;');
	$erreur.=checklongueur('partenaires',500,'Partenaires');
	$erreur.=checklongueur('descr',4000,'Description');
	$erreur.=checklongueur('specificites',1000,'Sp&eacute;cificit&eacute;s');
	$erreur.=checklongueur('note',1000,'Note');

 	//champs obligatoires
	if(isset($tab_post['titre']) && $tab_post['titre']==''){ $erreur.="<br>"."Intitul&eacute; obligatoire";}
	if(isset($tab_post['codeclassif']) && $tab_post['codeclassif']==''){ $erreur.="<br>"."Contexte du d&eacute;pot obligatoire";}
	if(isset($tab_post['codeimplication']) && $tab_post['codeimplication']==''){ $erreur.="<br>"."Niveau d'implication obligatoire";}
	if(isset($tab_post['partenaires']) && $tab_post['partenaires']==''){ $erreur.="<br>"."Partenaire obligatoire";}
	// nombres
	foreach($tab_champs_numerique as $champ_numerique=>$tab)
	{ if(isset($tab_post[$champ_numerique]))
		{ $tab_post[$champ_numerique]=str_replace(' ','',str_replace(',','.',$tab_post[$champ_numerique]));
			if($tab_post[$champ_numerique]!='' && !is_numeric($tab_post[$champ_numerique]))
			{ $erreur.="<br>Champ ".$tab['lib']." non num&eacute;rique";
			}
			else
			{ if(isset($tab['string_format']))
				{ if(isset($tab['max_length']) && strlen(sprintf($tab['string_format'],$tab_post[$champ_numerique]))>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
					else
					{ $tab_post[$champ_numerique]=sprintf($tab['string_format'],$tab_post[$champ_numerique]);
					}
				}
				else
				{ if(isset($tab['max_length']) && strlen($tab_post[$champ_numerique])>$tab['max_length'])
					{ $erreur.="<br>"."Longueur du champ '".$tab['lib']."' sup&eacute;rieure &agrave; ".$tab['max_length'];
					}
				}
			}
		}
	}
	// Dates
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	return $erreur;
}

function controle_form_actu(&$tab_post,$tab_controle_et_format)
{ $erreur="";
	$tab_champs_date=isset($tab_controle_et_format['tab_champs_date'])?$tab_controle_et_format['tab_champs_date']:array();
	$tab_champs_heure_mn=$tab_controle_et_format['tab_champs_heure_mn'];
	//$tab_champs_numerique=isset($tab_controle_et_format['tab_champs_numerique'])?$tab_controle_et_format['tab_champs_numerique']:array();
	// chaines de caracteres
	$erreur.=checklongueur('lieu',100,'Lieu');
	$erreur.=checklongueur('intervenants',500,'Intervenants');
	$erreur.=checklongueur('typemanifestation',30,'Description');
	$erreur.=checklongueur('titre_fr',500,'Intitul&eacute;');
	//$erreur.=checklongueur('descr_fr',2000,'Description');
	
 	//champs obligatoires
	if($tab_post['titre_fr']==''){ $erreur.="<br>"."Intitul&eacute; obligatoire";}
	if($tab_post['datedeb_actu_aaaa']==''){ $erreur.="<br>"."Date d&eacute;but obligatoire";}
	// Dates
	foreach($tab_champs_date as $champ_date=>$tab)
	{ $suffixe='';
		if(isset($tab_post[$champ_date.'_jj'.$suffixe]))
	  { if(strlen($tab_post[$champ_date.'_jj'.$suffixe])==1)
			{ $tab_post[$champ_date.'_jj'.$suffixe]='0'.$tab_post[$champ_date.'_jj'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_mm'.$suffixe])==1)
			{ $tab_post[$champ_date.'_mm'.$suffixe]='0'.$tab_post[$champ_date.'_mm'.$suffixe];
			}
			if(strlen($tab_post[$champ_date.'_aaaa'.$suffixe])==2)
			{ $tab_post[$champ_date.'_aaaa'.$suffixe]='20'.$tab_post[$champ_date.'_aaaa'.$suffixe];
			}
			$tab_champs_date[$champ_date]['jj']=$tab_post[$champ_date.'_jj'.$suffixe];
			$tab_champs_date[$champ_date]['mm']=$tab_post[$champ_date.'_mm'.$suffixe];
			$tab_champs_date[$champ_date]['aaaa']=$tab_post[$champ_date.'_aaaa'.$suffixe];
			if(!est_date($tab_champs_date[$champ_date]['jj'],$tab_champs_date[$champ_date]['mm'],$tab_champs_date[$champ_date]['aaaa']))
			{ $erreur.="<br>".$tab_champs_date[$champ_date.$suffixe]['lib'].' mal form&eacute;e : '.$tab_champs_date[$champ_date]['jj'].'/'.$tab_champs_date[$champ_date]['mm'].'/'.$tab_champs_date[$champ_date]['aaaa'];
			}
		}
	}
	foreach($tab_champs_heure_mn as $champ_heure_mn=>$tab)
	{ if(isset($tab_post[$champ_heure_mn.'_hh']) && isset($tab_post[$champ_heure_mn.'_mn']))
		{ if(strlen($tab_post[$champ_heure_mn.'_hh'])==1)
			{ $tab_post[$champ_heure_mn.'_hh']='0'.$tab_post[$champ_heure_mn.'_hh'];
			}
			if(strlen($tab_post[$champ_heure_mn.'_mn'])==1)
			{ $tab_post[$champ_heure_mn.'_mn']='0'.$tab_post[$champ_heure_mn.'_mn'];
			}
			else if(strlen($tab_post[$champ_heure_mn.'_mn'])==0 && strlen($tab_post[$champ_heure_mn.'_hh'])!=0)
			{ $tab_post[$champ_heure_mn.'_mn']='00';
			}
			$tab_champs_heure_mn[$champ_heure_mn]['hh']=$tab_post[$champ_heure_mn.'_hh'];
			$tab_champs_heure_mn[$champ_heure_mn]['mn']=$tab_post[$champ_heure_mn.'_mn'];
			if(!est_heure_mn($tab_champs_heure_mn[$champ_heure_mn]['hh'],$tab_champs_heure_mn[$champ_heure_mn]['mn']))
			{ $erreur.="<br>".$tab_champs_heure_mn[$champ_heure_mn]['lib'].' mal form&eacute;e : '.$tab_champs_heure_mn[$champ_heure_mn]['hh'].'H'.$tab_champs_heure_mn[$champ_heure_mn]['mn'];
			}
		}
	}

	return $erreur;
}

?>