<?php 
//Verif. user autorise a lancer le batch
$appel_ligne_commande_ou_http='http';
if(isset($argv[1]))//appel ligne commande
{ $_REQUEST['db']=$argv[1];
	$appel_ligne_commande_ou_http='ligne_commande'; 
}
else if(!isset($_REQUEST['db']))//si pas appel http 
{ exit;
}
require_once('_const_fonc.php');// ici pour controle nom bd dans nom dossier correct
$message_webmaster='';
$liste_mail_referent='';
$liste_mail_gesttheme='';
$liste_mail_agentprevention='';
$liste_mail_resp_relint='';
$liste_mail_arrivant='';
if($_REQUEST['db']=='cran')
{ $tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
  $tab_destinataires=array();
  $tab_referent=array();
  $tab_gesttheme=array();
  $tab_agentprevention=array();
  $tab_individu=array();
  $tab_resp_relint=array();
  $tab_relint=array();
  $tab_stagiaire_pour_attestation=array();
  $message_webmaster.="Bonjour,<br><br>";
  $message="";
  
  $rs=mysql_query("select individu.codeindividu,nom,prenom,email from individu,structureindividu,structure".
                  " where individu.codeindividu=structureindividu.codeindividu".
                  " and structureindividu.codestructure=structure.codestructure and structureindividu.estresp='oui' and codelib='relint'");
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_resp_relint[$row_rs['codeindividu']]=$row_rs;
  }
  
  // liste de tous les individus, referents possibles 
  $query_rs="select individu.codeindividu, libcourt_fr as libciv, login, nom, prenom, email,if(isnull(datedeb_sejour),'parti','present') as presentparti from civilite,individu".
            " left join individusejour on individu.codeindividu=individusejour.codeindividu and ".periodeencours('datedeb_sejour','datefin_sejour').
            " where individu.codeciv=civilite.codeciv and individu.codeindividu<>''";
  $rs=mysql_query($query_rs) or die(mysql_error());
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_individu[$row_rs['codeindividu']]=$row_rs;
  }
	
  // --------------- La veille de l'arrivee : mail a chaque arrivant
	// Séjours débutent le lendemain
	$demain=date("Y/m/d", mktime(0, 0, 0, date('m'),date('d')+1, date('Y')));
  $query_rs_individuarrivant= "SELECT individu.codeindividu,civilite.libcourt_fr as libciv,civilite.libcourt_en as libciv_en,individusejour.*,nom,prenom,if(email<>'',email,email_parti) as email,codelibcat,libcat_fr as libcat,corps.codecorps,".
                            " liblongcorps_fr as libcorps,pays.libpays as libpays_etab_orig,codereferent,codegesttheme,codeagentprevention,libcourtlieu as liblieu".
                            " from individu,civilite,individusejour,corps,cat,pays,lieu left join lieuagentprevention on lieu.codelieu=lieuagentprevention.codelieu".
                            " where individu.codeciv=civilite.codeciv and individu.codeindividu<>''".
														" and individu.codeindividu=individusejour.codeindividu".
                            " and individusejour.codecorps=corps.codecorps".
                            " and corps.codecat=cat.codecat".
                            " and datedeb_sejour=".GetSQLValueString($demain, "text").
                            " and individu.codelieu=lieu.codelieu".
                            " and individusejour.codepays_etab_orig=pays.codepays".
                            " order by datefin_sejour desc,nom,prenom";
  $rs_individuarrivant=mysql_query($query_rs_individuarrivant) or die(mysql_error());
	$nb_individuarrivant=mysql_num_rows($rs_individuarrivant);
  $liste_mail_arrivant='<table border="0">';
  while($row_rs_individuarrivant = mysql_fetch_assoc($rs_individuarrivant))
  { $tab_amj=duree_aaaammjj($row_rs_individuarrivant['datedeb_sejour'], $row_rs_individuarrivant['datefin_sejour']);
		if($tab_amj['a']+$tab_amj['m']>=1 || $tab_amj['j']>5)
		{ if($row_rs_individuarrivant['email']!='')
			{ $codereferent=$row_rs_individuarrivant['codereferent'];
				$erreur_envoi_mail="";
				$sujet='Bienvenue au CRAN / Welcome to the CRAN';
				if(strpos('aeiouy',strtolower(substr($row_rs_individuarrivant['libcat'],0,1)))!==false)
				{ $de_libcat='d&rsquo;'.$row_rs_individuarrivant['libcat'];
				}
				else
				{ $de_libcat='de '.$row_rs_individuarrivant['libcat'];
				}
				$message='Bonjour '.$row_rs_individuarrivant['libciv'].' '.$row_rs_individuarrivant['prenom'].' '.$row_rs_individuarrivant['nom'].',';
				$message.='<br><br>Votre s&eacute;jour au laboratoire est pr&eacute;vu du '.aaaammjj2jjmmaaaa($row_rs_individuarrivant['datedeb_sejour'],'/').' au '.aaaammjj2jjmmaaaa($row_rs_individuarrivant['datefin_sejour'],'/').' en qualit&eacute; '.$de_libcat.'.';
				$message.='<br>Dans l&rsquo;&eacute;ventualit&eacute; d&rsquo;un changement des dates de votre s&eacute;jour, nous vous remercions de bien vouloir nous en informer le plus rapidement possible en r&eacute;pondant &agrave; ce mail.';
				$message.='<br><br>Vous souhaitant d&rsquo;ores et d&eacute;j&agrave; la bienvenue au CRAN.';
				$message.='<br><br>Bien cordialement.';
				$message.='<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+ du laboratoire';
				
				$message.='<br><br>Hello '.$row_rs_individuarrivant['libciv_en'].' '.$row_rs_individuarrivant['prenom'].' '.$row_rs_individuarrivant['nom'].',';
				$message.='<br><br>Your stay in the laboratory is scheduled from '.aaaammjj2jjmmaaaa($row_rs_individuarrivant['datedeb_sejour'],'/').' to '.aaaammjj2jjmmaaaa($row_rs_individuarrivant['datefin_sejour'],'/').'.'; 
				$message.='<br>In the event of a change in the dates of your stay, we kindly ask you to inform us as soon as possible by replying to this email.';
				$message.='<br><br>You are already welcome to the CRAN.'; 
				$message.='<br><br>Best regards.';
				$message.='<br><br>Message automatically generated by the laboratory 12+ Server';
				
				$to=$row_rs_individuarrivant['prenom']." ".$row_rs_individuarrivant['nom']." <".$row_rs_individuarrivant['email'].">";
				$to.=', '.$GLOBALS['emailRH'].' <'.$GLOBALS['emailRH'].'>';
				if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
				{	$message.='<br>En test, destinataires en fin de message : '.$to;
					$to="TEST <".$GLOBALS['emailTest'].">";
				}
				
				list($headers,$message_html_txt)=mime_message(array ('Reply-To'=>$GLOBALS['emailRH'],'To' => $to,'Subject' => $sujet,'Message'=>$message));
				$liste_mail_arrivant.='<tr><td>';
				$liste_mail_arrivant.='<table border="1">'.
																'<tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'.
																'Mail envoy&eacute; &agrave; '.$row_rs_individuarrivant['prenom'].' '.$row_rs_individuarrivant['nom'].
																'</td></tr>';
				$liste_mail_arrivant.='<tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">';
				$liste_mail_arrivant.=$message;
				$erreur_envoi_mail="";
				if($GLOBALS['mode_avec_envoi_mail'])
				{ $erreur_envoi_mail.=envoimail($headers, $message_html_txt);
					if($erreur_envoi_mail!="")
					{ $liste_mail_arrivant.='<br><span style="color:#ff0000;">Une erreur d&rsquo;envoi de mail s&rsquo;est produite: </span><br>'.$erreur_envoi_mail;
					}
				}
				$liste_mail_arrivant.=	'</td></tr>'.
															'</table>';
				$liste_mail_arrivant.='</td></tr>';
			}
			else
			{ $liste_mail_arrivant.='<tr><td><br><span style="color:#ff0000;">'.$row_rs_individuarrivant['prenom'].' '.$row_rs_individuarrivant['nom'].' : pas d&rsquo;adresse mail</span></td></tr>';
			}
		}
  }
  $liste_mail_arrivant.="</table>";

  // --------------- le jour d'arrivee ou apres le depart : Mail a chaque referent + AP + gesttheme
	// Sejours debutes
  $query_rs= "SELECT individu.codeindividu,individusejour.*,nom,prenom,codelibcat,libcat_fr as libcat,corps.codecorps,".
                            " liblongcorps_fr as libcorps,pays.libpays as libpays_etab_orig,codereferent,codegesttheme,codeagentprevention,libcourtlieu as liblieu".
                            " from individu,individusejour,corps,cat,pays,lieu left join lieuagentprevention on lieu.codelieu=lieuagentprevention.codelieu".
                            " where individu.codeindividu=individusejour.codeindividu".
                            " and individusejour.codecorps=corps.codecorps".
                            " and corps.codecat=cat.codecat".
                            " and datedeb_sejour=".GetSQLValueString(date('Y/m/d'), "text").
                            " and individu.codelieu=lieu.codelieu".
                            " and individusejour.codepays_etab_orig=pays.codepays".
                            " order by datefin_sejour desc,nom,prenom";
  $rs=mysql_query($query_rs) or die(mysql_error()); 
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_amj=duree_aaaammjj($row_rs['datedeb_sejour'], $row_rs['datefin_sejour']);
    if($tab_amj['a']+$tab_amj['m']>=1 || $tab_amj['j']>5)
    { $tab_referent[$row_rs['codereferent']]['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
      $tab_gesttheme[$row_rs['codegesttheme']]['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
    }
    $tab_agentprevention[$row_rs['codeagentprevention']]['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
    $tab_agentprevention[$row_rs['codeagentprevention']]['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]['dureesejour']=($tab_amj['a']!=0?$tab_amj['a'].'a':'').($tab_amj['m']!=0?$tab_amj['m'].'m':'').($tab_amj['j']!=0?$tab_amj['j'].'j':'');
    if(($tab_amj['a']+$tab_amj['m']>=1 || $tab_amj['j']>=3) 
        && $row_rs['codelibcat']=='EXTERIEUR' && in_array($row_rs['codecorps'],array('54','56','58','59','60')))
    { $tab_relint[$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
    }
  }
  // Suppression eventuelle des sejours d'individuaction notes 'msgsejourtermine' mais dont le sejour est en cours car modif de date fin
  mysql_query(" delete from individuaction".
              " where codelibaction='msgsejourtermine'".
              " and  (codeindividu,numsejour) in (select codeindividu,numsejour from individusejour".
                                                " where ".periodeencours('datedeb_sejour','datefin_sejour').")")  or die(mysql_error());
  // Sejours termines
  $query_rs= 	"SELECT individu.codeindividu, individusejour.*,nom,prenom,codelibcat,libcat_fr as libcat,corps.codecorps,".
                            " liblongcorps_fr as libcorps,pays.libpays as libpays_etab_orig,codereferent,codegesttheme,montantfinancement,codeagentprevention,libcourtlieu as liblieu".
                            " from individu,individusejour,corps,cat,pays,lieu left join lieuagentprevention on lieu.codelieu=lieuagentprevention.codelieu".
                            " where individu.codeindividu=individusejour.codeindividu".
                            " and individusejour.codecorps=corps.codecorps".
                            " and corps.codecat=cat.codecat". //and codereferent='00560'
                            " and ".periodepassee('datefin_sejour').
                            " and individu.codelieu=lieu.codelieu".
                            " and individusejour.codepays_etab_orig=pays.codepays".
                            " and (individusejour.codeindividu,individusejour.numsejour) not in".
                            " (select codeindividu,numsejour from individuaction where codelibaction='msgsejourtermine')".
                            " order by datefin_sejour desc,nom,prenom";
  $rs=mysql_query($query_rs) or die(mysql_error()); 
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_amj=duree_aaaammjj($row_rs['datedeb_sejour'], $row_rs['datefin_sejour']);
    if($tab_amj['a']+$tab_amj['m']>=1 || $tab_amj['j']>5)
    { $tab_referent[$row_rs['codereferent']]['parti'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
      $tab_gesttheme[$row_rs['codegesttheme']]['parti'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
      if($row_rs['libcat']=='STAGIAIRE' && $row_rs['montantfinancement']!='')
      { $tab_stagiaire_pour_attestation[$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
      }
    }
    $tab_agentprevention[$row_rs['codeagentprevention']]['parti'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
  }
  
  // un mail par referent
  $liste_mail_referent='<table border="0">';
  foreach($tab_referent as $codereferent=>$un_tab_referent)
  { $estreferentpresent=($tab_individu[$codereferent]['presentparti']=='present');
    $erreur_envoi_mail="";
    $sujet='Pour information : s&eacute;jour(s) d&eacute;but&eacute;(s)/termin&eacute;(s)';
    $message='Bonjour,';
    $message.='<br><br>selon les informations dont nous disposons et enregistr&eacute;es sur le syst&egrave;me de gestion 12+, vous &ecirc;tes r&eacute;f&eacute;rent(e)';
    $message.=' de(s) personne(s) dont la dur&eacute;e de s&eacute;jour exc&egrave;de 5 jours et list&eacute;e(s) ci-dessous.';
    $message.='<br>Nous vous invitons, en cas d&rsquo;erreur, &agrave; r&eacute;pondre &agrave; ce message afin d&rsquo;en informer le service des ressources humaines.';
    foreach($un_tab_referent as $arrive_parti=>$tab_arrive_parti)
    { foreach($tab_arrive_parti as $codeindividusejour=>$un_individusejour)
      { $message.='<br>- '.($arrive_parti=='arrive'?'Arriv&eacute;e':'D&eacute;part').' le '.aaaammjj2jjmmaaaa($un_individusejour[($arrive_parti=='arrive'?'datedeb_sejour':'datefin_sejour')],'/').' : '.
                $tab_individu[$un_individusejour['codeindividu']]['libciv'].' '.$tab_individu[$un_individusejour['codeindividu']]['nom'].' '.$tab_individu[$un_individusejour['codeindividu']]['prenom'].
                ' - '.$un_individusejour['codelibcat'];
        if($arrive_parti=='parti' && $un_individusejour['codelibcat']=='DOCTORANT')
        { $query_rs_individuthese="select date_soutenance from individuthese".
                                  " where codeindividu=".GetSQLValueString($un_individusejour['codeindividu'], "text").
                                  " and numsejour=".GetSQLValueString($un_individusejour['numsejour'], "text");
          $rs_individuthese=mysql_query($query_rs_individuthese) or die(mysql_error());
          if($row_rs_individuthese = mysql_fetch_assoc($rs_individuthese))
          { if($row_rs_individuthese['date_soutenance']=='')
            { $message.="<b> Attention, pas de date de soutenance : ABANDON de th&egrave;se ?</b>";
            }
          }
        }
      }
    }
    $message.="<br><br>Cordialement,";
    $message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";
    $to=$tab_individu[$codereferent]['prenom']." ".$tab_individu[$codereferent]['nom']." <".$tab_individu[$codereferent]['email'].">";
    if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
    {	$message.='<br>En test, destinataires en fin de message : '.$to;
      $to="TEST <".$GLOBALS['emailTest'].">";
    }
    
    list($headers,$message_html_txt)=mime_message(array ('Reply-To'=>$GLOBALS['emailRH'].' <'.$GLOBALS['emailRH'].'>','To' => $to,'Subject' => $sujet,'Message'=>$message));
    $liste_mail_referent.='<tr><td>';
    $liste_mail_referent.='<table border="1"><tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'.($estreferentpresent?'Mail envoy&eacute; &agrave; ':'<span style="color:red;">R&eacute;f&eacute;rent parti, pas de mail envoy&eacute; &agrave; </span>').
                          $tab_individu[$codereferent]['prenom'].' '.$tab_individu[$codereferent]['nom'].' '.$tab_individu[$codereferent]['email'].'</td></tr>';
    $liste_mail_referent.='<tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">';
    $erreur_envoi_mail="";
    if($GLOBALS['mode_avec_envoi_mail'] && $estreferentpresent)
    { $erreur_envoi_mail.=envoimail($headers, $message_html_txt);
      $liste_mail_referent.=$message;
      if($erreur_envoi_mail!="")
      { $liste_mail_referent.='<br><span style="color:#ff0000;">Une erreur d&rsquo;envoi de mail s&rsquo;est produite: </span>'.'<br>'.$erreur_envoi_mail;
      }
    }
    else
    { $liste_mail_referent.=$message;
    }
    $liste_mail_referent.='</td></tr></table>';
    $liste_mail_referent.='</td></tr>';
  }
  $liste_mail_referent.="</table>";
  
  // un mail par gesttheme
  $liste_mail_gesttheme='<table border="0">';
  foreach($tab_gesttheme as $codegesttheme=>$un_tab_gesttheme)
  { $estgestthemepresent=($tab_individu[$codegesttheme]['presentparti']=='present');
    $erreur_envoi_mail="";
    $sujet='Pour information : s&eacute;jour(s) d&eacute;but&eacute;(s)/termin&eacute;(s)';
    $message='Bonjour,';
    $message.='<br><br>selon les informations dont nous disposons et enregistr&eacute;es sur le syst&egrave;me de gestion 12+, vous assurez le suivi de(s) dossier(s)';
    $message.=' de(s) personne(s) dont la dur&eacute;e de s&eacute;jour exc&egrave;de 5 jours et list&eacute;e(s) ci-dessous.';
    $message.='<br>Chaque r&eacute;f&eacute;rent a &eacute;t&eacute; pr&eacute;venu de l&rsquo;arriv&eacute;e ou du d&eacute;part de chaque individu et est invit&eacute;, en cas d&rsquo;erreur, &agrave; en informer le service des ressources humaines.';
    //$message.='<br>Nous vous invitons, en cas d&rsquo;erreur, &agrave; r&eacute;pondre &agrave; ce message afin d&rsquo;en informer le service des ressources humaines.';
    foreach($un_tab_gesttheme as $arrive_parti=>$tab_arrive_parti)
    { foreach($tab_arrive_parti as $codeindividusejour=>$un_individusejour)
      { $message.='<br>- '.($arrive_parti=='arrive'?'Arriv&eacute;e':'D&eacute;part').' le '.aaaammjj2jjmmaaaa($un_individusejour[($arrive_parti=='arrive'?'datedeb_sejour':'datefin_sejour')],'/').' : '.
                $tab_individu[$un_individusejour['codeindividu']]['libciv'].' '.$tab_individu[$un_individusejour['codeindividu']]['nom'].' '.$tab_individu[$un_individusejour['codeindividu']]['prenom'].
                ' - '.$un_individusejour['codelibcat'];
        if($arrive_parti=='parti' && $un_individusejour['codelibcat']=='DOCTORANT')
        { $query_rs_individuthese="select date_soutenance from individuthese".
                                  " where codeindividu=".GetSQLValueString($un_individusejour['codeindividu'], "text").
                                  " and numsejour=".GetSQLValueString($un_individusejour['numsejour'], "text");
          $rs_individuthese=mysql_query($query_rs_individuthese) or die(mysql_error());
          if($row_rs_individuthese = mysql_fetch_assoc($rs_individuthese))
          { if($row_rs_individuthese['date_soutenance']=='')
            { $message.="<b> Attention, pas de date de soutenance : ABANDON de th&egrave;se ?</b>";
            }
          }
        }
      }
    }
    $message.="<br><br>Cordialement,";
    $message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
  
    $to=$tab_individu[$codegesttheme]['prenom']." ".$tab_individu[$codegesttheme]['nom']." <".$tab_individu[$codegesttheme]['email'].">";
    if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
    {	$message.='<br>En test, destinataires en fin de message : '.$to;
      $to="TEST <".$GLOBALS['emailTest'].">";
    }
    
    list($headers,$message_html_txt)=mime_message(array ('Reply-To'=>$GLOBALS['emailRH'].' <'.$GLOBALS['emailRH'].'>','To' => $to,'Subject' => $sujet,'Message'=>$message));
    $liste_mail_gesttheme.='<tr><td>';
    $liste_mail_gesttheme.='<table border="1"><tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'.($estgestthemepresent?'Mail envoy&eacute; &agrave; ':'<span style="color:red;">Gestionnaire parti, pas de mail envoy&eacute; &agrave; </span>').
                          $tab_individu[$codegesttheme]['prenom'].' '.$tab_individu[$codegesttheme]['nom'].' '.$tab_individu[$codegesttheme]['email'].'</td></tr>';
    $liste_mail_gesttheme.='<tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'; /* */
    $erreur_envoi_mail="";
    if($GLOBALS['mode_avec_envoi_mail'] && $estgestthemepresent)
    { $erreur_envoi_mail.=envoimail($headers, $message_html_txt);
      $liste_mail_gesttheme.=$message;
      if($erreur_envoi_mail!="")
      { $liste_mail_gesttheme.='<br><span style="color:#ff0000;">Une erreur d&rsquo;envoi de mail s&rsquo;est produite: </span>'.'<br>'.$erreur_envoi_mail;
      }
    }
    else
    { $liste_mail_gesttheme.=$message;
    }
    $liste_mail_gesttheme.='</td></tr></table>';
    $liste_mail_gesttheme.='</td></tr>';
  }
  $liste_mail_gesttheme.="</table>";
  
  // un mail par agentprevention
  $liste_mail_agentprevention='<table border="0">';
  foreach($tab_agentprevention as $codeagentprevention=>$un_tab_agentprevention)
  { $estagentpreventionpresent=($tab_individu[$codeagentprevention]['presentparti']=='present');
    $erreur_envoi_mail="";
    $sujet='Pour information : s&eacute;jour(s) d&eacute;but&eacute;(s)/termin&eacute;(s)';
    $message='Bonjour,';
    $message.='<br><br>selon les informations dont nous disposons et enregistr&eacute;es sur le syst&egrave;me de gestion 12+, ';
    $message.='le(s) s&eacute;jour(s) suivant(s) d&eacute;bute(nt) ou se termine(nt) sur le(s) lieu(x) dont vous &ecirc;tes Agent de Pr&eacute;vention :';
    //$message.='<br>Nous vous invitons, en cas d&rsquo;erreur, &agrave; r&eacute;pondre &agrave; ce message afin d&rsquo;en informer le service des ressources humaines.';
    foreach($un_tab_agentprevention as $arrive_parti=>$tab_arrive_parti)
    { foreach($tab_arrive_parti as $codeindividusejour=>$un_individusejour)
      { $message.='<br>- '.($arrive_parti=='arrive'?'Arriv&eacute;e':'D&eacute;part').' le '.aaaammjj2jjmmaaaa($un_individusejour[($arrive_parti=='arrive'?'datedeb_sejour':'datefin_sejour')],'/').($arrive_parti=='arrive'?' ('.$un_individusejour['dureesejour'].')':'').' : '.
                $tab_individu[$un_individusejour['codeindividu']]['libciv'].' '.$tab_individu[$un_individusejour['codeindividu']]['nom'].' '.$tab_individu[$un_individusejour['codeindividu']]['prenom'].
                ' - '.$un_individusejour['codelibcat'].' - '.$un_individusejour['liblieu'];
      }
    }
    $message.="<br><br>Cordialement,";
    $message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
    $to=$tab_individu[$codeagentprevention]['prenom']." ".$tab_individu[$codeagentprevention]['nom']." <".$tab_individu[$codeagentprevention]['email'].">";
    if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
    {	$message.='<br>En test, destinataires en fin de message : '.$to;
      $to="TEST <".$GLOBALS['emailTest'].">";
    }
    
    list($headers,$message_html_txt)=mime_message(array ('Reply-To'=>$GLOBALS['emailRH'].' <'.$GLOBALS['emailRH'].'>','To' => $to,'Subject' => $sujet,'Message'=>$message));
    $liste_mail_agentprevention.='<tr><td>';
    $liste_mail_agentprevention.='<table border="1"><tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'.($estagentpreventionpresent?'Mail envoy&eacute; &agrave; ':'<span style="color:red;">AP parti, pas de mail envoy&eacute; &agrave; </span>').
                          $tab_individu[$codeagentprevention]['prenom'].' '.$tab_individu[$codeagentprevention]['nom'].' '.$tab_individu[$codeagentprevention]['email'].'</td></tr>';
    $liste_mail_agentprevention.='<tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'; /* */
    $erreur_envoi_mail="";
    if($GLOBALS['mode_avec_envoi_mail'] && $estagentpreventionpresent)
    { $erreur_envoi_mail.=envoimail($headers, $message_html_txt);
      $liste_mail_agentprevention.=$message;
      if($erreur_envoi_mail!="")
      { $liste_mail_agentprevention.='<br><span style="color:#ff0000;">Une erreur d&rsquo;envoi de mail s&rsquo;est produite: </span>'.'<br>'.$erreur_envoi_mail;
      }
    }
    else
    { $liste_mail_agentprevention.=$message;
    }
    $liste_mail_agentprevention.='</td></tr></table>';
    $liste_mail_agentprevention.='</td></tr>';
  }
  $liste_mail_agentprevention.="</table>";
  
  // relint
  $liste_relint="";
  $liste_mail_resp_relint="";
  if(count($tab_relint)>0)
  { foreach($tab_relint as $codeindividusejour=>$un_individusejour)
    { $query_rs="select titre_fr from individusujet,sujet".
                      " where codeindividu=".GetSQLValueString($un_individusejour['codeindividu'], "text").
                      " and numsejour=".GetSQLValueString($un_individusejour['numsejour'], "text").
                      " and individusujet.codesujet=sujet.codesujet";
      $rs=mysql_query($query_rs) or die(mysql_error());
      $titre_fr='';
      if($row_rs = mysql_fetch_assoc($rs))
      { $titre_fr=$row_rs['titre_fr'];
      }
    
      $liste_relint.='<br>- du '.aaaammjj2jjmmaaaa($un_individusejour['datedeb_sejour'],'/').' au '.aaaammjj2jjmmaaaa($un_individusejour['datefin_sejour'],'/').' : '.
              $tab_individu[$un_individusejour['codeindividu']]['libciv'].' '.$tab_individu[$un_individusejour['codeindividu']]['nom'].' '.$tab_individu[$un_individusejour['codeindividu']]['prenom'].
              ' - '.$un_individusejour['libcorps'].' ('.$tab_individu[$un_individusejour['codereferent']]['prenom'].' '.$tab_individu[$un_individusejour['codereferent']]['nom'].')'.
              '<br>&nbsp;&nbsp;&nbsp;&nbsp;Sujet : '.$titre_fr.
              '<br>&nbsp;&nbsp;&nbsp;&nbsp;Etablissement : '.$un_individusejour['etab_orig'].' - '.$un_individusejour['ville_etab_orig'].' - '.$un_individusejour['libpays_etab_orig'];
    }
    $liste_mail_resp_relint='<table border="0">';
    foreach($tab_resp_relint as $code_resp_relint=>$un_resp_relint)
    { $est_resp_relintpresent=($tab_individu[$code_resp_relint]['presentparti']=='present');
      $erreur_envoi_mail="";
      $sujet='Pour information : s&eacute;jour(s) d&eacute;but&eacute;(s)';
      $message='Bonjour,';
      $message.='<br><br>selon les informations dont nous disposons et enregistr&eacute;es sur le syst&egrave;me de gestion 12+, ';
      $message.=(count($tab_relint)==1?"le s&eacute;jour de trois jours et plus d&rsquo;une personne ext&eacute;rieure d&eacute;bute :":"les s&eacute;jours de trois jours et plus de personnes ext&eacute;rieures d&eacute;butent :");
      $message.=$liste_relint;
      $message.="<br><br>Cordialement,";
      $message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+";	
      
      $to=$tab_individu[$code_resp_relint]['prenom']." ".$tab_individu[$code_resp_relint]['nom']." <".$tab_individu[$code_resp_relint]['email'].">";
      if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
      {	$message.='<br>En test, destinataires en fin de message : '.$to;
        $to="TEST <".$GLOBALS['emailTest'].">";
      }
    
      list($headers,$message_html_txt)=mime_message(array ('Reply-To'=>$GLOBALS['emailRH'].' <'.$GLOBALS['emailRH'].'>','To' => $to,'Subject' => $sujet,'Message'=>$message));
      $liste_mail_resp_relint.='<tr><td>';
      $liste_mail_resp_relint.='<table border="1"><tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'.($est_resp_relintpresent?'Mail envoy&eacute; &agrave; ':'<span style="color:red;">Resp Rel. Int. parti, pas de mail envoy&eacute; &agrave; </span>').
                            $tab_individu[$code_resp_relint]['prenom'].' '.$tab_individu[$code_resp_relint]['nom'].' '.$tab_individu[$code_resp_relint]['email'].'</td></tr>';
      $liste_mail_resp_relint.='<tr><td style="border-style:solid;border-color:#FFFFFF;border-width:2px">'; /* */
      $erreur_envoi_mail="";
      if($GLOBALS['mode_avec_envoi_mail'] && $est_resp_relintpresent)
      { $erreur_envoi_mail.=envoimail($headers, $message_html_txt);
        $liste_mail_resp_relint.=$message;
        if($erreur_envoi_mail!="")
        { $liste_mail_resp_relint.='<br><span style="color:#ff0000;">Une erreur d&rsquo;envoi de mail s&rsquo;est produite: </span>'.'<br>'.$erreur_envoi_mail;
        }
      }
      else
      { $liste_mail_resp_relint.=$message;
      }
      $liste_mail_resp_relint.='</td></tr></table>';
      $liste_mail_resp_relint.='</td></tr>';
    }
    $liste_mail_resp_relint.="</table>";
  }
  
   //la liste des sejours termines y compris -5j est celle de $tab_agentprevention
  foreach($tab_agentprevention as $codeagentprevention=>$un_tab_agentprevention)
  { if(isset($un_tab_agentprevention['parti']))
    { foreach($un_tab_agentprevention['parti'] as $codeindividusejour=>$un_individusejour)
      { $codeindividu=$un_individusejour['codeindividu'];
        $numsejour=$un_individusejour['numsejour'];
        $updateSQL=	"delete from individuaction where codeindividu=".GetSQLValueString($codeindividu, "text").
                      " and numsejour=".GetSQLValueString($numsejour, "text")." and codelibaction='msgsejourtermine'"; 
        mysql_query($updateSQL);
        $updateSQL=	"insert into individuaction (codeindividu, numsejour, codelibaction, datedeb_action,datefin_action,codeacteur)".
                      " values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($numsejour, "text").",".
                            "'msgsejourtermine',".GetSQLValueString($aujourdhui, "text").",'','')";
        $message_webmaster.="<br>".$updateSQL;
        mysql_query($updateSQL);
      }
    }
  }
  
  // mail rh
	if(count($tab_agentprevention)>=1 || $nb_individuarrivant>=1)
	{ $sujet='Pour information : messages  aux r&eacute;f&eacute;rents, gestionnaires et agents de pr&eacute;vention des s&eacute;jour(s) d&eacute;but&eacute;(s)/termin&eacute;(s)';
		$message='<p style="text-align:center;font-size:18pt">Mails aux arrivants</p>'.$liste_mail_arrivant.
				'<p style="text-align:center;font-size:18pt">Mails aux r&eacute;f&eacute;rents</p>'.$liste_mail_referent.
				'<br><p style="text-align:center;;font-size:18pt">Mails aux gestionnaires</p>'.$liste_mail_gesttheme.
				'<br><p style="text-align:center;;font-size:18pt">Mails aux agents de prevention</p>'.$liste_mail_agentprevention.
				'<br><p style="text-align:center;;font-size:18pt">Mail resp. rel. internationales</p>'.$liste_mail_resp_relint;
				
		$to=$GLOBALS['emailRH'].',' .$GLOBALS['webMaster']['email'];
		if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
		{	$message.='<br>En test, destinataires en fin de message : '.$to;
			$to="TEST <".$GLOBALS['emailTest'].">";
		}
		
		list($headers,$message_html_txt)=mime_message(array ('To' => $to,'Subject' => $sujet,'Message'=>$message));
		if($GLOBALS['mode_avec_envoi_mail'])
		{ $erreur_envoi_mail=envoimail($headers, $message_html_txt);
		}
		else
		{ echo $sujet.'<br><br>'.$message;
		}
	}
	else
	{ $message_webmaster.="Pas d&rsquo;arriv&eacute; ou parti : pas de messages r&eacute;f&eacute;rents";
	}
 
  
  if(count($tab_stagiaire_pour_attestation)>=1)
  {	foreach($tab_stagiaire_pour_attestation as $un_codenumstagiaire=>$un_tab_stagiaire_pour_attestation)
    { $sujet='Attestation &agrave; &eacute;tablir pour le dossier '.$un_codenumstagiaire.' de '.$tab_individu[$un_tab_stagiaire_pour_attestation['codeindividu']]['libciv'].' '.$tab_individu[$un_tab_stagiaire_pour_attestation['codeindividu']]['nom'].' '.$tab_individu[$un_tab_stagiaire_pour_attestation['codeindividu']]['prenom'];
      $message='Bonjour,';
      $message.='<br><br>une attestation de stage doit &ecirc;tre &eacute;tablie pour '.$tab_individu[$un_tab_stagiaire_pour_attestation['codeindividu']]['libciv'].' '.$tab_individu[$un_tab_stagiaire_pour_attestation['codeindividu']]['nom'].' '.$tab_individu[$un_tab_stagiaire_pour_attestation['codeindividu']]['prenom'].
                ' qui a effectu&eacute; un s&eacute;jour au laboratoire du '.aaaammjj2jjmmaaaa($un_individusejour['datedeb_sejour'],'/').' au '.aaaammjj2jjmmaaaa($un_individusejour['datefin_sejour'],'/');
      $message.="<br><br>Cordialement,";
      
      $to=$GLOBALS['emailRH'].',' .$GLOBALS['webMaster']['email'];
      if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
      {	$message.='<br>En test, destinataires en fin de message : '.$to;
        $to="TEST <".$GLOBALS['emailTest'].">";
      }
    
      list($headers,$message_html_txt)=mime_message(array ('To' => $to,'Subject' => $sujet,'Message'=>$message));
      if($GLOBALS['mode_avec_envoi_mail'])
      { $erreur_envoi_mail=envoimail($headers, $message_html_txt);
      }
      else
      { echo $sujet.'<br><br>'.$message;
      }
    }
  }
	// Personnes sans emploi dans moins d'un mois (CDD, DOCTORANT, POSTDOC)
	$message_liste_sans_emploi="";
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
	{	$first=true;
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
					if($row_rs_individuemploi['datefin_emploi']!='' && $row_rs_individuemploi['datefin_emploi']<$row_rs_individusejour['datefin_sejour'])
					{ $tab_amj=duree_aaaammjj(date('Y/m/d'), $row_rs_individuemploi['datefin_emploi']);
					//echo $row_rs_individuemploi['codeindividu'].' '.$tab_amj['a'].' '.$tab_amj['m'].' '.$tab_amj['j'].'<br>';
						if($tab_amj['a']<=0 && $tab_amj['m']<=0)// nbjours 
						{ if($first)
							{ $message_liste_sans_emploi.="<br><br>";
								$message_liste_sans_emploi.='<table border="1" cellpadding="3" align="center">';
								$message_liste_sans_emploi.='<tr><td align="center" colspan="8"><b>CDD, DOCTORANT, POSTDOC sans emploi dans moins d&rsquo;un mois</b></td></tr>';
								$message_liste_sans_emploi.='<tr><td align="center"><b>Num dossier</b></td><td align="center"><b>Nom pr&eacute;nom</b></td><td align="center"><b>Cat&eacute;gorie</b></td>'.
													'<td align="center"><b>Fin s&eacute;jour</b><td align="center"><b>Fin emploi</b></td><td align="center"><b>Expire dans</b></td><td align="center"><b>R&eacute;f&eacute;rent</b></td><td align="center"><b>Suivi par</b></td></tr>'; 
								$first=false;
							}
							$tab_individu[$row_rs_individusejour['codeindividu']][$row_rs_individusejour['numsejour']]['msgsansemploi']=$row_rs_individusejour;
							$message_liste_sans_emploi.="<tr><td>".$row_rs_individusejour['codeindividu'].'.'.$row_rs_individusejour['numsejour'].'</td>';
							$message_liste_sans_emploi.='<td>'.$row_rs_individusejour['nom'].' '.$row_rs_individusejour['prenom'].'</td><td>'.$row_rs_individusejour['libcat'].
																					'</td><td nowrap>'.aaaammjj2jjmmaaaa($row_rs_individusejour['datefin_sejour'],'/').' </td><td nowrap><b>'.
																					/* $comp_fin_sejour_emloi. */'</b> '.aaaammjj2jjmmaaaa($row_rs_individuemploi['datefin_emploi'],'/').'</td>';
							$message_liste_sans_emploi.="<td>".$tab_amj['j'].($tab_amj['j']==''?'':'j').'</td>';
							$un_referent=$tab_individu[$row_rs_individusejour['codereferent']];
							$un_gesttheme=$tab_individu[$row_rs_individusejour['codegesttheme']];
							$message_liste_sans_emploi.='<td>'.$un_referent['nom'].' '.$un_referent['prenom'].'</td><td nowrap>'.$un_gesttheme['nom'].' '.$un_gesttheme['prenom'].'</td>';
							$message_liste_sans_emploi.="</tr>";
						}
					}
				}
			}
		}
		if($message_liste_sans_emploi!="")
		{ $message_liste_sans_emploi.="</table>";
		}
		if($first===false)//au moins un sans emploi
		{ $sujet="Pour information : CDD, Doctorants, Postdocs sans emploi dans moins d'un mois";
			$to=$GLOBALS['emailRH'].',' .$GLOBALS['webMaster']['email'];
			if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
			{	$message.='<br>En test, destinataires en fin de message : '.$to;
				$to="TEST <".$GLOBALS['emailTest'].">";
			}
			list($headers,$message_html_txt)=mime_message(array ('To' =>$to ,
																														'Subject' => $sujet ,'Message'=>$message_liste_sans_emploi));
			if($GLOBALS['mode_avec_envoi_mail'])
			{ $erreur_envoi_mail=envoimail($headers, $message_html_txt);
			}
			else
			{ echo $sujet.'<br><br>'.$message_liste_sans_emploi;
			}
		}
	}
}
else if($_REQUEST['db']=='imopa') 
{ $tab_mail_unique=array();//evite d'avoir deux fois le meme destinataire (meme adresse mail) dans le champ To:
  $tab_destinataires=array();
	$tab_individu_arrive_parti=array();
  $tab_individu=array();
  $message_webmaster.="Bonjour,<br><br>";
  $message="";
	$GLOBALS['mode_avec_envoi_mail']=true;
	
  
  // liste de tous les individus referents possibles 
  $query_rs="select individu.codeindividu, libcourt_fr as libciv, login, nom, prenom, email,if(isnull(datedeb_sejour),'parti','present') as presentparti from civilite,individu".
            " left join individusejour on individu.codeindividu=individusejour.codeindividu and ".periodeencours('datedeb_sejour','datefin_sejour').
            " where individu.codeciv=civilite.codeciv and individu.codeindividu<>''";
  $rs=mysql_query($query_rs) or die(mysql_error());
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_individu[$row_rs['codeindividu']]=$row_rs;
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

  // Sejours debutent aujourd'hui
  $query_rs= "SELECT individu.codeindividu,individusejour.*,nom,prenom,codelibcat,libcat_fr as libcat,corps.codecorps,".
                            " liblongcorps_fr as libcorps,pays.libpays as libpays_etab_orig,codereferent,codegesttheme,codeagentprevention,libcourtlieu as liblieu".
                            " from individu,individusejour,corps,cat,pays,lieu left join lieuagentprevention on lieu.codelieu=lieuagentprevention.codelieu".
                            " where individu.codeindividu=individusejour.codeindividu".
                            " and individusejour.codecorps=corps.codecorps".
                            " and corps.codecat=cat.codecat".
                            " and datedeb_sejour=".GetSQLValueString(date("Y/m/d"), "text").
                            " and individu.codelieu=lieu.codelieu".
                            " and individusejour.codepays_etab_orig=pays.codepays".
                            " order by datefin_sejour desc,nom,prenom";
  $rs=mysql_query($query_rs) or die(mysql_error()); 
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_amj=duree_aaaammjj($row_rs['datedeb_sejour'], $row_rs['datefin_sejour']);
    $tab_individu_arrive_parti['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
    $tab_individu_arrive_parti['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]['dureesejour']=($tab_amj['a']!=0?$tab_amj['a'].'a':'').($tab_amj['m']!=0?$tab_amj['m'].'m':'').($tab_amj['j']!=0?$tab_amj['j'].'j':'');
		$listetheme="";//pas de theme par defaut
		$first=true;
		foreach($tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]] as $codetheme=>$libtheme)
		{ $listetheme.=($first?"":", ").$libtheme;
			$first=false;
		}
		$tab_individu_arrive_parti['arrive'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]['listetheme']=$listetheme;
	}
  // Suppression eventuelle des sejours d'individuaction notes 'msgsejourtermine' mais dont le sejour est en cours car modif de date fin
  mysql_query(" delete from individuaction".
              " where codelibaction='msgsejourtermine'".
              " and  (codeindividu,numsejour) in (select codeindividu,numsejour from individusejour".
                                                " where ".periodeencours('datedeb_sejour','datefin_sejour').")")  or die(mysql_error());
  // Sejours termines
  $query_rs= 	"SELECT individu.codeindividu, individusejour.*,nom,prenom,codelibcat,libcat_fr as libcat,corps.codecorps,".
                            " liblongcorps_fr as libcorps,pays.libpays as libpays_etab_orig,codereferent,codegesttheme,montantfinancement,codeagentprevention,libcourtlieu as liblieu".
                            " from individu,individusejour,corps,cat,pays,lieu left join lieuagentprevention on lieu.codelieu=lieuagentprevention.codelieu".
                            " where individu.codeindividu=individusejour.codeindividu".
                            " and individusejour.codecorps=corps.codecorps".
                            " and corps.codecat=cat.codecat".
                            " and ".periodepassee('datefin_sejour').
                            " and individu.codelieu=lieu.codelieu".
                            " and individusejour.codepays_etab_orig=pays.codepays".
                            " and (individusejour.codeindividu,individusejour.numsejour) not in".
                            " (select codeindividu,numsejour from individuaction where codelibaction='msgsejourtermine')".
                            " order by datefin_sejour desc,nom,prenom";
  $rs=mysql_query($query_rs) or die(mysql_error()); 
  while($row_rs = mysql_fetch_assoc($rs))
  { $tab_amj=duree_aaaammjj($row_rs['datedeb_sejour'], $row_rs['datefin_sejour']);
    $tab_individu_arrive_parti['parti'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
		$listetheme="";//pas de theme par defaut
		$first=true;
		foreach($tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]] as $codetheme=>$libtheme)
		{ $listetheme.=($first?"":", ").$libtheme;
			$first=false;
		}
		$tab_individu_arrive_parti['parti'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]['listetheme']=$listetheme;
  }
  
	if(isset($tab_individu_arrive_parti['arrive']) || isset($tab_individu_arrive_parti['parti']))
  {// un mail pour liste ap et srh
		$erreur_envoi_mail="";
		$sujet='Pour information : s&eacute;jour(s) d&eacute;but&eacute;(s)/termin&eacute;(s)';
		$message='Bonjour,';
		$message.='<br><br>selon les informations dont nous disposons et enregistr&eacute;es sur le syst&egrave;me de gestion 12+, ';
		$message.='le(s) s&eacute;jour(s) suivant(s) d&eacute;bute(nt) ou se termine(nt) :';
		foreach($tab_individu_arrive_parti as $arrive_parti=>$tab_arrive_parti)
		{ foreach($tab_arrive_parti as $codeindividusejour=>$un_individusejour)
			{ $message.='<br>- '.($arrive_parti=='arrive'?'Arriv&eacute;e':'D&eacute;part').' le '.aaaammjj2jjmmaaaa($un_individusejour[($arrive_parti=='arrive'?'datedeb_sejour':'datefin_sejour')],'/').($arrive_parti=='arrive'?' ('.$un_individusejour['dureesejour'].')':'').' : '.
								$tab_individu[$un_individusejour['codeindividu']]['libciv'].' '.$tab_individu[$un_individusejour['codeindividu']]['nom'].' '.$tab_individu[$un_individusejour['codeindividu']]['prenom'].
								' - '.$un_individusejour['codelibcat'].' - '.$un_individusejour['listetheme'];
			}
		}
		$message.="<br><br>Cordialement,";
		$message.="<br><br>Message g&eacute;n&eacute;r&eacute; automatiquement par le Serveur 12+ ".$GLOBALS['acronymelabo'];	
		$to="AP <".$GLOBALS['emailACMO'].">".", SRH <".$GLOBALS['emailRH'].">".", WebMaster <".$GLOBALS['webMaster']['email'].">";
		if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
		{	$message.='<br>En test, destinataires en fin de message : '.$to;
			$to="TEST <".$GLOBALS['emailTest'].">";
		}
		list($headers,$message_html_txt)=mime_message(array ('Reply-To'=>$GLOBALS['emailRH'].' <'.$GLOBALS['emailRH'].'>','To' => $to,'Subject' => $sujet,'Message'=>$message));
		$erreur_envoi_mail="";
		if($GLOBALS['mode_avec_envoi_mail'])
		{ $erreur_envoi_mail.=envoimail($headers, $message_html_txt);
			$liste_mail_agentprevention.=$message;
			if($erreur_envoi_mail!="")
			{ $liste_mail_agentprevention.='<br><span style="color:#ff0000;">Une erreur d&rsquo;envoi de mail s&rsquo;est produite: </span>'.'<br>'.$erreur_envoi_mail;
			}
		}
		else
		{ $liste_mail_agentprevention.=$message;
		}
  	echo $liste_mail_agentprevention;
		// liste des sejours termines
		foreach($tab_individu_arrive_parti as  $arrive_parti=>$tab_arrive_parti)
		{ if($arrive_parti=='parti')
			{ foreach($tab_arrive_parti as $codeindividusejour=>$un_individusejour)
				{ $codeindividu=$un_individusejour['codeindividu'];
					$numsejour=$un_individusejour['numsejour'];
					$updateSQL=	"delete from individuaction where codeindividu=".GetSQLValueString($codeindividu, "text").
												" and numsejour=".GetSQLValueString($numsejour, "text")." and codelibaction='msgsejourtermine'"; 
					mysql_query($updateSQL);
					$updateSQL=	"insert into individuaction (codeindividu, numsejour, codelibaction, datedeb_action,datefin_action,codeacteur)".
												" values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($numsejour, "text").",".
															"'msgsejourtermine',".GetSQLValueString($aujourdhui, "text").",'','')";
					mysql_query($updateSQL);
				}
			}
		}
  
		// mail webmaster
		if($GLOBALS['mode_avec_envoi_mail'])
		{ $sujet='Pour information : messages  aux agents de pr&eacute;vention des s&eacute;jour(s) d&eacute;but&eacute;(s)/termin&eacute;(s)';
			$to=$GLOBALS['webMaster']['email'];
			if($GLOBALS['mode_exploit']=='test' || $GLOBALS['mode_exploit']=='demo')
			{	$message.='<br>En test, destinataires en fin de message : '.$to;
				$to="TEST <".$GLOBALS['emailTest'].">";
			}
		
			list($headers,$message_html_txt)=mime_message(array ('To' => $to,'Subject' => $sujet,
					'Message'=>'<p style="text-align:center;font-size:18pt">Mail aux AP, SRH</p>'.$liste_mail_agentprevention
					));
			$erreur_envoi_mail=envoimail($headers, $message_html_txt);
		}
  }
	else
	{ $message_webmaster.="Pas d&rsquo;arriv&eacute; ou parti : pas de messages AP, SRH";
	}
}


$nb_fichiers=0;
copierep($GLOBALS['path_to_rep_upload'],$GLOBALS['path_to_rep_upload_sauve']);
$message_webmaster.="<br>".$nb_fichiers.' copies';

//suppression des sauvegardes bd passees
$date_jour_moins_7=date("Ymd",mktime(0,0,0,date("m"),date("d")-7,(date("Y"))));
$premier_du_jour=false;
for($i=0;$i<=23;$i++)
{ $heure=str_pad((string)$i, 2 , "0", STR_PAD_LEFT);
	if(file_exists ($GLOBALS['path_to_rep_sauve'].'/sauvebd'.$_REQUEST['db'].'_'.$date_jour_moins_7.'_'.$heure.'h.sql'))
	{ if($premier_du_jour==false)//on conserve le premier trouve de 0 a 23h
		{ $premier_du_jour=true;
			$message_webmaster.='<br> Conserve '.$GLOBALS['path_to_rep_sauve'].'/sauvebd'.$_REQUEST['db'].'_'.$date_jour_moins_7.'_'.$heure.'h.sql';
		}
		else
		{ unlink ( $GLOBALS['path_to_rep_sauve'].'/sauvebd'.$_REQUEST['db'].'_'.$date_jour_moins_7.'_'.$heure.'h.sql'); 
			$message_webmaster.='<br> SUPPR '.$GLOBALS['path_to_rep_sauve'].'/sauvebd'.$_REQUEST['db'].'_'.$date_jour_moins_7.'_'.$heure.'h.sql';
		}
	}
}

$subject="Pour information admin : script batch_de_nuit.bat";
list($headers,$message_html_txt)=mime_message(array ('From' => "Serveur 12+ <".$GLOBALS['webMaster']['email'].">",'To' => $GLOBALS['webMaster']['email'],'Reply-To' => "Serveur 12+<".$GLOBALS['webMaster']['email'].">",'Subject' => $subject,'Message'=>$message_webmaster));
if($GLOBALS['mode_avec_envoi_mail'])
{ envoimail($headers, $message_html_txt);
}
echo $message_webmaster;

if(isset($rs))mysql_free_result($rs);
if(isset($rs_individustatutvisa))mysql_free_result($rs_individustatutvisa);

function copierep($nomreporig,$nomrepdest)
{ global $nb_fichiers,$message_webmaster;
	$contenurep = dir($nomreporig);
	while (false !== ($entry = $contenurep->read()))  
	{	clearstatcache();
		if(!is_dir($nomreporig.'/'.$entry))
		{	$nb_fichiers++;
			$message_webmaster.="<br>". date ("F d Y H:i:s.", filemtime($nomreporig.'/'.$entry)).' '.$nomreporig.'/'.$entry.' -> '.$nomrepdest.'/'.$entry;
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



?>