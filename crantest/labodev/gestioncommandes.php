<?php require_once('_const_fonc.php'); ?>
<?php
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$admin_bd=(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
/* $timedeb=microtime();echo '<br>'.$timedeb; 
if($admin_bd)*/
{ /* foreach($_POST as $key=>$val)
	{ echo $key.'=>'.$val.'<br>';
	} */
}
$nbtotalcarligne=0;//nb car des lignes envoyees
$temps_debut=microtime(true);
$afficheduree=false;//true;
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timedeb=microtime(true);
	$timedeb_avanthtml=$timedeb;
 	//echo 'usage memoire '.memory_get_usage ();
} 
$erreur="";
$msg_erreur_objet_mail="";
$erreur_envoimail="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";
$nb_col_a_afficher="17";// +1 si colonne 'invalider'
$form_gestioncommandes_voir="gestioncommandes_voir";
$mini=false;//taille de la version affichee sera egale a $_SESSION['b_cmd_version_mini']
//PG 20151031
$tab_contexte['prog']='gestioncommandes';
//PG 20151031
$tab_rs_commande=array();
$tab_rs_etat_commande=array();//table des commandes classees par etat
$tab_etat_commande=array('demande','engage','servicefait','paye','annule');//etats possibles (preaccueil, ...
$tab_etat_commande_entete=array(
						'demande'=>'<span class="rougegrascalibri10">Demande </span><span class="mauvecalibri10"> ((Commandes (et mission/commandes associ&eacute;es) dont 1er visa IEB non appos&eacute; ou n&deg; commande non saisi / Mission sans commande non encore effectu&eacute;e))</span>',
						'engage'=>'<span class="rougegrascalibri10">Engag&eacute;</span><span class="mauvecalibri10"> (Commande (et mission/commandes associ&eacute;es) dont 1er visa IEB appos&eacute; et n&deg; commande saisi)</span>',
						'servicefait'=>'<span class="rougegrascalibri10">Service fait</span><span class="mauvecalibri10"> (Commande (et mission/commandes associ&eacute;es) dont visa secr. appos&eacute; et Service fait (MIGO))</span>',
						'paye'=>'<span class="rougegrascalibri10">Pay&eacute;</span><span class="mauvecalibri10"> (Commande (et mission/commandes associ&eacute;es) dont date de paiement renseign&eacute;e / Mission sans commande effectu&eacute;e) </span>',
						'annule'=>'<span class="rougegrascalibri10">Annul&eacute;</span>');
foreach($tab_etat_commande as $etat_commande=>$etat_commande_val)
{ $tab_rs_etat_commande[$etat_commande_val]=array();
}
$tab_cmd_champrecherche=array(''=>'[Champ]','numinterne'=>'N&deg; interne','referent'=>'Demandeur nom pr&eacute;nom','numcommande'=>'N&deg; commande','libfournisseur'=>'Fournisseur','numfournisseur'=>'N&deg; Fournisseur','objet'=>'Objet');
$tab_mission=array();
$cmd_ou_miss=isset($_GET['cmd_ou_miss'])?$_GET['cmd_ou_miss']:(isset($_POST['cmd_ou_miss'])?$_POST['cmd_ou_miss']:"");
$codecommande=isset($_GET['codecommande'])?$_GET['codecommande']:(isset($_POST['codecommande'])?$_POST['codecommande']:"");
$codemission=isset($_GET['codemission'])?$_GET['codemission']:(isset($_POST['codemission'])?$_POST['codemission']:"");
$estannule=isset($_GET['estannule'])?$_GET['estannule']:(isset($_POST['estannule'])?$_POST['estannule']:"");
$estavoir=isset($_GET['estavoir'])?$_GET['estavoir']:(isset($_POST['estavoir'])?$_POST['estavoir']:"");
$traite_ieb=isset($_GET['traite_ieb'])?$_GET['traite_ieb']:(isset($_POST['traite_ieb'])?$_POST['traite_ieb']:"");
// 20170412
$estinventorie=isset($_GET['estinventorie'])?$_GET['estinventorie']:(isset($_POST['estinventorie'])?$_POST['estinventorie']:"");
$bloc_cmd_ancre=isset($_GET['bloc_cmd_ancre'])?$_GET['bloc_cmd_ancre']:(isset($_POST['bloc_cmd_ancre'])?$_POST['bloc_cmd_ancre']:"");
$cmd_ancre=isset($_GET['cmd_ancre'])?$_GET['cmd_ancre']:(isset($_POST['cmd_ancre'])?$_POST['cmd_ancre']:$bloc_cmd_ancre);
if($cmd_ancre=="")
{ $cmd_ancre=$bloc_cmd_ancre;
}
$codevisa_a_apposer=isset($_GET['codevisa_a_apposer'])?$_GET['codevisa_a_apposer']:(isset($_POST['codevisa_a_apposer'])?$_POST['codevisa_a_apposer']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$envoyer_mail_srh=isset($_GET['envoyer_mail_srh'])?true:(isset($_POST['envoyer_mail_srh'])?true:false);
$traite_assurance=isset($_GET['traite_assurance'])?$_GET['traite_assurance']:(isset($_POST['traite_assurance'])?$_POST['traite_assurance']:"");
$info_comp_relance_missionnaire=isset($_POST['info_comp_relance_missionnaire'])?$_POST['info_comp_relance_missionnaire']:"";
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_cmd_statutvisa et est "titulaire de ce role" ou "suppléant"
// définis par $estreferent et $estresptheme
$tab_cmd_statutvisa=get_cmd_statutvisa();// liste de tous les visas (roles)
$tab_cmd_statutvisa_texte_visa_title=get_cmd_statutvisa_texte_visa_title();//titres a mettre pour les images de visas title=""
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
$estrespcontrat=false;
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_cmd_roleuser($codeuser,'',$tab_cmd_statutvisa,$estreferent,$estresptheme,$estrespcontrat);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$estrespcontrat=$tab_resp_roleuser['estrespcontrat'];
$peut_etre_admin=estrole('du',$tab_roleuser) || estrole('admingestfin',$tab_roleuser);

if(!isset($_SESSION['b_cmd_version_mini'])) { $_SESSION['b_cmd_version_mini']=estrole('sif',$tab_roleuser);}
if(!isset($_SESSION['b_cmd_voir_pour_codesecrsite'])) { $_SESSION['b_cmd_voir_pour_codesecrsite']='';}
if(!isset($_SESSION['codecommande'])) { $_SESSION['codecommande']=$codecommande;}

// b_voir_.... : si ce bouton submit est envoye on shifte l'affichage
if(!isset($_SESSION['b_cmd_etre_admin'])) { $_SESSION['b_cmd_etre_admin']=estrole('sif',$tab_roleuser) ;}
if(!isset($_SESSION['b_cmd_voir_commandes'])) { $_SESSION['b_cmd_voir_commandes']=true;}
if(!isset($_SESSION['b_cmd_voir_missions'])) { $_SESSION['b_cmd_voir_missions']=true;}
// par defaut tout le monde voit credits ul, sauf gestcnrs qui voit cnrs
if(!isset($_SESSION['b_cmd_voir_typecredit_ul'])) { $_SESSION['b_cmd_voir_typecredit_ul']=true;}
if(!isset($_SESSION['b_cmd_voir_typecredit_cnrs'])) { $_SESSION['b_cmd_voir_typecredit_cnrs']=true;}
if(!isset($_SESSION['b_cmd_contrat'])) { $_SESSION['b_cmd_contrat']="";}
if(!isset($_SESSION['b_cmd_voir_avoirs_seuls'])) { $_SESSION['b_cmd_voir_avoirs_seuls']=false;}

if(!isset($_SESSION['b_cmd_petite_taille_colonnes'])){ $_SESSION['b_cmd_petite_taille_colonnes']=true;}
if(!isset($_SESSION['b_cmd_detail'])){ $_SESSION['b_cmd_detail']=false;}
// 20170412
if(!isset($_SESSION['b_cmd_voir_equipement_seul'])){ $_SESSION['b_cmd_voir_equipement_seul']=false;}
//colonnes a afficher ou non : valeurs par defaut
// affichage colonnes
if(!isset($_SESSION['b_cmd_voir_col#']))
{ $_SESSION['b_cmd_voir_col#']=array('referent'=>array('libcourt'=>'Dem.','liblong'=>'Demandeur','affiche'=>true),
																	'numcommande'=>array('libcourt'=>'N&deg;<br>C/M','liblong'=>'N&deg; commande<br>ou mission','affiche'=>true),
																	'objet'=>array('libcourt'=>'Objet - Motif','liblong'=>'Objet - Motif','affiche'=>true),
																	'nature'=>array('libcourt'=>'Nat','liblong'=>'Nature','affiche'=>true),
																	'libfournisseur'=>array('libcourt'=>'Four','liblong'=>'Fournisseur','affiche'=>true),
																	'imputation'=>array('libcourt'=>'Imp','liblong'=>'Imputation<br>budg&eacute;taire','affiche'=>true),
																	'migo'=>array('libcourt'=>'MIGO','liblong'=>'MIGO-liquidation','affiche'=>true),
																	'notes'=>array('libcourt'=>'Notes','liblong'=>'Notes','affiche'=>false));
}
if(!isset($_SESSION['b_cmd_voir_col#notes'])){ $_SESSION['b_cmd_voir_col#notes']=false;}
if(!isset($_SESSION['b_cmd_voir_col#referent'])){ $_SESSION['b_cmd_voir_col#referent']=true;}
if(!isset($_SESSION['b_cmd_voir_col#migo'])){ $_SESSION['b_cmd_voir_col#migo']=true;}
if(!isset($_SESSION['b_cmd_voir_col#justification'])){ $_SESSION['b_cmd_voir_col#justification']=false;}
if(!isset($_SESSION['b_cmd_voir_col_invalider'])){ $_SESSION['b_cmd_voir_col_invalider']=true;}
if(!isset($_SESSION['cmd_champrecherche'])){ $_SESSION['cmd_champrecherche']='';}
if(!isset($_SESSION['cmd_texterecherche'])){ $_SESSION['cmd_texterecherche']='';}
// recherche par contrat, eotp
if(!isset($_SESSION['cmd_contrat_ou_eotp_recherche_radio'])){ $_SESSION['cmd_contrat_ou_eotp_recherche_radio']='contrat';}
//modif rsa--lddir => code -1 pour aucun (''=rsa--lddir)
//if(!isset($_SESSION['cmd_contrat_ou_eotp_recherche'])){ $_SESSION['cmd_contrat_ou_eotp_recherche']='';}
if(!isset($_SESSION['cmd_contrat_ou_eotp_recherche'])){ $_SESSION['cmd_contrat_ou_eotp_recherche']='-1';}

if(!isset($_SESSION['b_cmd_champ_tri'])){ $_SESSION['b_cmd_champ_tri']='codecommande';}
if(!isset($_SESSION['b_cmd_ordre_tri'])){ $_SESSION['b_cmd_ordre_tri']='desc';}

// commandes par etat
foreach($tab_etat_commande as $etat_commande_val)
{ if(!isset($_SESSION['b_cmd_voir_'.$etat_commande_val])){ $_SESSION['b_cmd_voir_'.$etat_commande_val]=true;}
}
// Plusieurs formulaires de choix peuvent etre envoyes : on ne prend en compte que les parametres lies a chaque formulaire
if(isset($_POST['nomformulaire']))
{ $nomformulaire=$_POST['nomformulaire'];
	if($nomformulaire=='gestioncommandes_admin')
	{ $_SESSION['b_cmd_etre_admin']=isset($_POST['b_cmd_etre_admin_x'])? !$_SESSION['b_cmd_etre_admin']:$_SESSION['b_cmd_etre_admin'];
	}
	else if($nomformulaire=='gestioncommandes_voir')
	{ $b_cmd_voir=$_SESSION['b_cmd_voir_commandes'];
		$_SESSION['b_cmd_version_mini']=isset($_POST['b_cmd_version_mini_x'])? !$_SESSION['b_cmd_version_mini']:$_SESSION['b_cmd_version_mini'];
		$_SESSION['b_cmd_voir_commandes']=isset($_POST['b_cmd_voir_commandes_x'])? !$_SESSION['b_cmd_voir_commandes']:$_SESSION['b_cmd_voir_commandes'];
		$_SESSION['b_cmd_voir_missions']=isset($_POST['b_cmd_voir_missions_x'])? !$_SESSION['b_cmd_voir_missions']:$_SESSION['b_cmd_voir_missions'];
		if(!$_SESSION['b_cmd_voir_commandes'] && !$_SESSION['b_cmd_voir_missions'])//affichage obligatoire
		{ if($b_cmd_voir)
			{ $_SESSION['b_cmd_voir_commandes']=true;
			}
			else
			{ $_SESSION['b_cmd_voir_missions']=true;
			}
			$warning="<br>Vous devez cocher au moins une case";
		}
		// commandes par etat
		foreach($tab_etat_commande as $etat_commande_val)
		{ $_SESSION['b_cmd_voir_'.$etat_commande_val]=isset($_POST['b_cmd_voir_'.$etat_commande_val]);
		}
		//rechercher cmd_texterecherche
		$_SESSION['cmd_champrecherche']=isset($_POST['cmd_champrecherche'])?$_POST['cmd_champrecherche']:"";
		$_SESSION['cmd_texterecherche']=isset($_POST['cmd_texterecherche'])?$_POST['cmd_texterecherche']:"";
		if($_SESSION['cmd_champrecherche']!='' && $_SESSION['cmd_texterecherche']!='')
		{	$_SESSION['b_cmd_detail']=false;
			$_SESSION['b_cmd_voir_missions']=true;
			$_SESSION['b_cmd_voir_commandes']=true;
			$_SESSION['b_cmd_voir_typecredit_cnrs']=true;
			$_SESSION['b_cmd_voir_typecredit_ul']=true;
			// 20170412
			$_SESSION['b_cmd_voir_avoirs_seuls']=false;
			$_SESSION['b_cmd_voir_equipement_seul']=false;
			foreach($tab_etat_commande as $etat_commande_val)
			{ $_SESSION['b_cmd_voir_'.$etat_commande_val]=true;
			}
		}

		$b_cmd_voir=$_SESSION['b_cmd_voir_typecredit_ul'];//par defaut
		$_SESSION['b_cmd_voir_typecredit_ul']=isset($_POST['b_cmd_voir_typecredit_ul_x'])? !$_SESSION['b_cmd_voir_typecredit_ul']:$_SESSION['b_cmd_voir_typecredit_ul'];
		$_SESSION['b_cmd_voir_typecredit_cnrs']=isset($_POST['b_cmd_voir_typecredit_cnrs_x'])? !$_SESSION['b_cmd_voir_typecredit_cnrs']:$_SESSION['b_cmd_voir_typecredit_cnrs'];
		if(!$_SESSION['b_cmd_voir_typecredit_ul'] && !$_SESSION['b_cmd_voir_typecredit_cnrs'])//affichage obligatoire
		{ if($b_cmd_voir)
			{ $_SESSION['b_cmd_voir_typecredit_ul']=true;
			}
			else
			{ $_SESSION['b_cmd_voir_typecredit_cnrs']=true;
			}
			$warning="Vous devez cocher au moins une case";
		}
		$_SESSION['cmd_contrat_ou_eotp_recherche_radio']=isset($_POST['cmd_contrat_ou_eotp_recherche_radio'])?$_POST['cmd_contrat_ou_eotp_recherche_radio']:"virtuel";
		//modif rsa--lddir => code -1 pour aucun (''=rsa--lddir)
		//$_SESSION['cmd_contrat_ou_eotp_recherche']=isset($_POST['cmd_contrat_ou_eotp_recherche'])?$_POST['cmd_contrat_ou_eotp_recherche']:"";
		$_SESSION['cmd_contrat_ou_eotp_recherche']=isset($_POST['cmd_contrat_ou_eotp_recherche'])?$_POST['cmd_contrat_ou_eotp_recherche']:"-1";
		$_SESSION['b_cmd_voir_avoirs_seuls']=isset($_POST['b_cmd_voir_avoirs_seuls_x'])? !$_SESSION['b_cmd_voir_avoirs_seuls']:$_SESSION['b_cmd_voir_avoirs_seuls'];
		// 20170412
		$_SESSION['b_cmd_voir_equipement_seul']=isset($_POST['b_cmd_voir_equipement_seul_x'])? !$_SESSION['b_cmd_voir_equipement_seul']:$_SESSION['b_cmd_voir_equipement_seul'];
		$_SESSION['b_cmd_voir_pour_codesecrsite']=isset($_POST['b_cmd_voir_pour_codesecrsite'])?$_POST['b_cmd_voir_pour_codesecrsite']:$_SESSION['b_cmd_voir_pour_codesecrsite'];
	}
	else if($nomformulaire=="gestioncommandes_taille_colonnes")//colonnes etroites ou larges
	{	$_SESSION['b_cmd_petite_taille_colonnes']=isset($_POST['b_cmd_petite_taille_colonnes_x'])?!$_SESSION['b_cmd_petite_taille_colonnes']:$_SESSION['b_cmd_petite_taille_colonnes'];
	}
	// 20170412
	else if($nomformulaire=="gestioncommandes_voir_equipement_seul")
	{	$_SESSION['b_cmd_voir_equipement_seul']=isset($_POST['b_cmd_voir_equipement_seul_x'])?!$_SESSION['b_cmd_voir_equipement_seul']:$_SESSION['b_cmd_voir_equipement_seul'];
		if($_SESSION['b_cmd_voir_equipement_seul'])
		{ $_SESSION['b_cmd_voir_missions']=false;
			$_SESSION['b_cmd_voir_commandes']=true;
		}
		else
		{ $_SESSION['b_cmd_voir_missions']=true;
		}
	}
	else if($nomformulaire=="gestioncommandes_tri_et_voir_col")
	{ foreach($_POST as $postkey=>$val)
		{ if(strpos($postkey,'b_cmd_champ_tri#')!==false)
			{ $postkey=rtrim($postkey,'_x');
				$postkey=rtrim($postkey,'_y');
				$posdoublediese=strpos($postkey,'##');
				if($posdoublediese!==false)
				{ $_SESSION['b_cmd_champ_tri']=substr($postkey,strlen('b_cmd_champ_tri#'),$posdoublediese-strlen('b_cmd_champ_tri#'));
					$_SESSION['b_cmd_ordre_tri']=substr($postkey,$posdoublediese+2);
				}
			}
		}
		$_SESSION['b_cmd_voir_col#referent']=isset($_POST['b_cmd_voir_col#referent_x'])? !$_SESSION['b_cmd_voir_col#referent']:$_SESSION['b_cmd_voir_col#referent'];
		$_SESSION['b_cmd_voir_col#migo']=isset($_POST['b_cmd_voir_col#migo_x'])? !$_SESSION['b_cmd_voir_col#migo']:$_SESSION['b_cmd_voir_col#migo'];
		$_SESSION['b_cmd_voir_col#justification']=isset($_POST['b_cmd_voir_col#justification_x'])? !$_SESSION['b_cmd_voir_col#justification']:$_SESSION['b_cmd_voir_col#justification'];
		$_SESSION['b_cmd_voir_col_invalider']=isset($_POST['b_cmd_voir_col_invalider_x'])? !$_SESSION['b_cmd_voir_col_invalider']:$_SESSION['b_cmd_voir_col_invalider'];
		$_SESSION['b_cmd_voir_col#notes']=isset($_POST['b_cmd_voir_col#notes_x'])? !$_SESSION['b_cmd_voir_col#notes']:$_SESSION['b_cmd_voir_col#notes'];
	}
}
// 10092013 en attendant correction 
$mini=$_SESSION['b_cmd_version_mini'];//false;
// -----------------------------
// Traitement de l'action demandée dans le POST
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $_SERVER['PHP_SELF'])) 
{ $affiche_succes=true;
	$query_rs_cmd="select commande.*".
								" from commande".
								" where codecommande=".GetSQLValueString($codecommande, "text");
	$rs_cmd=mysql_query($query_rs_cmd) or die(mysql_error());
	$row_rs_cmd=mysql_fetch_assoc($rs_cmd);
	if($action=='valider' || $action=='valider_mail_visa_revalide')// && !isset($_POST['annuler_x']) 28/01/2014 : $_POST['annuler_x'] peut etre positionne si passage par confirmer_action_commande
	{ if($action=='valider' || ($action=='valider_mail_visa_revalide' && isset($_POST['envoyer_mail_validation'])))// si validation normale (sans annulation de visa anterieure) ou si validation avec annulation de visa anterieure et envoyer_mail_postionne dans confirme_action_commande 
		{ $erreur_envoimail=mail_validation_commande($row_rs_cmd,$codeuser,$codevisa_a_apposer,$envoyer_mail_srh);
		}
		
		$rs=mysql_query("select coderole from cmd_statutvisa where codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text"));
		$row_rs=mysql_fetch_assoc($rs);
		$codevisa_a_apposer_lib=$row_rs['coderole'];
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : Visa '".$codevisa_a_apposer_lib."' pour la commande ".$row_rs_cmd['codecommande'];;
			$affiche_succes=false;
			$erreur="Validation non effectu&eacute;e.";
		}
		else
		{ //Dans tous les cas
			$message_resultat_affiche="Validation effectu&eacute;e.";
			// 20/01/2014 plus d'une imput. virt.
			$viser_la_commande_totalement=true;
			// seuls resp. credits et ceux qui $_SESSION['b_cmd_etre_admin'] ont pu valider theme ou contrat
			// s'il n'y a qu'un visa a apposer ou si le dernier visa est apposé par resp. credits
			if(($codevisa_a_apposer_lib=='theme' || $codevisa_a_apposer_lib=='contrat') && !$_SESSION['b_cmd_etre_admin'])
			{	//imputations de la commande
				$query_rs="select numordre,codecontrat from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0'";
				$rs=mysql_query($query_rs);
				if(mysql_num_rows($rs)>1)// plus d'une imputation	virtuelle	
				{	$viser_la_commande_totalement=false;
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
					$tab_commandeimputationbudget_statutvisa_de_la_commande=array();
					$query_rs="select codecontrat,codestatutvisa".
										" from commandeimputationbudget_statutvisa".
										" where codecommande=".GetSQLValueString($codecommande, "text")."and (codestatutvisa='02' or codestatutvisa='03')";
					$rs=mysql_query($query_rs);
					while($row_rs=mysql_fetch_assoc($rs))
					{ $tab_commandeimputationbudget_statutvisa_de_la_commande[$row_rs['codecontrat']]=$row_rs['codestatutvisa'];
					}
					foreach($tab_commandeimputationbudget_de_la_commande as $uncodecontrat=>$numordre)
					{ if(array_key_exists($uncodecontrat,$tab_commandeimputationbudget_du_codeuser_de_la_commande) && !array_key_exists($uncodecontrat,$tab_commandeimputationbudget_statutvisa_de_la_commande))
						{ $updateSQL ="delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text").
													" and codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text")." and codecontrat=".GetSQLValueString($uncodecontrat, "text");
							mysql_query($updateSQL) or die(mysql_error());
							$updateSQL = "INSERT into commandeimputationbudget_statutvisa (codecommande,codestatutvisa,codecontrat,codeacteur,datevisa) values (".
														GetSQLValueString($codecommande, "text").",".
														GetSQLValueString($codevisa_a_apposer, "text").",".
														GetSQLValueString($uncodecontrat, "text").",".
														GetSQLValueString($codeuser, "text").",".
														GetSQLValueString($aujourdhui, "text").						
														")"; 
							mysql_query($updateSQL) or die(mysql_error());
						}
					}
					$query_rs="select codecontrat". 
										" from commandeimputationbudget".
										" where codecommande=".GetSQLValueString($codecommande, "text")." and virtuel_ou_reel='0'".
										" and codecontrat not in (select codecontrat from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text").
																							" and (codestatutvisa='02' or codestatutvisa='03')".")";
					$rs=mysql_query($query_rs);
					if(mysql_num_rows($rs)==0)//toutes les imputations ont le visa 'theme' ou 'contrat'
					{ $viser_la_commande_totalement=true;
					}
				}
			}
			// fin 20/01/2014 plus d'une imput. virt.			
			if($viser_la_commande_totalement)
			{ // la suppression dans commandeimputationbudget_statutvisa est faite ici car si peut_etre_admin, le visa est appose pour toutes les imputations
				$updateSQL ="delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text");
				mysql_query($updateSQL) or die(mysql_error()); 
				// suppression de la ligne avec $codeuser pour cette commande $codecommande pour le role si elle existe
				$updateSQL ="delete from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text").
										" and codestatutvisa=".GetSQLValueString($codevisa_a_apposer, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
				
				$updateSQL = "INSERT into commandestatutvisa (codecommande,codestatutvisa,codeacteur,datevisa) values (".
											GetSQLValueString($codecommande, "text").",".
											GetSQLValueString($codevisa_a_apposer, "text").",".
											GetSQLValueString($codeuser, "text").",".
											GetSQLValueString($aujourdhui, "text").						
											")";
				mysql_query($updateSQL) or die(mysql_error());
			}
			// modif 20/09/2014 cas d'une commande dupliquee non modifiee :on ne tient compte que de la 1ere imputation virtuelle dont on reporte le montantengage 
			// dans la 1ere reelle si elle est vide. Suppression dans ce cas des autres imputations reelles. Uniquement pour visa resp pour mettre un montant reel pour IEB
			if(($codevisa_a_apposer_lib=='referent'))
			{	$query_rs="select montantengage, virtuel_ou_reel from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")." and numordre='01' order by virtuel_ou_reel asc";
				$rs=mysql_query($query_rs);
				if($row_rs=mysql_fetch_assoc($rs))
				{ $montantengage_virtuel=$row_rs['montantengage'];
					if($row_rs=mysql_fetch_assoc($rs))
					{ $montantengage_reel=$row_rs['montantengage'];
						if($montantengage_reel=='')
						{ $updateSQL ="update commandeimputationbudget set montantengage=".GetSQLValueString($montantengage_virtuel, "text").
													" where codecommande=".GetSQLValueString($codecommande, "text").
													" and virtuel_ou_reel='1' and numordre='01'";
							mysql_query($updateSQL) or die(mysql_error());
							$updateSQL ="delete from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text").
													" and virtuel_ou_reel='1' and numordre<>'01'";
							mysql_query($updateSQL) or die(mysql_error());
						}
					}
				}
			}
			// fin modif 20/09/2014
		}
	}
	else if(isset($_POST['b_invalider_visa_x']))
	{	$message_resultat_affiche="Invalidation effectu&eacute;e.";
		// suppression de tous les visas pour cette commande $codecommande pour le role apres mail_invalidation_commande qui a besoin des statutsvisas de la commande
		$query_rs="select codestatutvisa from  cmd_statutvisa ".
							" where codestatutvisa in (select max(codestatutvisa) from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text").")";
		$rs=mysql_query($query_rs) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))//max de codestatutvisa
		{ $codestatutvisa_a_annuler=$row_rs['codestatutvisa'];
			$updateSQL ="delete from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text")." and codestatutvisa=".GetSQLValueString($codestatutvisa_a_annuler, "text");
			mysql_query($updateSQL) or die(mysql_error());
			if($row_rs['codestatutvisa']=='02' || $row_rs['codestatutvisa']=='03')//theme ou contrat : supprime visas partiels eventuels
			{ $updateSQL ="delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text"); 
				mysql_query($updateSQL) or die(mysql_error());
			}
			// on met a jour codevisaannulemax si le visa annule est plus grand : s'il est plus petit, il faut pouvoir atteindre codevisaannulemax
			// si codevisaannulemax='',codestatutvisa_a_annuler>codevisaannulemax  
			if($codestatutvisa_a_annuler>$row_rs_cmd['codevisaannulemax'])
			{ $updateSQL ="update commande set codevisaannulemax=".GetSQLValueString($codestatutvisa_a_annuler, "text")." where  codecommande=".GetSQLValueString($codecommande, "text");
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
	}
	else if($action=='annuler')
	{ if($cmd_ou_miss=='mission')
		{ $updateSQL ="update mission set estannule=".GetSQLValueString($estannule, "text")." where codemission=".GetSQLValueString($codemission, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
		else
		{ $updateSQL ="update commande set estannule=".GetSQLValueString($estannule, "text")." where codecommande=".GetSQLValueString($codecommande, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
	}
	else if($cmd_ou_miss=='commande' && $action=='avoir')
	{ $erreur_envoimail=mail_validation_commande_avoir($row_rs_cmd,$codeuser);
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : Op&eacute;ration sur avoir non effectu&eacute;e pour la commande ".$row_rs_cmd['codecommande'];
			$affiche_succes=false;
			$erreur="Op&eacute;ration sur avoir non effectu&eacute;e.";
		}
		else
		{ $updateSQL ="update commande set estavoir=".GetSQLValueString($estavoir, "text")." where codecommande=".GetSQLValueString($codecommande, "text");
			mysql_query($updateSQL) or die(mysql_error());
		}
	}
	else if(isset($_POST['b_supprimer_x']))
	{ $erreur="";
		// vérif que la suppression est possible
		if($erreur=="")
		{ $affiche_succes=true;
			$message_resultat_affiche='Suppression de la '.$cmd_ou_miss.' '.$codecommande.' effectu&eacute;e avec succ&egrave;s';
			if($cmd_ou_miss=='commande')
			{ // suppression des pieces jointes et du rep les contenant eventuels
				$rs_commandepj=mysql_query("select codetypepj from commandepj".
																		" where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				if(mysql_num_rows($rs_commandepj)>=1)
				{ while($row_rs_commandepj=mysql_fetch_assoc($rs_commandepj))
					{	unlink($GLOBALS['path_to_rep_upload'] .'/commande/'.$codecommande.'/'.$row_rs_commandepj['codetypepj']);
					}
					//suppression du rep de cette commande
					suppr_rep($GLOBALS['path_to_rep_upload'] .'/commande/'.$codecommande);
					// suppression des enreg. des pj pour ce commande
					mysql_query("delete from commandepj where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				}
				mysql_query("delete from commande where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				mysql_query("delete from commandeimputationbudget where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				mysql_query("delete from commandemigo where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				mysql_query("delete from commandemigoliquidation where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				// 20/01/2014 plus d'une imput. virt.			
				mysql_query("delete from commandeimputationbudget_statutvisa where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error()); 
				// fin 20/01/2014 plus d'une imput. virt.			
				mysql_query("delete from commandestatutvisa where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				mysql_query("delete from commandejustifiecontrat where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				// 20170405
				mysql_query("delete from commandeinventaire where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				
			}
			else if($cmd_ou_miss=='mission')
			{ // suppression des pieces jointes et du rep les contenant eventuels
				$rs_missionpj=mysql_query("select codetypepj from commandepj".
																		" where codecommande=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				if(mysql_num_rows($rs_missionpj)>=1)
				{ while($row_rs_missionpj=mysql_fetch_assoc($rs_missionpj))
					{	unlink($GLOBALS['path_to_rep_upload'] .'/mission/'.$codecommande.'/'.$row_rs_missionpj['codetypepj']);
					}
					//suppression du rep de cette mission
					suppr_rep($GLOBALS['path_to_rep_upload'] .'/mission/'.$codecommande);
					// suppression des enreg. des pj pour ce mission
					mysql_query("delete from missionpj where codemission=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				}
				mysql_query("delete from mission where codemission=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
				mysql_query("delete from missionetape where codemission=".GetSQLValueString($codecommande, "text")) or die(mysql_error());
			}
		}
	}
	else if($cmd_ou_miss=='mission' && $action=='traite_assurance')
	{ mysql_query("update mission set assureetranger=".GetSQLValueString($traite_assurance=='non'?"E":"V", "text").
								" where codemission=".GetSQLValueString($codemission, "text")) or die(mysql_error());
	}
	else if($cmd_ou_miss=='commande' && $action=='traite_ieb')
	{ $updateSQL ="update commande set traite_ieb=".GetSQLValueString($traite_ieb, "text")." where codecommande=".GetSQLValueString($codecommande, "text");
		mysql_query($updateSQL) or die(mysql_error());
	}
	// 20170412
	else if($cmd_ou_miss=='commande' && $action=='inventorier')
	{ $updateSQL ="update commande set estinventorie=".GetSQLValueString($estinventorie, "text")." where codecommande=".GetSQLValueString($codecommande, "text");
		mysql_query($updateSQL) or die(mysql_error());
	}
	// 20170412
	else if($cmd_ou_miss=='mission' && $action=='mail_relance_missionnaire')
	{ $erreur_envoimail=mail_relance_missionnaire($codecommande,$codeuser,$info_comp_relance_missionnaire);//codecommande est un codemission!!
		if($erreur_envoimail!="" && $GLOBALS['mode_avec_envoi_mail'])
		{ $warning="Echec envoi de mail : relance non effectu&eacute;e pour la mission ".$row_rs_cmd['codecommande'];
			$affiche_succes=false;
			$erreur="Op&eacute;ration de relance non effectu&eacute;e.";
		}

	}
	$_SESSION['codecommande']=$codecommande;
	if($erreur_envoimail=='')// repositionne a la ligne de validation si pas d'erreur de mail
	{ //http_redirect('gestioncommandes.php#'.$cmd_ancre);
	}
}
else
{ $codecommande=$_SESSION['codecommande'];
}
// ----------------------- Formulaire de la liste des commandes a afficher 
$clause_where_miss="";
$clause_where_cmd=($_SESSION['b_cmd_voir_typecredit_cnrs'] && $_SESSION['b_cmd_voir_typecredit_ul']?"":
									" and commandeimputationbudget.codetypecredit=".GetSQLValueString($_SESSION['b_cmd_voir_typecredit_cnrs']?'01':'02', "text"));

if($_SESSION['cmd_texterecherche']!='')
{	if($_SESSION['cmd_champrecherche']!='')
	{ if($_SESSION['cmd_champrecherche']=='numinterne')
		{ $clause_where_cmd.=" and commande.codecommande like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
			$clause_where_miss.=" and mission.codemission like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
		}
		else if($_SESSION['cmd_champrecherche']=='referent')
		{ $clause_where_cmd.=" and concat(referent.nom,' ',referent.prenom)  like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'"/* ." or referent.prenom  like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'".")" */;
			$clause_where_miss.=" and (concat(referent.nom,' ',referent.prenom) like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche']) ."%'" ./*." or referent.prenom  like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'".")" */
													" or mission.nom like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche']) ."%')";
		}
		else if($_SESSION['cmd_champrecherche']=='numcommande')
		{ $clause_where_cmd.=" and commande.numcommande like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
			$clause_where_miss.=" and false";
		}
		else if($_SESSION['cmd_champrecherche']=='libfournisseur')
		{ $clause_where_cmd.=" and commande.libfournisseur like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
			$clause_where_miss.=" and referent.nom like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
		}
		else if($_SESSION['cmd_champrecherche']=='numfournisseur')
		{ $clause_where_cmd.=" and commande.numfournisseur like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
			$clause_where_miss.=" and false";
		}
		else if($_SESSION['cmd_champrecherche']=='objet')
		{ $clause_where_cmd.=" and commande.".$_SESSION['cmd_champrecherche']." like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
			$clause_where_miss.=" and mission.motif like '%".mysql_real_escape_string($_SESSION['cmd_texterecherche'])."%'";
		}
	}
}
$b_cmd_voir_commandes=$_SESSION['b_cmd_voir_commandes'];
$b_cmd_voir_missions=$_SESSION['b_cmd_voir_missions'];
//modif rsa--lddir => code -1 pour aucun (''=rsa--lddir)
//if($_SESSION['cmd_contrat_ou_eotp_recherche']!='')
// s'il y a eu selection d'un contrat ou eotp et que le type de credit est deselectionne
if($_SESSION['cmd_contrat_ou_eotp_recherche']!='-1' && $_SESSION['cmd_contrat_ou_eotp_recherche']!='' && (!$_SESSION['b_cmd_voir_typecredit_cnrs'] || !$_SESSION['b_cmd_voir_typecredit_ul']))
{ $query_rs="select * from budg_".$_SESSION['cmd_contrat_ou_eotp_recherche_radio']."_source_vue,centrecout,centrefinancier".
						" where code".$_SESSION['cmd_contrat_ou_eotp_recherche_radio']."=".GetSQLValueString($_SESSION['cmd_contrat_ou_eotp_recherche'], "text").
						" and budg_".$_SESSION['cmd_contrat_ou_eotp_recherche_radio']."_source_vue.codecentrecout=centrecout.codecentrecout".
						" and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier".
						" and centrefinancier.codetypecredit=".GetSQLValueString(($_SESSION['b_cmd_voir_typecredit_cnrs']?'01':'02'), "text");
	$rs=mysql_query($query_rs);
	if(mysql_num_rows($rs)==0)
	{ $_SESSION['cmd_contrat_ou_eotp_recherche']='-1';
	}
}


if($_SESSION['cmd_contrat_ou_eotp_recherche']!='-1')
{ $clause_where_cmd.=" and commandeimputationbudget.code".$_SESSION['cmd_contrat_ou_eotp_recherche_radio']."=".GetSQLValueString($_SESSION['cmd_contrat_ou_eotp_recherche'], "text");
	$b_cmd_voir_missions=false;
}

if($_SESSION['b_cmd_voir_avoirs_seuls'])
{ $clause_where_cmd.=" and commande.estavoir='oui'";
}

if($_SESSION['b_cmd_voir_avoirs_seuls'])
{ $clause_where_cmd.=" and commande.estavoir='oui'";

}
if($_SESSION['b_cmd_voir_equipement_seul'])
{ $clause_where_cmd.=" and (commande.codenature='05' or commande.codenature='08' or commande.codenature='10')";
}


if($_SESSION['b_cmd_voir_pour_codesecrsite']!='')
{ $clause_where_cmd.=" and commande.codesecrsite=".GetSQLValueString($_SESSION['b_cmd_voir_pour_codesecrsite'], "text");
	$clause_where_miss.=" and codesecrsite=".GetSQLValueString($_SESSION['b_cmd_voir_pour_codesecrsite'], "text");
}

$clause_group_by ="";
// on prend les infos de contrat (virtuel) si champ tri!=libeotp
$virtuel_ou_reel='0';
$contrat_ou_eotp='contrat';
if($_SESSION['b_cmd_champ_tri']=='libeotp'|| $_SESSION['cmd_contrat_ou_eotp_recherche_radio']=="eotp")
{ $virtuel_ou_reel='1';
	$contrat_ou_eotp='eotp';
}
$budg_source_vue='budg_'.$contrat_ou_eotp.'_source_vue';

$clause_order_by=" codecommande asc";//par defaut
if($_SESSION['b_cmd_champ_tri']=='referent')
{ $clause_order_by="referentnom, referentprenom ".$_SESSION['b_cmd_ordre_tri'];
}
else
{ $clause_order_by=$_SESSION['b_cmd_champ_tri']." ".$_SESSION['b_cmd_ordre_tri'];
	if($_SESSION['b_cmd_champ_tri']=='libcontrat' && $contrat_ou_eotp=='eotp')
	{ $clause_order_by="libeotp ".$_SESSION['b_cmd_ordre_tri'];
	}
}

if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree avant select commandes, missions : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant toutes commandes-missions ordonnees dans le temps : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}
$tab_tout_miss=array();
$tab_tout_cmd_de_miss=array();
// 06122014
if($_SESSION['cmd_champrecherche']!='')
{ $query_rs=" SELECT distinct concat('M',mission.codemission) as codecmdmiss,".
							" 'mission' as cmd_ou_miss,mission.codemission as codecommande,".
							" mission.codemission, codecreateur,codemodifieur,codeagent as codereferent,codesecrsite,".
							" '' as numcommande, min(departdate) as datecommande,max(arriveedate) as dateretourmission, motif as objet,concat(mission.nom,' ',mission.prenom) as libfournisseur,".
							" '' as codenature,".
							" mission.nom as referentnom, mission.prenom as referentprenom,".
							" if(note='','&nbsp',note) as note,estetranger,if(estsansfrais='oui' or avecpriseenchargeautre_ul='oui','oui','non') as estsansfrais,".
							" assureetranger,estannule,'' as estavoir, '' as traite_ieb, '' as estinventorie".// 20170412
							" FROM individu as referent,mission left join missionetape on mission.codemission=missionetape.codemission".
							" WHERE mission.codeagent=referent.codeindividu". 
							" and  mission.codemission<>''".
							" GROUP BY missionetape.codemission";
	$rs = mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_tout_miss[$row_rs['codemission']]=$row_rs;
	}

	$query_rs =	//($b_cmd_voir_commandes?
							"SELECT distinct concat('C',commande.codecommande) as codecmdmiss,'commande' as cmd_ou_miss,commande.codecommande as codecommande,".
							" commande.codemission,commande.codecreateur,commande.codemodifieur,commande.codereferent,commande.codesecrsite,".
							" numcommande,commande.datecommande,'' as dateretourmission,objet,libfournisseur,commande.codenature,".
							" referent.nom as referentnom, referent.prenom as referentprenom,".
							" if(commande.note='','&nbsp',commande.note) as note,'' as estetranger,'' as estsansfrais,'' as assureetranger,commande.estannule,commande.estavoir,commande.traite_ieb,".
							" estinventorie,".// 20170412
							" ".($contrat_ou_eotp=='contrat'?"acronyme as libcontrat":"libeotp").
							" FROM commandeimputationbudget, ".$budg_source_vue.", individu as referent, commande".
							" left join mission on commande.codemission=mission.codemission".
							" WHERE commande.codereferent=referent.codeindividu". 
							" and commande.codecommande<>''".
							" and commande.codecommande=commandeimputationbudget.codecommande".
							" and commandeimputationbudget.code".$contrat_ou_eotp."=".$budg_source_vue.".code".$contrat_ou_eotp.
							" and commandeimputationbudget.virtuel_ou_reel=".GetSQLValueString($virtuel_ou_reel, "text").
							" ORDER BY ".$clause_order_by;
	$rs = mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_tout_cmd_de_miss[$row_rs['codemission']][$row_rs['codecommande']]=$row_rs;
	}	

}
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant commandes-missions ordonnees dans le temps : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

// fin 06122014
// commandes-missions ordonnees dans le temps
$query_rs =	($b_cmd_voir_commandes?
							"SELECT distinct concat('C',commande.codecommande) as codecmdmiss,'commande' as cmd_ou_miss,commande.codecommande as codecommande,".
							" commande.codemission,commande.codecreateur,commande.codemodifieur,commande.codereferent,commande.codesecrsite,".
							" numcommande,commande.datecommande,'' as dateretourmission,objet,libfournisseur,commande.codenature,".
							" referent.nom as referentnom, referent.prenom as referentprenom,".
							" if(commande.note='','&nbsp',commande.note) as note,'' as estetranger,'' as estsansfrais,'' as assureetranger,commande.estannule,commande.estavoir,commande.traite_ieb,".
							" estinventorie,".// 20170412
							" ".($contrat_ou_eotp=='contrat'?"acronyme as libcontrat":"libeotp").
							" FROM commandeimputationbudget, ".$budg_source_vue.", individu as referent, commande".
							" left join mission on commande.codemission=mission.codemission".
							" WHERE commande.codereferent=referent.codeindividu". 
							" and commande.codecommande<>''".
							" and commande.codecommande=commandeimputationbudget.codecommande".
							" and commandeimputationbudget.code".$contrat_ou_eotp."=".$budg_source_vue.".code".$contrat_ou_eotp.
							" and commandeimputationbudget.virtuel_ou_reel=".GetSQLValueString($virtuel_ou_reel, "text").
							$clause_where_cmd
							:"").
							($b_cmd_voir_commandes && $b_cmd_voir_missions?" UNION ":"").
							($b_cmd_voir_missions?
							" SELECT distinct concat('M',mission.codemission) as codecmdmiss,".
							" 'mission' as cmd_ou_miss,mission.codemission as codecommande,".
							" mission.codemission, codecreateur,codemodifieur,codeagent as codereferent,codesecrsite,".
							" '' as numcommande, min(departdate) as datecommande,max(arriveedate) as dateretourmission, motif as objet,concat(mission.nom,' ',mission.prenom) as libfournisseur,".
							" '' as codenature,".
							" mission.nom as referentnom, mission.prenom as referentprenom,".
							" if(note='','&nbsp',note) as note,estetranger,if(estsansfrais='oui' or avecpriseenchargeautre_ul='oui','oui','non') as estsansfrais,assureetranger,estannule,'' as estavoir, '' as traite_ieb,".
							" '' as estinventorie,".// 20170412
							($contrat_ou_eotp=='contrat'?" 'zzz' as libcontrat":"'zzz' as libeotp").
							" FROM mission".
							" left join missionetape on mission.codemission=missionetape.codemission".
							" left join individu as referent on mission.codeagent=referent.codeindividu".
							" WHERE mission.codemission<>''".
							$clause_where_miss.
							" GROUP BY missionetape.codemission"
							:"").
							(($b_cmd_voir_commandes || $b_cmd_voir_missions)?" ORDER BY ".$clause_order_by:"");
$rs = mysql_query($query_rs) or die(mysql_error());

$tab_cmd_miss=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cmd_miss[$row_rs['codecmdmiss']]=$row_rs;
}

if($_SESSION['cmd_texterecherche']!='')
{  $tab_missionliee=array();
	//ajout des missions liees et commandes subordonnees si elles ne sont pas dans le tableau
	foreach($tab_cmd_miss as $codecmdmiss=>$row_rs_cmd) 
	{ if($row_rs_cmd['cmd_ou_miss']=='commande')
		{ if($row_rs_cmd['codemission']!='' && !isset($tab_cmd_miss['M'.$row_rs_cmd['codemission']]) && isset($tab_tout_miss[$row_rs_cmd['codemission']]))
			{ $tab_cmd_miss['M'.$row_rs_cmd['codemission']]=$tab_tout_miss[$row_rs_cmd['codemission']];
				$tab_missionliee[$row_rs_cmd['codemission']]=$tab_tout_cmd_de_miss[$row_rs_cmd['codemission']];
			}
		}
		else//mission : ajout des commandes 
		{ // PG 20151105 : correction pour warning sans consequence
			if(isset($tab_tout_cmd_de_miss[$row_rs_cmd['codemission']]))
			{	// PG 20151105
				$tab_missionliee[$row_rs_cmd['codemission']]=$tab_tout_cmd_de_miss[$row_rs_cmd['codemission']];
			}
		}
	}
	// ajout des commandes liees aux missions
	foreach($tab_missionliee as $codemission=>$un_tab_missionliee)
	{ foreach($un_tab_missionliee as $codecommande=>$un_tab_tout_cmd_de_miss)
		{ $tab_cmd_miss['C'.$codecommande]=$un_tab_tout_cmd_de_miss;
		}
	}
}

if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree apres select commandes, missions : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

// commandes dont le codeuser est resptheme
$tab_cmd_resptheme=array();
$query_rs="select distinct codecommande,codecontrat from  commandeimputationbudget,centrecouttheme,structureindividu".
					" where commandeimputationbudget.codecentrecout=centrecouttheme.codecentrecout and commandeimputationbudget.codeeotp=''".
					" and centrecouttheme.codestructure=structureindividu.codestructure ".
					" and commandeimputationbudget.virtuel_ou_reel='0'".
					" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text");
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cmd_resptheme[$row_rs['codecommande']]=$row_rs['codecontrat'];
}
// theme dont codeuser est  resp
$query_rs="select structure.codestructure as codetheme from structureindividu,structure".
								" where structureindividu.codestructure=structure.codestructure ".
								" and substring(structure.date_deb,1,4)<=".GetSQLValueString(substr($GLOBALS['date_deb_exercice_comptable'],0,4), "text").
								" and esttheme='oui' and estresp='oui' and codeindividu=".GetSQLValueString($codeuser, "text");
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_contrat_source_du_codeuser=array();
// liste des commandes du resp theme : si le user est resp theme
// on etablit en meme temps la liste des contrats/sources dont le user est resp theme
if($row_rs=mysql_fetch_assoc($rs))
{ $codetheme=$row_rs["codetheme"];
	// sources dont le resp est dans le theme du resp theme, mais pas les sources d'autres dept (source directeur pas visibles par resp dept,...)
	$query_rs=" select distinct commande.codecommande,budg_contrat_source_vue.codecontrat from commandeimputationbudget,commande,budg_contrat_source_vue,individusejour,individutheme,centrecouttheme".
						" where commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat and commandeimputationbudget.codecommande=commande.codecommande".
						" and budg_contrat_source_vue.coderespscientifique=individusejour.codeindividu and ".intersectionperiodes('datedeb_sejour','datefin_sejour','datecommande','datecommande').
						" and individusejour.codeindividu=individutheme.codeindividu and individusejour.numsejour=individutheme.numsejour and individutheme.codetheme=centrecouttheme.codestructure".
						" and centrecouttheme.codestructure=".GetSQLValueString($codetheme, "text"); 
						" and commandeimputationbudget.virtuel_ou_reel='0'";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_cmd_resptheme[$row_rs['codecommande']]=$row_rs['codecontrat'];
		$tab_contrat_source_du_codeuser[$row_rs['codecontrat']]=$row_rs['codecontrat'];
	}
}
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant commandes dont le codeuser est respcontrat : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}


// resp de contrat ou de source ayant un respscientifique ou source de direction, dept 
$tab_cmd_respcontrat=array();
$query_rs="select distinct commandeimputationbudget.codecommande,budg_contrat_source_vue.codecontrat,if(budg_contrat_source_vue.coderespscientifique='',".GetSQLValueString($codeuser, "text").",budg_contrat_source_vue.coderespscientifique) as coderespscientifique".
					" from  commandeimputationbudget,budg_contrat_source_vue,individu".
					" where commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
					" and individu.codeindividu = budg_contrat_source_vue.coderespscientifique".
					" and commandeimputationbudget.codecontrat<>''".
					" and (			(individu.codeindividu = ".GetSQLValueString($codeuser, "text").")".
					"				or  (budg_contrat_source_vue.coderespscientifique='' ".
											" and budg_contrat_source_vue.codecentrecout in (select codecentrecout from centrecouttheme,structureindividu".
																																			" where centrecouttheme.codestructure=structureindividu.codestructure".
																																			" and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text").")".
											")".
					"			)".
					" and commandeimputationbudget.virtuel_ou_reel='0'";
// fin 20/01/2014 plus d'une imput. virt.

$rs=mysql_query($query_rs);
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree query resp de contrat ou de source ayant un respscientifique ou source de direction, dept : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}
// liste des contrats/sources des commandes dont le codeuser est resp.
// liste des contrats/sources dont le user est resp contrat : pour un resp theme la liste est deja remplie pour les contrats/sources dont il est resp theme : s'il a un contrat dans un
// autre theme, le contrat sera rajoute.
while($row_rs=mysql_fetch_assoc($rs))
{ // 20/01/2014 plus d'une imput. virt.			
	$tab_cmd_respcontrat[$row_rs['codecommande']][$row_rs['codecontrat']]=$row_rs['coderespscientifique'];//$tab_cmd_respcontrat[$row_rs['codecommande']]=$row_rs['codecommande']
	$tab_contrat_source_du_codeuser[$row_rs['codecontrat']]=$row_rs['codecontrat'];
	// fin 20/01/2014 plus d'une imput. virt.			
}

if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant 1er passage sur les commandes/missions : commandes : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

// 1er passage sur les commandes/missions : commandes. L'affichage des missions est subordonne a l'affichage de ses commandes liees
$tab_missionliee=array();
//PG 20151031
$tab_contexte['codeuser']=$codeuser;
$tab_contexte['groupe']='pole_gestion_fst';
//PG 20151031
foreach($tab_cmd_miss as $codecmdmiss=>$row_rs_cmd) 
{ if($row_rs_cmd['cmd_ou_miss']=='commande')
	{	//les roles du user en general : 
		// - sif, du, gestcnrs, gestul
		// - referent, secrsite, contrat et theme qui seront ecrases par ceux concernant la commande
		$codecommande=$row_rs_cmd['codecommande'];
		$tab_resp_roleuser_une_commande=$tab_resp_roleuser;//pour initialiser $tab_resp_roleuser_une_commande
		$tab_roleuser_une_commande=$tab_resp_roleuser_une_commande['tab_roleuser']; 
		$estreferent=false;
		$affiche=false;
		if($row_rs_cmd['codereferent']==$codeuser)
		{ $affiche=true;
			$tab_roleuser_une_commande['referent']=$tab_cmd_statutvisa['referent'];
			$estreferent=true;
		}
		//PG 20151031
		$tab_contexte['codesecrsite']=$row_rs_cmd['codesecrsite'];
		if($row_rs_cmd['codesecrsite']==$codeuser || droit_acces($tab_contexte))
		{ //PG 20151031
			$affiche=true;
			$tab_roleuser_une_commande['referent']=$tab_cmd_statutvisa['referent'];
			$tab_roleuser_une_commande['secrsite']=$tab_cmd_statutvisa['secrsite'];
			$estreferent=true;
		}
		if($_SESSION['b_cmd_etre_admin'])
		{ $affiche=true;
		}
		$estresptheme_une_commande=false;
		$estrespcontrat_une_commande=false;
		// role contrat si resp du theme et contrat == ''
		if(array_key_exists($codecommande,$tab_cmd_resptheme))
		{ $tab_roleuser_une_commande['theme']=$tab_cmd_statutvisa['theme'];
			$estresptheme_une_commande=true;
		// devenu inutile car toutes les imputations virtuelles ont un codecontra!=''?
			if($tab_cmd_resptheme[$codecommande]=='') $estrespcontrat_une_commande=true;
		// fin devenu inutile ?
		}
		
		// role contrat si resp contrat et contrat != ''
		if(array_key_exists($codecommande,$tab_cmd_respcontrat))
		{ $tab_roleuser_une_commande['contrat']=$tab_cmd_statutvisa['contrat'];
			$estrespcontrat_une_commande=true;
		}
		
		if($estresptheme_une_commande || $estrespcontrat_une_commande)
		{ $affiche=true;
		}
		$tab_resp_roleuser_une_commande['tab_roleuser']=$tab_roleuser_une_commande;
		$tab_resp_roleuser_une_commande['estreferent']=$estreferent;
		$tab_resp_roleuser_une_commande['estresptheme']=$estresptheme_une_commande;
		$tab_resp_roleuser_une_commande['estrespcontrat']=$estrespcontrat_une_commande;
		$tab_cmd_miss[$codecmdmiss]['tab_resp_roleuser']=$tab_resp_roleuser_une_commande;
		$tab_cmd_miss[$codecmdmiss]['affiche']=$affiche;//on conserve les commandes qui ne seront pas affichees pour le 2eme passage traitement missions
		if($affiche && $row_rs_cmd['codemission']!='')// prepare le second passage pour l'affichage des missions non affichees a priori
		{ $tab_missionliee['M'.$row_rs_cmd['codemission']]['affiche']=true;
//if($codecommande=='09018') echo $codecommande.' affiche '.($affiche===true?'oui':'non');
		}
	}
	else //mission : affichee si user est referent, secrsite ou peut_etre_admin
	{ //PG 20151031
		$tab_contexte['codesecrsite']=$row_rs_cmd['codesecrsite'];
		$tab_cmd_miss[$codecmdmiss]['affiche']=($row_rs_cmd['codereferent']==$codeuser || $row_rs_cmd['codesecrsite']==$codeuser || $_SESSION['b_cmd_etre_admin'] || droit_acces($tab_contexte));
		//PG 20151031
	}
}
unset($tab_cmd_resptheme);
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant 2eme passage sur les missions non affichees mais qui doivent l'etre si une commande liee : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

//passage sur les missions non affichees mais qui doivent l'etre si une commande liee est affichee ou sur les missions qui ne doivent pas l'etre car b_cmd_voir_avoirs_seuls
foreach($tab_cmd_miss as $codecmdmiss=>$row_rs_cmd)
{ if($row_rs_cmd['cmd_ou_miss']=='mission')
	{ if($_SESSION['b_cmd_voir_avoirs_seuls'])
		{ $tab_cmd_miss[$codecmdmiss]['affiche']=isset($tab_missionliee['M'.$row_rs_cmd['codemission']]['affiche']);
		}
		else if(!$tab_cmd_miss[$codecmdmiss]['affiche'] && isset($tab_missionliee['M'.$row_rs_cmd['codemission']]['affiche']) && $b_cmd_voir_commandes)//mission : affichee si resp dept/contrat d'une commande liee
		{	$tab_cmd_miss[$codecmdmiss]['affiche']=$tab_missionliee['M'.$row_rs_cmd['codemission']]['affiche'];
		}
	}
}
//On ne conserve que les lignes a afficher
foreach($tab_cmd_miss as $codecmdmiss=>$row_rs_cmd) //2eme passage sur les commandes/missions : traitement de l'affichage des missions
{ if($tab_cmd_miss[$codecmdmiss]['affiche'])
	{ $tab_rs_commande[$codecmdmiss]=$row_rs_cmd;
	}
}
unset($tab_cmd_miss);

$affichedependancemission=true;
// 06122014
/* if($_SESSION['b_cmd_champ_tri']=='libcontrat' || $_SESSION['b_cmd_champ_tri']=='libeotp'  || $_SESSION['cmd_contrat_ou_eotp_recherche']!='-1')
{ $affichedependancemission=false;
} */
if($_SESSION['b_cmd_champ_tri']=='libcontrat' || $_SESSION['b_cmd_champ_tri']=='libeotp' || $_SESSION['cmd_contrat_ou_eotp_recherche']!='-1')
{ $affichedependancemission=false;
}
// 06122014 fin 
//introduction d'un numero de 00001 a nnnnn : les commandes et missions sont classees selon ce numero pour intercaler les commandes de missions (si elles sont affichees)
$tab_ordonne_commande=array();
$i=0;
$nbtablerow=0;
$tab_cmd_montant=array();
$query_rs="select codecommande,virtuel_ou_reel,sum(montantengage) as montantengage, sum(montantpaye) as montantpaye from commandeimputationbudget ".
					"group by codecommande,virtuel_ou_reel" ;

$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cmd_montant[$row_rs['virtuel_ou_reel']][$row_rs['codecommande']]['montantengage']=$row_rs['montantengage'];
	$tab_cmd_montant[$row_rs['virtuel_ou_reel']][$row_rs['codecommande']]['montantpaye']=$row_rs['montantpaye'];
}
// 20/01/2014 plus d'une imput. virt.	
$tab_commandeimputationbudget_statutvisa=array();
$query_rs="select codecommande,codecontrat,codestatutvisa".
					" from commandeimputationbudget_statutvisa order by codecommande";
$rs=mysql_query($query_rs);
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_commandeimputationbudget_statutvisa[$row_rs['codecommande']][$row_rs['codecontrat']]=$row_rs['codestatutvisa'];
}
// fin 20/01/2014 plus d'une imput. virt.	
$tab_commandeimputationbudget=array();
/* 01/2014  */
$query_rs="SELECT budg_contrat_source_vue.codecontrat,budg_eotp_source_vue.codeeotp,individu.nom,individu.prenom".
					" from  budg_contrateotp_source_vue, budg_eotp_source_vue,budg_contrat_source_vue,individu".
					" where budg_contrat_source_vue.codecontrat=budg_contrateotp_source_vue.codecontrat and budg_contrateotp_source_vue.codeeotp=budg_eotp_source_vue.codeeotp".
					" and budg_contrat_source_vue.coderespscientifique=individu.codeindividu".
					" order by budg_contrat_source_vue.codecontrat"; 
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_eotpcontrat[$row_rs['codeeotp']][$row_rs['codecontrat']]=$row_rs;
}
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant imputations virtuelles : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

// modif 29/09/2014 : centre cout reel ajoute en suffixe
$query_rs="select codeeotp, centrecout_reel.libcourt as libcentrecout_reel from budg_eotp_source_vue, centrecout_reel where budg_eotp_source_vue.codecentrecout_reel=centrecout_reel.codecentrecout_reel";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_eotp_libcentrecout_reel[$row_rs['codeeotp']]=$row_rs['libcentrecout_reel'];
}
// fin modif 29/09/2014
// imputations virtuelles pour toutes les commandes de la base
$query_rs="SELECT commandeimputationbudget.codecommande, commandeimputationbudget.codecontrat as codecontrat,'' as codeeotp,".
					" typecredit.codetypecredit,typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
					" centrecout.libcourt as libcentrecout,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
					" budg_contrat_source_vue.coderespscientifique,respscientifique.prenom,respscientifique.nom,acronyme as libcontrat,'' as libeotp,contrat_ou_source,'' as eotp_ou_source,".
					" virtuel_ou_reel,commandeimputationbudget.numordre".
					" from commandeimputationbudget, typecredit,centrefinancier,centrecout,budg_contrat_source_vue,cmd_typesource,individu as respscientifique".
					" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
					" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
					" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
					" and commandeimputationbudget.virtuel_ou_reel='0' and commandeimputationbudget.codecontrat=budg_contrat_source_vue.codecontrat".
					" and budg_contrat_source_vue.coderespscientifique=respscientifique.codeindividu and budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource".
					"  order by commandeimputationbudget.virtuel_ou_reel,commandeimputationbudget.codecommande,commandeimputationbudget.numordre";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ //echo '<br>virtuel_ou_reel : '.$row_rs['virtuel_ou_reel'].' codecontrat : '.$row_rs['codecontrat'].' libcontrat : '.$row_rs['libcontrat'].' libeotp : '.$row_rs['libeotp'].' libtypesource : '.$row_rs['libtypesource'].' nom : '.$row_rs['nom'];
	if($row_rs['contrat_ou_source']=='contrat')
	{ $row_rs['libcontrat']=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'. - '.$row_rs['libcontrat'];
	}
	else // source
	{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																'libsource'=>$row_rs['libcontrat'],'libcentrecout_reel'=>'',
																'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
		$row_rs['libcontrat']=construitlibsource($tab_construitsource);
		$row_rs['libtypecredit']='CNRS-UL';
	}
	// Marquage des lignes validees
	if(isset($tab_commandeimputationbudget_statutvisa[$row_rs['codecommande']][$row_rs['codecontrat']]))
	{ $row_rs['estimputationvalidee']=true;
	}
	$tab_commandeimputationbudget[$row_rs['codecommande']][$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
}
// imputations reelles pour toutes les commandes de la base
$query_rs=" SELECT commandeimputationbudget.codecommande, '' as codecontrat,commandeimputationbudget.codeeotp as codeeotp,".
					" typecredit.codetypecredit,typecredit.libcourt as libtypecredit, centrefinancier.libcourt as libcentrefinancier,".
					" centrecout.libcourt as libcentrecout,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
					" budg_eotp_source_vue.coderespscientifique,respscientifique.prenom,respscientifique.nom,'' as libcontrat,libeotp,'' as contrat_ou_source,eotp_ou_source,".
					" virtuel_ou_reel,commandeimputationbudget.numordre".
					" from commandeimputationbudget, typecredit,centrefinancier,centrecout,budg_eotp_source_vue,cmd_typesource,individu as respscientifique".
					" where commandeimputationbudget.codetypecredit=typecredit.codetypecredit".
					" and commandeimputationbudget.codecentrefinancier=centrefinancier.codecentrefinancier".
					" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
					" and commandeimputationbudget.virtuel_ou_reel='1' and commandeimputationbudget.codeeotp=budg_eotp_source_vue.codeeotp".
					" and budg_eotp_source_vue.coderespscientifique=respscientifique.codeindividu and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource".
					"  order by commandeimputationbudget.virtuel_ou_reel,commandeimputationbudget.codecommande,commandeimputationbudget.numordre";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($row_rs['eotp_ou_source']=='eotp')
	{ if(count($tab_eotpcontrat[$row_rs['codeeotp']])>1)
		{ $tab_un_eotpcontrat=$tab_eotpcontrat[$row_rs['codeeotp']];
			list($un_codecontrat,$tab_un_contrat)=each($tab_un_eotpcontrat);
			$row_rs['libeotp']=$row_rs['libeotp'].' - '.$tab_un_contrat['nom'].' '.substr($tab_un_contrat['prenom'],0,1).'.';			
		}
		else
		{	$row_rs['libeotp']=$row_rs['libeotp'].' - '.$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'.';
		}
	}
	else // source
	{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																'libsource'=>$row_rs['libeotp'],'libcentrecout_reel'=>$tab_eotp_libcentrecout_reel[$row_rs['codeeotp']],
																'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
		$row_rs['libeotp']=construitlibsource($tab_construitsource);
	}
	$tab_commandeimputationbudget[$row_rs['codecommande']][$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
}
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."apres imputations reelles : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}

$query_rs="select commande.codecommande,coderole,commandestatutvisa.codestatutvisa ".
					" from commande left join commandestatutvisa  on commande.codecommande=commandestatutvisa.codecommande".
					" left join cmd_statutvisa on commandestatutvisa.codestatutvisa=cmd_statutvisa.codestatutvisa".
					" where  commande.codecommande<>''".
					" order by commande.codecommande,cmd_statutvisa.codestatutvisa";
$rs=mysql_query($query_rs) or die(mysql_error());
$codecommande='-1';
while($row_rs=mysql_fetch_assoc($rs))
{ if($row_rs['codecommande']!=$codecommande)//plusieurs visas/commande=>on change de $codecommande uniquement si nouvelle commande dans la liste 
	{ $codecommande=$row_rs['codecommande']; 
	}
	if(isset($tab_rs_commande['C'.$codecommande]))
	{ $tab_rs_commande['C'.$codecommande]['tab_commandestatutvisa'][$row_rs['coderole']]=$row_rs['codestatutvisa'];
	} 
}

foreach($tab_rs_commande as $codecmdmiss=>$un_tab_commande)
{ if($un_tab_commande['cmd_ou_miss']=='commande')//$tab_rs_commande['C'.$codecommande]['tab_commandestatutvisa']=$tab_commandestatutvisa;
	{ // 20/01/2014 plus d'une imput. virt.
		$codecommande=$un_tab_commande['codecommande'];	
		$tab_rs_commande[$codecmdmiss]['a_valider_par_resp']=false;
		if(!isset($tab_commandestatutvisa['theme']) && !isset($tab_commandestatutvisa['contrat']) && isset($tab_cmd_respcontrat[$codecommande]))//pas appose=> verif qu'il est ou pas tab_commandeimputationbudget_statutvisa
		{ foreach($tab_cmd_respcontrat[$codecommande] as $codecontrat=>$coderespscientifique)//pour chaque contrat de commande dont codeuser est resp credit
			{ if(!isset($tab_commandeimputationbudget_statutvisa[$codecommande][$codecontrat]) && !$tab_rs_commande['C'.$codecommande]['a_valider_par_resp'])
				{ $tab_rs_commande['C'.$codecommande]['a_valider_par_resp']=true;
				}
			}
			// fin 20/01/2014 plus d'une imput. virt.	
		}
	}
}
unset($tab_cmd_respcontrat);
unset($tab_commandeimputationbudget_statutvisa);

$query_rs="(select codesecr as codesecrsite,nomcourt as  secrsitenom from secr) UNION (select '' as codesecrsite,'' as  secrsitenom from secr) order by secrsitenom";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_secrsite[$row_rs['codesecrsite']]=$row_rs['secrsitenom'];
}
$query_rs="select codenature,libcourt as libnature from cmd_nature ";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_nature[$row_rs['codenature']]=$row_rs['libnature'];
}

// ajout a table des commandes : au moins un visa a ete annule, dateenvoi_etatfrais
$query_rs="select codecommande,codevisaannulemax,dateenvoi_etatfrais from commande";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if(isset($tab_rs_commande['C'.$row_rs['codecommande']]))
	{ $tab_rs_commande['C'.$row_rs['codecommande']]['codevisaannulemax']=$row_rs['codevisaannulemax'];
		$tab_rs_commande['C'.$row_rs['codecommande']]['dateenvoi_etatfrais']=$row_rs['dateenvoi_etatfrais'];
	}
}

if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."avant duree ordonne et decoupe blocs  missions : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}


// permettra d'établir le bloc d'emplacement de la mission fonction des commandes associees ou non
$query_rs="select commande.codecommande,commande.codemission,codestatutvisa,numcommande,nummigo,estannule,estavoir".
					" from commande ".
					" left join commandestatutvisa on commande.codecommande=commandestatutvisa.codecommande".
					" left join commandemigo on commande.codecommande=commandemigo.codecommande".
					" where commande.codemission<>''".
					" order by commande.codemission,commande.codecommande,codestatutvisa";
$rs=mysql_query($query_rs) or die(mysql_error());
$tab_cmd_liees=array();
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_cmd_liees[$row_rs['codemission']][$row_rs['codecommande']][$row_rs['codestatutvisa']]=$row_rs;
}

foreach($tab_rs_commande as $codecmdmiss=>$row_rs_cmd)
{ $nbtablerow++;
	$verif_commande='';//par defaut pas d'erreur dans la commande
	$tab_rs_commande[$codecmdmiss]['secrsitenom']=$tab_secrsite[$row_rs_cmd['codesecrsite']];
	$tab_rs_commande[$codecmdmiss]['libnature']=$row_rs_cmd['codenature'];
	$tab_rs_commande[$codecmdmiss]['libnature']=$tab_nature[$row_rs_cmd['codenature']];

	$tab_rs_commande[$codecmdmiss]['montantengage']['0']='';
	$tab_rs_commande[$codecmdmiss]['montantpaye']['0']='';
	$tab_rs_commande[$codecmdmiss]['montantengage']['1']='';
	$tab_rs_commande[$codecmdmiss]['montantpaye']['1']='';
	if($row_rs_cmd['cmd_ou_miss']=='commande')//commandes : ajout des imputations budgetaires virtuelles et reelles
	{ if(array_key_exists($row_rs_cmd['codecommande'],$tab_cmd_montant['0']))
		{ $tab_rs_commande[$codecmdmiss]['montantengage']['0']=$tab_cmd_montant['0'][$row_rs_cmd['codecommande']]['montantengage'];//$row_rs['montantengage'];
			$tab_rs_commande[$codecmdmiss]['montantpaye']['0']=$tab_cmd_montant['0'][$row_rs_cmd['codecommande']]['montantpaye'];//$row_rs['montantpaye'];
		}
		if(array_key_exists($row_rs_cmd['codecommande'],$tab_cmd_montant['1']))
		{ $tab_rs_commande[$codecmdmiss]['montantengage']['1']=$tab_cmd_montant['1'][$row_rs_cmd['codecommande']]['montantengage'];//$row_rs['montantengage'];
			$tab_rs_commande[$codecmdmiss]['montantpaye']['1']=$tab_cmd_montant['1'][$row_rs_cmd['codecommande']]['montantpaye'];//$row_rs['montantpaye'];
		}
		$tab_rs_commande[$codecmdmiss]['tab_commandeimputationbudget']=array();
		if(array_key_exists($row_rs_cmd['codecommande'],$tab_commandeimputationbudget))
		{ $tab_rs_commande[$codecmdmiss]['tab_commandeimputationbudget']=$tab_commandeimputationbudget[$row_rs_cmd['codecommande']];//[$row_rs['virtuel_ou_reel']][$row_rs['numordre']]=$row_rs;
		} 
		//$tab_rs_commande[$codecmdmiss]['tab_commandeimputationbudget']=$tab_commandeimputationbudget;
		// classement des commandes : pas les commandes liees si b_cmd_voir_missions ou affichedependancemission ou recherche sur champ numcommande
		if($tab_rs_commande[$codecmdmiss]['codemission']=='' || !$_SESSION['b_cmd_voir_missions'] || !$affichedependancemission)
		{ $i++;
			$num_ordonne=str_pad($i,5,'0',STR_PAD_LEFT);
			$tab_ordonne_commande=$tab_rs_commande[$codecmdmiss];
			// etat de la commande
			if($row_rs_cmd['estannule']=='oui')
			{ $tab_rs_etat_commande['annule'][$num_ordonne]=$tab_ordonne_commande;
			}
			else
			{ $tab_commandestatutvisa=$tab_rs_commande[$codecmdmiss]['tab_commandestatutvisa'];
				if(count($tab_commandestatutvisa)==0)
				{ $tab_rs_etat_commande['demande'][$num_ordonne]=$tab_ordonne_commande;
				}
				else 
				{ $etat_commande_val='demande';
					//while($row_rs=mysql_fetch_assoc($rs))
					foreach($tab_commandestatutvisa as $coderole=>$codestatutvisa)
					{ if($coderole=='sif#1' && $tab_rs_commande[$codecmdmiss]['numcommande']!='')
						{ $etat_commande_val='engage';
						}
						else if($coderole=='secrsite')
						{ $etat_commande_val='servicefait';
						}
						else if($coderole=='sif#2')
						{ $etat_commande_val='paye';
						}
					}
					$tab_rs_etat_commande[$etat_commande_val][$num_ordonne]=$tab_ordonne_commande;
				}	
			}
		}
	}
	else//mission
	{ $i++;
		$num_ordonne=str_pad($i,5,'0',STR_PAD_LEFT);
		$tab_ordonne_commande=$tab_rs_commande[$codecmdmiss];
		$codemission=$tab_rs_commande[$codecmdmiss]['codecommande'];
		// bloc d'emplacement de la mission fonction ou non des commandes associees ou non
		$tab_ordonne_commande['supprimable']=false;
		//if(mysql_num_rows($rs)==0 ||  !$affichedependancemission || !$_SESSION['b_cmd_voir_commandes'])//aucune commande associée ou pas d'affichage commande
		if(!array_key_exists($codemission,$tab_cmd_liees) ||  !$affichedependancemission || !$_SESSION['b_cmd_voir_commandes'])//aucune commande associée ou pas d'affichage commande
		{ if($row_rs_cmd['estannule']=='oui')
			{ $etat_commande_val='annule';
			}
			else if($row_rs_cmd['dateretourmission']>=$aujourdhui)
			{ $etat_commande_val='demande'; 
			}
			else
			{ $etat_commande_val='paye'; 
			}
			$tab_ordonne_commande['supprimable']=true;
			$tab_rs_etat_commande[$etat_commande_val][$num_ordonne]=$tab_ordonne_commande;
		}
		else
		{ $tab_coderole_mission_commande_max=array();//visa max pour chaque commande de la mission 
			$est_numcommande_saisi=true;//sera true en fin de while si numcommande saisi pour chaque commande associée
			$est_nummigo_saisi=true;
			$est_cmd_annule=true;
			foreach($tab_cmd_liees[$codemission] as $une_codecommande=>$tab_une_commande)//max statutvisa de chaque commande de la mission
			{ foreach($tab_une_commande as $un_codestatutvisa=>$row_rs)
				{ $est_cmd_annule=($row_rs['estannule']=='oui' && $est_cmd_annule);
				}
			}
			// une commande annulee, quelque soit son etat_commande_val, passe dans le bloc d'une commande non annulee si il existe une commande non annulee
			foreach($tab_cmd_liees[$codemission] as $une_codecommande=>$tab_une_commande)//max statutvisa de chaque commande de la mission
			{ foreach($tab_une_commande as $un_codestatutvisa=>$row_rs)
				{ if($est_cmd_annule ||(!$est_cmd_annule && $row_rs['estannule']!='oui'))
					{ $tab_coderole_mission_commande_max[$row_rs['codecommande']]=($row_rs['codestatutvisa']==''?'':$row_rs['codestatutvisa']);
						$est_numcommande_saisi=($row_rs['numcommande']!='' && $est_numcommande_saisi);//reste vrai si vrai en permanence pour toutes les commandes
						$est_nummigo_saisi=($row_rs['nummigo']!='' && $est_nummigo_saisi);
					}
					// ajout 10/10/2016 pour que cde annulée apparaisse si une cde liee n'est pas annulee. Attribue visa le plus élevé pour que n'ai pas d'incidence sur l'emplacement 
					else
					{ $tab_coderole_mission_commande_max[$row_rs['codecommande']]='sif#2';
					}
				}
			}
			// bloc d'emplacement des commandes de la mission
			if($est_cmd_annule)
			{ $tab_rs_etat_commande['annule'][$num_ordonne]=$tab_ordonne_commande;
				$etat_commande_val='annule';
			}
			else if(array_search(min($tab_coderole_mission_commande_max),$tab_cmd_statutvisa)=='sif#1' && $est_numcommande_saisi)
			{ $tab_rs_etat_commande['engage'][$num_ordonne]=$tab_ordonne_commande;
				$etat_commande_val='engage';
			}
			else if(array_search(min($tab_coderole_mission_commande_max),$tab_cmd_statutvisa)=='secrsite' && $est_nummigo_saisi)
			{	$tab_rs_etat_commande['servicefait'][$num_ordonne]=$tab_ordonne_commande;
				$etat_commande_val='servicefait';
			}
			else if(array_search(min($tab_coderole_mission_commande_max),$tab_cmd_statutvisa)=='sif#2')
			{	$tab_rs_etat_commande['paye'][$num_ordonne]=$tab_ordonne_commande;
				$etat_commande_val='paye';
			}
			else
			{	$tab_rs_etat_commande['demande'][$num_ordonne]=$tab_ordonne_commande;
				$etat_commande_val='demande';
			}
			//prepare la subordination des commandes liees
			if($affichedependancemission)
			{	$tab_mission[$codemission]['num_ordonne']=$num_ordonne;
				$tab_mission[$codemission]['etat_commande']=$etat_commande_val;
				$tab_mission[$codemission]['tab_mission_commande']=$tab_coderole_mission_commande_max;
			}
		}
	}
}

unset($tab_commandeimputationbudget);
unset($tab_cmd_montant);
unset($tab_cmd_liees);

if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree ordonne et decoupe blocs  missions : ".(($timefin-$timedeb))." sec";
 	echo '<br>usage memoire '.memory_get_usage ();
	$timedeb=$timefin; 
}


//ordonne les commandes de mission juste apres cette derniere : transforme son num_ordonne en num_ordonne de la mission suivi de _numordre (0 a 99)
if($_SESSION['b_cmd_voir_missions'] || $affichedependancemission)
{	foreach($tab_mission as $codemission=>$une_mission)
	{ $num_ordonne=$tab_mission[$codemission]['num_ordonne'];
		$etat_commande_val=$tab_mission[$codemission]['etat_commande'];
		$tab_mission_commande=$tab_mission[$codemission]['tab_mission_commande'];
		$i=0;
		foreach($tab_mission_commande as $codecommande=>$val)
		{ if(isset($tab_rs_commande['C'.$codecommande]))
			{ $numordre=str_pad($i+1,2,'0',STR_PAD_LEFT);
				$tab_rs_etat_commande[$etat_commande_val][$num_ordonne.'_'.$numordre]=$tab_rs_commande['C'.$codecommande];
				if(est_avecfrais($tab_rs_commande['C'.$codecommande]['numcommande']))
				{ $tab_rs_etat_commande[$tab_mission[$codemission]['etat_commande']][$num_ordonne]['avecfrais']=true;
					$tab_rs_etat_commande[$etat_commande_val][$num_ordonne.'_'.$numordre]['dateenvoi_etatfrais']=$tab_rs_commande['C'.$codecommande]['dateenvoi_etatfrais'];
				}
				$i++;
			}
		}
	}
}

unset($tab_mission);
unset($tab_rs_commande);

foreach($tab_etat_commande as $etat_commande_val)
{ ksort($tab_rs_etat_commande[$etat_commande_val]);
}
$tab_commandemigo=array();
if($_SESSION['b_cmd_voir_col#migo'])
{ $query_rs="select commandemigo.codecommande,commandemigo.codemigo,nummigo ".
						" from commandemigo ".
						" order by commandemigo.codecommande,commandemigo.codemigo";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_commandemigo[$row_rs['codecommande']][$row_rs['codemigo']]['nummigo']=$row_rs['nummigo'];
		$tab_commandemigo[$row_rs['codecommande']][$row_rs['codemigo']]['tab_liquidation']=array();
	}
	
	$query_rs="select codecommande,codemigo,codeliquidation,numliquidation ".
						" from commandemigoliquidation ".
						" order by codecommande,codemigo,codeliquidation";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_commandemigo[$row_rs['codecommande']][$row_rs['codemigo']]['tab_liquidation'][$row_rs['codeliquidation']]=$row_rs['numliquidation'];
	}
}

// 20170412
$tab_commandeinventaire=array();
$rs=mysql_query("SELECT distinct codecommande from commandeinventaire") or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ $tab_commandeinventaire[$row_rs['codecommande']]=$row_rs;
}
// 20170412

$tab_commandejustifiecontrat=array();
if($_SESSION['b_cmd_voir_col#justification']) 
{ $query_rs="SELECT codecommande,numordre,concat(substring(respscientifique.prenom,1,1),' ',respscientifique.nom,' - ',acronyme) as libcontrat".
						" from commandejustifiecontrat,contrat left join individu as respscientifique on contrat.coderespscientifique=respscientifique.codeindividu".
						" where  contrat.codecontrat=commandejustifiecontrat.codecontrat".
						" order by codecommande,numordre asc";
	$rs=mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_commandejustifiecontrat[$row_rs['codecommande']][$row_rs['numordre']]=$row_rs['libcontrat'];
	}
}
//modif rsa--lddir => code -1 pour aucun (''=rsa--lddir)
$clause_where_credit=($_SESSION['b_cmd_voir_typecredit_cnrs'] && $_SESSION['b_cmd_voir_typecredit_ul']?"":
									" and commandeimputationbudget.codetypecredit=".GetSQLValueString($_SESSION['b_cmd_voir_typecredit_cnrs']?'01':'02', "text"));
									
$lib_tout="Tout".($_SESSION['b_cmd_voir_typecredit_cnrs'] && $_SESSION['b_cmd_voir_typecredit_ul']?"":" ".($_SESSION['b_cmd_voir_typecredit_cnrs']?"CNRS":"").($_SESSION['b_cmd_voir_typecredit_ul']?"UL":""));
$lib_dotation=($_SESSION['b_cmd_voir_typecredit_cnrs']?"Dotation CNRS":"").($_SESSION['b_cmd_voir_typecredit_cnrs'] && $_SESSION['b_cmd_voir_typecredit_ul']?" - ":"").($_SESSION['b_cmd_voir_typecredit_ul']?"RSA-LDDIR":"");
$tab_contrat=array();
// liste des contrats
// ligne pour 'Tout'
$tab_contrat['-1']=array('codecontrat'=>'-1','codecentrecout'=>'','libcontrat'=>$lib_tout,'nom'=>'','sommemontantengage'=>'','solde'=>'');
$query_rs_solde = "SELECT sum(montantengage) as sommemontantengage".
									" from commandeimputationbudget,commande".
									" where commandeimputationbudget.codecommande=commande.codecommande and virtuel_ou_reel='0'".
									" and commande.estannule<>'oui'".
									$clause_where_credit;
$rs_solde=mysql_query($query_rs_solde) or die(mysql_error());
if($row_rs_solde=mysql_fetch_assoc($rs_solde))
{ $tab_contrat['-1']['sommemontantengage']=$row_rs_solde['sommemontantengage'];
	$tab_contrat['-1']['solde']=number_format($row_rs_solde['sommemontantengage'],2,'.',' ');//sprintf('%01.2f',round($row_rs_solde['sommemontantengage'],2));
}
// modif 15/02/2014
$query_rs = "SELECT distinct budg_contrat_source_vue.codecontrat,budg_contrat_source_vue.codecentrecout,acronyme as libcontrat,contrat_ou_source,".
						" centrecout.libcourt as libcentrecout,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,commandeimputationbudget.codetypecredit,".
						" budg_contrat_source_vue.coderespscientifique,respscientifique.nom, respscientifique.prenom, sum(montantengage) as sommemontantengage".
						" from cmd_typesource,centrecout,commandeimputationbudget,budg_contrat_source_vue left join individu as respscientifique ".
						" on budg_contrat_source_vue.coderespscientifique=respscientifique.codeindividu".
						" where budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource and budg_contrat_source_vue.codecontrat=commandeimputationbudget.codecontrat and virtuel_ou_reel='0'".
						" and commandeimputationbudget.codecentrecout=centrecout.codecentrecout".
						$clause_where_credit.
						" group by budg_contrat_source_vue.codecontrat,budg_contrat_source_vue.codecentrecout".
						" order by nom asc";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ // 20140512 : rajout condition restrictive pour permettre aux resps contrats, themes de voir la liste des commandes par contrat. Pour un admin tout est visible. 
	if(isset($tab_contrat_source_du_codeuser[$row_rs['codecontrat']]) || $_SESSION['b_cmd_etre_admin'])
	{ if($row_rs['contrat_ou_source']=='contrat')
		{ $row_rs['libcontrat']=$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'. - '.$row_rs['libcontrat'];
		}
		else // source
		{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																	'libsource'=>$row_rs['libcontrat'],'libcentrecout_reel'=>'',
																	'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																	'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
			$row_rs['libcontrat']=construitlibsource($tab_construitsource).' - '.$row_rs['libcentrecout'];
		}
		$tab_contrat[$row_rs['codecontrat']]=$row_rs;
		$tab_contrat[$row_rs['codecontrat']]['solde']=number_format($row_rs['sommemontantengage'],2,'.',' ');//sprintf('%01.2f',round($row_rs['sommemontantengage'],2));
	}
}
// Pour l'instant, pas possible de faire le cumul en meme temps que liste des eotp : si deux contrats pour un meme eotp alors montant = 2xMontant
$tab_eotp=array();
$tab_eotp['-1']=array('codeeotp'=>'-1','codecentrecout'=>'','libeotp'=>$lib_tout,'nom'=>'','sommemontantengage'=>'','solde'=>'');
$query_rs_solde = "SELECT sum(montantengage) as sommemontantengage".
									" from commandeimputationbudget,commande".
									" where commandeimputationbudget.codecommande=commande.codecommande and virtuel_ou_reel='1'".
									" and commande.estannule<>'oui'".
									$clause_where_credit;
									
$rs_solde=mysql_query($query_rs_solde) or die(mysql_error());
if($row_rs_solde=mysql_fetch_assoc($rs_solde))
{ $tab_eotp['-1']['sommemontantengage']=$row_rs_solde['sommemontantengage'];
	$tab_eotp['-1']['solde']=number_format($row_rs_solde['sommemontantengage'],2,'.',' ');//sprintf('%01.2f',round($row_rs_solde['sommemontantengage'],2));
}
// liste des montants des eotp faite a part : un eotp correspond un ou plusieurs contrats
$query_rs_solde = "SELECT codeeotp,sum(montantengage) as sommemontantengage".
									" from commandeimputationbudget,commande".
									" where commandeimputationbudget.codecommande=commande.codecommande and virtuel_ou_reel='1'".
									" and commande.estannule<>'oui'";
										
$rs_solde=mysql_query($query_rs_solde) or die(mysql_error());
while($row_rs_solde=mysql_fetch_assoc($rs_solde))
{	$tab_solde[$row_rs_solde['codeeotp']]=$row_rs_solde['sommemontantengage'];
}
$query_rs = "SELECT distinct budg_eotp_source_vue.codeeotp,budg_eotp_source_vue.codecentrecout,libeotp,eotp_ou_source,commandeimputationbudget.codetypecredit,cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource,".
						" centrecout.libcourt as libcentrecout,budg_eotp_source_vue.coderespscientifique,respscientifique.nom,respscientifique.prenom".
						" from centrecout,cmd_typesource,commandeimputationbudget,budg_eotp_source_vue".
						" left join contrateotp on budg_eotp_source_vue.codeeotp=contrateotp.codeeotp".
						" left join budg_contrat_source_vue on contrateotp.codecontrat=budg_contrat_source_vue.codecontrat".
						" left join individu as respscientifique on budg_eotp_source_vue.coderespscientifique=respscientifique.codeindividu".
						" where commandeimputationbudget.codeeotp=budg_eotp_source_vue.codeeotp and virtuel_ou_reel='1'".
						" and budg_eotp_source_vue.codetypesource=cmd_typesource.codetypesource".
						" and budg_eotp_source_vue.codecentrecout=centrecout.codecentrecout".
						$clause_where_credit .
						" order by nom asc";
$rs=mysql_query($query_rs) or die(mysql_error());
while($row_rs=mysql_fetch_assoc($rs))
{ if($row_rs['eotp_ou_source']=='eotp')
	{ if(count($tab_eotpcontrat[$row_rs['codeeotp']])>1)
		{ $tab_un_eotpcontrat=$tab_eotpcontrat[$row_rs['codeeotp']];
			list($un_codecontrat,$tab_un_contrat)=each($tab_un_eotpcontrat);
			$row_rs['libeotp']=$row_rs['libeotp'].' - '.$tab_un_contrat['nom'].' '.substr($tab_un_contrat['prenom'],0,1).'.';			
		}
		else
		{	$row_rs['libeotp']=$row_rs['libeotp'].' - '.$row_rs['nom'].' '.substr($row_rs['prenom'],0,1).'.';
		}
	}
	else // source
	{ $tab_construitsource=array(	'codetypesource'=>$row_rs['codetypesource'],'libtypesource'=>$row_rs['libtypesource'],
																'libsource'=>$row_rs['libeotp'],'libcentrecout_reel'=>$tab_eotp_libcentrecout_reel[$row_rs['codeeotp']],
																'coderespscientifique'=>$row_rs['coderespscientifique'],'nomrespscientifique'=>$row_rs['nom'],
																'prenomrespscientifique'=>$row_rs['prenom'],'codetypecredit'=>$row_rs['codetypecredit']);
		$row_rs['libeotp']=construitlibsource($tab_construitsource).' - '.$row_rs['libcentrecout'];
	}
	$codeeotp=$row_rs['codeeotp'];
	$tab_eotp[$codeeotp]=$row_rs;
	if(array_key_exists($codeeotp,$tab_solde))
	{ $tab_eotp[$codeeotp]['solde']=number_format($tab_solde[$codeeotp],2,'.',' ');//sprintf('%01.2f',round($row_rs_solde['sommemontantengage'],2));
	}
	else
	{ $tab_eotp[$codeeotp]['solde']='';
	}
}
// fin modif 15/02/2014
unset($tab_solde);
// PG 20151106 : tous les resp contrats et themes ont acces au lien cumul depenses/recettes
$query_rs = "SELECT budg_contrat_source_vue.codecontrat".//,budg_contrat_source_vue.codecentrecout,budg_contrat_source_vue.total_depense_anterieur,budg_contrat_source_vue.montant_ht,
										//" centrecout.libcourt as libcentrecout, centrefinancier.libcourt as libcentrefinancier,typecredit.codetypecredit,typecredit.libcourt as libtypecredit,".
										//" cmd_typesource.codetypesource,cmd_typesource.libcourt as libtypesource".
										//" ,individu.nom, individu.prenom, acronyme as libcontrat,coderespscientifique,contrat_ou_source".
										" from centrecout,cmd_typesource, budg_contrat_source_vue"./* individu,centrefinancier,typecredit, */
										" where budg_contrat_source_vue.codecontrat<>''".
										" and budg_contrat_source_vue.codetypesource=cmd_typesource.codetypesource".
										//" and budg_contrat_source_vue.coderespscientifique=individu.codeindividu".
										" and centrecout.codecentrecout=budg_contrat_source_vue.codecentrecout".
										//" and centrecout.codecentrefinancier=centrefinancier.codecentrefinancier".
										//" and centrefinancier.codetypecredit=typecredit.codetypecredit".
										" and ".intersectionperiodes('datedeb_contrat','datefin_ieb',"'".$GLOBALS['date_deb_exercice_comptable']."'","'".$GLOBALS['date_fin_exercice_comptable']."'").
										" and ((contrat_ou_source='contrat' and acronyme<>'') or contrat_ou_source='source')".
										" and (budg_contrat_source_vue.coderespscientifique=".GetSQLValueString($codeuser, "text").// Resp contrat ou resp theme ou peut_etre_edmin (sif, du)
										"			 or (budg_contrat_source_vue.codecentrecout in (select codecentrecout from centrecouttheme,structureindividu where centrecouttheme.codestructure=structureindividu.codestructure
																																					 and structureindividu.codeindividu = ".GetSQLValueString($codeuser, "text").
																																					")".
															")".
										//"				or ".($peut_etre_admin?'1':'0').
													")";
										//" order by typecredit.libcourt asc, centrefinancier.libcourt, centrecout.libcourt,individu.nom";
$rs=mysql_query($query_rs) or die(mysql_error());
$estrespcredit=false;
if($row_rs=mysql_fetch_assoc($rs))
{ $estrespcredit=true;
}
// PG 20151106

$query_rs_annonce="select * from annonce";
$rs_annonce=mysql_query($query_rs_annonce);

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
<title>Gestion des commandes <?php echo $GLOBALS['acronymelabo'] ?></title>
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

</style>
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>

<SCRIPT language="javascript">
var tab_contrat=new Array();
var tab_eotp=new Array();
<?php 
$nb=0;
foreach($tab_contrat as $codecontrat=>$row_rs_contrat)
{  ?> 
	var o=new Object();
	o["codecentrecout"]="<?php echo js_tab_val($row_rs_contrat['codecentrecout']); ?>";
	o["codecontrat"]="<?php echo js_tab_val($row_rs_contrat['codecontrat']); ?>";
	o["libcontrat"]="<?php echo js_tab_val($row_rs_contrat['libcontrat']);?>"; 
	o["solde"]="<?php echo js_tab_val($row_rs_contrat['solde']);?>";
	tab_contrat[<?php echo $nb ?>]=o;
	
<?php
	$nb++;
}?>
nbcontrat=<?php echo $nb; ?>;

<?php 
$nb=0;
foreach($tab_eotp as $codeeotp=>$row_rs_eotp)
{  ?> 
	var o=new Object();
	o["codecentrecout"]="<?php echo js_tab_val($row_rs_eotp['codecentrecout']); ?>";
	o["codeeotp"]="<?php echo js_tab_val($row_rs_eotp['codeeotp']); ?>";
	o["libeotp"]="<?php echo js_tab_val($row_rs_eotp['libeotp']);?>"; 
	o["solde"]="<?php echo js_tab_val($tab_eotp[$codeeotp]['solde']);?>";
	tab_eotp[<?php echo $nb ?>]=o;

<?php 
	$nb++;
}?>
nbeotp=<?php echo $nb; ?>;

function populate(champ_radio,liste)
{ // liste(s) des sous-structures d'une structure
	// le nom du champ, hors codetypecredit, indique les listes select concernées
	var frm=document.forms["<?php echo $form_gestioncommandes_voir ?>"];
	var valchamp_radio=champ_radio.value;
	liste_contrat_ou_eotp=frm.elements[liste];
	//alert(frm.elements[liste].value);
	liste_contrat_ou_eotp.options.length=0;
	if(valchamp_radio=="contrat")
	{ for(i=0;i<nbcontrat;i++)
		{ liste_contrat_ou_eotp.options[liste_contrat_ou_eotp.options.length]=new Option(tab_contrat[i].libcontrat, tab_contrat[i].codecontrat);
		}
	}
	else
	{ for(i=0;i<nbeotp;i++)
		{ liste_contrat_ou_eotp.options[liste_contrat_ou_eotp.options.length]=new Option(tab_eotp[i].libeotp, tab_eotp[i].codeeotp);
		}
	}
	liste_contrat_ou_eotp.selectedIndex=0;
}

function affichesolde(champ_radio,codecontrat_ou_eotp,zoneaffichage)
{ var frm=document.forms["<?php echo $form_gestioncommandes_voir ?>"];
	var solde=0;
	if(frm.elements[champ_radio][0].checked)
	{ for(i=0;i<nbcontrat;i++)
		{ if(tab_contrat[i].codecontrat==codecontrat_ou_eotp)
			{ solde=tab_contrat[i].solde;
			}
		}
	}
	else if(frm.elements[champ_radio][1].checked)
	{ for(i=0;i<nbeotp;i++)
		{ if(tab_eotp[i].codeeotp==codecontrat_ou_eotp)
			{ solde=tab_eotp[i].solde;
			}
		}
	}
	document.getElementById(zoneaffichage).innerHTML=solde;
}

var w;
function OuvrirVisible(codecmdmiss)
{ if(codecmdmiss.substring(0,1)=='C')
	{ url="detailcommande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length);
	}
	else
	{ url="detailmission.php?codemission="+codecmdmiss.substring(1,codecmdmiss.length);
	}
	w=window.open(url,'detail',"scrollbars = yes,width=1000,height=500,location=no,mebubar=no,status=no,directories=no");
	w.document.close();
	w.focus();
}
function liste_cumul_montant_contrat(url)
{ w=window.open(url,'detail',"scrollbars = yes,width=1000,height=500,location=no,mebubar=no,status=no,directories=no");
	w.document.close();
	w.focus();
}
function OV(codecmdmiss)
{ return OuvrirVisible(codecmdmiss);
}
function Fermer() 
{ if (w.document) { w.close(); }
}
// marquage ligne tr
<?php
$nbtablerow=0; 
foreach($tab_etat_commande as $key=>$etat_commande_val)
{ if($_SESSION['b_cmd_voir_'.$etat_commande_val])
	{ $nbtablerow+=count($tab_rs_etat_commande[$etat_commande_val]);
	}
}
?>
var nbtablerow=<?php echo $nbtablerow ?>;

function m(tablerow)// marque ligne en vert
{ even_ou_odd='even';
	for(numrow=1;numrow<=nbtablerow;numrow++)
	{ if(document.getElementById('tc'+numrow))
		{ even_ou_odd=(even_ou_odd=='even'?'odd':'even');
			document.getElementById('tc'+numrow).className=even_ou_odd;
		}
		else if(document.getElementById('tm'+numrow))
		{ document.getElementById('tm'+numrow).className='mission';
		}
		else if(document.getElementById('tca'+numrow))
		{ document.getElementById('tca'+numrow).className='avoir';
		}
	}
	document.getElementById(tablerow.id).className='marked';
}

function e(codecmdmiss)
{ if(codecmdmiss.substring(0,1)=='C')
	{ document.location.href="edit_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&action=modifier&cmd_ancre="+codecmdmiss;
	}
	else
	{ document.location.href="edit_mission.php?codemission="+codecmdmiss.substring(1,codecmdmiss.length)+"&action=modifier&cmd_ancre="+codecmdmiss;
	}
}

function a(codecmdmiss)//ajout commande de mission
{ document.location.href="edit_commande.php?action=creer&codecommande=&codemission="+codecmdmiss.substring(1,codecmdmiss.length)+"&cmd_ancre="+codecmdmiss;
}

function c(codecmdmiss,action,codevisa_a_apposer)//confirmer action commande/mission
{ txt_confirm='';
	if(action=='s')
	{ action='supprimer';
		txt_confirm='Supprimer ?';
	}
	else if(action=='i')
	{ action='invalider_visa';
		txt_confirm='Invalider ?';	
	}
	else if(action=='msrh')
	{ action='valider_mail_srh';
		txt_confirm='Valider ?';
		codevisa_a_apposer="04";	
	}
	else if(action=='confirmmail')
	{ action='valider_mail_visa_revalide';
		txt_confirm='Valider ?';	
	}

	if(confirm(txt_confirm)) 	
	{ if(codecmdmiss.substring(0,1)=='C')//C=commande uniquement
		{ document.location.href="confirmer_action_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&action="+action+"&cmd_ou_miss=commande&cmd_ancre="+codecmdmiss+"&codevisa_a_apposer="+codevisa_a_apposer;
		}
		else
		{ document.location.href="confirmer_action_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&action="+action+"&cmd_ou_miss=mission&cmd_ancre="+codecmdmiss;
		}
	}
}

function d(codecmdmiss)
{ if(codecmdmiss.substring(0,1)=='C')
	{ document.location.href="dupliquer_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&cmd_ancre="+codecmdmiss;
	}
	else
	{ document.location.href="dupliquer_mission.php?codemission="+codecmdmiss.substring(1,codecmdmiss.length)+"&cmd_ancre="+codecmdmiss;
	}
}

function u(codecmdmiss)
{ document.location.href="confirmer_action_commande.php?codecommande="+codecmdmiss.substring(1,codecmdmiss.length)+"&cmd_ancre="+codecmdmiss+"&action=mail_relance_missionnaire&cmd_ou_miss=mission";
}
function p(action,codecmdmiss,val_champ_a_renseigner)//post val du formulaire
{ frm=document.forms["gestioncommandes"];
	txt_confirm='';
	if(codecmdmiss.substring(0,1)=='C')
	{ cmd_ou_miss='commande';
	}
	else
	{ cmd_ou_miss='mission';
	}
	if(action=='a')
	{ action='annuler';
		champ_a_renseigner='estannule';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='oui';
			txt_confirm='Annuler la '+cmd_ou_miss+' ?';
		}
		else 
		{ val_champ_a_renseigner='non';
			txt_confirm='Retablir la '+cmd_ou_miss+' ?';
		}
	}
	else if (action=='av')
	{ action='avoir';
		champ_a_renseigner='estavoir';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='oui';
			txt_confirm='Creer un avoir ?';
		}
		else 
		{ val_champ_a_renseigner='non';
			txt_confirm='Retablir cette commande sans avoir ?';
		}
	}
	else if (action=='v')
	{ action='valider';
		champ_a_renseigner='codevisa_a_apposer';
		txt_confirm='Valider ?'
	}
	else if(action=='ti')
	{ action='traite_ieb';
		champ_a_renseigner='traite_ieb';
		if(val_champ_a_renseigner=='o')
		{ val_champ_a_renseigner='oui';
			txt_confirm='Cocher fin de traitement IEB  ?';
		}
		else 
		{ val_champ_a_renseigner='non';
			txt_confirm='Annuler fin de traitement IEB ?';
		}
	}
	// 20170412
	else if(action=='inv')
	{ action='inventorier';
		champ_a_renseigner='estinventorie';
		val_champ_a_renseigner='oui'
		txt_confirm='Fin d\'inventaire pour cette commande ?';
	}
	// 20170412
	frm.elements['cmd_ou_miss'].value=cmd_ou_miss;
	frm.elements['code'+cmd_ou_miss].value=codecmdmiss.substring(1,codecmdmiss.length);
	frm.elements['action'].value=action;
	frm.elements['cmd_ancre'].value=codecmdmiss;
	frm.elements[champ_a_renseigner].value=val_champ_a_renseigner;
	if(confirm(txt_confirm))
	{ frm.submit();
	}
}

// version mini
var tab_img=new Array(20,40,60,80,100,120,140,260,280);
var nb_vignette=9; 
var dX, dY;
function f(objet,event)
{	event.returnValue = false;
	if( event.preventDefault ) event.preventDefault();
	action='';
	val_champ_a_renseigner='';
	nomobjet=objet.name;
	codecmdmiss=nomobjet.substring(0,6);
	posdisese=nomobjet.indexOf("#");
	if(posdisese!=-1)
	{ nomimage=nomobjet.substring(6,posdisese);
		// 01/2014 visa IEB1=04 message srh
		posdoubledisese=nomobjet.indexOf("##");
		if(posdoubledisese!=-1)
		{ val_champ_a_renseigner=nomobjet.substring(posdisese+1,posdoubledisese);
			action=nomobjet.substring(posdoubledisese+2,nomobjet.length);//'msrh';
		}
		else
		{ val_champ_a_renseigner=nomobjet.substring(posdisese+1,nomobjet.length);
		}
	}
	else
	{	nomimage=nomobjet.substring(6,nomobjet.length);
	}
	if(codecmdmiss.substring(0,1)=='C')
	{ cmd_ou_miss='commande';
	}
	else
	{ cmd_ou_miss='mission';
	}
	//Coordonnees de la souris
  var x = event.clientX + (document.documentElement.scrollLeft + document.body.scrollLeft);
  var y = event.clientY + (document.documentElement.scrollTop + document.body.scrollTop);

	//Coordonnées de l'élément
  var eX = 0;
  var eY = 0;
  var element = objet;
	//i=0;
  do
  { //i++;
    eX += element.offsetLeft;
    eY += element.offsetTop;
    element = element.offsetParent;
  } while( element && element.style.position != 'absolute');

	//Calcul du decalage
  dX = x - eX;
  dY = y - eY;
	i=0;
	trouve=false;
	while(!trouve &&i<nb_vignette)
	{ di=tab_img[i];
		if(dX<=di)
		{ trouve=true;
		} 
		else
		{ i++;
		}
	}
	if(i<7)
	{ vignette=nomimage.substring(i,i+1);
	}
	else if(i==7)
	{ vignette=nomimage.substring(i,nomimage.length-1);
	}
	else
	{ vignette=nomimage.substring(nomimage.length-1,nomimage.length);
	}
	// traitement
	if(vignette=='o' || vignette=='p')// voir
	{	OuvrirVisible(codecmdmiss)
	}
	else if(vignette=='m')//modifier
	{ e(codecmdmiss)
	}
	else if(vignette=='a')//ajout cmd de mission 
	{ a(codecmdmiss)
	}
	else if(vignette=='d')// suppression
	{ c(codecmdmiss,'s','')
	}
	else if(vignette=='j')//avoir
	{ p('av',codecmdmiss,'n')
	}
	else if(vignette=='k')//avoir
	{ p('av',codecmdmiss,'o')
	}
	else if(vignette=='i')//invalidation
	{ c(codecmdmiss,'i','')
	}
	else if(vignette=='c')//duplication
	{ d(codecmdmiss)
	}
	else if(vignette=='q')//annulation
	{ p('a',codecmdmiss,'o')
	}
	else if(vignette=='r')//retablir
	{ p('a',codecmdmiss,'n')
	}
	else if(vignette=='v' || vignette=='yv' || vignette=='yyv' || vignette=='yyyv' || vignette=='yyyyv')//visa
	{ if(action=='')
		{ p('v',codecmdmiss,val_champ_a_renseigner);
		}
		else
		{ c(codecmdmiss,action,val_champ_a_renseigner)
		}
	}
	else if(vignette=='u')
	{ u(codecmdmiss);
	}
}
</SCRIPT>
</head>
<body <?php 
			if($erreur!='' || $warning!='')
			{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
			<?php 
			}
			else
      {?> onLoad="window.location.hash='<?php echo $cmd_ancre ?>'"
      <?php 
			}?>
      onUnload="document.getElementById('chargementencours_texte').innerHTML='Envoi de la demande';	document.getElementById('chargementencours').className='affiche';"
>

<table border="0" align="center" cellpadding="0" cellspacing="1">
	<?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des commandes/missions','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'msg_erreur_objet_mail'=>'commande/mission','erreur_envoimail'=>$erreur_envoimail)) ?>
	<tr>
  	<td>&nbsp;
    </td>
  </tr>
	<tr>
		<td>
    	<table align="center">
      	<tr>
    			<?php 
					if($peut_etre_admin)
    			{ ?>
    			<td>
    				<form name="gestioncommandes_admin" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            	<input type="hidden" name="nomformulaire" value="gestioncommandes_admin">
      				<input  name="b_cmd_etre_admin" type="image" src="images/b_cmd_etre_admin_<?php echo ($_SESSION['b_cmd_etre_admin']?"oui":"non") ?>.png" alt="Administration">
						</form>
    			</td>
					<?php 
					}?>
          <td><img src="images/espaceur.gif" width="300" height="0" border="0">
    			</td>
          <td>
          <?php $first=true;$class="bleucalibri11";
					while($row_rs_annonce=mysql_fetch_assoc($rs_annonce))
					{ if($first)
						{?> <img src="images/fleche_page_suiv_clignotante.gif" border="0">
          		</td>
          		<td>
						<?php 
						}
						$class=($class=="bleucalibri11"?"mauvecalibri11":"bleucalibri11");
						echo ($first?'':'<br>').'<span class="'.$class.'">'.$row_rs_annonce['libcourt'].'</span>';
						$first=false;
          }?>	
          </td>
      	</tr>
    	</table><!-- table5-->
    </td>
	</tr>
	<tr>
  	<td align="center">
    	<table border="0" width="50%">
      	<tr>
         <td align="center">
          <div id="chargementencours" class="affiche">
            <table class="table_chargementencours" cellpadding="5">
              <tr>
                <td>
                  <div id="chargementencours_texte" class="noirgrascalibri10">En cours de chargement de <?php echo $nbtablerow ?> lignes ...</div>
                </td>
              </tr>
            </table>
          </div> 
          </td>
        </tr>
      </table>
    </td>
	</tr>
	<tr>
		<td align="left">
			<table border="0" cellspacing="1" cellpadding="0" width="100%">
				<tr>
        	<td>
        		<table>
              <tr>
              	<td>
                	<table>
                  	<tr>
                    	<?php 
											if($_SESSION['b_cmd_etre_admin'] || ($aujourdhui>=$GLOBALS['date_ouverture_saisie_commandes_secr_site'] && $aujourdhui<=$GLOBALS['date_fermeture_saisie_commandes_secr_site']))
                      {?> <td nowrap><span class="bleugrascalibri10">Cr&eacute;ation :&nbsp;</span>
                        </td>
                        <form name="edit_commande" method="post" action="edit_commande.php">
                        <td>
                          <input name="submit_creer" type="image"  img src="images/b_cmd_creer.png" alt="Cr&eacute;er" title="Cr&eacute;er une commande" />
                          <input type="hidden" name="action" value="creer">
                          <input type="hidden" name="codecommande" value="">
                        </td>
                        </form>
                        <form name="edit_mission" method="post" action="edit_mission.php">
                        <td>
                          <input name="submit_creer" type="image"  img src="images/b_cmd_om_creer.png" alt="Cr&eacute;er" title="Cr&eacute;er une mission" />
                          <input type="hidden" name="action" value="creer">
                          <input type="hidden" name="codemission" value="">
                        </td>
                        </form>
                      
                      <?php 
											}
											else
											{?>
                      	<td><span class="rougecalibri10">Exercice <?php echo substr($GLOBALS['date_deb_exercice_comptable'],0,4) ?> : cr&eacute;ation de commande/mission non autoris&eacute;e</span>
												</td>
											<?php 
											}?>
                      <td><a href="aide_gestioncommandes.php">Aide m&eacute;moire 12+ budget/commandes</a></td>
                    </tr>
                  </table><!-- table10-->
                </td>
              </tr>
              <tr>
                <td>
                  <table border="0">
                    <tr>
                     <form name="<?php echo $form_gestioncommandes_voir ?>" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"
                     	onSubmit="if(document.gestioncommandes_voir.cmd_champrecherche.value=='' && document.gestioncommandes_voir.cmd_texterecherche.value!='') 
                      					{ document.gestioncommandes_voir.cmd_champrecherche.value='numinterne';
                                }
                                "
											> <input type="image" src="images/espaceur.gif" width="0" height="0"><!-- la touche entree dans le champ recherche texte fait un submit sur cette image-->
                     		<input type="hidden" name="nomformulaire" value="gestioncommandes_voir">
                      <td nowrap>
											<!-- 21/09/2014 : en attendant correction version mini pour secr.-->
                      <?php if($_SESSION['b_cmd_etre_admin'])
                      { ?><span class="bleucalibri9">Mini.</span><input  name="b_cmd_version_mini" type="image" src="images/b_checked_<?php echo ($mini?"oui":"non") ?>.png" title="Version mini.">
                       <?php 
											}?>
                       <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_recherche_selection">
                       <div class="tooltipContent_cadre" id="info_recherche_selection">
                        <span class="noircalibri10">
                          Les cases &agrave; cocher de ces encadr&eacute;s r&eacute;agissent diff&eacute;remment.<br>
                          - 1er encadr&eacute; de choix de blocs (engage,...), champ et texte de recherche : une validation est n&eacute;cessaire avec <img src="images/b_rechercher.png"><br>
                          &nbsp;&nbsp;&nbsp;ou la touche entr&eacute;.<br>
                          - 2&egrave;me encadr&eacute; de choix commande/mission d&rsquo;une part et 3&egrave;me encadr&eacute; de choix <br>
                          &nbsp;&nbsp;&nbsp;cr&eacute;dits UL et CNRS d'autre part : un clic sur la case &agrave; cocher et la s&eacute;lection est imm&eacute;diate.
                        </span>
                      </div>
                      <script type="text/javascript">
                        var sprytooltip_info_recherche_selection = new Spry.Widget.Tooltip("info_recherche_selection", "#sprytrigger_info_recherche_selection", {offsetX:-100, offsetY:0, closeOnTooltipLeave:true});
                      </script>
                      </td>  
											<td>
                      	<table class="table_gris_encadre">
                        	<tr>
														<?php // commandes par etat
                            foreach($tab_etat_commande as $etat_commande_val)
                            { ?>
                            <td><span class="bleucalibri10"><?php echo $etat_commande_val?></span>
                            </td>
                            <td><input type="checkbox" name="b_cmd_voir_<?php echo $etat_commande_val ?>" <?php echo ($_SESSION['b_cmd_voir_'.$etat_commande_val]?"checked":"") ?>> 
                            <!-- <input name="b_cmd_voir_<?php //echo $etat_commande_val ?>" type="image" src="images/b_checked_<?php //echo ($_SESSION['b_cmd_voir_'.$etat_commande_val]?"oui":"non") ?>.png">-->
                            </td>
                            <?php 
                            }?>
                            <td>
                              <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_etat">
                              <div align="justify" class="tooltipContent_cadre" id="info_etat">
                                <span class="noircalibri10">
                                  Les &eacute;tats, d&rsquo;une commande ou d&rsquo;une mission, d&eacute;finissent chacun un bloc, &agrave; afficher ou non.<br>
                                  Par d&eacute;faut tous les blocs sont affich&eacute;s et sont d&eacute;finis comme suit :<br>
                                  - <b>Demande</b> :  1er visa IEB non appos&eacute; ou n&deg; de commande non renseign&eacute;<br>
                                  - <b>Engag&eacute;</b> : 1er visa IEB appos&eacute; et n&deg; de commande renseign&eacute;<br>
                                  - <b>Service fait</b> : 1er visa IEB appos&eacute; et MIGO saisi<br>
                                  - <b>Pay&eacute;</b> : 2&egrave;me visa IEB appos&eacute; concernant les commandes, date de retour de mission d&eacute;pass&eacute;<br>
                                  - <b>Annul&eacute;</b> : choix de l&rsquo;ic&ocirc;ne <img src="images/b_cmd_miss_annuler_oui.png" width="12" height="12">.  
                                  </span>
                                  <span class="noircalibri10">
                                  Une mission passe dans le bloc &rsquo;pay&eacute;&rsquo; ou &rsquo;annul&eacute;&rsquo; selon qu&rsquo;elle est finie ou annul&eacute;e ou dans le bloc correspondant &agrave; l&rsquo;&eacute;tat de ses commandes selon les cas :<br>
                                  &nbsp;&nbsp;&nbsp;1/ aucune commande n&rsquo;en d&eacute;pend : la date de la mission doit &ecirc;tre pass&eacute;e (date d&rsquo;arriv&eacute;e du dernier trajet) <br>
                                  &nbsp;&nbsp;&nbsp;2/ une ou plusieurs commandes en d&eacute;pendent : le passage d&rsquo;une mission d&rsquo;un bloc &agrave; l&rsquo;autre est subordonn&eacute; au passage de toutes ses commandes d&rsquo;un bloc &agrave; l&rsquo;autre.
                                </span>
                                <br>
                                <span class="mauvegrascalibri10">
                                Attention aux r&eacute;percussions des choix de classement par bloc :<br>
                                </span> 
                                <span class="mauvecalibri10">
                                - lors de la sortie du formulaire commande, apr&egrave;s saisie du n&deg; de commande ou MIGO, il se peut que la commande passe d&rsquo;un bloc &agrave; un autre bloc (&eacute;ventuellement non visible)<br>
                                - une mission peut appara&icirc;tre dans le bloc 'demande' un jour, puis le lendemain se retrouver dans le bloc 'pay&eacute;' (effectu&eacute;e) : le lendemain la date d&rsquo;arriv&eacute;e (retour) est d&eacute;pass&eacute;e.<br>
                                - si une mission comporte plusieurs commandes, elles seront pr&eacute;sent&eacute;es comme indiqu&eacute; en 2/. Par contre, si vous choisissez de ne lister que les commandes, chacune des commandes de la m&ecirc;me mission<br>
                                &nbsp;&nbsp;&nbsp;seront dispatch&eacute;es dans le ou les bloc(s) en fonction de son &eacute;tat.
                                </span>
                                <br><br>
                                <span class="noirgrascalibri10">
                                Pour retrouver une commande &agrave; coup s&ucirc;r, cochez toutes les cases, d&eacute;cochez Mission, saisissez &eacute;ventuellement un crit&egrave;re de recherche, l&rsquo;id&eacute;al &eacute;tant le N&deg; interne, et lancez la recherche.
                              	</span>
                              </div>
                              <script type="text/javascript">
                                var sprytooltip_info_etat = new Spry.Widget.Tooltip("info_etat", "#sprytrigger_info_etat", {offsetX:-300, offsetY:0, closeOnTooltipLeave:true});
                              </script>
                            </td>
                            <td><img src="images/espaceur.gif" width="5" height="1">
                            </td>
                            <td nowrap>
                              <select name="cmd_champrecherche" class="noircalibri9">
                              <?php 
                              foreach($tab_cmd_champrecherche as $un_cmd_champrecherche=>$cmd_valchamprecherche)
                              { ?><option value="<?php echo $un_cmd_champrecherche ?>" <?php echo ($_SESSION['cmd_champrecherche']==$un_cmd_champrecherche?'selected':'')?>><?php echo $cmd_valchamprecherche ?></option>
                              <?php 
                              }?>
                              </select>
                            </td>
                            <td class="bleucalibri10">contient
                            </td>
                            <td><input type="text" name="cmd_texterecherche" class="noircalibri10" id="cmd_texterecherche" value="<?php echo $_SESSION['cmd_texterecherche']==''?'':htmlspecialchars($_SESSION['cmd_texterecherche']) ?>" size="20">
                            </td>
                            <td>
                            <td><input type="image"  name="b_rechercher" id="b_rechercher" img src="images/b_rechercher.png" width="22" height="21" title="Lancer la recherche">
                            </td>
                            <td><input type="image" name="b_corbeille" src="images/b_corbeille.png" width="16" height="16" title="Vider les zones de recherche"
                            		onClick="document.<?php echo $form_gestioncommandes_voir ?>.cmd_texterecherche.value='';
                                document.<?php echo $form_gestioncommandes_voir ?>.cmd_champrecherche.value='';
                                return false;">
                            </td>
                             <td><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_rechercher">
                              <div class="tooltipContent_cadre" id="info_rechercher">
                                <span class="noircalibri10">
                                  S&eacute;lectionnez les 'blocs' demande,... &agrave; afficher, &eacute;ventuellement le champ de recherche si<br>
                                  vous saisissez tout ou partie d&rsquo;un texte &agrave; rechercher, et cliquez sur <img src="images/b_rechercher.png" width="16" height="16"><br>
                                  Pour vider les zones de recherche cliquez sur la jolie corbeille <img src="images/b_corbeille.png" width="16" height="16"><br>
                                </span>
                              </div>
                              <script type="text/javascript">
                                var sprytooltip_info_rechercher = new Spry.Widget.Tooltip("info_rechercher", "#sprytrigger_info_rechercher", {offsetX:-200, offsetY:0, closeOnTooltipLeave:true});
                              </script>
                           </td>
                          </tr>
                        </table>
                      </td>
                      <td><img src="images/espaceur.gif" width="3" height="1">
                      </td>
                      <td>
                      	<table class="table_gris_encadre" >
                        	<tr>
                            <td><span class="bleucalibri10">Commandes</span>
                            </td>
                            <td>
                              <input  name="b_cmd_voir_commandes" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_commandes']?"oui":"non") ?>.png" alt="Afficher les commandes : <?php echo ($_SESSION['b_cmd_voir_commandes']?"oui":"non") ?>" title="Afficher les commandes : <?php echo ($_SESSION['b_cmd_voir_commandes']?"oui":"non") ?>">
                            </td>
                            <td><span class="bleucalibri10">Missions</span>
                            </td>
                            <td>
                              <input  name="b_cmd_voir_missions" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_missions']?"oui":"non") ?>.png" alt="Afficher les missions : <?php echo ($_SESSION['b_cmd_voir_missions']?"oui":"non") ?>" title="Afficher les missions : <?php echo ($_SESSION['b_cmd_voir_missions']?"oui":"non") ?>">
                            </td>
                            <td><img src="images/espaceur.gif" width="1" height="1">
                            </td>
                          </tr>
                        </table>
                      </td>
											<td><img src="images/espaceur.gif" width="3" height="1">
                      </td>
                      <td>
                       	<table class="table_gris_encadre" >
                        	<tr>
                            <td nowrap>
                             <span class="bleucalibri10">UL&nbsp;</span><input  name="b_cmd_voir_typecredit_ul" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_typecredit_ul']?"oui":"non") ?>.png" alt="Afficher les commandes : <?php echo ($_SESSION['b_cmd_voir_typecredit_ul']?"oui":"non") ?>">
                            </td>
                            <td nowrap>
                              <span class="bleucalibri10">CNRS&nbsp;</span><input  name="b_cmd_voir_typecredit_cnrs" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_typecredit_cnrs']?"oui":"non") ?>.png" alt="Afficher les commandes : <?php echo ($_SESSION['b_cmd_voir_typecredit_cnrs']?"oui":"non") ?>" >
                            </td>
                          </tr>
                        </table>
                      </td>
											 <?php
                      if(count($tab_contrat)>=2)//acces listing commandes : admin ou resp de plus d'un contrat
                      { if($_SESSION['b_cmd_etre_admin'])
                        { ?>
                          <td><span class="bleucalibri10">Virt.</span>
                          </td>
                          <td>
                          <input type="radio" name="cmd_contrat_ou_eotp_recherche_radio" id="cmd_contrat_ou_eotp_recherche_radio" value="contrat" <?php echo $_SESSION['cmd_contrat_ou_eotp_recherche_radio']=="contrat"?'checked':'' ?> 
                          onClick="populate(this,'cmd_contrat_ou_eotp_recherche');document.getElementById('solde_cmd_contrat_ou_eotp_recherche').innerHTML='0.00'">	
                          </td>
                          <td><span class="bleucalibri10">R&eacute;el</span>
                          </td>
                          <td>
                          <input type="radio" name="cmd_contrat_ou_eotp_recherche_radio" id="cmd_contrat_ou_eotp_recherche_radio" value="eotp" <?php echo $_SESSION['cmd_contrat_ou_eotp_recherche_radio']=="eotp"?'checked':'' ?> 
                          onClick="populate(this,'cmd_contrat_ou_eotp_recherche');document.getElementById('solde_cmd_contrat_ou_eotp_recherche').innerHTML='0.00'">	
                          </td>
                        <?php 
												}
												else // utile pour que le programme fonctionne a la recuperation du parametre cmd_contrat_ou_eotp_recherche_radio utilise en clause where
												{ ?><input type="hidden" name="cmd_contrat_ou_eotp_recherche_radio" id="cmd_contrat_ou_eotp_recherche_radio" value="contrat" <?php echo $_SESSION['cmd_contrat_ou_eotp_recherche_radio']=="contrat"?'checked':'' ?> 
                          onClick="populate(this,'cmd_contrat_ou_eotp_recherche');document.getElementById('solde_cmd_contrat_ou_eotp_recherche').innerHTML='0.00'">
                        <?php 
												}?>
                        <td>
                          <select name="cmd_contrat_ou_eotp_recherche" id="cmd_contrat_ou_eotp_recherche" class="noircalibri9" 
                          onChange="affichesolde('cmd_contrat_ou_eotp_recherche_radio',this.value,'solde_cmd_contrat_ou_eotp_recherche');">
                          <?php
                          $tab_contrat_ou_eotp=$tab_contrat;
                          if($_SESSION['cmd_contrat_ou_eotp_recherche_radio']=="eotp")
                          { $tab_contrat_ou_eotp=$tab_eotp;
                          }
                          foreach($tab_contrat_ou_eotp as $code_contrat_ou_eotp=>$row_rs_contrat_ou_eotp)
                          { ?> 
                          <option value="<?php echo $code_contrat_ou_eotp ?>" <?php echo ($_SESSION['cmd_contrat_ou_eotp_recherche']==$code_contrat_ou_eotp?'selected':'') ?>><?php echo $row_rs_contrat_ou_eotp['lib'.$_SESSION['cmd_contrat_ou_eotp_recherche_radio']] ?></option>
                          <?php
                          } ?>
                          </select>
                        </td>
                        <td><input type="image"  name="b_rechercher" id="b_rechercher" img src="images/b_rechercher.png" width="22" height="21" title="Lancer la recherche">
                        </td>
                        <?php
                      }
											// PG 20151106 
                      if($estrespcredit /* $estresptheme || $estrespcontrat */ ||  $peut_etre_admin)//acces listing commandes
                      { // PG 20151106
												$url="liste_cumul_montant_contrat.php" ?>
                       <td><a href="javascript:liste_cumul_montant_contrat('<?php echo $url ?>')">D&eacute;penses/recettes/solde <?php echo substr($GLOBALS['date_deb_exercice_comptable'],0,4); ?></a>
                       </td>
											<?php
											} 
											?>
                    </tr>
										<?php if(estrole('sif',$tab_roleuser))
                    {?> 
                    <tr>
                      <td colspan="2">
                      </td> 
                      <td colspan="7">
                       	<table>
                        	<tr>
                            <td nowrap>
															<span class="bleucalibri10">Par secr. d'appui : </span>
                            </td>
                           <td nowrap>
                            <select name="b_cmd_voir_pour_codesecrsite" class="noircalibri10" id="b_cmd_voir_pour_codesecrsite" >
                              <?php 
                                foreach($tab_secrsite as $un_codesecrsite=>$un_nomsecrsite)
                                { ?>
                              <option value="<?php echo $un_codesecrsite ?>" <?php echo ($_SESSION['b_cmd_voir_pour_codesecrsite']==$un_codesecrsite?'selected':'') ?>><?php echo $un_nomsecrsite ?></option>
                              <?php
                                } ?>
                              </select>
                            </td>
                           	<td><input type="image"  name="b_rechercher" id="b_rechercher" img src="images/b_rechercher.png" width="22" height="21" title="Lancer la recherche">
                            </td>
                           <td nowrap>
															<span class="bleucalibri10">Avoirs seuls&nbsp;</span>
                            </td>
                            <td><input  name="b_cmd_voir_avoirs_seuls" type="image" src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_avoirs_seuls']?"oui":"non") ?>.png" alt="Afficher uniquement les avoirs : <?php echo ($_SESSION['b_cmd_voir_avoirs_seuls']?"oui":"non") ?>">
                      			</td>
                            
                          </tr>
                        </table>
                      </td> 
                      <td colspan="2">
											<div id='solde_cmd_contrat_ou_eotp_recherche'><?php echo isset($_SESSION['cmd_contrat_ou_eotp_recherche']) && isset($tab_contrat_ou_eotp[$_SESSION['cmd_contrat_ou_eotp_recherche']]['solde'])?$tab_contrat_ou_eotp[$_SESSION['cmd_contrat_ou_eotp_recherche']]['solde']:''?>
											</div>
                      </td>
                    </tr>
										<?php 
                    }?>
                  </table>
              	</td>
							</tr>
            </table>
           </form>
          </td>
        </tr>
			</table>
		</td>
	</tr>
	<tr>
		<td>
			<table width="100%" border="0" class="data" id="table_results" cellpadding="0">
		<?php 
    // un resp dept, du et sif voient la colonne Invalider
    $colonne_peut_invalider=false;
    if((estrole('theme',$tab_roleuser) && $estresptheme) || (estrole('contrat',$tab_roleuser)) || $_SESSION['b_cmd_etre_admin'] /* || estrole('admingestfin',$tab_roleuser) */)
    { $colonne_peut_invalider=false; 
    }?>
      <!--<tr>
        <td colspan="<?php //echo $colonne_peut_invalider?$nb_col_a_afficher+1:$nb_col_a_afficher ?>">
        </td>
      </tr> -->
    <?php
		if($nbtablerow==0)
		{ ?>
    	<tr>
				<td valign="bottom" align="center">
        	<table class="table_cadre_arrondi_rouge">
          	<tr>
            	<td>
              	<table>
                	<tr>
                  	<td colspan="2" class="bleugrascalibri10">Aucun r&eacute;sultat : v&eacute;rifiez que les bonnes cases sont coch&eacute;es et les crit&egrave;res corrects
              			</td>
                  </tr>
									<tr>
										<td>Afficher commandes :</td><td><?php if(isset($_SESSION['b_cmd_voir_commandes']) && $_SESSION['b_cmd_voir_commandes']){?> oui <?php } else {?> non <?php }?></td>
                  </tr>
									<tr>
										<td>Afficher missions :</td><td><?php if(isset($_SESSION['b_cmd_voir_missions']) && $_SESSION['b_cmd_voir_missions']){?> oui <?php } else {?> non <?php }?></td>
                  </tr>
									<tr>
										<td>Afficher cr&eacute;dits UL :</td><td><?php if(isset($_SESSION['b_cmd_voir_typecredit_ul']) && $_SESSION['b_cmd_voir_typecredit_ul']){?> oui <?php } else {?> non <?php }?></td>
                  </tr>
									<tr>
										<td>Afficher cr&eacute;dits CNRS :</td><td><?php if(isset($_SESSION['b_cmd_voir_typecredit_cnrs']) && $_SESSION['b_cmd_voir_typecredit_cnrs']){?> oui <?php } else {?> non <?php }?></td>
                  <?php 
									foreach($tab_etat_commande as $etat_commande_val)
                  {	if($_SESSION['b_cmd_voir_'.$etat_commande_val])
                    {?><tr><td><?php echo $etat_commande_val?> :</td><td><?php if($_SESSION['b_cmd_voir_'.$etat_commande_val]){?> oui <?php }else{?> non <?php }?></td>
                    	</tr>
                 		<?php 
										}
									}
									if(estrole('sif',$tab_roleuser))
									{ ?><tr>
											<td>Afficher avoir seuls :</td><td><?php if(isset($_SESSION['b_cmd_voir_avoirs_seuls']) && $_SESSION['b_cmd_voir_avoirs_seuls']){?> oui <?php } else {?> non <?php }?></td>
                  	</tr>
                  <?php
                  }
                  
                  if($_SESSION['cmd_champrecherche']!='' && $_SESSION['cmd_texterecherche']!='')
                  { ?>
                  <tr>
										<td colspan="2" align="center"><b>Crit&egrave;re de recherche</b></td>
                  </tr>
                  <tr><td colspan="2"><?php echo $tab_cmd_champrecherche[$_SESSION['cmd_champrecherche']] ?><span class="bleucalibri10">&nbsp;contient : </span><?php echo $_SESSION['cmd_texterecherche'] ?></td>
                  </tr>
                  <?php
                  }?>
                  <tr>
										<td colspan="2" align="center"><b>Droits d&rsquo;acc&egrave;s aux lignes et limitations (cumul des)</b></td>
                  </tr>
										<?php
                    $tab_droits=array('secrsite'=>array('col1'=>'Secr&eacute;tariat','col2'=>'lignes du secr&eacute;tariat'),
																	'contrat'=>array('col1'=>'Resp. contrat','col2'=>'lignes d&rsquo;imputation du contrat'),
																	'theme'=>array('col1'=>'Resp. d&eacute;pt.','col2'=>'lignes d&rsquo;imputation de la dotation du d&eacute;pt. et des contrats du d&eacute;pt.'),
																	'sif#2'=>array('col1'=>'IEB','col2'=>'aucune'),
																	'du'=>array('col1'=>'DU','col2'=>'aucune'),
																	);
                  	foreach($tab_droits as $coderole=>$tab)
                    { if(isset($tab_roleuser[$coderole]))
											{?>
											<tr><td><?php echo $tab['col1'] ?> :</td><td><?php if($tab_roleuser[$coderole]){echo $tab['col2']; }?></td>
											</tr>
												<?php 
											}
										}?>
                </table>
              </td>
            </tr>
          </table>
      	</td>
      </tr>
    <?php
		}
		else//si une ligne de tableau au moins
    { $first=false;
      $numrow=0; ?>
      		<!-- 10/10/2016 form deplace pour que fonctionne avec IE -->
					<form name="gestioncommandes" id="gestioncommandes" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="MM_update" id="MM_update"  value="<?php echo $_SERVER['PHP_SELF'] ?>">
            <input type="hidden" name="action" id="action" value="">
            <input type="hidden" name="codecommande" id="codecommande" value="">
            <input type="hidden" name="codemission" id="codemission" value="">
            <input type="hidden" name="codevisa_a_apposer" id="codevisa_a_apposer" value="">
            <input type="hidden" name="cmd_ancre" id="cmd_ancre" value="">
            <input type="hidden" name="cmd_ou_miss" id="cmd_ou_miss" value="">
            <input type="hidden" name="estannule" id="estannule" value="">
            <input type="hidden" name="estavoir" id="estavoir" value="">
           <input type="hidden" name="traite_assurance" id="traite_assurance" value="">
           	<input type="hidden" name="traite_ieb" id="traite_ieb" value="">
           	<input type="hidden" name="estinventorie" id="estinventorie" value=""><?php // 20170412?>
            <!-- fin 10/10/2016 -->
    <?php
			foreach($tab_etat_commande as $etat_commande=>$etat_commande_val)
			{ if($_SESSION['b_cmd_voir_'.$etat_commande_val] && count($tab_rs_etat_commande[$etat_commande_val])>0)
				{ if(!$first)
					{ ?>
          <tr><?php // modif. 01/2014 traite_ieb
            if($_SESSION['b_cmd_etre_admin'])
            { ?><td class="bleugrascalibri10"></td>
            <?php 
						} // fin modif. 01/2014 traite_ieb?>
            <td colspan="<?php echo $colonne_peut_invalider?$nb_col_a_afficher+($mini?0:1):$nb_col_a_afficher ?>"><?php echo $tab_etat_commande_entete[$etat_commande_val] ?>
            </td>
          </tr>
					<?php 
					}?>
					<tr class="head">
            <?php // modif. 01/2014 traite_ieb
						 //colonne traite_ieb
            if($_SESSION['b_cmd_etre_admin'])
            { ?><td class="bleugrascalibri10"></td>
            <?php 
						} // fin modif. 01/2014 traite_ieb?>

						<td nowrap colspan="2" align="center"><a name="bloc_<?php echo $etat_commande ?>"></a>
            <form name="gestioncommandes_taille_colonnes" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          	<input type="hidden" name="bloc_cmd_ancre" value="bloc_<?php echo $etat_commande ?>">
            <input type="hidden" name="nomformulaire" value="gestioncommandes_taille_colonnes">
            <input  name="b_cmd_petite_taille_colonnes" type="image" src="images/b_taille_colonnes_<?php echo ($_SESSION['b_cmd_petite_taille_colonnes']?"plus":"moins") ?>.jpg" title="Taille des colonnes : <?php echo ($_SESSION['b_cmd_petite_taille_colonnes']?"plus":"moins") ?>">
						</form>
            <?php // 20170412?>
            <br>
            <form name="gestioncommandes_voir_equipement_seul" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          	<input type="hidden" name="bloc_cmd_ancre" value="bloc_<?php echo $etat_commande ?>">
            <input type="hidden" name="nomformulaire" value="gestioncommandes_voir_equipement_seul">
            <input  name="b_cmd_voir_equipement_seul" type="image" src="images/b_cmd_voir_equipement_seul_<?php echo ($_SESSION['b_cmd_voir_equipement_seul']?"non":"oui") ?>.png" title="Taille des colonnes : <?php echo ($_SESSION['b_cmd_petite_taille_colonnes']?"plus":"moins") ?>">
						</form>
            </td>
          	<form name="gestioncommandes_tri_et_voir_col" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
          	<input type="hidden" name="bloc_cmd_ancre" value="bloc_<?php echo $etat_commande ?>">
          	<input type="hidden" name="nomformulaire" value="gestioncommandes_tri_et_voir_col">
             <td nowrap align="center"><input type="image" name="b_cmd_champ_tri#codecommande##<?php if($_SESSION['b_cmd_champ_tri']=='codecommande') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																																			<span class="bleugrascalibri10">N&deg;
																																			<br>int.</span>
						</td>
						<td nowrap align="center"><input type="image" name="b_cmd_champ_tri#referent##<?php if($_SESSION['b_cmd_champ_tri']=='referent') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																			<span class="bleugrascalibri10"><?php if($_SESSION['b_cmd_voir_col#referent']){?>Demandeur<?php }else{?>Dem.<?php }?></span>
																			<br><input type="image"  name="b_cmd_voir_col#referent" img src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_col#referent']?"oui":"non") ?>.png">
						</td>
						<td nowrap align="center"><input type="image" name="b_cmd_champ_tri#numcommande##<?php if($_SESSION['b_cmd_champ_tri']=='numcommande') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																																			<span class="bleugrascalibri10">N&deg; comm<br>miss</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_cmd_champ_tri#datecommande##<?php if($_SESSION['b_cmd_champ_tri']=='datecommande') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																																			Date</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">Objet - Motif</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">Nature</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10"><input type="image" name="b_cmd_champ_tri#libfournisseur##<?php if($_SESSION['b_cmd_champ_tri']=='libfournisseur') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																																			Fournisseur</span>
						</td>
						<td nowrap align="center"><input type="image" name="b_cmd_champ_tri#libcontrat##<?php if($_SESSION['b_cmd_champ_tri']=='libcontrat') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																			<span class="bleugrascalibri10">Imputation<br>budg&eacute;taire</span>
																			<?php if($_SESSION['b_cmd_etre_admin'])
																			{?> <input type="image" name="b_cmd_champ_tri#libeotp##<?php if($_SESSION['b_cmd_champ_tri']=='libeotp') { echo $_SESSION['b_cmd_ordre_tri']=='desc'?'asc':'desc'; }?>" src="images/b_tri_fleche.png">
																			<?php 
																			}?>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">Secr.<br>appui</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">Montant<br>comm.</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">Montant<br>pay&eacute;</span>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">MIGO</span>
																			<br><input type="image"  name="b_cmd_voir_col#migo" img src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_col#migo']?"oui":"non") ?>.png">
						<?php 
            if($_SESSION['b_cmd_etre_admin'])
            { ?>
						</td>
						<td nowrap align="center"><span class="bleugrascalibri10">Justif.<br>contrat</span>
																			<br><input type="image"  name="b_cmd_voir_col#justification" img src="images/b_checked_<?php echo ($_SESSION['b_cmd_voir_col#justification']?"oui":"non") ?>.png">
            <?php 
						}?>
					</form><!-- fin form recherche et tris -->
						</td>
						<?php
						if($mini)
            { ?><td class="bleugrascalibri10" align="left" nowrap><img src="i/actions.png"><img src="i/drisi.png"><?php echo($_SESSION['b_cmd_etre_admin']?'Inv.':'') ?></td>
            <?php 
						}
						else
						{	?> 
            	<td nowrap align="center"><span class="bleugrascalibri10">Actions</span>
            	</td>
            	<td class="bleugrascalibri10">Dem.</td>
            	 <td class="bleugrascalibri10">Resp.</td>
               <td class="bleugrascalibri10">IEB1</td>
               <td class="bleugrascalibri10">Secr.</td>
               <td class="bleugrascalibri10">IEB2</td>
            <?php
            }
						 //colonne invalider
            if($_SESSION['b_cmd_etre_admin'])
            { if($mini){?><?php }else{?><td class="bleugrascalibri10"><span>Inv.</span></td><?php }?>
            <?php 
						}?>
					</tr>
					<?php 	
					$class="even";
					$tab_ordonne_commande_par_etat=$tab_rs_etat_commande[$etat_commande_val];
					foreach($tab_ordonne_commande_par_etat as $num_ordonne=>$row_rs_commande)//$codecmdmiss=$num_ordonne
					{ $numrow++;
            echo affiche_ligne_cmd_ou_miss($row_rs_commande,$num_ordonne,$tab_ordonne_commande_par_etat,$tab_commandemigo, $tab_commandeinventaire, $tab_cmd_statutvisa, $tab_cmd_statutvisa_texte_visa_title,$tab_contexte,$class,$cmd_ancre,$numrow,$mini,$codeuser,$tab_roleuser);
						/* // table des roles et droits de $codeuser et $estreferent+$estresptheme pour cette commande
						$cmd_ou_miss=$row_rs_commande['cmd_ou_miss'];
						$codecmdmiss=$row_rs_commande['codecmdmiss'];
						if($cmd_ou_miss=='commande')
						{ $tab_resp_roleuser=$row_rs_commande['tab_resp_roleuser'];//get_tab_cmd_roleuser($codeuser,$row_rs_commande['codecommande'],$tab_cmd_statutvisa,false,false,true);
							$tab_rolecmduser=$tab_resp_roleuser['tab_roleuser'];
							$estreferent=$tab_resp_roleuser['estreferent'];// user a le role referent mais n'est pas forcément le referent
							$estresptheme=$tab_resp_roleuser['estresptheme'];// user a le role theme mais n'est pas forcément référent : peut etre le gesttheme
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
						// contenu pour chaque colonne visa affichee + droit read/write pour chaque role sous la forme :
						// $tab_contenu_col_role_droit['referent']['colonne']='visa appose', 'valider', 'brancher','sablier' ou 'n/a'
						// $tab_contenu_col_role_droit['referent']['droit']='read', 'write'
						//

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
								// dans $tab_contenu_col_role_droit pour son role le plus 'élevé'
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
						{ if( $cmd_ou_miss=='commande' && (array_key_exists('secrsite',$tab_rolecmduser) || droit_acces($tab_contexte))
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
						$ligne.='<tr class="'.($cmd_ancre==$codecmdmiss?'marked':(($cmd_ou_miss=='mission')?'mission':($row_rs_commande['estavoir']=='oui'?'avoir':$class))).'" id="t'.substr($cmd_ou_miss,0,1).($row_rs_commande['estavoir']=='oui'?'a':'').$numrow.'" onClick="m(this)" '.(($_SESSION['b_cmd_etre_admin'] || $droitmodif=="write" || $droitmodif_inventaire)?'onDblClick="e(\''.$codecmdmiss.'\')"':'').'>';
						if($_SESSION['b_cmd_etre_admin'])
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
											if((array_key_exists('secrsite',$tab_rolecmduser) || $peut_etre_admin || droit_acces($tab_contexte))
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
											if((array_key_exists('secrsite',$tab_rolecmduser) || $peut_etre_admin || droit_acces($tab_contexte))
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
						$ligne.=($mini?'':'<td>');*/
						/* //duplication possible pour tous sauf le referent s'il n'est que referent
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
						// else
						//{ $ligne.=$mini?'':'<td>';
						//	if($mini)
						//	{ $nomimagemini.='z';
						//	}
						//	else
						//	{ $ligne.='<img src="i/z1.gif">';
						//	}
						//} 
						if($mini)
						{ if(($row_rs_commande['codenature']=='06' || $row_rs_commande['codenature']=='12') && $codestatutvisamini=='04') 
							{ $ligne.='<img src="i_res/'.$nomimagemini.'.png" name="'.$codecmdmiss.$nomimagemini.'#'.$codestatutvisamini.'##msrh'.'" id="'.$codecmdmiss.'" onClick="f(this,event)">';//.($codestatutvisamini==''?'>':(' onClick="p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisamini.'\')">'));
							}
							else
							{ $ligne.='<img src="i_res/'.$nomimagemini.'.png" name="'.$codecmdmiss.$nomimagemini.'#'.$codestatutvisamini.$confirme_validation_mini.'" id="'.$codecmdmiss.'" onClick="f(this,event)">';//.($codestatutvisamini==''?'>':(' onClick="p(\'v\',\''.$codecmdmiss.'\',\''.$codestatutvisamini.'\')">'));
							}
							$ligne.='</td>';
						}
	
						$ligne.='</tr>';$nbtotalcarligne+=(strlen($ligne)+5);
						echo $ligne.chr(13);
					*/
					}//fin for bloc d'un etat_commande
						
					
				}//finsi $_SESSION['b_cmd_voir_'.$etat_commande_val]
			}// fin for des etat_commande	?>
			 </form>
<?php				}
?>
			</table><!-- table17-->
		</td>
	</tr>
</table>
<script>
document.getElementById("chargementencours").className="cache";
</script>
<!-- table18-->
</body>
</html>
<?php 
if(isset($rs_cmd_suivi_developpement_info)) mysql_free_result($rs_cmd_suivi_developpement_info);
if(isset($rs_solde)) mysql_free_result($rs_solde);
if(isset($rs)) mysql_free_result($rs);
if(isset($rs_cmdliee)) mysql_free_result($rs_cmdliee);
if(isset($rs_commandepj)) mysql_free_result($rs_commandepj);
if($GLOBALS['mode_exploit']=='test' && $afficheduree)
{ $timefin=microtime(true);
	echo '<br>'."duree traitement html : ".(($timefin-$timedeb))." sec"; 
 	echo '<br>usage memoire '.memory_get_usage ();
	foreach($tab_etat_commande as $key=>$etat_commande_val)
	{ echo '<br>'.$etat_commande_val.' : '.count($tab_rs_etat_commande[$etat_commande_val]);
	}
	echo '<br>Dur&eacute;e du traitement : '.sprintf('%01.2f',round((microtime(true)-$temps_debut),2)).' secondes';
	echo '<br>Nb car lignes envoyes : '.$nbtotalcarligne;
}
?>