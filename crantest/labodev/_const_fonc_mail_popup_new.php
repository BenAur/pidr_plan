<?php 

function message_action_individu($row_rs_individu,$codeuser,$tab_roleuser,$action)// action supprimer dossier, invalider
{ /* destinataires de message : 
  'referent' : referent, gesttheme, resptheme, srhue si pas ue
	'srhue' : referent, gesttheme, resptheme1,...
	'theme' : referent, gesttheme, resptheme1,..., srh
	'srh' : gesttheme, resptheme1,..., du
	'du' : referent, gesttheme, resptheme1,..., srh
  */
  // le user codeuser n'est pas destinataire (&eacute;ventuel) du message envoy&eacute; a certains autres acteurs
	$destinataires='';
	$message='';
	$tab_acteurs=get_tab_individu_acteurs($row_rs_individu);
	$tab_destinataires=array();
	$tab_coderole=array();
	$tab_statutvisa=get_statutvisa();//referent=>01, srhu...tous les roles dont sii, adminfin,...
	// libelles longs des roles acteurs
	$tab_roles_liblong=get_roles_liblong();

	$tab_individu_visas_apposes=get_individu_visas($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$tab_statutvisa);
	if(in_array($tab_statutvisa['theme'],$tab_individu_visas_apposes))
	{ $tab_coderole=array('referent','theme','gesttheme');
	}
	else if(in_array($tab_statutvisa['referent'],$tab_individu_visas_apposes))
	{ if($action=="valider")// accord fsd : resptheme pas destinataire
		{ $tab_coderole=array('referent','gesttheme');
		}
		else
		{ $tab_coderole=array('referent','theme','gesttheme');
		}
	}
	if(in_array($tab_statutvisa['referent'],$tab_individu_visas_apposes))//srhue et admingestfin ont un message si au minimum visa referent
	{ $tab_coderole[]='srhue';
		$tab_coderole[]='admingestfin';
	}
	
	$tab_codeacteur=array();
	foreach($tab_coderole as $coderole)
	{ foreach($tab_acteurs as $coderoleacteur=>$tab_un_acteur)
		{ if($coderoleacteur==$coderole)
			{ foreach($tab_un_acteur as $i=>$tab_infouser)
				{ $codeacteur=$tab_infouser['codeindividu'];
					if(!in_array($codeacteur,$tab_codeacteur))// pas de doublons dans les destinataires : plusieurs roles eventuels
					/* { $tab_destinataire_roles[$codeacteur].=', '.$tab_roles_liblong[$coderoleacteur];
					}
					else */
					{ $tab_codeacteur[]=$codeacteur;
						$tab_destinataire_roles[$codeacteur]='- '.$tab_infouser['prenom'].' '.$tab_infouser['nom']/* .' : '.$tab_roles_liblong[$coderoleacteur] */;
					}
				}
			}
		}
	}
	switch ($action)
	{	case "invalider" :
			$message.='<p align="center"><b>Cette action invalide tous les visas du dossier de '.
								 $row_rs_individu['libciv_fr']." ".$row_rs_individu['prenom'].
								 " ".$row_rs_individu['nom']." pour le s&eacute;jour concern&eacute;.</b>"."</p>";
			$message.="La proc&eacute;dure d&rsquo;accueil devra, le cas &eacute;ch&eacute;ant, &ecirc;tre enti&egrave;rement r&eacute;appliqu&eacute;e."."<br>";
			break;
		case "supprimer" :
			$supprindividu=false;
			$rs=mysql_query("select * from individusejour  where codeindividu=".GetSQLValueString($row_rs_individu['codeindividu'], "text")) or die(mysql_error());
			if(mysql_num_rows($rs)==1)// un seul sejour : suppression de l'individu et du séjour si $supprsejour
			{ $supprindividu=true;
			}
			$message.='<p align="center"><b>Cette action supprime '.($supprindividu?"le dossier":"le s&eacute;jour")." de ".
								 $row_rs_individu['libciv_fr']." ".$row_rs_individu['prenom'].
								 " ".$row_rs_individu['nom']."</b></p>";
			mysql_free_result($rs);
			break;
		case "valider" :
		  $message.='<p align="center"><b>Cette action appose le visa &laquo; fsd &raquo; au dossier de '.
					   $row_rs_individu['libciv_fr']." ".$row_rs_individu['prenom'].
					   " ".$row_rs_individu['nom'].".</b></p>";
			/*$message.="Un mail va &ecirc;tre envoy&eacute &agrave; :"."<br>";
			 foreach($tab_destinataire_roles as $codeacteur=>$ligne_destinataire)
			{ $message.=addslashes($ligne_destinataire)."<br>";
			} */
			/* if(isset($row_rs_individu['texte_attente_sujet']) && $row_rs_individu['texte_attente_sujet']!='')
			{ $message.="Le message pr&eacute;cisera : ".addslashes($row_rs_individu['texte_attente_sujet']);
			} */

			//$message.=str_replace("\\n","<br>",popup_validation_individu($row_rs_individu,$codeuser,$tab_roleuser,'02',false,false));
			break;
		default :
			break;
	}
	if(!empty($tab_destinataire_roles)/*  && $action!="valider" */)
	{ $message.="Un mail sera envoy&eacute aux acteurs concern&eacute;s par ce dossier &agrave; ce stade de la proc&eacute;dure :"."<br>";
		foreach($tab_destinataire_roles as $une_ligne_destinataire)
		{ $message.=$une_ligne_destinataire."<br>";
		}
	}
	return $message;
}

function mail_adminbd($level,$programme,$message)
{ $subject=html_entity_decode($level.' '.$programme);
	$message=html_entity_decode($message);
	$from = $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$to=$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	$replyto=$to;
	list($headers,$message_html_txt)=mime_message(array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject,'Message'=>$message));
	//$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
	/* $mime = new Mail_mime("\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);

	$headers = $mime->headers($headers); */
	
	$erreur=""; 
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message_html_txt);//envoimail($tab_destinataires, $headers, $message);
	}
	else
	{ $erreur=$message;
	}
	return $erreur;	
}
function mail_action_individu($row_rs_individu,$codeuser,$action)
{ /* destinataires de message : 
  'referent' : referent, gesttheme, resptheme, srhue si pas ue
	'srhue' : referent, gesttheme, resptheme1,...
	'theme' : referent, gesttheme, resptheme1,..., srh
	'srh' : gesttheme, resptheme1,..., du
	'du' : referent, gesttheme, resptheme1,..., srh
  */
  // le user codeuser n'est pas destinataire (&eacute;ventuel) du message envoy&eacute; a certains autres acteurs
	$destinataires='';
	$message='';
	$tab_acteurs=get_tab_individu_acteurs($row_rs_individu);
	$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
	$tab_destinataires=array();
	$tab_coderole=array();
	$tab_statutvisa=get_statutvisa();//referent=>01, srhu...tous les roles dont sii, adminfin,...
	// libelles longs des roles acteurs
	$tab_roles_liblong=get_roles_liblong();

	$tab_individu_visas_apposes=get_individu_visas($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$tab_statutvisa);
	/* if(in_array($tab_statutvisa['du'],$tab_individu_visas_apposes) || in_array($tab_statutvisa['srh'],$tab_individu_visas_apposes))
	{ $tab_coderole=array('referent','theme','gesttheme','srh','du','admingestfin');
	}
	else  */
	if(in_array($tab_statutvisa['theme'],$tab_individu_visas_apposes))
	{ $tab_coderole=array('referent','theme','gesttheme','admingestfin');
	}
	else if(in_array($tab_statutvisa['referent'],$tab_individu_visas_apposes))
	{ $tab_coderole=array('referent','theme','gesttheme','admingestfin');
	}
	if(in_array($tab_statutvisa['referent'],$tab_individu_visas_apposes))
	{ $tab_coderole[]='srhue';
		$tab_coderole[]='admingestfin';
	}
	$to="";
	$first=true;
	$tab_codeacteur=array();
	foreach($tab_coderole as $coderole)
	{ foreach($tab_acteurs as $coderoleacteur=>$tab_un_acteur)
		{ if($coderoleacteur==$coderole)
			{ foreach($tab_un_acteur as $i=>$tab_infouser)
				{ if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
					{ $tab_mail_unique[strtolower($tab_infouser['email'])]=$tab_infouser['email'];
						$to.=($first?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom'].' <'.$tab_infouser['email'].'>';
						$first=false;
					}
				}
			}
		}
	}
	//le user expediteur s'il n'est pas deja dans $to
	$tab_infouser=get_info_user($codeuser);
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}
	

	switch ($action)
	{	case "invalider" :
			$mot_action="Invalidation";
			$phrase_action="Le dossier n&deg; ".$row_rs_individu['codeindividu']." a &eacute;t&eacute; invalid&eacute;";
			break;
		case "supprimer" :
			$rs=mysql_query("select * from individusejour  where codeindividu=".GetSQLValueString($row_rs_individu['codeindividu'], "text")) or die(mysql_error());
			if(mysql_num_rows($rs)==1)// un seul sejour : suppression de l'individu et du séjour
			{ $mot_action="suppression";
				$phrase_action="Le dossier n&deg; ".$row_rs_individu['codeindividu']." a &eacute;t&eacute; supprim&eacute;";
			}
			else
			{ $mot_action="Suppression du s&eacute;jour";
				$phrase_action="Le s&eacute;jour du dossier n&deg; ".$row_rs_individu['codeindividu']." a &eacute;t&eacute; supprim&eacute;";
			}
			mysql_free_result($rs);
			break;
		default :
			break;
	}
	$subject="Pour information : ".$mot_action." du dossier de ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom'];

	$message="";
	$message.="Bonjour,\n\n";
	$message.=$phrase_action." sur le serveur 12+ par ".$tab_infouser['prenom']." ".$tab_infouser['nom'].".";
	$message.="\n\n (<b>".$mot_action."</b>)";
	$message.="\n".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom']." (".$row_rs_individu['libcorps'].")";
	$message.="\nS&eacute;jour du&nbsp;".aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/")."&nbsp;&nbsp;au ".aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour'],"/");
	$message.="\n".$GLOBALS['libcourt_theme_fr']." :&nbsp;".$row_rs_individu['theme'];
	$message.="\n\n";
	$message.="cordialement,";
	$message.="\n\n";
	$message.="Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
	
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	$subject=	html_entity_decode($subject);
	$from = $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $GLOBALS['webMaster']['nom'].'<'.$GLOBALS['webMaster']['email'].'>';
	
	//$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
	list($headers,$message_html_txt)=mime_message(array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject,'Message'=>$message));

 	/* //--------------- modifs pour mime
	//$text = $message;
	$message=nl2br($message);
	// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
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

	//fin mime */
 	$erreur=""; 
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message_html_txt);//envoimail($tab_destinataires, $headers, $message);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$message;
	}	
  if(isset($rs_individuaction))mysql_free_result($rs_individuaction);
	
	return $erreur;
}

function mail_validation_individu($row_rs_individu,$codeuser,$codevisa_a_apposer)
{ // $row_rs_individu['demander_autorisation'] est transmis dans gestionindividus : evite transmission des infos pour calculer demander_autorisation
	/* destinataires de message : 
  'referent' : referent, gesttheme, resptheme, srh
	'srhue' : referent, gesttheme, resptheme1,...
	'theme' : referent, gesttheme, resptheme1,..., srh
  */
  // le user codeuser n'est pas destinataire (&eacute;ventuel) du message envoy&eacute; a certains autres acteurs
	$tab_acteurs=get_tab_individu_acteurs($row_rs_individu);
	$tab_destinataires=array();
	$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
	$tab_statutvisa=get_statutvisa();//referent=>01, ...tous les roles dont sii, adminfin,...
	$codevisa_a_apposer_lib=array_search($codevisa_a_apposer,$tab_statutvisa);
	$texte_visa_appose="";
	$tab_statutvisa['gesttheme']='';
	$subject="";
	$msg="";
  foreach($tab_acteurs as $coderoleacteur=>$tab_acteurs_par_role)
  { foreach($tab_acteurs_par_role as $ieme_acteur=>$tab_info_un_acteur_du_role)
		{ $un_destinataire=array('codeacteur'=>$tab_info_un_acteur_du_role['codeindividu'],'prenomnom'=>$tab_info_un_acteur_du_role['prenom'].' '.
																	$tab_info_un_acteur_du_role['nom'],
																'email'=>$tab_info_un_acteur_du_role['email']);
			if($codevisa_a_apposer_lib=='referent')
			{ if($coderoleacteur=='referent' || $coderoleacteur=='gesttheme' || $coderoleacteur=='srhue' || $coderoleacteur=='theme' || $coderoleacteur=='admingestfin')
				{ $tab_destinataires[]=$un_destinataire;
					$texte_visa_appose="Pr&eacute;vision d'arriv&eacute;e";
					$subject="Pour information : ".strtolower($texte_visa_appose)." de ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom'];
					$msg="L'arriv&eacute;e au laboratoire de ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom']." est pr&eacute;vue le ".aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/")."."; 
				}
			}
			else if($codevisa_a_apposer_lib=='srhue')
			{ if($coderoleacteur=='referent' || $coderoleacteur=='gesttheme'  || $coderoleacteur=='srhue'  || $coderoleacteur=='admingestfin')
				{ $tab_destinataires[]=$un_destinataire;
					$texte_visa_appose="Autorisation d'acc&egrave;s au laboratoire";
					$subject="Pour information : ".strtolower($texte_visa_appose)." pour ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom'];
					$msg=$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom']." a obtenu son ".strtolower($texte_visa_appose)." &agrave; compter du ".aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/").".";
				}
			}
			else if($codevisa_a_apposer_lib=='theme')
			{ if($coderoleacteur=='referent' || $coderoleacteur=='gesttheme' || $coderoleacteur=='srhue' || $coderoleacteur=='theme' || $coderoleacteur=='admingestfin')
				{ $tab_destinataires[]=$un_destinataire;
					$texte_visa_appose="Arriv&eacute;e";
					$subject="Pour information : ".strtolower($texte_visa_appose)." de ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom'];
					$msg=$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom']." est arriv&eacute;".($row_rs_individu['codeciv']=="1"?"":"e")." au laboratoire le ".aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/").".";
				}
			}
		}
  }
	
	$tab_infouser=get_info_user($codeuser);
	if(isset($row_rs_individu['texte_attente_sujet']) && $row_rs_individu['texte_attente_sujet']!='')
	{ $subject.=" (".$row_rs_individu['texte_attente_sujet'].")";
	}
	$message="";
	$message.="Bonjour,<br><br>";
	$message.=$msg;
	$message.="<br><br>Dossier n&deg; ".$row_rs_individu['codeindividu']." - visa &quot;<b>".$texte_visa_appose."</b>&quot;"." appos&eacute; par ".$tab_infouser['prenom']." ".$tab_infouser['nom'].".";
	if($row_rs_individu['codelibcat']=='STAGIAIRE' && $row_rs_individu['codestageformationremunere']=='oui' && $codevisa_a_apposer_lib=='srhue')
	{ $message.='<br><font color="#FF0000">Stage gratifi&eacute;</font>';
	}
	$message.="<br>".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nom']." (".$row_rs_individu['libcorps'].")";
	$message.="<br>S&eacute;jour du&nbsp;".aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/")."&nbsp;&nbsp;au ".aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour'],"/");
	$message.="<br>".$GLOBALS['libcourt_theme_fr']." :&nbsp;".$row_rs_individu['theme'];
	$message.="<br>T&eacute;l. :&nbsp;".$row_rs_individu['tel']."&nbsp;&nbsp;&nbsp;&nbsp;Mail : ".$row_rs_individu['email'];
	$message.="<br>Lieu de travail : ".($row_rs_individu['liblieu']==''?$row_rs_individu['autrelieu']:$row_rs_individu['liblieu']);
	if(($row_rs_individu['codelibcat']=='STAGIAIRE' &&  $row_rs_individu['sujetstageobligatoire']=='oui')
		 || $row_rs_individu['codelibcat']=='DOCTORANT' 
		 || ($row_rs_individu['codelibcat']=='POSTDOC' && $row_rs_individu['codesujet']!='')
		 || ($row_rs_individu['codelibcat']=='EXTERIEUR' && isset($row_rs_individu['texte_attente_sujet']) && $row_rs_individu['texte_attente_sujet']!=''))
	{ $message.="<br>Sujet : ".$row_rs_individu['titresujet'];
		$message.=(isset($row_rs_individu['texte_attente_sujet']) && $row_rs_individu['texte_attente_sujet']!='')?'&nbsp;(<font color="#FF0000">'.$row_rs_individu['texte_attente_sujet'].'</font>)':'';
	}
	$message.="<br><br>";
	$message.="cordialement,";
	$message.="<br><br>";
	$message.="Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
	
	$subject=	html_entity_decode($subject);
	
	$from = $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $GLOBALS['webMaster']['nom'].'<'.$GLOBALS['webMaster']['email'].'>';
	
	$to="";
	$first=true;
	foreach($tab_destinataires as $un_destinataire=>$tab_un_destinataire)
	{ if(!array_key_exists(strtolower($tab_un_destinataire['email']),$tab_mail_unique) && est_mail($tab_un_destinataire['email']))
		{ $tab_mail_unique[strtolower($tab_un_destinataire['email'])]=$tab_un_destinataire['email'];
			$to.=($first?"":", ").$tab_un_destinataire['prenomnom'].' <'.$tab_un_destinataire['email'].'>';
			$first=false;
		}
	}
	//le user expediteur s'il n'est pas deja dans $to
	$tab_infouser=get_info_user($codeuser);
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}

	
	// apposition du visa theme : message accueil
	if($codevisa_a_apposer_lib=='theme')
	{ $query_rs_individuaction= "select * from individuaction".
															" where codeindividu=".GetSQLValueString($row_rs_individu['codeindividu'], "text").
															" and numsejour=".GetSQLValueString($row_rs_individu['numsejour'], "text")." and codelibaction='msgaccueil'";
		$rs_individuaction=mysql_query($query_rs_individuaction);
		if(mysql_num_rows($rs_individuaction)==0)
		{ if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
			{ $to.=", TEST cran-direction <".$GLOBALS['emailTest'].">, TEST ACMO <".$GLOBALS['emailTest'].">, TEST Serv. Info. <".$GLOBALS['emailTest'].">";//test
			}
			else
			{ $to.=", cran-direction <".$GLOBALS['emailDIRECTION'].">, ACMO <".$GLOBALS['emailACMO'].">, SID <".$GLOBALS['emailSID'].">";
			}
		}
	}
	
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}

	//$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
	list($headers,$message_html_txt)=mime_message(array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject,'Message'=>$message));
 /* //--------------- modifs pour mime
	// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'"styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
	$mime = new Mail_mime("\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);
	$headers = $mime->headers($headers); */

	//fin mime
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message_html_txt);//envoimail($tab_destinataires, $headers, $message);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$message;
	}
  if(isset($rs_individuaction))mysql_free_result($rs_individuaction);
	
	return $erreur;
}

function mail_validation_declaration_fsd($codeindividu,$numsejour,$codeuser,$_post_val_user)
{ // mail de dde de declaration fsd
	$tab_infouser=get_info_user($codeuser);
	$tab_mail_unique=array();
	$tab_destinataires=array();
	$mime = new Mail_mime("\n");
	
  // roles srh, admingestfin (provenant de) structure
  $rs=mysql_query("select codeindividu as coderesp,codelib from structureindividu,structure".
									" where structureindividu.codestructure=structure.codestructure".
									" and (codelib=".GetSQLValueString('srh', "text")." or codelib=".GetSQLValueString('admingestfin', "text").")".
									" and structureindividu.estresp='oui'") or die(mysql_error());
  $i=0;
	while($row_rs = mysql_fetch_assoc($rs))
  { $i++;
		$tab_destinataires[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
  }
  // role srhue pour le role srh
  /* if(array_key_exists('srh',$tab_destinataires))
  { list($i,$tab)=each($tab_destinataires['srh']);
		$tab_destinataires['srhue'][1]=$tab_destinataires['srh'][$i];
  } */
  if(isset($rs))mysql_free_result($rs);

	$query_rs_user= "select tel,fax,email,lieu.liblonglieu as liblieu from individu,lieu ".
									" where codeindividu=".GetSQLValueString($codeuser, "text").
									" and individu.codelieu=lieu.codelieu";
	$rs_user=mysql_query($query_rs_user);
	$row_rs_user=mysql_fetch_assoc($rs_user);
	$query_rs_individu=	"select civilite.libcourt_fr as libciv, if(nomjf='',nom,nomjf) as nompatronymique, prenom,numdossierzrr,codegesttheme from individu,individusejour,civilite".
											" where individu.codeciv=civilite.codeciv and individu.codeindividu=".GetSQLValueString($codeindividu,"text").
											" and individu.codeindividu=individusejour.codeindividu and numsejour=".GetSQLValueString($numsejour,"text");
	$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
	$row_rs_individu=mysql_fetch_assoc($rs_individu);

	$tab_destinataires['codegesttheme'][1]=get_info_user($row_rs_individu['codegesttheme']);
	//20161213
	$query_rs_individupj=	"select individupj.*,typepjindividu.* from individupj,typepjindividu".
												" where individupj.codetypepj=typepjindividu.codetypepj".
												" and individupj.codelibcatpj=typepjindividu.codelibcatpj".
												" and individupj.codelibcatpj=".GetSQLValueString('sejour', "text").
												" and (typepjindividu.codelibtypepj=".GetSQLValueString('cv', "text").
												"			 or typepjindividu.codelibtypepj=".GetSQLValueString('piece_identite', "text").
												"			 or typepjindividu.codelibtypepj=".GetSQLValueString('fsd_sujet', "text").
												"			 or typepjindividu.codelibtypepj=".GetSQLValueString('fsd_financement', "text").
												"			 or typepjindividu.codelibtypepj=".GetSQLValueString('fsd', "text").")".
												" and individupj.numcatpj=".GetSQLValueString($numsejour, "text").
												" and codeindividu=".GetSQLValueString($codeindividu, "text") or die(mysql_error());
	$rs_individupj=mysql_query($query_rs_individupj);

	$subject="Protection du Potentiel Scientifique et Technique de la Nation - ".$GLOBALS['acronymelabo']." UMR 7039 (".
						$row_rs_individu['numdossierzrr']." - ".$row_rs_individu['libciv']." ".$row_rs_individu['prenom']." ".$row_rs_individu['nompatronymique'].")";
	$message="";
	$message.="Monsieur ".$GLOBALS['fsd_contact']['prenomnom'].",";
	$message.="<br><br>Je vous prie de bien vouloir trouver ci-joint, le formulaire de demande accompagn&eacute; du CV, de la pi&egrave;ce d&rsquo;identit&eacute;, du sujet et des &eacute;l&eacute;ments de financement concernant ";
	$message.='<b>'.$row_rs_individu['libciv']." ".$row_rs_individu['prenom'].' '.strtoupper($row_rs_individu['nompatronymique']).'</b>';
	$message.="<br><br>Restant &agrave; votre disposition pour tout compl&eacute;ment d'information, ";
	$message.="veuillez agr&eacute;er, Monsieur ".$GLOBALS['fsd_contact']['prenomnom'].", mes salutations distingu&eacute;es.";
	$message.="<br><br>".$tab_infouser['prenom'].' '.$tab_infouser['nom'];
	$message.="<br>--";
	$message.="<br>".construitliblabo(array('appel'=>'mail_validation_declaration_fsd'));
	$message.="<br>".$row_rs_user['liblieu'];
	$message.="<br>T&eacute;l. : ".$row_rs_user['tel']."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fax : ".$row_rs_user['fax'];
	$message.="<br>Mail : ".$row_rs_user['email'];
	
	$subject=	html_entity_decode($subject);
	
	$from = $tab_infouser['prenom'].' '.$tab_infouser['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $tab_infouser['prenom'].' '.$tab_infouser['nom'].'<'.$tab_infouser['email'].'>';
	$to="";
	$to.=$GLOBALS['fsd_contact']['prenomnom'].' <'.$GLOBALS['fsd_contact']['email'].'>';

	$first=true;
	foreach($tab_destinataires as $un_role=>$tab_un_role)
	{ foreach($tab_un_role as $i=>$tab_un_destinataire)
		if(!array_key_exists(strtolower($tab_un_destinataire['email']),$tab_mail_unique) && est_mail($tab_un_destinataire['email']))
		{	$tab_mail_unique[strtolower($tab_un_destinataire['email'])]=$tab_un_destinataire['email'];
			$to.=($to=='')?"":", ".$tab_un_destinataire['prenom'].' '.$tab_un_destinataire['nom'].' <'.$tab_un_destinataire['email'].'>';
			$first=false;
		}
	}
	//le user expediteur s'il n'est pas deja dans $to
	$tab_infouser=get_info_user($codeuser);
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}


	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	
	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
 //--------------- modifs pour mime
// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	$mime->setHTMLBody($html_message);
	// 19022017
	$rs=mysql_query("select * from _accent_sans_accent");
	$row_rs=mysql_fetch_assoc($rs);
	$liste_accent=$row_rs['accent'];
	$liste_sans_accent=$row_rs['sans_accent'];
	while($row_rs_individupj=mysql_fetch_assoc($rs_individupj))
	{ $filename = explode('.', $row_rs_individupj['nomfichier']);
		$filenameext = $filename[count($filename)-1];
		$mime->addAttachment ($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/sejour/'.$numsejour.'/'.$row_rs_individupj['codetypepj'],
			 $GLOBALS['file_types_mime_array'][$filenameext], strtr($row_rs_individupj['nomfichier'],$liste_accent,$liste_sans_accent));
	}
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);
	$headers = $mime->headers($headers);
	//fin mime

	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message_html_txt);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br><br>'.$message;
	}

  if(isset($rs_user))mysql_free_result($rs_user);

	return $erreur;
}


function popup_validation_contrat($codecontrat,$codeuser)
{ $destinataires='';
	$message='';
	$tab_acteurs=array();
	$tab_destinataires=array();
	// roles du, sif, admingestfin (provenant de) structure
  $rs=mysql_query("select codeindividu as coderesp,codelib from structureindividu,structure".
									" where structureindividu.codestructure=structure.codestructure".
									" and (codelib=".GetSQLValueString('sif', "text")." or codelib=".GetSQLValueString('admingestfin', "text").
									") and structureindividu.estresp='oui'") or die(mysql_error());
	$i=0;
  while($row_rs = mysql_fetch_assoc($rs))
	{ $i++;
		$tab_acteurs[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
  }
  foreach($tab_acteurs as $coderoleacteur=>$tab_acteurs_par_role)
  { foreach($tab_acteurs_par_role as $ieme_acteur=>$tab_info_un_acteur_du_role)
		{ $un_destinataire=array('codeacteur'=>$tab_info_un_acteur_du_role['codeindividu'],'prenomnom'=>$tab_info_un_acteur_du_role['prenom'].' '.
																	$tab_info_un_acteur_du_role['nom'],
																'coderoleacteur'=>$coderoleacteur);
			$tab_destinataires[]=$un_destinataire;
		}
	}
	// libelles longs des roles acteurs
	$tab_roles_liblong=get_cmd_roles_liblong();

	//suppression des doublons de noms de destinataires et une ligne par destinataire avec son ou ses roles en libelle long
	$tab_destinataire_roles=array();
	foreach($tab_destinataires as $un_destinataire)
	{ if(array_key_exists($un_destinataire['codeacteur'],$tab_destinataire_roles))
		{ $tab_destinataire_roles[$un_destinataire['codeacteur']].=', '.$tab_roles_liblong[$un_destinataire['coderoleacteur'].($un_destinataire['coderoleacteur']=='sif'?"#1":"")];
		}
		else
		{ $tab_destinataire_roles[$un_destinataire['codeacteur']]='- '.$un_destinataire['prenomnom'].' : '.$tab_roles_liblong[$un_destinataire['coderoleacteur'].($un_destinataire['coderoleacteur']=='sif'?"#1":"")];
		}
	}
	
  $message.=addslashes("Cette action informe les destinataires que le contrat ".$codecontrat." fait partie de la base contrats.")."\\n";
	$message.="Un mail va &ecirc;tre envoy&eacute; &agrave; :"."\\n";
	foreach($tab_destinataire_roles as $codeacteur=>$ligne_destinataire)
	{ $message.=addslashes($ligne_destinataire)."\\n";
	}

	if(isset($rs))mysql_free_result($rs);
	return $message;
}

function mail_validation_contrat($codecontrat,$codeuser)
{ $destinataires='';
	$message='';
	$tab_acteurs=array();
	$tab_destinataires=array();
	$tab_mail_unique=array();
	// roles du, sif, admingestfin (provenant de) structure
  $query_rs="select codeindividu as coderesp,codelib from structureindividu,structure".
						" where structureindividu.codestructure=structure.codestructure".
						" and (codelib=".GetSQLValueString('sif', "text")." or codelib=".GetSQLValueString('admingestfin', "text")." or codelib=".GetSQLValueString('relint', "text").
						") and structureindividu.estresp='oui'";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$i=0;
  while($row_rs = mysql_fetch_assoc($rs))
	{ $i++;
		$tab_acteurs[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
  }
	
	$rs_contrat=mysql_query("select contrat.*, individu.nom, individu.prenom,libcourtorggest as liborggest,".
													" cont_type.libcourttype as libtype, cont_type.codetypecat, cont_classif.libcourtclassif as libclassif,libcourtsecteur as libsecteur,".
													" libcourtnivconfident as libnivconfident, libcourttypeconvention as libtypeconvention, libcourtorgfinanceur as liborgfinanceur".
													" from contrat, individu, cont_orggest,cont_type, cont_classif, cont_secteur, cont_nivconfident, cont_typeconvention,cont_orgfinanceur ".
													" where contrat.coderespscientifique=individu.codeindividu ".
													" and contrat.codeorggest=cont_orggest.codeorggest".
													" and contrat.codetype=cont_type.codetype".
													" and contrat.codeclassif=cont_classif.codeclassif".
													" and contrat.codesecteur=cont_secteur.codesecteur".
													" and contrat.codenivconfident=cont_nivconfident.codenivconfident".
													" and contrat.codetypeconvention=cont_typeconvention.codetypeconvention".
													" and contrat.codeorgfinanceur=cont_orgfinanceur.codeorgfinanceur".
													" and codecontrat=".GetSQLValueString($codecontrat, "text")) or die(mysql_error());
	$row_rs_contrat = mysql_fetch_assoc($rs_contrat);
	
	$tab_infouser=get_info_user($codeuser);
	$subject="Pour information : validation du contrat ".$codecontrat." (".substr($row_rs_contrat['ref_contrat'],0,30).")";
	$message.="Le contrat n&deg; ".$codecontrat." a &eacute;t&eacute; valid&eacute; sur le serveur 12+ par ".$tab_infouser['prenom']." ".$tab_infouser['nom'].".";
	$message.="<br><br><b>R&eacute;f&eacute;rence du contrat : </b>".$row_rs_contrat['ref_contrat'];
	$message.="<br><b>Du </b>".aaaammjj2jjmmaaaa($row_rs_contrat['datedeb_contrat'],'/')."&nbsp;<b>au</b> ".aaaammjj2jjmmaaaa($row_rs_contrat['datefin_contrat'],'/');
	$message.="<br><b>Organisme gestionnaire des cr&eacute;dits : </b>".$row_rs_contrat['liborggest']."&nbsp;&nbsp;<b>Financeur</b> : ".$row_rs_contrat['liborgfinanceur'];
	$message.="<br><b>Responsable scientifique : </b>".$row_rs_contrat['prenom'].' '.$row_rs_contrat['nom'];
	$message.="<br><b>Montant : </b>".$row_rs_contrat['montant_ht']." &euro;";
	$message.="<br><br><b>R&eacute;f&eacute;rence programme long : </b>".$row_rs_contrat['ref_prog_long'];
	//doctorant
	if($row_rs_contrat['codetypecat']=='01' && $row_rs_contrat['codedoctorant']!='')
	{ $query_rs= "SELECT concat(nom,' ',substring(prenom,1,1)) as nomprenomdoctorant,sujet.titre_fr as titre_these". 
							 " FROM individu,individusejour".
							 " left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
							 " left join sujet on individusujet.codesujet=sujet.codesujet".
							 " WHERE individu.codeindividu=individusejour.codeindividu".
							 " and individu.codeindividu=".GetSQLValueString($row_rs_contrat['codedoctorant'],"text");
		$rs=mysql_query($query_rs) or die(mysql_error());
		$row_rs=mysql_fetch_assoc($rs);
		$message.="<br><b>Doctorant</b> : ".$row_rs['nomprenomdoctorant'];
		$row_rs_contrat['sujet']=$row_rs['titre_these'];	
	}
	$message.="<br><b>Sujet</b> : ".$row_rs_contrat['sujet'];
	$message.="<br><b>Secteur</b> : ".$row_rs_contrat['libsecteur']."&nbsp;&nbsp;<b>Confidentialit&eacute;</b> : ".$row_rs_contrat['libnivconfident'].
						"&nbsp;&nbsp;<b>Type convention</b> : ".$row_rs_contrat['libtypeconvention'];
	$message.="<br><b>Type</b> : ".$row_rs_contrat['libtype']."&nbsp;&nbsp;<b>Classification</b> : ".$row_rs_contrat['libclassif'];
	$message.="<br><b>Partenaires</b> : ";
	$query_rs="select liblongpart". 
						" from contratpart,cont_part".
						" where contratpart.codepart=cont_part.codepart and codecontrat=".GetSQLValueString($codecontrat,"text").
						" order by numordre";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $message.=nl2br($row_rs['liblongpart']).' ; ';
	}
	$message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
	
	$subject=	html_entity_decode($subject);
	
 	$from = $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$to="";
	$first=true;
  foreach($tab_acteurs as $coderoleacteur=>$tab_acteurs_par_role)
  { foreach($tab_acteurs_par_role as $ieme_acteur=>$tab_info_un_acteur_du_role)//ecrasement de l'index dans $tab_destinataires pour mail unique
		{ if(!array_key_exists(strtolower($tab_info_un_acteur_du_role['email']),$tab_mail_unique) && est_mail($tab_info_un_acteur_du_role['email']))
			{ $tab_mail_unique[strtolower($tab_info_un_acteur_du_role['email'])]=$tab_info_un_acteur_du_role['email'];
				$to.=($first?"":", ").$tab_info_un_acteur_du_role['prenom'].' '.$tab_info_un_acteur_du_role['nom'].' <'.$tab_info_un_acteur_du_role['email'].'>';
				$first=false;
			}
		}
	}
	//le user expediteur s'il n'est pas deja dans $to
	$tab_infouser=get_info_user($codeuser);
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}

	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}

	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
 //--------------- modifs pour mime
// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
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

 	$erreur="";
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message_html_txt);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br><br>'.$message;
	}

	if(isset($rs_contrat))mysql_free_result($rs_contrat);
	if(isset($rs))mysql_free_result($rs);
	return $erreur;
}

function mail_validation_commande($row_rs_commande,$codeuser,$codevisa_a_apposer,$envoyer_mail_srh)
{ /* destinataires de message : 
  'referent' : referent, secrsite, resptheme ou respcontrat
	'theme' ou 'contrat' : referent, secrsite, resptheme1,...,sif
	'sif#1' : secrsite, sif (et srh si salaire)
	'secrsite' : sif, maif
	'sif#2' : referent, gesttheme, resptheme1,..., srh
  */
  // le user codeuser n'est pas destinataire (&eacute;ventuel) du message envoy&eacute; a certains autres acteurs
	$numaction='';//permet d'indiquer le numero d'action #1, #2 de sif
	$tab_destinataires=array();
	$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
	$tab_statutvisa=get_cmd_statutvisa();//referent=>01, ...tous les roles dont sii, adminfin,...
	$codevisa_a_apposer_lib=array_search($codevisa_a_apposer,$tab_statutvisa);
	$texte_visa_appose="";
	$codecommande=$row_rs_commande['codecommande'];
	$tab_commandeimputationbudget=array();
	$tab_acteurs=get_tab_cmd_acteurs($row_rs_commande);
	$texte_info_imputation="";
	$demandeur='';
	$nomp_missionnaire='';
	$texte_info_mission='';
	$message="";
	$subject="";
	$tab_contrat_a_viser=array();	
	$tab_commandeimputationbudget_du_codeuser_de_la_commande=array();
	$tab_commandeimputationbudget_statutvisa_de_la_commande=array();
	//salaires
	if(($row_rs_commande['codenature']=='06' || $row_rs_commande['codenature']=='12') && $codevisa_a_apposer_lib=='sif#1' && $envoyer_mail_srh)//codenature salaire 06 et 12
	{ $rs=mysql_query("select codeindividu as coderesp,codelib from structureindividu,structure".
										" where structureindividu.codestructure=structure.codestructure".
										" and (codelib=".GetSQLValueString('srh', "text").
										" or codelib=".GetSQLValueString('admingestfin', "text").
										/* " or codelib=".GetSQLValueString('gestperscontrat', "text"). */") and structureindividu.estresp='oui'") or die(mysql_error());


		$i=0;
		while($row_rs = mysql_fetch_assoc($rs))
		{ $i++;
			$tab_acteurs[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
		}
		
	}
	
	$query="select nom as referentnom, prenom as referentprenom from individu ".
				 " where individu.codeindividu=".GetSQLValueString($row_rs_commande['codereferent'], "text");
	$rs=mysql_query($query) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $demandeur=$row_rs['referentprenom'].' '.$row_rs['referentnom'];
	}
	// modif 01/2014 ajout code mission sur visa sif#1
	if($row_rs_commande['codemission']!='')
	{ $texte_info_mission.='Mission <b>'.($codevisa_a_apposer_lib=='sif#1'?'('.$row_rs_commande['codemission'].')':'').'</b> : ';
		$query_rs_mission="select codeagent,nom, prenom, motif from mission where codemission=".GetSQLValueString($row_rs_commande['codemission'], "text");
		$rs_mission=mysql_query($query_rs_mission);
		if($row_rs_mission=mysql_fetch_assoc($rs_mission))
		{ $nomp_missionnaire=$row_rs_mission['nom']." ".substr($row_rs_mission['prenom'],0,1);
			$texte_info_mission.="<b>".$row_rs_mission['motif']."</b>";
			if($row_rs_mission['codeagent']=='')
			{ $texte_info_mission.="(<b>".$row_rs_mission['nom']." ".$row_rs_mission['prenom']."</b>)";
			}
		}
	}
	// 20/01/2014 plus d'une imput. virt.
	$viser_la_commande_totalement=true;
	if(($codevisa_a_apposer_lib=='theme' || $codevisa_a_apposer_lib=='contrat') && !$_SESSION['b_cmd_etre_admin'])
	{	//imputations de la commande
		$query_rs="select numordre,codecontrat from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0'";
		$rs=mysql_query($query_rs);
		if(mysql_num_rows($rs)>1)// plus d'une imputation	virtuelle	
		{	//$viser_la_commande_totalement=false;
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_commandeimputationbudget_de_la_commande[$row_rs['codecontrat']]=$row_rs['numordre'];
			}
			// imputations dont le codeuser est resp.
			$tab_commandeimputationbudget_du_codeuser_de_la_commande=array();
			$query_rs="select distinct commandeimputationbudget.codecontrat from  commandeimputationbudget,budg_contrat_source_vue,individu".
								" where commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
								" and commandeimputationbudget.codecontrat<>''".
								" and (			(individu.codeindividu = budg_contrat_source_vue.coderespscientifique and individu.codeindividu = ".GetSQLValueString($codeuser, "text").")".
								"				or  (budg_contrat_source_vue.coderespscientifique='' ".
														" and budg_contrat_source_vue.codecentrecout in (select codecentrecout from centrecouttheme,structureindividu".
																																						" where centrecouttheme.codestructure=structureindividu.codestructure".
																																						" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text").")".
														")".
								"			)".
								" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text");
			$rs=mysql_query($query_rs);
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_commandeimputationbudget_du_codeuser_de_la_commande[$row_rs['codecontrat']]=$row_rs['codecontrat'];
			}
			$query_rs="select codecontrat,codestatutvisa".
								" from commandeimputationbudget_statutvisa".
								" where codecommande=".GetSQLValueString($codecommande, "text")."and (codestatutvisa='02' or codestatutvisa='03')";
			$rs=mysql_query($query_rs);
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_commandeimputationbudget_statutvisa_de_la_commande[$row_rs['codecontrat']]=$row_rs['codestatutvisa'];
			}
			foreach($tab_commandeimputationbudget_de_la_commande as $uncodecontrat=>$numordre)
			{ if(array_key_exists($uncodecontrat,$tab_commandeimputationbudget_du_codeuser_de_la_commande) && !array_key_exists($uncodecontrat,$tab_commandeimputationbudget_statutvisa_de_la_commande))
				{	$tab_contrat_a_viser[$uncodecontrat]=$numordre;
				}
			}
			// toutes les imputations visas=on enleve celles deja visees et on verifie que celles de $tab_contrat_a_viser sont les dernieres 
			$query_rs="select codecontrat". 
								" from commandeimputationbudget".
								" where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0'".
								" and codecontrat not in (select codecontrat from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text").
																					" and (codestatutvisa='02' or codestatutvisa='03')".")";
			$rs=mysql_query($query_rs);
			while($row_rs=mysql_fetch_assoc($rs))//toutes les imputations ont le visa 'theme' ou 'contrat'
			{ $viser_la_commande_totalement=$viser_la_commande_totalement && array_key_exists($row_rs['codecontrat'],$tab_contrat_a_viser);
			}
		}
	}
	// fin 20/01/2014 plus d'une imput. virt.
	$query_rs="(SELECT commandeimputationbudget.codecommande, commandeimputationbudget.codecontrat as codecontrat,commandeimputationbudget.codeeotp as codeeotp,".
						" typecredit.codetypecredit,typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
						" centrecout.libcourt as libcentrecout,'' as libcentrecout_reel,'' as libcentrefinancier_reel,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
						" budg_contrat_source_vue.coderespscientifique,respscientifique.prenom,respscientifique.nom,acronyme as libcontrat,'' as libeotp,contrat_ou_source,'' as eotp_ou_source,".
						" virtuel_ou_reel,montantengage,montantpaye,commandeimputationbudget.numordre".
						" from commandeimputationbudget, typecredit,centrefinancier,centrecout,budg_contrat_source_vue,cmd_typesource,individu as respscientifique".
						" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
						" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
						" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
						" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat and budg_contrat_source_vue.coderespscientifique=respscientifique.codeindividu and budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource".
						" and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text").
						" order by virtuel_ou_reel,commandeimputationbudget.numordre".')'.
						" UNION".
						" (SELECT commandeimputationbudget.codecommande, commandeimputationbudget.codecontrat as codecontrat,commandeimputationbudget.codeeotp as codeeotp,".
						" typecredit.codetypecredit,typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
						" centrecout.libcourt as libcentrecout,centrecout_reel.libcourt as libcentrecout_reel,centrefinancier_reel.libcourt as libcentrefinancier_reel,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
						" budg_eotp_source_vue.coderespscientifique,respscientifique.prenom,respscientifique.nom,'' as libcontrat,libeotp,'' as contrat_ou_source,eotp_ou_source,".
						" virtuel_ou_reel,montantengage,montantpaye,commandeimputationbudget.numordre".
						" from commandeimputationbudget, typecredit,centrefinancier,centrecout,centrecout_reel,centrefinancier_reel,budg_eotp_source_vue,cmd_typesource,individu as respscientifique".
						" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
						" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
						" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
						" and commandeimputationbudget.virtuel_ou_reel='1' and commandeimputationbudget.codeeotp=budg_eotp_source_vue.codeeotp and budg_eotp_source_vue.coderespscientifique=respscientifique.codeindividu and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource".
						" and budg_eotp_source_vue.codecentrecout_reel=centrecout_reel.codecentrecout_reel".
						" and centrecout_reel.codecentrefinancier_reel=centrefinancier_reel.codecentrefinancier_reel".
						" and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text").
						" order by virtuel_ou_reel,commandeimputationbudget.numordre".')';
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ if($row_rs['virtuel_ou_reel']=='0') //demande
		{ if($row_rs['contrat_ou_source']=='contrat')
			{ $row_rs['libcontrat']=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'. - '.$row_rs['libcontrat'];
			}
			else // source
			{ /* $rs1=mysql_query( "select libcourt as libcentrecout_reel from budg_eotp_source_vue,centrecout_reel, budg_contrateotp_source_vue".
													" where budg_eotp_source_vue.codecentrecout_reel=centrecout_reel.codecentrecout_reel".
													" and budg_contrateotp_source_vue.codecontrat=".GetSQLValueString($row_rs['codecontrat'], "text").
													" and budg_contrateotp_source_vue.codeeotp=budg_eotp_source_vue.codeeotp") or die(mysql_error());
				$libcentrecout_reel='';
				if($row_rs1=mysql_fetch_assoc($rs1))
				{ $libcentrecout_reel=$row_rs1['libcentrecout_reel'];
				} */
				$tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																		'libsource'=>$row_rs['libcontrat'],'libcentrecout_reel'=>'',//$libcentrecout_reel
																		'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																		'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);//'CNRS-UL'
				$row_rs['libcontrat']=construitlibsource($tab_construitsource);
				$row_rs['libtypecredit']='CNRS-UL';
			}
		}
		else // reel
		{ if($row_rs['eotp_ou_source']=='eotp')
			{ $row_rs['libeotp']=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'. - '.$row_rs['libeotp'];
			}
			else
			{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																		'libsource'=>$row_rs['libeotp'],'libcentrecout_reel'=>$row_rs['libcentrecout_reel'],
																		'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																		'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
				$row_rs['libeotp']=construitlibsource($tab_construitsource);
			}
		}
		$tab_commandeimputationbudget[$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
	}
	if($codevisa_a_apposer_lib=='referent' || $codevisa_a_apposer_lib=='theme' || $codevisa_a_apposer_lib=='contrat')
	{ $virtuel_ou_reel='0';
	}
	else
	{ $virtuel_ou_reel='1';
	}
 	if(isset($tab_commandeimputationbudget[$virtuel_ou_reel]['01']['libtypecredit']))
	{ $tab_commandeimputationbudget_virtuel_ou_reel=$tab_commandeimputationbudget[$virtuel_ou_reel];
		$texte_info_imputation.='<table>';
		 
		$first=true;
		foreach($tab_commandeimputationbudget_virtuel_ou_reel as $numordre=>$une_commandeimputationbudget)
		{ $texte_info_imputation.='<tr><td nowrap>';
			if($first)
			{ $texte_info_imputation.='Imputation(s)';
				$first=false;
			}
			$texte_info_imputation.='</td>';
			$color="#000000";
			if($virtuel_ou_reel=='0')
			{ if(array_key_exists($une_commandeimputationbudget['codecontrat'],$tab_commandeimputationbudget_du_codeuser_de_la_commande))
				{ $color="#993399"; 
				}
				else if(array_key_exists($une_commandeimputationbudget['codecontrat'],$tab_commandeimputationbudget_statutvisa_de_la_commande))
				{ $color="#00CC00";
				}
			}
			$texte_info_imputation.='<td nowrap>Cr&eacute;dits : </td><td nowrap><font color="'.$color.'"><b>'.$une_commandeimputationbudget['libtypecredit'].'</b></font></td>';
			$texte_info_imputation.='<font color="'.$color.'">';
			$texte_info_imputation.='<td nowrap>Enveloppe : </td><td nowrap><font color="'.$color.'"><b>'.$une_commandeimputationbudget['libcentrecout'].'</b></font></td>';
			$texte_info_imputation.='';
			if($virtuel_ou_reel=='0')// contrat si virtuel
			{ $texte_info_imputation.='<td nowrap>Source/contrat : </td><td nowrap><font color="'.$color.'"><b>'.$une_commandeimputationbudget['libcontrat'].'</b></font></td>';
			}
			if($virtuel_ou_reel=='1')// eotp si reel
			{ $texte_info_imputation.='<td nowrap>&nbsp;&nbsp;&nbsp;Source/EOTP : </td><td nowrap><font color="'.$color.'"><b>'.$une_commandeimputationbudget['libeotp'].'</b></font></td><td>';
			}
			$texte_info_imputation.='';
			$texte_info_imputation.='<td nowrap>&nbsp;&nbsp;&nbsp;Montant : </td><td nowrap><font color="'.$color.'"><b>'.$une_commandeimputationbudget['montantengage'].'</b></font></td>';
			$texte_info_imputation.='</tr>';
		}
		$texte_info_imputation.='</table>';
		// sorti du for, le pointeur est sur la dernniere imputation reelle qui contient, comme les autres,  libcentrefinancier et reel
		if($codevisa_a_apposer_lib=='sif#1' || $codevisa_a_apposer_lib=='secrsite' || $codevisa_a_apposer_lib=='sif#2')
		{ $texte_info_imputation.="&nbsp;&nbsp;&nbsp;<br>Centre financier : <b>".$une_commandeimputationbudget['libcentrefinancier_reel']."</b>&nbsp;&nbsp;&nbsp;Centre de co&ucirc;t : <b>".$une_commandeimputationbudget['libcentrecout_reel']."</b>";
		}
	}

  foreach($tab_acteurs as $coderoleacteur=>$tab_acteurs_par_role)
  { foreach($tab_acteurs_par_role as $ieme_acteur=>$tab_info_un_acteur_du_role)
		{ $un_destinataire=array('codeacteur'=>$tab_info_un_acteur_du_role['codeindividu'],'prenomnom'=>$tab_info_un_acteur_du_role['prenom'].' '.
																	$tab_info_un_acteur_du_role['nom'],
																'email'=>$tab_info_un_acteur_du_role['email']);
			if($codevisa_a_apposer_lib=='referent')
			{	if(/* $coderoleacteur=='referent'  ||*/ $coderoleacteur=='theme' || $coderoleacteur=='contrat' || $coderoleacteur=='sif'  || $coderoleacteur=='secrsite' )
				{ $tab_destinataires[]=$un_destinataire;
					if($coderoleacteur=='sif');
					{ $numaction='#1';
					}
					$texte_visa_appose="Nouvelle commande";
				}
			}
			else if($codevisa_a_apposer_lib=='theme')
			{ if(/* $coderoleacteur=='referent' || $coderoleacteur=='secrsite' || $coderoleacteur=='theme' ||  */$coderoleacteur=='sif')
				{ $tab_destinataires[]=$un_destinataire;
					$texte_visa_appose='Visa'.($viser_la_commande_totalement?'':'&nbsp;partiel').'&nbsp;Resp. cr&eacute;dits';
					//$texte_visa_appose='Visa'.($viser_la_commande_totalement?'':'&nbsp;partiel').'&nbsp;'.$GLOBALS['libcourt_theme_fr'];
					if($coderoleacteur=='sif');
					{ $numaction='#1';
					}
				}
			}
			else if($codevisa_a_apposer_lib=='contrat')
			{ if(/* $coderoleacteur=='referent' || $coderoleacteur=='secrsite' || $coderoleacteur=='contrat' ||  */$coderoleacteur=='sif')
				{ $tab_destinataires[]=$un_destinataire;
					$texte_visa_appose='Visa'.($viser_la_commande_totalement?'':'&nbsp;partiel').'&nbsp;Resp. cr&eacute;dits';
					if($coderoleacteur=='sif');
					{ $numaction='#1';
					}
				}
			}
			else if($codevisa_a_apposer_lib=='sif#1')
			{ if($coderoleacteur=='sif' || $coderoleacteur=='secrsite' || $coderoleacteur=='srh'  || $coderoleacteur=='admingestfin'
				 /* || $coderoleacteur=='gestperscontrat' */)
				{ $tab_destinataires[]=$un_destinataire;
					if($coderoleacteur=='sif');
					{ $numaction='#1';
					}
					$texte_visa_appose="Visa Ing&eacute;nierie d'ex&eacute;cution budg&eacute;taire (centre de co&ucirc;t et EOTP)";
				}
			}
			else if($codevisa_a_apposer_lib=='secrsite')
			{ if($coderoleacteur=='sif'/*  || $coderoleacteur=='secrsite' */)
				{ $tab_destinataires[]=$un_destinataire;
					$numaction='#2';
				}
				$texte_visa_appose='Visa MIGO';
			}
			else if($codevisa_a_apposer_lib=='sif#2')
			{ if($coderoleacteur=='sif')
				{ $tab_destinataires[]=$un_destinataire;
					$numaction='#2';
				}
				$texte_visa_appose="Visa Ing&eacute;nierie d'ex&eacute;cution budg&eacute;taire (Paiement)";
			}
		}
  }

	$tab_infouser=get_info_user($codeuser);
	if($codevisa_a_apposer_lib=='referent')
	{ $subject.="URGENT - POUR VALIDATION -";
	}
	else if ($codevisa_a_apposer_lib=='contrat' || $codevisa_a_apposer_lib=='theme')
	{ $subject.="Pour information : validation de la";
	}
	else
	{ $subject.="Validation de la";
	}
	$subject.=" commande ".$codecommande." (".$row_rs_commande['objet'].")".($row_rs_commande['codemission']!=''?" - Mission de : ".$nomp_missionnaire.".":'');
	$message.="Bonjour,<br><br>";
	$message.="La commande n&deg; <b>".$codecommande."</b> a &eacute;t&eacute; valid&eacute;e sur le serveur 12+ par ".$tab_infouser['prenom']." ".$tab_infouser['nom'].".";
	$message.="<br><br> (<b>".$texte_visa_appose."</b>)".($row_rs_commande['estavoir']=='oui'?' <b>Avoir de commande</b>':'');
	$message.="<br><br>Demandeur : <b>".$demandeur."</b>";
	$message.="<br>".$texte_info_mission;
	$message.="<br>Objet : <b>".$row_rs_commande['objet']."</b>";
	if(($row_rs_commande['codenature']=='06' || $row_rs_commande['codenature']=='12') && $codevisa_a_apposer_lib=='sif#1')//codenature salaire 06 et 12
	{ $message.="<br>D&eacute;tail : <b>".nl2br($row_rs_commande['description'])."</b>";
	}
	$message.="<br>Fournisseur : <b>".$row_rs_commande['libfournisseur']."</b>";
	$message.="<br>".$texte_info_imputation;
	$message.=($codevisa_a_apposer_lib=='referent'?'<br><br>Le responsable de contrat/source de cr&eacute;dits est invit&eacute; &agrave; valider la commande sur le'.
																								'&nbsp;<a href="'.$GLOBALS['Serveur12+commande']['lien'].'">'.$GLOBALS['Serveur12+commande']['nom'].'</a>'.
																								'<br><br>Pour toute question relative &agrave; cette commande, veuillez adresser un mail &agrave; <a href="mailto:'.
																								$GLOBALS['Serveur12+commande']['emailretour'].'?subject=Commande%20'.$codecommande.'">Carole Courrier</a>'
																								:"");
	$message.="<br><br>cordialement,";
	$message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
	
	
	$subject=	html_entity_decode($subject);
	
 	$from = $GLOBALS['Serveur12+commande']['nom'].'<'.$GLOBALS['Serveur12+commande']['emailexpediteur'].'>';
	$replyto= $GLOBALS['Serveur12+commande']['nom'].'<'.$GLOBALS['Serveur12+commande']['emailretour'].'>';
 /*	$from = $GLOBALS['expediteur_commande']['nom'].'<'.$$GLOBALS['expediteur_commande']['email'].'>';
	$replyto= $GLOBALS['expediteur_commande']['nom'].'<'.$GLOBALS['expediteur_commande']['email'].'>';
	*/
	$to="";
	$first=true;
	foreach($tab_destinataires as $un_destinataire=>$tab_un_destinataire)
	{ if(!array_key_exists(strtolower($tab_un_destinataire['email']),$tab_mail_unique) && est_mail($tab_un_destinataire['email']))
		{ $tab_mail_unique[strtolower($tab_un_destinataire['email'])]=$tab_un_destinataire['email'];
			$to.=($first?"":", ").$tab_un_destinataire['prenomnom'].' <'.$tab_un_destinataire['email'].'>';
			$first=false;
		}
	}
	
	//le user expediteur s'il n'est pas deja dans $to
	$tab_infouser=get_info_user($codeuser);
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}
	
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}

	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
 //--------------- modifs pour mime
// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
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
 	$erreur="";
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message_html_txt);//envoimail($tab_destinataires, $headers, $message);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br><br>'.$message;
	}
	if(isset($rs_mission))mysql_free_result($rs_mission);
	if(isset($rs1))mysql_free_result($rs1);
	if(isset($rs))mysql_free_result($rs);
	return $erreur;
}


function mail_validation_commande_avoir($row_rs_commande,$codeuser)
{ /* destinataires de message : 
  'secrsite'
  */
	$codecommande=$row_rs_commande['codecommande'];
	$tab_infouser=get_info_user($codeuser);
	$tab_destinataires=array();
	$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
	$tab_acteurs=array();
	$demandeur='';
	$query="select nom as referentnom, prenom as referentprenom from individu ".
				 " where individu.codeindividu=".GetSQLValueString($row_rs_commande['codereferent'], "text");
	$rs=mysql_query($query) or die(mysql_error());
	if($row_rs=mysql_fetch_assoc($rs))
	{ $demandeur=$row_rs['referentprenom'].' '.$row_rs['referentnom'];
	}
	$query_rs="SELECT commandeimputationbudget.codecontrat as codecontrat, commandeimputationbudget.codeeotp as codeeotp,".
						" typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
						" centrecout.libcourt as libcentrecout,acronyme as libcontrat,libeotp,".
						" virtuel_ou_reel,montantengage,montantpaye,commandeimputationbudget.numordre as numordre".
						" from commandeimputationbudget, typecredit,centrefinancier,centrecout,budg_eotp_source_vue,budg_contrat_source_vue left join individu as respscientifique ".
						" on budg_contrat_source_vue.coderespscientifique=respscientifique.codeindividu".
						" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
						" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
						" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
						" and commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
						" and commandeimputationbudget.codeeotp=budg_eotp_source_vue.codeeotp".
						" and commandeimputationbudget.codecommande=".GetSQLValueString($codecommande, "text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	$montantengage=0;
	while($row_rs=mysql_fetch_assoc($rs))
	{ $montantengage+=$row_rs['virtuel_ou_reel']=='0'?$row_rs['montantengage']:0;
	}
	// roles du, sif, admingestfin (provenant de) structure
  $query_rs="select codeindividu as coderesp,codelib from structureindividu,structure".
						" where structureindividu.codestructure=structure.codestructure".
						" and codelib=".GetSQLValueString('sif', "text")." and structureindividu.estresp='oui'";
	$rs=mysql_query($query_rs);
	$i=0;
  while($row_rs = mysql_fetch_assoc($rs))
	{ $i++;
		$tab_acteurs[$row_rs['codelib']][$i]=get_info_user($row_rs['coderesp']);
  }
  foreach($tab_acteurs as $coderoleacteur=>$tab_acteurs_par_role)
  { foreach($tab_acteurs_par_role as $ieme_acteur=>$tab_info_un_acteur_du_role)//ecrasement de l'index dans $tab_destinataires pour mail unique
		{ $tab_destinataires[$tab_info_un_acteur_du_role['email']]=array('codeacteur'=>$tab_info_un_acteur_du_role['codeindividu'],'prenomnom'=>$tab_info_un_acteur_du_role['prenom'].' '.$tab_info_un_acteur_du_role['nom'],'email'=>$tab_info_un_acteur_du_role['email']);
		}
	}
	
	$subject="Pour information : Avoir de la commande ".$codecommande;
	$message="";
	$message.="Bonjour,<br><br>";
	if($row_rs_commande['estavoir']=='oui')
	{ $message.="L&rsquo;avoir de la commande n&deg; <b>".$codecommande."</b> a &eacute;t&eacute; r&eacute;tabli en commande par ".$tab_infouser['prenom']." ".$tab_infouser['nom'];
	}
	else
	{ $message.="Le montant de la commande n&deg; <b>".$codecommande."</b> a &eacute;t&eacute; transform&eacute; en avoir par ".$tab_infouser['prenom']." ".$tab_infouser['nom'];
	}
	$message.="<br><br>Demandeur : <b>".$demandeur."</b>";
	$message.="<br>Objet : <b>".$row_rs_commande['objet']."</b>";
	$message.="<br>Fournisseur : <b>".$row_rs_commande['libfournisseur']."</b>";
	$message.="<br>Montant engag&eacute; : <b>".number_format($montantengage,2,'.',' ')."</b>";
	$message.="<br><br>Cordialement,";
	$message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
	
	$subject=	html_entity_decode($subject);
	
	$from = $tab_infouser['prenom'].' '.$tab_infouser['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $tab_infouser['prenom'].' '.$tab_infouser['nom'].'<'.$tab_infouser['email'].'>';
	
	$to="";
	$first=true;
	foreach($tab_destinataires as $un_destinataire=>$tab_un_destinataire)
	{ if(!array_key_exists(strtolower($tab_un_destinataire['email']),$tab_mail_unique) && est_mail($tab_un_destinataire['email']))
		{ $tab_mail_unique[strtolower($tab_un_destinataire['email'])]=$tab_un_destinataire['email'];
			$to.=($first?"":", ").$tab_un_destinataire['prenomnom'].' <'.$tab_un_destinataire['email'].'>';
			$first=false;
		}
	}	
	//le user expediteur s'il n'est pas deja dans $to
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}
	
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	
	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
 //--------------- modifs pour mime
	// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
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
	if($GLOBALS['mode_avec_envoi_mail'])
	{	$erreur=envoimail($headers, $message_html_txt);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br><br>'.$message;
	}
	//$erreur=$message;

	if(isset($rs1))mysql_free_result($rs1);
	if(isset($rs))mysql_free_result($rs);
	return $erreur;
}

function mail_relance_missionnaire($codemission,$codeuser,$info_comp_relance_missionnaire)
{ /* destinataires de message : 
  'secrsite'
  */
	$tab_infouser=get_info_user($codeuser);
	$tab_destinataires=array();
	$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
	$query_rs_mission="select mission.*, departdate from mission,missionetape".
										" where mission.codemission=".GetSQLValueString($codemission, "text")." and  mission.codemission=missionetape.codemission and numetape='01'";
	$rs_mission=mysql_query($query_rs_mission);
	$row_rs_mission=mysql_fetch_assoc($rs_mission);
	$tab_destinataires[$row_rs_mission['email']]=array('prenomnom'=>$row_rs_mission['prenom'].' '.$row_rs_mission['nom'],'email'=>$row_rs_mission['email']);	
	
	$subject="Mission du ".aaaammjj2jjmmaaaa($row_rs_mission['departdate'],'/')." - ".$row_rs_mission['nom']." ".$row_rs_mission['prenom']." : demande de justificatifs";
	$message=nl2br($info_comp_relance_missionnaire);
/* 	$message.="Bonjour, ";

	$message.="<br><br>vous avez effectu&eacute; une mission le ".aaaammjj2jjmmaaaa($row_rs_mission['departdate'],'/')." : ".$row_rs_mission['motif'];
	$message.="<br>Afin de proc&eacute;der au remboursement des d&eacute;penses engag&eacute;es, je vous remercie de me communiquer le d&eacute;tail des".
						" frais et de me retourner les originaux de vos factures.";
	if($info_comp_relance_missionnaire!='')
	{ $message.='<br>'.;
	}
	$message.="<br><br>Cordialement,";
	$message.="<br><br>".$tab_infouser['prenom'].' '.$tab_infouser['nom'];
 */	
	$subject=	html_entity_decode($subject);
	
	$from = $tab_infouser['prenom'].' '.$tab_infouser['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $tab_infouser['prenom'].' '.$tab_infouser['nom'].'<'.$tab_infouser['email'].'>';

	$to="";
	$first=true;
	foreach($tab_destinataires as $un_destinataire=>$tab_un_destinataire)
	{ if(!array_key_exists(strtolower($tab_un_destinataire['email']),$tab_mail_unique) && est_mail($tab_un_destinataire['email']))
		{ $tab_mail_unique[strtolower($tab_un_destinataire['email'])]=$tab_un_destinataire['email'];
			$to.=($first?"":", ").$tab_un_destinataire['prenomnom'].' <'.$tab_un_destinataire['email'].'>';
			$first=false;
		}
	}	
	//le user expediteur s'il n'est pas deja dans $to
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}
	
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	
	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto,'Subject' => $subject);
 //--------------- modifs pour mime
// TESTE SUR PC ET MAC : OK : $message.=detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['numsejour'],$codeuser);

	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
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
 	$erreur=""; 
	if($GLOBALS['mode_avec_envoi_mail'])
	{	$erreur=envoimail($headers, $message_html_txt);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br><br>'.$message;
	}

  if(isset($rs_mission))mysql_free_result($rs_mission);

	return $erreur;
}

function popup_validation_sujet($typevalidation,$codesujet,$tab_infouser)
{ // $typevalidation=demandevalidation ou validation
	$tab_destinataires=array();
	$message="";
	//expediteur
	$tab_destinataires[$tab_infouser['codeindividu']]=$tab_infouser['prenom']." ".$tab_infouser['nom'];
	// createur
	$rs=mysql_query("select individu.codeindividu,nom,prenom from sujet,individu".
									" where sujet.codesujet=".GetSQLValueString($codesujet, "text").
									" and sujet.codecreateur=individu.codeindividu") or die(mysql_error());
	if($row_rs=mysql_fetch_array($rs))
	{ $tab_destinataires[$row_rs['codeindividu']]=$row_rs['prenom']." ".$row_rs['nom'];
	}
  // dir du sujet 
	$rs=mysql_query(" select individu.codeindividu,nom,prenom from sujetdir,individu".
									" where sujetdir.codesujet=".GetSQLValueString($codesujet,"text").
									" and sujetdir.codedir=individu.codeindividu".
									" order by numordre") or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_destinataires[$row_rs['codeindividu']]=$row_rs['prenom']." ".$row_rs['nom'];
	}
	// resp themes concerne
	$query_rs="select individu.codeindividu, nom, prenom from sujettheme,structureindividu,individu".
						" where sujettheme.codetheme=structureindividu.codestructure and structureindividu.codeindividu=individu.codeindividu and estresp='oui'".
						" and sujettheme.codesujet=".GetSQLValueString($codesujet, "text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_destinataires[$row_rs['codeindividu']]=$row_rs['prenom']." ".$row_rs['nom'];
	}

	$message = addslashes("Apr&egrave;s avoir valid&eacute; cette proposition, vous ne pourrez plus la modifier.");
	if($typevalidation=='demandevalidation')
	{ $message .= addslashes(" Seul le Responsable de ".$GLOBALS['libcourt_theme_fr']." y aura acc&egrave;s.");
	}
	$message.= "\\n";
	$message.= addslashes("Un message de validation va &ecirc;tre adress&eacute; &agrave; :");
	foreach($tab_destinataires as $codeindividu=>$undestinataire)
	{ $message.= "\\n"."- ".addslashes($undestinataire);
	}
  if(isset($rs_acteur_du_role_a_prevenir))mysql_free_result($rs_acteur_du_role_a_prevenir);
  if(isset($rs_etudiant))mysql_free_result($rs_etudiant);
  if(isset($rs))mysql_free_result($rs);
	return $message;
}

// mail envoye aux acteurs concernes du sujet 
function mail_validation_sujet($codesujet,$tab_infouser,$coderole_a_prevenir)
{ $sujetaffecte=false;
	$message='';
	$tab_destinataires=array();// destinataires : pas de doublon car ecrasement par $tab_destinataires[$....['codeindividu']]
	$tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
	$tab_theme=array();
	$rs_sujet=mysql_query("select sujet.*,individu.codeindividu,nom,prenom,email,".
												" typesujet.libcourt_fr as libtypesujet,libstatutsujet,codelibtypestage,libcourttypestage as libtypestage".
												" from sujet,individu,typesujet,statutsujet,typestage".
												" where sujet.codesujet=".GetSQLValueString($codesujet, "text").
												" and sujet.codecreateur=individu.codeindividu and sujet.codetypesujet=typesujet.codetypesujet".
												" and sujet.codestatutsujet=statutsujet.codestatutsujet and sujet.codetypestage=typestage.codetypestage") or die(mysql_error());
	$row_rs_sujet=mysql_fetch_assoc($rs_sujet);
	//createur
	$tab_destinataires[$row_rs_sujet['codeindividu']]=array('prenomnom'=>$row_rs_sujet['prenom'].' '.$row_rs_sujet['nom'],'email'=>$row_rs_sujet['email']);
	
	$rs_dir=mysql_query("select individu.codeindividu,nom,prenom,email from sujetdir,individu ".
											" where sujetdir.codesujet=".GetSQLValueString($codesujet, "text").
											" and sujetdir.codedir=individu.codeindividu ".
											" order by numordre") or die(mysql_error());
	//encadrants
	while($row_rs_dir=mysql_fetch_array($rs_dir))
	{ $tab_destinataires[$row_rs_dir['codeindividu']]=array('prenomnom'=>$row_rs_dir['prenom'].' '.$row_rs_dir['nom'],'email'=>$row_rs_dir['email']);
	}
	//resp(s) theme(s)
	if($GLOBALS['avecvisathemesujet'])
	{ $rs_theme=mysql_query("select individu.codeindividu,libcourt_fr as libtheme,nom,prenom,email from sujettheme,structure,structureindividu,individu ".
												" where codesujet=".GetSQLValueString($codesujet, "text").
												" and sujettheme.codetheme=structure.codestructure and structureindividu.estresp='oui'".
												" and structure.codestructure=structureindividu.codestructure and structureindividu.codeindividu=individu.codeindividu".
												" order by structure.codestructure") or die(mysql_error());
		while($row_rs_theme=mysql_fetch_assoc($rs_theme))
		{ $tab_destinataires[$row_rs_theme['codeindividu']]=array('prenomnom'=>$row_rs_theme['prenom'].' '.$row_rs_theme['nom'],'email'=>$row_rs_theme['email']);
			$tab_theme[$row_rs_theme['libtheme']]=$row_rs_theme['libtheme'];//ecrase les doublons eventuels
		}
	}
	// message eventuel a la gestionnaire theme et SRH si sujet attendu apres saisie du dossier de l'etudiant
	if($coderole_a_prevenir!='')
	{ $sujetaffecte=true;
		$rs_etudiant=mysql_query(	"select individu.codeindividu,nom,prenom,codegesttheme from individu,individusejour,individusujet ".
															" where individu.codeindividu=individusejour.codeindividu".
															" and individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
															" and individusujet.codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
		$row_rs_etudiant=mysql_fetch_assoc($rs_etudiant);
		$rs_acteur_du_role_a_prevenir=mysql_query("select codeindividu,nom,prenom,email from individu where codeindividu=".GetSQLValueString($row_rs_etudiant['codegesttheme'], "text")) or die(mysql_error());
		if($row_rs_acteur_du_role_a_prevenir=mysql_fetch_assoc($rs_acteur_du_role_a_prevenir))
		{ $tab_destinataires[$row_rs_acteur_du_role_a_prevenir['codeindividu']]=array('prenomnom'=>$row_rs_acteur_du_role_a_prevenir['prenom'].' '.$row_rs_acteur_du_role_a_prevenir['nom'],'email'=>$row_rs_acteur_du_role_a_prevenir['email']);
		}
		$rs_acteur_du_role_a_prevenir=mysql_query("select individu.codeindividu,nom,prenom,email from individu,structureindividu,structure".
																								" where individu.codeindividu=structureindividu.codeindividu".
																								" and structureindividu.codestructure=structure.codestructure".
																								" and codelib=".GetSQLValueString('srh', "text")) or die(mysql_error());
		if($row_rs_acteur_du_role_a_prevenir=mysql_fetch_assoc($rs_acteur_du_role_a_prevenir))
		{ $tab_destinataires[$row_rs_acteur_du_role_a_prevenir['codeindividu']]=array('prenomnom'=>$row_rs_acteur_du_role_a_prevenir['prenom'].' '.$row_rs_acteur_du_role_a_prevenir['nom'],'email'=>$row_rs_acteur_du_role_a_prevenir['email']);
		}
	}
	
	// contenu du message de validation
	$message.="Bonjour,<br><br>";
	$message.="Le sujet suivant".($coderole_a_prevenir==''?"":", affect&eacute; &agrave; ".$row_rs_etudiant['prenom']." ".$row_rs_etudiant['nom'].",")." a &eacute;t&eacute; ".(($row_rs_sujet['codestatutsujet']=="E")?"enregistr&eacute;":($row_rs_sujet['codestatutsujet']=="V"?"valid&eacute;":($row_rs_sujet['codestatutsujet']=="P"?"pass&eacute; a l'&eacute;tat pourvu":"")))." sur le serveur 12+ par ".$tab_infouser['prenom']." ".$tab_infouser['nom']." :";
	$message.="<br><br>Num&eacute;ro de sujet : ".$codesujet;
	$message.="<br>Type de sujet : ".$row_rs_sujet['libtypesujet'].($row_rs_sujet['codetypestage']!=''?" - ".$row_rs_sujet['libtypestage']:"")." (".aaaammjj2jjmmaaaa($row_rs_sujet['datedeb_sujet'],"/")." - ".aaaammjj2jjmmaaaa($row_rs_sujet['datefin_sujet'],"/").")";
	$message.="<br><br>Directeur(s) membre(s) ".$GLOBALS['acronymelabo']." : ";
	
	mysql_data_seek($rs_dir,0);
	while($row_rs_dir=mysql_fetch_array($rs_dir))
	{ $message.=$row_rs_dir['prenom']." ".$row_rs_dir['nom'];
		$message.=" ";
	}
	if($row_rs_sujet['autredir1']!="")
	{ $message.="<br>Autre(s) Directeur(s) : ".$row_rs_sujet['autredir1']." ".$row_rs_sujet['autredir2'];
	}
	
	$message.="<br><br>".$GLOBALS['liblong_theme_fr']."(s) : ";
	$first=true;
	foreach($tab_theme as $key=>$libtheme)
	{ $message.=($first?"":", ").$libtheme;
		$first=false;
	}
	$message.="<br><br>Titre en fran&ccedil;ais : ".$row_rs_sujet['titre_fr'];
	$message.='<p align="justify">Description en fran&ccedil;ais : '.$row_rs_sujet['descr_fr'].'</p>';
	if(($row_rs_sujet['codetypesujet']!='02' || ($row_rs_sujet['codetypesujet']=='02' && $row_rs_sujet['codelibtypestage']=='MASTER')) && $row_rs_sujet['codetypesujet']!='05')
	{	$message.="<br>Mots cl&eacute;s fran&ccedil;ais : ".$row_rs_sujet['motscles_fr'];
		$message.="<br><br>Conditions fran&ccedil;ais : ".$row_rs_sujet['conditions_fr'];
		$message.="<br><br>Financement fran&ccedil;ais : ".$row_rs_sujet['financement_fr'];
		
		$message.="<br><br>Titre en anglais : ".$row_rs_sujet['titre_en'];
		$message.='<p align="justify">Description en anglais : '.$row_rs_sujet['descr_en'].'</p>';
		$message.="<br>Mots cl&eacute;s anglais : ".$row_rs_sujet['motscles_en'];
		$message.="<br><br>Conditions anglais : ".$row_rs_sujet['conditions_en'];
		$message.="<br><br>Financement anglais : ".$row_rs_sujet['financement_en'];
		$message.="<br><br>R&eacute;f&eacute;rences de publications : ".$row_rs_sujet['ref_publis'];
	}
	if($row_rs_sujet['codestatutsujet']=="E")
	{ $message.="<br><br>";
		$message.="Ce sujet est actuellement 'En cours de validation' : la personne l'ayant propos&eacute; ne peut plus le modifier.";
		$message.="<br><br>";
		$message.="Connectez-vous au serveur 12+ afin de le VALIDER."; 
		
		$subject="Nouvelle proposition de sujet".($coderole_a_prevenir!=''?' ('.$row_rs_etudiant['prenom'].' '.$row_rs_etudiant['nom'].') ':'');
	}
	else /* if($row_rs_sujet['codestatutsujet']=="V") */
	{ if($row_rs_sujet['codetypesujet']!='05')
		{ if($row_rs_sujet['afficher_sujet_web']=='oui')
			{ $message.="<br><br>Ce sujet appara&icirc;t en zone publique du serveur Web ".$GLOBALS['acronymelabo'].
			" dans la liste des sujets ".($sujetaffecte?($row_rs_sujet['afficher_sujet_propose']!="oui"?"'pourvus'":"'propos&eacute;s' (car 'Toujours propos&eacute;')"):"'propos&eacute;s'").".";
			}
			else
			{ $message.="<br><br>Quand l&rsquo;affichage public sera valid&eacute;, ce sujet appara&icirc;tra en zone publique du serveur Web ".$GLOBALS['acronymelabo'].".";
			}
		}
		//$message.="<br><br>Ce sujet appara&icirc;t d&eacute;sormais en zone publique du serveur Web ".$GLOBALS['acronymelabo']." dans la liste des sujets 'pourvus'";
		$subject="Pour information : validation de sujet".($coderole_a_prevenir!=''?' ('.$row_rs_etudiant['prenom'].' '.$row_rs_etudiant['nom'].') ':'');
	}
	
	$message.="<br><br>";
	$message.="cordialement,";
	$message.="<br><br>";
	$message.="Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";			
	$message.="<br><br>";
	$message=html_entity_decode($message);
	
	$subject=	html_entity_decode($subject);

	$from = $GLOBALS['Serveur12+']['nom'].' <'.$GLOBALS['Serveur12+']['email'].'>';
	$replyto= $GLOBALS['Serveur12+']['nom'].' <'.$GLOBALS['Serveur12+']['email'].'>';
	
	$to="";
	$first=true;
	foreach($tab_destinataires as $un_destinataire=>$tab_un_destinataire)
	{ if(!array_key_exists(strtolower($tab_un_destinataire['email']),$tab_mail_unique))
		{ $tab_mail_unique[strtolower($tab_un_destinataire['email'])]=$tab_un_destinataire['email'];
			$to.=($first?"":", ").$tab_un_destinataire['prenomnom'].' <'.$tab_un_destinataire['email'].'>';
			$first=false;
		}
	}	
	//le user expediteur s'il n'est pas deja dans $to
	if(!array_key_exists(strtolower($tab_infouser['email']),$tab_mail_unique) && est_mail($tab_infouser['email']))
	{ $to.=($to==""?"":", ").$tab_infouser['prenom']." ".$tab_infouser['nom']." <".$tab_infouser['email'].">";
	}
	// le développeur
	if(!array_key_exists(strtolower($GLOBALS['webMaster']['email']),$tab_mail_unique))
	{ $to.=($to==""?"":", ").$GLOBALS['webMaster']['nom'].' <'.$GLOBALS['webMaster']['email'].'>';
	}
	
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}

	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto, 'Subject' => $subject);
 //--------------- modifs pour mime
	//$text = $message;
	$message=nl2br($message);
	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
	$mime = new Mail_mime( "\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);
	$headers = $mime->headers($headers);
	$erreur=""; 
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br>'.$message;
	}
	//$erreur=$message;
	if(isset($rs_acteur_du_role_a_prevenir))mysql_free_result($rs_acteur_du_role_a_prevenir);
	if(isset($rs_etudiant))mysql_free_result($rs_etudiant);
	if(isset($rs_dir))mysql_free_result($rs_dir);
	if(isset($rs_theme))mysql_free_result($rs_theme);
	if(isset($rs_sujet))mysql_free_result($rs_sujet);
	
	return $erreur;
}

function popup_validation_projet($typevalidation,$codeprojet,$tab_infouser)
{ $message="";
	$query_rs="select structure.codelib as codelibtheme from projet,structure".
						" where projet.codetheme=structure.codestructure".
						" and projet.codeprojet=".GetSQLValueString($codeprojet, "text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);
	$codelibtheme=$row_rs['codelibtheme']; 	

	if($typevalidation=='brouillon')
	{ $message= addslashes("Le sujet ne sera plus visible par les autres membres");
	}
	else
	{ $message= addslashes("Un message de publication de ce projet va &ecirc;tre adress&eacute; &agrave; la liste cran-".$codelibtheme)."\\n".
							addslashes("et le sujet sera visible par l'ensemble de ses membres");
	}
  if(isset($rs))mysql_free_result($rs);
	return $message;
}

// mail envoye aux acteurs concernes du projet 
function mail_validation_projet($codeprojet,$tab_infouser)
{ $message="";
	$query_rs="select structure.codelib as codelibtheme from projet,structure".
						" where projet.codetheme=structure.codestructure".
						" and projet.codeprojet=".GetSQLValueString($codeprojet, "text");
	$rs=mysql_query($query_rs) or die(mysql_error());
	$row_rs=mysql_fetch_assoc($rs);
	$codelibtheme=$row_rs['codelibtheme']; 	
 	$rs_projet=mysql_query("select projet.*, cont_classif.libcourtclassif as libclassif  from projet, cont_classif".
												 " where projet.codeclassif=cont_classif.codeclassif and projet.codeprojet=".GetSQLValueString($codeprojet,"text")) or die(mysql_error());
	$row_rs_projet=mysql_fetch_assoc($rs_projet);
	$subject="Nouveau projet d&eacute;pos&eacute; par ".$tab_infouser['prenom']." ".$tab_infouser['nom'];
	// contenu du message de validation
	$message.="Message &agrave; l'attention des chercheurs, doctorants et post-doctorants du d&eacute;partement ".strtoupper($codelibtheme)."<br><br>";
	$message.="Bonjour,<br><br>";
	$message.="Le projet suivant a &eacute;t&eacute; d&eacute;pos&eacute; sur le serveur 12+ par ".$tab_infouser['prenom']." ".$tab_infouser['nom']." :";
	
	if($row_rs_projet['titrecourt']!='')
	{ $message.="<br>Intitul&eacute; court : ".$row_rs_projet['titrecourt'];
	}
	$message.="<br>Intitul&eacute; : ".$row_rs_projet['titre'];
	$message.="<br>Type de projet : ".$row_rs_projet['libclassif'];
	$message.="<br>Date : ".aaaammjj2jjmmaaaa($row_rs_projet['datedeb_projet'],"/");
	$message.="<br>Description : ".$row_rs_projet['descr'];
	
	
	$message.="<br><br>";
	$message.="cordialement,";
	$message.="<br><br>";
	$message.="Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";			
	$message.="<br><br>";
	$message=html_entity_decode($message);

	$subject=	html_entity_decode($subject);
	$from = $tab_infouser['prenom']." ".$tab_infouser['nom'].' <'.$tab_infouser['email'].'>';
	$replyto= $tab_infouser['prenom']." ".$tab_infouser['nom'].' <'.$tab_infouser['email'].'>';
	$to="cran-".$codelibtheme."@univ-lorraine.fr";

	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	
	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto, 'Subject' => $subject);
 //--------------- modifs pour mime
	//$text = $message;
	$message=nl2br($message);
	$message=html_entity_decode($message);
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	$mime = new Mail_mime( "\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);
	$headers = $mime->headers($headers);
	$erreur=""; 
	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur=$subject.'<br>'.$message;
	}
	if(isset($rs))mysql_free_result($rs);
	if(isset($rs_projet))mysql_free_result($rs_projet);
	
	return $erreur;
}


function mail_validation_registre($tab_post)
{  $erreur="";
	// contenu du message de demande de validation
	$subject="Nouvelle observation dans le registre H&S";
	$message="
	Bonjour,\n\n
	Une nouvelle observation a &eacute;t&eacute; depos&eacute;e par ".$tab_post['nom']." dans le registre H&S du serveur 12+ :\n
	Lieu : ".$tab_post['lieu']."\n
	Fait constat&eacute; : ".$tab_post['fait']."\n
	Observations : ".$tab_post['observation']."\n
	Suggestions : ".$tab_post['suggestion']."\n\n
	cordialement,\n\n
	Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";
	$subject=	html_entity_decode($subject);
	
	$from = "Serveur 12+<".$GLOBALS['webMaster']['email'].">";
	$replyto="Serveur 12+<".$GLOBALS['webMaster']['email'].">";
	$to="";
	if(est_mail($GLOBALS['emailDU']))
	{ $to.="DU<".$GLOBALS['emailDU'].">";
	}
	if(est_mail($GLOBALS['emailACMO']))
	{ $to.=($to==""?"":",")."ACMO<".$GLOBALS['emailACMO'].">";
	}
	if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{	$message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}


	$headers = array ('From' => $from,'To' => $to,'Reply-To' => $replyto, 'Subject' => $subject);
	//--------------- modifs pour mime
	//$text = $message;
	$message=nl2br($message);
	$message=html_entity_decode($message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//$mime->setTXTBody($text);Le texte est transformé en html : erreur de paramétrage meme avec 
	$mime = new Mail_mime( "\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";//par defaut
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	$message_html_txt = $mime->get($mimeparams);
	//$mime->addBcc('pascal.gend@wanadoo.fr');
	$headers = $mime->headers($headers);


	if($GLOBALS['mode_avec_envoi_mail'])
	{ $erreur=envoimail($headers, $message);
	}
	else if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
	{ $erreur='mode_avec_envoi_mail=false<br>'.$subject.'<br>'.$message;
	} 
	return $erreur;
}

// mail envoye aux acteurs concernes 
function popup_validation_actu($typevalidation,$codeactu,$tab_infouser)
{
}

function mail_validation_actu($codeactu,$tab_infouser,$coderole_a_prevenir)
{ $erreur="";
	
	return $erreur;
}

function header_mimemail()
{
}

function envoimail($headers, $message)
{ //$smtpserver = "smtp.uhp-nancy.fr";
	$smtpserver = $GLOBALS['smtpserver'];
	$smtp = Mail::factory('smtp',array ('host' => $GLOBALS['smtpserver'],/*'port' => $GLOBALS['smtpport'],*/"localhost"=>$GLOBALS['smtplocalhost']));
	$resmail=$smtp->send($headers['To'], $headers, $message);
	if(PEAR::isError($resmail))
	{ return $resmail;
	}
	else
	{ return "";
	}
}

function mime_message($tab_params)
{ //requis : To : chaine de cars ou tableau email=>(email=>email, nom=>nom, prenom=>prenom,....) ; 
	// Subject; 
	// Message
	$message=nl2br($tab_params['Message']);
	$headers['From']=isset($tab_params['From'])?$tab_params['From']:$GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>';
	$headers['Reply-To']=(isset($tab_params['Reply-To'])?$tab_params['Reply-To']:$GLOBALS['Serveur12+']['nom'].'<'.$GLOBALS['Serveur12+']['email'].'>');
	$headers['Subject']=html_entity_decode($tab_params['Subject']);
	$to="";
	if(is_array($tab_params['To']))
	{ $first=true;
		foreach($tab_params['To'] as $email=>$un_tab_mail_unique)
		{ $to.=($first?"":", ").$un_tab_mail_unique['prenom'].' '.$un_tab_mail_unique['nom'].' <'.$un_tab_mail_unique['email'].'>';
			$first=false;
		}
	}
	else
	{ $to=$tab_params['To'];
	}
	
	if($GLOBALS['mode_exploit']=='test')
	{ $message.='<br>En test, destinataires en fin de message : '.$to;
		$to="TEST <".$GLOBALS['emailTest'].">";
	}
	$headers['To']=$to;
	$message=str_replace("images/",$GLOBALS['racine_site_web_labo']."images/",$message);
	$html_message = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
	<title>....</title>
	<link rel="stylesheet" href="'.$GLOBALS['racine_site_web_labo'].'styles/normal.css">
	</head>
	<body>'.
	$message.'
	</body>
	</html>';
	//par defaut
	$mime = new Mail_mime("\n");
	$mime->setHTMLBody($html_message);
	$mimeparams=array();  
	$mimeparams['text_encoding']="7bit";
	$mimeparams['html_encoding']="quoted-printable";//par defaut
	$mimeparams['text_charset']="iso-8859-1";
	$mimeparams['html_charset']="iso-8859-1";
	$mimeparams['head_charset']="iso-8859-1";
	
	if(isset($tab_params['mimeparams']))
	{	foreach($tab_params['mimeparams'] as $mimeparam=>$mimeval)
		{ $mimeparams[$mimeparam]=$mimeval;
		}
	}
	return array($mime->headers($headers),$mime->get($mimeparams));
}


