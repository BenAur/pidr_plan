<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_contexte=array('prog'=>'gestionindividus','codeuser'=>$codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);

$nbtotalcarligne=0;//nb car des lignes envoyees
$temps_debut=microtime(true);
$afficheduree=false;//true;
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timedeb=microtime(true);
	$timedeb_avanthtml=$timedeb;
 	//echo 'usage memoire '.memory_get_usage ();
} 
if($admin_bd)
{ 
	/* foreach($_POST as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}
	 foreach($_GET as $postkey=>$postval)
	{ echo $postkey." : ".$postval."<br>";
	}*/ 
}
$form_gestionindividus_voir='gestionindividus_voir';
// Par défaut, codeindividu est initialise 
$tab_etat_individu=array('preaccueil','accueil','sejourpartinonvalide','anomalie','present','parti');//etats possibles (preaccueil, ...
$tab_etat_individu_entete=array(
						'preaccueil'=>'<span class="rougegrascalibri">Pr&eacute;-accueil </span><span class="infomauve"> (date d&rsquo;arriv&eacute;e sup&eacute;rieure &agrave; aujourd&rsquo;hui)</span>',
						'accueil'=>'<span class="rougegrascalibri">Accueil</span><span class="infomauve"> ((date d&rsquo;arriv&eacute;e effective renseign&eacute;e) mais manque au moins un visa</span>',
						'sejourpartinonvalide'=>'<span class="rougegrascalibri">Non valid&eacute;</span><span class="infomauve"> (s&eacute;jours pass&eacute;s les plus r&eacute;cents non valid&eacute;s)</span>',
						'anomalie'=>'<span class="rougegrascalibri">Non r&eacute;pertori&eacute;</span><span class="infomauve"> (individus ne figurant pas dans les autres listes : incoh&eacute;rences de dossiers non trait&eacute;es par le syst&egrave;me de gestion) </span>',
						'present'=>'<span class="rougegrascalibri">Présent</span><span class="infomauve"> (date d&rsquo;arriv&eacute;e renseign&eacute;e ET (date de d&eacute;part non renseign&eacute;e ou sup&eacute;rieure &agrave; aujourd&rsquo;hui)) : personne pr&eacute;sente et dossier complet)</span>',
						'parti'=>'<span class="rougegrascalibri">Parti</span><span class="infomauve"> (date de d&eacute;part inf&eacute;rieure &agrave; aujourd&rsquo;hui)</span>');
foreach($tab_etat_individu as $key=>$etat_individu_val)
{ $tab_rs_individu[$etat_individu_val]=array();
}
$tab_ind_champrecherche=array('nomprenomindividu'=>'nompr&eacute;nom','codeindividu'=>'N&deg; dossier');
$erreur="";
$erreur_envoimail="";
$msg_erreur_objet_mail="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";
$nb_col_a_afficher="14";// ou 15 si colonne 'invalider'
$codelibcat="";
$sujet_valide_par_theme="";//une des conditions d'affichage de l'icone 'valider' ou 'attente validation sujet' pour srh, du 
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
$codevisa_a_apposer=isset($_GET['codevisa_a_apposer'])?$_GET['codevisa_a_apposer']:(isset($_POST['codevisa_a_apposer'])?$_POST['codevisa_a_apposer']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");
// envoyer_mail : case a cocher de confirmer_action_individu.php
$envoyer_mail=isset($_GET['envoyer_mail'])?true:(isset($_POST['envoyer_mail'])?true:false);
$hors_effectif_existe=false;
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
// définis par $estreferent et $estresptheme
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$estgesttheme=array_key_exists('theme',$tab_roleuser) && !$estresptheme;// 20161213
$est_admin=array_key_exists('du',$tab_roleuser)||array_key_exists('admingestfin',$tab_roleuser)||$admin_bd || droit_acces($tab_contexte);

if(!isset($_SESSION['codeindividu'])) { $_SESSION['codeindividu']=$codeindividu;}

// b_voir_.... : si ce bouton submit est envoye on shifte l'affichage des sujets depasses
//if(!isset($_SESSION['b_ind_etre_admin'])) { $_SESSION['b_ind_etre_admin']=true;}
if(!isset($_SESSION['b_ind_voir_presents'])) { $_SESSION['b_ind_voir_presents']=true;}
if(!isset($_SESSION['b_ind_voir_partis'])) { $_SESSION['b_ind_voir_partis']=false;}
//if(!isset($_SESSION['b_voir_tous_themes'])){ $_SESSION['b_voir_tous_themes']=(array_key_exists('srh',$tab_roleuser) || $est_admin)?true:false;}
// 20161213
if(!isset($_SESSION['b_ind_voir_mes_dossiers'])){ $_SESSION['b_ind_voir_mes_dossiers']=(array_key_exists('srh',$tab_roleuser) || $est_admin)?false:true;}
if(!isset($_SESSION['b_lister_sejours'])) { $_SESSION['b_lister_sejours']=false;}
//b_rechercher textenomrecherche
if(!isset($_SESSION['ind_champrecherche'])){ $_SESSION['ind_champrecherche']='nomprenomindividu';}
if(!isset($_SESSION['ind_texterecherche'])){ $_SESSION['ind_texterecherche']='';}
$_SESSION['ind_champrecherche']=isset($_POST['ind_champrecherche'])?$_POST['ind_champrecherche']:$_SESSION['ind_champrecherche'];
$_SESSION['ind_texterecherche']=isset($_POST['ind_texterecherche'])?$_POST['ind_texterecherche']:"";
// filtre par categorie
if(!isset($_SESSION['select_ind_voir_categorie'])) { $_SESSION['select_ind_voir_categorie']="";}
// 20170315
if(!isset($_SESSION['select_ind_voir_theme'])) { $_SESSION['select_ind_voir_theme']="";}
if(!isset($_SESSION['b_ind_voir_cdd'])){ $_SESSION['b_ind_voir_cdd']=false;}
if(!isset($_SESSION['b_ind_voir_hdr'])){ $_SESSION['b_ind_voir_hdr']=false;}
if(!isset($_SESSION['b_ind_voir_associe'])){ $_SESSION['b_ind_voir_associe']=false;}
if(!isset($_SESSION['b_ind_voir_cotutelle'])){ $_SESSION['b_ind_voir_cotutelle']=false;}
if(!isset($_SESSION['b_ind_voir_doctorant_sans_inscription'])){ $_SESSION['b_ind_voir_doctorant_sans_inscription']=false;}
if(!isset($_SESSION['b_ind_voir_doctorant_abandon'])){ $_SESSION['b_ind_voir_doctorant_abandon']=false;}
if(!isset($_SESSION['b_ind_voir_hors_effectif'])){ $_SESSION['b_ind_voir_hors_effectif']=false;}
if(!isset($_SESSION['b_ind_voir_demander_autorisation'])){ $_SESSION['b_ind_voir_demander_autorisation']=false;}
if(!isset($_SESSION['b_ind_voir_pas_de_declaration_fsd'])){ $_SESSION['b_ind_voir_pas_de_declaration_fsd']=false;}
if(!isset($_SESSION['b_ind_voir_cahier_visite'])){ $_SESSION['b_ind_voir_cahier_visite']=false;}
//if(!isset($_SESSION['b_ind_voir_anomalies'])){ $_SESSION['b_ind_voir_anomalies']=false;}
// colonnes a afficher ou non
if(!isset($_SESSION['b_ind_voir_col_invalider'])){ $_SESSION['b_ind_voir_col_invalider']=false;}
//if(!isset($_SESSION['b_ind_voir_col_notes'])){ $_SESSION['b_ind_voir_col_notes']=false;}// 20161213
if(!isset($_SESSION['b_ind_voir_col_anomalies'])){ $_SESSION['b_ind_voir_col_anomalies']=false;}

//$_SESSION['b_ind_etre_admin']=isset($_POST['b_ind_etre_admin_x'])? !$_SESSION['b_ind_etre_admin']:$_SESSION['b_ind_etre_admin'];
$_SESSION['b_ind_voir_presents']=isset($_POST['b_ind_voir_presents_x'])? !$_SESSION['b_ind_voir_presents']:$_SESSION['b_ind_voir_presents'];
$_SESSION['b_ind_voir_partis']=isset($_POST['b_ind_voir_partis_x'])? !$_SESSION['b_ind_voir_partis']:$_SESSION['b_ind_voir_partis'];
//$_SESSION['b_voir_tous_themes']=isset($_POST['b_voir_tous_themes_x'])? !$_SESSION['b_voir_tous_themes']:$_SESSION['b_voir_tous_themes'];
$_SESSION['b_ind_voir_mes_dossiers']=isset($_POST['b_ind_voir_mes_dossiers_x'])? !$_SESSION['b_ind_voir_mes_dossiers']:$_SESSION['b_ind_voir_mes_dossiers'];
$_SESSION['b_lister_sejours']=isset($_POST['b_lister_sejours_x'])? !$_SESSION['b_lister_sejours']:$_SESSION['b_lister_sejours'];
// filtre par categorie
$_SESSION['select_ind_voir_categorie']=isset($_POST['select_ind_voir_categorie'])? $_POST['select_ind_voir_categorie']:$_SESSION['select_ind_voir_categorie'];
// 20170315
$_SESSION['select_ind_voir_theme']=isset($_POST['select_ind_voir_theme'])? $_POST['select_ind_voir_theme']:$_SESSION['select_ind_voir_theme'];
$_SESSION['b_ind_voir_cdd']=isset($_POST['b_ind_voir_cdd_x'])? !$_SESSION['b_ind_voir_cdd']:$_SESSION['b_ind_voir_cdd'];
$_SESSION['b_ind_voir_associe']=isset($_POST['b_ind_voir_associe_x'])? !$_SESSION['b_ind_voir_associe']:$_SESSION['b_ind_voir_associe'];
$_SESSION['b_ind_voir_hdr']=isset($_POST['b_ind_voir_hdr_x'])? !$_SESSION['b_ind_voir_hdr']:$_SESSION['b_ind_voir_hdr'];
$_SESSION['b_ind_voir_cotutelle']=isset($_POST['b_ind_voir_cotutelle_x'])? !$_SESSION['b_ind_voir_cotutelle']:$_SESSION['b_ind_voir_cotutelle'];
$_SESSION['b_ind_voir_doctorant_sans_inscription']=isset($_POST['b_ind_voir_doctorant_sans_inscription_x'])? !$_SESSION['b_ind_voir_doctorant_sans_inscription']:$_SESSION['b_ind_voir_doctorant_sans_inscription'];
$_SESSION['b_ind_voir_doctorant_abandon']=isset($_POST['b_ind_voir_doctorant_abandon_x'])? !$_SESSION['b_ind_voir_doctorant_abandon']:$_SESSION['b_ind_voir_doctorant_abandon'];
if($_SESSION['b_ind_voir_doctorant_abandon'])
{ $_SESSION['b_ind_voir_presents']=false;
	$_SESSION['b_ind_voir_partis']=true;
}
$_SESSION['b_ind_voir_hors_effectif']=isset($_POST['b_ind_voir_hors_effectif_x'])? !$_SESSION['b_ind_voir_hors_effectif']:$_SESSION['b_ind_voir_hors_effectif'];
if(isset($_POST['b_ind_voir_demander_autorisation_x']))
{ $_SESSION['b_ind_voir_demander_autorisation']=!$_SESSION['b_ind_voir_demander_autorisation'];
	if($_SESSION['b_ind_voir_demander_autorisation'])
	{ $_SESSION['b_ind_voir_pas_de_declaration_fsd']=false;
		$_SESSION['b_ind_voir_cahier_visite']=false;
	}
}
else if(isset($_POST['b_ind_voir_pas_de_declaration_fsd_x']))
{ $_SESSION['b_ind_voir_pas_de_declaration_fsd']=!$_SESSION['b_ind_voir_pas_de_declaration_fsd'];
	if($_SESSION['b_ind_voir_pas_de_declaration_fsd'])
	{ $_SESSION['b_ind_voir_demander_autorisation']=false;
		$_SESSION['b_ind_voir_cahier_visite']=false;
	}
}
else if(isset($_POST['b_ind_voir_cahier_visite_x']))
{ $_SESSION['b_ind_voir_cahier_visite']=!$_SESSION['b_ind_voir_cahier_visite'];
	if($_SESSION['b_ind_voir_cahier_visite'])
	{ $_SESSION['b_ind_voir_demander_autorisation']=false;
		$_SESSION['b_ind_voir_pas_de_declaration_fsd']=false;
	}
}

// colonnes a afficher ou non
$_SESSION['b_ind_voir_col_invalider']=isset($_POST['b_ind_voir_col_invalider_x'])? !$_SESSION['b_ind_voir_col_invalider']:$_SESSION['b_ind_voir_col_invalider'];
//$_SESSION['b_ind_voir_col_notes']=isset($_POST['b_ind_voir_col_notes_x'])? !$_SESSION['b_ind_voir_col_notes']:$_SESSION['b_ind_voir_col_notes'];
$_SESSION['b_ind_voir_col_anomalies']=isset($_POST['b_ind_voir_col_anomalies_x'])? !$_SESSION['b_ind_voir_col_anomalies']:$_SESSION['b_ind_voir_col_anomalies'];
/* if($_SESSION['b_ind_voir_anomalies'])
{ $_SESSION['b_ind_voir_col_anomalies']=true;
} */
if(isset($_POST['b_corbeille_x']) || isset($_POST['b_ind_voir_demander_autorisation_x']) || isset($_POST['b_ind_voir_pas_de_declaration_fsd_x'])  || isset($_POST['b_ind_voir_cahier_visitex']))
{ $_SESSION['ind_champrecherche']='nomprenomindividu';
 	$_SESSION['ind_texterecherche']='';
}
if($_SESSION['ind_texterecherche']!='' || $_SESSION['b_ind_voir_mes_dossiers'])
{ $_SESSION['b_lister_sejours']=false;
	$_SESSION['b_ind_voir_presents']=true;
	$_SESSION['b_ind_voir_partis']=true;
	//$_SESSION['b_voir_tous_themes']=true;
	$_SESSION['select_ind_voir_categorie']="";
	// 20170315
	$_SESSION['select_ind_voir_theme']="";
	$_SESSION['b_ind_voir_cdd']=false;
	$_SESSION['b_ind_voir_associe']=false;
	$_SESSION['b_ind_voir_hdr']=false;
	$_SESSION['b_ind_voir_cotutelle']=false;
	$_SESSION['b_ind_voir_doctorant_sans_inscription']=false;
	$_SESSION['b_ind_voir_doctorant_abandon']=false;
	$_SESSION['b_ind_voir_hors_effectif']=false;
	$_SESSION['b_ind_voir_demander_autorisation']=false;
	$_SESSION['b_ind_voir_pas_de_declaration_fsd']=false;
	$_SESSION['b_ind_voir_cahier_visite']=false;
	if($_SESSION['ind_texterecherche']!='')
	{ $_SESSION['b_ind_voir_mes_dossiers']=false;
	}
}
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $affiche_succes=true;
	// informations a envoyer dans le mail s'il y en a un
	$query_rs_ind="select civilite.libcourt_fr as libciv,individu.*,individusejour.*,".
									" corps.liblongcorps_fr as libcorps, cat.codelibcat,codelibtypestage,sujetstageobligatoire,lieu.libcourtlieu as liblieu,sujet.codesujet,sujet.codestatutsujet,sujet.titre_fr as titresujet".
									" from civilite,lieu,individu,corps,cat,typestage,individusejour".
									" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
									" left join sujet on individusujet.codesujet=sujet.codesujet". 
									" where individu.codeciv=civilite.codeciv".
									" and individu.codeindividu=individusejour.codeindividu".
									" and individu.codelieu=lieu.codelieu". 
									" and individusejour.codecorps=corps.codecorps". 
									" and corps.codecat=cat.codecat". 
									" and individusejour.codetypestage=typestage.codetypestage".
									" and individusejour.codeindividu=".GetSQLValueString($codeindividu, "text").
									" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
	$rs_ind=mysql_query($query_rs_ind) or die(mysql_error());
	$row_rs_ind=mysql_fetch_assoc($rs_ind);
	$rs=mysql_query("SELECT libcourt_fr from individusejour,individutheme,structure".
									" WHERE individusejour.codeindividu=individutheme.codeindividu AND individusejour.numsejour=individutheme.numsejour".
									" AND individutheme.codetheme=structure.codestructure".
									" AND individutheme.codeindividu=".GetSQLValueString($codeindividu, "text").
									" AND individutheme.numsejour=".GetSQLValueString($numsejour, "text"));
	$row_rs_ind['theme']="";
	$first=true;
	while($row_rs = mysql_fetch_assoc($rs))
	{ $row_rs_ind['theme'].=($first?"":",").$row_rs['libcourt_fr'];
		$first=false;
	}
	$row_rs_ind['demander_autorisation']=(isset($_POST['demander_autorisation']) && $_POST['demander_autorisation']=='oui');
	if($action=="valider")
	{ // REFERENT : un mail est toujours emis sur visa referent (a l'emetteur, gesttheme, referent, srh)
		// si pas de dde d'autorisation necessaire (autorisé sans visa ou -5j). Dans ce cas si present ou parti,
		// insertion simultanee des deux visas referent et theme (srhue n'est pas appose).
		// SRHUE (fsd) : la colonne FSD comporte un visa a valider si autorisation demandee et pas encore de retour. Mail a emetteur, gesttheme, resptheme, referent, srh
		// le calcul de demander_autorisation a ete realise pour la page d'affichage des lignes envoyees : on le recupere dans le POST : moins clair
		// en programmation mais evite de refaire le calcul a nouveau pour savoir s'il faut demander une autorisation 
		// THEME : Pas de mail sur visa theme si en accueil et visa srhue appose ou pas demander_autorisation (evite d'envoyer des mails 'inutiles'. Choix discutable)
		// 20170515  pour apposition automatique du visa srhue si pas estzrr
		$rs=mysql_query("select codestatutvisa from statutvisa where coderole=".GetSQLValueString('srhue', "text"));
		$row_rs=mysql_fetch_assoc($rs);
		$codestatutvisa_srhue=$row_rs['codestatutvisa'];
		// 20170515 fin
		
		$rs=mysql_query("select coderole from statutvisa where codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text"));
		if(mysql_num_rows($rs)==1)
		{ $row_rs=mysql_fetch_assoc($rs);
			$codevisa_a_apposer_lib=$row_rs['coderole'];
			if(!$row_rs_ind['demander_autorisation'])
			{	$rs=mysql_query("select codestatutvisa,coderole from statutvisa".
												" where coderole=".GetSQLValueString('referent', "text")." or coderole=".GetSQLValueString('theme', "text")." order by codestatutvisa");
				while($row_rs=mysql_fetch_assoc($rs))
				{ if($row_rs['coderole']=='referent' || $codevisa_a_apposer_lib=='theme' || $row_rs_ind['datedeb_sejour']<=$aujourdhui /* arrive ou parti =appose visa theme sans mail */)
					{ $updateSQL ="delete from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text").
												" and codestatutvisa=".GetSQLValueString($row_rs['codestatutvisa'], "text").
												" and numsejour=".GetSQLValueString($numsejour, "text"); 
						mysql_query($updateSQL) or die(mysql_error());
						$updateSQL = "INSERT into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa) values (".
													GetSQLValueString($codeindividu, "text").",".
													GetSQLValueString($numsejour, "text").",".
													GetSQLValueString($row_rs['codestatutvisa'], "text").",".
													GetSQLValueString($codeuser, "text").",".
													GetSQLValueString($aujourdhui, "text").						
													")";
						mysql_query($updateSQL) or die(mysql_error());
					}
				}
				// 20170515  pour apposition automatique du visa srhue si pas zrr
				if(!$GLOBALS['estzrr'] && $codevisa_a_apposer_lib=='referent')		
				{ $updateSQL ="delete from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text").
								" and codestatutvisa=".GetSQLValueString($codestatutvisa_srhue, "text").
								" and numsejour=".GetSQLValueString($numsejour, "text"); 
					mysql_query($updateSQL) or die(mysql_error());
					$updateSQL = "INSERT into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa) values (".
												GetSQLValueString($codeindividu, "text").",".
												GetSQLValueString($numsejour, "text").",".
												GetSQLValueString($codestatutvisa_srhue, "text").",".
												GetSQLValueString($codeuser, "text").",".
												GetSQLValueString($aujourdhui, "text").						
												")";
					mysql_query($updateSQL) or die(mysql_error());
				}
				// 20170515 fin

				$message_resultat_affiche="Validation effectu&eacute;e sans envoi de mail";
			}
			else
			{	$erreur_envoimail="";
				// le mail comportera un texte_attente_sujet si manque sujet (referent) ou sujet pas valide (resp theme)
				$tab_sujet_saisi_valide=sujet_saisi_valide($row_rs_ind,$codevisa_a_apposer_lib);//le sujet est considere comme saisi pour visa srhue
				$row_rs_ind['texte_attente_sujet']=$tab_sujet_saisi_valide['texte_attente_sujet'];
				$erreur_envoimail=mail_validation_individu($row_rs_ind,$codeuser,$codevisa_a_apposer);
				if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
				{ $msg_erreur_objet_mail="Visa '".$codevisa_a_apposer_lib."' pour le dossier ".$row_rs_ind['codeindividu'].".".$row_rs_ind['numsejour']." (".$row_rs_ind['nom']." ".$row_rs_ind['prenom'].")";
					$erreur="Aucune action effectu&eacute";
					$warning="Erreur envoi de mail : ".$msg_erreur_objet_mail;
				}
				else if($erreur=='')
				{ // suppression de la ligne avec $codeuser pour cet individu $codeindividu pour le role si elle existe
					$updateSQL ="delete from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text").
											" and codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text").
											" and numsejour=".GetSQLValueString($numsejour, "text"); 
					mysql_query($updateSQL) or die(mysql_error());
				
					$updateSQL = "INSERT into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa) values (".
												GetSQLValueString($codeindividu, "text").",".
												GetSQLValueString($numsejour, "text").",".
												GetSQLValueString($codevisa_a_apposer, "text").",".
												GetSQLValueString($codeuser, "text").",".
												GetSQLValueString($aujourdhui, "text").						
												")";
					mysql_query($updateSQL) or die(mysql_error());
					// 20170515  pour apposition automatique du visa srhue si pas zrr
					if(!$GLOBALS['estzrr'] && $codevisa_a_apposer_lib=='referent')		
					{ $updateSQL ="delete from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text").
									" and codestatutvisa=".GetSQLValueString($codestatutvisa_srhue, "text").
									" and numsejour=".GetSQLValueString($numsejour, "text"); 
						mysql_query($updateSQL) or die(mysql_error());
						$updateSQL = "INSERT into individustatutvisa (codeindividu,numsejour,codestatutvisa,codeacteur,datevisa) values (".
													GetSQLValueString($codeindividu, "text").",".
													GetSQLValueString($numsejour, "text").",".
													GetSQLValueString($codestatutvisa_srhue, "text").",".
													GetSQLValueString($codeuser, "text").",".
													GetSQLValueString($aujourdhui, "text").						
													")";
						mysql_query($updateSQL) or die(mysql_error());
					}
					// 20170515 fin
					$message_resultat_affiche="Validation effectu&eacute;e avec envoi de mail";
				}
			}
		}
	}
	else if(isset($_POST['b_traite_srh_oui_x']) || isset($_POST['b_traite_srh_non_x']))
	{ $traite_srh=isset($_POST['b_traite_srh_non_x'])?"oui":"non";
		mysql_query("update individusejour set traite_srh=".GetSQLValueString($traite_srh, "text").
								" where codeindividu=".GetSQLValueString($codeindividu, "text").
								" AND numsejour=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
	}
	else if($action=="supprimer")//individu si un seul sejour sinon uniquement le sejour
	{ $erreur="";
		if($erreur=="")
		{ $affiche_succes=true;
			// le message doit etre envoye avant la suppression dans les tables sinon mail_action_individu ne trouvera plus les donnees
			// pour construire le mail a envoyer
			$erreur_envoimail='';
			if($envoyer_mail)
			{ $erreur_envoimail=mail_action_individu($row_rs_ind,$codeuser,'supprimer');
			}
			if($envoyer_mail && $erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
			{ $msg_erreur_objet_mail='Suppression du dossier '.$row_rs_ind['codeindividu'].'.'.$row_rs_ind['numsejour'].' ('.$row_rs_ind['nom'].' '.$row_rs_ind['prenom'].')';
				$warning="Erreur envoi de mail : ".$msg_erreur_objet_mail;
			}
			else
			{	$nom=$row_rs_ind['nom'];
				$prenom=$row_rs_ind['prenom'];
				// suppression des pieces jointes et du rep les contenant eventuels
				$rs_individupj=mysql_query("select codetypepj from individupj".
																		" where codeindividu=".GetSQLValueString($codeindividu, "text").
																		" and codelibcatpj=".GetSQLValueString('sejour', "text").
																		" and numcatpj=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
				if(mysql_num_rows($rs_individupj)>=1)
				{ while($row_rs_individupj=mysql_fetch_assoc($rs_individupj))
					{	unlink($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/sejour/'.$numsejour.'/'.$row_rs_individupj['codetypepj']);
					}
					//suppression du rep de ce sejour
					suppr_rep($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/sejour/'.$numsejour);
					
					// suppression des enreg. des pj pour ce sejour
					mysql_query("delete from individupj".
											" where codeindividu=".GetSQLValueString($codeindividu, "text").
											" and codelibcatpj=".GetSQLValueString('sejour', "text").
											" and numcatpj=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
				}
				// un seul sejour ou plusieurs ?
				$rs=mysql_query("select count(*) as nbsejour from individusejour where codeindividu=".GetSQLValueString($codeindividu, "text"));
				$row_rs=mysql_fetch_assoc($rs);
				$un_seul_sejour=((int)$row_rs['nbsejour']==1);
				if($un_seul_sejour)// un seul sejour => suppression de l'individu
				{ //suppression du rep 'sejour' de cet individu
					suppr_rep($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/sejour/');
					//suppression des emplois
					// suppression des enreg. des pj pour les emplois, des reps par emploi et du rep emploi
					$rs_individuemploi=mysql_query( "select numemploi from individuemploi ".
																					"where codeindividu=".GetSQLValueString($codeindividu, "text"));
					while($row_rs_individuemploi=mysql_fetch_assoc($rs_individuemploi))
					{ $rs=mysql_query("select numcatpj,codetypepj from individupj".
														" where codeindividu=".GetSQLValueString($codeindividu, "text").
														" and codelibcatpj=".GetSQLValueString('emploi', "text").
														" and numcatpj=".GetSQLValueString($row_rs_individuemploi['numemploi'], "text")) or die(mysql_error());
						while($row_rs=mysql_fetch_assoc($rs))
						{ unlink($GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu.'/emploi/'.$row_rs['numcatpj'].'/'.$row_rs['codetypepj']);	
						}
						suppr_rep($GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu.'/emploi/'.$row_rs_individuemploi['numemploi']);
					}
					suppr_rep($GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu.'/emploi/');
					// suppr de tous les enreg individuemploi de l'individu
					mysql_query("delete from individuemploi where codeindividu=".GetSQLValueString($codeindividu, "text"));
					// suppression des enreg. des pj
					mysql_query("delete from individupj".
											" where codeindividu=".GetSQLValueString($codeindividu, "text"));
					// suppression du rep eventuel de l'individu
					suppr_rep($GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu);
					mysql_query("delete from individu where codeindividu=".GetSQLValueString($codeindividu, "text"));
					mysql_query("delete from individued where codeindividu=".GetSQLValueString($codeindividu, "text"));

					$message_resultat_affiche='Suppression du dossier '.$codeindividu.' de '.$prenom.' '.$nom.' effectu&eacute;e avec succ&egrave;s';
				}
				else
				{ $query_rs_individusejour ="SELECT individusejour.* FROM individusejour".
																		" WHERE codeindividu=".GetSQLValueString($codeindividu, "text").
																		" and numsejour=".GetSQLValueString($numsejour, "text");
					$rs_individusejour = mysql_query($query_rs_individusejour);
					$row_rs_individusejour=mysql_fetch_assoc($rs_individusejour);
					$datedeb_sejour=$row_rs_individusejour['datedeb_sejour'];
					$datefin_sejour=$row_rs_individusejour['datefin_sejour'];
					// emplois liés a ce séjour
					$query_rs_individuemploi= "SELECT individuemploi.* FROM individuemploi".
																		" WHERE codeindividu=".GetSQLValueString($codeindividu, "text").
																		" AND ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$datedeb_sejour."'","'".$datefin_sejour."'");
					$rs_individuemploi = mysql_query($query_rs_individuemploi) or die(mysql_error());
					// on supprime l'emploi s'il n'est pas lié a un autre sejour
					while($row_rs_individuemploi = mysql_fetch_assoc($rs_individuemploi))
					{ $query_rs_individusejour ="SELECT codeindividu,numsejour FROM individusejour".
																			" WHERE codeindividu=".GetSQLValueString($codeindividu, "text").
																			" and numsejour<>".GetSQLValueString($numsejour, "text").
																			" AND ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".
																							$row_rs_individuemploi['datedeb_emploi']."'","'".$row_rs_individuemploi['datefin_emploi']."'");
						$rs_individusejour = mysql_query($query_rs_individusejour);
						// si aucun autre sejour n'est lié a cet emploi
						if(mysql_num_rows($rs_individusejour)==0)
						{ $rs=mysql_query("select numcatpj,codetypepj from individupj".
																" where codeindividu=".GetSQLValueString($codeindividu, "text").
																" and codelibcatpj=".GetSQLValueString('emploi', "text").
																" and numcatpj=".GetSQLValueString($row_rs_individuemploi['numemploi'], "text")) or die(mysql_error());
							while($row_rs=mysql_fetch_assoc($rs))
							{ unlink($GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu.'/emploi/'.$row_rs['numcatpj'].'/'.$row_rs['codetypepj']);	
							}
							// suppression des enreg. des pj pour cet emploi
							mysql_query("delete from individupj".
													" where codeindividu=".GetSQLValueString($codeindividu, "text").
													" and codelibcatpj=".GetSQLValueString('emploi', "text").
													" and numcatpj=".GetSQLValueString($row_rs_individuemploi['numemploi'], "text")) or die(mysql_error());
							
							suppr_rep($GLOBALS['path_to_rep_upload'].'/individu/'.$codeindividu.'/emploi/'.$row_rs_individuemploi['numemploi']);
							mysql_query("delete from individuemploi where codeindividu=".GetSQLValueString($codeindividu, "text").
													" and numemploi=".GetSQLValueString($row_rs_individuemploi['numemploi'], "text"));
						}
					}
					$message_resultat_affiche='Suppression du s&eacute;jour de '.$prenom.' '.$nom.' effectu&eacute;e avec succ&egrave;s';
				}
				// desaffectation de sujet pour ce sejour
				$rs=mysql_query("select codesujet from individusujet".
												" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text")) or die(mysql_error());
				if($row_rs=mysql_fetch_assoc($rs))
				{	mysql_query("update sujet set codestatutsujet='V' where codesujet=".GetSQLValueString($row_rs['codesujet'], "text")) or die(mysql_error());
				}
				mysql_query("delete from individusejour where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
				mysql_query("delete from individutheme   where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
				mysql_query("delete from individusujet   where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
				mysql_query("delete from individupostit  where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
				mysql_query("delete from individustatutvisa where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
				mysql_query("delete from individuthese where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
				mysql_query("delete from individuaction where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text"));
			}
		}
	}
	$_SESSION['codeindividu']=$codeindividu;
}
else
{ $codeindividu=$_SESSION['codeindividu'];
}
// ----------------------- Formulaire de la liste des sejours d'un individu ou de la liste des individus a afficher
// Incoherences affichees sous forme d'un panneau attention 
if( !$_SESSION['b_lister_sejours'])
{ $tab_incoherences=array();
	// cas d'un nouveau sejour pour un permanent : une datefin_sejour a pu etre fixee par une gestionnaire 
	// mais la datefin_sejour_prevue='' car la gestionnaire ne peut pas la modifier : seuls les rôles autorisés fsd le peuvent
	$query_rs =	" SELECT individu.codeindividu,numsejour,nom, prenom, datefin_sejour from individu, individusejour".
							" where individu.codeindividu=individusejour.codeindividu and datefin_sejour_prevu='' and datefin_sejour<>''".
							" order by nom, prenom, codeindividu, numsejour";
	$rs = mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_incoherences['fsd']['datefin_sejour_incoherentes'][$row_rs['codeindividu'].'.'.$row_rs['numsejour']]=$row_rs;
	}
}

// tous les visas de tous les individus,sejours
$query_rs="select codeindividu,numsejour,coderole".
					" from individustatutvisa,statutvisa".
					" where  individustatutvisa.codestatutvisa=statutvisa.codestatutvisa order by codeindividu,numsejour";
$rs= mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_tout_individustatutvisa[$row_rs['codeindividu']][$row_rs['numsejour']][$row_rs['coderole']]=$row_rs['coderole'];
}

// 20170315 tous individus,theme(s)
$query_rs="SELECT individusejour.codeindividu,individusejour.numsejour, datedeb_sejour, datefin_sejour, individutheme.codetheme,if(individutheme.codetheme='00','SC-Adm',structure.libcourt_fr) as libtheme, structure.date_fin as date_fin_structure".
					" FROM individusejour,individutheme,structure".
					" WHERE individusejour.codeindividu=individutheme.codeindividu and individusejour.numsejour=individutheme.numsejour".
					" and individutheme.codetheme=structure.codestructure AND structure.esttheme='oui' order by individusejour.codeindividu,individusejour.numsejour,structure.date_fin";
$rs=mysql_query($query_rs) or die(mysql_error());
$listetheme="";//pas de theme par defaut
$tab_individutheme=array();
while($row_rs = mysql_fetch_assoc($rs))
{ if(isset($tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]]))
	{ if($row_rs["date_fin_structure"]=="" || $row_rs["date_fin_structure"]==$tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]]["date_fin_structure"])
		{ $tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]]["theme"][$row_rs["codetheme"]]=$row_rs['libtheme'];
			$tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]]["date_fin_structure"]=$row_rs["date_fin_structure"];
		}
	}
	else
	{ $tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]]["theme"][$row_rs["codetheme"]]=$row_rs['libtheme'];
		$tab_individutheme[$row_rs["codeindividu"]][$row_rs["numsejour"]]["date_fin_structure"]=$row_rs["date_fin_structure"];
	}
}

// liste ordonnee des sejours dans le temps (selon datedeb_sejour_prevu) des individus : pour vérification de la contiguite des sejours
$query_rs="select codeindividu, numsejour, datedeb_sejour, datedeb_sejour_prevu, datefin_sejour, datefin_sejour_prevu, date_autorisation from individusejour order by codeindividu,datedeb_sejour_prevu";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_dates_sejour[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
}

if($_SESSION['b_lister_sejours'])
{ $query_rs_ind =	" SELECT civilite.libcourt_fr as libciv_fr,individu.codeindividu,nom, prenom,adresse_pers, codepostal_pers, ville_pers, codepays_pers,codelieu,autrelieu,email, codenat,".
									" individusejour.*, cat.codecat, cat.codelibcat, cat.libcat_fr as libcat_fr, corps.codestatutpers".//,'du' as coderole".
									" FROM civilite, individu".
									" LEFT JOIN individusejour ON individu.codeindividu=individusejour.codeindividu ".
									" LEFT JOIN corps ON individusejour.codecorps=corps.codecorps ".
									" LEFT JOIN cat ON corps.codecat=cat.codecat".
									" WHERE individu.codeindividu=".GetSQLValueString($codeindividu, "text").
									" and individu.codeciv=civilite.codeciv".
									" ORDER BY datedeb_sejour desc";

	$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
	while($row_rs_ind=mysql_fetch_assoc($rs_ind))
	{ $datedeb_sejour=$row_rs_ind['datedeb_sejour'];
		$datefin_sejour=$row_rs_ind['datefin_sejour'];
		$numsejour=$row_rs_ind['numsejour'];
		if($datedeb_sejour>$aujourdhui)
	  { $etat_individu_val='preaccueil';
		}
		else
	  { $demander_autorisation=false;
			if($row_rs_ind['date_autorisation']=='' &&  !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']))
			{ $tab_demander_autorisation=demander_autorisation($row_rs_ind,$tab_dates_sejour[$codeindividu]);
				$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
			}
			if((!isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']) && $demander_autorisation) || !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['theme']))
			{ if($datefin_sejour>=$aujourdhui || $datefin_sejour=='')
				{ $etat_individu_val='accueil';
				}
				else
				{ $etat_individu_val='sejourpartinonvalide';
				}
			}
			else
			{	if($datefin_sejour>=$aujourdhui || $datefin_sejour=='')
				{ $etat_individu_val='present';
				}
				else
				{ $etat_individu_val='parti';
				}
			}
		}
		$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]=$row_rs_ind;
	}
}
else
{	$clause_where="";
	$clause_from="";
	$clause_group_by ="";
	if($_SESSION['ind_texterecherche']!='')
	{ if($_SESSION['ind_champrecherche']=='nomprenomindividu')
		{ $clause_where=" and concat(individu.nom,individu.prenom) like '%".mysql_real_escape_string($_SESSION['ind_texterecherche'])."%'";
		}
		else
		{ $clause_where=" and individu.codeindividu like '%".mysql_real_escape_string($_SESSION['ind_texterecherche'])."%'";
		}
	}
	/* else if(!$_SESSION['b_voir_tous_themes'])
	{ $clause_from=", individutheme";
		$clause_where=" and individusejour.codeindividu=individutheme.codeindividu".
									" and individutheme.codetheme in ( select codetheme from individutheme where codeindividu=".GetSQLValueString($codeuser, "text").")";
		$clause_group_by =" individu.codeindividu";
	} */
	else if($_SESSION['b_ind_voir_mes_dossiers'])
	{ if($estgesttheme || $est_admin || array_key_exists('srh',$tab_roleuser))
		{ $clause_where=" and individusejour.codegesttheme=".GetSQLValueString($codeuser, "text");
		}
		else
		{	$clause_where=" and individusejour.codereferent=".GetSQLValueString($codeuser, "text");
		}
	}
	// preacceuil
	$query_rs_ind =	"SELECT distinct civilite.libcourt_fr as libciv_fr,individu.codeindividu,nom, prenom,adresse_pers, codepostal_pers, ville_pers, codepays_pers, hdr,codelieu,autrelieu,email,codenat,".
								" individusejour.*,cat.codecat, cat.codelibcat, cat.libcat_fr as libcat_fr, corps.codestatutpers".
								" FROM individu, individusejour, cat,corps, civilite".
								$clause_from.
								" WHERE individu.codeindividu=individusejour.codeindividu ". 
								" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
								" and individu.codeciv=civilite.codeciv and individu.codeindividu<>''".
								" and datedeb_sejour>".GetSQLValueString($aujourdhui, "text").
								$clause_where.
								($clause_group_by==""?"":" GROUP BY ".$clause_group_by).
								" ORDER BY individu.nom,individu.prenom,individusejour.datedeb_sejour asc";// asc = du plus ancien au plus récent
	$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
	while($row_rs_ind=mysql_fetch_assoc($rs_ind))
	{ $tab_rs_individu['preaccueil'][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind;
	}
	// accueil (present mais pas tous les visas) et presents
	$query_rs_ind =	"SELECT distinct civilite.libcourt_fr as libciv_fr,individu.codeindividu,nom, prenom,adresse_pers, codepostal_pers, ville_pers, codepays_pers, hdr,codelieu,autrelieu,email,codenat,".
									" individusejour.*,cat.codecat, cat.codelibcat, cat.libcat_fr as libcat_fr, corps.codestatutpers".
									" FROM individu, individusejour, cat, corps, civilite ".
									$clause_from.
									" WHERE individu.codeindividu=individusejour.codeindividu ". 
									" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
									" and individu.codeciv=civilite.codeciv and individu.codeindividu<>''".
									" and ".periodeencours('datedeb_sejour','datefin_sejour').
									$clause_where.
									($clause_group_by==""?"":" GROUP BY ".$clause_group_by).
									" ORDER BY individu.nom,individu.prenom,individusejour.datedeb_sejour asc";// asc = du plus ancien au plus récent
	//echo '<br>'.$query_rs_ind;
	$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
	while($row_rs_ind=mysql_fetch_assoc($rs_ind))
	{ $codeindividu=$row_rs_ind['codeindividu'];
		$numsejour=$row_rs_ind['numsejour'];
		$demander_autorisation=false;
		// calcul de demander_autorisation si pas de date d'autoristation et visa srhue pas appose
		if($GLOBALS['estzrr'])
		{ if($row_rs_ind['date_autorisation']=='' &&  !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']))
			{ $tab_demander_autorisation=demander_autorisation($row_rs_ind,$tab_dates_sejour[$codeindividu]);
				$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
			}
		}
		// on laisse en bloc accueil si (srhue pas appose et demander autorisation) ou pas de visa theme, sinon en bloc present
		if((!isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']) && $demander_autorisation) || !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['theme']))
		{ $tab_rs_individu['accueil'][$codeindividu][$numsejour]=$row_rs_ind;
		}
		else
		{	if(!isset($tab_rs_individu['accueil'][$row_rs_ind['codeindividu']]))
			{ $tab_rs_individu['present'][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind; // on affiche tous les séjours présents si plusieurs par erreur
			}
		}
	}
	
	// partis et séjours passés non valides (manque un visa) : le plus récent par individu
	$query_rs_ind = "SELECT distinct civilite.libcourt_fr as libciv_fr,individu.*,individusejour.*,cat.codecat, cat.codelibcat, cat.libcat_fr as libcat_fr, corps.codestatutpers".
									" FROM  individu, individusejour,civilite,corps,cat".
									$clause_from.
									" WHERE individu.codeindividu=individusejour.codeindividu ".
									" AND individusejour.codecorps=corps.codecorps AND corps.codecat=cat.codecat".
									" AND individu.codeciv=civilite.codeciv".
									" AND ".periodepassee('datefin_sejour').
									$clause_where.
									($clause_group_by==""?"":" GROUP BY ".$clause_group_by).
									" ORDER BY individu.nom,individu.prenom,individusejour.datedeb_sejour desc";
	$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
	while($row_rs_ind=mysql_fetch_assoc($rs_ind))
	{ $codeindividu=$row_rs_ind['codeindividu'];
		$numsejour=$row_rs_ind['numsejour'];
		$demander_autorisation=false;
		// 20161213
		if($GLOBALS['estzrr'])
		{ $tab_demander_autorisation=demander_autorisation($row_rs_ind,$tab_dates_sejour[$codeindividu]);
			if($row_rs_ind['date_autorisation']=='' &&  !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']))
			{ $demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
			}
		}
		if((!isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']) && $demander_autorisation) || (!isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['theme']))
		 //20170228
		 && isset($tab_demander_autorisation['pourquoi_pas_de_demande_fsd']) && $tab_demander_autorisation['pourquoi_pas_de_demande_fsd']!='- de 5j')
		{ $tab_rs_individu['sejourpartinonvalide'][$codeindividu][$numsejour]=$row_rs_ind;
		}
		else
		{	if(!isset($tab_rs_individu['parti'][$codeindividu]))//on affiche un seul sejour parti
			{ $tab_rs_individu['parti'][$codeindividu][$numsejour]=$row_rs_ind;
			}
		}
	}
	
	// anomalies : tous les individus non répertoriés dans les listes précédentes
	// individus non listés ci-dessus : partis sans visa, anomalies
	$query_rs_ind =	"SELECT distinct individu.*,individusejour.* from individu,individusejour".
									($clause_from==""?"":$clause_from).
									" WHERE individu.codeindividu<>''  and  individu.codeindividu=individusejour.codeindividu ".
									($clause_where==""?"":$clause_where).
									($clause_group_by==""?"":" GROUP BY ".$clause_group_by).
									" ORDER BY individu.nom,individu.prenom";
	//echo $query_rs_ind.'<br>';
	$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
	while($row_rs_ind=mysql_fetch_assoc($rs_ind))
	{ $tab_rs_individu['anomalie'][$row_rs_ind['codeindividu']][$row_rs_ind['numsejour']]=$row_rs_ind;
	}
	if(isset($tab_rs_individu['anomalie']))
	{ foreach(array_diff($tab_etat_individu,array('anomalie')) as $key=>$etat_individu_val)//pour tous les états sauf anomalie
		{ if(isset($tab_rs_individu[$etat_individu_val]))
			{ $tab_rs_individu['anomalie']=array_diff_key ($tab_rs_individu['anomalie'], $tab_rs_individu[$etat_individu_val]);
			}
		}
	}
	
	foreach($tab_rs_individu['anomalie'] as $codeindividu=>$row_rs_ind)
	{ $query_rs_ind = " SELECT civilite.libcourt_fr as libciv_fr,individu.codeindividu,nom, prenom,adresse_pers, codepostal_pers, ville_pers, codepays_pers, hdr,codelieu,autrelieu,email,".
										" individusejour.*,cat.codecat, cat.codelibcat, cat.libcat_fr as libcat_fr, corps.codestatutpers".
										" FROM civilite, individu".
										" LEFT JOIN individusejour ON individu.codeindividu=individusejour.codeindividu ".
										" LEFT JOIN corps ON individusejour.codecorps=corps.codecorps ".
										" LEFT JOIN cat ON corps.codecat=cat.codecat".
										" WHERE individu.codeindividu=".GetSQLValueString($codeindividu, "text").
										" and individu.codeciv=civilite.codeciv".
										" GROUP BY individusejour.codeindividu, individusejour.numsejour".
										" ORDER BY individu.nom,individu.prenom,individusejour.datedeb_sejour asc";
		$rs_ind = mysql_query($query_rs_ind) or die(mysql_error());
		$tab_rs_individu['anomalie'][$codeindividu]['']=mysql_fetch_assoc($rs_ind) or die(mysql_error());
	}
	// les unset sont faits apres le traitement précédent de 'anomalie' sinon $tab_rs_individu['anomalie'] contiendrait présents et partis
	if($_SESSION['ind_texterecherche']=='') 
	{ if( !$_SESSION['b_ind_voir_presents'] && isset($tab_rs_individu['present'])){ unset($tab_rs_individu['present']);}
		if( !$_SESSION['b_ind_voir_partis'] && isset($tab_rs_individu['parti'])){ unset($tab_rs_individu['parti']);}
	}
}
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree apres select des blocs d'individus : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}
$tab_individu_fsd=array();
$query_rs="select individupj.*,typepjindividu.* from individupj,typepjindividu".
					" where individupj.codetypepj=typepjindividu.codetypepj".
					" and individupj.codelibcatpj=typepjindividu.codelibcatpj".
					" and individupj.codelibcatpj=".GetSQLValueString('sejour', "text").
					" and (typepjindividu.codelibtypepj=".GetSQLValueString('cv', "text").
								"	or typepjindividu.codelibtypepj=".GetSQLValueString('piece_identite', "text").
								"	or typepjindividu.codelibtypepj=".GetSQLValueString('fsd_sujet', "text").
								"	or typepjindividu.codelibtypepj=".GetSQLValueString('fsd_financement', "text").
								" or typepjindividu.codelibtypepj=".GetSQLValueString('fsd', "text").")";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_individu_fsd[$row_rs['codeindividu']][$row_rs['numcatpj']][$row_rs['codelibtypepj']]=$row_rs;
}

$tab_individu_pj=array();
$query_rs="select individupj.*,typepjindividu.* from individupj,typepjindividu".
					" where individupj.codetypepj=typepjindividu.codetypepj".
					" and individupj.codelibcatpj=typepjindividu.codelibcatpj".
					" and individupj.codelibcatpj=".GetSQLValueString('sejour', "text").
					" and (typepjindividu.codelibtypepj=".GetSQLValueString('resp_civile', "text").
								" or typepjindividu.codelibtypepj=".GetSQLValueString('conv_accueil', "text").")";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_individu_pj[$row_rs['codeindividu']][$row_rs['numcatpj']][$row_rs['codelibtypepj']]=$row_rs;
}

$tab_indgestionnaire=array();// tous les createurs, modifieurs,referents,...
$query_rs = "select codeindividu,nom, prenom,email from individu ";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_indgestionnaire[$row_rs['codeindividu']]=$row_rs;
}

$tab_postit=array();
$query_rs ="SELECT codeindividu,numsejour,codeacteur,postit FROM individupostit ".
				" WHERE  codeacteur=".GetSQLValueString($codeuser, "text");
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_postit[$row_rs['codeindividu']][$row_rs['numsejour']][$row_rs['codeacteur']]=$row_rs['postit'];
}
$tab_typestage=array();
// 20150620	
$query_rs ="SELECT codetypestage,codelibtypestage,sujetstageobligatoire, libcourttypestage as libtypestage FROM typestage ";
// 20150620	
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_typestage[$row_rs['codetypestage']]=$row_rs;
}
$tab_sujet=array();
$query_rs ="SELECT individusujet.codeindividu,individusujet.numsejour,sujet.codesujet as codesujet, sujet.titre_fr as titresujet_fr, codestatutsujet".
				" FROM individusujet,sujet ".
				" WHERE individusujet.codesujet=sujet.codesujet";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_sujet[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
}

$tab_individusujet=array();
$query_rs ="SELECT individuthese.*,libcourted_fr as libed FROM individuthese,ed where individuthese.codeed_these=ed.codeed";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_individuthese[$row_rs['codeindividu']][$row_rs['numsejour']]=$row_rs;
}

$tab_associe=array();
$query_rs ="SELECT codeindividu FROM individu where associe='oui' ";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_associe[$row_rs['codeindividu']]=$row_rs['codeindividu'];
}

$tab_hors_effectif=array();
$query_rs ="SELECT codeindividu FROM individu where hors_effectif='oui' ";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_hors_effectif[$row_rs['codeindividu']]=$row_rs['codeindividu'];
  $hors_effectif_existe=true;
}

$tab_ed=array();
$query_rs ="SELECT codeindividu,ed.codeed,libcourted_fr as libed FROM individu,ed where individu.codeed=ed.codeed";
$rs = mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_ed[$row_rs['codeindividu']]=$row_rs;
}
// nb de sejours individu
$tab_nbsejour=array();
$rs=mysql_query("select codeindividu,count(*) as nbsejour from individusejour group by codeindividu") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_nbsejour[$row_rs['codeindividu']]['nbsejour']=$row_rs['nbsejour'];
}
// **************** Suppression impossible de sejour selon condition (et seuls les sejours preaccueil et accueil peuvent etre supprimes)
// Les restictions suivantes s'appliquent.
// fonction dans la structure
$tab_sejournonsuppr=array();
//  20170617
$rs=mysql_query("select codeindividu from structureindividu") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Occupe une fonction dans la structure';
	}
}
$rs=mysql_query("select valeur as codeindividu from progacces where critere='codeindividu'") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Occupe une fonction dans la structure';
	}
}

// gestionnaire de theme : non supprimable
$rs=mysql_query("select distinct codegesttheme as codeindividu from gesttheme") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Gestionnaire';
	}
}
// secr site : non supprimable
$rs=mysql_query("select distinct codesecrsite as codeindividu from secrsite where codesecrsite<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- secr&eacute;tariat d&rsquo;appui';
	}
}
// secr site : non supprimable pour mission
$rs=mysql_query("select distinct codesecrsite as codeindividu from mission where codemission<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- secr&eacute;tariat d&rsquo;appui de mission';
	}
}
// secr site : non supprimable pour commande
$rs=mysql_query("select distinct codesecrsite as codeindividu from commande where codecommande<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- secr&eacute;tariat d&rsquo;appui de commande';
	}
}

// createur, modifieur individu non supprimable
$rs=mysql_query("select distinct codecreateur from individusejour") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codecreateur']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codecreateur']]['codeindividu']=$row_rs['codecreateur'];
		$tab_sejournonsuppr[$row_rs['codecreateur']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codecreateur']]['explication'])?$tab_sejournonsuppr[$row_rs['codecreateur']]['explication'].'<br>':'').'- cr&eacute;ateur individu';
	}
}
$rs=mysql_query("select distinct codemodifieur from individusejour") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{	if($tab_nbsejour[$row_rs['codemodifieur']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codemodifieur']]['codeindividu']=$row_rs['codemodifieur'];
		$tab_sejournonsuppr[$row_rs['codemodifieur']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codemodifieur']]['explication'])?$tab_sejournonsuppr[$row_rs['codemodifieur']]['explication'].'<br>':'').'- modifieur individu';
	}
}

// createur, modifieur commande non supprimable
$rs=mysql_query("select distinct codecreateur from commande where codecommande<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codecreateur']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codecreateur']]['codeindividu']=$row_rs['codecreateur'];
		$tab_sejournonsuppr[$row_rs['codecreateur']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codecreateur']]['explication'])?$tab_sejournonsuppr[$row_rs['codecreateur']]['explication'].'<br>':'').'- cr&eacute;ateur commande';
	}
}
$rs=mysql_query("select distinct codemodifieur from commande where codecommande<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{	if($tab_nbsejour[$row_rs['codemodifieur']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codemodifieur']]['codeindividu']=$row_rs['codemodifieur'];
		$tab_sejournonsuppr[$row_rs['codemodifieur']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codemodifieur']]['explication'])?$tab_sejournonsuppr[$row_rs['codemodifieur']]['explication'].'<br>':'').'- modifieur commande';
	}
}

// createur, modifieur mission non supprimable
$rs=mysql_query("select distinct codecreateur from mission where codemission<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codecreateur']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codecreateur']]['codeindividu']=$row_rs['codecreateur'];
		$tab_sejournonsuppr[$row_rs['codecreateur']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codecreateur']]['explication'])?$tab_sejournonsuppr[$row_rs['codecreateur']]['explication'].'<br>':'').'- cr&eacute;ateur mission';
	}
}
$rs=mysql_query("select distinct codemodifieur from mission where codemission<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{	if($tab_nbsejour[$row_rs['codemodifieur']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codemodifieur']]['codeindividu']=$row_rs['codemodifieur'];
		$tab_sejournonsuppr[$row_rs['codemodifieur']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codemodifieur']]['explication'])?$tab_sejournonsuppr[$row_rs['codemodifieur']]['explication'].'<br>':'').'- modifieur mission';
	}
}

// encadrant ou createur de sujet
$rs=mysql_query("select distinct codedir as codeindividu from sujetdir ") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Encadrant';
	}
}
// createur d'au moins un sujet : non supprimable
$rs=mysql_query("select distinct codecreateur as codeindividu from sujet where codesujet<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Cr&eacute;ateur de sujet';
	}
}
// doctorant d'un contrat : non supprimable
$rs=mysql_query("select distinct codedoctorant as codeindividu,codecontrat from contrat where codecontrat<>'' and codedoctorant<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(isset($tab_nbsejour[$row_rs['codeindividu']]) && $tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Li&eacute; au contrat '.$row_rs['codecontrat'];
	}
}
// responsable d'un contrat : non supprimable
$rs=mysql_query("select distinct coderespscientifique as codeindividu from contrat where codecontrat<>''".
								" UNION select distinct coderespaci as codeindividu from budg_aci where codeaci<>'' and coderespaci<>'' ") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Responsable de contrat';
	}
}

// missionnaire : non supprimable. Pas genant si le missionnaire est deselectionne puis reselectionne dans la mission car ce sont les dates de sejour pendant la mission
// qui donnent les informations du missionnaire.
$rs=mysql_query("select distinct codeagent as codeindividu from mission where codeagent<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Agent missionnaire';
	}
}

// demandeur de commande : non supprimable sauf si plus d'un sejour.
$rs=mysql_query("select distinct codereferent as codeindividu from commande where codereferent<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Demandeur de commande';
	}
}
// 20170412
// beneficiaire d'equipement inventorie: non supprimable sauf si plus d'un sejour.
$rs=mysql_query("select distinct codedestinataire as codeindividu from commandeinventaire where codedestinataire<>''") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($tab_nbsejour[$row_rs['codeindividu']]['nbsejour']<=1)
	{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
		$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- B&eacute;n&eacute;ficiare d&rsquo;&eacute;quipement inventori&eacute;';
	}
}
// 20170412
// sujet verrouillé pour un doctorant
$rs=mysql_query("select distinct codeindividu,numsejour from individuthese where sujet_verrouille='oui'") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_sejournonsuppr[$row_rs['codeindividu']]['codeindividu']=$row_rs['codeindividu'];
	$tab_sejournonsuppr[$row_rs['codeindividu']]['explication']=(isset($tab_sejournonsuppr[$row_rs['codeindividu']]['explication'])?$tab_sejournonsuppr[$row_rs['codeindividu']]['explication'].'<br>':'').'- Sujet de th&egrave;se verrouill&eacute;';
	$tab_sejournonsuppr[$row_rs['codeindividu']]['numsejour_sujet_verrouille']=$row_rs['numsejour'];
}

// role referent
$tab_roleuser_individusejour=array();
$query_rs="select codeindividu,numsejour,codereferent".
								" from individusejour ".
								" where codereferent=".GetSQLValueString($codeuser, "text").
								" or codegesttheme=".GetSQLValueString($codeuser, "text").
								" or codecreateur=".GetSQLValueString($codeuser, "text").
								" or codemodifieur=".GetSQLValueString($codeuser, "text");
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_roleuser_individusejour[$row_rs['codeindividu']][$row_rs['numsejour']]['referent']=$tab_statutvisa['referent'];
	$tab_roleuser_individusejour[$row_rs['codeindividu']][$row_rs['numsejour']]['estreferent']=($codeuser==$row_rs['codereferent']);
}

// role gesttheme
$query_rs="select individutheme.codeindividu,individutheme.numsejour from individutheme,gesttheme,structure".
					" where individutheme.codetheme=gesttheme.codetheme ".
					" and gesttheme.codetheme=structure.codestructure".
					" and gesttheme.codegesttheme = ".GetSQLValueString($codeuser, "text");
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_roleuser_individusejour[$row_rs['codeindividu']][$row_rs['numsejour']]['theme']=$tab_statutvisa['theme'];
	$tab_roleuser_individusejour[$row_rs['codeindividu']][$row_rs['numsejour']]['estresptheme']=false;
}

// role theme
$query_rs="select individutheme.codeindividu,individutheme.numsejour from individutheme,structureindividu".
					" where individutheme.codetheme=structureindividu.codestructure ".
					" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text");
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_roleuser_individusejour[$row_rs['codeindividu']][$row_rs['numsejour']]['tab_roleuser']['theme']=$tab_statutvisa['theme'];
	$tab_roleuser_individusejour[$row_rs['codeindividu']][$row_rs['numsejour']]['estresptheme']=true;
}
$tab_pj_fsd=array('cv','piece_identite','fsd_sujet','fsd_financement','fsd');
foreach($tab_rs_individu as $etat_individu_val=>$val)//pour chaque clé etat_individu de tab_individu => isset($tab_rs_individu[$etat_individu_val])=true 
{ foreach($tab_rs_individu[$etat_individu_val] as $codeindividu=>$row_rs_ind_sejour)
	{	foreach($row_rs_ind_sejour  as $numsejour=>$row_rs_ind)
		{ $datedeb_sejour=$row_rs_ind['datedeb_sejour'];
			$datefin_sejour=$row_rs_ind['datefin_sejour'];
			// FSD
			$demander_autorisation=false;
			$pourquoi_pas_de_demande_fsd='autoris&eacute; sans visa';//par défaut
			$tab_demander_autorisation=demander_autorisation($row_rs_ind,$tab_dates_sejour[$codeindividu]);
			if($row_rs_ind['date_autorisation']=='' &&  !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']))
			{ $demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
				$pourquoi_pas_de_demande_fsd=$tab_demander_autorisation['pourquoi_pas_de_demande_fsd'];
			}
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['demander_autorisation']=$demander_autorisation;
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['pourquoi_pas_de_demande_fsd']=$pourquoi_pas_de_demande_fsd;
			// prolongation/renouvellement : datefin_sejour_prevu < datefin_sejour pour preaccueil, accueil ou present
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['prolongation_fsd']=false;
			if(!$tab_demander_autorisation['dans_t0_moins_de_5_ans_contigu'] && $row_rs_ind['datefin_sejour_prevu']!='' && $row_rs_ind['datefin_sejour_prevu']<$row_rs_ind['datefin_sejour']
				 && ($etat_individu_val=='preaccueil' || $etat_individu_val=='accueil' || $etat_individu_val=='present'))
			{ $datefin_sejour_prevu_plus_1=date("Y/m/d",mktime(0,0,0,substr($row_rs_ind['datefin_sejour_prevu'],5,2),substr($row_rs_ind['datefin_sejour_prevu'],8,2)+1,substr($row_rs_ind['datefin_sejour_prevu'],0,4)));
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_duree_prolongation_fsd']=duree_aaaammjj($datefin_sejour_prevu_plus_1, $row_rs_ind['datefin_sejour']);
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_duree_avant_expire_fsd']=duree_aaaammjj(date("Y/m/d"), $row_rs_ind['datefin_sejour_prevu']);
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['prolongation_fsd']=($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_duree_avant_expire_fsd']['a']==0 && $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_duree_avant_expire_fsd']['m']<=6);
			}
			// controle de ces champs que si demande autorisation necessaire et demande et autorisation pas faite
			$tab_manque_info_fsd=array();
			if($demander_autorisation && $row_rs_ind['date_demande_fsd']=='' && $row_rs_ind['date_autorisation']=='')
			{	if($row_rs_ind['adresse_pers']==''){ $tab_manque_info_fsd['adresse_pers']="Adresse pers";}
				if($row_rs_ind['ville_pers']==''){ $tab_manque_info_fsd['ville_pers']="Ville adresse pers";}
				if($row_rs_ind['codepays_pers']==''){ $tab_manque_info_fsd['codepays_pers']="Pays adresse pers";}
				if($row_rs_ind['codesituationprofessionnelle']==''){ $tab_manque_info_fsd['situationprofessionnelle']="Situation professionnelle";}
				if($row_rs_ind['etab_orig']==''){ $tab_manque_info_fsd['etab_orig']="Organisme d'appartenance";}
				if($row_rs_ind['adresse_etab_orig']==''){ $tab_manque_info_fsd['adresse_etab_orig']="Adresse organisme d'appartenance";}
				if($row_rs_ind['ville_etab_orig']==''){ $tab_manque_info_fsd['ville_etab_orig']="Ville organisme d'appartenance";}
				if($row_rs_ind['codepays_etab_orig']==''){ $tab_manque_info_fsd['codepays_etab_orig']="Pays organisme d'appartenance";}
				if($row_rs_ind['codetypepieceidentite']=='' && $row_rs_ind['autretypepieceidentite']==''){ $tab_manque_info_fsd['typepieceidentite']="Type pi&egrave;ce d'identit&eacute;";}
				if($row_rs_ind['numeropieceidentite']==''){ $tab_manque_info_fsd['numeropieceidentite']="N&deg; pi&egrave;ce d'identit&eacute;";}
				if($row_rs_ind['codetypeacceszrr']==''){ $tab_manque_info_fsd['codetypeacceszrr']="Statut zrr";}
				if($row_rs_ind['codephysiquevirtuelzrr']==''){ $tab_manque_info_fsd['codephysiquevirtuelzrr']="Acc&egrave;s zrr";}
				if($row_rs_ind['codeoriginefinancement']==''){ $tab_manque_info_fsd['codeoriginefinancement']="Origine financement";}
				//if($row_rs_ind['montantfinancement']==''){ $tab_manque_info_fsd['montantfinancement']="Montant financement";}
				$tab=array();
				if(isset($tab_individu_fsd[$codeindividu][$numsejour]))
				{ foreach($tab_individu_fsd[$codeindividu][$numsejour] as $codelibtypepj=>$un_tab_individu_fsd)
					{ $tab[]=$codelibtypepj;
					  // 20170128
						$tab_suffixe=(explode('.',$un_tab_individu_fsd['nomfichier']));
						$suffixe=$tab_suffixe[1];
					  if($suffixe!='pdf' && in_array($codelibtypepj,array('cv','piece_identite','fsd_sujet','fsd_financement')))
						{ $tab_manque_info_fsd[$codelibtypepj]=$codelibtypepj.' : non pdf !';
						}
						// fin 20170128
					}
				}
				foreach(array_diff ( $tab_pj_fsd, $tab ) as $codelibtypepj)
				{ $tab_manque_info_fsd[$codelibtypepj]=$codelibtypepj;
				}
				// pour un POSTDOC, on a besoin de cette info si pas de sujet
				if($row_rs_ind['codelibcat']=='EC' || $row_rs_ind['codelibcat']=='CHERCHEUR' || $row_rs_ind['codelibcat']=='ITARF'
				 || ($row_rs_ind['codelibcat']=='POSTDOC' &&	!isset($tab_sujet[$codeindividu][$numsejour])))
				{ if($row_rs_ind['intituleposte']=='') {$tab_manque_info_fsd['intituleposte']="Intitul&eacute; du poste";}
					if($row_rs_ind['descriptionmission']=='') {$tab_manque_info_fsd['descriptionmission']="Description de la mission";}
					if($row_rs_ind['codedomainescientifique1']=='') {$tab_manque_info_fsd['codedomainescientifique1']="Domaine scientifique";}
					if($row_rs_ind['codedisciplinescientifique1']=='') {$tab_manque_info_fsd['codedisciplinescientifique1']="Discipline scientifique";}
				}
				// controle numdossierzrr si srhue ou admin
				if(isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['referent']) && !isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']) && (array_key_exists('srh',$tab_roleuser) || $est_admin))
				{ if($row_rs_ind['numdossierzrr']=='') {$tab_manque_info_fsd['numdossierzrr']="numdossierzrr";}
				}
				if(count($tab_manque_info_fsd)>0)
				{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['manque_info_fsd']=$tab_manque_info_fsd;
				}
			}
			// fin FSD
			$defaut_sejour='';//par defaut pas d'erreur dans le sejour de ce dossier
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['createurnom']=$tab_indgestionnaire[$row_rs_ind['codecreateur']]['nom'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['createurprenom']=$tab_indgestionnaire[$row_rs_ind['codecreateur']]['prenom'];
		
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['modifieurnom']=$tab_indgestionnaire[$row_rs_ind['codemodifieur']]['nom'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['modifieurprenom']=$tab_indgestionnaire[$row_rs_ind['codemodifieur']]['prenom'];
		
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['referentnom']=$tab_indgestionnaire[$row_rs_ind['codereferent']]['nom'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['referentprenom']=$tab_indgestionnaire[$row_rs_ind['codereferent']]['prenom'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['referentemail']=$tab_indgestionnaire[$row_rs_ind['codereferent']]['email'];
		
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['gestthemenom']=$tab_indgestionnaire[$row_rs_ind['codegesttheme']]['nom'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['gestthemeprenom']=$tab_indgestionnaire[$row_rs_ind['codegesttheme']]['prenom'];
			
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['postit']=isset($tab_postit[$codeindividu][$numsejour][$codeuser])?$tab_postit[$codeindividu][$numsejour][$codeuser]:'';

			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['codelibtypestage']=$tab_typestage[$row_rs_ind['codetypestage']]['codelibtypestage'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['sujetstageobligatoire']=$tab_typestage[$row_rs_ind['codetypestage']]['sujetstageobligatoire'];
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['libtypestage']=$tab_typestage[$row_rs_ind['codetypestage']]['libtypestage'];

			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['codesujet']=isset($tab_sujet[$codeindividu][$numsejour])?$tab_sujet[$codeindividu][$numsejour]['codesujet']:'';
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['titresujet_fr']=isset($tab_sujet[$codeindividu][$numsejour])?$tab_sujet[$codeindividu][$numsejour]['titresujet_fr']:'';
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['codestatutsujet']=isset($tab_sujet[$codeindividu][$numsejour])?$tab_sujet[$codeindividu][$numsejour]['codestatutsujet']:'';
			
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['associe']=isset($tab_associe[$codeindividu]);
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['hors_effectif']=isset($tab_hors_effectif[$codeindividu]);
			
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour_par_admin']=true;
			$supprsejour=true;//suppression du sejour (et du dossier si un seul sejour) possible par defaut)
			// sejour non supprimable car individu dans d'autres tables ou séjour parti
			// A revoir : faire la distinction d'un sejour ou d'un individu non supprimable ou dupliquer les elements necessaires (si encadrant these : copie des infos pour la these, si doctorant idem
			if(isset($tab_sejournonsuppr[$codeindividu]) || $etat_individu_val=='present' || $etat_individu_val=='parti' || $etat_individu_val=='sejourpartinonvalide')
			{ $supprsejour=false;
			}
			if($tab_nbsejour[$codeindividu]['nbsejour']==1)// un seul sejour : suppression de l'individu et du séjour si $supprsejour
			{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour']=$supprsejour;
				if(isset($tab_sejournonsuppr[$codeindividu]))
				{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour_par_admin']=false;
				}
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprindividu']=true;
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['textesuppression']="Supprimer d&eacute;finitivement ".$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['prenom'].' '.$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['nom'];
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['listersejour']=false;//pas de détail : un seul séjour
			}
			else 
			{	// plus d'un séjour : suppression du séjour uniquement
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour']=$supprsejour;
				if(isset($tab_sejournonsuppr[$codeindividu]['numsejour_sujet_verrouille']))
				{ if($tab_sejournonsuppr[$codeindividu]['numsejour_sujet_verrouille']!=$numsejour)
					{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour']=true;
						$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour_par_admin']=true;
					}
					else
					{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour']=false;
						$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour_par_admin']=false;
					}
				}
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprindividu']=false;
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['textesuppression']="Supprimer le s&eacute;jour de ".$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['prenom'].' '.$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['nom'];
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['listersejour']=true;
			}
			// roles du user sur le sejour de l'individu
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_resp_roleuser']=$tab_resp_roleuser;
			if(isset($tab_roleuser_individusejour[$codeindividu][$numsejour]['referent']))
			{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_resp_roleuser']['tab_roleuser']['referent']=$tab_roleuser_individusejour[$codeindividu][$numsejour]['referent'];
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_resp_roleuser']['estreferent']=$tab_roleuser_individusejour[$codeindividu][$numsejour]['estreferent'];
			}
			if(isset($tab_roleuser_individusejour[$codeindividu][$numsejour]['theme']))
			{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_resp_roleuser']['tab_roleuser']['theme']=$tab_roleuser_individusejour[$codeindividu][$numsejour]['theme'];
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_resp_roleuser']['estresptheme']=$tab_roleuser_individusejour[$codeindividu][$numsejour]['estresptheme'];
			}
			// infos supplémentaires et Defauts dans les séjours
			if(($row_rs_ind['codelibcat']=='EC' || $row_rs_ind['codelibcat']=='CHERCHEUR'))
			{ if(isset($tab_ed[$codeindividu]['codeed']) && $tab_ed[$codeindividu]['codeed']=='')
				{ $defaut_sejour.=($defaut_sejour==''?'':'<br>')."Pas d'ED";
				}
				$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['libed']=$tab_ed[$codeindividu]['libed'];
			}
			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['annee_these']='';
 			if($row_rs_ind['codestatutpers']=='02' && /* $row_rs_ind['codelibcat']!='PATP' &&  */$row_rs_ind['codelibcat']!='EXTERIEUR' )
			{ /* if($datefin_sejour=='')
				{ $defaut_sejour.='Date fin s&eacute;jour vide';
					if($row_rs_ind['datefin_sejour_prevu']!='')
					{ $defaut_sejour.=' - Date fin s&eacute;jour pr&eacute;vue renseign&eacute;e';
					}
				} */
				$query_rs = "SELECT datedeb_emploi,datefin_emploi,codemodefinancement FROM individusejour,individuemploi ".
										" WHERE individusejour.codeindividu=".GetSQLValueString($codeindividu, "text").
										" and individusejour.numsejour=".GetSQLValueString($numsejour, "text").
										" and individusejour.codeindividu=individuemploi.codeindividu".
										" and ".intersectionperiodes('datedeb_sejour','datefin_sejour','datedeb_emploi','datefin_emploi').
										" order by datedeb_emploi";
				$rs = mysql_query($query_rs) or die(mysql_error());
				$first=true;
				$datedeb_emploi_dernier='';
				$datefin_emploi_dernier='';
				$codemodefinancement='';// 20170315
				while($row_rs = mysql_fetch_assoc($rs))
				{ if($first)
					{ $first=false;
					}
					else if($row_rs['datefin_emploi']!='' && date("Y/m/d",mktime(0,0,0,substr($datefin_emploi_dernier,5,2),substr($datefin_emploi_dernier,8,2)+1,substr($datefin_emploi_dernier,0,4)))!=$row_rs['datedeb_emploi'])
					{ $defaut_sejour.=($defaut_sejour==''?'':'<br>').'Dates emplois non contig&uuml;es : '.aaaammjj2jjmmaaaa(date("Y/m/d",mktime(0,0,0,substr($datefin_emploi_dernier,5,2),substr($datefin_emploi_dernier,8,2)+1,substr($datefin_emploi_dernier,0,4))),'/').' et '.aaaammjj2jjmmaaaa($row_rs['datedeb_emploi'],'/');
					}
					$datedeb_emploi_dernier=$row_rs['datedeb_emploi'];
					$datefin_emploi_dernier=$row_rs['datefin_emploi'];
					if($row_rs['datefin_emploi']=='')
					{ $defaut_sejour.=($defaut_sejour==''?'':'<br>').'Date fin emploi vide';
					}
					$codemodefinancement=$row_rs['codemodefinancement'];
				}
				if($row_rs_ind['codelibcat']=='DOCTORANT' && $codemodefinancement=='01')//ATER
				{ $query_rs="select date_soutenance from individuthese".
										" where codeindividu=".GetSQLValueString($codeindividu, "text").
										" and numsejour=".GetSQLValueString($numsejour, "text");
					$rs= mysql_query($query_rs) or die(mysql_error());
					$date_soutenance='';
					if($row_rs=mysql_fetch_assoc($rs))
					{ $date_soutenance=$row_rs['date_soutenance'];
					}
					if($date_soutenance!='' && ($datefin_emploi_dernier=='' || $datefin_emploi_dernier>$datefin_sejour))
					{ //recherche sejour postdoc emploi
						$query_rs ="SELECT * FROM individusejour,individuemploi".
										" WHERE individusejour.codeindividu=".GetSQLValueString($codeindividu, "text").
										" and ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$datedeb_emploi_dernier."'","'".$datefin_emploi_dernier."'").
										" and individusejour.codecorps='53'";
						
						$rs= mysql_query($query_rs) or die(mysql_error());
						if(!($row_rs=mysql_fetch_assoc($rs)))
						{ $defaut_sejour.=($defaut_sejour==''?'':'<br>').'<b>Doctorant ATER sans postdoc</b>';
						}
					}
				}
				if($row_rs_ind['codelibcat']=='DOCTORANT')
				{ if($etat_individu_val!='preaccueil')
					{	if(!isset($tab_individuthese[$codeindividu][$numsejour]))
						{ $defaut_sejour.=($defaut_sejour==''?'':'<br>').'Sujet non verrouill&eacute;';
						}
						else
						{ if($datefin_sejour!=$tab_individuthese[$codeindividu][$numsejour]['date_soutenance'] && $tab_individuthese[$codeindividu][$numsejour]['date_soutenance']!='')
							{ $defaut_sejour.=($defaut_sejour==''?'':'<br>').'date fin s&eacute;jour : '.$datefin_sejour.' date soutenance : '.$tab_individuthese[$codeindividu][$numsejour]['date_soutenance'];
							}
							$row=$tab_individuthese[$codeindividu][$numsejour];
							$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['annee_these']=calcule_annee_these($row['date_preminscr'],$row['date_soutenance'],$row['num_inscr_ajuste']).'A';
							if($datefin_sejour<$aujourdhui && $datefin_sejour!='' && $tab_individuthese[$codeindividu][$numsejour]['date_soutenance']=='')
							{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['annee_these']='ABANDON';
							}
							if($tab_individuthese[$codeindividu][$numsejour]['date_soutenance']<$aujourdhui && $tab_individuthese[$codeindividu][$numsejour]['date_soutenance']!='')
							{ $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['annee_these']='SOUTENU';	
							}
							$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['libed_these']=$tab_individuthese[$codeindividu][$numsejour]['libed'];
						}
					}
				}
			}
 			$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['defaut_sejour']=$defaut_sejour;
		}
	}
}
// application des filtres si liste d'individus
if(!$_SESSION['b_lister_sejours'])
{ $tab_rs_individu_filtree=array();
	foreach($tab_rs_individu as $etat_individu_val=>$tab_rs_individu_etat) 
	{ foreach($tab_rs_individu_etat as $codeindividu=>$row_rs_ind)
		{	foreach($row_rs_ind  as $numsejour=>$row_rs_ind_sejour)
			{ $affiche=true;
				if($_SESSION['select_ind_voir_categorie']!='' && $row_rs_ind_sejour['codecat']!=$_SESSION['select_ind_voir_categorie'])
				{ $affiche=false;
				}
				// 20170315
				if($_SESSION['select_ind_voir_theme']!='' && !isset($tab_individutheme[$codeindividu][$numsejour]["theme"][$_SESSION['select_ind_voir_theme']]))
				{ $affiche=false;
				}
				if($_SESSION['b_ind_voir_cdd'] && ($row_rs_ind_sejour['codestatutpers']=='01' || $row_rs_ind_sejour['codelibcat']=='EXTERIEUR' /* || $row_rs_ind_sejour['codelibcat']=='PATP' */))
				{ $affiche=false;
				}
				if($_SESSION['b_ind_voir_associe'] && !$row_rs_ind_sejour['associe'])
				{ $affiche=false;
				}
				if($_SESSION['b_ind_voir_hdr'] && $row_rs_ind_sejour['hdr']!='oui')
				{ $affiche=false;
				}
				if($_SESSION['b_ind_voir_cotutelle'])
				{ if(!isset($tab_individuthese[$codeindividu][$numsejour]))
					{ $affiche=false;
					}
					else if($tab_individuthese[$codeindividu][$numsejour]['cotutelle']!='oui')
					{ $affiche=false;
					}
				}
				if($_SESSION['b_ind_voir_doctorant_sans_inscription'])
				{ if(!isset($tab_individuthese[$codeindividu][$numsejour]))
					{ $affiche=false;
					}
					else if($tab_individuthese[$codeindividu][$numsejour]['date_preminscr']!='')
					{ $affiche=false;
					}
				}
				if($_SESSION['b_ind_voir_doctorant_abandon'])
				{ if(!isset($tab_individuthese[$codeindividu][$numsejour]))
					{ $affiche=false;
					}
					else if($row_rs_ind_sejour['annee_these']!='ABANDON')
					{ $affiche=false;
					}
				}
				if($_SESSION['b_ind_voir_hors_effectif'] && $row_rs_ind_sejour['hors_effectif']!='oui')
				{ $affiche=false;
				}

				// demande d'autorisation ou prolongation
				if($_SESSION['b_ind_voir_demander_autorisation'] && 
					(isset($tab_tout_individustatutvisa[$codeindividu][$numsejour]['srhue']) || !$row_rs_ind_sejour['demander_autorisation']) && 
					(!$row_rs_ind_sejour['prolongation_fsd']) &&  $row_rs_ind_sejour['date_demande_modification_fsd']=='')
				{ $affiche=false;
				}
				if($_SESSION['b_ind_voir_pas_de_declaration_fsd'] && $row_rs_ind_sejour['demander_autorisation'])
				{ $affiche=false;
				}
				if($_SESSION['b_ind_voir_cahier_visite'] && ($row_rs_ind_sejour['pourquoi_pas_de_demande_fsd']!='- de 5j' && $row_rs_ind_sejour['pourquoi_pas_de_demande_fsd']!='stage < M2'))
				{ $affiche=false;
				}
				if($affiche)
				{ $tab_rs_individu_filtree[$etat_individu_val][$codeindividu][$numsejour]=$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour];
				}
			}
		}
	}	
	$tab_rs_individu=$tab_rs_individu_filtree;
}

$rs=mysql_query("select codecat, codelibcat from cat order by numordre");
$tab_cat=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cat[$row_rs['codecat']]=$row_rs['codelibcat'];
}
// 20170315
$rs=mysql_query("select codestructure as codetheme, if(codestructure='00','SC-Adm',structure.libcourt_fr) as libtheme from structure where (esttheme='oui' or codestructure='') and ".periodeencours('date_deb','date_fin')." order by codestructure");
$tab_theme=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_theme[$row_rs['codetheme']]=$row_rs['libtheme'];
}

if(isset($rs)) mysql_free_result($rs);
if(isset($rs_ind))mysql_free_result($rs_ind);
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree reste traitement avant html : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	echo '<br>duree totale avant html : '.($timefin-$timedeb_avanthtml); 
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des personnels <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico" />
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<style type="text/css">
body,td,th {
	font-family: Calibri;
	font-size: 10pt;
	color: #000;
}
.m {
	color: #990099;
	font-size: 10pt;
	font-family: Calibri;
}

#note {
  position: absolute;
	background-color: #FFF;
	border: thin solid #DD04B1;
	width: auto;
	height: auto;
	color: #006;
	border-radius: 3px;
	padding: 6px;
	font-family: Calibri;
	font-size: 12px;
	margin: 4px;
	text-align: left;
}

</style>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

<SCRIPT language="javascript">


var tab_note=new Array();
var tab_postit=new Array();
var tab_manque_info_fsd=new Array();
<?php 
$nbtablerow=0;
foreach($tab_etat_individu as $etat_individu_val)
{ if(isset($tab_rs_individu[$etat_individu_val]) && count($tab_rs_individu[$etat_individu_val])>0)//si une ligne de tableau au moins pour etat_individu_val
  { foreach($tab_rs_individu[$etat_individu_val] as $codeindividu=>$row_rs_ind_sejour)
		{	foreach($row_rs_ind_sejour  as $numsejour=>$row_rs_individu)
			{	$nbtablerow++?>	
				tab_note['<?php echo $codeindividu.$numsejour ?>']="<?php echo js_tab_val(nl2br($row_rs_individu['note'])) ?>";
				tab_postit['<?php echo $codeindividu.$numsejour ?>']="<?php echo js_tab_val(nl2br($row_rs_individu['postit'])) ?>";
				<?php 
				if(isset($row_rs_individu['manque_info_fsd']) && count($row_rs_individu['manque_info_fsd'])>0)
				{?> //tab_manque_info_fsd['<?php //echo $codeindividu.$numsejour ?>']="<table><tr><td nowrap><?php //echo js_tab_val(implode("</td></tr><tr><td nowrap>",$row_rs_individu['manque_info_fsd'])) ?></table>";
						tab_manque_info_fsd['<?php echo $codeindividu.$numsejour ?>']="- <?php echo js_tab_val(str_replace(" ","&nbsp;",implode("<br>- ",$row_rs_individu['manque_info_fsd']))) ?>";
				<?php 
				}
			}
		}
	}
}
?>
var tab_sejournonsuppr=new Array();
<?php foreach($tab_sejournonsuppr as $codeindividu=>$row_rs)
{?> 
	tab_sejournonsuppr['<?php echo $codeindividu ?>']="<?php echo js_tab_val($tab_sejournonsuppr[$codeindividu]['explication']) ?>";
<?php
}?>

function affiche_note(objet,typeinfo,code,event)
{ //alert(objet);
	var postit='';
	event.returnValue = false;
	if( event.preventDefault ) event.preventDefault();
	//Coordonnees de la souris
  var x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft);
  var y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop);

	//Coordonnées de l'élément
  var eX = 0;
  var eY = 0;
	var offsetWidth=0;
  var element = objet;
  do
  { eX += element.offsetLeft;
    eY += element.offsetTop;
		offsetWidth=element.offsetWidth;
    element = element.offsetParent;
  } while( element && element.style.position != 'absolute');
	
	
	document.getElementById('note').className="affiche";
	document.getElementById('note').style.position = 'absolute';
	if(document.body.offsetWidth - eX>200)
	{ document.getElementById('note').style.left = (eX+20) + 'px';
	}
	else
	{ document.getElementById('note').style.left = (eX-200) + 'px';
	}
	document.getElementById('note').style.top = (eY+20) + 'px';
	if(typeinfo=='note')
	{ if(tab_postit[code]!="")
		{ postit='<br><span class="grisgrascalibri11">Notes perso.</span><br>'+tab_postit[code];
		}
		document.getElementById('note').innerHTML=tab_note[code]+postit;
	}
	else if(typeinfo=='nonsuppr')
	{	document.getElementById('note').innerHTML='Non supprimable car :<br>'+tab_sejournonsuppr[code];
	}
	else if(typeinfo=='manque_info_fsd')
	{ document.getElementById('note').innerHTML='<b>Dossier FSD incomplet :</b><br>'+tab_manque_info_fsd[code];
		//document.getElementById('note').style.left=document.getElementById('note')
	}
	else if(typeinfo=='manque_pj')
	{ document.getElementById('note').innerHTML='<b>Manque PJ:</b><br>'/* +tab_manque_info_fsd[code] */;
		//document.getElementById('note').style.left=document.getElementById('note')
	}
}
//affiche ou cache les filtres en fonction de la catégorie choisie
function affiche_cache_filtre()
{ document.getElementById('note').className="cache";
}

function cache_note(code)
{ document.getElementById('note').className="cache";
}
var w;
function OuvrirVisible(code)
{ if(code.substring(0,1)=='I')
	{ codeindividu=code.substring(1,6);
		numsejour=code.substring(6,8);
		url="detailindividu.php?codeindividu="+codeindividu+"&numsejour="+numsejour;
	}
	else if(code.substring(0,1)=='S')
	{ codesujet=code.substring(1,6);
		url="detailsujet.php?codesujet="+codesujet;
	}
	w=window.open(url,'detailindividu',"scrollbars = yes,width=700,height=350,location=no,mebubar=no,status=no,directories=no");
	w.document.close();
	w.focus();
}
function OV(code)
{ return OuvrirVisible(code);
}
function Fermer() 
{ if (w.document) { w.close(); }
}

var nbtablerow=<?php echo $nbtablerow ?>;
function m(tablerow)// marque ligne en vert
{ even_ou_odd='even';
	for(numrow=1;numrow<=nbtablerow;numrow++)
	{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
		document.getElementById('t'+numrow).className=even_ou_odd;
	}
	document.getElementById(tablerow.id).className='marked';
}

function e(codeindividusejour,etat_individu_val)
{ var tab_codeindividusejour = codeindividusejour.split(".");
	document.location.href="edit_individu.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour="+tab_codeindividusejour[1]+"&etat_individu="+etat_individu_val+"&action=modifier&ind_ancre="+codeindividusejour;//"
}
function ep(codeindividusejour,etat_individu_val)
{ var tab_codeindividusejour = codeindividusejour.split(".");
	document.location.href="edit_fiche_dossier_pers_partiel.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour="+tab_codeindividusejour[1]+"&etat_individu="+etat_individu_val+"&action=modifier&ind_ancre="+codeindividusejour;//"
}

function ns(codeindividusejour,permanent)
{ var tab_codeindividusejour = codeindividusejour.split(".");
	document.location.href="edit_individu.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour_leplusrecent="+tab_codeindividusejour[1]+"&permanent="+permanent+
													"&etat_individu=preaccueil&submit_ajouter_sejour_x=";
}
							
function cai(codeindividusejour,action,codevisa_a_apposer)
{ var tab_codeindividusejour = codeindividusejour.split(".");
	document.location.href="confirmer_action_individu.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour="+tab_codeindividusejour[1]
													+"&action="+action+"&ind_ancre="+codeindividusejour+(codevisa_a_apposer==''?"":"&codevisa_a_apposer="+codevisa_a_apposer);
}
/* function xl(codeindividusejour,docfsdfait)
{ if(docfsdfait=='o')
	{ if(!confirm("Le document FSD existe : écraser l'ancien ?"))
		{ return;
		}
	}
	var tab_codeindividusejour = codeindividusejour.split(".");
	document.location.href="fsd_xl_php.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour="+tab_codeindividusejour[1]+"&ind_ancre="+codeindividusejour;

} */

function fsd(codeindividusejour,action)
{ var tab_codeindividusejour = codeindividusejour.split(".");
	if(action=='D')
	{ document.location.href="confirmer_mail_validation_declaration_fsd.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour="+tab_codeindividusejour[1]+"&ind_ancre="+codeindividusejour/*  */;
	}
	else//action='P'
	{ document.location.href="confirmer_mail_validation_prolongation_fsd.php?codeindividu="+tab_codeindividusejour[0]+"&numsejour="+tab_codeindividusejour[1]+"&ind_ancre="+codeindividusejour/*  */;
	}
}
function prolongation_fsd(codeindividusejour)
{ var tab_codeindividusejour = codeindividusejour.split(".");
}

</SCRIPT>

</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"<?php 
						}
						else
						{?> onLoad="window.location.hash='<?php echo $ind_ancre ?>';"
						<?php 
						}?>
            >
<div id="note" class="cache" style="width:200px">
 cadre d'affichage des notes au passage souris
</div>
<a name="haut_page"></a>
<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'images/b_couple.png','titrepage'=>'Gestion des personnels','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>$msg_erreur_objet_mail,'erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
  <tr>
		<td align="left">
		<?php 
		// modif 20160426
		//if(!$_SESSION['b_lister_sejours'])
    if(!$_SESSION['b_lister_sejours'])
		// fin modif 20160426
    {?>
			<table border="0" cellspacing="1" cellpadding="0" width="100%">
				<tr>
        	<td>
        		<table>
              <tr>
              	<td>
                	<table>
                  	<tr>
                      <td nowrap><span class="bleugrascalibri11">Cr&eacute;ation :&nbsp;</span>
                      </td>
                      <form name="edit_individu" method="post" action="edit_individu.php">
                      <input type="hidden" name="etat_individu" value="preaccueil">
                      <input type="hidden" name="action" value="creer">
                      <td>
                        <input name="submit_creer" id="sprytrigger_submit_creer" type="image"  img class="icon" src="images/b_ind_creer.png" alt="Cr&eacute;er" />
                      <?php 
                       if(!(array_key_exists('srh',$tab_roleuser) || $estgesttheme || $est_admin))// 20161213 limitation
                       {?><span class="orangecalibri11">(Moins de 5 jours)</span>
                       <?php 
                       }?>
                         <div class="tooltipContent_cadre" id="submit_creer">
                          <span class="noircalibri10">
                            <b>Il est tr&egrave;s important de ne pas cr&eacute;er de doublons dans la base de donn&eacute;es.</b><br>
                            Avant de cr&eacute;er un nouveau dossier, assurez-vous que la personne n'est pas d&eacute;j&agrave; enregistr&eacute;e<br>
                            en effectuant une recherche sur le crit&egrave;re 'NomPr&eacute;nom' 'contient' tout ou partie du nom et/ou pr&eacute;nom.<br>
                            La recherche peut &ecirc;tre effectu&eacute;e sur n'importe quelle partie du NomPr&eacute;nom.<br>
                            Exemple : pour retrouver vos NomPr&eacute;nom, vous pouvez rechercher <b><i><?php echo strtolower(substr($tab_infouser['nom'],0,4)) ?></i></b> ou <b><i><?php echo strtolower(substr($tab_infouser['nom'],strlen($tab_infouser['nom'])-3)) ?></i></b> ou encore <b><i><?php echo strtolower(substr($tab_infouser['nom'],strlen($tab_infouser['nom'])-2).substr($tab_infouser['prenom'],0,2)) ?></i></b><br>
                          	Ne faites pas de recherche sur des lettres accentu&eacute;es.<br>
                            <b>Les noms, contrairement aux pr&eacute;noms, sont saisis en majuscules pour &eacute;viter les lettres accentu&eacute;es et ainsi<br>faciliter les recherches</b><br>
                         	  Si la personne existe d&eacute;j&agrave;, cliquez sur l'ic&ocirc;ne <img src="images/b_add.png" width="16" height="16"> de la colonne "Actions" :<br>
                            les informations du dernier s&eacute;jour seront copi&eacute;es dans le nouveau s&eacute;jour que vous cr&eacute;ez</span>
                        </div>
                        <script type="text/javascript">
                          var sprytooltip_submit_creer = new Spry.Widget.Tooltip("submit_creer", "#sprytrigger_submit_creer", {offsetX:+20, offsetY:0, closeOnTooltipLeave:true});
                        </script>                      
                      </td>
                      </form>
                      <td class="bleugrascalibri10"><a href="aide_gestionindividus.php"><img src="images/b_info.png">&nbsp;Aide gestion individus</a></td>
                      <?php // 20161213 limitation
											if($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser))
                      {?> 
                      	<td class="bleugrascalibri10">&nbsp;&nbsp;<a href="https://process.univ-lorraine.fr/depeche" target="_blank">&nbsp;D&eacute;claration des personnels h&eacute;berg&eacute;s</a></td>
                      	<td class="bleucalibri10">&nbsp;&nbsp;<a href="listes_individus.php">Listes individus : annuaire, cahier de visites, personnels</a></td>
                    	<?php 
											}?>
                    </tr>
                  </table><!-- table10-->
                </td>
              </tr>
              <tr>
                <td>
                  <table>
                  <form name="<?php echo $form_gestionindividus_voir ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <tr>
                      <td>
                        <img src="images/espaceur.gif" width="20" height="1">
                      </td>
                      <td nowrap>
                        <span class="bleugrascalibri11">Afficher :&nbsp;</span>
                      </td>
                      <td>
                        <input  name="b_ind_voir_presents" type="image" class="icon" src="images/b_ind_voir_presents_<?php echo ($_SESSION['b_ind_voir_presents']?"oui":"non") ?>.png" alt="Afficher les pr&eacute;sents : <?php echo ($_SESSION['b_ind_voir_presents']?"oui":"non") ?>" title="Afficher les pr&eacute;sents : <?php echo ($_SESSION['b_ind_voir_presents']?"oui":"non") ?>">
                      </td>
                      <td>
                        <input type="image"  name="b_ind_voir_partis" img class="icon" src="images/b_ind_voir_partis_<?php echo ($_SESSION['b_ind_voir_partis']?"oui":"non") ?>.png" alt="Afficher les partis : <?php echo ($_SESSION['b_ind_voir_partis']?"oui":"non") ?>" title="Afficher les partis : <?php echo ($_SESSION['b_ind_voir_partis']?"oui":"non") ?>">
                      </td>
                      <!-- 20170312
                      <td><img src="images/espaceur.gif" width="5" height="1">            
                        <input type="image"  name="b_voir_tous_themes" img class="icon" src="images/b_voir_tous_themes_<?php //echo ($_SESSION['b_voir_tous_themes']?"oui":"non") ?>.png" alt="Afficher tous les <?php //echo $GLOBALS['libcourt_theme_fr'] ?>s : <?php //echo ($_SESSION['b_voir_tous_themes']?"oui":"non") ?>" title="Afficher tous les <?php //echo $GLOBALS['libcourt_theme_fr'] ?>s : <?php // echo ($_SESSION['b_voir_tous_themes']?"oui":"non") ?>">
                      </td> -->
                      <!-- 20161213 -->
                       <td><img src="images/espaceur.gif" width="5" height="1">            
                        <input type="image"  name="b_ind_voir_mes_dossiers" src="images/b_ind_voir_mes_dossiers_<?php echo ($_SESSION['b_ind_voir_mes_dossiers']?"oui":"non") ?>.png" alt="Afficher tous les dossiers : <?php echo ($_SESSION['b_ind_voir_mes_dossiers']?"oui":"non") ?>" title="Afficher tous les dossiers : <?php echo ($_SESSION['b_ind_voir_mes_dossiers']?"oui":"non") ?>">
                      </td>
                      <td><img src="images/espaceur.gif" width="20" height="1">
                      </td>
                      <td nowrap>
                      <select name="ind_champrecherche" class="noircalibri9">
												<?php 
                        foreach($tab_ind_champrecherche as $un_ind_champrecherche=>$ind_valchamprecherche)
                        { ?><option value="<?php echo $un_ind_champrecherche ?>" <?php echo ($_SESSION['ind_champrecherche']==$un_ind_champrecherche?'selected':'')?>><?php echo $ind_valchamprecherche ?></option>
                        <?php 
                        }?>
                      </select>
												<span class="bleugrascalibri10"> contient :&nbsp;</span>
                      </td>
                      <td><input type="text" class="noircalibri10" name="ind_texterecherche" id="ind_texterecherche" value="<?php echo htmlspecialchars($_SESSION['ind_texterecherche']==''?'':$_SESSION['ind_texterecherche'])  ?>" size="20">
                      </td>
                      <td>
                        <input type="image"  name="b_rechercher" img class="icon" src="images/b_rechercher.png" alt="Rechercher" title="Rechercher">
                      </td>
											<td><input type="image" name="b_corbeille" src="images/b_corbeille.png" width="16" height="16" title="Vider la zone de recherche"
                          >
                      </td>
                       <td align="left">
                        <img src="images/espaceur.gif"  width="50" height="1"><a href="listeperslabo.php" target="_blank">Export annuaire format Excel</a>
                       </td>
                     </tr>
                   </table>
                 </td>
              </tr>
              <tr>
                 <td>
                   <table>
                    	<tr>
                        <td>
                          <img src="images/espaceur.gif" width="20" height="1">
                        </td>
                        <td>
                          <select class="noircalibri10" name="select_ind_voir_categorie" onChange="submit()">
                          <?php 
                          foreach($tab_cat as $codecat=>$codelibcat)
                          {?><option class="noircalibri10" value="<?php echo $codecat ?>" <?php echo ($codecat==$_SESSION['select_ind_voir_categorie']?"selected":"")?>><?php echo $codelibcat ?></option>
                          <?php 
                          }?>
                          </select>
                        </td>
                      	<td>
                          <table>
                            <tr>
                              <td><img src="images/espaceur.gif" width="5" height="1">
                                <input type="image"  name="b_ind_voir_cdd" img class="icon" src="images/b_ind_voir_cdd_<?php echo ($_SESSION['b_ind_voir_cdd']?"oui":"non") ?>.png">
                              </td>
                              <td><img src="images/espaceur.gif" width="5" height="1">            
                                <input type="image"  name="b_ind_voir_associe" img class="icon" src="images/b_ind_voir_associe_<?php echo ($_SESSION['b_ind_voir_associe']?"oui":"non") ?>.png">
                              </td>
                              <td><img src="images/espaceur.gif" width="5" height="1">
                                <input type="image"  name="b_ind_voir_hdr" img class="icon" src="images/b_ind_voir_hdr_<?php echo ($_SESSION['b_ind_voir_hdr']?"oui":"non") ?>.png">
                              </td>
                              <td><img src="images/espaceur.gif" width="5" height="1">
                                <input type="image"  name="b_ind_voir_cotutelle" img class="icon" src="images/b_ind_voir_cotutelle_<?php echo ($_SESSION['b_ind_voir_cotutelle']?"oui":"non") ?>.png">
                              </td>
                              <td><img src="images/espaceur.gif" width="5" height="1">
                                <input type="image"  name="b_ind_voir_doctorant_sans_inscription" img class="icon" src="images/b_ind_voir_doctorant_sans_inscription_<?php echo ($_SESSION['b_ind_voir_doctorant_sans_inscription']?"oui":"non") ?>.png">
                              </td>
                              <td><img src="images/espaceur.gif" width="5" height="1">
                                <input type="image"  name="b_ind_voir_doctorant_abandon" img class="icon" src="images/b_ind_voir_doctorant_abandon_<?php echo ($_SESSION['b_ind_voir_doctorant_abandon']?"oui":"non") ?>.png">
                              </td>
                              <?php 
															if($hors_effectif_existe)
                              {?> 
                              	<td><img src="images/espaceur.gif" width="5" height="1">
                                <input type="image"  name="b_ind_voir_hors_effectif" img class="icon" src="images/b_ind_voir_hors_effectif_<?php echo ($_SESSION['b_ind_voir_hors_effectif']?"oui":"non") ?>.png">
                              	</td>
                              <?php 
															}?>
                            </tr>
                          </table>
                      </td>
                      <td><img src="images/espaceur.gif" width="50" height="1"></td>
                      <td nowrap class="bleugrascalibri10"><?php echo $GLOBALS['libcourt_theme_fr'] ?>
                        <select class="noircalibri10" name="select_ind_voir_theme" onChange="submit()">
                        <?php 
                        foreach($tab_theme as $codetheme=>$libtheme)
                        {?><option class="noircalibri10" value="<?php echo $codetheme ?>" <?php echo ($codetheme==$_SESSION['select_ind_voir_theme']?"selected":"")?>><?php echo $libtheme ?></option>
                        <?php 
                        }?>
                        </select>
                      </td>
                      <td><img src="images/espaceur.gif" width="50" height="1"></td>
                      <td>
                      <?php  
											if($GLOBALS['estzrr'] && isset($tab_incoherences['fsd']['datefin_sejour_incoherentes']) && (array_key_exists('srh',$tab_roleuser)  || $est_admin))//20170315
											{ ?>
											<img src="images/b_ind_incoherences.gif" id="sprytrigger_info_fsd_incoherences">
											 <div class="tooltipContent_cadre" id="info_fsd_incoherences">
												<span class="noircalibri10">
													Le(s) s&eacute;jour(s) suivant(s) comporte(nt) une date de fin mais pas de date de fin pr&eacute;vue : <br>
													<?php foreach($tab_incoherences['fsd']['datefin_sejour_incoherentes'] as $codeindividu_numsejour=>$un_individu_sejour)
													{ echo $codeindividu_numsejour.' '.$un_individu_sejour['nom'].' '.$un_individu_sejour['prenom'].'<br>';
													}
													?>
												</span>
											</div>
											<script type="text/javascript">
												var sprytooltip_info_fsd_incoherences = new Spry.Widget.Tooltip("info_fsd_incoherences", "#sprytrigger_info_fsd_incoherences", {offsetX:-100, offsetY:0, closeOnTooltipLeave:true});
											</script>
											<?php 
											}
											else /**/
											{?>
                      <img src="images/espaceur.gif" width="50" height="1">
											<?php 
											}?>
                      </td>
                      <?php if($GLOBALS['estzrr'])
                      {?> <td><img src="images/espaceur.gif" width="5" height="1"><input type="image" name="b_ind_voir_demander_autorisation" src="images/b_ind_voir_demander_autorisation_<?php echo ($_SESSION['b_ind_voir_demander_autorisation']?"oui":"non") ?>.png" class="noircalibri10" alt="b_ind_voir_demander_autorisation" title="Autorisation FSD &agrave; faire">
                      </td>
                      <td><img src="images/espaceur.gif" width="5" height="1"><input type="image" name="b_ind_voir_pas_de_declaration_fsd" src="images/b_ind_voir_pas_de_declaration_fsd_<?php echo ($_SESSION['b_ind_voir_pas_de_declaration_fsd']?"oui":"non") ?>.png" class="noircalibri10" alt="b_ind_voir_pas_de_declaration_fsd" title="Pas de d&eacute;claration fsd">
                      </td>
                      <td><img src="images/espaceur.gif" width="5" height="1"><input type="image" name="b_ind_voir_cahier_visite" src="images/b_ind_voir_cahier_visite_<?php echo ($_SESSION['b_ind_voir_cahier_visite']?"oui":"non") ?>.png" class="noircalibri10" alt="b_ind_voir_cahier_visite" title="Cahier de visite">
                      </td>
                      <?php }?>
                     <!-- <td><img src="images/espaceur.gif" width="100" height="1">
                        <input type="image"  name="b_ind_voir_anomalies" img class="icon" src="images/b_ind_voir_anomalies_<?php //echo ($_SESSION['b_ind_voir_anomalies']?"oui":"non") ?>.png">
                      <
                    </tr>
                  </table><!-- table11-->
                </td>
              </tr>
            </form>
            </table><!-- table12-->
          </td>
        </tr>
			</table><!-- table13-->
		<?php
    }
    else//voir detail individu
    {?>
			<table border="0" cellspacing="1" cellpadding="0" align="center">
				<tr>
          <td align="left">
            <img src="images/espaceur.gif" alt="" width="20" height="1">
            <form name="form_fiche_dossier_pers_lister_sejours" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
              <input type="hidden" name="MM_update" value="<?php echo $_SERVER['PHP_SELF'] ?>">
              <input type="hidden" name="action" value="lister_sejours">
              <input type="image" name="b_lister_sejours" img class="icon" src="images/b_ind_detail_retour.png" title="Liste des individus"/>
            </form>
            </td>
			  </tr>
			</table><!-- table14-->
		<?php 
    }?>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" class="data" id="table_results">
		<?php 
		$numrow=0;
    foreach($tab_etat_individu as $etat_individu_val)
    {	if(isset($tab_rs_individu[$etat_individu_val]) && count($tab_rs_individu[$etat_individu_val])>0)//si une ligne de tableau au moins pour etat_individu_val
      { ?>
				<tr>
					<td colspan="<?php echo $nb_col_a_afficher ?>"><?php echo $tab_etat_individu_entete[$etat_individu_val] ?>
					</td>
				</tr>
				<tr class="head">
					<td nowrap align="center"><span class="bleugrascalibri11">N&deg;</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri11">Dossier </span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri11">Cat&eacute;gorie</span>
					</td>
          <?php if($GLOBALS['gestionindividus_col_sujet'])
					{?><td nowrap align="center"><span class="bleugrascalibri11">Sujet</span>
					</td>
          <?php 
					}?>
          <?php if($GLOBALS['gestionindividus_col_theme'])
					{?><td nowrap align="center"><span class="bleugrascalibri11"><?php echo $GLOBALS['libcourt_theme_fr'] ?></span>
					</td>
          <?php 
					}?>
					<td nowrap align="center"><span class="bleugrascalibri11">R&eacute;f&eacute;rent</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri11">Suivi par</span>
					</td>
					<td nowrap align="center">
          	<span class="bleugrascalibri11">
            <?php 
						switch ($etat_individu_val)
						{ case 'preaccueil' :?>Arriv&eacute;e<br>pr&eacute;vue
            <?php break;
						  case 'accueil' :?>D&eacute;but<br>s&eacute;jour
            <?php break;
							case 'present' :?>D&eacute;but<br>s&eacute;jour
            <?php break;
							case 'parti' :?>Fin<br>s&eacute;jour
            <?php break;
							case 'sejourpartinonvalide' :?>D&eacute;but<br>s&eacute;jour
						<?php break;
							case 'anomalie' :?>D&eacute;but<br>s&eacute;jour
						<?php break;
            }?>
          	</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri11">Actions</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri11">Visa<br>R&eacute;f&eacute;rent</span>
					</td>
          <?php if($GLOBALS['estzrr'])
          {?> <td nowrap align="center"><span class="bleugrascalibri11">FSD</span>
           <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_fsd_<?php echo $etat_individu_val ?>">
           <div class="tooltipContent_cadre" id="info_fsd_<?php echo $etat_individu_val ?>">
            <span class="noircalibri10">
          		La demande FSD n'est pas faite (indiqu&eacute; dans la colonne) si : <br>
              - si la dur&eacute;e du s&eacute;jour est inf&eacute;rieure ou &eacute;gale &agrave; 5 jours<br>
              - ou si l&rsquo;&eacute;tudiant est stagiaire de niveau inf&eacute;rieur &agrave; BAC+5<br>
              - ou si la derni&egrave;re autorisation a moins de 5 ans et les s&eacute;jours contig&uuml;s.<br> 
              Les ic&ocirc;nes de cette colonne varient en fonction des dates, des conditions et des pi&egrave;ces jointes figurant dans la fiche :<br>
							- <b>si</b> le visa FSD est appos&eacute; : <img src="images/b_visa.png"><br>
              - <b>sinon</b><br>
              <?php 
              if(array_key_exists('srh',$tab_roleuser) || $est_admin)
							{?>
              &nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> une date de demande par mail : date de la demande <img src="i/v1.png"><br>
              &nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b><br> 
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> informations manquantes dans le dossier : <img src="images/b_attention.png" align="absbottom"><br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b> : <img src="images/b_mail_fsd.gif" align="absbottom"> (mail pour le FSD)<br>
              <br>
							Les dates de demande et d'autorisation sont renseign&eacute;es automatiquement dans la fiche de l'individu sur envoi du mail ou validation.<br>
              Il appartient au gestionnaire de modifier ces dates dans la fiche s'il le juge n&eacute;cessaire. Par exemple :<br>
              - si le visa n'est pas appos&eacute;, la suppression de la date de demande par mail FSD et de la date d'autorisation permettra de refaire une demande par mail au FSD<br>
              - la date  de demande peut &ecirc;tre saisie si le mail a d&eacute;j&agrave; &eacute;t&eacute; envoy&eacute;<br>
              A noter : si la date d'autorisation est remplie, le syst&egrave;me ne v&eacute;rifie pas que la date d'envoi au FSD est renseign&eacute;e
							<?php 
              }
              else
              { ?>
              &nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> informations manquantes dans le dossier : <img src="images/b_attention.png"><br>
              &nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b><br> 
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>si</b> la demande a &eacute;t&eacute; faite, en attente d'autorisation : date de la demande<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- <b>sinon</b> le gestionnaire FSD du labo. doit constituer le dossier et faire la demande : <img src="i/b1.png"><br>
							<?php 
              }?>
              <br><b>Prolongation/ Renouvellement / Modification :</b><br>
              - le syst&egrave;me indique qu&rsquo;une prolongation doit &ecirc;tre faite par le texte "expire le ..." pour ... dur&eacute;e &eacute;gale &agrave; datefin - datefin pr&eacute;vue (si &gt; 0)<br>
              &nbsp;&nbsp;la couleur est mauve si la date d&rsquo;expiration est comprise entre 2 et 6 mois, rouge entre 0 et 2 mois<br>
              - la demande faite, "(Nelle demande) date de demande" est affich&eacute;
            </span>
          </div>
          <script type="text/javascript">
            var sprytooltip_info_fsd_<?php echo $etat_individu_val ?> = new Spry.Widget.Tooltip("info_fsd_<?php echo $etat_individu_val ?>", "#sprytrigger_info_fsd_<?php echo $etat_individu_val ?>", {offsetX:-100, offsetY:0, closeOnTooltipLeave:true});
          </script>
					</td>
          <?php 
					}?>
					<td nowrap align="center"><span class="bleugrascalibri11">Visa<br>arriv&eacute;e</span>
					</td>
					<!--<td nowrap align="center"><span class="bleugrascalibri11">Visa<br>SRH</span>
					</td>
					<td nowrap align="center"><span class="bleugrascalibri11">Visa<br>DU</span>
					</td> -->
					<!--<td nowrap align="center">
          <span class="bleugrascalibri11">Notes</span><br>
          	<form name="gestionindividus_voir" method="post" action="<?php //echo $_SERVER['PHP_SELF'] ?>">
          		<input type="image"  name="b_ind_voir_col_notes" img class="icon" src="images/b_checked_<?php //echo ($_SESSION['b_ind_voir_col_notes']?"oui":"non") ?>.png">
						</form>
					</td> -->
					<?php 
					if(array_key_exists('srh',$tab_roleuser) || $est_admin)
					{?>
					<td nowrap align="center"><span class="bleugrascalibri11">V&eacute;rif.</span><br>
          	<form name="gestionindividus_voir" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          		<input type="image"  name="b_ind_voir_col_anomalies" src="images/b_checked_<?php echo ($_SESSION['b_ind_voir_col_anomalies']?"oui":"non") ?>.png">
						</form>
					</td> 
          <?php
          }?>
				</tr>
				<?php 	
				$class="even";
				foreach($tab_rs_individu[$etat_individu_val] as $codeindividu=>$row_rs_ind_sejour)
				{	foreach($row_rs_ind_sejour  as $numsejour=>$row_rs_individu)
					{ $numrow++;
						$numsejour=$row_rs_individu['numsejour'];// table des roles et droits de $codeuser et $estreferent+$estresptheme pour cet individu
						$codelibcat=$row_rs_individu['codelibcat'];
						// 20150620
						$codelibtypestage=$row_rs_individu['codelibtypestage'];
						// 20150620
						$tab_resp_roleuser=$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['tab_resp_roleuser'];
						$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
						$estreferent=$tab_resp_roleuser['estreferent'];// user a le role referent mais n'est pas forcément le referent
						$estresptheme=$tab_resp_roleuser['estresptheme'];// user a le role theme mais n'est pas forcément référent : peut etre le gesttheme
	
						// table des statuts visas deja apposes pour cet individu
						$tab_individustatutvisa=get_individu_visas($codeindividu,$numsejour,$tab_statutvisa);
	
						/* contenu pour chaque colonne visa affichee + droit read/write pour chaque role sous la forme :
						// $tab_contenu_col_role_droit['referent']['colonne']='visa appose', 'valider', 'brancher','sablier' ou 'n/a'
						// $tab_contenu_col_role_droit['referent']['droit']='read', 'write'
						*/
						//$ue="non";//car historique des anciennes declarations : $ue=$row_rs_individu['ue'];// 20161213
						foreach($tab_statutvisa as $codelibstatutvisa=>$valsatutvisa)
						{ $tab=contenu_col_role_droit($row_rs_individu,$tab_individustatutvisa,$tab_roleuser,$codelibstatutvisa);// 20161213
							$tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=$tab[$codelibstatutvisa]['colonne'];
							$tab_contenu_col_role_droit[$codelibstatutvisa]['droit']=$tab[$codelibstatutvisa]['droit'];
						} 
						// le droit de $codeuser est fonction du droit par role : le role de $tab_roleuser a un droit vrai 
						// dans $tab_contenu_col_role_droit pour son role le plus 'élevé'
						$droitmodif="read";//pas de modif/suppr par defaut
						foreach($tab_roleuser as $keyroleuser=>$valroleuser)
						{ //if($tab_contenu_col_role_droit[$keyroleuser]['droit']=="write")
							//modifié pour deverrouiller accs gesttheme
							if($tab_contenu_col_role_droit[$keyroleuser]['droit']=="write")
							{ $droitmodif="write";
							}
							// 20161213
							/* else
							{ break;
							} */
						}
						/* if($estgesttheme)// 20161213
						{ $droitmodif="write";
						} */
						$ligne='';
						// edit fiche complete ou partielle
						// 20161213
						if($droitmodif=="write" || array_key_exists('srh',$tab_roleuser) || $est_admin)
						{ $onDoubleClick='onDblClick="e(\''.$codeindividu.'.'.$numsejour.'\',\''.$etat_individu_val.'\')"';
						}
						else if($estgesttheme)
						{ $onDoubleClick='onDblClick="ep(\''.$codeindividu.'.'.$numsejour.'\',\''.$etat_individu_val.'\')"';
						}
						else
						{ $onDoubleClick='';
						}
						$ligne.='<tr class="'.(($ind_ancre==$codeindividu.'.'.$numsejour)?'marked':($class=="even"?$class="odd":$class="even")).
										'" id="t'.$numrow.'" onClick="m(this)" '.$onDoubleClick.'>';
						//$ligne.='<tr class="'.($class=="even"?$class="odd":$class="even").'">';
						$ligne.='<td><a name="'.$codeindividu.'.'.$numsejour.'">'.$codeindividu.'.'.$numsejour.'</a>';
						$ligne.='</td>';
						$ligne.='<td nowrap>'.($row_rs_individu['email']==''?'<img src="images/espaceur.gif" width="10">':'<a href="mailto:'.$row_rs_individu['email'].'"><img src="images/b_mail.gif" width="10"></a>').'&nbsp;'.htmlspecialchars($row_rs_individu['nom'].' '.substr($row_rs_individu['prenom'],0,1)).'.';
						$ligne.='</td>';
						$ligne.='<td nowrap>';
						$ligne.=htmlspecialchars($row_rs_individu['libcat_fr']);
						if($row_rs_individu['codelibcat']=='DOCTORANT')
						{ if($etat_individu_val!='preaccueil')
							{ $span='';
								$finspan='';
								if($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['annee_these']=='ABANDON')
								{ $span='<span class="rougecalibri10">';
									$finspan='</span>';
								}
								$ligne.=' '.$span.$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['annee_these'].$finspan;
								//$ligne.=' ('.htmlspecialchars($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['libed_these']).')';
							}
						}
						if($row_rs_individu['codelibcat']=='STAGIAIRE')
						{ // 20150620	
							$ligne.=' ('.htmlspecialchars(strtolower($row_rs_individu['libtypestage'])).')';//codelibtypestage
							// 20150620	
						}
						if($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['associe'])
						{ $ligne.=" Associ&eacute;";
						}
						/* if(isset($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['libed']))
						{ $ligne.=" (".htmlspecialchars($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['libed']).")"; 
						} */
						$ligne.='</td>';
						if($GLOBALS['gestionindividus_col_sujet'])
						{ $ligne.='<td nowrap>';
							$ligne.=($row_rs_individu['codesujet']!=""?'<a href="javascript:OV(\'S'.$row_rs_individu['codesujet'].'\')"><img src="i/o.png"></a>'.htmlspecialchars(substr($row_rs_individu['titresujet_fr'],0,30)):'');
							$ligne.='</td>';
						}
						if($GLOBALS['gestionindividus_col_theme'])
						{ $ligne.='<td>';
							$firsttheme=true;
							foreach($tab_individutheme[$codeindividu][$numsejour]["theme"] as $codetheme => $libtheme)
							{ $ligne.=($firsttheme?"":" ").str_replace(" ","&nbsp;",$libtheme);
								$firsttheme=false;
							}
							$ligne.='</td>';
						}
						$ligne.='<td nowrap>'.htmlspecialchars(substr($row_rs_individu['referentprenom'],0,1).'. '.$row_rs_individu['referentnom']).'<img src="i/z.gif" width="5">'.($row_rs_individu['referentemail']==''?'':'<a href="mailto:'.$row_rs_individu['referentemail'].'"><img src="images/b_mail.gif" width="10"></a>');
						$ligne.='</td>';
						$ligne.='<td nowrap>'.htmlspecialchars(substr($row_rs_individu['gestthemeprenom'],0,1).'. '.$row_rs_individu['gestthemenom']);
						$ligne.='</td>';
						$ligne.='<td>';
						switch ($etat_individu_val)
						{ case 'preaccueil' : 							$ligne.=aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/");break;
							case 'accueil' : 									$ligne.=aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/");break;
							case 'present' :									$ligne.=aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/");break;
							case 'parti' :										$ligne.=aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour'],"/");break;
							case 'sejourpartinonvalide' :			$ligne.=aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/");break;
							case 'anomalie' :									$ligne.=aaaammjj2jjmmaaaa($row_rs_individu['datedeb_sejour'],"/");break;
						}
						$ligne.='</td>';
						$ligne.='<td>';
						$ligne.='<table>';
						$ligne.='<tr>';
						$ligne.='<td>';
						if($ind_ancre==$codeindividu.'.'.$numsejour)
						{ $ligne.='<img src="images/b_fleche_haut.png" onClick="window.location.hash=\'haut_page\'">';
						}
						$ligne.='<a href="javascript:OV(\'I'.$codeindividu.$numsejour.'\')">';
						$note_a_afficher="";
						if(array_key_exists('srh',$tab_roleuser) || $estgesttheme || $est_admin)
						{ $note_a_afficher=($row_rs_individu['note'].$row_rs_individu['postit']==''?'':'p');
						}
						$ligne.='<img src="i/o'.$note_a_afficher.'.png" id="note'.$row_rs_individu['codeindividu'].$row_rs_individu['numsejour'].'"'.
										($note_a_afficher==''?'':' onMouseOver="affiche_note(this,\'note\',\''.$row_rs_individu['codeindividu'].$row_rs_individu['numsejour'].'\',event)" onMouseOut="cache_note()"').'>';
						$ligne.='</a>';
						$ligne.='</td>';
						/* $ligne.='<div id="note'.$row_rs_individu['codeindividu'].$row_rs_individu['numsejour'].' class="cache">';
						$ligne.='</div>'; */
						$ligne.='<td>';
						if(	$tab_rs_individu[$etat_individu_val][$codeindividu][$row_rs_individu['numsejour']]['listersejour'] &&!$_SESSION['b_lister_sejours'])
						{ $ligne.='<form name="form_fiche_dossier_pers_modif'.$codeindividu.$numsejour.'" method="post" action="'.$_SERVER['PHP_SELF'].'">';
							$ligne.='<input type="hidden" name="MM_update" value="'.$_SERVER['PHP_SELF'].'">';
							$ligne.='<input type="hidden" name="codeindividu" value="'.$codeindividu.'">';
							$ligne.='<input type="hidden" name="action" value="lister_sejours">';
							$ligne.='<input type="image" name="b_lister_sejours" src="i/lp1.png"  title="Liste s&eacute;jours"/>';
							$ligne.='</form>';
						}
						else
						{ $ligne.='<img src="i/z1.png">';
						}
						$ligne.='</td>';
						// ajout d'un sejour si (present ou parti) et pas en preaccueil et visa du
						$ligne.='<td>';
						$preaccueilli=false;
						if(isset($tab_rs_individu['preaccueil']))
						{ $preaccueilli=array_key_exists($codeindividu,$tab_rs_individu['preaccueil']) || (isset($tab_rs_individu['sejourpartinonvalide']) && array_key_exists($codeindividu,$tab_rs_individu['sejourpartinonvalide']));
						}
						// modif 20160426
						// 20161213 if($est_admin || array_key_exists('theme',$tab_roleuser) || array_key_exists('srh',$tab_roleuser))
						// modif 20160426{	}
						if($row_rs_individu['datefin_sejour']!='')
						{ $ligne.='<form name="form_fiche_dossier_pers_choix_corps_'.$codeindividu.'" method="post" action="edit_individu.php">';
							$ligne.='<input type="hidden" name="codeindividu" value="'.$codeindividu.'">';
							$ligne.='<input type="hidden" name="numsejour_leplusrecent" value="'.$numsejour.'">';
							$ligne.='<input type="hidden" name="permanent" value="'.($row_rs_individu['codestatutpers']=='01'?'oui':'non').'">';
							$ligne.='<input type="hidden" name="etat_individu" value="preaccueil">';
							$ligne.='<input type="hidden" name="action" value="ajouter_sejour">';
							$ligne.='<input type="image" name="submit_ajouter_sejour" src="i/n1.png" title="Nouveau s&eacute;jour" onClick="return confirme(\'nouveau séjour\',\'Ajouter un nouveau séjour ?\')">';
							$ligne.='</form>'; 
						}
						else
						{ $ligne.='<img src="i/ng1.png" title="Manque date fin">';
						}
						
						// modif 20161213
						/* else
						{ $ligne.='<img src="i/z1.png">';
						} */
						$ligne.='</td>';
						// si droit write ou ((present ou parti) et role srh ou admin coché), affichage des images b_edit et b_drop (si supprsejour)
						$ligne.='<td>';
						if($droitmodif=="write" || array_key_exists('srh',$tab_roleuser) || $est_admin)
						{ $ligne.='<img src="i/m1.png" onClick="e(\''.$codeindividu.'.'.$numsejour.'\',\''.$etat_individu_val.'\')" title="Modifier"/>';
							$ligne.='</td>';
							$ligne.='<td>';
							if(($etat_individu_val=='present' || $etat_individu_val=='parti' || $etat_individu_val=='sejourpartinonvalide') && !$est_admin)
							{ $ligne.='<img src="i/z1.png">';
							}
							else
							{ if($tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour'])
								{ $ligne.='<img src="i/d1.png" onClick="cai(\''.$codeindividu.'.'.$numsejour.'\',\'supprimer\',\'\')">';
								}
								else if($est_admin && $tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['supprsejour_par_admin'])
								{ $ligne.='<img src="i/d1.png" onClick="cai(\''.$codeindividu.'.'.$numsejour.'\',\'supprimer\',\'\')"';
								 	if(isset($tab_sejournonsuppr[$codeindividu]['explication']))
									{	$ligne.=' onMouseOver="affiche_note(this,\'nonsuppr\',\''.$row_rs_individu['codeindividu'].'\',event)" onMouseOut="cache_note()"';
									}
									$ligne.='>';
								}
								else
								{ $ligne.='<img src="i/dn.png" onMouseOver="affiche_note(this,\'nonsuppr\',\''.$row_rs_individu['codeindividu'].'\',event)" onMouseOut="cache_note()">';
									//$ligne.='<img src="i/z1.png">';
								}
							}
						} 
						else if($estgesttheme)
						{ $ligne.='<img src="i/mp1.png" onClick="ep(\''.$codeindividu.'.'.$numsejour.'\',\''.$etat_individu_val.'\')" title="Modifier"/>';
							$ligne.='</td>';
							$ligne.='<td>';
							$ligne.='<img src="i/z1.png">';
						}
						$ligne.='</td>';
						if($est_admin && $row_rs_individu['codenat']!='079')//manque pj resp_civile ou conv_accueil
						{	$ligne.='<td>';
							if(($row_rs_individu['resp_civile_inutile']!='oui' && !isset($tab_individu_pj[$codeindividu][$numsejour]['resp_civile']))
									|| ($row_rs_individu['conv_accueil_inutile']!='oui' && !isset($tab_individu_pj[$codeindividu][$numsejour]['conv_accueil'])))
							{ $ligne.='<img src="images/b_manque_pj.png"  onMouseOver="affiche_note(this,\'manque_pj\',\''.$row_rs_individu['codeindividu'].$row_rs_individu['numsejour'].'\',event)" onMouseOut="cache_note()">';
							}
							$ligne.='</td>';
						}

						$ligne.='</tr>';
						$ligne.='</table>';
						$ligne.='</td>';
						// affichage pour chaque colonne  visa referent, visa srhue,... d'une image b_visa, b_valider,...
						foreach($tab_statutvisa as $codelibstatutvisa=>$codestatutvisa)//'referent'=>'01',...
						{ if(!($codelibstatutvisa=='du' || $codelibstatutvisa=='srh' || ($codelibstatutvisa=='srhue' && !$GLOBALS['estzrr'])))//plus de colonne srh et du
							{ if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']!="")// colonne="" si le visa/role n'est pas une colonne affichee, n/a
								{ $tab_sujet_saisi_valide=sujet_saisi_valide($row_rs_individu,$codelibstatutvisa);// toujours vrai si srhue !!!!!
									$row_rs_individu['texte_attente_sujet']=$tab_sujet_saisi_valide['texte_attente_sujet'];

									// Ajout d'un texte informatif sous visa (ou pourquoi pas de demande fsd) pour prolongation necessaire ou demande modification
									$texte_html_prolongation_modification_fsd='';
									if($codelibstatutvisa=='srhue' && ($row_rs_individu['prolongation_fsd'] || $row_rs_individu['date_demande_modification_fsd']!='')/* && ( array_key_exists('srh',$tab_roleuser) ||  $est_admin)*/)
									{ //$ligne.=$texte_duree_prolongation_fsd;
										// prolongation >= 2 mois
										if($row_rs_individu['date_demande_modification_fsd']=='')
										{	$texte_duree_prolongation_fsd=($row_rs_individu['tab_duree_prolongation_fsd']['a']==0?'':$row_rs_individu['tab_duree_prolongation_fsd']['a'].'a').($row_rs_individu['tab_duree_prolongation_fsd']['m']==0?'':$row_rs_individu['tab_duree_prolongation_fsd']['m'].'m').($row_rs_individu['tab_duree_prolongation_fsd']['j']==0?'':$row_rs_individu['tab_duree_prolongation_fsd']['j'].'j');
											$styletexte_fsd="";
											if($row_rs_individu['tab_duree_avant_expire_fsd']['a']==0 && $row_rs_individu['tab_duree_avant_expire_fsd']['m']<=6)
											{ $texte_html_prolongation_modification_fsd.='<table align="right"><tr>';
												if($row_rs_individu['tab_duree_avant_expire_fsd']['m']<=2)
												{ $styletexte_fsd="rougecalibri10";
												}
												else
												{ $styletexte_fsd="mauvecalibri10";
												}
												$texte_html_prolongation_modification_fsd.='<td nowrap>';
												if($row_rs_individu['tab_duree_avant_expire_fsd']['m']!=0 || $row_rs_individu['tab_duree_avant_expire_fsd']['j']!=0)
												{ $texte_html_prolongation_modification_fsd.='Expire le : <span class="'.$styletexte_fsd.'">'.aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour_prevu'],'/').'</span> pour <span class="'.$styletexte_fsd.'">'.$texte_duree_prolongation_fsd.'</span>';
												}
												else
												{ $texte_html_prolongation_modification_fsd.='<span class="rougecalibri10">Expir&eacute; le '.aaaammjj2jjmmaaaa($row_rs_individu['datefin_sejour_prevu'],'/').'</span> pour <span class="rougecalibri10">'.$texte_duree_prolongation_fsd.'</span>';
												}
												$texte_html_prolongation_modification_fsd.='</td><td>';
												//$ligne.='<img src="images/b_mail_fsd.gif" align="right" onClick="fsd(\''.$codeindividu.'.'.$numsejour.'\',\'P\')">';
												$texte_html_prolongation_modification_fsd.='</td></tr></table>';
											}
										}
										else
										{ $texte_html_prolongation_modification_fsd.='<table cellpadding="0" cellspacing="0"><tr><td style="font-family:Calibri; color:#FD7C09; font-weight:bold; font-size: 10pt; ">(Nelle demande) '.aaaammjj2jjmmaaaa($row_rs_individu['date_demande_modification_fsd'],'/').'</td></tr></table>';
										}
									}
									// fin Ajout d'un texte informatif sous visa 
									$ligne.='<td nowrap align="center">';
									if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='visa appose' )
									{ $ligne.='<img src="i/y1.png">'.$texte_html_prolongation_modification_fsd;
									}
									// si colonne visa a valider pour ce sejour ou (brancher et est_admin et visa != srh et !=srhue)
									else if(($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='valider') || ($est_admin && $tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='brancher'/*  && $codelibstatutvisa!='srhue' && $codelibstatutvisa!='srh' */))
									{ // 20161213
										/* $tab_sujet_saisi_valide=sujet_saisi_valide($row_rs_individu,$codelibstatutvisa);// toujours vrai si srhue !!!!!
										$row_rs_individu['texte_attente_sujet']=$tab_sujet_saisi_valide['texte_attente_sujet']; */
										// 20161213
										if($tab_sujet_saisi_valide['sujet_saisi_valide'])//$tab_sujet_saisi_valide['sujet_saisi_valide'] toujours vrai si srhue
										{	if($codelibstatutvisa=='srhue')// (visa srhue = visa fsd) 
											{	if($row_rs_individu['demander_autorisation'])
												{	if($row_rs_individu['date_demande_fsd']=='')// msgfsd pas envoye
													{	$ligne.='<table cellpadding="0" cellspacing="0"><tr>';
														$ligne.='<td>';
														if(isset($row_rs_individu['manque_info_fsd']) && count($row_rs_individu['manque_info_fsd'])>0)
														{ $ligne.='<img src="images/b_attention.png"  onMouseOver="affiche_note(this,\'manque_info_fsd\',\''.$row_rs_individu['codeindividu'].$row_rs_individu['numsejour'].'\',event)" onMouseOut="cache_note()">';
														}
														else /* if(isset($tab_individu_fsd[$codeindividu][$numsejour]['fsd']) && isset($tab_individu_fsd[$codeindividu][$numsejour]['cv'])) */
														{ if($est_admin)
															{ $ligne.='<form name="gestionindividus_valider_'.$codeindividu.'_'.$numsejour.'_traite_srh" method="post" action="'.$_SERVER['PHP_SELF'].'">';
																$ligne.='<input type="hidden" name="MM_update" value="'.$_SERVER['PHP_SELF'].'">';
																$ligne.='<input type="hidden" name="codeindividu" value="'.$codeindividu.'">';
																$ligne.='<input type="hidden" name="numsejour" value="'.$numsejour.'">';
																$ligne.='<input type="hidden" name="action" value="traite_srh">';
																$ligne.='<input type="image" name="b_traite_srh_'.($row_rs_individu['traite_srh']=='oui'?'oui':'non').
																				'" src="images/b_traite_'.($row_rs_individu['traite_srh']=='oui'?'oui':'non').'.png">';
																$ligne.='</form>';
															}
															else
															{ $ligne.='<img src="images/b_traite_'.($row_rs_individu['traite_srh']=='oui'?'oui':'non').'.png">';
															}
															if($row_rs_individu['traite_srh']=='oui' || $est_admin)
															{ $ligne.='<img src="images/b_mail_fsd.gif" onClick="fsd(\''.$codeindividu.'.'.$numsejour.'\',\'D\')">';
															}
														}
														/* else
														{ $ligne.='<img src="images/espaceur.gif" width="30" height="0">';
														} */
														$ligne.='</td>';
														$ligne.='</tr></table>';
													}
													else
													{ $ligne.='<table cellpadding="0" cellspacing="0"><tr><td style="font-family:Calibri; color:#FD7C09; font-weight:bold; font-size: 10pt; ">'.aaaammjj2jjmmaaaa($row_rs_individu['date_demande_fsd'],'/').'</td>';
														$ligne.='<td><form name="confirmer_mail_validation_autorisation_fsd_'.$codeindividu.'_'.$numsejour.'_'.$codestatutvisa.'" method="post" action="confirmer_mail_validation_autorisation_fsd.php">';
														$ligne.='<input type="hidden" name="codeindividu" value="'.$codeindividu.'">';
														$ligne.='<input type="hidden" name="numsejour" value="'.$numsejour.'">';
														$ligne.='<input type="hidden" name="action" value="valider">';
														$ligne.='<input type="hidden" name="codevisa_a_apposer" value="'.$codestatutvisa.'">';
														$ligne.='<input type="hidden" name="demander_autorisation" value="'.($row_rs_individu['demander_autorisation']?'oui':'non').'">';
														$ligne.='<input type="hidden" name="ind_ancre" value="'.$codeindividu.'.'.$numsejour.'">';
														$ligne.='<input name="b_valider" type="image" src="i/v1.png">';
														$ligne.='</form></td></tr></table>';
													}
												}
												else
												{ $ligne.='<span class="vertfoncegrascalibri11">'.$row_rs_individu['pourquoi_pas_de_demande_fsd'].'</span>'.$texte_html_prolongation_modification_fsd;//'<img src="i/z1.png">'
												}
											}
											else // visa referent ou theme
											{ if($codelibstatutvisa=='theme' && $etat_individu_val=='preaccueil')
												{ $ligne.='<img src="i/z.gif">';
												}
												else
												{ $ligne.='<form name="gestionindividus_valider_'.$codeindividu.'_'.$numsejour.'_'.$codestatutvisa.'" method="post" action="'.$_SERVER['PHP_SELF'].'">';
													$ligne.='<input type="hidden" name="MM_update" value="'.$_SERVER['PHP_SELF'].'">';
													$ligne.='<input type="hidden" name="codeindividu" value="'.$codeindividu.'">';
													$ligne.='<input type="hidden" name="numsejour" value="'.$numsejour.'">';
													$ligne.='<input type="hidden" name="action" value="valider">';
													$ligne.='<input type="hidden" name="codevisa_a_apposer" value="'.$codestatutvisa.'">';
													$ligne.='<input type="hidden" name="demander_autorisation" value="'.($row_rs_individu['demander_autorisation']?'oui':'non').'">';
													$ligne.='<input type="hidden" name="ind_ancre" value="'.$codeindividu.'.'.$numsejour.'">';
													$ligne.='<input name="b_valider" type="image" src="i/v1.png" title="Valider" onClick="return confirme(\'valider\',\'Valider ?\')">';
													$ligne.='</form>';
												}
											}
										}
										else
										{ $ligne.='<img src="images/b_attente_validation_sujet.png" title="'.$row_rs_individu['texte_attente_sujet'].'">';
										}
									}
									else if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='brancher')
									{ if($codelibstatutvisa=='srhue')
									 	{ if(!$row_rs_individu['demander_autorisation'])
											{ $ligne.='<span class="vertfoncegrascalibri11">'.$row_rs_individu['pourquoi_pas_de_demande_fsd'].'</span>'.$texte_html_prolongation_modification_fsd;//'<img src="i/z1.png">'
											}
											else if($row_rs_individu['date_demande_fsd']!='')
											{ $ligne.='<span style="font-family:Calibri; color:#FD7C09; font-weight:bold; font-size: 10pt; ">'.aaaammjj2jjmmaaaa($row_rs_individu['date_demande_fsd'],"/").'</span>';//'<img src="i/z1.png">'
											}
											else if($row_rs_individu['date_demande_fsd']=='')
											{ /* if(!isset($tab_individu_fsd[$codeindividu][$numsejour]['cv']) || isset($row_rs_individu['manque_info_fsd']))
												{ if(!isset($tab_individu_fsd[$codeindividu][$numsejour]['cv']))
													{ $ligne.='<img src="images/b_attente_cv.png"></a>';
													} */
												if(isset($row_rs_individu['manque_info_fsd']))
												{ $ligne.='<img src="images/b_attention.png"  onMouseOver="affiche_note(this,\'manque_info_fsd\',\''.$row_rs_individu['codeindividu'].$row_rs_individu['numsejour'].'\',event)" onMouseOut="cache_note()">';
												}
												/* } */
												else
												{ $ligne.='<img src="i/b1.png">';
												}
											}
										}
										else if($codelibstatutvisa=='theme' && $etat_individu_val=='preaccueil')
										{ $ligne.='<img src="i/z.gif">';
										}
										else
										{ $ligne.='<img src="i/b1.png">';
										}
									}
									else if($tab_contenu_col_role_droit[$codelibstatutvisa]['colonne']=='sablier')
									{ if($codelibstatutvisa=='srhue' && !$row_rs_individu['demander_autorisation'])
										{  $ligne.='<span class="vertfoncegrascalibri11">'.$row_rs_individu['pourquoi_pas_de_demande_fsd'].'</span>'.$texte_html_prolongation_modification_fsd;//'<img src="i/z1.png">'
										}
										else if($codelibstatutvisa=='theme' && $etat_individu_val=='preaccueil')
										{ $ligne.='<img src="i/z.gif">';
										}
										else
										{ $ligne.='<img src="i/s1.png">';
										}
									}
									$ligne.='</td>';
								}//fin if
							}
						}//fin foreach statutvisa
						/* $ligne.='<td nowrap>';
						if($_SESSION['b_ind_voir_col_notes']=='oui')
						{ if($row_rs_individu['note']!=''){$ligne.='<span class="bleucalibri10">'.htmlspecialchars($row_rs_individu['note']).'<br></span>';  }
							if($row_rs_individu['postit']!=''){$ligne.='<span class="grisfoncecalibri10">Postit&nbsp;('.htmlspecialchars($tab_infouser['prenom']).')&nbsp;:<br></span><span class="bleucalibri10">'.htmlspecialchars($row_rs_individu['postit']).'</span>'; }
						}
						$ligne.='</td>'; */
						if($est_admin || array_key_exists('srh',$tab_roleuser))
						{ $ligne.='<td nowrap class="mauvecalibri10">';
							if($_SESSION['b_ind_voir_col_anomalies']=='oui')
							{ $ligne.=$tab_rs_individu[$etat_individu_val][$codeindividu][$numsejour]['defaut_sejour'];
							}	
							$ligne.='</td>';
						} 
						$ligne.='</tr>';
						if($_SESSION['b_lister_sejours'])
						{ $ligne.='<tr>';
							$ligne.='<td colspan="'.$nb_col_a_afficher.'">';
							$ligne.=detailindividu($codeindividu,$numsejour,$codeuser);
							$ligne.='</td>';
							$ligne.='</tr>';
						}
						echo $ligne.chr(13);
					}//fin for
				}// fin if
			}// fin ligne d'un dossier individu
		}// fin foreach etatindividu
/* echo "duree : ".((microtime()-$timedeb))." sec<br>"; */ 
?>
			</table><!-- table17-->
		</td>
	</tr>
</table><!-- table18-->
</body>
</html>
<?php /* echo '<br>Dur&eacute;e du traitement : '.sprintf('%01.2f',round((microtime(true)-$temps_debut),2)).' secondes';
echo '<br>Nb car lignes envoyes : '.$nbtotalcarligne;
 */?>