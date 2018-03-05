<?php require_once('_const_fonc.php'); ?>
<?php
/* error_reporting(0);
try
{ */
$codeuser=deconnecte_ou_connecte();
$tab_infouser=get_info_user($codeuser);
$tab_contexte=array('prog'=>'edit_individu','codeuser'=>$codeuser);
$admin_bd =(strtolower($tab_infouser['login'])==$GLOBALS['admin_bd']);
/* if($admin_bd)  
	{ foreach($_POST as $postkey=>$postval)
		{ echo $postkey.'='.$postval."<br>";
		}   
	 foreach($_GET as $postkey=>$postval)
		{ echo $postkey.'='.$postval."<br>";
		}
	}*/ 

$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forcément le referent
$estresptheme=false;// user a le role theme mais pas forcément référent : peut aussi etre le gesttheme
// table des nom, prenom et roles+resp de $codeuser
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$estgesttheme=array_key_exists('theme',$tab_roleuser) && !$estresptheme;// 20161213
$est_admin=array_key_exists('du',$tab_roleuser)||array_key_exists('admingestfin',$tab_roleuser)||$admin_bd || droit_acces($tab_contexte);// 20161213
$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$information_defaut="";//information user de défauts éventuels
$affiche_succes=false;//affichage d'un message suite a un enregistrement (sans erreur) 
$message_resultat_affiche="";
$nouveausejour=false;
$form_fiche_dossier_pers='form_fiche_dossier_pers';// formulaire d'edition du dossier pers
// affichage du formulaire de choix permanent/corps 
$avant_choix_corps=false;
// apres reception du formulaire de choix permanent/corps 
$apres_choix_corps=false;
// $codecorpschoisi = envoyé dans le POST  de choix permanent/corps 
$permanent='';
// $codecorpschoisi = envoyé dans le POST  de choix permanent/corps 
$codecorpschoisi='';
$etudiant_ou_exterieur='';
$codelibcat='';
$codecorps='';
$codelibtypestage='';
// $ancre permet de positionner la page dans le navigateur a #$ancre
//$ancre="";
// $numemploi_leplusrecent : le numemploi de l'emploi le plus recent en date != dernier numemploi  
$numemploi_leplusrecent='';
// affichage des zones d'un nouvel emploi pour saisie : si dde creation explicite d'un nouvel emploi (submit_afficher_nouvel_emploi)
// ou si creer/nouveau sejour. POST['ajouter_emploi']=oui/non selon $afficher_nouvel_emploi=true/false
$afficher_nouvel_emploi=false;
//$ajouter_emploi=ajout d'un nouvel emploi initialisé dans le POST['ajouter_emploi'](oui/non)
$ajouter_emploi=false;
// $modifier_emploi=true si $submit=='submit_modifier_emploi' || $submit=='submit_enregistrer_emploi'
$modifier_emploi=false;
$numemploi_a_modifier='';
// $supprimer_emploi=true si $submit='submit_supprimer_emploi'
$supprimer_emploi=false;
$numemploi_a_supprimer='';
$lien_emploi_precedent=false;
$lien_emploi_suivant=false;
//pjemploi
$supprimer_une_pj=false;// $supprimer_une_pj=true si $submit='submit_supprimer_une_pj'
//fin pjemploi
$codesujet='';// codesujet choisi éventuellement a la saisie du formulaire
// codesujet éventuellement enregistré pour l'individu
$codesujetenregistre='';

//sujet verrouille
$sujet_verrouille_avant='';//etat verrouille ou non avant l'envoi du formulaire ?
$sujet_verrouille=''; //nouvel etat verrouille ou non
// attestation_stage
$attestation_stage=''; //nouvel etat attestation_stage ou non
$sujet_valide_par_theme=false;//condition pour avoir acces a la case a cocher (image submit) sujet_verrouille
$duree_mois_these=0;//duree en mois calculée : date soutenance-datedeb sejour
$rs_individutheme=null;
$row_rs_individutheme=array();
if($GLOBALS['avecequipe'])
{ $row_rs_individuequipe=array();
}
$tab_dureesejour_amj=array();
$datedeb_theme='';
//types de sujets concernés par type d'étudiant
$tab_etudiant_ou_exterieur_typesujet=array('STAGIAIRE'=>'02','DOCTORANT'=>'03','POSTDOC'=>'04','EXTERIEUR'=>'05');
$tab_rs_dir=array();//directeurs de these
$tab_cmd_gratification=array();//gratification attestation stage

// insert (creation individu ou sejour ou erreur) : valeurs par défaut, pour tous les champs : contrairement a l'update, il faut renseigner tous les champs par défaut
// 20170315 : ajout hors_effectif 
$tab_champs_ouinon_defaut=array('cotutelle'=>'non','piece_lettre_motiv'=>'non','piece_assurance'=>'non','resp_civile_inutile'=>'non','conv_accueil_inutile'=>'non',
																'missioncomp'=>'non','hdr'=>'non','etude_postdoc'=>'non','associe'=>'non','hors_effectif'=>'non',
																'afficheannuaire'=>'oui','liste_diff'=>'non','proc_accueil'=>'non','proc_depart'=>'non',
																'badge_acces_estrendu'=>'non','cle_estrendu'=>'non');

$tab_champs_date=array( 'date_naiss' =>  array("lib" => "Date de naissance","jj" => "","mm" => "","aaaa" => ""),
												'datedeb_sejour_prevu' => array("lib" => "Date de d&eacute;but de s&eacute;jour pr&eacute;vue","jj" => "","mm" => "","aaaa" => ""),
												'datefin_sejour_prevu' =>  array("lib" => "Date de fin de s&eacute;jour pr&eacute;vue","jj" => "","mm" => "","aaaa" => ""),
												'datedeb_sejour' =>  array("lib" => "Date de d&eacute;but de s&eacute;jour","jj" => "","mm" => "","aaaa" => ""),
												'datefin_sejour' =>  array("lib" => "Date de fin de s&eacute;jour","jj" => "","mm" => "","aaaa" => ""),
												'date_demande_fsd'  =>  array("lib" => "Date demande acc&egrave;s","jj" => "","mm" => "","aaaa" => ""),
												'date_autorisation'  =>  array("lib" => "Date autorisation","jj" => "","mm" => "","aaaa" => ""),
												'date_demande_modification_fsd'  =>  array("lib" => "Date demande modification fsd","jj" => "","mm" => "","aaaa" => ""),
												'datedeb_emploi' =>  array("lib" => "Date de d&eacute;but de contrat","jj" => "","mm" => "","aaaa" => ""),
												'datefin_emploi' =>  array("lib" => "Date de fin de contrat","jj" => "","mm" => "","aaaa" => ""),
												'date_preminscr' =>  array("lib" => "Date de premi&egrave;re inscription","jj" => "","mm" => "","aaaa" => ""),
												'date_soutenance' =>  array("lib" => "Date de soutenance","jj" => "","mm" => "","aaaa" => ""),
												'date_suivi_comite_selection_12_mois' =>  array("lib" => "Date de suivi comit&eacute; de selection &agrave; 12 mois","jj" => "","mm" => "","aaaa" => ""),
												'date_suivi_comite_selection_30_mois' =>  array("lib" => "Date de suivi comit&eacute; de selection &agrave; 30 mois","jj" => "","mm" => "","aaaa" => ""),
												'dateemploi_postdoc' =>  array("lib" => "Date emploi postdoc","jj" => "","mm" => "","aaaa" => ""),
												'date_hdr'  =>  array("lib" => "Date HDR","jj" => "","mm" => "","aaaa" => "")
												);
$tab_champs_heure_mn=	array('heure_soutenance' =>  array("lib" => "Heure soutenance","hh" => "","mn" => ""),
														'heure_hdr' =>  array("lib" => "Heure HDR","hh" => "","mn" => ""));
$tab_champs_numerique=array('montantfinancement' => array('lib' => 'Montant du financement'),/* ,'string_format'=>'%01.2f' */
														'num_inscr' =>  array('lib' => 'Num&eacute;ro d&rsquo;inscription'),
														'duree_mois_these' =>  array('lib' => 'Dur&eacute;e en mois'),
														'montant_mensuel_charge' =>  array('lib' => 'montant mensuel charge','string_format'=>'%01.2f'),
														'montant_mensuel_brut' =>  array('lib' => 'montant mensuel brut','string_format'=>'%01.2f'),
														'montant_mensuel_net' =>  array('lib' => 'montant mensuel net','string_format'=>'%01.2f'),
														'quotite_admin' =>  array('lib' => 'quotit&eacute; administrative'),
														'quotite_unite' =>  array('lib' => 'quotit&eacute; unit&eacute;'),
														'num_insee' =>  array('lib' => 'n&deg; INSEE'));

// b_img_suivi_doctorant_plus_ou_moins : si ce bouton submit est envoye on shifte l'affichage du suivi doctorant
$_SESSION['b_img_suivi_doctorant_plus_ou_moins']=isset($_POST['b_img_suivi_doctorant_plus_ou_moins'])?$_POST['b_img_suivi_doctorant_plus_ou_moins']:'b_plus';

// Par défaut, codeindividu est initialise 
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
// numero du sejour le plus recent pour cet individu
$numsejour_leplusrecent=isset($_GET['numsejour_leplusrecent'])?$_GET['numsejour_leplusrecent']:(isset($_POST['numsejour_leplusrecent'])?$_POST['numsejour_leplusrecent']:"");
// le numsejour concerné apres création individu ou séjour : passage en modification
$numsejour=isset($_GET['numsejour'])?$_GET['numsejour']:(isset($_POST['numsejour'])?$_POST['numsejour']:"");
$ind_ancre=isset($_GET['ind_ancre'])?$_GET['ind_ancre']:(isset($_POST['ind_ancre'])?$_POST['ind_ancre']:"");

//$numemploi=emploi ouvert a la saisie
$numemploi=isset($_GET['numemploi'])?$_GET['numemploi']:(isset($_POST['numemploi'])?$_POST['numemploi']:"");
$etat_individu=isset($_GET['etat_individu'])?$_GET['etat_individu']:(isset($_POST['etat_individu'])?$_POST['etat_individu']:"");
$action=isset($_GET['action'])?$_GET['action']:(isset($_POST['action'])?$_POST['action']:"");
$codestatutpers=isset($_GET['codestatutpers'])?$_GET['codestatutpers']:(isset($_POST['codestatutpers'])?$_POST['codestatutpers']:"");
$permanent=isset($_GET['permanent'])?$_GET['permanent']:(isset($_POST['permanent'])?$_POST['permanent']:"");
$codecorps=isset($_GET['codecorps'])?$_GET['codecorps']:(isset($_POST['codecorps'])?$_POST['codecorps']:"");
$codetypestage='';
if($codecorps=='52')//type de stage pour STAGIAIRE uniquement
{ $codetypestage=isset($_GET['codetypestage'])?$_GET['codetypestage']:(isset($_POST['codetypestage'])?$_POST['codetypestage']:"");
}
//$ue=isset($_GET['ue'])?$_GET['ue']:(isset($_POST['ue'])?$_POST['ue']:"");
$etudiant_ou_exterieur=isset($_GET['etudiant_ou_exterieur'])?$_GET['etudiant_ou_exterieur']:(isset($_POST['etudiant_ou_exterieur'])?$_POST['etudiant_ou_exterieur']:"");
$sujet_verrouille_avant=(isset($_POST['sujet_verrouille'])?$_POST['sujet_verrouille']:"");// champ hidden = valeur qu'a sujet_verrouille
$sujet_verrouille=$sujet_verrouille_avant;//initialisation : si submit_sujet_verrouille, ces variables auront des valeurs inversees oui/non
$attestation_stage=(isset($_POST['attestation_stage'])?$_POST['attestation_stage']:"");
// PG fsd 20160120
$generer_classeur_fsd=false;
// PG fsd 20160120
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppléant"
// défini par $estreferent et $estresptheme
$tab_statutvisa=get_statutvisa();
$estreferent=false;// user a le role referent mais n'est pas forcément referent
$estresptheme=false;// user a le role theme mais pas forcément resptheme : peut etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,$codeindividu,$numsejour,$tab_statutvisa,$estreferent,$estresptheme);//renvoie table des roles
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];

$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$est_admin=array_key_exists('srh',$tab_roleuser) || array_key_exists('du',$tab_roleuser)||array_key_exists('admingestfin',$tab_roleuser)||$admin_bd || droit_acces($tab_contexte);// 20161213

// les traitements d'update sont effectues sur l'ensemble des champs des tables et sont traites s'ils sont dans le post
// mais les champs sous forme de case a cocher ne sont envoyes dans le post que s'ils sont On : il faut etre sur de les
// avoir proposés pour en déduire qu'ils sont a On
// => contrairement aux autres champs, il faut les mentionner explictement comme etant dans le formulaire.
// La valeur par défaut est precisee dans $tab_champs_ouinon_defaut

// Les individus qui peuvent acceder a ce contenu ont tous acces aux champs suivants :
// 20170315 : ajout hors_effectif 
$tab_champs_ouinon=array('piece_assurance','missioncomp','hdr','cotutelle','associe','hors_effectif','badge_acces_estrendu','cle_estrendu');
// les champs suivants sont envoyes et, eventuellement recus, sur la condition suivante
if($sujet_verrouille_avant=='oui' && (array_key_exists('srh',$tab_roleuser) || $est_admin))
{ $tab_champs_ouinon[]='etude_postdoc';
	$tab_champs_ouinon[]='piece_lettre_motiv';
}
// 
if(array_key_exists('srh',$tab_roleuser) || $est_admin)
{ $tab_champs_ouinon[]='resp_civile_inutile';
	$tab_champs_ouinon[]='conv_accueil_inutile';
}
if($admin_bd)
{ $tab_champs_ouinon=array_merge($tab_champs_ouinon,array('afficheannuaire','liste_diff','proc_accueil','proc_depart'));
}
$submit="";$submit_val="";
foreach($_POST as $postkey=>$postval)
{ if(strlen($postkey)>=6 && substr($postkey,0,6)=="submit")
	{ $submit=$postkey;
		$posdoublediese=strpos($postkey,'##');
		if($posdoublediese!==false)
		{ $submit=substr($postkey,0,$posdoublediese);
			$posdiese=strpos($submit,"#");
			if($posdiese!=false)
			{ $submit_val=substr($submit,$posdiese+1);//peut etre un numero ou autre (oui/non pour submit_verrouiller_sujet)
				$submit=substr($submit,0,$posdiese);
			}
		}
	}
}

if(isset($_POST['ajouter_emploi']) && $_POST['ajouter_emploi']=='oui')//reception des donnees pour ajout d'un nouvel emploi
{ $ajouter_emploi=true;
	//$ancre="#contrats";
}
//pjemploi
// Dans le cas d'une suppression le numcatpj(numemploi est initialise !='' car l'emploi a deja ete enregistre
if($submit=='submit_supprimer_une_pj')
{ $supprimer_une_pj=true;
	$tab_pj_a_supprimer=explode ('_',$submit_val);
	$codelibcatpj=$tab_pj_a_supprimer[0];
	$numcatpj=$tab_pj_a_supprimer[1];
	$codetypepj=$tab_pj_a_supprimer[2];
}
//fin pjemploi

if($submit=='submit_afficher_nouvel_emploi')//submit du bouton image nouvel emploi : affichage d'un nouvel emploi
{ $afficher_nouvel_emploi=true;
	//$ancre="#contrats";
}
if($submit=='submit_modifier_emploi' || $submit=='submit_enregistrer_emploi')
{ $modifier_emploi=true;
	$numemploi_a_modifier=$submit_val;
	//$ancre="#contrats";
}

if($submit=='submit_supprimer_emploi')
{ $supprimer_emploi=true;
	$numemploi_a_supprimer=$submit_val;
	if($numemploi_a_supprimer=='')// l'emploi nouvellement affiché
	{ $ajouter_emploi=false;
	}
	//$ancre="#contrats";
}

if($submit=='submit_verrouiller_sujet')
{ $sujet_verrouille=($sujet_verrouille=='non'?'oui':'non');//le bouton=submit_verrouiller_sujet#oui ou non## a la reception. Il a ete clique => oui
}
if($submit=='submit_attestation_stage')
{ $attestation_stage=($attestation_stage=='non'?'oui':'non');//le bouton=submit_attestation_stage#oui ou non## a la reception. Il a ete clique => oui
}
if(isset($_POST['submit_generer_classeur_fsd_x']))
{ $generer_classeur_fsd=true;
}
// 20170322
$editer_sujet=false;
if(isset($_POST['submit_editer_sujet_x']))
{ $editer_sujet=true;
}
// 20170322 fin
$editFormAction = $_SERVER['PHP_SELF'];

// Traitement des parametres de la requete
// 3 cas de reception de parametres => traitement et affichage de formulaire
// 1. Si demande de création de dossier ou de nouveau séjour : preparation des parametres pour le formulaire de choix de statutpers, corps
// pour l'affichage de données, une création est assimilée a un nouveau séjour : $nouveausejour=true;

if(isset($_POST['submit_creer_x']) || isset($_POST['submit_ajouter_sejour_x']))
{ $avant_choix_corps=true;
	$nouveausejour=true;
}
// 2. Si reception du formulaire de choix codestatutpers et codecorps
if((isset($_POST["MM_choix"])) && ($_POST["MM_choix"] == "form_fiche_dossier_pers_choix_corps"))
{ $tab_controle_et_format=array('tab_champs_date' =>  array( 'datedeb_sejour' => array("lib" => "Date de d&eacute;but de s&eacute;jour","jj" => "","mm" => "","aaaa" => ""),
				 												'datefin_sejour' =>  array("lib" => "Date de fin de s&eacute;jour","jj" => "","mm" => "","aaaa" => "")));
	
	$erreur.=controle_form_fiche_dossier_pers_choix_corps($_POST,$tab_controle_et_format);
	if($erreur=='')
	{ $apres_choix_corps=true;
		$nouveausejour=true;
		$permanent=($codestatutpers=='01'?'oui':'non');
		// Les dates de sejour ont ete renseignees dans le choix corps : les $_POST sont positionnes pour passer a l'etape suivante apres_choix_corps
		foreach(array('datedeb_sejour','datefin_sejour') as $champ_date)
		{ $_POST[$champ_date.'_prevu']=$_POST[$champ_date];
		}
	}
	else // retour a l'ecran de choix
	{ $avant_choix_corps=true;
		$nouveausejour=true;
	}
}
// 3. Vérification puis si pas d'erreur, Enregistrement des données du formulaire : si création dossier codeindividu="" sinon codeindividu existe déja
if((isset($_POST["MM_update"])) && ($_POST["MM_update"] == $form_fiche_dossier_pers))
{ //directeurs de these et taux d'encadrement
	foreach($_POST as $postkey=>$codedir)
	{ if(strpos($postkey,'codedir#')!==false && $codedir!='' )
		{ $numordre=substr($postkey,strlen('codedir#'));
			if(!in_array($codedir,$tab_rs_dir))//enleve tout doublon de directeur choisi par erreur
			{ $tab_rs_dir[$numordre]=$codedir;
			}
		}
	}
	foreach($_POST as $postkey=>$taux_encadrement)
	{ if(strpos($postkey,'taux_encadrement#')!==false)
		{ $numordre=substr($postkey,strlen('taux_encadrement#'));
			if(array_key_exists($numordre,$tab_rs_dir))
			{ $tab_rs_dir[$numordre]=array('codedir'=>$tab_rs_dir[$numordre],'taux_encadrement'=>$taux_encadrement);
				$tab_champs_numerique[$postkey]=array('lib' => 'taux encadrement');
			}
		}
	}
	// Vérifications saisies utilisateur : pour l'instant pour aller vite, seules les informations essentielles sont vérifiées, le reste est fait par le script de contrôle javascript
	// Longueurs si textearea et type varchar (pas de limite champs text)
	$tab_controle_et_format=array('tab_champs_date' =>  $tab_champs_date,'tab_champs_heure_mn' =>  $tab_champs_heure_mn,'tab_champs_numerique' =>  $tab_champs_numerique);
	$erreur.=controle_form_fiche_dossier_pers($_POST,$tab_controle_et_format);
	if(isset($_POST['date_hdr_aaaa']) && $_POST['date_hdr_aaaa']!='' && !isset($_POST['hdr']))
	{ $erreur.="<br>Ann&eacute;e HDR renseign&eacute;e : HDR doit &ecirc;tre coch&eacute;e";
	}
	// login unique si pas création : le login est ou non envoyé dans le POST. S'il est envoyé, il doit etre <>'' et ne pas exister
	if($action!="creer" and isset($_POST['login']))
	{ $query_rs_individu ="SELECT nom,prenom from individu".
												" WHERE codeindividu <>".GetSQLValueString($codeindividu, "text").
												" AND login=".GetSQLValueString($_POST['login'], "text");
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		if($row_rs_individu =mysql_fetch_assoc($rs_individu))
		{ $erreur.="<br>Login ".$_POST['login']." d&eacute;j&agrave; utilis&eacute; pour ".$row_rs_individu['prenom'].' '.$row_rs_individu['nom'];
		}
	}
	// code zrr unique
	if(isset($_POST['numdossierzrr']) && $_POST['numdossierzrr']!='')
	{ $query_rs = "SELECT individu.codeindividu,numsejour,nom,prenom".
								" from individusejour,individu".
								" where individusejour.codeindividu=individu.codeindividu".
								" and individu.codeindividu<>".GetSQLValueString($codeindividu, "text")." and numsejour<>".GetSQLValueString($numsejour, "text")." and numdossierzrr=".GetSQLValueString($_POST['numdossierzrr'], "text");
		$rs = mysql_query($query_rs) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs))
		{ $erreur.="<br>Num. dossier zrr ".$_POST['numdossierzrr']." d&eacute;j&agrave; utilis&eacute; pour ".$row_rs['prenom'].' '.$row_rs['nom'];
		}
	}
	
	// affectation d'au moins un theme obligatoire
	$themechecked=false;
	foreach($_POST as $postkey=>$postval)
	{ if(strpos($postkey,'codetheme#')!==false)
		{ $themechecked=true;
			$row_rs_individutheme[substr($postkey,strlen('codetheme#'))]=substr($postkey,strlen('codetheme#'));
		}
	}
	if(!$themechecked)
	{ $erreur.='<br>'.'Aucun(e) '.$GLOBALS['libcourt_theme_fr'].' s&eacute;lectionn&eacute; !<br>'; 
	}
	//modif pour cohabit GT et dept debut
	else if(date('Y/m/d')<$GLOBALS['date_bascule_gt_vers_dept'])
	{ $estgt=false;
		$estdept=false;
		foreach($row_rs_individutheme as $codetheme)
		{ if($codetheme=='00' && count($row_rs_individutheme)==1)
			{ $estgt=true;
				$estdept=true; 
			}
			if($codetheme>='01' && $codetheme<='05')
			{ $estgt=true;
			}
			else if($codetheme>='06' && $codetheme<='08')
			{ $estdept=true;
			}
		}
		if(!($estgt && $estdept))
		{ $erreur.='<br>'.'Il faut un GT et un Dept. !<br>'; 
		} 
	}
	if($GLOBALS['avecequipe'])
	{ foreach($_POST as $postkey=>$postval)
		{ if(strpos($postkey,'codeequipe#')!==false)
			{ $row_rs_individuequipe[substr($postkey,strlen('codeequipe#'))]=substr($postkey,strlen('codeequipe#'));
			}
		}
	}

	if(($action=='modifier' && ($codeindividu=='' || $numsejour=='')) || ($action=='ajouter_sejour'  && $codeindividu==''))
	{ $erreur.="Modification d'individu ou de s&eacute;jour sans n&deg;";
	}
	
//$erreur='erreur forcée pour test';
	if($erreur=="")
	{ // pas de maj de datedeb_sejour_prevu si date_demande_fsd!='' ou date_autorisation!=''
	  $affiche_succes=true;// pas d'erreur : affichage du message d'enregistrement effectué
		$message_resultat_affiche='Enregistrement effectu&eacute; avec succ&egrave;s.';
		$maj_date_sejour_prevu=true;
		$_POST['datedeb_sejour_prevu']=isset($_POST['datedeb_sejour_prevu'])?$_POST['datedeb_sejour_prevu']:$_POST['datedeb_sejour'];
		$_POST['datefin_sejour_prevu']=isset($_POST['datefin_sejour_prevu'])?$_POST['datefin_sejour_prevu']:$_POST['datefin_sejour'];
		// seuls les roles srh, admingestfin,du et l'admin bd ont le droit de modifier les dates de sejour prevues meme si date_demande_fsd!='' or date_autorisation!='' 
		if( !array_key_exists('srh',$tab_roleuser) && !$est_admin)
    { $query_rs="select date_demande_fsd,date_autorisation from individusejour".
								" where codeindividu=".GetSQLValueString($codeindividu, "text").
								" and numsejour=".GetSQLValueString($numsejour, "text").
								" and (date_demande_fsd<>'' or date_autorisation<>'')";
			$rs=mysql_query($query_rs) or die(mysql_error());
			if($row_rs=mysql_fetch_assoc($rs))
			{ $maj_date_sejour_prevu=false;
			}
		}
		// date deb sejour = date inscr si pas vide
		if(isset($_POST['datedeb_sejour_jj']) && isset($_POST['date_preminscr_jj']))//si datefin et date_preminscr dans le form  
		{ if($_POST['date_preminscr_jj'].$_POST['date_preminscr_mm'].$_POST['date_preminscr_aaaa']!="" && $_POST['datedeb_sejour_aaaa'].$_POST['datedeb_sejour_mm'].$_POST['datedeb_sejour_jj'] != $_POST['date_preminscr_aaaa'].$_POST['date_preminscr_mm'].$_POST['date_preminscr_jj'])
			{ $warning.='<br>'.$tab_champs_date['datedeb_sejour']['lib'].' ('.$_POST['datedeb_sejour_jj'].'/'.$_POST['datedeb_sejour_mm'].'/'.$_POST['datedeb_sejour_aaaa'].') remplac&eacute;e par '.$tab_champs_date['date_preminscr']['lib'].' ('.$_POST['date_preminscr_jj'].'/'.$_POST['date_preminscr_mm'].'/'.$_POST['date_preminscr_aaaa'].')';
				$_POST['datedeb_sejour_jj']=$_POST['date_preminscr_jj'];
				$_POST['datedeb_sejour_mm']=$_POST['date_preminscr_mm'];
				$_POST['datedeb_sejour_aaaa']=$_POST['date_preminscr_aaaa'];
			}
		}
		// date fin sejour = date soutenance si pas vide
		if(isset($_POST['datefin_sejour_jj']) && isset($_POST['date_soutenance_jj']))//si datefin et datesoutenance dans le form  
		{ if($_POST['date_soutenance_jj'].$_POST['date_soutenance_mm'].$_POST['date_soutenance_aaaa']!="" && $_POST['datefin_sejour_aaaa'].$_POST['datefin_sejour_mm'].$_POST['datefin_sejour_jj'] != $_POST['date_soutenance_aaaa'].$_POST['date_soutenance_mm'].$_POST['date_soutenance_jj'])
			{ $warning.='<br>'.$tab_champs_date['datefin_sejour']['lib'].' ('.$_POST['datefin_sejour_jj'].'/'.$_POST['datefin_sejour_mm'].'/'.$_POST['datefin_sejour_aaaa'].') remplac&eacute;e par '.$tab_champs_date['date_soutenance']['lib'].' ('.$_POST['date_soutenance_jj'].'/'.$_POST['date_soutenance_mm'].'/'.$_POST['date_soutenance_aaaa'].')';
				$_POST['datefin_sejour_jj']=$_POST['date_soutenance_jj'];
				$_POST['datefin_sejour_mm']=$_POST['date_soutenance_mm'];
				$_POST['datefin_sejour_aaaa']=$_POST['date_soutenance_aaaa'];
			}
		}
		// 20170404
		if(isset($_POST['datefin_sejour_jj']) && isset($_POST['datefin_sejour_prevu_jj']) && $_POST['datefin_sejour_jj']!='' && $_POST['datefin_sejour_prevu_jj']=='')
		{ $warning.='<br>'.$tab_champs_date['datefin_sejour_prevu']['lib'].' vide alors que '.$tab_champs_date['datefin_sejour']['lib'].' renseign&eacute;e';
		}
		if(isset($_POST['datefin_sejour_jj']) && isset($_POST['datefin_sejour_prevu_jj']) && $_POST['datefin_sejour_jj']=='' && $_POST['datefin_sejour_prevu_jj']!='')
		{ $warning.='<br>'.$tab_champs_date['datefin_sejour']['lib'].' vide alors que '.$tab_champs_date['datefin_sejour_prevu']['lib'].' renseign&eacute;e';
		}
		// 20170404 fin		
 		// calcul duree en mois et message d'information si modif
		if(isset($_POST['date_soutenance_jj']) && $_POST['date_soutenance_mm']!='' && $_POST['date_soutenance_aaaa']!=''
			&& isset($_POST['datedeb_sejour_jj']) && $_POST['datedeb_sejour_mm']!='' && $_POST['datedeb_sejour_aaaa']!=''
				&& isset($_POST['duree_mois_these']))
		{	$duree_mois_these=($_POST['date_soutenance_aaaa']-$_POST['date_preminscr_aaaa']-1)*12+(12-$_POST['date_preminscr_mm'])+$_POST['date_soutenance_mm'];
			if($_POST['duree_mois_these']!=$duree_mois_these)
			{ $information_defaut.='<br>'."La dur&eacute;e en mois calcul&eacute;e est de ".$duree_mois_these." au lieu de ".$_POST['duree_mois_these'];
			}
		}

		// Les dates d'emploi ne sont pas forcement dans le formulaire (pour un permanent) => copie de celle de datedeb_sejour dans les autres
		// la datedeb_emploi doit etre renseignée avec une valeur!=''
		// la date de début de sejour a ete initialisée avant
		if(!isset($_POST['datedeb_emploi_jj']) || (isset($_POST['datedeb_emploi_jj']) && $_POST['datedeb_emploi_jj']==''))
		{ $ch='datedeb_sejour';
			foreach(array('jj','mm','aaaa') as $sufx)
			{ $_POST['datedeb_emploi_'.$sufx]=$_POST[$ch.'_'.$sufx];
			}
		}
		if(!isset($_POST['datefin_emploi_jj']) || (isset($_POST['datefin_emploi_jj']) && $_POST['datefin_emploi_jj']==''))
		{ foreach(array('jj','mm','aaaa') as $sufx)
			{ $_POST['datefin_emploi_'.$sufx]=$_POST['datefin_sejour_'.$sufx];
			}
		}
		// par défaut, actuellement sans édition de datedeb_theme, datedeb_theme=datedeb_sejour
	  $datedeb_theme=jjmmaaaa2date($_POST['datedeb_sejour_jj'],$_POST['datedeb_sejour_mm'],$_POST['datedeb_sejour_aaaa']);
		// seules certaines tables auront un enreg ajouté selon les cas suivants
		$tables=array('individu','individusejour','individuemploi');
		//si creation : insertion d'un nouvel enregistrement avec les champs mini dans 'individu','individusejour','individuemploi'
		if($action=="creer" || $action=="ajouter_sejour" || ($action=='modifier' && $ajouter_emploi))
		{ if($action=="creer")
			{ mysql_query("START TRANSACTION") or  die(mysql_error());
				$rs_seq_number=mysql_query("select currentnumber from seq_number where nomtable='individu'") or  die(mysql_error());
				$row_seq_number=mysql_fetch_assoc($rs_seq_number);
				$codeindividu=$row_seq_number['currentnumber'];
				$codeindividu=str_pad((string)((int)$codeindividu+1), 5, "0", STR_PAD_LEFT);  
				mysql_query("update seq_number set currentnumber=".GetSQLValueString($codeindividu, "text")." where nomtable='individu'") or  die(mysql_error());
				//mysql_free_result($rs_seq_number); // ressource inconnue $rs_seq_number a l'execution php !!!
				mysql_query("COMMIT") or  die(mysql_error());
				mysql_query("SET AUTOCOMMIT = 1") or  die(mysql_error());
				// insertion d'enregistrement avec champs remplis et les autres="" dans les tables 'individu','individusejour','individuemploi'
				// une fois les insertions faites, les autres champs reçus seront valués en modification
				$tables=array('individu','individusejour','individuemploi');
				if($codecorps=='51')//doctorant
				{ $tables[]=('individuthese');
				}
				$numsejour='01';$numemploi='01';
			}
			else if($action=="ajouter_sejour")
			{ // dernier numero de sejour pour cet individu : ce dernier sejour n'est pas forcement le plus recent
				$rs_seq_number=mysql_query("SELECT max(numsejour) as numsejour_dernier from individusejour where codeindividu=".GetSQLValueString($codeindividu, "text")) or  die(mysql_error());
				$row_seq_number=mysql_fetch_assoc($rs_seq_number);
				$numsejour=str_pad((string)(int)($row_seq_number['numsejour_dernier']+1), 2, "0", STR_PAD_LEFT);
				
				// Pas d'ajout d'emploi systématique : un emploi en cours peut exister d'un autre séjour
				if($ajouter_emploi || $permanent=='oui')//on ajoute un emploi si permanent : la ligne n'est pas affichée mais contient codeetab
				{ $rs_seq_number=mysql_query("SELECT max(numemploi) as numemploi_dernier from individuemploi".
																		 " where codeindividu=".GetSQLValueString($codeindividu, "text")) or  die(mysql_error());
					$row_seq_number=mysql_fetch_assoc($rs_seq_number);
					$numemploi=str_pad((string)(int)($row_seq_number['numemploi_dernier']+1), 2, "0", STR_PAD_LEFT);
					$tables=array('individusejour','individuemploi');
				}
				else
				{ $tables=array('individusejour');
				}

				if($codecorps=='51')//doctorant
				{ $tables[]=('individuthese');
				}
			}
			else if($action=='modifier' && $ajouter_emploi)
			{ // dernier numero d'emploi de ce sejour pour cet individu : ce dernier emploi n'est pas forcement le plus recent
				$rs_seq_number=mysql_query("SELECT max(numemploi) as numemploi_dernier from individuemploi".
																	 " where codeindividu=".GetSQLValueString($codeindividu, "text")) or  die(mysql_error());
				$row_seq_number=mysql_fetch_assoc($rs_seq_number);
				$numemploi=str_pad((string)(int)($row_seq_number['numemploi_dernier']+1), 2, "0", STR_PAD_LEFT);
				$numemploi_a_modifier=$numemploi;
				$tables=array('individuemploi');
			}
			// En modif. ou en creation (l'enregistrement vient d'etre créé ci-dessus) : maj de l'enregistrement et des enregistrements des tables associees
			// traitement de l'ensemble des champs : un test ecarte les champs a ne pas modifier ou ils ne sont pas traites car pas envoyes dans le post
			// si certains champs ne sont pas renseignes par le post : 
			// - le champ a ete propose au user mais n'est pas recu pour une case a cocher : valeur='non'
			// - le champ n'a pas ete propose au user : pas d'update de ce champ
			// En modification ou apres creation d'un enregistrement pour les tables concernees, toutes les tables sont mises a jour avec les données envoyées
			
			// creer, ajouter_sejour ou (modifier et ajouter_emploi) : les insert ne sont faits que sur certaines tables en fonction de l'action
			foreach($tables as $table) 
			{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
				$first=true;
				$liste_champs="";$liste_val="";
				while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
				{ $Field=$row_rs_fields['Field'];
					$liste_champs.=($first?"":",").$Field;
					$liste_val.=($first?"":",");
					$first=false;
					if($Field=='codeindividu')
					{ $liste_val.=GetSQLValueString($codeindividu, "text");
					}
					else if($table=='individu' && $Field=='login') 
					{  $liste_val.=GetSQLValueString($codeindividu."#sans login", "text");
					}
					else if(($table=='individusejour' || $table=='individuthese') && $Field=='numsejour')
					{	$liste_val.=GetSQLValueString($numsejour, "text");
					}
					else if($table=='individuemploi' && $Field=='numemploi')
					{	$liste_val.=GetSQLValueString($numemploi, "text");
					}
					else if($table=='individusejour' && $Field=='codecreateur')
					{ $liste_val.=GetSQLValueString($codeuser, "text");
					}
					else if($table=='individusejour' && $Field=='date_creation')
					{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
					}
					else if($table=='individusejour' && $Field=='codemodifieur')
					{ $liste_val.=GetSQLValueString($codeuser, "text");
					}
					else if($table=='individusejour' && $Field=='date_modif')
					{ $liste_val.=GetSQLValueString(date("Y/m/d"), "text");
					}
					else if($table=='individusejour' && $Field=='codecorps')
					{ $liste_val.=GetSQLValueString($codecorps, "text");
					}
					else if(array_key_exists($Field,$tab_champs_ouinon_defaut))
					{ $liste_val.=GetSQLValueString($tab_champs_ouinon_defaut[$Field], "text");
					}
					else if($table=='individuthese' && $Field=='sujet_verrouille')
					{ $liste_val.=GetSQLValueString($sujet_verrouille, "text");
					}
					else if($table=='individusejour' && $Field=='attestation_stage')
					{ $liste_val.=GetSQLValueString($attestation_stage, "text");
					}
					else
					{ $liste_val.="''";
					}
				}
				$updateSQL = "insert into ".$table." (".$liste_champs.") values (".$liste_val.")";
				mysql_query($updateSQL) or  die(mysql_error());
			}
			$action="modifier";//On est ou on passe en "modifier" pour la suite : apres les insert, les update seront faits de la meme facon
		}// fin traitement creation individu, sejour ou emploi
		
		// En modif. ou en creation (l'enregistrement vient d'etre créé ci-dessus) : maj de l'enregistrement et des enregistrements des tables associees
		// initialisation de l'update avec les champs date_modif et codemodifieur qui sont fixés et pas saisis par le user
		// traitement de l'ensemble des champs : un test ecarte les champs a ne pas modifier ou ils ne sont pas traites car pas envoyes dans le post
		// si certains champs ne sont pas renseignes par le post : 
		// - le champ a ete propose au user mais n'est pas recu pour une case a cocher : valeur='non'
		// - le champ n'a pas ete propose au user : pas d'update de ce champ
		// En modification ou apres creation d'un enregistrement pour les tables concernees, toutes les tables sont mises a jour avec les données envoyées
		$tables=array('individu','individusejour','individuemploi');
		if($codecorps=='51')//doctorant : table individuthese, individusujet
		{ $tables[]=('individuthese');
		}
		
		foreach($tables as $table)
		{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
			$updateSQL = "UPDATE ".$table." SET ";
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ // on update que les champs envoyés dans le POST
				$Field=$row_rs_fields['Field'];
				if(!in_array($Field,array("codeindividu","numsejour","numemploi","datedeb_sejour_prevu","datefin_sejour_prevu")))
				{	if(isset($_POST[$Field]) ||
					 	(isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa'] )) || 
						(isset($_POST[$Field.'_hh']) && isset($_POST[$Field.'_mn'])))
					{ //echo $row_rs_fields['Field']." : ".$_POST[$row_rs_fields['Field']]."<br>"; 
						//les donnees codeindividu codecreateur date_creation ne sont plus jamais modifiees : uniquement en creation en insert
						//les donnees numsejour et numemploi ne sont pas modifiees : numsejour => passage par ajoutersejour, numemploi pas encore programmé !!
						if(in_array($Field, $tab_champs_ouinon)===false)
						{ $updateSQL.=$Field."=";
							// le champ login doit etre renseigne et unique
							if($Field=='login')
							{ $updateSQL.=GetSQLValueString($_POST['login']==''?$codeindividu."#sans login":$_POST['login'], "text");
							}
							else if(array_key_exists($Field, $tab_champs_date)!==false)
							{ $updateSQL.=GetSQLValueString(jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']), "text");
							}
							else if(array_key_exists($Field, $tab_champs_heure_mn)!==false)
							{ $updateSQL.=GetSQLValueString(hhmn2heure($_POST[$Field.'_hh'],$_POST[$Field.'_mn']), "text");
							}
							else if($table=='individuthese' && $Field=='sujet_verrouille')
							{ if($sujet_verrouille!='')
								{ $updateSQL.=GetSQLValueString($sujet_verrouille, "text");
								}
							}
							else if($table=='individusejour' && $Field=='attestation_stage')
							{ if($attestation_stage!='')
								{ $updateSQL.=GetSQLValueString($attestation_stage, "text");
								}
							}
							else
							{ $updateSQL.=GetSQLValueString($_POST[$Field], "text");
							}
						}
						else
						{ $updateSQL.=$Field."='oui'";
						}
						$updateSQL.=",";
					}
					else
					{ if(in_array($Field, $tab_champs_ouinon)!==false)//mis dans le formulaire mais non renvoye dans le POST (=>Off=non coche)
						{ $updateSQL.=$Field."='non'".",";
						}
						else if($Field=='codemodifieur')
						{ $updateSQL.=$Field."=".GetSQLValueString($codeuser, "text").",";
						}
						else if($Field=='date_modif')
						{ $updateSQL.=$Field."=".GetSQLValueString(date("Y/m/d"), "text").",";
						}
					}
				}
				if($maj_date_sejour_prevu && $table=='individusejour' && ($Field=='datedeb_sejour_prevu' || $Field=='datefin_sejour_prevu'))
				{ $updateSQL.=$Field."=".GetSQLValueString($_POST[$Field], "text").",";
				}
			}
			$updateSQL=rtrim($updateSQL,",");// enleve la derniere , mise en fin de chaine
			$updateSQL.=" WHERE codeindividu=".GetSQLValueString($codeindividu, "text");
			if($table!="individu")// individusejour et individuemploi
			{ if($table=="individusejour" || $table=="individuthese")
				{$updateSQL.=" and numsejour=".GetSQLValueString($numsejour, "text");
				}
				if($table=="individuemploi")
				{ $updateSQL.=" and numemploi=".GetSQLValueString($numemploi, "text");
				}
			}
			mysql_query($updateSQL) or die(mysql_error());
		}
		// sujet
		if(isset($_POST['codesujet']))
		{ $codesujetenregistre=isset($_POST['codesujetenregistre'])?$_POST['codesujetenregistre']:'';
			$codesujet=$_POST['codesujet'];
		  $updateSQL ="DELETE from individusujet". 
									" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text");
			mysql_query($updateSQL) or die(mysql_error());
			if($_POST['codesujet']!='')
			{ $updateSQL ="INSERT into individusujet (codeindividu,numsejour,codesujet)". 
										" values (". 
															GetSQLValueString($codeindividu, "text").",".
															GetSQLValueString($numsejour, "text").",".
															GetSQLValueString($codesujet, "text").
														")";
				mysql_query($updateSQL) or die(mysql_error());
				
				// sujet passe a pourvu si valide
				$rs=mysql_query("select codestatutsujet from sujet where codesujet=".GetSQLValueString($codesujet, "text"));
				$row_rs=mysql_fetch_assoc($rs);
				if($row_rs['codestatutsujet']=='V')  
				{ $updateSQL = " update sujet set codestatutsujet='P' where codesujet=".GetSQLValueString($codesujet, "text");
					mysql_query($updateSQL) or die(mysql_error());
				}
				// si codesujetenregistre <>'' et different de celui en cours et s'il était pourvu il passe a valide : rendu a la liste des sujets affectables
				// s'il était en cours de validation (E), il n'a pas pu etre passe a pourvu 
				if($codesujetenregistre!='' && $codesujetenregistre!=$codesujet)
				{ $rs=mysql_query("select codestatutsujet from sujet where codesujet=".GetSQLValueString($codesujetenregistre, "text"));
					$row_rs=mysql_fetch_assoc($rs);
					if($row_rs['codestatutsujet']=='P')  
					{ $updateSQL = " update sujet set codestatutsujet='V' where codesujet=".GetSQLValueString($codesujetenregistre, "text");
						mysql_query($updateSQL) or die(mysql_error());
					}
				}
				//si doctorant : les champs de suivi du doctorant ne sont recus dans le POST que si le sujet etait deja verrouille
				if($codecorps=='51' && $sujet_verrouille_avant=='oui')
				{ $updateSQL = 	" update sujet set autredir1=".GetSQLValueString($_POST['autredir1'], "text").",".
																					"autredir1mail=".GetSQLValueString($_POST['autredir1mail'], "text").",".	
																					"autredir2=".GetSQLValueString($_POST['autredir2'], "text").",".	
																					"autredir2mail=".GetSQLValueString($_POST['autredir2mail'], "text").	
												" where codesujet=".GetSQLValueString($codesujet, "text");
					mysql_query($updateSQL) or die(mysql_error());
					// ----------------------------- affectation directeur(s)
					// suppression des directeurs existants
					mysql_query("delete from sujetdir where codesujet=".GetSQLValueString($codesujet, "text")) or die(mysql_error());
					// insertion des directeurs
					foreach($tab_rs_dir as $numordre=>$un_dir)
					{ $updateSQL ="INSERT into sujetdir (codesujet,codedir,taux_encadrement,numordre) ".
													" values (".GetSQLValueString($codesujet, "text").",".GetSQLValueString($un_dir['codedir'], "text").",".
																			GetSQLValueString($un_dir['taux_encadrement'], "text").",".GetSQLValueString($numordre, "text").")";
						mysql_query($updateSQL) or die(mysql_error());
					}
				}
			}
		}
		// affectation theme(s)
		// suppression
		$updateSQL ="delete from individutheme ".
								" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text");
		mysql_query($updateSQL) or die(mysql_error());
		// puis insertion
		foreach($row_rs_individutheme as $codetheme)
		{ $updateSQL ="INSERT into individutheme (codeindividu,numsejour,codetheme,datedeb_theme,datefin_theme) values (".
										GetSQLValueString($codeindividu, "text").",".
										GetSQLValueString($numsejour, "text").",".
										GetSQLValueString($codetheme, "text").",".
										GetSQLValueString($datedeb_theme, "text").",".
										"''".")";
			mysql_query($updateSQL) or die(mysql_error());
		}
		if($GLOBALS['avecequipe'])
		{ // suppression
			$updateSQL ="delete from individuequipe ".
									" where codeindividu=".GetSQLValueString($codeindividu, "text")." and numsejour=".GetSQLValueString($numsejour, "text");
			mysql_query($updateSQL) or die(mysql_error());
			// puis insertion
			foreach($row_rs_individuequipe as $codeequipe)
			{ $updateSQL ="INSERT into individuequipe (codeindividu,numsejour,codeequipe,datedeb_equipe,datefin_equipe) values (".
											GetSQLValueString($codeindividu, "text").",".
											GetSQLValueString($numsejour, "text").",".
											GetSQLValueString($codeequipe, "text").",".
											GetSQLValueString($datedeb_theme, "text").",".
											"''".")";
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
		// post-it perso.
		if(isset($_POST['postit']))
		{ $updateSQL ="DELETE from individupostit". 
									" where codeindividu=".GetSQLValueString($codeindividu, "text").
									" and numsejour=".GetSQLValueString($numsejour, "text")." and codeacteur =".GetSQLValueString($codeuser, "text");
			mysql_query($updateSQL) or die(mysql_error());
			if($_POST['postit']!='')
			{ $updateSQL = "INSERT into individupostit (codeindividu,numsejour,codeacteur,postit) values (".
											GetSQLValueString($codeindividu, "text").",".
											GetSQLValueString($numsejour, "text").",".
											GetSQLValueString($codeuser, "text").",".
											GetSQLValueString($_POST['postit'], "text").")";
				mysql_query($updateSQL) or die(mysql_error());
			}
		}
		
		// pjemploi numcatpj=numsejour ou numemploi
		// suppression emploi : si c'est un nouveau, $numemploi_a_supprimer='', il n'y a rien a supprimer sauf une pj envoyée par erreur utilisateur
		if($supprimer_emploi)
		{	if($numemploi_a_supprimer!='')
			{ $updateSQL ="delete from individuemploi ".
										" where codeindividu=".GetSQLValueString($codeindividu, "text").
										" and numemploi=".GetSQLValueString($numemploi_a_supprimer, "text");
				mysql_query($updateSQL) or die(mysql_error());
				// suppression des pieces jointes de l'emploi et du rep les contenant eventuels
				$rs_individupj=mysql_query("select codetypepj,nomfichier".
																		" from individupj".
																		" where codeindividu=".GetSQLValueString($codeindividu, "text").
																		" and codelibcatpj=".GetSQLValueString("emploi", "text").
																		" and numcatpj=".GetSQLValueString($numemploi_a_supprimer, "text")) or die(mysql_error());
				if(mysql_num_rows($rs_individupj)>=1)
				{ while($row_rs_individupj=mysql_fetch_assoc($rs_individupj))
					{ $codetypepj=$row_rs_individupj['codetypepj'];
						unlink($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/emploi/'.$numemploi_a_supprimer.'/'.$codetypepj);
					}
					//suppression du rep de cet emploi
					suppr_rep($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/emploi/'.$numemploi_a_supprimer);
					// suppression des enreg. des pj pour cet emploi
					mysql_query("delete from individupj".
											" where codeindividu=".GetSQLValueString($codeindividu, "text").
											" and codelibcatpj=".GetSQLValueString("emploi", "text").
											" and numcatpj=".GetSQLValueString($numemploi_a_supprimer, "text")) or die(mysql_error());
				}
			}
			// enleve de $_FILES la piece jointe eventuelle associée a l'emploi supprimé alors qu'il y a suppression de l'emploi la contenant : le user supprime un emploi avec un upload de pj simultané pour cet emploi
			foreach($_FILES["pj"]["name"] as $key=>$val)
			{ $tab_pj=explode('_',$key);
				if($tab_pj[0]=='emploi' && $tab_pj[1]==$numemploi_a_supprimer)
				{ unset($_FILES["pj"]["name"][$key]);
				}
			}
		}

		// suppression pj (et rep(s) si vide)
		if($supprimer_une_pj)
		{ $updateSQL ="delete from individupj ".
									" where codeindividu=".GetSQLValueString($codeindividu, "text").
									" and codelibcatpj=".GetSQLValueString($tab_pj_a_supprimer[0], "text").
									" and numcatpj=".GetSQLValueString($tab_pj_a_supprimer[1], "text").
									" and codetypepj=".GetSQLValueString($tab_pj_a_supprimer[2], "text");
			mysql_query($updateSQL) or die(mysql_error());
			unlink($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/'.$tab_pj_a_supprimer[0].'/'.$tab_pj_a_supprimer[1].'/'.$tab_pj_a_supprimer[2]);
			if(rep_estvide($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/'.$tab_pj_a_supprimer[0].'/'.$tab_pj_a_supprimer[1]))
			{ suppr_rep($GLOBALS['path_to_rep_upload'] .'/individu/'.$codeindividu.'/'.$tab_pj_a_supprimer[0].'/'.$tab_pj_a_supprimer[1]);
			}
		}
		
		//ajout pj et reps eventuels
		// inutile ?
		$tab_individupj=array();
		//liste de toutes les pj individu 
		$rs_pj=mysql_query("select codelibcatpj,codetypepj,codelibtypepj from typepjindividu");
		while($row_rs_pj=mysql_fetch_assoc($rs_pj))
		{ $tab_pj[$row_rs_pj['codelibtypepj']]=$row_rs_pj['codetypepj'];
		}
		// fin inutile
		if(isset($_FILES["pj"]["name"]))
		{ foreach ($_FILES["pj"]["name"] as $key => $nomfichier)//$key=codelibcatpj_numcatpj_codetypepj (emploi_02_01) 
			{ $tab_pj=explode('_',$key);
				$codelibcatpj=$tab_pj[0];
				if($codelibcatpj=='individu')
				{ $numcatpj=$tab_pj[1];
				}
				else if($codelibcatpj=='emploi')
				{ $numcatpj=($tab_pj[1]==""?$numemploi:$tab_pj[1]);
				}
				else if($codelibcatpj=='sejour')
				{ $numcatpj=($tab_pj[1]==""?$numsejour:$tab_pj[1]);
				}
				$codetypepj=$tab_pj[2];
				
				if($nomfichier!='')
				{ clearstatcache();//efface le cache relatif a l'existence des repertoires
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
					$tab_res_upload=upload_file($_FILES,$rep_upload,"pj",$key,$codetypepj);
					if($tab_res_upload['erreur']=='' && $tab_res_upload['nomfichier']!='')
					{	// si existe deja
						$updateSQL= "delete from individupj where codeindividu=".GetSQLValueString($codeindividu, "text").
												" and codelibcatpj=".GetSQLValueString($codelibcatpj, "text").
												" and numcatpj=".GetSQLValueString($numcatpj, "text").
												" and codetypepj=".GetSQLValueString($codetypepj, "text");
						mysql_query($updateSQL) or die(mysql_error());
						$updateSQL="insert into individupj (codeindividu,codelibcatpj,numcatpj,codetypepj,nomfichier)". 
												" values (".GetSQLValueString($codeindividu, "text").",".GetSQLValueString($codelibcatpj, "text").",".GetSQLValueString($numcatpj, "text").",".
																		GetSQLValueString($codetypepj,"text").",".GetSQLValueString($tab_res_upload['nomfichier'], "text").")";
						mysql_query($updateSQL) or die(mysql_error());
					}
					else if($tab_res_upload['nomfichier']!='')
					{ $warning.='<br>'.$tab_res_upload['erreur'];
					}
				}
			}
		}
		// PG fsd 20161213
		if($generer_classeur_fsd)
		{ $erreur_fsd=fsd_xl_php($codeindividu,$numsejour);
			if($erreur_fsd!='')
			{ $warning.='<br>Classeur FSD non g&eacute;n&eacute;r&eacute; : '.$erreur_fsd;
			}
			$erreur_sujet_pdf=fsd_sujet_pdf_php($codeindividu,$numsejour);
			if($erreur_sujet_pdf)
			{ $warning.='<br>Sujet pdf non g&eacute;n&eacute;r&eacute; : '.$erreur_sujet_pdf;
			}
		}
		// PG fsd 20160120
		if(isset($rs_pj)) {mysql_free_result($rs_pj);}
		if(isset($rs_individupj)) {mysql_free_result($rs_individupj);}
		// 20170322
		if($editer_sujet)
		{ if(isset($_POST['codesujet']))
			{ if($_POST['codesujet']=='')
				{ $action='creer';
				}
				else
				{ $action='modifier';
				}
				$query_rs="select codelibcat from corps,cat where corps.codecat=cat.codecat and codecorps=".GetSQLValueString($codecorps, "text");
				$rs=mysql_query($query_rs) or die(mysql_error());
				$row_rs = mysql_fetch_assoc($rs);
				$codetypesujet=$tab_etudiant_ou_exterieur_typesujet[$row_rs['codelibcat']];
				http_redirect("edit_sujet.php?appel_de_individu=oui&action=".$action."&codesujet=".$_POST['codesujet']."&codetypesujet=".$codetypesujet.
											"&codeetudiantsejour=".$codeindividu.".".$numsejour."&sujet_a_affecter=oui&etat_individu=".$etat_individu."&ind_ancre=".$ind_ancre);
			}
		}
		// 20170322 fin
		//fin ajout pj et reps eventuels
	}//fin if($erreur=="")
}// fin if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form_fiche_dossier_pers"))


// ------------------------------  TRAITEMENT DE L'ECRAN D'AFFICHAGE DU FORMULAIRE DE SAISIE 
// AVANT CHOIX CORPS
if($avant_choix_corps)// statutpers et corps a presenter dans le formulaire de choix
{ $query_rs_statutpers = "SELECT codestatutpers, libstatutpers_fr FROM statutpers WHERE codestatutpers<>'' ".
												(!($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte))?" and codestatutpers='02'":"").
													"ORDER BY numordre";
	$rs_statutpers = mysql_query($query_rs_statutpers) or die(mysql_error());
	while($row_rs_statutpers =mysql_fetch_assoc($rs_statutpers))
	{ $tab_statutpers[$row_rs_statutpers['codestatutpers']]=$row_rs_statutpers['libstatutpers_fr'];
	}
	
	//pour liste select des corps pour chaque valeur de codestatutpers
	// 20161213
	$query_rs_statutperscorps = "select statutpers.codestatutpers,corps.codecorps,liblongcorps_fr as  libcorps".
															" from statutpers, corps, cat  where statutpers.codestatutpers=corps.codestatutpers and corps.codecorps<>''".
															"  and corps.codecat=cat.codecat".
															(!($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte))?
															" and (codelibcat='EXTERIEUR' or codelibcat='PRESTATAIRE')"
															:"").
															" and ".periodeencours('corps.date_deb','corps.date_fin').
															" order by statutpers.codestatutpers,corps.numordre ";
	$rs_statutperscorps = mysql_query($query_rs_statutperscorps) or die(mysql_error());
	while($row_rs_statutperscorps = mysql_fetch_assoc($rs_statutperscorps))
	{ $tab_statutperscorps[$row_rs_statutperscorps['codestatutpers']][$row_rs_statutperscorps['codecorps']]=$row_rs_statutperscorps['libcorps'];
	}
	$query_rs_typestage=" SELECT codetypestage,libcourttypestage as libtypestage".
											" FROM typestage".
											" WHERE codetypestage<>'' and ".periodeencours('date_deb','date_fin').
											" ORDER BY numordre asc";
	$rs_typestage = mysql_query($query_rs_typestage) or die(mysql_error());

	$query_rs_lieu = "SELECT codelieu,if(codelieu='','[ Obligatoire ]',libcourtlieu) as liblieu FROM lieu".
									" WHERE ".periodeencours('date_deb','date_fin')." order by libcourtlieu";
	$rs_lieu = mysql_query($query_rs_lieu) or die(mysql_error());
	
	// formation d'un enregistrement mini. d'individu pour affichage
	$query_rs_individu ="SELECT individu.*,civilite.libcourt_fr as libciv_fr FROM civilite,individu WHERE individu.codeciv=civilite.codeciv and  ".
											" individu.codeindividu = ".GetSQLValueString($codeindividu, "text");
	$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
	$row_rs_individu = mysql_fetch_assoc($rs_individu);
	if($erreur=='')
	{ if($action=='creer')
		{ $row_rs_individu['codestatutpers']='02';//Par defaut : non permanent
			$row_rs_individu['codecorps']='';//Par defaut : stagiaire ou doctorant selon la periode de l'année
			$row_rs_individu['datedeb_sejour']='';
		}
		else if($action=='ajouter_sejour')
		{	$query_rs_individu ="SELECT individusejour.*, corps.liblongcorps_fr as libcorps,cat.codelibcat, corps.codecorps_suivant,".
														" codelibtypestage, statutpers.codestatutpers,statutpers.libstatutpers_fr".
														" from individusejour, corps,cat, statutpers,typestage".
														" WHERE individusejour.codecorps=corps.codecorps and corps.codestatutpers=statutpers.codestatutpers".
														" and corps.codecat=cat.codecat and individusejour.codetypestage=typestage.codetypestage".
														" and individusejour.codeindividu = ".GetSQLValueString($codeindividu, "text").
														" and individusejour.numsejour=".GetSQLValueString($numsejour_leplusrecent, "text");
			$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
			$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
			if($row_rs_individu['codecorps_suivant']!='')
			{ $row_rs_individu['codecorps']=$row_rs_individu['codecorps_suivant'];
				if($row_rs_individu['codecorps']=='03')//mcf venant de postdoc : statutpers=01
				{ $row_rs_individu['codestatutpers']='01';
				}
			}
			//initialisation des dates de sejour
			if($row_rs_individu['codelibcat']=='STAGIAIRE' && $row_rs_individu['codelibtypestage']=='MASTER' && $row_rs_individu['datefin_sejour']<=date("Y").'/10/01')// master devient doctorant
			{ $row_rs_individu['datedeb_sejour']=date("Y").'/10/01';
			}
			else if($row_rs_individu['datefin_sejour']!='')
			{ $row_rs_individu['datedeb_sejour']=date("Y/m/d",mktime(0,0,0,substr($row_rs_individu['datefin_sejour'],5,2),substr($row_rs_individu['datefin_sejour'],8,2)+1,substr($row_rs_individu['datefin_sejour'],0,4)));
			}
		}
		$row_rs_individu['datefin_sejour']='';
	}
	else
	{ foreach($_POST as $key=>$val)
		{ $row_rs_individu[$key]=$val;
		}
	}
}
else// ----------------------- DEBUT FORMULAIRE DE SAISIE APRES CHOIX CORPS ------------------------------
{	// formation d'un enregistrement complet d'un individu pour creation ou modif : $row_rs_individu.
	// Création d'individu : l'enreg. '' d'individu associé a un sejour vide, un emploi vide, des themes
	// S'il s'agit d'un nouveau sejour, une copie des infos du dernier sejour est presentee
	if(isset($_POST['datefin_sejour']) && $_POST['datefin_sejour']!='')
	{ $tab_dureesejour_amj=duree_aaaammjj($_POST['datedeb_sejour'], $_POST['datefin_sejour']);
	}

	$query_rs_individu ="SELECT individu.*,civilite.libcourt_fr as libciv_fr FROM civilite,individu WHERE individu.codeciv=civilite.codeciv and  ".
											" individu.codeindividu = ".GetSQLValueString($codeindividu, "text");
	$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
	$row_rs_individu = mysql_fetch_assoc($rs_individu);
	$row_rs_individu['libgrade']='';//20170711

	if($action=="creer")
	{ // verif doublon
		$rs=mysql_query("SELECT nom, prenom from individu");
		$tab_ind_bd=array();
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_ind_bd[strtolower($row_rs['nom'].$row_rs['prenom'])]=$row_rs;
		}
		if($codecorps=='51')//doctorant
		{ $query_rs_individu ="SELECT individusejour.*,individuemploi.*,individusujet.*,individuthese.*".
													" from individu ".
													" left join individusejour on individu.codeindividu=individusejour.codeindividu".
													" left join individuthese on individu.codeindividu=individuthese.codeindividu".
													" left join individuemploi on individu.codeindividu=individuemploi.codeindividu".
													" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
													" WHERE individu.codeindividu = ".GetSQLValueString($codeindividu, "text");
		}
		else
		{ $query_rs_individu ="SELECT individusejour.*,individuemploi.*,individusujet.*".
													" from individu ".
													" left join individusejour on individu.codeindividu=individusejour.codeindividu".
													" left join individuemploi on individu.codeindividu=individuemploi.codeindividu".
													" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
													" WHERE individu.codeindividu = ".GetSQLValueString($codeindividu, "text");
		}
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());

		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
		// 20161213
		if(!($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte)) && $row_rs_individu['codereferent']=='')
		{ $row_rs_individu['codereferent']=$codeuser;
		}
		
		foreach(array('datedeb_sejour','datefin_sejour') as $champ_date)
		{ $row_rs_individu[$champ_date.'_prevu']=$_POST[$champ_date.'_prevu'];
			$row_rs_individu[$champ_date]=$_POST[$champ_date];
		}
		$row_rs_individu['codelieu']=$_POST['codelieu'];
		$row_rs_individu['autrelieu']=$_POST['autrelieu'];

		$query_rs_individu="SELECT sujet.* from sujet where codesujet=''";
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));

		$query_rs_individu ="SELECT corps.codecorps, corps.liblongcorps_fr as libcorps,statutpers.codestatutpers,statutpers.libstatutpers_fr,cat.codelibcat".
												" from corps,statutpers,cat ".
												" WHERE corps.codestatutpers=statutpers.codestatutpers and corps.codecat=cat.codecat".
												" and corps.codecorps=".GetSQLValueString($codecorps, "text");
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
		
		$row_rs_individu['createurnom']=$tab_infouser['nom'];$row_rs_individu['createurprenom']=$tab_infouser['prenom'];
		$row_rs_individu['date_creation']=date("Y/m/d");
		$row_rs_individu['modifieurnom']=$tab_infouser['nom'];$row_rs_individu['modifieurprenom']=$tab_infouser['prenom'];
		$row_rs_individu['date_modif']=date("Y/m/d");

		$row_rs_individu['tel']='';

		$row_rs_individu['codepays_naiss']='079';//france
		$row_rs_individu['codenat']=$row_rs_individu['codepays_naiss'];

		if($row_rs_individu['codelibcat']=='DOCTORANT')//doctorant
		{ $row_rs_individu['sujet_verrouille']='non';
		}

		if($row_rs_individu['codelibcat']=='STAGIAIRE')//stagiaire
		{ $query_rs_individu ="SELECT codetypestage,codelibtypestage,libcourttypestage as libtypestage from typestage ".
													" WHERE typestage.codetypestage=".GetSQLValueString($codetypestage, "text");
			$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
			$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
			$row_rs_individu['attestation_stage']='non';
		}
		
		$row_rs_individu['codegesttheme']=$codeuser;
		// 20161223
		// codetypeacceszrr
		if($row_rs_individu['codelibcat']=='EC' || $row_rs_individu['codelibcat']=='CHERCHEUR' || $row_rs_individu['codelibcat']=='ITARF')
		{	if($row_rs_individu['codestatutpers']=='01')//permanent
			{ $row_rs_individu['codetypeacceszrr']='01';// CDI
			}
			else
			{ $row_rs_individu['codetypeacceszrr']='07';//CDD
			}
		}
		else if($row_rs_individu['codelibcat']=='STAGIAIRE')
		{	$row_rs_individu['codetypeacceszrr']='04';//stage
		}
		else if($row_rs_individu['codelibcat']=='DOCTORANT')
		{	$row_rs_individu['codetypeacceszrr']='05';//doctorat
		}
		else if($row_rs_individu['codelibcat']=='POSTDOC')
		{	$row_rs_individu['codetypeacceszrr']='06';//postdoc
		}
		else if($row_rs_individu['codelibcat']=='PRESTATAIRE')
		{	$row_rs_individu['codetypeacceszrr']='03';//
		}
		else
		{ $row_rs_individu['codetypeacceszrr']='02';//collaboration
		}
		
		$row_rs_individu['codephysiquevirtuel']='03';//physique virtuel
		//$row_rs_individu['codetypeacceszrrglobal']='non';
		//$row_rs_individu['codestageformationremunere']='non';	
		/* if(($row_rs_individu['codelibcat']=='STAGIAIRE' && $tab_dureesejour_amj['a']*12+ $tab_dureesejour_amj['m']>=2 && $row_rs_individu['codelibtypestage']=='MASTER')
			  || $row_rs_individu['codelibcat']=='DOCTORANT' || $row_rs_individu['codelibcat']=='POSTDOC')
		{ $row_rs_individu['codestageformationremunere']='oui';
		} */
		
		if($row_rs_individu['codelibcat']=='STAGIAIRE'  || $row_rs_individu['codelibcat']=='DOCTORANT' || $row_rs_individu['codelibcat']=='POSTDOC' || in_array($codecorps,array('58','59')))
		{ $row_rs_individu['codesituationprofessionnelle']='01';//etudiant
		}
		else if($row_rs_individu['codelibcat']=='EC' || $row_rs_individu['codelibcat']=='CHERCHEUR' || in_array($codecorps,array('54','56','60')))
		{ $row_rs_individu['codesituationprofessionnelle']='04'; // chercheur
		}
		else
		{ $row_rs_individu['codesituationprofessionnelle']='05'; // salarie
		}
		// 20161223 fin
		
		$row_rs_individu['afficheannuaire']=$tab_champs_ouinon_defaut['afficheannuaire'];
		$row_rs_individu['liste_diff']=$tab_champs_ouinon_defaut['liste_diff'];
		$row_rs_individupostit['codeacteur']=$codeuser;
		$row_rs_individupostit['nom']=$tab_infouser['nom'];
		$row_rs_individupostit['prenom']=$tab_infouser['prenom'];
		$row_rs_individupostit['postit']='';
		
		$row_rs_individu['tab_emploi']=array();
		$rs_fields = mysql_query('SHOW COLUMNS FROM individuemploi');
		while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
		{ $Field=$row_rs_fields['Field'];
			$row_rs_individu['tab_emploi'][''][$Field]='';
		}
		$row_rs_individu['tab_emploi']['']['datedeb_emploi']=$_POST['datedeb_sejour'];
		$row_rs_individu['tab_emploi']['']['datefin_emploi']=$_POST['datefin_sejour'];
		$row_rs_individu['tab_emploi']['']['codeetab']='07';// UL
		if($row_rs_individu['codestatutpers']=='02' &&
			 (($row_rs_individu['codelibcat']=='STAGIAIRE' && $tab_dureesejour_amj['a']*12+ $tab_dureesejour_amj['m']>=2 && $row_rs_individu['codelibtypestage']=='MASTER')
				|| ($row_rs_individu['codelibcat']=='ITARF' || $row_rs_individu['codelibcat']=='CHERCHEUR' )))//CDD
		{	$row_rs_individu['tab_emploi']['']['codemodefinancement']='03';//budget labo
		}
		$numemploi='';
	}
	else if($action=="ajouter_sejour")
	{ // on initialise ce sejour avec les informations du sejour le plus recent.
		// il peut y avoir plusieurs emplois
		if($codecorps=='51')// doctorant
		{ $query_rs_individu ="SELECT individuthese.*,individusejour.*,codelibcat,liblongcorps_fr as libcorps".
													" FROM individusejour,corps,cat,individu ".
													" left join individuthese on individu.codeindividu=individuthese.codeindividu".
													" WHERE individu.codeindividu=individusejour.codeindividu".
													" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
													" and individu.codeindividu = ".GetSQLValueString($codeindividu, "text").
													" and individusejour.numsejour=".GetSQLValueString($numsejour_leplusrecent, "text");
		}
		else
		{ $query_rs_individu ="SELECT individusejour.*,codelibcat,liblongcorps_fr as libcorps".
													" FROM individu,individusejour,corps,cat ".
													" WHERE individu.codeindividu=individusejour.codeindividu".
													" and individusejour.codecorps=corps.codecorps and corps.codecat=cat.codecat".
													" and individu.codeindividu = ".GetSQLValueString($codeindividu, "text").
													" and individusejour.numsejour=".GetSQLValueString($numsejour_leplusrecent, "text");
		}
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
		
		// Liste des codetheme 
		$rs=mysql_query("select codetheme ".
										" from individutheme where codeindividu=".GetSQLValueString($codeindividu,"text").
										" and numsejour=".GetSQLValueString($numsejour_leplusrecent, "text").// 11072017
										" order by codetheme") or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_individutheme[$row_rs['codetheme']]=$row_rs['codetheme'];
		}
		if($GLOBALS['avecequipe'])
		{ $row_rs_individuequipe=array();
			$rs=mysql_query("select codeequipe ".
											" from individuequipe where codeindividu=".GetSQLValueString($codeindividu,"text").
											" and numsejour=".GetSQLValueString($numsejour_leplusrecent, "text").// 11072017
											" order by codeequipe") or die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{ $row_rs_individuequipe[$row_rs['codeequipe']]=$row_rs['codeequipe'];
			}
		}
		
		foreach(array('datedeb_sejour','datefin_sejour','datedeb_sejour_prevu','datefin_sejour_prevu') as $champ_date)
		{ $row_rs_individu[$champ_date]=$_POST[$champ_date];
		}
		
		$row_rs_individu['codelieu']=$_POST['codelieu'];
		$row_rs_individu['autrelieu']=$_POST['autrelieu'];
		// categorie du sejour le plus recent : pour initialiser master si passage de stagiaire a master, test doctorant qui devient postdoc
		$codelibcat_leplusrecent=$row_rs_individu['codelibcat'];

		$query_rs_individu ="SELECT corps.codecorps, corps.liblongcorps_fr as libcorps,statutpers.codestatutpers,statutpers.libstatutpers_fr,cat.codelibcat".
												" from corps,statutpers,cat ".
												" WHERE corps.codestatutpers=statutpers.codestatutpers and corps.codecat=cat.codecat".
												" and corps.codecorps=".GetSQLValueString($codecorps, "text");
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));

		if($row_rs_individu['codelibcat']=='STAGIAIRE')
		{ $query_rs_individu ="SELECT codetypestage,codelibtypestage,libcourttypestage as libtypestage from typestage ".
													" WHERE typestage.codetypestage=".GetSQLValueString($codetypestage, "text");
			$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
			$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
			$row_rs_individu['attestation_stage']='non';
		}
		
		// pas de report des infos fsd pour nouveau sejour
		foreach(array('date_demande_fsd','date_autorisation','date_demande_modification_fsd','note_demande_modification_fsd','numdossierzrr','codestageformationremunere',
									'codenaturefinancement','autrenaturefinancement','montantfinancement','codeoriginefinancement','codesourcefinancement') as $champ)
		{ $row_rs_individu[$champ]='';
		}
		
		// 20161223
		// codetypeacceszrr
		if($row_rs_individu['codelibcat']=='EC' || $row_rs_individu['codelibcat']=='CHERCHEUR' || $row_rs_individu['codelibcat']=='ITARF')
		{	if($row_rs_individu['codestatutpers']=='01')//permanent
			{ $row_rs_individu['codetypeacceszrr']='01';// CDI
			}
			else
			{ $row_rs_individu['codetypeacceszrr']='07';//CDD
			}
		}
		else if($row_rs_individu['codelibcat']=='STAGIAIRE')
		{	$row_rs_individu['codetypeacceszrr']='04';//stage
		}
		else if($row_rs_individu['codelibcat']=='DOCTORANT')
		{	$row_rs_individu['codetypeacceszrr']='05';//doctorat
		}
		else if($row_rs_individu['codelibcat']=='POSTDOC')
		{	$row_rs_individu['codetypeacceszrr']='06';//postdoc
		}
		else if($row_rs_individu['codelibcat']=='PRESTATAIRE')
		{	$row_rs_individu['codetypeacceszrr']='03';//
		}
		else
		{ $row_rs_individu['codetypeacceszrr']='02';//collaboration
		}
		
		$row_rs_individu['codephysiquevirtuel']='03';//physique virtuel
		
		if($row_rs_individu['codelibcat']=='STAGIAIRE'  || $row_rs_individu['codelibcat']=='DOCTORANT' || $row_rs_individu['codelibcat']=='POSTDOC' || in_array($codecorps,array('58','59')))
		{ $row_rs_individu['codesituationprofessionnelle']='01';//etudiant
		}
		else if($row_rs_individu['codelibcat']=='EC' || $row_rs_individu['codelibcat']=='CHERCHEUR' || in_array($codecorps,array('54','56','60')))
		{ $row_rs_individu['codesituationprofessionnelle']='04'; // chercheur
		}
		else
		{ $row_rs_individu['codesituationprofessionnelle']='05'; // salarie
		}
		// 20161223 fin
		
		// emploi en cours dans le sejour le plus récent
		$query_rs_individuemploi ="SELECT individuemploi.* FROM individuemploi".
															" WHERE codeindividu=".GetSQLValueString($codeindividu, "text").
															" AND ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individu['datedeb_sejour']."'","'".$row_rs_individu['datefin_sejour']."'");
		$rs_individuemploi = mysql_query($query_rs_individuemploi) or die(mysql_error());
		// on ne propose pas un nouvel emploi vide si un emploi a une intersection avec le nouveau sejour
		if($row_rs_individuemploi=mysql_fetch_assoc($rs_individuemploi))
		{ $numemploi=$row_rs_individuemploi['numemploi'];
			$rs_fields = mysql_query('SHOW COLUMNS FROM individuemploi');
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				$row_rs_individu['tab_emploi'][$numemploi][$Field]=$row_rs_individuemploi[$Field];
			}
			$row_rs_individu['tab_emploi'][$numemploi]['lien_emploi_precedent']['numsejour']=$row_rs_individu['numsejour'];
		}
		else
		{	$row_rs_individu['tab_emploi']=array();
			$rs_fields = mysql_query('SHOW COLUMNS FROM individuemploi');
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				$row_rs_individu['tab_emploi'][''][$Field]='';
				if($Field=='datedeb_emploi')
				{ $row_rs_individu['tab_emploi'][''][$Field]=$row_rs_individu['datedeb_sejour'];
				}
			}
			$row_rs_individu['tab_emploi']['']['datedeb_emploi']=$_POST['datedeb_sejour'];
			$row_rs_individu['tab_emploi']['']['datefin_emploi']=$_POST['datefin_sejour'];
			$row_rs_individu['tab_emploi']['']['codeetab']='07';// UL
			if($row_rs_individu['codestatutpers']=='02' &&
				 (($row_rs_individu['codelibcat']=='STAGIAIRE' && $tab_dureesejour_amj['a']*12+ $tab_dureesejour_amj['m']>=2 && $row_rs_individu['codelibtypestage']=='MASTER')
					|| ($row_rs_individu['codelibcat']=='ITARF' || $row_rs_individu['codelibcat']=='CHERCHEUR' )))//CDD
			{	$row_rs_individu['tab_emploi']['']['codemodefinancement']='03';//budget labo
			}
			$numemploi='';
			$afficher_nouvel_emploi=true;
		}
		
		$row_rs_individu['codegesttheme']=$codeuser;
		$row_rs_individu['codesujet']='';
		
		$row_rs_individu['date_soutenance']='';
		$row_rs_individu['prog_rech']='';
		// pour un stagiaire qui devient doctorant on reprend le diplome préparé qui devient le master obtenu
		if($row_rs_individu['codelibcat']=='DOCTORANT' && $codelibcat_leplusrecent=='STAGIAIRE')
		{ $row_rs_individu['codemaster_obtenu']=$row_rs_individu['codediplome_prep'];
			$row_rs_individu['autremaster_obtenu_lib']=$row_rs_individu['autrediplome_prep'];
		}
		
		if($row_rs_individu['codelibcat']=='DOCTORANT')
		{ $row_rs_individu['sujet_verrouille']='non';
			$row_rs_individu['autredir1']='';
			$row_rs_individu['autredir1mail']='';
			$row_rs_individu['autredir2']='';
			$row_rs_individu['autredir2mail']='';
		}

		$row_rs_individu['createurnom']=$tab_infouser['nom'];$row_rs_individu['createurprenom']=$tab_infouser['prenom'];
		$row_rs_individu['date_creation']=date("Y/m/d");
		$row_rs_individu['modifieurnom']=$tab_infouser['nom'];$row_rs_individu['modifieurprenom']=$tab_infouser['prenom'];
		$row_rs_individu['date_modif']=date("Y/m/d");
		$row_rs_individupostit['codeacteur']=$codeuser;
		$row_rs_individupostit['nom']=$tab_infouser['nom'];
		$row_rs_individupostit['prenom']=$tab_infouser['prenom'];
		$row_rs_individupostit['postit']='';
	}
	else if($action=="modifier")
	{ $query_rs_individu ="SELECT sujet.*,individusujet.*,individusejour.*, ".//LAISSER individusejour apres sujet et individusujet car si pas de sujet alors codeindividu=NULL
												" corps.liblongcorps_fr as libcorps,cat.codelibcat,statutpers.codestatutpers,statutpers.libstatutpers_fr,".
												" createur.nom as createurnom,createur.prenom as createurprenom, modifieur.nom as modifieurnom, modifieur.prenom as modifieurprenom ".
												" FROM individu,corps,statutpers,cat, individu as createur, individu as modifieur,individusejour ".
												" left join individusujet on individusejour.codeindividu=individusujet.codeindividu and individusejour.numsejour=individusujet.numsejour".
												" left join sujet on individusujet.codesujet=sujet.codesujet".
												" WHERE individu.codeindividu=individusejour.codeindividu".
												" and individusejour.codecorps=corps.codecorps and corps.codestatutpers=statutpers.codestatutpers and corps.codecat=cat.codecat".
												" and createur.codeindividu=individusejour.codecreateur and modifieur.codeindividu=individusejour.codemodifieur".
												" and individu.codeindividu = ".GetSQLValueString($codeindividu, "text").
												" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
		$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
		$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
		
		
		// Liste des codetheme 
		$rs=mysql_query("select codetheme ".
										" from individutheme where codeindividu=".GetSQLValueString($codeindividu,"text").
										" and numsejour=".GetSQLValueString($numsejour, "text").// 11072017
										" order by codetheme") or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $row_rs_individutheme[$row_rs['codetheme']]=$row_rs['codetheme'];
		}
		if($GLOBALS['avecequipe'])
		{ $row_rs_individuequipe=array();
			$rs=mysql_query("select codeequipe ".
											" from individuequipe where codeindividu=".GetSQLValueString($codeindividu,"text").
											" and numsejour=".GetSQLValueString($numsejour, "text").// 11072017
											" order by codeequipe") or die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{ $row_rs_individuequipe[$row_rs['codeequipe']]=$row_rs['codeequipe'];
			}
		}
		
		
		$query_rs_individu="select libgrade_fr as libgrade from grade where codecorps=".GetSQLValueString($row_rs_individu['codecorps'], "text")." and codegrade=".GetSQLValueString($row_rs_individu['codegrade'], "text");
		$rs_individu=mysql_query($query_rs_individu) or die(mysql_error());
		if($row_rs=mysql_fetch_assoc($rs_individu))
		{ $row_rs_individu['libgrade']=$row_rs['libgrade'];
		}
		// Elements pour un doctorant
		$codecorps=$row_rs_individu['codecorps'];
		if($row_rs_individu['codecorps']=='51')//doctorant
		{ $query_rs_individu ="SELECT individuthese.* from individuthese".
													" WHERE individuthese.codeindividu = ".GetSQLValueString($codeindividu, "text").
													" and individuthese.numsejour=".GetSQLValueString($numsejour, "text");
 			$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
			$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
		}
		
		if($row_rs_individu['codelibcat']=='STAGIAIRE')
		{ $query_rs = "SELECT libcourtdiplome_fr as libdiplomeprep FROM individusejour".
									" left join diplome on individusejour.codediplome_prep=diplome.codediplome".
									" WHERE individusejour.codeindividu = ".GetSQLValueString($codeindividu, "text").
									" and individusejour.numsejour=".GetSQLValueString($numsejour, "text");
			$rs = mysql_query($query_rs) or die(mysql_error());
			if($rows_rs=mysql_fetch_assoc($rs))
			{ $row_rs_individu=array_merge($row_rs_individu,$rows_rs);
			}
			else
			{	$row_rs_individu=array_merge($row_rs_individu,array('libdiplomeprep'=>''));
			}
			$query_rs_individu ="SELECT codelibtypestage,libcourttypestage as libtypestage from typestage ".
													" WHERE typestage.codetypestage=".GetSQLValueString($row_rs_individu['codetypestage'], "text");
			$rs_individu = mysql_query($query_rs_individu) or die(mysql_error());
			$row_rs_individu=array_merge($row_rs_individu,mysql_fetch_assoc($rs_individu));
			$codetypestage=$row_rs_individu['codetypestage'];
		}
		
		// emplois de cet individu concernés par ce séjour : intersection([datedeb_emploi,datefin_emploi],[datedeb_sejour,datefin_sejour]) 
		$query_rs_individuemploi ="SELECT individuemploi.* FROM individuemploi".
															" WHERE codeindividu = ".GetSQLValueString($codeindividu, "text").
															" AND ".intersectionperiodes('datedeb_emploi','datefin_emploi',"'".$row_rs_individu['datedeb_sejour']."'","'".$row_rs_individu['datefin_sejour']."'").
															" ORDER BY datedeb_emploi desc";
		$rs_individuemploi = mysql_query($query_rs_individuemploi) or die(mysql_error());
		$row_rs_individu['tab_emploi']=array();
		//pas d'emploi et pas de demande d'ajout de nouvel emploi '' : affichage nouvel emploi ''
		if(!($row_rs_individuemploi=mysql_fetch_assoc($rs_individuemploi)) && !$afficher_nouvel_emploi)
		{ $rs_fields = mysql_query('SHOW COLUMNS FROM individuemploi');
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				$row_rs_individuemploi[$Field]='';
				if($Field=='datedeb_emploi')
				{ $row_rs_individuemploi['tab_emploi'][''][$Field]=$row_rs_individu['datedeb_sejour'];
				}
			}
			$afficher_nouvel_emploi=true;
			$numemploi='';
		}
		
		// lecture du premier enregistrement : on recupere le numemploi et la date_emploi de l'emploi le plus recent pour
		// la creation d'un nouvel emploi eventuel
		$numemploi_leplusrecent=$row_rs_individuemploi['numemploi'];
		$datefin_emploi_le_plus_recent=$row_rs_individuemploi['datefin_emploi'];
		$numemploi=$numemploi_leplusrecent;//par defaut
		
		// nouvel emploi initialise pour l'affichage des champs d'un nouvel emploi
		if($afficher_nouvel_emploi)
		{ if($datefin_emploi_le_plus_recent=='')
			{ $warning.='<br>La date de fin du dernier emploi doit &ecirc;tre renseign&eacute;e avant d&rsquo;en cr&eacute;er un plus r&eacute;cent';
			}
		  $rs_fields = mysql_query('SHOW COLUMNS FROM individuemploi');
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				if($Field=='datedeb_emploi')
				{ if($datefin_emploi_le_plus_recent!='')
					{ $row_rs_individu['tab_emploi'][''][$Field]=date("Y/m/d",mktime(0,0,0,substr($datefin_emploi_le_plus_recent,5,2),substr($datefin_emploi_le_plus_recent,8,2)+1,substr($datefin_emploi_le_plus_recent,0,4)));
					}
					else
					{ $row_rs_individu['tab_emploi'][''][$Field]=$row_rs_individu['datedeb_sejour'];
					}						
				}
				else if($Field=='datefin_emploi')
				{ $row_rs_individu['tab_emploi'][''][$Field]='';
				}
				else
				{ $row_rs_individu['tab_emploi'][''][$Field]=$row_rs_individuemploi[$Field];
				}
			}
			$numemploi='';
		}
		// le premier emploi de la table a déja été lu pour remplir eventuellement un nouvel emploi ''
		do//liens entre les sejours concernes par chaque emploi
		{ $row_rs_individu['tab_emploi'][$row_rs_individuemploi['numemploi']]=$row_rs_individuemploi;
			$un_numemploi=$row_rs_individuemploi['numemploi'];
			// cet emploi est-il lié a un autre séjour ?
			$query_rs_individusejour ="SELECT numsejour,datedeb_sejour,datefin_sejour,liblongcorps_fr as libcorps".
																" FROM individusejour,corps".
																" WHERE codeindividu = ".GetSQLValueString($codeindividu, "text").
																" and individusejour.codecorps=corps.codecorps".
																" and individusejour.numsejour<>".GetSQLValueString($numsejour, "text").
																" AND ".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_individuemploi['datedeb_emploi']."'","'".$row_rs_individuemploi['datefin_emploi']."'").
																" ORDER BY datedeb_sejour asc";// ordonné dans le temps du +ancien au +lointain
			$rs_individusejour = mysql_query($query_rs_individusejour) or die(mysql_error());
			while($row_rs_individusejour = mysql_fetch_assoc($rs_individusejour))
			{ $lien_emploi="";
			  if($row_rs_individusejour['datefin_sejour']<$row_rs_individu['datedeb_sejour'] && $row_rs_individusejour['datefin_sejour']!='')
				{ $lien_emploi='lien_emploi_precedent';
				}
				// on arrete le parcours de liste individusejour si $lien_emploi=='lien_emploi_suivant' : le sejour suivant a été trouvé
				else if($lien_emploi!='lien_emploi_suivant' && ($row_rs_individusejour['datedeb_sejour']>$row_rs_individu['datefin_sejour'] || $row_rs_individu['datefin_sejour']==''))
				{ $lien_emploi='lien_emploi_suivant';
				}
				if($lien_emploi!="")
				{ $row_rs_individu['tab_emploi'][$un_numemploi][$lien_emploi]['numsejour']=$row_rs_individusejour['numsejour'];
				}
			}
		} while($row_rs_individuemploi=mysql_fetch_assoc($rs_individuemploi));
		
		if($modifier_emploi)
		{ $numemploi=$numemploi_a_modifier;
		}
		//postit perso
		$query_rs_individupostit ="SELECT codeacteur,postit, nom, prenom FROM individu,individupostit".
															" WHERE individupostit.codeacteur=individu.codeindividu".
															" and individupostit.codeindividu=".GetSQLValueString($codeindividu, "text").
															" and numsejour=".GetSQLValueString($numsejour, "text").
															" and codeacteur= ".GetSQLValueString($codeuser, "text");
		$rs_individupostit = mysql_query($query_rs_individupostit) or die(mysql_error());
		if(!$row_rs_individupostit=mysql_fetch_assoc($rs_individupostit))//pas de postit : on precomplete les champs affiches
		{ $row_rs_individupostit['codeacteur']=$codeuser;
			$row_rs_individupostit['nom']=$tab_infouser['nom'];
			$row_rs_individupostit['prenom']=$tab_infouser['prenom'];
			$row_rs_individupostit['postit']='';
		}
		
		// liste des commandes pour attestation de stage
		if($row_rs_individu['codelibcat']=='STAGIAIRE')
		{ $query_rs =	"SELECT distinct commande.codecommande, numcommande,commande.datecommande,libfournisseur,commande.codenature,sum(montantengage) as montant".
									" FROM commandeimputationbudget, commande".
									" WHERE commande.codecommande<>''".
									" and commande.codecommande=commandeimputationbudget.codecommande".
									" and commandeimputationbudget.virtuel_ou_reel=".GetSQLValueString('1', "text").
									" and LOWER(libfournisseur) like ".GetSQLValueString('%'.strtolower($row_rs_individu['nom']).'%', "text").
									" and LOWER(objet) like '%gratif%'".
									" and commande.estannule<>'oui' and commande.estavoir<>'oui'".
									" GROUP BY commande.codecommande";
			//echo $query_rs;
			$rs=mysql_query($query_rs) or die(mysql_error());
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_cmd_gratification[$row_rs['codecommande']]=$row_rs;
			}
			
		}

	}//fin if action=modifier
	
	if($erreur=="")
	{	// Liste des codedir
		if($row_rs_individu['codecorps']=='51')
		{ $rs=mysql_query(" select numordre,codedir,taux_encadrement,codeed from sujetdir,individu".
											" where sujetdir.codesujet=".GetSQLValueString($row_rs_individu['codesujet'],"text").
											" and sujetdir.codedir=individu.codeindividu".
											" order by numordre") or die(mysql_error());
			// ed du 1er dir.
			$first=true;
			while($row_rs=mysql_fetch_assoc($rs))
			{ $tab_rs_dir[$row_rs['numordre']]['codedir']=$row_rs['codedir'];
				$tab_rs_dir[$row_rs['numordre']]['taux_encadrement']=$row_rs['taux_encadrement'];
				if($first && $row_rs_individu['codeed_these']=='')
				{ $first=false;
					$row_rs_individu['codeed_these']=$row_rs['codeed'];
				}
			}
		}
	}
	else// erreur : écrasement des valeurs des champs par celles du POST s'il contient ces champs
	{ $tables=array('individu','individusejour','individuemploi','individusujet');
		if($codecorps=='51')//doctorant
		{ $tables[]='individuthese';
			$tables[]='sujet';
		}
		foreach($tables as $table)
		{	$rs_fields = mysql_query('SHOW COLUMNS FROM '.$table);
			while($row_rs_fields = mysql_fetch_assoc($rs_fields)) 
			{ $Field=$row_rs_fields['Field'];
				if(isset($_POST[$Field]) || (isset($_POST[$Field.'_jj']) && isset($_POST[$Field.'_mm']) && isset($_POST[$Field.'_aaaa']))
																 || (isset($_POST[$Field.'_hh']) && isset($_POST[$Field.'_mn'])))
				{ if(in_array($Field, $tab_champs_ouinon)===false)
					{ if(array_key_exists($Field,$tab_champs_date)!==false && isset($_POST[$Field.'_jj']))
						{ if($table=='individuemploi')
							{ $row_rs_individu['tab_emploi'][$numemploi][$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
							}
							else
							{ $row_rs_individu[$Field]=jjmmaaaa2date($_POST[$Field.'_jj'],$_POST[$Field.'_mm'],$_POST[$Field.'_aaaa']);
							}
						}
						else if(array_key_exists($Field,$tab_champs_heure_mn)!==false && isset($_POST[$Field.'_hh']))
						{ $row_rs_individu[$Field]=hhmn2heure($_POST[$Field.'_hh'],$_POST[$Field.'_mn']);
						}
						else
						{ if($table=='individuemploi')
							{ $row_rs_individu['tab_emploi'][$numemploi][$Field]=$_POST[$Field];
							}
							else
							{ $row_rs_individu[$Field]=$_POST[$Field];
							}
						}
					}
					else
					{ if($table=='individuemploi')
						{ $row_rs_individu['tab_emploi'][$numemploi][$Field]='oui';
						}
						else
						{ $row_rs_individu[$Field]='oui';
						}
					}
				}
				else
				{ if(in_array($Field, $tab_champs_ouinon)!==false)// champs oui/non pour lesquels $_POST n'est pas reçu (Off ou pas dans le formulaire)
					{ if($table=='individuemploi')
						{ $row_rs_individu['tab_emploi'][$numemploi][$Field]=$tab_champs_ouinon_defaut[$Field];
						}
						else
						{ $row_rs_individu[$Field]=$tab_champs_ouinon_defaut[$Field];
						}
					}
					else if($Field=='codemodifieur')
					{ $row_rs_individu[$Field]=$codeuser;
					}
					else if($Field=='date_modif')
					{ $row_rs_individu[$Field]=date("Y/m/d");
					}
				}
			}
		}

		if(isset($_POST['postit']))
		{ $row_rs_individupostit['postit']=$_POST['postit'];
		}
	}// fin if erreur

	// Champs conditionnant l'affichage des zones du formulaire										 
	// permanent ou non : affiche la partie du formulaire d'un permanent ou d'un non permanent 
	$permanent=$row_rs_individu['codestatutpers']=='01'?'oui':'non';
	
	// categorie et corps pour n'afficher que certains champs
	$codelibcat=$row_rs_individu['codelibcat'];
	$etudiant_ou_exterieur=($codelibcat=='STAGIAIRE' || $codelibcat=='DOCTORANT' || $codelibcat=='POSTDOC' || $codelibcat=='EXTERIEUR' )?"oui":"non";
	$estinvite=($codecorps=='54' || $codecorps=='56');
	/* if(isset($ue) && $ue!=''){$row_rs_individu['ue']=$ue;}
	$ue=$row_rs_individu['ue']; */
	// sujet de these
	if($codelibcat=='DOCTORANT') 
	{ $sujet_verrouille=$row_rs_individu['sujet_verrouille'];
		if($row_rs_individu['codetypeprofession_postdoc']=='')
		{ $row_rs_individu['codetypeprofession_postdoc']='08';// INCONNU par défaut
		}
	}
	else//par defaut : il n'y a pas de champ sujet_verrouille mais des tests sont faits sur $sujet_verrouille (affichage champ libre prog_rech)
	{ $sujet_verrouille='non';
	}
	
	if($codelibcat=='STAGIAIRE') 
	{ $attestation_stage=$row_rs_individu['attestation_stage']=='oui'?'oui':'non';
	}
	else//par defaut : il n'y a pas de champ attestation_stage mais des tests sont faits sur $attestation_stage (affichage champ libre prog_rech)
	{ $attestation_stage='non';
	}
	
	$demander_autorisation=true;
	$pourquoi_pas_de_demande_fsd='';
	// on comparera les sejours passes au sejour en cours
	$query_rs="select codeindividu, numsejour, datedeb_sejour, datedeb_sejour_prevu, datefin_sejour, datefin_sejour_prevu, date_autorisation".
						" from individusejour".
						" where codeindividu=".GetSQLValueString($codeindividu,"text").
						(($action=="creer" || $action=="ajouter_sejour")?
						" union select ".GetSQLValueString($codeindividu, "text")." as codeindividu, '' as numsejour,".GetSQLValueString($row_rs_individu['datedeb_sejour'], "text")." as datedeb_sejour,".
						GetSQLValueString($row_rs_individu['datedeb_sejour_prevu'], "text")." as datedeb_sejour_prevu,".
						GetSQLValueString($row_rs_individu['datefin_sejour'], "text")." as datefin_sejour,".
						GetSQLValueString($row_rs_individu['datefin_sejour_prevu'], "text")." as datefin_sejour_prevu,".
						" '' as date_autorisation from individusejour"
						:'').
						//" and datefin_sejour<".GetSQLValueString($row_rs_individu['datedeb_sejour'], "text")." and datefin_sejour<>''";
						" order by datedeb_sejour_prevu";
	$rs=mysql_query($query_rs) or die(mysql_error());
	$tab_dates_individu_sejours=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_dates_individu_sejours[$row_rs['numsejour']]=$row_rs;
	}
	// apres choix corps, pour ajout sejour on a utilise $row_rs_individu['numsejour']=$numsejour_leplusrecent
	if($action=='ajouter_sejour')
	{ $row_rs_individu['ajouter_sejour']='';// ???????????
	}
	$tab_demander_autorisation=demander_autorisation($row_rs_individu,$tab_dates_individu_sejours);
	$demander_autorisation=$tab_demander_autorisation['demander_autorisation'];
	$pourquoi_pas_de_demande_fsd=$tab_demander_autorisation['pourquoi_pas_de_demande_fsd'];
	$numdossierzrr_propose="";
	// PG fsd 20160120
	$classeur_fsd_existe=false;
	// PG fsd 20160120
	if($demander_autorisation)
	{	// PG fsd 20160120
		$rs=mysql_query("select * from individupj,typepjindividu where individupj.codetypepj=typepjindividu.codetypepj and codeindividu=".GetSQLValueString($row_rs_individu['codeindividu'], "text")." and individupj.codelibcatpj=".GetSQLValueString('sejour', "text")." and individupj.numcatpj=".GetSQLValueString($row_rs_individu['numsejour'], "text")." and codelibtypepj=".GetSQLValueString('fsd', "text"));
		if($row_rs=mysql_fetch_assoc($rs))
		{ $classeur_fsd_existe=true;
		}
		// PG fsd 20160120
	}
	$codelibtypestage=isset($row_rs_individu['codelibtypestage'])?$row_rs_individu['codelibtypestage']:'';
	$sujet_dans_liste_obligatoire=false;
	$sujet_liste_affiche=false;
	if($etudiant_ou_exterieur=="oui")
	{ $sujet_liste_affiche=true;
		if(($codelibcat=='STAGIAIRE' && $codelibtypestage=='MASTER')|| $codelibcat=='DOCTORANT')
		{ $sujet_dans_liste_obligatoire=true;
		}
		if($codelibcat=='EXTERIEUR')
		{ if($demander_autorisation)
			{ $sujet_dans_liste_obligatoire=true;
			}
		}
	}

	// ----------------------- FIN DONNEES DU FORMULAIRE DE SAISIE ------------------------------	
	// ------------------------- listes select utilisees dans le formulaire
	// referents possibles 
	/* 20170404
	$query_rs_referent ="SELECT distinct individu.codeindividu,nom,prenom,concat(nom,' ',prenom) as nomprenom, codetheme ". 
											" FROM individu,individusejour,corps,individutheme".
											" WHERE individu.codeindividu=individusejour.codeindividu".
											 periodeencours('datedeb_sejour','datefin_sejour').
											" and (".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_individu['datedeb_sejour']."'","'".$row_rs_individu['datefin_sejour']."'").
											" or individu.codeindividu='".$row_rs_individu['codereferent'].
														"')".
											
											" and individusejour.codecorps=corps.codecorps".
											" and individusejour.codeindividu=individutheme.codeindividu and individusejour.numsejour=individutheme.numsejour".
											" and ".periodeencours('datedeb_theme','datefin_theme').
											" and individu.codeindividu<>''".
											" UNION select codeindividu,nom,prenom,'[--- Choix obligatoire ---]' as nomprenom, '' as codetheme from individu where codeindividu=''".
											" ORDER BY nom, prenom";
	*/
	$query_rs_referent ="SELECT distinct individu.codeindividu,nom,prenom,concat(nom,' ',prenom) as nomprenom ". 
											" FROM individu,individusejour".
											" WHERE individu.codeindividu=individusejour.codeindividu".
											" and (".intersectionperiodes('datedeb_sejour','datefin_sejour',"'".$row_rs_individu['datedeb_sejour']."'","'".$row_rs_individu['datefin_sejour']."'").
											" 			or individu.codeindividu='".$row_rs_individu['codereferent'].
														"')".
											" and individu.codeindividu<>''".
											" UNION select codeindividu,nom,prenom,'[--- Choix obligatoire ---]' as nomprenom from individu where codeindividu=''".
											" ORDER BY nom, prenom";
	// 20170404 fin 
	$rs_referent = mysql_query($query_rs_referent) or die(mysql_error());

	// Tous les themes a afficher
	$query_rs_theme = "select codestructure as codetheme,libcourt_fr,date_fin from structure where codestructure<>'' and esttheme='oui' order by codestructure";
	$rs_theme=mysql_query($query_rs_theme) or die(mysql_error());	
	if($GLOBALS['avecequipe'])
	{ $query_rs = "select codestructure as codeequipe,codepere as codetheme,libcourt_fr,date_fin from structure where codestructure<>'' and estequipe='oui' order by codetheme,codeequipe";
	  $rs=mysql_query($query_rs) or die(mysql_error());
		while($row_rs=mysql_fetch_assoc($rs))
		{ $tab_themeequipe[$row_rs['codetheme']][$row_rs['codeequipe']]=$row_rs;
		}
	}

	$query_rs_civilite = "SELECT * FROM civilite ORDER BY codeciv ASC";
	$rs_civilite = mysql_query($query_rs_civilite) or die(mysql_error());
	
	$query_rs_etab = "SELECT codeetab,libcourtetab_fr as libetab_fr  FROM etab WHERE ".periodeencours('date_deb','date_fin')." ORDER BY numordre ASC";
	$rs_etab = mysql_query($query_rs_etab) or die(mysql_error());
	
	/* $query_rs_etab_avant_ul = "SELECT codeetab,libcourtetab_fr as libetab_fr  FROM etab WHERE ".periodepassee('date_fin')." or codeetab='' ORDER BY numordre ASC";
	$rs_etab_avant_ul = mysql_query($query_rs_etab_avant_ul) or die(mysql_error()); */
	
	$query_rs_lieu = "SELECT codelieu,if(codelieu='','[ Obligatoire ]',libcourtlieu) as liblieu".
									"  FROM lieu WHERE ".periodeencours('date_deb','date_fin')." order by libcourtlieu";
	$rs_lieu = mysql_query($query_rs_lieu) or die(mysql_error());
	
	$query_rs_corpsgrade = "select corps.codecorps,grade.codegrade, libgrade_fr".
													" from corps,grade".
													" where corps.codecorps=".GetSQLValueString($row_rs_individu['codecorps'], "text").
													" and corps.codecorps=grade.codecorps".
													" and ".periodeencours('corps.date_deb','corps.date_fin').
													" order by grade.codegrade DESC";
	$rs_corpsgrade = mysql_query($query_rs_corpsgrade) or die(mysql_error());

	// sujets en fonction du type d'étudiant ou postdoc et qui ne sont pas affectés a quelqu'un sauf le sujet de l'individu traité ou encore poly attribuable
	if(array_key_exists($codelibcat,$tab_etudiant_ou_exterieur_typesujet))
	{ $query_rs_sujet = "(SELECT sujet.codesujet as codesujet,sujet.codestatutsujet as codestatutsujet,sujet.titre_fr AS titre_sujet, ".
											" structure.codestructure as codetheme, structure.libcourt_fr as libtheme,sujet.codetypestage, datedeb_sujet".
											" FROM sujet, sujettheme, structure ".
											" WHERE sujet.codetypesujet = ".GetSQLValueString($tab_etudiant_ou_exterieur_typesujet[$codelibcat], "text").
											" AND ( ".
											" 		  ( (sujet.codestatutsujet='E' OR sujet.codestatutsujet='V' OR sujet.codestatutsujet='P') ".
											" 				AND (sujet.codesujet NOT IN (SELECT individusujet.codesujet FROM individusujet )".
											// 12/02/2015 ajout pour sujets de these poly attribuables
											"								OR (sujet.codetypesujet='03' ".
											"										AND sujet.codesujet IN (SELECT individusujet.codesujet".
											"																						FROM individuthese,individusujet".
											"																						WHERE individuthese.codeindividu=individusujet.codeindividu AND individuthese.numsejour=individusujet.numsejour".
											"																						AND sujet_verrouille<>'oui')".
											"										)".
											// 12/02/2015 fin ajout pour sujets de these poly attribuables
											"							)".
											" 				AND sujet.codetypestage=".GetSQLValueString($codetypestage, "text").
											"					AND ".intersectionperiodes('structure.date_deb','structure.date_fin',GetSQLValueString($row_rs_individu['datedeb_sejour'], "text"),GetSQLValueString($row_rs_individu['datefin_sejour'], "text")).
											"				)".
											" 		  OR  sujet.codesujet=".GetSQLValueString($row_rs_individu['codesujet'], "text").
											"			)".
											" AND sujet.codesujet=sujettheme.codesujet".
											" AND sujettheme.codetheme=structure.codestructure".
											" AND sujet.codesujet<>''".
											" AND structure.codestructure<>''".
											" AND sujettheme.codetheme<>'')".
											" UNION (select '' as codesujet,'' as codestatutsujet, '' as titre_sujet,".
											" '' as codetheme, '' as libtheme, codetypestage,'' as datedeb_sujet from sujet where codesujet='')".
											" ORDER BY titre_sujet";
		$rs_sujet = mysql_query($query_rs_sujet) or die(mysql_error());
		while($row_rs_sujet = mysql_fetch_assoc($rs_sujet))
		{ $tab_rs_sujettheme[$row_rs_sujet['codesujet']][$row_rs_sujet['codetheme']]=$row_rs_sujet;//pour liste select sujet en fonction du theme choisi
		}
	}
	$query_rs="select codesujet,codeindividu,numsejour from individusujet where codesujet<>'' and codeindividu<>".GetSQLValueString($codeindividu, "text");
	$rs = mysql_query($query_rs) or die(mysql_error());
	$tabsujet_associe_a_autre_individu=array();
	while($row_rs = mysql_fetch_assoc($rs))
	{ $tabsujet_associe_a_autre_individu[$row_rs['codesujet']]=$row_rs;//pour liste select sujet en fonction du theme choisi
	}
	
	$query_rs_centrecout_pers = "SELECT * FROM centrecout_pers".
															" where ".periodeencours('centrecout_pers.date_deb','centrecout_pers.date_fin').
															" or date_fin='2012/12/31' ORDER BY numordre";
	$rs_centrecout_pers = mysql_query($query_rs_centrecout_pers) or die(mysql_error());
	
	$query_rs_bap = "SELECT codebap, libcourtbap FROM bap where ".periodeencours('bap.date_deb','bap.date_fin')." order by codebap";
	$rs_bap = mysql_query($query_rs_bap) or die(mysql_error());
	
	$query_rs_gesttheme = "SELECT distinct gesttheme.codegesttheme as codegesttheme,concat(prenom,' ', nom) as nomprenomgesttheme FROM individu, gesttheme WHERE gesttheme.codegesttheme= individu.codeindividu ORDER BY prenom ";
	$rs_gesttheme = mysql_query($query_rs_gesttheme) or die(mysql_error());
	
	$query_rs = "SELECT codecommission, libcourtcommission_fr as libcommission FROM commission ORDER BY libcommission";
	$rs = mysql_query($query_rs) or die(mysql_error());
	$tab_commission=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_commission[$row_rs['codecommission']]=$row_rs;
	}

	$query_rs = "SELECT codesection, codecommission, numsection, if(length(liblongsection_fr)>50,concat(substring(liblongsection_fr,1,50),'...'),liblongsection_fr) as libsection FROM commissionsection ORDER BY numsection";
	$rs = mysql_query($query_rs) or die(mysql_error());
	$tab_commissionsection=array();
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_commissionsection[$row_rs['codesection']]=$row_rs;
	}


	$query_rs_ed = "SELECT codeed, libcourted_fr as libed, lienpghttped FROM ed where ".periodeencours('ed.date_deb','ed.date_fin')." ORDER BY codeed";
	$rs_ed = mysql_query($query_rs_ed) or die(mysql_error());

	$query_rs_diplome = "SELECT codediplome,libcourtdiplome_fr as libdiplome_fr FROM diplome where ".periodeencours('diplome.date_deb','diplome.date_fin');
	$rs_diplome = mysql_query($query_rs_diplome) or die(mysql_error());
	
	$query_rs_typeprofession_postdoc = 	"SELECT codetypeprofession_postdoc,libcourttypeprofession_postdoc as libtypeprofession_postdoc".
																			" FROM typeprofession_postdoc where codetypeprofession_postdoc<>'' and ".periodeencours('typeprofession_postdoc.date_deb','typeprofession_postdoc.date_fin').
																			" UNION".
																			" SELECT codetypeprofession_postdoc,'[- Choix obligatoire -]' as libtypeprofession_postdoc".
																			" FROM typeprofession_postdoc where codetypeprofession_postdoc=''". 
																			" order by codetypeprofession_postdoc";
	$rs_typeprofession_postdoc = mysql_query($query_rs_typeprofession_postdoc) or die(mysql_error());
	
	$query_rs_corpsmodefinancement= "SELECT modefinancement.codemodefinancement,liblongmodefinancement as libmodefinancement FROM modefinancement,corpsmodefinancement where ".
																	" modefinancement.codemodefinancement=corpsmodefinancement.codemodefinancement".
																	" and corpsmodefinancement.codecorps= ".GetSQLValueString($row_rs_individu['codecorps'], "text"). 
																	" and ".periodeencours('modefinancement.date_deb','modefinancement.date_fin').
																	" order by numordre";
	$rs_corpsmodefinancement = mysql_query($query_rs_corpsmodefinancement) or die(mysql_error());
	$query_rs_pays =" SELECT codepays,libpays,libnat,numordre FROM pays where codepays<>''".
									" UNION".
									" SELECT codepays,'[ Choix obligatoire ]' as libpays,'[ Choix obligatoire ]' as libnat,numordre".
									" FROM pays where codepays=''". 
									" order by numordre asc,codepays asc";
	$rs_pays = mysql_query($query_rs_pays) or die(mysql_error());
	
	$query_rs_cotutelle_pays =" SELECT codepays,libpays,numordre FROM pays order by numordre asc,codepays asc";
	$rs_cotutelle_pays = mysql_query($query_rs_cotutelle_pays) or die(mysql_error());
	
	$query_rs_pays_etab_orig =" SELECT codepays,libpays,numordre FROM pays where codepays<>''".
														" UNION".
														" SELECT codepays,'[Choix obligatoire]' as libpays,numordre".
														" FROM pays where codepays=''". 
														" order by numordre asc,codepays asc";
	$rs_pays_etab_orig = mysql_query($query_rs_pays_etab_orig) or die(mysql_error());
	
	$query_rs_pays_postdoc ="SELECT codepays,libpays,numordre FROM pays order by numordre asc,codepays asc";
	$rs_pays_postdoc = mysql_query($query_rs_pays_postdoc) or die(mysql_error());
	
	$query_rs_pays_autreetab =" SELECT codepays,libpays,numordre FROM pays".
														" where codepays<>'' UNION".
														" SELECT codepays,'[Choix obligatoire]' as libpays,numordre". 
														" FROM pays where codepays=''".
														" order by numordre asc,codepays asc";
	$rs_pays_autreetab = mysql_query($query_rs_pays_autreetab) or die(mysql_error());


	$query_rs_typepieceidentite =	"SELECT codetypepieceidentite,if(codetypepieceidentite='','[ Choix obligatoire ]',libcourttypepieceidentite) as libtypepieceidentite,numordre FROM typepieceidentite". 
																" order by numordre";
	$rs_typepieceidentite = mysql_query($query_rs_typepieceidentite) or die(mysql_error());
	
	$query_rs_situationprofessionnelle = "SELECT codesituationprofessionnelle, if(codesituationprofessionnelle='','[ Choix obligatoire ]',libsituationprofessionnelle) as libsituationprofessionnelle, numordre".
																			 " FROM situationprofessionnelle".
																			 " order by numordre";
	$rs_situationprofessionnelle = mysql_query($query_rs_situationprofessionnelle) or die(mysql_error());
	
	$query_rs_typeacceszrr = "SELECT codetypeacceszrr, if(codetypeacceszrr='','[ Choix obligatoire ]',libtypeacceszrr) as libtypeacceszrr, numordre".
													 " FROM typeacceszrr".
													 " order by numordre";
	$rs_typeacceszrr = mysql_query($query_rs_typeacceszrr) or die(mysql_error());
	
	$query_rs_zrr_naturefinancement = "SELECT codenaturefinancement, libnaturefinancement, numordre".
																	 " FROM zrr_naturefinancement".
																	 " order by numordre";
	$rs_zrr_naturefinancement = mysql_query($query_rs_zrr_naturefinancement) or die(mysql_error());
	
	$query_rs_zrr_originefinancement = "SELECT codeoriginefinancement, if(codeoriginefinancement='','[ Choix obligatoire ]',liboriginefinancement) as liboriginefinancement, numordre".
																	 " FROM zrr_originefinancement".
																	 " order by numordre";
	$rs_zrr_originefinancement = mysql_query($query_rs_zrr_originefinancement) or die(mysql_error());
	
	$query_rs_zrr_sourcefinancement = "SELECT codesourcefinancement, libsourcefinancement, numordre".
																	 " FROM zrr_sourcefinancement".
																	 " order by numordre";
	$rs_zrr_sourcefinancement = mysql_query($query_rs_zrr_sourcefinancement) or die(mysql_error());
	
	$query_rs_zrr_physiquevirtuel = "SELECT codephysiquevirtuelzrr, if(codephysiquevirtuelzrr='','[ Choix obligatoire ]',libphysiquevirtuelzrr) as libphysiquevirtuelzrr FROM zrr_physiquevirtuel".
																	 " order by numordre";
	$rs_zrr_physiquevirtuel = mysql_query($query_rs_zrr_physiquevirtuel) or die(mysql_error());
	
	$rs_ouinon=mysql_query("SELECT codeouinon,libcourt as libouinon FROM ouinon WHERE codeouinon<>'' order by numordre desc");

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
	$tab_typepjindividu=array();
	$query_rs =	"SELECT * FROM typepjindividu where estutilise='oui'";
	$rs = mysql_query($query_rs) or die(mysql_error());
	while($row_rs=mysql_fetch_assoc($rs))
	{ $tab_typepjindividu[$row_rs['codelibtypepj']]=$row_rs['estutilise'];
	}
}	

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Gestion des personnels <?php echo $GLOBALS['acronymelabo'] ?></title>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>">
<link rel="icon" type="image/png" href="images/12plus.ico">
<link rel="stylesheet" href="styles/normal.css">
<link rel="stylesheet" href="styles/tableau_bd.css">
<link href="SpryAssets/SpryTooltip.css" rel="stylesheet" type="text/css">
<!-- <script src="_java_script/functions.js" type="text/javascript" language="javascript"></script>-->
<script src="_java_script/tooltip.js" type="text/javascript" language="javascript"></script>
<script src="_java_script/alerts.js" type="text/javascript"></script>
<script src="SpryAssets/SpryTooltip.js" type="text/javascript"></script>
<script language="javascript">
	var w;
	function OuvrirVisible(url)
	{ 
		w=window.open(url,'detailsujet',"scrollbars = yes,width=700,height=350,location=no,mebubar=no,status=no,directories=no");
		w.document.close();
		w.focus();
	}
	function Fermer() 
	{ if (w.document) { w.close(); }
	}
	<?php if($action=='creer')
	{?> 
		var tab_ind_bd=new Array();
		<?php 
		foreach($tab_ind_bd as $nomprenom=>$row_rs)
		{?>
			tab_ind_bd["<?php echo js_tab_val($nomprenom) ?>"]="<?php echo js_tab_val($row_rs['nom'].' '.$row_rs['prenom']); ?>";
		<?php 
		}
	}
	?>
	var tab_domainescientifique=new Array();
	var tab_disciplinescientifique=new Array();
	var tab_objectifscientifique=new Array();
	<?php 
	$nb=0;
	foreach($tab_domainescientifique as $codedomainescientifique=>$row_rs)
	{?> var o=new Object();
		o["codedomainescientifique"]="<?php echo $row_rs['codedomainescientifique']; ?>";
		o["libdomainescientifique"]="<?php echo js_tab_val($row_rs['libdomainescientifique']) ?>";
		tab_domainescientifique[<?php echo $nb ?>]=o;
	<?php 
		$nb++; 
	}
	?>
	nbdomainescientifique=<?php echo $nb; ?>;
	<?php 
	$nb=0;
	foreach($tab_disciplinescientifique as $codedisciplinescientifique=>$row_rs)
	{?> 
		var o=new Object();
		o["codedisciplinescientifique"]="<?php echo js_tab_val($row_rs['codedisciplinescientifique']) ?>";
		o["codedomainescientifique"]="<?php echo js_tab_val($row_rs['codedomainescientifique']); ?>";
		o["libdisciplinescientifique"]="<?php echo js_tab_val($row_rs['libdisciplinescientifique']) ?>";
		tab_disciplinescientifique[<?php echo $nb ?>]=o;
	<?php 
		$nb++; 
	}
	 ?>
	nbdisciplinescientifique=<?php echo $nb; ?>;
	
	function affichedisciplinescientifique(champ)
	{ var frm=document.forms["<?php echo $form_fiche_dossier_pers ?>"];
		var valchamp=champ.value;
		var firstdisciplinescientifique=true;
		numordre=champ.name.substr((new String('codedomainescientifique')).length);
		listedisciplinescientifique=frm.elements['codedisciplinescientifique'+new String(numordre)];
		listedisciplinescientifique.options.length=0;
		if(numordre==1)
		{ listedisciplinescientifique.options[0]=new Option("[choix obligatoire]","");
		}
		else
		{ listedisciplinescientifique.options[0]=new Option("","");
		}
		for(i=0;i<nbdisciplinescientifique;i++)
		{ if(tab_disciplinescientifique[i].codedomainescientifique==valchamp)
			{ if(firstdisciplinescientifique)
				{ valdisciplinescientifique=tab_disciplinescientifique[i].codedisciplinescientifique;
					firstdisciplinescientifique=false;
				}
				listedisciplinescientifique.options[listedisciplinescientifique.options.length]=new Option(tab_disciplinescientifique[i].libdisciplinescientifique, tab_disciplinescientifique[i].codedisciplinescientifique);
			}
		}
	}
	
	var tab_commission=new Array();
	var tab_commissionsection=new Array();
	<?php 
	$nb=0;
	foreach($tab_commission as $codecommission=>$row_rs)
	{?> var o=new Object();
		o["codecommission"]="<?php echo $row_rs['codecommission']; ?>";
		o["libcommission"]="<?php echo js_tab_val($row_rs['libcommission']) ?>";
		tab_commission[<?php echo $nb ?>]=o;
	<?php 
		$nb++; 
	}
	?>
	nbcommission=<?php echo $nb; ?>;
	
	<?php 
	$nb=0;
	foreach($tab_commissionsection as $codesection=>$row_rs)
	{?> 
		var o=new Object();
		o["codesection"]="<?php echo js_tab_val($row_rs['codesection']) ?>";
		o["codecommission"]="<?php echo js_tab_val($row_rs['codecommission']); ?>";
		o["numsection"]="<?php echo js_tab_val($row_rs['numsection']) ?>";
		o["libsection"]="<?php echo js_tab_val($row_rs['libsection']) ?>";
		tab_commissionsection[<?php echo $nb ?>]=o;
	<?php 
		$nb++; 
	}
	 ?>
	nbsection=<?php echo $nb; ?>;
	
	function affichesection(champ)
	{ var frm=document.forms["<?php echo $form_fiche_dossier_pers ?>"];
		var valchamp=champ.value;
		var first=true;
		liste=frm.elements['codesection'];
		liste.options.length=0;
		liste.options[0]=new Option("","");
		for(i=0;i<nbsection;i++)
		{ if(tab_commissionsection[i].codecommission==valchamp)
			{ if(first)
				{ valsection=tab_commissionsection[i].codesection;
					first=false;
				}
				liste.options[liste.options.length]=new Option(tab_commissionsection[i].numsection+' '+tab_commissionsection[i].libsection, tab_commissionsection[i].codesection);
			}
		}
	}
</script>

</head>
<body <?php if($erreur!='' || $warning!='')
						{?>onLoad="alert('<?php echo html2js($erreur).($erreur!='' && $warning!=''?'\\n':'').html2js($warning) ?>')"
						<?php 
						}?>
>
<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" align="center" cellpadding="0" cellspacing="0">
	<?php echo entete_page(array('image'=>'images/b_couple.png','titrepage'=>'FICHE <span class="mauvegrascalibri11">'.$etat_individu_entete[$etat_individu].'</span>','lienretour'=>'gestionindividus.php?ind_ancre='.$ind_ancre,'texteretour'=>'Retour &agrave; la gestion des dossiers des personnels',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser,'erreur'=>$erreur,'affiche_succes'=>$affiche_succes,
                                'message_resultat_affiche'=>$message_resultat_affiche,'warning'=>$warning,'information_defaut'=>$information_defaut)) ?>
  <tr>
    <td><!--	// PG fsd 20160120 -->
				<div id="chargementencours" class="cache">
          <table class="table_chargementencours" cellpadding="5">
            <tr>
              <td>
                <div id="chargementencours_texte" class="noirgrascalibri10">Enregistrement</div>
              </td>
            </tr>
          </table>
        </div>
        <!--	// PG fsd 20160120 -->
    </td>
  </tr>
  <tr>
    <td>
     	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="0">
      <?php 
      	if(!$avant_choix_corps && $GLOBALS['estzrr'])
				{ ?>
        <tr>
        	<td>
          	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
            	<tr>
              	<td><table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>"  class="table_cadre_arrondi_rouge_fond_rose"><tr><td class="bleucalibri10">Infos. dde FSD</td></tr></table>
              	</td>
              	
                <td><table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>"  class="table_cadre_arrondi_rouge_fond_beige"><tr><td class="bleucalibri10">Infos. restreintes SRH</td></tr></table>
              	</td>

              </tr>
            </table>
          </td>
        </tr>
        <?php 
				}?>
      	<tr>
        	<td>
         		<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
          		<tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="2">
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="0">
                          <tr>
                            <td>
                             	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="0">
                                <tr>
                                  <td nowrap>
                                  <?php
                                  if($action=='creer')
                                  { ?><span class="bleugrascalibri10">Cr&eacute;ation de dossier personnel</span>
                                  <?php
                                  }
                                  else
                                  { ?><span class="bleugrascalibri10">Dossier personnel n&deg;&nbsp;</span><span class="infomauve"><?php echo $codeindividu ?></span>
                                      &nbsp;<span class="bleugrascalibri10">Num. s&eacute;jour : </span><span class="infomauve"><?php echo $numsejour ?></span>
                                  <?php 
                                  }?>
                                  </td>
                                  <td nowrap class="bleugrascalibri10"> <img src="images/espaceur.gif" width="10" height="1">-<img src="images/espaceur.gif" alt="" width="10" height="1">
                                  </td>
                                  <?php 
                                  if($avant_choix_corps)
                                  { ?>
                                    <form name="form_fiche_dossier_pers_choix_corps" id="form_fiche_dossier_pers_choix_corps" method="POST"  action="<?php echo $editFormAction; ?>"  onSubmit="return controle_form_fiche_dossier_pers_choix_corps('form_fiche_dossier_pers_choix_corps')">
                                    <input type="hidden" name="etat_individu" value="<?php echo $etat_individu ?>">
                                    <input type="hidden" name="action" value="<?php echo $action ?>">
                                    <input type="hidden" name="codeindividu" value="<?php echo $codeindividu ?>">
                                    <input type="hidden" name="numsejour_leplusrecent" value="<?php echo $numsejour_leplusrecent ?>">
                                    <!-- 20161213 -->
                                    <?php 
																		if(!($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte)))
                                    {?> <input type="hidden" name="limitation5joursmax">
                                    <?php 
																		}
																		
                                    if($action=='ajouter_sejour')
                                    {?> 
                                    <td width="33%" nowrap><span class="bleugrascalibri10">
                                    <?php echo  $row_rs_individu['libciv_fr'].' '.$row_rs_individu['nom'].' '.$row_rs_individu['prenom']?>
                                    </span>
                                    </td>
                                    <?php
                                    }
                                  }
                                  else
                                  { ?>
                                  <td width="33%" nowrap><span class="bleugrascalibri10">R&eacute;f&eacute;rent </span><span class="bleucalibri9">(membre <?php echo $GLOBALS['acronymelabo'] ?> charg&eacute; de l'accueil)</span><span class="bleugrascalibri10"> :&nbsp;</span>
                                  </td>
                                  	<!--	// PG fsd 20160120 -->
                                    <form name="<?php echo $form_fiche_dossier_pers ?>" method="POST" enctype="multipart/form-data" action="<?php echo $editFormAction/* .$ancre */; ?>" target="_self" onSubmit="return controle_form_fiche_dossier_pers('<?php echo $form_fiche_dossier_pers ?>','')">
                                    <!--	// PG fsd 20160120 -->
                                    <input type="hidden" name="MM_update" value="<?php echo $form_fiche_dossier_pers ?>">
                                    <input type="hidden" name="action" value="<?php echo $action ?>">
              											<input type="hidden" name="ind_ancre" value="<?php echo $ind_ancre ?>"> 
                                    <input type="hidden" name="codeindividu" value="<?php echo $row_rs_individu['codeindividu']; ?>">
                                    <input type="hidden" name="numsejour_leplusrecent" value="<?php echo $numsejour_leplusrecent ?>">
                                    <input type="hidden" name="numsejour" value="<?php echo $row_rs_individu['numsejour']; ?>">
                                    <input type="hidden" name="ajouter_emploi" value="<?php echo $afficher_nouvel_emploi?'oui':'non'; ?>">
                                    <input type="hidden" name="numemploi" value="<?php echo $numemploi; ?>">
                                    <input type="hidden" name="etat_individu" value="<?php echo $etat_individu ?>">
                                    <input type="hidden" name="permanent" value="<?php echo $permanent ?>">
                                    <input type="hidden" name="demander_autorisation" value="<?php echo $demander_autorisation  ?>">
                                    <input type="hidden" name="etudiant_ou_exterieur" value="<?php echo $etudiant_ou_exterieur ?>">
                                    <input type="hidden" name="codesujetenregistre" value="<?php echo $row_rs_individu['codesujet'] ?>">
                                    <input type="hidden" name="codecorps" value="<?php echo $codecorps ?>">
                                    <input type="hidden" name="codetypestage" value="<?php echo $codetypestage ?>">
																		<input type="image" src="images/espaceur.gif" width="0" height="0"> <!-- bouton submit par defaut pour ne pas en declencher un autre avec touche ENTREE-->
                                    <!-- 20161213 -->
                                    <?php 
																		if(!($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte)))
                                    {?> <input type="hidden" name="limitation5joursmax">
                                    <?php 
																		}
                                    if($action=='creer' || $action=='ajouter_sejour')
                                    {?> 
                                    <input type="hidden" name="codestatutpers" value="<?php echo $codestatutpers ?>">
                                      <?php 
                                    }?>
                                  <td width="5%" nowrap>
                                    <select name="codereferent" class="noircalibri10" id="codereferent" onChange="//modif 08122013inittheme(this.form.elements['codereferent'].options[this.form.elements['codereferent'].selectedIndex].value)">
                                    <?php
                                    $codereferent_prec="-1";
                                    while ($row_rs_referent = mysql_fetch_assoc($rs_referent)) 
                                    { $codereferent=$row_rs_referent['codeindividu'];
                                      if($codereferent!=$codereferent_prec)
                                      { ?>
                                      <option value="<?php echo $codereferent?>" <?php echo ($codereferent==$row_rs_individu['codereferent'])?'selected':''; ?>><?php echo $row_rs_referent['nomprenom']?></option>
                                      <?php
                                        $codereferent_prec=$codereferent;
                                      }
                                    }
                                    ?>
                                    </select>
                                  </td>
                                  <td width="12%" nowrap>&nbsp;
                                    <span class="bleugrascalibri10">Suivi par :&nbsp;</span>
                                  </td>
                                  <td width="12%" nowrap>
                                    <select name="codegesttheme" class="noircalibri10" id="codegesttheme">
                                    <?php
                                    while ($row_rs_gesttheme = mysql_fetch_assoc($rs_gesttheme)) 
                                    { ?>
                                      <option value="<?php echo $row_rs_gesttheme['codegesttheme']?>" <?php echo $row_rs_gesttheme['codegesttheme']==$row_rs_individu['codegesttheme']?'selected':''?>><?php echo $row_rs_gesttheme['nomprenomgesttheme']?></option>
                                    <?php
                                    }
                                    ?>
                                    </select>
                                  <?php 
                                  }?>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <tr>
                          <?php 
                          if($avant_choix_corps)
                          {	if($action=='ajouter_sejour')
                            {?> 
                            <td nowrap>
                            <span class="bleugrascalibri10">Nouveau s&eacute;jour</span><br>
                             </td>
                            <?php
                            }
                          }
                          else
                          {?>
                            <td nowrap>
                            <span class="bleucalibri9">Cr&eacute;&eacute; par : </span>
                            <span class="infomauve"><?php echo ($row_rs_individu['codecreateur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_individu['createurprenom']." ".$row_rs_individu['createurnom']); ?></span>
                            <span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_individu['date_creation'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_individu['date_creation'],"/")) ?></span>
                            <img src="images/espaceur.gif" width="10" height="1"><span class="bleucalibri9">Modifi&eacute; par : </span>
                            <span class="infomauve"><?php echo ($row_rs_individu['codemodifieur']==""?$tab_infouser['prenom']." ".$tab_infouser['nom']:$row_rs_individu['modifieurprenom']." ".$row_rs_individu['modifieurnom']); ?></span>
                            <span class="bleucalibri9">, le : </span><span class="infomauve"><?php echo (aaaammjj2jjmmaaaa($row_rs_individu['date_modif'],"/")==""?date("d/m/Y"):aaaammjj2jjmmaaaa($row_rs_individu['date_modif'],"/")) ?></span>
                            </td>
                          <?php 
                          }?>
                          </tr>
                        </table>
                      </td>
              			</tr>
                    <tr>
                      <td nowrap>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="0" width="100%">
                          <tr>
                            <td>
                              <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%">
                                <tr>
                                  <?php 
                                  if($avant_choix_corps)													
                                  {?>
                                  <td nowrap>
                                  <span class="bleugrascalibri10">Statut :&nbsp;</span>
                                  </td>
                                  <td nowrap>
                                    <select name="codestatutpers" class="noircalibri10" id="codestatutpers" 
                                    onChange="updatecorps(this.selectedIndex);/* updatecorpsue(this.selectedIndex); */
                                    					if(this.value=='02' && document.form_fiche_dossier_pers_choix_corps.elements['codecorps']=='52')
                                     					{ document.getElementById('typestage').className='affiche'
                                              }
                                              else
                                     					{ document.getElementById('typestage').className='cache'
                                              }">
                                    <?php 
                                    foreach($tab_statutpers as $codestatutpers=>$libstatutpers_fr)
                                    { ?>
                                      <option value="<?php echo $codestatutpers ?>" <?php echo ($row_rs_individu['codestatutpers']==$codestatutpers?'selected':'') ?>><?php echo $libstatutpers_fr ?></option>
                                    	<?php
                                    }?>
                                    </select>
                                  </td>
                                  <td nowrap>
                                    <select name="codecorps" class="noircalibri10" id="codecorps"
                                     onchange="if(this.value=='52')
                                     					{ document.getElementById('typestage').className='affiche'
                                              }
                                              else
                                     					{ document.getElementById('typestage').className='cache';
                                              }
                                              "
                                     >
                                      <?php
                                      foreach($tab_statutperscorps[$row_rs_individu['codestatutpers']] as $un_codecorps=>$libcorps)
                                      { ?>
                                      <option value="<?php echo $un_codecorps ?>" <?php echo ($row_rs_individu['codecorps']==$un_codecorps?'selected':'') ?>><?php echo $libcorps ?></option>
                                      <?php
                                      } ?>
                                    </select>
                                      <script type="text/javascript">
                                        var statutperslist=document.form_fiche_dossier_pers_choix_corps.codestatutpers
                                        var corpslist=document.form_fiche_dossier_pers_choix_corps.codecorps
                                        var corps=new Array()
                                        <?php
                                        $i=0;
                                        foreach($tab_statutpers as $codestatutpers=>$libstatutpers_fr)
                                        { $tab_corps=$tab_statutperscorps[$codestatutpers];?>
                                          corps[<?php echo $i ?>]=[
                                          <?php 
                                          $j=1;
                                          foreach($tab_corps as $un_codecorps=>$libcorps)
                                          { ?>
                                            "<?php echo $libcorps ?>|<?php echo $un_codecorps ?>"<?php echo ($j==count ($tab_corps)?'':',') ?>
                                            <?php 
                                            $j++;
                                          } ?>
                                          ]
                                          <?php 
                                          $i++;	
                                        }?>
                                        function updatecorps(selectedstatutpers)/* function updatecorpsue(selectedstatutpers) */
                                        { /* if(selectedstatutpers==0) 
                                          {document.form_fiche_dossier_pers_choix_corps.ue[0].checked=true
                                          }
                                          else {document.form_fiche_dossier_pers_choix_corps.ue[1].checked=true} */
                                          corpslist.options.length=0
                                          for (i=0; i<corps[selectedstatutpers].length; i++)
                                          { corpslist.options[corpslist.options.length]=new Option(corps[selectedstatutpers][i].split("|")[0], corps[selectedstatutpers][i].split("|")[1])
                                          }
                                        }
                                      </script>
                                  </td>
                                  <td>
                      							<div id="typestage" class="<?php echo $row_rs_individu['codecorps']=='52'?'affiche':'cache'?>">
                 										<select name="codetypestage" id="codetypestage" class="noircalibri10">
																		<?php 
																		while($row_rs_typestage=mysql_fetch_assoc($rs_typestage))
                                    {?> 
                                    	<option value="<?php echo $row_rs_typestage['codetypestage']?>"><?php echo $row_rs_typestage['libtypestage'] ?></option>
                                    <?php 
																		}?>
                                    </select>	
                                  	</div>
                                  </td>
                                  <td>&nbsp;&nbsp;
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                    	<td>
                      	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                          <tr>
                            <td class="bleugrascalibri10" nowrap>Dates du s&eacute;jour :&nbsp;&nbsp;</td>
                            <td class="bleugrascalibri10" nowrap> Arriv&eacute;e :&nbsp;</td>
                            <td nowrap>
                            	<input name="datedeb_sejour_jj" type="text" class="noircalibri10" id="datedeb_sejour_jj" value="<?php echo substr($row_rs_individu['datedeb_sejour'],8,2) ?>" size="2" maxlength="2">
                              <input name="datedeb_sejour_mm" type="text" class="noircalibri10" id="datedeb_sejour_mm" value="<?php echo substr($row_rs_individu['datedeb_sejour'],5,2) ?>" size="2" maxlength="2">
                              <input name="datedeb_sejour_aaaa" type="text" class="noircalibri10" id="datedeb_sejour_aaaa" value="<?php echo substr($row_rs_individu['datedeb_sejour'],0,4) ?>" size="4" maxlength="4">
                            </td>
                            <td class="bleugrascalibri10" nowrap>&nbsp;&nbsp;D&eacute;part :&nbsp;&nbsp;</td>
                            <td nowrap>
                            	<input name="datefin_sejour_jj" type="text" class="noircalibri10" id="datefin_sejour_jj" value="<?php echo substr($row_rs_individu['datefin_sejour'],8,2) ?>" size="2" maxlength="2">
                              <input name="datefin_sejour_mm" type="text" class="noircalibri10" id="datefin_sejour_mm" value="<?php echo substr($row_rs_individu['datefin_sejour'],5,2) ?>" size="2" maxlength="2">
                              <input name="datefin_sejour_aaaa" type="text" class="noircalibri10" id="datefin_sejour_aaaa" value="<?php echo substr($row_rs_individu['datefin_sejour'],0,4) ?>" size="4" maxlength="4">
                            </td>
                          </tr>
                        </table>
                    	</td>
                    </tr>
                    <tr>
                    	<td>
                      	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                          <tr>
                            <td nowrap class="bleugrascalibri10">Lieu de travail pour l'activit&eacute; de recherche<sup><span class="champoblig">*</span></sup> :</td>
                            <td nowrap class="bleugrascalibri10">
                            <select name="codelieu" class="noircalibri10">
                              <?php
                              while ($row_rs_lieu = mysql_fetch_assoc($rs_lieu))
                              { ?>
                              <option value="<?php echo $row_rs_lieu['codelieu']?>" <?php echo $row_rs_lieu['codelieu']==$row_rs_individu['codelieu']?'selected':'' ?>><?php echo $row_rs_lieu['liblieu']?></option>
                              <?php
                              } 
                              ?>
                              </select>
                            </td>
                          </tr>
                          <tr>
                            <td align="right" nowrap><span class="bleucalibri9">&nbsp;&nbsp;- Autre, pr&eacute;ciser :</span>&nbsp;</td>
                            <td nowrap><input name="autrelieu" type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_individu['autrelieu']) ; ?>" size="50" maxlength="100"></td>
                          </tr>
                        </table>
                    	</td>
                    </tr>
                    <tr>
                    	<td>
                        <input type="hidden" name="MM_choix" value="form_fiche_dossier_pers_choix_corps">
                        <input name="submit_choix_corps" type="submit" class="noircalibri10" id="submit_choix_corps" value="Valider"> 
                        <!-- onClick="return confirm('Valider la création')"> -->
                      </td>
                    </tr>
                    <tr>
                    	<td>
                        <div class="tooltipContent_cadre" id="info_date_sejour_prevu">
                          <span class="noircalibri10">
                          	Les dates de s&eacute;jour et le lieu d&eacute;terminent quels sont les champs requis pour l'autorisation d'acc&eacute;s au laboratoire.<br>
                          </span>
                        </div>
                       </td>
                    </tr>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" class="table_cadre_arrondi_rouge" cellpadding="3" cellspacing="3" align="center">
                          <tr>
                            <td class="noircalibri10">
                            La cr&eacute;ation d&rsquo;un nouveau dossier doit &ecirc;tre effectu&eacute;e uniquement si l&rsquo;individu ne fait pas d&eacute;j&agrave; partie de<br>
                            la base de donn&eacute;s (l&rsquo;outil de recherche de nom permet de le v&eacute;rifier). S&rsquo;il en fait partie, il faut cr&eacute;er un nouveau s&eacute;jour.<br>
                            La cr&eacute;ation d&rsquo;un nouveau s&eacute;jour <strong>doit</strong> avoir lieu dans les cas suivants :<br>
                            &nbsp;&nbsp;- changement de statut ou de corps (pas de grade)<br>
                            &nbsp;&nbsp;- d&eacute;part (il n&rsquo;est plus li&eacute; au laboratoire) puis retour au laboratoire : deux p&eacute;riodes s&eacute;par&eacute;es dans le temps<br>
                            Dans le cas d'un nouveau contrat/emploi dans le m&ecirc;me statut/corps, il n'y a pas lieu de cr&eacute;er un s&eacute;jour :<br>
                            il suffit de cr&eacute;er un nouvel emploi dans le s&eacute;jour en cours.<br>
                            Pour un doctorant (en co-tutelle par ex.) ou un stagiaire qui effectuent des p&eacute;riodes s&eacute;par&eacute;es, dans le cadre d'une meme convention, au laboratoire :<br>
                            il n&rsquo;effectue qu&rsquo;un s&eacute;jour (une p&eacute;riode dans un seul statut/corps).
                            </td>
                          </tr>
                        </table>
                      </td>
                      <?php 
                      }// fin nouveausejour
                      else//pour info statut-corps
                      { ?>
                      <td nowrap>
                      <span class="bleugrascalibri10">Statut :&nbsp;</span>
                      <span class="mauvegrascalibri10" align="left">
                        <?php echo htmlspecialchars($row_rs_individu['libstatutpers_fr'].' - '.$row_rs_individu['libcorps'].($row_rs_individu['libgrade']==''?'':' - '.$row_rs_individu['libgrade'])) ;
                        if($codelibcat=='STAGIAIRE')
                        { echo ' - '.htmlspecialchars($row_rs_individu['libtypestage']);
													if(isset($row_rs_individu['libdiplomeprep']) && $row_rs_individu['libdiplomeprep']<>'')
													{ echo ' ('.htmlspecialchars($row_rs_individu['libdiplomeprep']).')';
													}
                        } ?>
                      </span>
                      </td>
												<?php 
                        if(array_key_exists('srh',$tab_roleuser) || array_key_exists('gestperscontrat',$tab_roleuser) || $est_admin)
                        { ?>
                        <td align="right"><span class="<?php echo $row_rs_individu['codelabintel']!=''?'bleugrascalibri10':'rougegrascalibri10' ?>">R&eacute;f&eacute;rence dossier Labintel :</span>
                          <input name="codelabintel" type="text" class="noircalibri10" value="<?php echo $row_rs_individu['codelabintel']; ?>">
                          <?php if($row_rs_individu['codelabintel']!=""){ ?><a href="javascript:OuvrirVisible('https://web-ast.dsi.cnrs.fr/l3c/owa/personnel.infos_admin?p_numero_sel=<?php echo $row_rs_individu['codelabintel']; ?>')"><img src="images/b_oeil.png" name="codesujetindividu" width="16" height="16" class="icon" id="codesujetindividu" alt="D&eacute;tail du sujet"/></a><?php }?>
                        </td>
                        <?php
                        }
                      }?>
                    </tr>
                  </table>
                </td>
                </tr>
                </table>
                </td>
        			</tr>
           	</table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="10"></td>
  </tr>
	<?php
  if(!$avant_choix_corps) 
  { ?> 
  <tr>
    <td>
      <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
        <?php 
				if((array_key_exists('srh',$tab_roleuser) || $est_admin) && $GLOBALS['estzrr'])
				{ ?>
				<tr>
					<td>
						<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="52%"  cellpadding="0" cellspacing="2"  class="table_cadre_arrondi_rouge_fond_beige">
							<tr>
								<td width="10%" nowrap><span class="bleugrascalibri10">Dates pr&eacute;vues du s&eacute;jour :&nbsp;&nbsp;</span></td>
								<td width="10%" nowrap><span class="bleugrascalibri10"> Arriv&eacute;e :&nbsp;</span></td>
								<td width="10%" nowrap>
								<input name="datedeb_sejour_prevu_jj" type="text" class="noircalibri10" id="datedeb_sejour_prevu_jj" value="<?php echo substr($row_rs_individu['datedeb_sejour_prevu'],8,2); ?>" size="2" maxlength="2">
									<input name="datedeb_sejour_prevu_mm" type="text" class="noircalibri10" id="datedeb_sejour_prevu_mm" value="<?php echo substr($row_rs_individu['datedeb_sejour_prevu'],5,2); ?>" size="2" maxlength="2">
									<input name="datedeb_sejour_prevu_aaaa" type="text" class="noircalibri10" id="datedeb_sejour_prevu_aaaa" value="<?php echo substr($row_rs_individu['datedeb_sejour_prevu'],0,4); ?>" size="4" maxlength="4">
								</td>
								<td width="40%" nowrap><span class="bleugrascalibri10"> &nbsp;&nbsp;D&eacute;part :&nbsp;&nbsp;</span></td>
								<td width="40%" nowrap><input name="datefin_sejour_prevu_jj" type="text" class="noircalibri10" id="datefin_sejour_prevu_jj" value="<?php echo substr($row_rs_individu['datefin_sejour_prevu'],8,2); ?>" size="2" maxlength="2">
									<input name="datefin_sejour_prevu_mm" type="text" class="noircalibri10" id="datefin_sejour_prevu_mm" value="<?php echo substr($row_rs_individu['datefin_sejour_prevu'],5,2); ?>" size="2" maxlength="2">
									<input name="datefin_sejour_prevu_aaaa" type="text" class="noircalibri10" id="datefin_sejour_prevu_aaaa" value="<?php echo substr($row_rs_individu['datefin_sejour_prevu'],0,4); ?>" size="4" maxlength="4">
									<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_date_sejour_prevu">
									<div class="tooltipContent_cadre" id="info_date_sejour_prevu">
										<span class="noircalibri10">
											Pour les secr. de site : les dates de s&eacute;jour pr&eacute;vues ne sont pas visibles et sont remplac&eacute;es par les <br>
											dates de s&eacute;jour tant que la date de demande d'autorisation et la date d'autorisation sont vides<br>
											Pour la personne en charge des demandes d'acc&egrave;s, ces dates peuvent &ecirc;tre modifi&eacute;es.<br>
											Si la <b>dur&eacute;e pr&eacute;vue</b> est inf&eacute;rieure ou &eacute;gale &agrave; 5 jours, ou si la derni&egrave;re demande FSD est<br>
                      inf&eacute;rieure &agrave; 5 ans et les s&eacute;jours sont contig&uuml;s, la demande n'a pas lieu d'&ecirc;tre faite.<br>
                      <b>Si une date de demande d'autorisation est saisie, l'apposition du visa FSD n'est plus requise.</b>                      
										</span>
									</div>
									<script type="text/javascript">
									var sprytooltip1 = new Spry.Widget.Tooltip("info_date_sejour_prevu", "#sprytrigger_info_date_sejour_prevu", {offsetX:20, offsetY:20});
									</script>
								</td>
								<?php 
								if($demander_autorisation || $pourquoi_pas_de_demande_fsd=='FSD - de 5 ans')
								{	
								?>
                	<td class="orangecalibri10" nowrap><?php echo $pourquoi_pas_de_demande_fsd!=''?'':$pourquoi_pas_de_demande_fsd ?></td>
									<td width="40%" nowrap><span class="bleugrascalibri10">&nbsp;Date demande&nbsp;d'acc&egrave;s :&nbsp;</span>
									</td>
									<td width="40%" nowrap>
										<input name="date_demande_fsd_jj" type="text" class="noircalibri10" id="date_demande_fsd_jj" value="<?php echo substr($row_rs_individu['date_demande_fsd'],8,2); ?>" size="2" maxlength="2">
										<input name="date_demande_fsd_mm" type="text" class="noircalibri10" id="date_demande_fsd_mm" value="<?php echo substr($row_rs_individu['date_demande_fsd'],5,2); ?>" size="2" maxlength="2">
										<input name="date_demande_fsd_aaaa" type="text" class="noircalibri10" id="date_demande_fsd_aaaa" value="<?php echo substr($row_rs_individu['date_demande_fsd'],0,4); ?>" size="4" maxlength="4">
									</td>
									<td width="40%" nowrap><span class="bleugrascalibri10">&nbsp;Date d'autorisation&nbsp;:&nbsp;</span>
									</td>
									<td width="40%" nowrap>
										<input name="date_autorisation_jj" type="text" class="noircalibri10" id="date_autorisation_jj" value="<?php echo substr($row_rs_individu['date_autorisation'],8,2); ?>" size="2" maxlength="2">
										<input name="date_autorisation_mm" type="text" class="noircalibri10" id="date_autorisation_mm" value="<?php echo substr($row_rs_individu['date_autorisation'],5,2); ?>" size="2" maxlength="2">
										<input name="date_autorisation_aaaa" type="text" class="noircalibri10" id="date_autorisation_aaaa" value="<?php echo substr($row_rs_individu['date_autorisation'],0,4); ?>" size="4" maxlength="4">
									</td>
									<?php 
								}
								else
								{ ?>
								<td colspan="5" class="orangecalibri10" nowrap>Pas de demande FSD &agrave; faire : <?php echo $pourquoi_pas_de_demande_fsd ?></td>
								<?php 
								} ?>
    	        </tr>
              <?php if((array_key_exists('srh',$tab_roleuser) || $est_admin) && ($demander_autorisation || $pourquoi_pas_de_demande_fsd=='FSD - de 5 ans'))
              {?>
              <tr>
              	<td colspan="6" align="right" valign="top">
                	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_date_demande_modification_fsd">
									<div class="tooltipContent_cadre" id="info_date_demande_modification_fsd">
										<span class="noircalibri10">
                			Pour les demandes de prolongation de + de 2 mois
                      <br>et d'une dur&eacute;e inf&eacute;rieure (ou &eacute;gale) &agrave; la demande initiale,
                      <br>et SANS autre changement (NE change PAS de statut, m&ecirc;me financement, m&ecirc;me sujet, m&ecirc;me lieu de travail ZRR....) :
                      <br>vous n'&ecirc;tes pas  oblig&eacute; de refaire un dossier complet ZRR : cf point pr&eacute;c&eacute;dent.
											<br><br>Pour les demandes de prolongation de + de 2 mois
                      <br>et d'une dur&eacute;e sup&eacute;rieure (ou &eacute;gale) &agrave; la demande initiale , ou AVEC un autre changement (change de statut, PAS le m&ecirc;me financement, PAS le m&ecirc;me sujet de travail, PAS le m&ecirc;me lieu de travail ZRR....) : dossier complet (classeur XLS).
										</span>
									</div>
									<script type="text/javascript">
									var sprytooltip_date_demande_modification_fsd = new Spry.Widget.Tooltip("info_date_demande_modification_fsd", "#sprytrigger_info_date_demande_modification_fsd", {offsetX:20, offsetY:20});
									</script>

                </td>
                <td width="40%" valign="top" nowrap><span class="bleugrascalibri10">&nbsp;Date demande&nbsp;modification :&nbsp;</span>
                </td>
                <td width="40%" valign="top" nowrap>
                  <input name="date_demande_modification_fsd_jj" type="text" class="noircalibri10" id="date_demande_modification_fsd_jj" value="<?php echo substr($row_rs_individu['date_demande_modification_fsd'],8,2); ?>" size="2" maxlength="2">
                  <input name="date_demande_modification_fsd_mm" type="text" class="noircalibri10" id="date_demande_modification_fsd_mm" value="<?php echo substr($row_rs_individu['date_demande_modification_fsd'],5,2); ?>" size="2" maxlength="2">
                  <input name="date_demande_modification_fsd_aaaa" type="text" class="noircalibri10" id="date_demande_modification_fsd_aaaa" value="<?php echo substr($row_rs_individu['date_demande_modification_fsd'],0,4); ?>" size="4" maxlength="4">
                </td>
                <td class="bleugrascalibri10" valign="top" nowrap>Notes :&nbsp;</td>
                <td><textarea name="note_demande_modification_fsd" cols="40" rows="3" class="noircalibri10"><?php echo $row_rs_individu['note_demande_modification_fsd']; ?></textarea></td>
								</td>
              </tr>
              <?php 
							}?>
            </table>
          </td>
        </tr>
				<?php 
        }
				 ?>
				<tr>
        	<td>
          	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
            	<tr>
                <td nowrap></td>
                <td nowrap><span class="bleugrascalibri10"> D&eacute;but s&eacute;jour<sup><span class="champoblig">*</span></sup> :</span>&nbsp;</td>
                <td nowrap>
									<?php $readonly='';
									$bgstyle='';
									if($codelibcat=='DOCTORANT' && $row_rs_individu['date_preminscr']!='')
									{ $readonly='readonly';
									  $bgstyle='style="background-color:#FFFFCC"';
									}?>
                	<input name="datedeb_sejour_jj" type="text" <?php echo $bgstyle ?> class="noircalibri10" id="datedeb_sejour_jj" value="<?php echo substr($row_rs_individu['datedeb_sejour'],8,2); ?>" <?php echo $readonly ?> size="2" maxlength="2">
                  <input name="datedeb_sejour_mm" type="text" <?php echo $bgstyle ?> class="noircalibri10" id="datedeb_sejour_mm" value="<?php echo substr($row_rs_individu['datedeb_sejour'],5,2); ?>" <?php echo $readonly ?> size="2" maxlength="2">
                  <input name="datedeb_sejour_aaaa" type="text" <?php echo $bgstyle ?> class="noircalibri10" id="datedeb_sejour_aaaa" value="<?php echo substr($row_rs_individu['datedeb_sejour'],0,4); ?>" <?php echo $readonly ?> size="4" maxlength="4"></td>
                <td nowrap><span class="bleugrascalibri10"> &nbsp;&nbsp;Fin<?php if($permanent=='non') { ?><sup><span class="champoblig">*</span></sup><?php }?> :&nbsp;</span></td>
                <td nowrap><input name="datefin_sejour_jj" type="text" class="noircalibri10" id="datefin_sejour_jj" value="<?php echo substr($row_rs_individu['datefin_sejour'],8,2); ?>" size="2" maxlength="2">
                  <input name="datefin_sejour_mm" type="text" class="noircalibri10" id="datefin_sejour_mm" value="<?php echo substr($row_rs_individu['datefin_sejour'],5,2); ?>" size="2" maxlength="2">
                  <input name="datefin_sejour_aaaa" type="text" class="noircalibri10" id="datefin_sejour_aaaa" value="<?php echo substr($row_rs_individu['datefin_sejour'],0,4); ?>" size="4" maxlength="4">
                </td>
                <td>                  
                	<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_date_sejour">
                  <div class="tooltipContent_cadre" id="info_date_sejour">
                    <span class="noircalibri10">
                    Les dates du s&eacute;jour  correspondent, en g&eacute;n&eacute;ral, aux dates de contrat ou d'emploi de l'individu,<br>
                    permettant d'attester que l'individu est sous contrat ou en stage au laboratoire.<br>
                    </span>
                  </div>
                  <script type="text/javascript">
                  var sprytooltip_info_date_sejour = new Spry.Widget.Tooltip("info_date_sejour", "#sprytrigger_info_date_sejour", {offsetX:20, offsetY:20});
                  </script>
								</td>
                <?php
                if($codelibcat=='DOCTORANT')
                { ?>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="2">
                    <tr>
                      <td nowrap class="bleugrascalibri10">Date d'inscription :&nbsp;</td>
                      <td nowrap>
                      <?php $readonly='readonly';
											if(array_key_exists('srh',$tab_roleuser) || $est_admin)
											{ $readonly=''; 
											}?>
                        <input name="date_preminscr_jj" type="text" <?php echo $bgstyle ?>  class="noircalibri10" id="date_preminscr_jj" value="<?php echo substr($row_rs_individu['date_preminscr'],8,2); ?>" <?php echo $readonly ?> size="2" maxlength="2">
                        <input name="date_preminscr_mm" type="text" <?php echo $bgstyle ?> class="noircalibri10" id="date_preminscr_mm" value="<?php echo substr($row_rs_individu['date_preminscr'],5,2); ?>" <?php echo $readonly ?> size="2" maxlength="2">
                        <input name="date_preminscr_aaaa" type="text" <?php echo $bgstyle ?> class="noircalibri10" id="date_preminscr_aaaa" value="<?php echo substr($row_rs_individu['date_preminscr'],0,4); ?>" <?php echo $readonly ?> size="4" maxlength="4">
                      </td>
                      <td nowrap>
                      </td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Ann&eacute;e :&nbsp;</span>
                      <span class="mauvegrascalibri11">
                      <?php 
                      $num_annee=calcule_annee_these($row_rs_individu['date_preminscr'],$row_rs_individu['date_soutenance'],$row_rs_individu['num_inscr_ajuste']);
                      if($num_annee>0)
                      { echo $num_annee.'A';
                      }
                      ?>
                      </span>
                      </td>
                      <td nowrap>&nbsp;<span class="bleugrascalibri10">Ajuste ann&eacute;e :&nbsp;</span>
                      </td>
                      <td nowrap><input name="num_inscr_ajuste" type="text" <?php echo $bgstyle ?> class="noircalibri10" id="num_inscr_ajuste"  value="<?php echo $row_rs_individu['num_inscr_ajuste'] ?>"  <?php echo $readonly ?> size="2" maxlength="2">
                        <img src="images/b_info.png" name="sprytrigger_info_num_inscr" width="16" height="16" id="sprytrigger_info_num_inscr">
                        <div class="tooltipContent_cadre" id="info_num_inscr">
                        <span class="noircalibri10">
                          L'ann&eacute;e de th&egrave;se est calcul&eacute;e automatiquement et s'appuie sur la date d'inscription et la date du jour (date courante).<br>
                          Le d&eacute;but de th&egrave;se est recal&eacute; sur le mois d'octobre pr&eacute;c&eacute;dent le mois de la date d'inscription.<br>
                          (Si le mois d'inscription est compris entre janvier et septembre, l'ann&eacute;e de d&eacute;but de th&egrave;se = l'ann&eacute;e d'inscription-1)<br>
                          <br>
                          L'ann&eacute;e de th&egrave;se = ann&eacute;e courante-ann&eacute;e de d&eacute;but de th&egrave;se (si le mois courant est compris<br>
                          entre octobre et d&eacute;cembre, l'ann&eacute;e de th&egrave;se est augment&eacute;e de 1)<br>
                          Enfin, si l'ann&eacute;e de th&egrave;se ne correspond pas &agrave; la r&eacute;alit&eacute;, "Ajuste ann&eacute;e" permet de la rectifier :<br>
                          ann&eacute;e de th&egrave;se = ann&eacute;e de th&egrave;se + "Ajuste ann&eacute;e" (un nombre n&eacute;gatif diminue l'ann&eacute;e).<br>
                          Enregistrez le formulaire pour recalculer l'ann&eacute;e ann&eacute;e de th&egrave;se si vous modifiez la date d'inscription ou "Ajuste ann&eacute;e".<br>
                          </span>
                          <img src="images/calcul_annee_these.jpg" border="0">
                          </div>
                        <script type="text/javascript">
                        var sprytooltip_num_inscr = new Spry.Widget.Tooltip("info_num_inscr", "#sprytrigger_info_num_inscr", {offsetX:10, closeOnTooltipLeave:false});
                        </script>
                      </td>
                    </tr>
                  </table>
                </td>
      	       <?php
                } ?>
                <td><?php echo ligne_txt_upload_pj_individu($codeindividu,'individu','00','photo','Photo',$form_fiche_dossier_pers,true);?>
          			</td>
								<?php 
								if(isset($tab_typepjindividu['carnet_vaccin']) && $tab_typepjindividu['carnet_vaccin']['estutilise'])
                {?><td><?php echo ligne_txt_upload_pj_individu($codeindividu,'individu','00','carnet_vaccin','Carnet Vaccination',$form_fiche_dossier_pers,true);?>
                  </td>
                <?php 
								}?>
                <td><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fiche_arrivant','Fiche nouvel arrivant',$form_fiche_dossier_pers,true);?>
          			</td>
              </tr>
						</table>
          </td>
        </tr>
        <tr>
      	  <td>
      	      <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2" cellspacing="2">
      	        <tr>
      	          <td nowrap><sup><span class="champoblig">*</span></sup>
                  <select name="codeciv" class="bleucalibri10" id="codeciv">
      	            <?php
										while ($row_rs_civilite = mysql_fetch_assoc($rs_civilite)) 
										{ ?>
      	            <option value="<?php echo $row_rs_civilite['codeciv']?>" <?php if ($row_rs_civilite['codeciv']==$row_rs_individu['codeciv']) {echo "SELECTED";} ?> ><?php echo $row_rs_civilite['libcourt_fr']?></option>
      	            <?php
										} 
										?>
    	            </select></td>
      	          <td nowrap><span class="bleugrascalibri10">Nom</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span></td>
      	          <td nowrap><input name="nom" type="text" class="noircalibri10" id="nom" value="<?php echo htmlspecialchars($row_rs_individu['nom']); ?>" size="30" maxlength="30"></td>
      	          <td nowrap><span class="bleugrascalibri10">Pr&eacute;nom</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span>
                  </td>
      	          <td nowrap><input name="prenom" type="text" class="noircalibri10" id="prenom" value="<?php echo htmlspecialchars($row_rs_individu['prenom']); ?>" size="20" maxlength="20"
                  <?php if($action=='creer'){?>onChange="if(document.getElementById('nom').value+document.getElementById('prenom').value in tab_ind_bd)
                  																			{ alert('Pour information, ce nom existe deja dans la base : '+tab_ind_bd[document.getElementById('nom').value+document.getElementById('prenom').value])
                                                        }"
										<?php }?>>
									
									</td>
      	          <?php 
									if($codelibtypestage!='COLLEGE' && $codelibtypestage!='LYCEE')
                  {?> 
                  	<td nowrap><span class="bleugrascalibri10">Nom de jeune fille<sup><span class="champoblig">*</span></sup> :&nbsp;</span></td>
      	          	<td nowrap><input name="nomjf" type="text" class="noircalibri10" id="nomjf" value="<?php echo htmlspecialchars($row_rs_individu['nomjf']); ?>" size="30" maxlength="30"></td>
    	          	<?php 
									}
									else
									{ ?><td colspan="2"></td>
    	          	<?php 
									}?>
                </tr>
      	        <tr>
      	          <td nowrap>&nbsp;</td>
      	          <td nowrap><span class="bleugrascalibri10">N&eacute;(e) le</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span> 
                  </td>
      	          <td nowrap>
                  	<input name="date_naiss_jj" type="text"  class="noircalibri10" id="date_naiss_jj" value="<?php echo substr($row_rs_individu['date_naiss'],8,2); ?>" size="2" maxlength="2">
      	            <input name="date_naiss_mm" type="text"  class="noircalibri10" id="date_naiss_mm" value="<?php echo substr($row_rs_individu['date_naiss'],5,2); ?>" size="2" maxlength="2">
      	            <input name="date_naiss_aaaa" type="text"  class="noircalibri10" id="date_naiss_aaaa" value="<?php echo substr($row_rs_individu['date_naiss'],0,4); ?>" size="4" maxlength="4">
                  </td>
      	          <td nowrap><span class="bleugrascalibri10">Code postal</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                  </td>
      	          <td nowrap>
      	            <input name="codepostal_naiss" type="text" class="noircalibri10" id="codepostal_naiss" value="<?php echo htmlspecialchars($row_rs_individu['codepostal_naiss']); ?>" size="6" maxlength="20">
    	            </td>
      	          <td nowrap><span class="bleugrascalibri10">Ville</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                  </td>
      	          <td nowrap>
      	            <input name="ville_naiss" type="text" class="noircalibri10" id="ville_naiss" value="<?php echo htmlspecialchars($row_rs_individu['ville_naiss']); ?>" size="30" maxlength="50">
    	            </td>
    	          </tr>
      	        <tr>
      	          <td nowrap colspan="3"></td>
                  <td nowrap><span class="bleugrascalibri10">Pays</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span>
									</td>
      	          <td nowrap>
                    <select name="codepays_naiss" class="noircalibri10" id="codepays_naiss" 
                    onChange="if(this.form.elements['codenat'] && this.form.elements['codenat'].value=='')
                    					{ this.form.elements['codenat'].selectedIndex=this.form.elements['codepays_naiss'].selectedIndex;
                              }
                              if(this.form.elements['codepays_etab_orig'] && this.form.elements['codepays_etab_orig'].value=='')
                    					{ this.form.elements['codepays_etab_orig'].selectedIndex=this.form.elements['codepays_naiss'].selectedIndex;
                              }
                              if(this.form.elements['codetypepieceidentite'] && this.form.elements['codenat'] && this.form.elements['codenat'].value!='079')//hors france
                    					{ this.form.elements['codetypepieceidentite'].value='02';
                              }
                              ">
                    <?php
                    while ($row_rs_pays = mysql_fetch_assoc($rs_pays)) 
                    { ?>
                      <option value="<?php echo $row_rs_pays['codepays']?>" <?php echo $row_rs_pays['codepays']==$row_rs_individu['codepays_naiss']?'selected':''?>><?php echo substr($row_rs_pays['libpays'],0,20)?></option>
                   		<?php
                    }
                    ?>
                    </select>
										<img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_liste_nat">
                    <div class="tooltipContent_cadre" id="info_liste_nat">
                      <span class="noircalibri10">
                        Les listes des pays et nationalit&eacute;s sont pr&eacute;sent&eacute;es en deux parties :<br>
                        - une premi&egrave;re partie tri&eacute;e des plus fr&eacute;quent(e)s au laboratoire<br>
                        - suivie d&rsquo;une seconde partie tri&eacute;e de toutes les autres.
                      </span>
                    </div>
                    <script type="text/javascript">
                    var sprytooltip_info_liste_nat = new Spry.Widget.Tooltip("info_liste_nat", "#sprytrigger_info_liste_nat", {offsetX:10, showDelay:10});
                    </script>                  
                  </td>
      	          <td nowrap><span class="bleugrascalibri10">Nationalit&eacute;</span><sup><span class="champoblig">*</span></sup><span class="bleucalibri10"> :&nbsp;</span>
									</td>
      	          <td nowrap>                        	
                  	<select name="codenat" class="noircalibri10" id="codenat"
                    onChange="if(this.form.elements['codetypepieceidentite'] && this.form.elements['codenat'].value!='079')//hors france
                    					{ this.form.elements['codetypepieceidentite'].value='02';
                              }
                              ">
										<?php
										mysql_data_seek ($rs_pays,0);
                    while ($row_rs_pays = mysql_fetch_assoc($rs_pays)) 
                    {	?>
											<option value="<?php echo $row_rs_pays['codepays']?>" <?php echo $row_rs_pays['codepays']==$row_rs_individu['codenat']?'selected':''?>><?php echo substr($row_rs_pays['libnat'],0,20)?></option>
										<?php
                    }
                    ?>
                    </select>
									</td>
    	          </tr>
    	        </table>
            </td>
    	  </tr>
        <?php if($codelibcat!='PRESTATAIRE')
      	{ ?>
        <tr>
          <td>
            <table  border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2" cellspacing="2">
              <tr>
              	<td>
                	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                  	<tr>
                      <td valign="top" nowrap>
                        <span class="bleugrascalibri10">T&eacute;l. labo. :</span></td>
                      <td>
                        <input name="tel" type="text" class="noircalibri10" id="tel" value="<?php echo $row_rs_individu['tel']; ?>" size="20" maxlength="20">
                       <img src="images/b_effacer_zone.png" align="absbottom" onClick="document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['tel'].value=''" title="Effacer le n&deg;">
                      </td>
                      <td valign="top" nowrap>
                        <span class="bleugrascalibri10">Autre t&eacute;l. labo. :</span></td>
                      <td>
                        <input name="autretel" type="text" class="noircalibri10" id="autretel" value="<?php echo $row_rs_individu['autretel']; ?>" size="20" maxlength="20">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">T&eacute;l. portable :<span class="bleucalibri9">(opt. missions)</span></span>
                      </td>
                      <td><input name="telport" type="text" class="noircalibri10" id="telport" value="<?php echo $row_rs_individu['telport']; ?>" size="20" maxlength="20">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">Fax :</span>
                      </td>
                      <td><input name="fax" type="text" class="noircalibri10" id="fax" value="<?php echo $row_rs_individu['fax']; ?>" size="20" maxlength="20">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
              	<td>
                	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                  	<tr>
                      <td nowrap><span class="bleugrascalibri10">Mail<sup><span class="champoblig">*</span></sup> :</span></td>
                      <td>
                        <input name="email" type="text" class="noircalibri10" id="email" value="<?php echo $row_rs_individu['email']; ?>" size="40" maxlength="100">
                      </td>
                      <td valign="top"><span class="bleugrascalibri10">Mail&nbsp;:</span><span class="bleucalibri9">(apr&egrave;s le d&eacute;part)</span>
                      </td>
                      <td valign="top"><input name="email_parti" type="text" class="noircalibri10" id="email_parti" value="<?php echo $row_rs_individu['email_parti']; ?>" size="40" maxlength="100">
                      </td>
                      <td colspan="2">
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
          	</table>
          </td>
        </tr>
        <tr>
          <td>
            <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
              <tr>
                <td valign="top" nowrap><span class="bleugrascalibri10">Adresse pers.<sup><span class="champoblig">*</span></sup> :</span><br>
                  <span class="bleucalibri9">(</span><span id="adresse_pers#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['adresse_pers']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                <td><textarea name="adresse_pers" cols="30" rows="2" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'adresse_pers#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['adresse_pers']; ?></textarea></td>
                <td valign="top" colspan="2">
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                    <tr>
                    	<td>
											<span class="bleugrascalibri10">Code postal :&nbsp;</span>
                      <input name="codepostal_pers" type="text" class="noircalibri10" id="codepostal_pers" value="<?php echo htmlspecialchars($row_rs_individu['codepostal_pers']); ?>" size="6" maxlength="20">
                      </td>
                      <td>
                      <span class="bleugrascalibri10">Ville<sup><span class="champoblig">*</span></sup> :&nbsp;</span>
                      <input name="ville_pers" type="text" class="noircalibri10" id="ville_pers" value="<?php echo htmlspecialchars($row_rs_individu['ville_pers']); ?>" size="30" maxlength="50">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">Pays<sup><span class="champoblig">*</span></sup> :&nbsp;</span>
                        <select name="codepays_pers" class="noircalibri10" id="codepays_pers">
                          <?php
                          mysql_data_seek ($rs_pays,0);
                          while ($row_rs_pays = mysql_fetch_assoc($rs_pays)) 
                          {	?>
                            <option value="<?php echo $row_rs_pays['codepays']?>" <?php echo $row_rs_pays['codepays']==$row_rs_individu['codepays_pers']?'selected':''?>><?php echo substr($row_rs_pays['libpays'],0,20)?></option>
                          <?php
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td valign="top" nowrap><span class="bleugrascalibri10">R&eacute;sidence admin. :</span><br>
                  <span class="bleucalibri9">(</span><span id="adresse_admin#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['adresse_admin']) ?></span><span class="bleucalibri9">/200 car. max.)</span></td>
                <td><textarea name="adresse_admin" cols="30" rows="3" wrap="PHYSICAL" class="noircalibri10" <?php echo affiche_longueur_js("this","200","'adresse_admin#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['adresse_admin']; ?></textarea></td>
                <td valign="top" colspan="2">
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                    <tr>
                      <td class="bleugrascalibri10">Badge :&nbsp;</td>
                      <td><input name="num_badge_acces" type="text" class="noircalibri10" id="num_badge_acces" value="<?php echo $row_rs_individu['num_badge_acces']; ?>" size="20" maxlength="30"> 
                      </td>
                      <td class="bleugrascalibri10">Rendu :&nbsp;
                      </td>
                      <td><input type="checkbox" name="badge_acces_estrendu" id="badge_acces_estrendu" <?php echo ($row_rs_individu['badge_acces_estrendu']=="oui"?"checked":""); ?>>
                      </td>
                    </tr>
                    <tr>
                      <td class="bleugrascalibri10">N° bureau :&nbsp;</td>
                      <td><input name="num_bureau" type="text" class="noircalibri10" id="num_bureau" value="<?php echo $row_rs_individu['num_bureau']; ?>" size="20" maxlength="30">
                      </td>
                      <td></td>
                      <td></td>
                    </tr>
                    <tr>
                      <td class="bleugrascalibri10">Cl&eacute; :&nbsp;</td>
                      <td><input name="num_cle" type="text" class="noircalibri10" id="num_cle" value="<?php echo $row_rs_individu['num_cle']; ?>" size="20" maxlength="30">
                      </td>
                      <td class="bleugrascalibri10">Rendu :&nbsp;
                      </td>
                      <td><input type="checkbox" name="cle_estrendu" id="cle_estrendu" <?php echo ($row_rs_individu['cle_estrendu']=="oui"?"checked":""); ?>>
                      </td>
                    </tr>
                  </table>
                 </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <?php 
	}?>
    <input type="hidden" name="etat_individu" value="<?php echo $etat_individu ?>">
  <tr>
    <td height="10"></td>
  </tr>
  <tr>
    <td>
      <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%"  cellpadding="3" cellspacing="3" class="table_cadre_arrondi">
        <tr>
          <td>
            <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
              <tr>
                <td nowrap>
                </td>
              </tr>
              <tr>
                <td nowrap>
                  <span class="bleugrascalibri10">Situation professionnelle actuelle</span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10"> :&nbsp;</span>
                  <select name="codesituationprofessionnelle" class="noircalibri10" id="codesituationprofessionnelle">
                  <?php
                  while ($row_rs_situationprofessionnelle = mysql_fetch_assoc($rs_situationprofessionnelle)) 
                  { ?>
                    <option value="<?php echo $row_rs_situationprofessionnelle['codesituationprofessionnelle']?>" <?php echo $row_rs_situationprofessionnelle['codesituationprofessionnelle']==$row_rs_individu['codesituationprofessionnelle']?'selected':''?>><?php echo $row_rs_situationprofessionnelle['libsituationprofessionnelle']?></option>
                    <?php
                  }
                  ?>
                  </select>
                  <span class="bleugrascalibri10">Organisme employeur actuel<sup><span class="champoblig">*</span></sup> :&nbsp;</span>
                  <input name="etab_orig" type="text" class="noircalibri10" id="etab_orig" value="<?php echo htmlspecialchars($row_rs_individu['etab_orig']); ?>" size="60" maxlength="100">
                  <!--<span class="bleugrascalibri10">Pr&eacute;ciser si autre :&nbsp;</span>
                  <input name="autresituationprofessionnelle" type="text" class="noircalibri10" id="autresituationprofessionnelle" value="<?php //echo htmlspecialchars($row_rs_individu['autresituationprofessionnelle']); ?>" size="30" maxlength="50"> -->
                </td>
              </tr>
              <tr>
                <td nowrap>
                  <span class="bleugrascalibri10">Adresse </span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                  <input name="adresse_etab_orig" type="text" class="noircalibri10" id="adresse_etab_orig" value="<?php echo htmlspecialchars($row_rs_individu['adresse_etab_orig']); ?>" size="50" maxlength="100">
                  <span class="bleugrascalibri10">Code postal</span><?php /*  if ($ue=='non') {  */?><sup><span class="champoblig">*</span></sup><?php /*  } */ ?><span class="bleucalibri10"> :&nbsp;</span>
                  <input name="codepostal_etab_orig" type="text" class="noircalibri10" id="codepostal_etab_orig" value="<?php echo htmlspecialchars($row_rs_individu['codepostal_etab_orig']); ?>" size="6" maxlength="20">
                  <span class="bleugrascalibri10">&nbsp;Ville </span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                  <input name="ville_etab_orig" type="text" class="noircalibri10" id="ville_etab_orig" value="<?php echo htmlspecialchars($row_rs_individu['ville_etab_orig']); ?>" size="30" maxlength="50">
                  <span class="bleugrascalibri10">&nbsp;Pays </span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                  <select name="codepays_etab_orig" class="noircalibri10" id="codepays_etab_orig">
                  <?php
                  while ($row_rs_pays_etab_orig = mysql_fetch_assoc($rs_pays_etab_orig)) 
                  { ?>
                    <option value="<?php echo $row_rs_pays_etab_orig['codepays']?>" <?php echo $row_rs_pays_etab_orig['codepays']==$row_rs_individu['codepays_etab_orig']?'selected':''?>><?php echo strlen($row_rs_pays_etab_orig['libpays'])>20?substr($row_rs_pays_etab_orig['libpays'],0,20).'...':$row_rs_pays_etab_orig['libpays']?></option>
                    <?php
                  }
                  ?>
                  </select>
                </td>
              </tr>
              <?php 
              if($demander_autorisation && $GLOBALS['estzrr'])
              {?>               
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" class="table_cadre_arrondi_rouge_fond_rose">
                    <tr>
                      <td nowrap>
                        <span class="bleugrascalibri10">Statut au sein de la ZRR</span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                        <select name="codetypeacceszrr" class="noircalibri10" id="codetypeacceszrr">
                        <?php
                        while ($row_rs_typeacceszrr = mysql_fetch_assoc($rs_typeacceszrr)) 
                        { ?>
                          <option value="<?php echo $row_rs_typeacceszrr['codetypeacceszrr']?>" <?php echo $row_rs_typeacceszrr['codetypeacceszrr']==$row_rs_individu['codetypeacceszrr']?'selected':''?>><?php echo $row_rs_typeacceszrr['libtypeacceszrr']?></option>
                          <?php
                        }
                        ?>
                        </select>
                        <span class="bleugrascalibri10">Acc&egrave;s physique/virtuel</span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                        <select name="codephysiquevirtuelzrr" class="noircalibri10" id="codephysiquevirtuelzrr">
                        <?php
                        while ($row_rs_zrr_physiquevirtuel = mysql_fetch_assoc($rs_zrr_physiquevirtuel)) 
                        { ?>
                          <option value="<?php echo $row_rs_zrr_physiquevirtuel['codephysiquevirtuelzrr']?>" <?php echo $row_rs_zrr_physiquevirtuel['codephysiquevirtuelzrr']==$row_rs_individu['codephysiquevirtuelzrr']?'selected':''?>><?php echo $row_rs_zrr_physiquevirtuel['libphysiquevirtuelzrr']?></option>
                          <?php
                        }
                        ?>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td nowrap>
                       <?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'piece_identite','Pi&egrave;ce d&rsquo;identit&eacute;',$form_fiche_dossier_pers,true);?>
                        <span class="bleugrascalibri10">Type de pi&egrave;ce d'identit&eacute;</span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                        <select name="codetypepieceidentite" class="noircalibri10" id="codetypepieceidentite"
                        onChange="if(this.form.elements['codenat'] && this.form.elements['codenat'].value!='079' && this.form.elements['codetypepieceidentite'].value!='02')//hors france
                                  { this.form.elements['codetypepieceidentite'].value='02';
                                    alert('Etranger : passeport obligatoire')
                                  }
                                  ">
                        <?php
                        while ($row_rs_typepieceidentite = mysql_fetch_assoc($rs_typepieceidentite)) 
                        { ?>
                          <option value="<?php echo $row_rs_typepieceidentite['codetypepieceidentite']?>" <?php echo $row_rs_typepieceidentite['codetypepieceidentite']==$row_rs_individu['codetypepieceidentite']?'selected':''?>><?php echo $row_rs_typepieceidentite['libtypepieceidentite']?></option>
                          <?php
                        }
                        ?>
                        </select>
                        <span class="bleugrascalibri10">&nbsp;Num&eacute;ro </span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">:&nbsp;</span>
                        <input name="numeropieceidentite" type="text" class="noircalibri10" id="numeropieceidentite" value="<?php echo $row_rs_individu['numeropieceidentite']; ?>" size="30" maxlength="50">
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                          <tr>
                            <td>
                            <?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fsd_financement','Financement',$form_fiche_dossier_pers,true);
                            ?>
                            </td>
                            <td><span class="bleugrascalibri10">Montant<sup><span class="champoblig">*</span></sup> (&euro;) :&nbsp;</span>
                                <input type="text" name="montantfinancement" id="montantfinancement" class="noircalibri10" value="<?php echo $row_rs_individu['montantfinancement'] ?>" size="12" maxlength="12">
                            </td>
                            <?php if($codelibcat=='STAGIAIRE')
                            { if(count($tab_cmd_gratification)>0)
                              {?> 
                                <?php $montant=0;
                                foreach($tab_cmd_gratification as $codecommande=>$un_tab_cmd_gratification)
                                { $montant+=$un_tab_cmd_gratification['montant'];
                                }
                                ?>
                                <td class="orangegrascalibri11">
                                  <?php	echo $montant; ?>
                                </td>
                                <td><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_gratification">
                                  <div class="tooltipContent_cadre" id="info_gratification">
                                  <table border="1" class="table_cadre_noir">
                                  <tr><td colspan="3" style="background-color:#00FFFF; text-align:center">Objet contient "gratif", Fournisseur contient "<?php echo htmlspecialchars($row_rs_individu['nom']) ?>"</td></tr>
                                  <tr><td>Fournisseur</td><td>Montant</td><td>N&deg; commande</td></tr>
                                  <?php
                                  foreach($tab_cmd_gratification as $codecommande=>$un_tab_cmd_gratification)
                                  {?> <tr><td><?php echo htmlspecialchars($un_tab_cmd_gratification['libfournisseur']); ?></td><td><?php echo $un_tab_cmd_gratification['montant']; ?></td><td><?php echo $un_tab_cmd_gratification['numcommande']; ?></td></tr>
                                  <?php 
                                  }?>
                                  </table>
                                  </div>
                                  <script type="text/javascript">
                                  var sprytooltip_info_gratification = new Spry.Widget.Tooltip("info_gratification", "#sprytrigger_info_gratification", {offsetX:10,offsetY:0});
                                  </script>
                                </td>
                              <?php 
                              }
                            }?>
                            <td><span class="bleugrascalibri10">Origine</span><sup><span class="champoblig">*</span></sup><span class="bleugrascalibri10">&nbsp;:</span>
                              <select name="codeoriginefinancement" class="noircalibri10" id="codeoriginefinancement">
                              <?php
                              while ($row_rs_zrr_originefinancement = mysql_fetch_assoc($rs_zrr_originefinancement)) 
                              { ?>
                                <option value="<?php echo $row_rs_zrr_originefinancement['codeoriginefinancement']?>" <?php echo $row_rs_zrr_originefinancement['codeoriginefinancement']==$row_rs_individu['codeoriginefinancement']?'selected':''?>><?php echo $row_rs_zrr_originefinancement['liboriginefinancement']?></option>
                                <?php
                              }
                              ?>
                              </select>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                          <tr>
                            <td nowrap>
                              <?php
                              echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'cv','CV',$form_fiche_dossier_pers,true);
                              ?>
                            </td>
                            <td nowrap>
                              <?php
                              echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fsd_sujet','FSD Sujet',$form_fiche_dossier_pers,true);
                              ?>
                            </td>
                          </tr>
                        </table> 	
                      </td>
                    </tr>
                   <?php 
                   if(array_key_exists('srh',$tab_roleuser) || $est_admin)
                   {?>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" class="table_cadre_arrondi_rouge_fond_beige">
                          <tr>
                            <td nowrap>
															<?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fsd','FSD joint',$form_fiche_dossier_pers,true);?>
                            </td>
                            <td><span class="bleugrascalibri11">Avis motiv&eacute;</span>
                                 <span class="bleugrascalibri11">(</span><span id="avis_motive_resp_zrr#nbcar_js" class="bleugrascalibri11"><?php echo strlen($row_rs_individu['avis_motive_resp_zrr']) ?> </span><span class="bleugrascalibri11">/300 car. max.) "FAVORABLE" par d&eacute;faut :</span>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                <tr>
                                  <td>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                      <tr>
                                        <td><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_numdossierzrr">
                                          <div class="tooltipContent_cadre" id="info_numdossierzrr">
                                            <span class="noircalibri10">
                                            Le num&eacute;ro de dossier zrr est g&eacute;n&eacute;r&eacute; automatiquement, &agrave; moins que vous ne le remplissiez manuellement.<br>
                                            Un contr&ocirc;le est effectu&eacute; sur un doublon de num&eacute;ro de dossier zrr<br>
                                            Aucun contr&ocirc;le n'est effectu&eacute; sur un num&eacute;ro de dossier zrr d&eacute;ja saisi en cas de changement de lieu.
                                            </span>
                                          </div>
                                          <script type="text/javascript">
                                          var sprytooltip_numdossierzrr = new Spry.Widget.Tooltip("info_numdossierzrr", "#sprytrigger_info_numdossierzrr", {offsetX:20, offsetY:20});
                                          </script>
                                        </td>
                                        <td><span class="bleugrascalibri10">N&deg; dossier ZRR :&nbsp;</span>
                                        </td>
                                        <td>
                                          <input type="text" name="numdossierzrr" id="numdossierzrr" class="noircalibri10" value="<?php echo $row_rs_individu['numdossierzrr'] ?>" size="20" maxlength="20">
                                        </td>
                                        <!--// PG fsd 20160120 -->
                                        <td align="center" nowrap>
                                         <input name="submit_generer_classeur_fsd" type="image" src="images/b_generer_classeur_fsd.png"
                                         onclick="<?php 
                                                  if($classeur_fsd_existe) 
                                                  {?> 
                                                    if(confirm('Ecraser le classeur FSD existant ?'))
                                                    {<?php 
                                                  }?>
                                                      if(controle_form_fiche_dossier_pers('<?php echo $form_fiche_dossier_pers ?>',this.name)) 
                                                      { div_chargementencours=document.getElementById('chargementencours')
                                                        e=event
                                                        if(e.offsetX || e.offsetY) 
                                                        {	x = e.pageX;
                                                          y = e.pageY;
                                                        }
                                                        else if(e.layerX || e.layerY) 
                                                        { x = e.clientX;
                                                          y = e.clientY;
                                                        }
                                                        div_chargementencours.style.position = 'absolute';
                                                        div_chargementencours.style.left=x +'px'
                                                        div_chargementencours.style.top=y + 'px'
                                                        div_chargementencours.style.display='block'
                                                      }
                                                      else
                                                      { return false
                                                      };
                                                  <?php 
                                                  if($classeur_fsd_existe) 
                                                  {?>
                                                    } 
                                                    else
                                                    {return false
                                                    }
                                                  <?php 
                                                  }?>">
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td nowrap>
                                   <?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'retour_fsd','Retour FSD',$form_fiche_dossier_pers,$est_admin);?>
                                  </td>
                                </tr>
                              </table>
                            </td>
                            <td>
                                <textarea name="avis_motive_resp_zrr" cols="70" rows="3" wrap="physical" class="noircalibri10" <?php echo affiche_longueur_js("this","300","'avis_motive_resp_zrr#nbcar_js'","'bleugrascalibri11'","'rougegrascalibri11'") ?>><?php echo ($row_rs_individu['avis_motive_resp_zrr']=='' && isset($row_rs_individu['avis_motive_encadrant_zrr']))?$row_rs_individu['avis_motive_encadrant_zrr']:$row_rs_individu['avis_motive_resp_zrr'] ?></textarea>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
							<?php // 20170319
                }
              }
							else
							{ ?>
              	<tr>
                  <td>
                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                      <tr>
                        <td nowrap>
                          <?php
                          echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'cv','CV',$form_fiche_dossier_pers,true);
                          ?>
                        </td>
                        <td nowrap>
                          <?php
                       		echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'piece_identite','Pi&egrave;ce d&rsquo;identit&eacute;',$form_fiche_dossier_pers,true);
                          ?>
                        </td>
                      </tr>
                    </table> 	
                  </td>
                </tr>

							<?php // 20170319
              }
              if($codelibcat=='DOCTORANT')
              { ?>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="1">
                    <tr>
                      <td nowrap>&nbsp;</td>
                      <td colspan="2" align="center"><span class="bleugrascalibri10">Intitul&eacute;</span></td>
                      <td align="center"><span class="bleugrascalibri10">Etablissement de d&eacute;livrance - Pays</span></td>
                      <td align="center"><span class="bleugrascalibri10">Ann&eacute;e</span></td>
                    </tr>
                    <tr>
                      <td nowrap class="bleugrascalibri10">Master obtenu :</td>
                      <td nowrap><select name="codemaster_obtenu" class="noircalibri10" id="codemaster_obtenu">
                        <?php 
                        mysql_data_seek($rs_diplome, 0);
                        while ($row_rs_diplome = mysql_fetch_assoc($rs_diplome))
                        { ?>
                        <option value="<?php echo $row_rs_diplome['codediplome']?>" <?php if ($row_rs_diplome['codediplome']==$row_rs_individu['codemaster_obtenu']) {echo "SELECTED";} ?>><?php echo $row_rs_diplome['libdiplome_fr']?></option>
                        <?php
                        } 
                        ?>
                        </select>
                        <span class="bleucalibri9">ou&nbsp;</span></td>
                      <td nowrap><input name="autremaster_obtenu_lib" type="text" class="noircalibri10" id="autremaster_obtenu_lib" value="<?php echo $row_rs_individu['autremaster_obtenu_lib']; ?>" size="40" maxlength="100"></td>
                      <td><input name="master_obtenu_etab_pays" type="text" class="noircalibri10" id="master_obtenu_etab_pays" value="<?php echo $row_rs_individu['master_obtenu_etab_pays']; ?>" size="40" maxlength="100"></td>
                      <td align="center"><input name="master_obtenu_annee" type="text" class="noircalibri10" id="master_obtenu_annee" value="<?php echo $row_rs_individu['master_obtenu_annee']; ?>" size="4" maxlength="4"></td>
                    </tr>
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Dernier dipl&ocirc;me obtenu&nbsp;</span><span class="bleucalibri9">(hors Master)</span><span class="bleugrascalibri10"> :&nbsp;</span></td>
                      <td nowrap></td>
                      <td align="center" valign="top"><input name="diplome_dernier_lib" type="text" class="noircalibri10" id="diplome_dernier_lib" value="<?php echo htmlspecialchars($row_rs_individu['diplome_dernier_lib']); ?>" size="40" maxlength="100"></td>
                      <td valign="top"><input name="diplome_dernier_etab_pays" type="text" class="noircalibri10" id="diplome_dernier_etab_pays" value="<?php echo htmlspecialchars($row_rs_individu['diplome_dernier_etab_pays']); ?>" size="40" maxlength="100"></td>
                      <td align="center" valign="top"><input name="diplome_dernier_annee" type="text" class="noircalibri10" id="diplome_dernier_annee" value="<?php echo $row_rs_individu['diplome_dernier_annee']; ?>" size="4" maxlength="4"></td>
                    </tr>
                    <tr>
                      <td colspan="5">&nbsp;</td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php 
              }?>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="85%"  cellpadding="0" cellspacing="0" bordercolor="#0000FF">
                    <tr valign="top">
                      <td width="26%" nowrap>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" align="center" cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
                          <tr>
                            <td align="center" nowrap><span class="bleugrascalibri10"><?php echo $GLOBALS['libcourt_theme_fr'] ?></span><sup><span class="champoblig">*</span></sup>
                            </td>
                          </tr>
                          <tr>
                            <td align="center" id="sprytrigger_info_theme_sujet">
                              <div class="tooltipContent_cadre" id="info_theme_sujet">
                              <span class="noircalibri10">
                              Si une liste de sujets est propos&eacute;e, elle sera renseign&eacute;e avec les sujets <?php echo $GLOBALS['libcourt_theme_fr'] ?> s&eacute;lectionn&eacute;(e).<br>
                              Si un sujet est d&eacute;j&agrave; s&eacute;lectionn&eacute; et qu&rsquo;il ne fait pas partie des sujets <?php echo $GLOBALS['libcourt_theme_fr'] ?> choisi, <br> 
                              il faut d&rsquo;abord d&eacute;selectionner le sujet (sujet vide)
                              </span>
                              </div>
                              <script type="text/javascript">
                              var sprytooltip_theme_sujet = new Spry.Widget.Tooltip("info_theme_sujet", "#sprytrigger_info_theme_sujet", {closeOnTooltipLeave:true, followMouse:true});
                              </script>
                              <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="70%" align="center" cellpadding="0" cellspacing="0">
                                <?php // theme(s)
                                ksort($row_rs_individutheme); 
                                reset($row_rs_individutheme); 
                                list($codetheme,$codetheme)=each($row_rs_individutheme);
                                $nonfintheme=($codetheme!=''?true:false);
                                while($row_rs_theme=mysql_fetch_assoc($rs_theme))//pour chaque theme
                                {  //modif 08122013 : pas d'affichage des anciens gt mais ils sont cachés et envoyés pour ne pas les perdre-->
                                  $class="affiche";
                                  if($row_rs_theme['date_fin']<$GLOBALS['date_bascule_gt_vers_dept'] && $row_rs_theme['date_fin']!="")
                                  { $class="cache";
                                  }
                                  //modif fin 08122013
                                  ?>
                                <tr>
                                  <td nowrap>
                                    <div class="<?php echo $class?>"><!-- //modif 08122013 : pas d'affichage des anciens gt mais ils sont cachés et envoyés pour ne pas les perdre-->
                                    <?php
                                    $checked="";
                                    if($nonfintheme)
                                    { if($row_rs_theme['codetheme']==$codetheme)
                                      { $checked=" checked ";
                                        (list($codetheme,$codetheme)=each($row_rs_individutheme));
                                        $nonfintheme=($codetheme!=''?true:false);
                                       }
                                    }?>
                                      <span class="noirgrascalibri10"><?php echo $row_rs_theme['libcourt_fr'] ?>&nbsp;&nbsp;&nbsp;</span>
                                    </div>
                                  </td>
                                  <td align="center"><?php
                                  	if($GLOBALS['avecequipe'])
                                    {// les services communs n'ont pas d'equipe : on peut cocher ce theme, contrairement aux autres?> 
                                    <div class="<?php echo $row_rs_theme['codetheme']=='00'?'affiche':'cache'?>">
                                    <input type="checkbox" name="codetheme#<?php echo $row_rs_theme['codetheme'] ?>" <?php echo $checked ?>
                                     <?php if($sujet_liste_affiche) {?>onclick="updatelistesujet(this)" <?php }?>
                                    >
                                    </div>
                                    <?php 
																		}
                                    else
                                    {?>
                                    <div class="<?php echo $class?>">
                                    <input type="checkbox" name="codetheme#<?php echo $row_rs_theme['codetheme'] ?>" <?php echo $checked ?>
                                     <?php if($sujet_liste_affiche) {?>onclick="updatelistesujet(this)" <?php }?>
                                    >
                                    </div>
                                    <?php }?>
                                  </td>
                                </tr>
                                  <?php 
																	if($GLOBALS['avecequipe'])//liste des equipes par theme
                                  { if(isset($tab_themeequipe[$row_rs_theme['codetheme']]))
																		{?>
																			<?php
																			foreach($tab_themeequipe[$row_rs_theme['codetheme']] as $codeequipe=>$un_tab_equipe)
																			{ $checked="";
																				if(isset($row_rs_individuequipe[$un_tab_equipe['codeequipe']]))
																				{ $checked=" checked ";
																				}?>
																				<tr>
																					<td><span class="noircalibri10">&nbsp;&nbsp;<?php echo $un_tab_equipe['libcourt_fr'] ?>&nbsp;&nbsp;&nbsp;</span></td>
																				 <td>
                                         <input type="checkbox" name="codeequipe#<?php echo $un_tab_equipe['codeequipe'] ?>" <?php echo $checked ?>
                                     			onclick="update_equipetheme(this)"
                                         >
																				 </td>
																				</tr>
																			<?php
																			}
																		} 
																	}
                                } ?>
                              </table>
                              	<?php 
															if($GLOBALS['avecequipe'])
															{ ?> 
																<script language="javascript">
																var frm=document.forms['<?php echo $form_fiche_dossier_pers ?>'];
																var tab_equipetheme=new Array()
																var tab_themeequipe=new Array()
                                <?php 
																foreach($tab_themeequipe as $codetheme=>$un_tab_theme)
																{ ?>tab=new Array()
																		i=0
                                  <?php
                                  foreach($un_tab_theme as $codeequipe=>$un_tab_equipe)
																	{?> tab_equipetheme['<?php echo $codeequipe ?>']="<?php echo $codetheme ?>"
																		  tab[i]='<?php echo $codeequipe ?>'
																			i++
																	<?php 
																	}?>
																	tab_themeequipe['<?php echo $codetheme ?>']=tab
																	<?php 
																}?>
																function update_equipetheme(champequipe)
																{ inputcodeequipe=new String(champequipe.name);
																	codeequipe=inputcodeequipe.substring((new String('codeequipe#')).length) 
																	codetheme=tab_equipetheme[codeequipe]
																	champtheme=frm.elements['codetheme#'+codetheme]
                                  if(champequipe.checked)//l'equipe vient d'etre selectionnee
																	{ if(!champtheme.checked)//le theme n'est pas selectionne
                                    { champtheme.checked=true;
                                      <?php 
																			if($sujet_liste_affiche) 
																			{?> updatelistesujet(champtheme)
																			 <?php 
																			}?>
                                    }
                                  }
                                  else //l'equipe est deselectionnee
                                  { tab=tab_themeequipe[codetheme]
																		//alert('deselection de '+codeequipe)
																		theme_encore_selectionne=false
																		if(champtheme.checked)//le theme est selectionne : faut-il le deselectionner ?
                                    { for(i=0;i<tab.length;i++)//une autre equipe de ce theme est-elle selectionnee
																			{ if(tab[i]!=codeequipe && frm.elements['codeequipe#'+tab[i]].checked)
                                      	{ theme_encore_selectionne=true;break;//alert('codeequipe#'+tab[i]+' '+frm.elements['codeequipe#'+tab[i]].checked)
																				}
																			}
																			if(!theme_encore_selectionne)
																			{ frm.elements['codetheme#'+codetheme].checked=false
																				<?php 
																				if($sujet_liste_affiche) 
																				{?>possible_de_deselectionner=updatelistesujet(champtheme)
																					if(!possible_de_deselectionner)
																					{ champequipe.checked=true;
																					}
																				 <?php 
																				}?>
																			}
                                    }
                                  }
                                  //.checked=!champtheme.checked;
																}
                                </script>
                                <?php 
															} 
                              if(isset($tab_rs_sujettheme))
                              {?>	
                              <script language="javascript">
                                var frm=document.forms['<?php echo $form_fiche_dossier_pers ?>'];
                                var tabsujettheme=new Array();
                                var tab_theme_nbsujet=new Array();
                                var tabsujetcodestatutsujet=new Array();//utilisé dans le onChange du champ select codesujet
																var tabsujet_associe_a_autre_individu=new Array();
                                <?php
																foreach($tabsujet_associe_a_autre_individu as $codesujet=>$row_rs)
																{ ?>
																	o=new Object();o['codeindividu']='<?php echo $row_rs['codeindividu'] ?>';o['numsejour']='<?php echo $row_rs['numsejour'] ?>'
																 	tabsujet_associe_a_autre_individu['<?php echo $codesujet ?>']=o;
                                <?php
																}
																
                                $tab_theme_nbsujet=array();
                                foreach($tab_rs_sujettheme as $codesujet=>$tab_codetheme)
                                { foreach($tab_codetheme as $codetheme=>$row_rs_sujet)
                                  {?> tabsujetcodestatutsujet['<?php echo $codesujet ?>']='<?php echo $row_rs_sujet['codestatutsujet'] ?>';
                                    <?php
                                    if($codesujet!="")
                                    { if(isset($tab_theme_nbsujet[$codetheme]))//pour liste de sujets selon le(s) theme(s) choisi
                                      { $tab_theme_nbsujet[$codetheme]++;
                                      }
                                      else
                                      { $tab_theme_nbsujet[$codetheme]=1;?>
                                        tabsujettheme['<?php echo $codetheme ?>']=new Array();
                                      <?php
                                      }
                                      $titre_sujet=str_replace('"','\"',str_replace(chr(13),"",str_replace(chr(10),"",$row_rs_sujet['titre_sujet'])));
                                      // pour classement ordre alpha pour la premiere lettre
                                      if(array_key_exists(htmlentities(substr($titre_sujet,0,1)),$GLOBALS['tab_car_accent_maj_transforme']))
                                      { $titre_sujet=$GLOBALS['tab_car_accent_maj_transforme'][htmlentities(substr($titre_sujet,0,1))].substr($titre_sujet,1);
                                      } ?>
                                      tabsujettheme['<?php echo $codetheme ?>'][<?php echo $tab_theme_nbsujet[$codetheme]-1 ?>]=new Array("<?php echo strlen($titre_sujet)>150?substr($titre_sujet,0,147).'...':$titre_sujet ?>","<?php echo $codesujet ?>");
                                      <?php
                                    }
                                  }
                                }
                                foreach($tab_theme_nbsujet as $codetheme=>$nbsujet)
                                { ?>
                                  tab_theme_nbsujet['<?php echo $codetheme ?>']=<?php echo $tab_theme_nbsujet[$codetheme] ?>;
                                <?php
                                }
                                ?>
                                function updatelistesujet(champtheme)
                                { if(frm.elements['codesujet'])
                                  { var sujetlist=frm.elements['codesujet'];
                                    var codesujetselectionne=sujetlist.value;
                                    var tab_sujetcodesujet=new Array();//table des (sujet,codesujet) des themes a constituer pour liste select
                                    //sujetlist.options.length=0;
                                    tab_sujetcodesujet[0]=new Array("", "");//sujet vide
                                    index=0;//indice du dernier sujet de la liste select des sujets a afficher tab_sujetcodesujet
                                    //sujetlist.options[sujetlist.options.length]=new Option("","");
                                    for(i=0;i<frm.elements.length;i++)//parcours de tous les elements du formulaire pour trouver les codetheme
                                    { if(frm.elements[i].name.substring(0,(new String('codetheme#')).length) =='codetheme#')
                                      { //pour chaque theme selectionne
                                        if(frm.elements[i].checked)//si theme selectionne
                                        { inputcodetheme=new String(frm.elements[i].name);
                                          selectedcodetheme=inputcodetheme.substring(inputcodetheme.indexOf('#')+1,inputcodetheme.length);
                                          // pour chaque sujet du theme 
                                          for (j=0; j<tab_theme_nbsujet[selectedcodetheme]; j++)
                                          { //verif qu'il n'est pas deja dans la liste : un sujet sur plus d'un theme
                                            titre_sujet=tabsujettheme[selectedcodetheme][j][0]
                                            codesujet=tabsujettheme[selectedcodetheme][j][1]
                                            fin=false;doublon=false;
                                            k=0;//indice de parcours de tab_codesujet pour recherche doublon jusqu'a index qui est le dernier element de tab_codesujet
                                            while(!fin && !doublon)
                                            { if(tab_sujetcodesujet[k][1]==codesujet)
                                              { doublon=true;
                                              }
                                              k++;
                                              if(k>index)
                                              { index++;
                                                tab_sujetcodesujet[index]=new Array(titre_sujet, codesujet);
                                                //sujetlist.options[sujetlist.options.length]=new Option(titre_sujet, codesujet)
                                                fin=true;
                                              }
                                            }
                                          }
                                        }
                                      }
                                    }
                                    // on peut changer le theme, a condition que le sujet soit dans la liste du theme qui vient d'etre selectionne
                                    if((tab_sujetcodesujet.join(", ")).indexOf(codesujetselectionne,0)!=-1)
                                    { <?php 
                                      // si le sujet est verrouille et que c'est un doctorant, la liste n'est pas modifiee
                                      if(!($sujet_verrouille=='oui' && $codelibcat=='DOCTORANT'))
                                      {?>
                                        sujetlist.options.length=0;
                                        sujetlist.options[sujetlist.options.length]=new Option(<?php echo $sujet_dans_liste_obligatoire?'"[--- Si le sujet n\'est pas dans la liste, laisser inchangé (lire le contenu de l\'info-bulle) ---]"':'""' ?>,"");
                                        indexcodesujetselectionne=0;
                                        tab_sujetcodesujet.sort();
                                        tab_sujetcodesujet=unique(tab_sujetcodesujet);//fonction unique definie ci-dessous
                                        for(k=1;k<tab_sujetcodesujet.length;k++)//index=nombre de sujets de tab_sujetcodesujet
                                        { sujetlist.options[sujetlist.options.length]=new Option(tab_sujetcodesujet[k][0], tab_sujetcodesujet[k][1])
                                          if(tab_sujetcodesujet[k][1]==codesujetselectionne)
                                          { indexcodesujetselectionne=k;//l'index (entier) du sujet selectionne dans la liste select
                                          }
                                        }
                                        sujetlist.selectedIndex=indexcodesujetselectionne;
                                      <?php 
                                      }?>
																			return true;
                                    }
                                    else
                                    { alert("Vous devez deselectionner le sujet avant de changer : il ne figure pas dans la liste <?php echo $GLOBALS['libcourt_theme_fr'] ?> selectionne");
                                      champtheme.checked=!champtheme.checked;
																			return false;
                                    }
                                  }
																	else
																	{ return true;
																	}
                                }
                                function unique(arrayName)
                                { var newArray=new Array();
                                  label:
                                  for(var i=0; i<arrayName.length;i++ )
                                  { for(var j=0; j<newArray.length;j++ )
                                    { if(newArray[j][1]==arrayName[i][1]) 
                                      { continue label;
                                      }
                                    }
                                    newArray[newArray.length] = arrayName[i];
                                  }
                                  return newArray;
                                }
                              </script>
                              <?php 
                              }?>
                            </td>
                          </tr>
                        </table>
                      </td>
                      <td width="4%" nowrap>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                      <td width="70%" nowrap>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="88%"  cellpadding="0" cellspacing="5" class="table_cadre_arrondi">
                          <tr>
                            <td nowrap class="bleugrascalibri10">Lieu de travail pour le laboratoire<sup><span class="champoblig">*</span></sup> :
                            </td>
                            <td nowrap class="bleugrascalibri10">
                            <select name="codelieu" class="noircalibri10">
                              <?php
                              while ($row_rs_lieu = mysql_fetch_assoc($rs_lieu))
                              { ?>
                              <option value="<?php echo $row_rs_lieu['codelieu']?>" <?php echo $row_rs_lieu['codelieu']==$row_rs_individu['codelieu']?'selected':'' ?>><?php echo $row_rs_lieu['liblieu']?></option>
                              <?php
                              } 
                              ?>
                            </select>
                            </td>
                          </tr>
                          <tr>
                            <td align="right" nowrap><span class="bleucalibri9">&nbsp;&nbsp;- Autre, pr&eacute;ciser :</span>&nbsp;</td>
                            <td nowrap><input name="autrelieu" type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_individu['autrelieu']); ?>" size="50" maxlength="100"></td>
                          </tr>
                          <?php 
                          if($codelibcat=='EC' || $codelibcat=='DOCTORANT' || $codelibcat=='POSTDOC' )
                          {?>
                          <tr>
                            <td nowrap class="bleugrascalibri10">Composante d'enseignement :&nbsp;</td>
                            <td nowrap class="bleugrascalibri10"><input name="composante_enseigne" type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_individu['composante_enseigne']); ?>" size="50" maxlength="100">
                            </td>
                          </tr>
                          <?php 
                          }?>
                        </table>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php 
              if(($codelibcat=='EC' || $codelibcat=='CHERCHEUR' || $codelibcat=='ITARF' || $codelibcat=='POSTDOC' || !$demander_autorisation) && !$sujet_dans_liste_obligatoire)
              {?>
              <tr>
                <?php 
                if($codelibtypestage=='COLLEGE' || $codelibtypestage=='LYCEE')// étudiant collégien
                {?>	
                  <td valign="top" nowrap><span class="bleugrascalibri10">Objet du stage :&nbsp;d&eacute;couverte du laboratoire</span></td>
                <?php 
                }
                else
                { ?>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                    <tr>
                      <td class="bleugrascalibri10">
                      <?php 
                      if($demander_autorisation)
                      {?>Intitul&eacute; de la mission
                      <?php 
                      }
                      else
                      { if($codelibcat=='STAGIAIRE')
												{?>Intitul&eacute; du stage
												<?php
                        }
												else
												{ ?>Objet du s&eacute;jour
                      <?php 
                        }
                      }?><sup><span class="champoblig">*</span></sup> :&nbsp;
                      </td>
                      <td nowrap>
                      <input type="text" name="intituleposte" id="intituleposte" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_individu['intituleposte']); ?>" size="130" maxlength="200"> 
                      </td>
                    </tr>
                    <tr>
                      <td valign="top" nowrap><span class="bleugrascalibri10">Description de la mission<sup><span class="champoblig">*</span></sup> :</span><br><span class="bleucalibri9">(</span><span id="descriptionmission#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['descriptionmission']) ?></span><span class="bleucalibri9">/6000 car. max.)</span><span class="bleugrascalibri10">&nbsp;:</span>
                      </td>
                      <td><textarea name="descriptionmission" cols="130" rows="10" wrap="PHYSICAL" class="noircalibri10" id="descriptionmission" <?php echo affiche_longueur_js("this","6000","'descriptionmission#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['descriptionmission']; ?></textarea>
                      </td>
                    </tr>
                    <?php 
										if($GLOBALS['estzrr'])//20170320
                    {?> <tr>
                    	<td></td>
                      <td valign="top"><span class="bleugrascalibri10">Domaine<sup><span class="champoblig">*</span></sup></span>
                        <select name="codedomainescientifique1" id="codedomainescientifique1" onChange="return affichedisciplinescientifique(this)">
                        <?php
                        foreach($tab_domainescientifique as $codedomainescientifique=>$un_domainescientifique)
                        { ?>	
                          <option value="<?php echo $codedomainescientifique ?>"  <?php if($codedomainescientifique==$row_rs_individu['codedomainescientifique1']){?> selected <?php }?>><?php echo ($codedomainescientifique=='')?"[choix obligatoire]":$un_domainescientifique['libdomainescientifique'];?></option>
                        <?php 
                        }?>
                        </select>
                      
                      	<span class="bleugrascalibri10">Discipline<sup><span class="champoblig">*</span></sup></span>
                        <select name="codedisciplinescientifique1" id="codedisciplinescientifique1" >
                        <?php
                        foreach($tab_disciplinescientifique as $codedisciplinescientifique=>$un_disciplinescientifique)
                        { if($un_disciplinescientifique['codedomainescientifique']==$row_rs_individu['codedomainescientifique1'] || $un_disciplinescientifique['codedisciplinescientifique']=='')
                          { ?> <option value="<?php echo $codedisciplinescientifique ?>"  <?php if($codedisciplinescientifique==$row_rs_individu['codedisciplinescientifique1']){?> selected <?php }?>><?php echo ($codedisciplinescientifique=='')?"[choix obligatoire]":$un_disciplinescientifique['libdisciplinescientifique']; ?></option>
                          <?php 
                          }
                        }?>
                        </select>
                      </td>
                    </tr>
                    <?php 
										}?>
                  </table>
                </td>
                <?php 
                }?>
              </tr>
              <?php 
              }?>
              <tr>
                <td>
                  <?php
                  if($permanent=='non')
                  { ?>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="3">
                    <tr>
                      <td></td>
                    </tr>
                    <?php
                    if($etudiant_ou_exterieur=="oui")
                    {?>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="2">
                        <?php
                        if($codelibcat=='STAGIAIRE' && $codelibtypestage!='COLLEGE' && $codelibtypestage!='LYCEE')
                        {?>
                        <tr>
                          <td nowrap>&nbsp;</td>
                          <td>
                            <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                              <tr>
                                <?php if($codelibtypestage=='MASTER')
                                {?> 
                                <td nowrap>
                                  <span class="bleugrascalibri10">Dipl&ocirc;me pr&eacute;par&eacute; :&nbsp;</span>
                                </td>
                                <td>
                                  <select name="codediplome_prep" class="noircalibri10" id="codediplome_prep">
                                  <?php
                                  while ($row_rs_diplome = mysql_fetch_assoc($rs_diplome))
                                  { ?>
                                  <option value="<?php echo $row_rs_diplome['codediplome']?>" <?php if ($row_rs_diplome['codediplome']==$row_rs_individu['codediplome_prep']) {echo "SELECTED";} ?>><?php echo $row_rs_diplome['libdiplome_fr']?></option>
                                  <?php
                                  }
                                  ?>
                                  </select>
                                </td>
                                <?php 
                                }?>
                                <td nowrap>
                                <span class="bleugrascalibri10">
                                <?php 
                                if($codelibtypestage=='MASTER')
                                {?> &nbsp;Autre (si n'est pas dans la liste) :&nbsp;
                                <?php 
                                }
                                else
                                {?> Dipl&ocirc;me pr&eacute;par&eacute; :&nbsp;
                                <?php 
                                }?>
                                </span>
                                </td>
                                <td><input name="autrediplome_prep" type="text" class="noircalibri10" id="autrediplome_prep" value="<?php echo htmlspecialchars($row_rs_individu['autrediplome_prep']); ?>" size="45" maxlength="100"></td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                        <?php 
                        }//fin STAGIAIRE
                        if($codelibcat=='POSTDOC')
                        { ?>
                        <tr>
                          <td></td>
                          <td class="orangegrascalibri11">
                          Le sujet est obligatoire si&nbsp;
                          <?php 
                          if($demander_autorisation)
                          {?>
                          l'intitul&eacute; et la description ne sont pas renseign&eacute;s
                          <?php 
                          }
                          else
                          { ?>l'objet n'est pas renseign&eacute;
                          <?php 
                          }?>
                          </td>
                        </tr>	
                        <?php 
                        }?>
                        <tr>
                        <?php 
                        if($codelibtypestage=='COLLEGE' || $codelibtypestage=='LYCEE')// étudiant collégien
                        { if($demander_autorisation)
                          { ?>	
                          <td valign="top" nowrap><span class="bleugrascalibri10">Objet du stage :&nbsp;</span>
                          </td>
                          <td><span class="bleugrascalibri10">d&eacute;couverte du laboratoire</span>
                          </td>
                        <?php 
                          }
                        }
                        else if($sujet_dans_liste_obligatoire || $codelibcat=='POSTDOC')
                        {?>
                          <td valign="top" nowrap><span class="bleugrascalibri10">Sujet :&nbsp;</span></td>
                          <td nowrap align="left">
                            <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="1">
                              <tr>
                                <td align="left">
                                  <select name="codesujet" id="codesujet" class="noircalibri10" 
																		<?php 
                                      if($codelibcat=='DOCTORANT')
                                      {?>
                                   			onChange="javascript:
																									<?php //de/verrouillage propose  selon conditions
                                                  if((array_key_exists('srh',$tab_roleuser) || $est_admin || array_key_exists('theme',$tab_roleuser)))
                                                  {?>
                                                    if(tabsujetcodestatutsujet[this.value]=='E' || tabsujet_associe_a_autre_individu[this.value]  || this.value=='')// tabsujetcodestatutsujet a été initialisé dans le code relatif aux sujets sélectionnés des themes
                                                    { document.getElementById('zone_verrouillage_sujet').className='cache'
                                                    }
                                                    else
                                                    { document.getElementById('zone_verrouillage_sujet').className='affiche'
                                                    }
                                                   
                                                  <?php 
                                                  }?>
                                                  if(tabsujet_associe_a_autre_individu[this.value])
                                                  { document.getElementById('zone_sujet_associe_a_autre_individu').className='affiche'
                                                  }
                                                  else
                                                  { document.getElementById('zone_sujet_associe_a_autre_individu').className='cache'
                                                  }
                                                  "
																		<?php 
                                    }?>
                                   >
                                    <?php 
                                    if(!($codelibcat=='DOCTORANT' && $sujet_verrouille=='oui'))
                                    { ?>
                                    <option value="" ><?php if($sujet_dans_liste_obligatoire) {?> [--- Si le sujet n&rsquo;est pas dans la liste, laisser inchang&eacute; (lire le contenu de l&rsquo;info-bulle) ---] <?php }else {echo str_repeat('&nbsp;',50); }?></option>
                                    <?php 
                                    }
                                    $sujet_valide_par_theme=false;
                                    $listeselectsujet=array();//liste des codesujet sans doublon
                                    foreach($tab_rs_sujettheme as $codesujet=>$tab_codetheme)
                                    { foreach($tab_codetheme as $codetheme=>$row_rs_sujet)
                                      { if(array_key_exists($row_rs_sujet['codetheme'],$row_rs_individutheme))
                                        { if($codelibcat!='DOCTORANT' || $sujet_verrouille!='oui' || ($codelibcat=='DOCTORANT' && $sujet_verrouille=='oui' && $codesujet==$row_rs_individu['codesujet']))
                                          { if($codesujet==$row_rs_individu['codesujet'] && ($row_rs_sujet['codestatutsujet']=='V' || $row_rs_sujet['codestatutsujet']=='P'))
                                            { $sujet_valide_par_theme=true;
                                            }?>
                                            <?php
                                            if(!array_key_exists($codesujet,$listeselectsujet))
                                            { $listeselectsujet[$codesujet]=$codesujet;
                                              ?>
                                              <option value="<?php echo $codesujet?>" <?php if ($codesujet==$row_rs_individu['codesujet']) {echo "selected";} ?>><?php echo strlen($row_rs_sujet['titre_sujet'])>150?substr($row_rs_sujet['titre_sujet'],0,147).'...':$row_rs_sujet['titre_sujet'] ?></option>
                                            <?php
                                            }
                                          }
                                        }
                                      }
                                    } ?>
                                  </select>
                                </td>
                                <td>
                                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" align="center">
                                    <tr>
                                      <?php 
                                      if($sujet_dans_liste_obligatoire)
                                      {?>
                                      <td><img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_sujet_obligatoire">
                                      <div class="tooltipContent_cadre" id="info_sujet_obligatoire">
                                        <span class="noircalibri10">
                                          Le sujet est obligatoire. S&rsquo;il n&rsquo;est pas dans la liste, vous pouvez malgr&eacute; tout enregistrer ce dossier.<br>
                                          Quand vous apposerez le visa r&eacute;f&eacute;rent, le message envoy&eacute; au r&eacute;f&eacute;rent et au responsable <?php echo $GLOBALS['libcourt_theme_fr'] ?> leur pr&eacute;cisera que le sujet n&rsquo;a pas &eacute;t&eacute; renseign&eacute;.<br>
                                          Il leur appartiendra de saisir le sujet et de l&rsquo;associer au dossier de cet &eacute;tudiant. En attendant, il ne sera pas possible d&rsquo;apposer le visa d&rsquo;arriv&eacute;e.
                                        </span>
                                      </div>
                                      <script type="text/javascript">
                                      var sprytooltip_sujet_obligatoire = new Spry.Widget.Tooltip("info_sujet_obligatoire", "#sprytrigger_info_sujet_obligatoire", {offsetX:10});
                                      </script>
                                      </td>
                                      <?php 
                                      }?>
                                      <td align="center">
                                        <a href="javascript:OuvrirVisible('detailsujet.php?codesujet='+document.getElementById('codesujet').value)"><img src="images/b_oeil.png" name="codesujetindividu" width="16" height="16" class="icon" id="codesujetindividu" alt="D&eacute;tail du sujet"/></a>
                                      </td>
                                      <?php // 20170322
																			if(isset($GLOBALS['edit_sujet_dans_individu']) && $GLOBALS['edit_sujet_dans_individu'] && $action=='modifier' && $est_admin)
																			{ ?><td>&nbsp;&nbsp;<input type="image" name="submit_editer_sujet" src="images/b_outils.png" title="Edition sujet">&nbsp;&nbsp;</td>
																			<?php 
																			}
																			// 20170322 fin
                                      if($codelibcat=='DOCTORANT')
                                      { if(((array_key_exists('srh',$tab_roleuser) || $est_admin || array_key_exists('theme',$tab_roleuser))))
																				{ if($sujet_verrouille=='oui')
																					{ $texte_sujet_verrou='Cette action d&eacute;verrouille le sujet :\\n'.
																																' - il ne sera plus possible de voir les champs de suivi du doctorant\\n'.
																																' - vous pourrez choisir un autre sujet';
																					}
																					else
																					{ $texte_sujet_verrou='Cette action verrouille le sujet : il sera possible de modifier les encadrants.';
																						$sujet_verrouille='non';
																					}?>
                                          <td align="center">
                                           <div id='zone_verrouillage_sujet' class="<?php echo (($sujet_valide_par_theme && !isset($tabsujet_associe_a_autre_individu[$row_rs_individu['codesujet']]))?'affiche':'cache') ?>"> 
                                            <input type="image" name="submit_enregistrer" src="images/espaceur.gif" id="submit_enregistrer">
                                            <input type="image" name="submit_verrouiller_sujet#<?php echo $sujet_verrouille ?>##" src="images/b_sujet_verrouille_<?php echo $sujet_verrouille ?>.png" width="85" height="15"
                                            onClick="return confirme('valider','<?php echo $texte_sujet_verrou ?>')">
                                            <input type="hidden" name="sujet_verrouille" value="<?php echo $sujet_verrouille ?>">
                                            <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_sujet_these">
                                            <div class="tooltipContent_cadre" id="info_sujet_these">
                                              <span class="noircalibri10">
                                              Cette case &agrave; cocher n'est affich&eacute;e que pour un doctorant si le sujet est valid&eacute; et non affect&eacute; &agrave; un autre doctorant<br>
                                              Si cette case est :<br>
                                              - coch&eacute;e : les informations de suivi* du doctorant sont visibles et modifables. Le sujet est fix&eacute; et la liste d&eacute;roulante<br>
                                              &nbsp;&nbsp;n'est plus propos&eacute;e.<br>
                                              - non coch&eacute;e : les informations de suivi du doctorant ne sont pas visibles. Il est possible de choisir un sujet.<br>
                                              Si vous d&eacute;cochez cette case, les informations de suivi ne sont pas perdues tant que vous n'avez pas choisi et verrouill&eacute;<br>
                                              &nbsp;&nbsp;un autre sujet : ces informations de suivi pourront alors &ecirc;tre modifi&eacute;es.<br>
                                              * <b>Les encadrants sont li&eacute;s au sujet</b> et apparaissent dans les informations de suivi lors de l&rsquo;association doctorant-sujet <br>
                                              &nbsp;&nbsp;contrairement aux autres informations de suivi : si vous changez de sujet, toutes les informations de suivi sont conservées pour<br>
                                              &nbsp;&nbsp;le doctorant sauf les encadrants.
                                              </span>
                                            </div>
                                            <script type="text/javascript">
                                            var sprytooltip_sujet_these = new Spry.Widget.Tooltip("info_sujet_these", "#sprytrigger_info_sujet_these", {offsetX:10});
                                            </script>
                                           </div>
                                          </td>
                                      <?php
																			}?>
                                      <td>
                                      <?php 
																			while ($row_rs_ed = mysql_fetch_assoc($rs_ed))
                                      { if($row_rs_ed['lienpghttped']!='')
                                      	{?> <a href="<?php echo $row_rs_ed['lienpghttped'] ?>" target="_blank"><?php echo $row_rs_ed['libed']?></a>
																			<?php
																				}
                                      }
                                      ?>
                                      </td>
                                      <td><!--12/02/2015 ajout pour sujets de these poly attribuables  -->
                                       <div id='zone_sujet_associe_a_autre_individu' class='<?php echo isset($tabsujet_associe_a_autre_individu[$row_rs_individu['codesujet']])?'affiche':'cache' ?>'>
                                       <span class="orangecalibri11">Aussi affect&eacute; &agrave; </span><a href="javascript:OuvrirVisible('detailindividu.php?codeindividu='+tabsujet_associe_a_autre_individu[document.getElementById('codesujet').value].codeindividu+'&numsejour='+tabsujet_associe_a_autre_individu[document.getElementById('codesujet').value].numsejour)"><img src="images/b_oeil.png" width="16" height="16"/></a>
                                            <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_sujet_associe_a_autre_individu">
                                            <div class="tooltipContent_cadre" id="info_sujet_associe_a_autre_individu">
                                              <span class="noircalibri10">
                                              Ce sujet est aussi affect&eacute; &agrave; un autre doctorant : il n'est pas possible de le verrouiller dans cette situation.
                                              </span>
                                            </div>
                                            <script type="text/javascript">
                                            var sprytooltip_sujet_associe_a_autre_individu = new Spry.Widget.Tooltip("info_sujet_associe_a_autre_individu", "#sprytrigger_info_sujet_associe_a_autre_individu", {offsetX:10});
                                            </script>
                                       </div>
                                      </td><!--12/02/2015 fin ajout pour sujets de these poly attribuables -->
                                      <?php
                                      }	
																			?>
                                    </tr>
                                  </table>
                                </td>
                              </tr>
                            </table>
                          </td>
                        <?php 
                        } ?>
                        </tr>
                        <?php 
                        if($codelibcat=='DOCTORANT')
                        { ?>
                        <tr>
                          <td nowrap>&nbsp;</td>
                          <td>
                            <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="2" cellpadding="2">
                              <tr>
                                <td nowrap valign="top"><span class="bleugrascalibri10">Co-tutelle : </span>
                                </td>
                                <td nowrap valign="top"><input name="cotutelle" type="checkbox" id="cotutelle" <?php echo $row_rs_individu['cotutelle']=="oui"?'checked':''; ?>
                                			onclick="if(this.checked)
                                      				 { document.getElementById('cotutelle_detail').style.display='block'
                                               }
                                               else 
                                      				 { if(document.getElementById('cotutelle_etab').value=='' && document.getElementById('codepays_cotutelle').value=='')
                                               	 { document.getElementById('cotutelle_detail').style.display='none'
                                                 }
                                                 else 
                                                 { if(confirm('Vider etablissement et pays ?'))
                                                   {	document.getElementById('cotutelle_etab').value=''
                                                      document.getElementById('codepays_cotutelle').value=''
                                                      document.getElementById('cotutelle_detail').style.display='none'
                                                   }
                                                   else
                                                   { this.checked=true
                                                   }
                                                 }
                                               }
                                      				">
                                </td>
                                <td nowrap>
                                	<div id="cotutelle_detail" style="display:<?php echo $row_rs_individu['cotutelle']=='oui'?'block':'none'; ?>">
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                                      <tr>
                                        <td><span class="bleugrascalibri10">Etablissement :&nbsp;</span>
                                        </td>
                                        <td><input type="text" class="noircalibri10" name="cotutelle_etab" id="cotutelle_etab" value="<?php echo htmlspecialchars($row_rs_individu['cotutelle_etab']); ?>" size="45" maxlength="100">
                                        </td>
                                        <td>&nbsp;<span class="bleugrascalibri10">Pays :&nbsp;</span>
                                        </td>
                                        <td><select name="codepays_cotutelle" class="noircalibri10" id="codepays_cotutelle">
                                          <?php
                                          while ($row_rs_cotutelle_pays = mysql_fetch_assoc($rs_cotutelle_pays))
                                          { ?>
                                          <option value="<?php echo $row_rs_cotutelle_pays['codepays']?>" <?php if ($row_rs_cotutelle_pays['codepays']==$row_rs_individu['codepays_cotutelle']) {echo "SELECTED";} ?>><?php echo $row_rs_cotutelle_pays['libpays']?></option>
                                          <?php
                                          }
                                          ?>
                                          </select>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td nowrap colspan="4"><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_cotutelle','Convention de cotutelle',$form_fiche_dossier_pers,true);?>
                                        </td>
                                      </tr>
                                    </table>
                                 	</div>
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
													<?php 
                          if($sujet_verrouille=='oui')
                          { ?>
                          <tr>
                            <td nowrap><img src="images/<?php echo $_SESSION['b_img_suivi_doctorant_plus_ou_moins'] ?>.png" name="image_suivi_doctorant" id="image_suivi_doctorant"
                                      onClick="javascript:
                                                suivi_doctorant=document.getElementById('suivi_doctorant');
                                                if(suivi_doctorant.className=='affiche')
                                                { suivi_doctorant.className='cache';
                                                  document.getElementById('image_suivi_doctorant').src='images/b_plus.png';
                                                  document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['b_img_suivi_doctorant_plus_ou_moins'].value='b_plus';
                                                }
                                                else 
                                                { suivi_doctorant.className='affiche';
                                                  document.getElementById('image_suivi_doctorant').src='images/b_moins.png';
                                                  document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['b_img_suivi_doctorant_plus_ou_moins'].value='b_moins';
                                                }
                                              "
                                                >
                            </td>
                            <td></td>
                          </tr>
                          <tr>
                            <td align="center" valign="top" nowrap>
                              <input type="hidden" name="b_img_suivi_doctorant_plus_ou_moins" id="b_img_suivi_doctorant_plus_ou_moins" value="<?php echo $_SESSION['b_img_suivi_doctorant_plus_ou_moins'] ?>">
                            </td>
                            <td>
                              <div id="suivi_doctorant" class="<?php echo $_SESSION['b_img_suivi_doctorant_plus_ou_moins']=='b_moins'?'affiche':'cache'?>">
                              <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="3" cellspacing="3">
                                <tr>
                                  <td nowrap>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2">
                                            <tr>
                                              <td>
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="2">
                                                  <tr>
                                                    <td nowrap>
                                                      <?php // 20170315
                                                      if(array_key_exists('theme',$tab_roleuser) || array_key_exists('srh',$tab_roleuser) || $est_admin)
                                                      {	echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'fiche_inscr','Fiche d&rsquo;inscription',$form_fiche_dossier_pers,true); 
                                                      }
                                                      ?>
                                                    </td>
                                                    <td nowrap>
                                                      <?php
                                                      if(array_key_exists('theme',$tab_roleuser) || array_key_exists('srh',$tab_roleuser) || $est_admin)
                                                      {	echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'lettre_motiv','Lettre de motivation au del&agrave; de la 3&egrave;me ann&eacute;e d&rsquo;inscription',$form_fiche_dossier_pers,true); 
                                                      }
                                                      ?>
                                                    </td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td nowrap>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2">
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">Labo. d'accueil :&nbsp;</td>
                                              <td nowrap class="bleugrascalibri10"><input type="text" class="noircalibri10" name="labo_accueil" id="labo_accueil" value="<?php echo htmlspecialchars($row_rs_individu['labo_accueil']); ?>" size="30" maxlength="50"></td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Etablissement&nbsp;:&nbsp;</td>
                                              <td nowrap class="bleugrascalibri10"><input type="text" class="noircalibri10" name="etab_these" id="etab_these" value="<?php echo htmlspecialchars($row_rs_individu['etab_these']); ?>" size="20" maxlength="50"></td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;ED :&nbsp;</td>
                                              <td nowrap class="bleugrascalibri10">
                                              <select name="codeed_these" class="noircalibri10" id="codeed_these">
                                                <?php
																								mysql_data_seek($rs_ed,0);
                                                while ($row_rs_ed = mysql_fetch_assoc($rs_ed))
                                                { ?>
                                                <option value="<?php echo $row_rs_ed['codeed']?>" <?php if ($row_rs_ed['codeed']==$row_rs_individu['codeed_these']) {echo "SELECTED";} ?>><?php echo $row_rs_ed['libed']?></option>
                                                <?php
                                                }
                                                ?>
                                              </select>
                                              </td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Dur&eacute;e mois&nbsp;:&nbsp;
                                              </td>
                                              <td nowrap class="bleugrascalibri10"><input type="text" style="text-align:right" class="noircalibri10" name="duree_mois_these" id="duree_mois_these" value="<?php echo $row_rs_individu['duree_mois_these']; ?>" size="2" maxlength="2">
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td nowrap>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2">
                                            <tr>
                                              <td valign="top">
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2">
                                                  <tr>
                                                    <td align="center"><span class="bleugrascalibri10">(Co)Directeur(s)&nbsp;membre(s) <?php echo $GLOBALS['acronymelabo'] ?></span></td>
                                                    <td align="center"><span class="bleugrascalibri10">Tx encadrement</span></td>
                                                  </tr>
                                                  <?php
                                                  ksort($tab_rs_dir);
                                                  reset($tab_rs_dir);  
                                                  //list($numordre,$codedir)=each($tab_rs_dir);// premier dir du sujet
                                                  for($numdir=1;$numdir<=3;$numdir++)//3 directeurs maxi.
                                                  { list($numordre,$un_dir)=each($tab_rs_dir);//dir suivant du sujet
                                                    // distinct ne devrait pas etre utilise mais un individu peut avoir deux s&eacute;jours en cours par erreur
																										$query_rs= "SELECT distinct individu.codeindividu,concat(nom,' ',prenom) as nomprenom,if(hdr='oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='10','oui','non') as hdr". 
																																		 " FROM individu,individusejour,corps,cat,statutpers".
																																		 " WHERE individu.codeindividu=individusejour.codeindividu and individusejour.codecorps=corps.codecorps".
																																		 " and corps.codecat=cat.codecat and corps.codestatutpers=statutpers.codestatutpers".
																																		 " and (cat.codelibcat='EC' or cat.codelibcat='CHERCHEUR' or cat.codelibcat='ITARF') and ".periodeencours('datedeb_sejour','datefin_sejour').//permanent present
																																		 " and individu.codeindividu<>''". 
																																		 (($codelibcat=='DOCTORANT' && $numdir==1)?" and (hdr='oui' or corps.codecorps='01' or corps.codecorps='02' or corps.codecorps='03' or corps.codecorps='04' or corps.codecorps='10' or corps.codecorps='11')":"").//uniquement HDR pour 1er dir si these
																																		 //force a choisir un encadrant non vide($numdir==1?"":)
																																		 " UNION SELECT codeindividu,".($numdir==1?"' [Choix obligatoire] '":"' '")." as nomprenom,hdr from individu WHERE individu.codeindividu=''".
																																		 " ORDER BY nomprenom asc";
                                                    $rs=mysql_query($query_rs) or die(mysql_error());
																										if ($codelibcat=='DOCTORANT' && $numdir==1)
																										{?>
																											<script language="javascript">
                                                      tab_hdr=new Array()
                                                      <?php 
                                                      while($row_rs=mysql_fetch_assoc($rs))
                                                      { if($row_rs['hdr']=='oui')
                                                        {?>tab_hdr['<?php echo $row_rs['codeindividu'] ?>']=true
                                                        <?php
                                                        }
                                                       
                                                      }?>
                                                      function esthdr(codeindividu)
                                                      { return tab_hdr[codeindividu]
                                                      }
                                                      </script>
                                                    <?php   
																										}?>
                                                  <tr>
                                                    <td><select name="codedir#<?php echo $numdir ?>" class="noircalibri10" 
																											<?php 
																												if ($codelibcat=='DOCTORANT' && $numdir==1)
                                                      	{ ?> onChange="javascript:if(!esthdr(this.value)) alert('Pour information : le premier encadrant devrait &ecirc;tre HDR')"
                                                         <?php 
																												}?>   
                                                        >
                                                      <?php
																											mysql_data_seek($rs,0); 
                                                      while($row_rs=mysql_fetch_assoc($rs))
                                                      { ?>
                                                      <option value="<?php echo $row_rs['codeindividu'] ?>" <?php echo $row_rs['codeindividu']==$un_dir['codedir']?" selected ":"" ?>><?php echo $row_rs['nomprenom'];?></option>
                                                      <?php 
                                                      }?>
                                                    </select>
                                                      <?php 
                                                      if($numdir==1)
																											{?>
                                                      <sup><span class="champoblig">*</span></sup>
                                                      <?php 
																											}?></td>
                                                    <td align="center"><input name="taux_encadrement#<?php echo $numdir ?>" type="text" style="text-align:right" class="noircalibri10"  value="<?php echo $un_dir['taux_encadrement'] ?>" size="5" maxlength="5">
                                                      %</td>
                                                  </tr>
                                                  <?php
                                                  }?>
                                                </table>
                                              </td>
                                              <td valign="top">
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                                                  <tr>
                                                    <td colspan="2" align="center"><span class="bleugrascalibri10">Autre(s) (Co)Directeur(s) non membres <?php echo $GLOBALS['acronymelabo'] ?></span></td>
                                                  </tr>
                                                  <tr>
                                                    <td align="center"><span class="bleugrascalibri10">Titre nom pr&eacute;nom</span></td>
                                                    <td align="center"><span class="bleugrascalibri10">Mail</span></td>
                                                  </tr>
                                                  <tr>
                                                    <td><input name="autredir1" type="text" class="noircalibri10"  value="<?php echo htmlspecialchars($row_rs_individu['autredir1']) ?>" size="30" maxlength="50">
                                                    </td>
                                                    <td><input name="autredir1mail" type="text" class="noircalibri10"  value="<?php echo $row_rs_individu['autredir1mail'] ?>" size="30" maxlength="100">
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td><input name="autredir2" type="text" class="noircalibri10"  value="<?php echo htmlspecialchars($row_rs_individu['autredir2']) ?>" size="30" maxlength="50">
                                                    </td>
                                                    <td><input name="autredir2mail" type="text" class="noircalibri10"  value="<?php echo $row_rs_individu['autredir2mail'] ?>" size="30" maxlength="100">
                                                    </td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td valign="top" nowrap>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="2" cellpadding="0">
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">Dates de suivi comit&eacute; de s&eacute;lection &agrave; 12 mois :</td>
                                              <td><input name="date_suivi_comite_selection_12_mois_jj" type="text" class="noircalibri10" id="date_suivi_comite_selection_12_mois_jj" value="<?php echo substr($row_rs_individu['date_suivi_comite_selection_12_mois'],8,2); ?>" size="2" maxlength="2">
                                                <input name="date_suivi_comite_selection_12_mois_mm" type="text" class="noircalibri10" id="date_suivi_comite_selection_12_mois_mm" value="<?php echo substr($row_rs_individu['date_suivi_comite_selection_12_mois'],5,2); ?>" size="2" maxlength="2">
                                                <input name="date_suivi_comite_selection_12_mois_aaaa" type="text" class="noircalibri10" id="date_suivi_comite_selection_12_mois_aaaa" value="<?php echo substr($row_rs_individu['date_suivi_comite_selection_12_mois'],0,4); ?>" size="4" maxlength="4">
                                              </td>
                                              <td nowrap class="bleugrascalibri10">&agrave; 30 mois :</td>
                                              <td><input name="date_suivi_comite_selection_30_mois_jj" type="text" class="noircalibri10" id="date_suivi_comite_selection_30_mois_jj" value="<?php echo substr($row_rs_individu['date_suivi_comite_selection_30_mois'],8,2); ?>" size="2" maxlength="2">
                                                <input name="date_suivi_comite_selection_30_mois_mm" type="text" class="noircalibri10" id="date_suivi_comite_selection_30_mois_mm" value="<?php echo substr($row_rs_individu['date_suivi_comite_selection_30_mois'],5,2); ?>" size="2" maxlength="2">
                                                <input name="date_suivi_comite_selection_30_mois_aaaa" type="text" class="noircalibri10" id="date_suivi_comite_selection_30_mois_aaaa" value="<?php echo substr($row_rs_individu['date_suivi_comite_selection_30_mois'],0,4); ?>" size="4" maxlength="4">
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td valign="top" nowrap>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="2">
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">Date de soutenance :</td>
                                              <td>
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="2" cellpadding="0">
                                                  <tr>
                                                    <td><input name="date_soutenance_jj" type="text" class="noircalibri10" id="date_soutenance_jj" value="<?php echo substr($row_rs_individu['date_soutenance'],8,2); ?>" size="2" maxlength="2">
                                                      <input name="date_soutenance_mm" type="text" class="noircalibri10" id="date_soutenance_mm" value="<?php echo substr($row_rs_individu['date_soutenance'],5,2); ?>" size="2" maxlength="2">
                                                      <input name="date_soutenance_aaaa" type="text" class="noircalibri10" id="date_soutenance_aaaa" value="<?php echo substr($row_rs_individu['date_soutenance'],0,4); ?>" size="4" maxlength="4">
                                                      <img src="images/b_info.png" name="sprytrigger_info_date_soutenance" width="16" height="16" id="sprytrigger_info_date_soutenance">
                                                      <div class="tooltipContent_cadre" id="info_date_soutenance">
                                                      <span class="noircalibri10">
                                                        Si la date de soutenance n'est pas renseign&eacute;e et que la date de fin de s&eacute;jour est ant&eacute;rieure &agrave; aujourd'hui,<br>
                                                        la th&egrave;se est consid&eacute;r&eacute;e comme arr&ecirc;t&eacute;e &agrave; la date de fin de s&eacute;jour.<br>
                                                        A priori, si une th&egrave;se est soutenue, date de soutenance=date de fin de s&eacute;jour
                                                        </span></div>
                                                      <script type="text/javascript">
                                                      var sprytooltip_date_soutenance = new Spry.Widget.Tooltip("info_date_soutenance", "#sprytrigger_info_date_soutenance", {offsetX:10});
                                                      </script>
                                                    </td>
                                                    <td nowrap class="bleugrascalibri10">Heure :&nbsp;</td>
                                                    <td nowrap><input type="text" class="noircalibri10" name="heure_soutenance_hh" id="heure_soutenance_hh" value="<?php echo substr($row_rs_individu['heure_soutenance'],0,2); ?>" size="2" maxlength="2">
                                                      <span class="bleugrascalibri10">&nbsp;H&nbsp;</span>
                                                      <input type="text" class="noircalibri10" name="heure_soutenance_mn" id="heure_soutenance_mn" value="<?php echo substr($row_rs_individu['heure_soutenance'],3,2); ?>" size="2" maxlength="2"></td>
                                                    <td><span class="bleugrascalibri10">&nbsp;Lieu :</span>&nbsp;</td>
                                                    <td><input type="text" class="noircalibri10" name="lieu_soutenance" id="lieu_soutenance" value="<?php echo htmlspecialchars($row_rs_individu['lieu_soutenance']); ?>" size="45" maxlength="100"></td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">Titre :&nbsp;</td>
                                              <td><input type="text" class="noircalibri10" name="titre_these" id="titre_these" value="<?php echo htmlspecialchars($row_rs_individu['titre_these']); ?>" size="100" maxlength="300"></td>
                                            </tr>
                                            <tr>
                                              <td valign="top" nowrap class="bleugrascalibri10">R&eacute;sum&eacute; :&nbsp;<br>
                                                <span class="bleucalibri9">(</span><span id="resume_these#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['resume_these']) ?></span><span class="bleucalibri9">/6000 car. max.)</span></td>
                                              <td><textarea name="resume_these" cols="80" rows="5" wrap="PHYSICAL" class="noircalibri10" id="resume_these" <?php echo affiche_longueur_js("this","6000","'resume_these#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['resume_these']; ?></textarea></td>
                                            </tr>
                                            <tr>
                                              <td colspan="2" valign="top" nowrap class="bleugrascalibri10">
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="2" cellpadding="0">
                                                  <tr>
                                                    <td nowrap class="bleugrascalibri10">Rapporteur 1 :&nbsp;
                                                    </td>
                                                    <td><input type="text" class="noircalibri10" name="jury_rapp1_these" id="jury_rapp1_these" value="<?php echo htmlspecialchars($row_rs_individu['jury_rapp1_these']); ?>" size="35" maxlength="200">
                                                    </td>
                                                    <td rowspan="2" valign="top" nowrap><span class="bleugrascalibri10">&nbsp;Autres membres :&nbsp;</span><br>
                                                      <span class="bleucalibri9">&nbsp;(</span><span id="jury_autres_membres_these#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['jury_autres_membres_these']) ?></span><span class="bleucalibri9">/1000 car. max.)</span>
                                                    </td>
                                                    <td rowspan="2"><textarea name="jury_autres_membres_these" cols="35" rows="3" wrap="PHYSICAL" class="noircalibri10" id="jury_autres_membres_these" <?php echo affiche_longueur_js("this","1000","'jury_autres_membres_these#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['jury_autres_membres_these']; ?></textarea>
                                                    </td>
                                                  </tr>
                                                  <tr>
                                                    <td nowrap class="bleugrascalibri10">Rapporteur 2 :</td>
                                                    <td colspan="3"><input type="text" class="noircalibri10" name="jury_rapp2_these" id="jury_rapp2_these" value="<?php echo htmlspecialchars($row_rs_individu['jury_rapp2_these']); ?>" size="35" maxlength="200"></td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td valign="top" nowrap class="bleugrascalibri10">
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0" cellspacing="2">
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">Postdoc :
                                              </td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Etudes postdoc :&nbsp;
                                              </td>
                                              <td nowrap><input name="etude_postdoc" type="checkbox" id="etude_postdoc" <?php echo $row_rs_individu['etude_postdoc']=="oui"?'checked':''; ?>>
                                              </td>
                                              <td nowrap>&nbsp;
                                              </td>
                                              <td nowrap>&nbsp;
                                              </td>
                                            </tr>
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">&nbsp;</td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Date d&eacute;but :&nbsp;</td>
                                              <td nowrap><input name="dateemploi_postdoc_jj" type="text" class="noircalibri10" id="dateemploi_postdoc_jj" value="<?php echo substr($row_rs_individu['dateemploi_postdoc'],8,2); ?>" size="2" maxlength="2">
                                                <input name="dateemploi_postdoc_mm" type="text" class="noircalibri10" id="dateemploi_postdoc_mm" value="<?php echo substr($row_rs_individu['dateemploi_postdoc'],5,2); ?>" size="2" maxlength="2">
                                                <input name="dateemploi_postdoc_aaaa" type="text" class="noircalibri10" id="dateemploi_postdoc_aaaa" value="<?php echo substr($row_rs_individu['dateemploi_postdoc'],0,4); ?>" size="4" maxlength="4">
                                              </td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Employeur :</td>
                                              <td nowrap><input type="text" class="noircalibri10" name="employeur_postdoc" id="employeur_postdoc" value="<?php echo htmlspecialchars($row_rs_individu['employeur_postdoc']); ?>" size="30" maxlength="100">
                                              </td>
                                            </tr>
                                            <tr>
                                              <td nowrap class="bleugrascalibri10">&nbsp;
                                              </td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Profession :
                                              </td>
                                              <td nowrap><input type="text" class="noircalibri10" name="profession_postdoc" id="profession_postdoc" value="<?php echo htmlspecialchars($row_rs_individu['profession_postdoc']); ?>" size="30" maxlength="100">
                                              </td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Type :&nbsp;</td>
                                              <td nowrap><select name="codetypeprofession_postdoc" class="noircalibri10" id="codetypeprofession_postdoc">
                                                <?php
                                                while ($row_rs_typeprofession_postdoc = mysql_fetch_assoc($rs_typeprofession_postdoc))
                                                { ?>
                                                <option value="<?php echo $row_rs_typeprofession_postdoc['codetypeprofession_postdoc']?>" <?php if ($row_rs_typeprofession_postdoc['codetypeprofession_postdoc']==$row_rs_individu['codetypeprofession_postdoc']) {echo "SELECTED";} ?>><?php echo $row_rs_typeprofession_postdoc['libtypeprofession_postdoc']?></option>
                                                <?php
                                                }
                                                ?>
                                              	</select>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td nowrap>&nbsp;</td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Ville :&nbsp;</td>
                                              <td nowrap><input type="text" class="noircalibri10" name="ville_postdoc" id="ville_postdoc" value="<?php echo htmlspecialchars($row_rs_individu['ville_postdoc']); ?>" size="30" maxlength="50"></td>
                                              <td nowrap class="bleugrascalibri10">&nbsp;Pays :&nbsp;</td>
                                              <td nowrap><select name="codepays_postdoc" class="noircalibri10" id="codepays_postdoc">
                                                <?php
                                                while ($row_rs_pays_postdoc = mysql_fetch_assoc($rs_pays_postdoc))
                                                { ?>
                                                <option value="<?php echo $row_rs_pays_postdoc['codepays']?>" <?php if ($row_rs_pays_postdoc['codepays']==$row_rs_individu['codepays_postdoc']) {echo "SELECTED";} ?>><?php echo $row_rs_pays_postdoc['libpays']?></option>
                                                <?php
                                                }
                                                ?>
                                              </select>
                                              </td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td valign="top" nowrap class="bleugrascalibri10">
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="100%" cellpadding="2" class="table_emploi">
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="2">
                                            <tr>
                                              <td class="bleugrascalibri10">Notes doctorant :&nbsp;</td>
                                              <td><input type="text" class="noircalibri10" name="note_doct" id="note_doct" value="<?php echo $row_rs_individu['note_doct']; ?>" size="100" maxlength="100"></td>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                              </table>
                              </div>
                            </td>
                          </tr>
                          <?php
                          }
                        }//fin DOCTORANT?>
                       </table>
                      </td>
                    </tr>
                    
                    <?php 
                    }//fin $etudiant_ou_exterieur
										// 20170312
                    if($codelibcat=='EC' || $codelibcat=='CHERCHEUR' || $codelibcat=='ITARF')
                    {?>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="87%"  cellpadding="0">
                          <tr>
                            <td width="7%" nowrap><span class="bleugrascalibri10">Temps de travail :</span> <span class="bleucalibri9">(rempli par le SRH)</span></td>
                            <td width="11%" nowrap><span class="bleugrascalibri10">&nbsp;Quotit&eacute; administrative % :&nbsp;</span>
                              <input name="quotite_admin" type="text" class="noircalibri10" id="quotite_admin" value="<?php echo $row_rs_individu['quotite_admin']; ?>" size="2" maxlength="3"></td>
                            <td nowrap><span class="bleugrascalibri10">&nbsp;ETPT % :&nbsp;</span>
                              <input name="quotite_unite" type="text" class="noircalibri10" id="quotite_unite" value="<?php echo $row_rs_individu['quotite_unite']; ?>" size="2" maxlength="3"></td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <?php 
										}?>
                    <tr>
                      <td nowrap>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                          <tr>
                            <td><span class="mauvegrascalibri10"><?php echo htmlspecialchars($row_rs_individu['libcorps']); ?></span>
                              <?php 
                              if($estinvite)//invité
                              { ?> 
                              <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_corps">
                              <div class="tooltipContent_cadre" id="info_corps">
                                <span class="noircalibri10">
                                  En g&eacute;n&eacute;ral, un Invit&eacute;, durant son s&eacute;jour au laboratoire, est employ&eacute; et financ&eacute; par la tutelle.
                                </span>
                              </div>
                              <script type="text/javascript">
                              var sprytooltip_info_corps = new Spry.Widget.Tooltip("info_corps", "#sprytrigger_info_corps", {offsetX:10});
                              </script>
                              <?php 
                              }?>
                            </td>
                            <?php	
                            if(($codelibcat=='EC' || $codelibcat=='CHERCHEUR' || $codelibcat=='ITARF') && mysql_num_rows($rs_corpsgrade)>0)
                            { ?>
                            <td nowrap><span class="bleugrascalibri10">Grade :&nbsp;</span></td>
                            <td nowrap><select name="codegrade" class="noircalibri10" id="codegrade" >
                              <?php
                              while ($row_rs_corpsgrade = mysql_fetch_assoc($rs_corpsgrade))
                              { ?>
                              <option value="<?php echo $row_rs_corpsgrade['codegrade']; ?>" <?php echo ($row_rs_corpsgrade['codegrade']==$row_rs_individu['codegrade']?'selected':'') ?>><?php echo $row_rs_corpsgrade['libgrade_fr'] ?></option>
                              <?php
                              } ?>
                              </select>
                            </td>
                            <?php 
                            }
                            if($codelibcat=='ITARF')
                            {  ?>
                            <td>
                              <span class="bleugrascalibri10">BAP&nbsp;:&nbsp;</span>
                            </td>
                            <td>
                              <select name="codebap" class="noircalibri10" id="codebap">
                              <?php
                              while ($row_rs_bap = mysql_fetch_assoc($rs_bap)) 
                              { ?>
                                <option value="<?php echo $row_rs_bap['codebap']?>"<?php echo ($row_rs_bap['codebap']==$row_rs_individu['codebap']?'selected':"") ?>><?php echo $row_rs_bap['libcourtbap']?></option>
                              <?php
                              }
                              ?>
                              </select>
                            </td>
                            <?php
                            }
                            //if($codelibcat=='STAGIAIRE' || ($codelibcat=='EXTERIEUR' && $row_rs_individu['codenat']!='079'))
                            if($codelibcat=='STAGIAIRE' || $row_rs_individu['codenat']!='079')
                           	{ ?>
                            	<td nowrap><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'assurance_stage','Assurance jointe',$form_fiche_dossier_pers,true);?>
                              </td>
                              <?php	
                              if($codelibcat=='STAGIAIRE')
                              { ?>
                              <td nowrap><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_stage','Convention de stage',$form_fiche_dossier_pers,true);?>
                              </td>
															<?php 
															}
                            }
														// 20170315
														if(($codelibcat=='STAGIAIRE' && $codelibtypestage!='COLLEGE' && $codelibtypestage!='LYCEE') || $codelibcat=='DOCTORANT')
														{ ?>
														<td nowrap><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'carte_etudiant','Carte d&rsquo;&eacute;tudiant',$form_fiche_dossier_pers,true);?>
														</td>
														<?php
														} ?>
                          </tr>
                        </table>
                      </td>
                    </tr>
										<?php 
                    if(($est_admin || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte)) && $row_rs_individu['codenat']!='079')
                    { ?>
                      <tr>
                        <td>
                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                          	<tr>
                            	<td><?php echo str_repeat("&nbsp;",strlen(htmlspecialchars($row_rs_individu['libcorps']))); ?></td>
                              <td nowrap><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'resp_civile','Responsabilit&eacute; civile',$form_fiche_dossier_pers,true);?>
                              </td>
                              <td nowrap><span class="bleugrascalibri10">Inutile : </span>
                              <input type="checkbox" name="resp_civile_inutile" <?php echo $row_rs_individu['resp_civile_inutile']=="oui"?'checked':'' ?> class="noircalibri10"></td>
                              <td nowrap><?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'conv_accueil','Convention d&rsquo;accueil',$form_fiche_dossier_pers,true);?></td>
                              <td nowrap><span class="bleugrascalibri10">Inutile : </span>
                              <input type="checkbox" name="conv_accueil_inutile" <?php echo $row_rs_individu['conv_accueil_inutile']=="oui"?'checked':'' ?> class="noircalibri10">
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    <?php 
                    }?> 											
                     <?php                     	
										if($codelibcat=='STAGIAIRE')// attestation stage
										{	$dureestage_proposee_mois='';$dureestage_proposee_semaine='';
											$tab_dureesejour_amj=duree_aaaammjj($row_rs_individu['datedeb_sejour'], $row_rs_individu['datefin_sejour']);
											if($row_rs_individu['dureestage_mois']=='' && $row_rs_individu['dureestage_semaine']=='')
											{	$dureestage_proposee_mois=0;
												$dureestage_proposee_semaine=0;
												$arrondi_au_mois=false;//arrondi nb jours >= 22 a 1 mois
												if($tab_dureesejour_amj['a']+$tab_dureesejour_amj['m']>0)
												{ $dureestage_proposee_mois=$tab_dureesejour_amj['a']*12+$tab_dureesejour_amj['m'];
												}
												if($tab_dureesejour_amj['j']>=22)
												{ $dureestage_proposee_mois++;
													$arrondi_au_mois=true;
												}
												if($tab_dureesejour_amj['j']>=7 && !$arrondi_au_mois)
												{ $dureestage_proposee_semaine=(int)($tab_dureesejour_amj['j']/7);
												} 
												$dureestage_proposee_mois=$dureestage_proposee_mois>0?$dureestage_proposee_mois:'';
												$dureestage_proposee_semaine=$dureestage_proposee_semaine>0?$dureestage_proposee_semaine:'';
											}?>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                          <tr>
                            <td>
                              <span class="bleugrascalibri10">Attestation - Dur&eacute;e mois :&nbsp;</span>
                              <input type="text" name="dureestage_mois" id="dureestage_mois" class="noircalibri10" value="<?php echo $row_rs_individu['dureestage_mois']==''?$dureestage_proposee_mois:$row_rs_individu['dureestage_mois'] ?>" size="2" maxlength="2">
                            </td>
                            <td>
                              <span class="bleugrascalibri10">semaines :&nbsp;</span>
                              <input type="text" name="dureestage_semaine" id="dureestage_semaine" class="noircalibri10" value="<?php echo $row_rs_individu['dureestage_semaine']==''?$dureestage_proposee_semaine:$row_rs_individu['dureestage_semaine'] ?>" size="1" maxlength="1">
                            </td>
                            <td>
                            </td>
                            <td class="orangegrascalibri11">
                              (Pour information, dur&eacute;e exacte : <?php echo ($tab_dureesejour_amj['a']==0?'':$tab_dureesejour_amj['a'].'a').($tab_dureesejour_amj['m']==0?'':$tab_dureesejour_amj['m'].'m').($tab_dureesejour_amj['j']==0?'':$tab_dureesejour_amj['j'].'j') ?>)
                            </td>	
                            <?php 
													  if(!($demander_autorisation && $GLOBALS['estzrr']))// si pas de dde FSD ou si pas zrr, on peut saisir le montant du stage
              						  {?>
                            <td><span class="bleugrascalibri10">Montant<sup><span class="champoblig">*</span></sup> (&euro;) :&nbsp;</span>
                                <input type="text" name="montantfinancement" id="montantfinancement" class="noircalibri10" value="<?php echo $row_rs_individu['montantfinancement'] ?>" size="12" maxlength="12">
                            </td>
                            <?php 
														}?>
														<td>
                            <?php   
														/*if($row_rs_individu['montantfinancement']>0)
                            {	*/?>
                            	<input type="image" name="submit_attestation_stage#<?php echo $attestation_stage ?>##" src="images/b_attestation_stage_<?php echo $attestation_stage ?>.png" width="85" height="15"
                                            onClick="return confirme('valider','Attestation  envoy&eacute;e <?php echo $attestation_stage=='oui'?': NON ':'' ?> ?')">
                                            <input type="hidden" name="attestation_stage" value="<?php echo $attestation_stage ?>">
                              </td>
                            	<td>
                              <a href="detailattestationstage.php?codeindividu=<?php echo $codeindividu ?>&numsejour=<?php echo $numsejour ?>" target="_blank">Editer l'attestation de stage</a>
														<?php 
														/* }
                            else
                            {?> Pas d'attestation &agrave; &eacute;tablir car pas de montant de gratification
                            <?php 
														} */?>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
										<?php 
                    }// fin STAGIAIRE attestation
    
                    if($codelibtypestage!='COLLEGE' && $codelibtypestage!='LYCEE' && $codelibcat!='PRESTATAIRE' && $pourquoi_pas_de_demande_fsd!='- de 5j')
                    {?>
                    <tr>
                      <td nowrap>
                        <a name="contrats"></a>
                          <span class="bleugrascalibri10">Contrat / Emploi :</span>
                        <?php 
                        if(!$afficher_nouvel_emploi)//bouton ajouter_emploi si on n'est pas déja en afficher_nouvel_emploi
                        { ?>
                        <input name="submit_afficher_nouvel_emploi##" value="afficher_nouvel_emploi" type="image" class="icon" id="submit_afficher_nouvel_emploi" src="images/b_add.png" 
                                  alt="Nouveau contrat/emploi" title="Nouveau contrat/emploi" onClick="return confirme('nouveau contrat','Enregistrer et ajouter un nouveau contrat/emploi ?')">
                        <?php 
                        }?>
                      <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_emploi">
                      <div class="tooltipContent_cadre" id="info_emploi">
                        <span class="noircalibri10">Les contrats (emplois) sont relatifs &agrave; un s&eacute;jour mais peuvent parfois s'&eacute;taler sur plus d'un s&eacute;jour.<br>
                        C'est le cas pour un doctorant qui soutient sa th&egrave;se avant la fin de son contrat d'ATER : il devient postdoc au lendemain de sa soutenance. <br>
                        Dans ce cas, il y a cr&eacute;ation d'un nouveau s&eacute;jour en qualit&eacute; de postdoc : l'emploi dans ce nouveau s&eacute;jour commence automatiquement en qualit&eacute; d'ATER,<br>
                        les dates d'emploi sont celles pr&eacute;cis&eacute;es dans son s&eacute;jour en qualit&eacute; de doctorant.<br>
                        </span>
                        <span class="mauvecalibri10">On ne peut pas supprimer un emploi s'il est le seul dans le séjour : il faut en créer un nouveau afin de le supprimer ensuite ou plus simplement remplacer les informations de l'emploi &agrave; supprimer.</span>
                        <br><img src="images/b_link.png" border="0"><span class="noircalibri10"> : l&rsquo;emploi est li&eacute; &agrave; un autre s&eacute;jour (pointer la souris sur l&rsquo;ic&ocirc;ne de liaison). Un changement de date d&rsquo;un emploi li&eacute; peut entrainer la
                        dissociation de l&rsquo;emploi d&rsquo;un s&eacute;jour.</span>
                        <br><span class="rougecalibri10">Ces dates sont donc &agrave; manipuler avec pr&eacute;caution.</span><span class="noircalibri10"> Si un emploi est dissoci&eacute; du s&eacute;jour en cours par erreur (l'emploi disparait si ses dates ne couvrent plus une partie des dates de s&eacute;jour), deux méthodes :
                        <br>
                        - modifier les dates du s&eacute;jour et enregistrer afin de voir appara&icirc;tre l&rsquo;emploi puis ajuster les dates du s&eacute;jour et de l'emploi</span><br>
                        <span class="noircalibri10">- ou &eacute;diter le s&eacute;jour auquel il est li&eacute; et modifer les dates de l'emploi.</span>
                        </div>
                        <script type="text/javascript">
                        var sprytooltip_info_emploi = new Spry.Widget.Tooltip("info_emploi", "#sprytrigger_info_emploi", {offsetX:10});
                        </script>
                       </td>
                    </tr>
                    <?php
                    // emplois
                    $plus_dun_emploi=(count($row_rs_individu['tab_emploi'])>1);
                    // boucle emplois
                    foreach($row_rs_individu['tab_emploi'] as $un_numemploi=>$row_rs_individuemploi) 
                    { mysql_data_seek($rs_etab,0);
                      $lien_emploi_precedent=false; $lien_emploi_suivant=false;
                      if(isset($row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']))
                      { $lien_emploi_precedent=true;
                      }
                      if(isset($row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']))
                      { $lien_emploi_suivant=true;
                      }
                      if(mysql_num_rows($rs_corpsmodefinancement)>=1){mysql_data_seek($rs_corpsmodefinancement,0);}
                      mysql_data_seek($rs_centrecout_pers,0);//Déplace le pointeur interne au 1er enregistrement
                      // seul l'emploi a modifier presente des champs modifiables et dont les noms ne sont pas suffixes par #nn
                      if($un_numemploi==$numemploi)//emploi a editer : les champs n'ont pas de suffixe
                      { $disabled="";
                        $editable=true;
                        $sufx_numemploi="";
                        $emploi_a_modifier=true;
                        $class_cache_ou_affiche='affiche';
                        $img_plus_ou_moins='b_moins.png';
                        $input_disabled="noircalibri10";
                      } 
                      else
                      { $disabled="disabled";
                        $editable=false;
                        $sufx_numemploi="#".$un_numemploi;
                        $emploi_a_modifier=false;
                        $class_cache_ou_affiche='cache';
                        $img_plus_ou_moins='b_plus.png';
                        $input_disabled="noircalibri10";
                      }?>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>"  class="table_emploi<?php echo $emploi_a_modifier?'':'_grise' ?>" >
                          <tr>
                            <td align="center" valign="top">
                              <img src="images/<?php echo $img_plus_ou_moins ?>" alt="" name="image_emploi<?php echo $sufx_numemploi ?>" id="image_emploi<?php echo $sufx_numemploi ?>"
                                      onClick="javascript:
                                                emploi=document.getElementById('emploi<?php echo $sufx_numemploi ?>');
                                                if(emploi.className=='affiche')
                                                { emploi.className='cache';
                                                  document.getElementById('image_emploi<?php echo $sufx_numemploi ?>').src='images/b_plus.png';
                                                }
                                                else 
                                                { emploi.className='affiche';
                                                  document.getElementById('image_emploi<?php echo $sufx_numemploi ?>').src='images/b_moins.png';
                                                }"
                                                >
                            </td>
                            <td colspan="2">
                              <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                                <tr>
                                  <td nowrap><span class="bleugrascalibri10">&nbsp;du&nbsp;:&nbsp;&nbsp;</span>
                                  </td>
                                  <td nowrap>
                                    <input name="datedeb_emploi_jj<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="datedeb_emploi_jj<?php echo $sufx_numemploi ?>" value="<?php echo substr($row_rs_individuemploi['datedeb_emploi'],8,2); ?>" size="2" maxlength="2" <?php echo $disabled; ?>>
                                    <input name="datedeb_emploi_mm<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="datedeb_emploi_mm<?php echo $sufx_numemploi ?>" value="<?php echo substr($row_rs_individuemploi['datedeb_emploi'],5,2); ?>" size="2" maxlength="2" <?php echo $disabled; ?>>
                                    <input name="datedeb_emploi_aaaa<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="datedeb_emploi_aaaa<?php echo $sufx_numemploi ?>" value="<?php echo substr($row_rs_individuemploi['datedeb_emploi'],0,4); ?>" size="4" maxlength="4" <?php echo $disabled; ?>>
                                  </td>
                                  <td nowrap class="bleugrascalibri10">&nbsp;au&nbsp;: &nbsp;
                                  </td>
                                  <td nowrap><input name="datefin_emploi_jj<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="datefin_emploi_jj<?php echo $sufx_numemploi ?>" value="<?php echo substr($row_rs_individuemploi['datefin_emploi'],8,2); ?>" size="2" maxlength="2" <?php echo $disabled; ?>>
                                    <input name="datefin_emploi_mm<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="datefin_emploi_mm<?php echo $sufx_numemploi ?>" value="<?php echo substr($row_rs_individuemploi['datefin_emploi'],5,2); ?>" size="2" maxlength="2" <?php echo $disabled; ?>>
                                    <input name="datefin_emploi_aaaa<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="datefin_emploi_aaaa<?php echo $sufx_numemploi ?>" value="<?php echo substr($row_rs_individuemploi['datefin_emploi'],0,4); ?>" size="4" maxlength="4" <?php echo $disabled; ?>>
                                  </td>
                                  <td nowrap><span class="bleugrascalibri10">Employeur<sup><span class="champoblig">*</span></sup> :&nbsp;</span>
                                  </td>
                                  <td nowrap>
                                   <select name="codeetab<?php echo $sufx_numemploi ?>" id="codeetab<?php echo $sufx_numemploi ?>" class="<?php echo $input_disabled ?>" <?php echo $disabled; ?>>
                                    <?php
                                    while ($row_rs_etab = mysql_fetch_assoc($rs_etab)) 
                                    { ?>
                                    <option value="<?php echo $row_rs_etab['codeetab']?>" <?php if ($row_rs_etab['codeetab']==$row_rs_individuemploi['codeetab']) {echo "SELECTED";} ?>><?php echo $row_rs_etab['libetab_fr']?></option>
                                    <?php
                                    } 
                                    ?>
                                    </select>
                                  </td>
                                  <td nowrap><span class="bleucalibri9">Autre, pr&eacute;ciser : </span></td>
                                  <td nowrap><input name="autreetab<?php echo $sufx_numemploi ?>" id="autreetab<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" value="<?php echo htmlspecialchars($row_rs_individuemploi['autreetab']); ?>" size="30" maxlength="100" <?php echo $disabled; ?>></td>
                                  <td nowrap><span class="bleucalibri9">Pays : </span></td>
                                  <td>
                                  	<?php mysql_data_seek($rs_pays_autreetab,0);?>
                                  	<select name="codepays_autreetab<?php echo $sufx_numemploi ?>" class="noircalibri10" id="codepays_autreetab<?php echo $sufx_numemploi ?>" <?php echo $disabled; ?>>
																		<?php
                                    while ($row_rs_pays_autreetab = mysql_fetch_assoc($rs_pays_autreetab)) 
                                    { ?>
                                      <option value="<?php echo $row_rs_pays_autreetab['codepays']?>" <?php echo $row_rs_pays_autreetab['codepays']==$row_rs_individuemploi['codepays_autreetab']?'selected':''?>><?php echo strlen($row_rs_pays_autreetab['libpays'])>27?substr($row_rs_pays_autreetab['libpays'],0,27).'...':$row_rs_pays_autreetab['libpays']?></option>
                                      <?php
                                    }
                                    ?>
                                    </select>

                                  </td>
                                  <td>
                                    <?php 
                                    if($emploi_a_modifier)
                                    { ?>
                                    <input name="submit_enregistrer_emploi#<?php echo $un_numemploi ?>##" type="image" class="icon" id="submit_enregistrer_emploi" src="images/b_save.png" 
                                            alt="Enregistrer ce contrat/emploi" title="Enregistrer ce contrat/emploi" onClick="return confirme('Enregistrer contrat','Enregistrer ce contrat/emploi ?')">
                                      <?php
                                    }
                                    else
                                    { ?>
                                    <img src="images/espaceur.gif" alt="" width="16" height="1">
                                      <?php
                                    }?>
                                  </td>
                                  <?php
                                  if($plus_dun_emploi)//affichage des icones modifier et/ou supprimer
                                  {?>
                                  <td>
                                    <?php 
                                    if($emploi_a_modifier)
                                    { ?>
                                    <img src="images/espaceur.gif" alt="" width="16" height="1">
                                    <?php
                                    }
                                    else
                                    { ?>
                                    <input name="submit_modifier_emploi#<?php echo $un_numemploi ?>##" type="image" class="icon" id="submit_modifier_emploi" src="images/b_edit.png" 
                                          alt="Modifier ce contrat/emploi" title="Modifier ce contrat/emploi" onClick="return confirme('Modifier contrat','Modifier ce contrat/emploi ?')">
                                    <?php 
                                    }?>
                                  </td>
                                  <td><img src="images/espaceur.gif" alt="" width="10" height="1">
                                  </td>
                                  <td><input name="submit_supprimer_emploi#<?php echo $un_numemploi ?>##" type="image" class="icon" id="submit_supprimer_emploi" src="images/b_drop.png" 
                                          alt="Supprimer ce contrat/emploi" title="Supprimer ce contrat/emploi" 
                                      onClick="return confirme('Supprimer contrat','<?php if($lien_emploi_precedent || $lien_emploi_suivant) {?>Ce contrat porte sur plusieurs s&eacute;jours.\n<?php }?>Supprimer ce contrat ?')">
                                  </td>
                                    <?php
                                  }?>
                                </tr>
                              </table>
                            </td>
                          </tr>
                          <tr>
                            <td>&nbsp;</td>
                            <td>
                              <?php 
                              if($lien_emploi_precedent)
                              {?>
                              <img class="icon" width="20" height="10" src="images/b_link.png" name="sprytrig_visualiser_<?php echo $row_rs_individu['codeindividu']; ?>" id="sprytrig_visualiser_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']['numsejour']; ?>"/>
                              <div class="tooltipContent_cadre" id="sprytooltip_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']['numsejour']; ?>"><?php echo detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']['numsejour'],$codeuser) ?>
                              </div>
                                                 
                              <script type="text/javascript">
                              var sprytooltip_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']['numsejour']; ?> = new Spry.Widget.Tooltip("sprytooltip_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']['numsejour']; ?>", "#sprytrig_visualiser_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_precedent']['numsejour']; ?>", {offsetX:0, offsetY:0});
                              </script>
                              <?php 
                              }
                              if($lien_emploi_suivant)
                              {?>	
                              <img class="icon" width="20" height="10" src="images/b_link.png" name="sprytrig_visualiser_<?php echo $row_rs_individu['codeindividu']; ?>" id="sprytrig_visualiser_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']['numsejour']; ?>"/>
                              <div class="tooltipContent_cadre" id="sprytooltip_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']['numsejour']; ?>"><?php echo detailindividu($row_rs_individu['codeindividu'],$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']['numsejour'],$codeuser) ?>
                              </div>
                                                 
                              <script type="text/javascript">
                              var sprytooltip_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']['numsejour']; ?> = new Spry.Widget.Tooltip("sprytooltip_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']['numsejour']; ?>", "#sprytrig_visualiser_<?php echo $row_rs_individu['codeindividu'].$row_rs_individu['tab_emploi'][$un_numemploi]['lien_emploi_suivant']['numsejour']; ?>", {offsetX:0, offsetY:0});
                              </script>
                              <?php 
                              }
                              if(!$lien_emploi_precedent && !$lien_emploi_suivant)
                              {?>
                              <img src="images/espaceur.gif" width="20" height="1" border="0">
                              <?php 
                              }?>
                            </td>
                            <td>
                              <div id="emploi<?php echo $sufx_numemploi ?>" class="<?php echo $class_cache_ou_affiche?>">
                              <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>"  class="table_emploi<?php echo $emploi_a_modifier?'':'_grise' ?>" >
                                <tr>
                                  <td>
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                            <tr>
                                              <?php 
                                              $au_moins_un_mode_non_vide=false;
                                              $row_rs_corpsmodefinancement = mysql_fetch_assoc($rs_corpsmodefinancement);
                                              if(mysql_num_rows($rs_corpsmodefinancement)>1)
                                              { $au_moins_un_mode_non_vide=true;
                                              }
                                              else if(mysql_num_rows($rs_corpsmodefinancement)==1)
                                              { if($row_rs_corpsmodefinancement['codemodefinancement']!='')
                                                { $au_moins_un_mode_non_vide=true; 
                                                }
                                              }
                                              
                                              if($au_moins_un_mode_non_vide)
                                              { ?>
                                              <td nowrap class="bleugrascalibri10">Mode de financement :&nbsp; </td>
                                              <td nowrap> 
                                                <select name="codemodefinancement<?php echo $sufx_numemploi ?>" id="codemodefinancement<?php echo $sufx_numemploi ?>" class="noircalibri10"
                                                       onclick="javascript: if(this.value=='03')
                                                                            {document.getElementById('modefinancement_sur_budget_labo<?php echo $sufx_numemploi ?>').className='affiche';
                                                                            }
                                                                            else 
                                                                            {document.getElementById('modefinancement_sur_budget_labo<?php echo $sufx_numemploi ?>').className='cache';
                                                                            }"
                                                        <?php echo $disabled; ?>>
                                                  <?php
                                                  do 
                                                  { ?>
                                                  <option value="<?php echo $row_rs_corpsmodefinancement['codemodefinancement']?>" <?php if ($row_rs_corpsmodefinancement['codemodefinancement']==$row_rs_individuemploi['codemodefinancement']) {echo "SELECTED";} ?>><?php echo $row_rs_corpsmodefinancement['libmodefinancement']?></option>
                                                  <?php
                                                  }while ($row_rs_corpsmodefinancement = mysql_fetch_assoc($rs_corpsmodefinancement));
                                                  ?>
                                                  </select>
                                              </td>
                                              <td nowrap><span class="bleugrascalibri10">D&eacute;tail
                                              <?php 
                                              if($codelibcat!='DOCTORANT' && $codelibcat!='POSTDOC')
                                              {?> (ou autre mode de financement)
                                              <?php 
                                              } ?>
                                              &nbsp;:&nbsp;</span></td>
                                              <td nowrap><input name="detailmodefinancement<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="detailmodefinancement<?php echo $sufx_numemploi ?>" value="<?php echo htmlspecialchars($row_rs_individuemploi['detailmodefinancement']); ?>" size="50" maxlength="100" <?php echo $disabled; ?>></td>
                                              <?php 
                                              }
                                              else
                                              {?> 
                                              <td nowrap><span class="bleugrascalibri10">Mode de financement :&nbsp;</span></td>
                                              <td nowrap><input name="detailmodefinancement<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="detailmodefinancement<?php echo $sufx_numemploi ?>" value="<?php echo htmlspecialchars($row_rs_individuemploi['detailmodefinancement']); ?>" size="50" maxlength="100" <?php echo $disabled; ?>></td>
                                              <?php
                                              }?>
                                            </tr>
                                          </table>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <div id="modefinancement_sur_budget_labo<?php echo $sufx_numemploi ?>" class=<?php echo ($row_rs_individuemploi['codemodefinancement']=='03'?'"affiche"':'"cache"') ?>>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="95%"  cellpadding="0">
                                            <tr>
                                              <td nowrap><span class="bleugrascalibri10">Centre de co&ucirc;t : </span></td>
                                              <td nowrap><input name="autrecentrecout<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="autrecentrecout<?php echo $sufx_numemploi ?>" value="<?php echo htmlspecialchars($row_rs_individuemploi['autrecentrecout']); ?>" size="20" maxlength="100" <?php echo $disabled; ?>></td>
                                              <td nowrap><span class="bleugrascalibri10">- EOTP(s) n&deg; : </span></td>
                                              <td nowrap><input name="eotp<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="eotp<?php echo $sufx_numemploi ?>" value="<?php echo htmlspecialchars($row_rs_individuemploi['eotp']); ?>" size="50" maxlength="100" <?php echo $disabled; ?>></td>
                                              <td nowrap><span class="bleugrascalibri10">- Contrat(s) : </span></td>
                                              <td nowrap><input name="contrat<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="contrat<?php echo $sufx_numemploi ?>" value="<?php echo htmlspecialchars($row_rs_individuemploi['contrat']); ?>" size="50" maxlength="100" <?php echo $disabled; ?>></td>
                                            </tr>
                                          </table>
                                          </div>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                                            <tr>
                                              <td>
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                                                  <tr>
                                                    <td class="bleugrascalibri10">Autres missions compl&eacute;mentaires (monitorat,...) :&nbsp;</td>
                                                    <td><input name="missioncomp<?php echo $sufx_numemploi ?>" type="checkbox" class="<?php echo $input_disabled ?>" id="missioncomp<?php echo $sufx_numemploi ?>" <?php echo $row_rs_individuemploi['missioncomp']=='oui'?'checked':''; ?> <?php echo $disabled; ?>></td>
                                                    <td nowrap class="bleugrascalibri10">&nbsp;D&eacute;tail autres missions :&nbsp;</td>
                                                    <td><input name="missioncomp_detail<?php echo $sufx_numemploi ?>" type="text" class="noircalibri10" id="missioncomp_detail<?php echo $sufx_numemploi ?>" value="<?php echo htmlspecialchars($row_rs_individuemploi['missioncomp_detail']); ?>" size="50" maxlength="100" <?php echo $disabled; ?>></td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                            <?php 
                                            if(array_key_exists('srh',$tab_roleuser) || $est_admin || array_key_exists('theme',$tab_roleuser))
                                            {?>
                                            <tr>
                                              <td>
                                                <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                                                  <tr>
                                                    <td nowrap class="bleugrascalibri10">Montant mensuel charg&eacute; :&nbsp;</td>
                                                    <td><input name="montant_mensuel_charge<?php echo $sufx_numemploi ?>" type="text" style="text-align:right" class="noircalibri10" id="montant_mensuel_charge<?php echo $sufx_numemploi ?>" value="<?php echo $row_rs_individuemploi['montant_mensuel_charge']; ?>" size="10" maxlength="10" <?php echo $disabled; ?>></td>
                                                    <td nowrap class="bleugrascalibri10">&nbsp;Montant mensuel brut :&nbsp;</td>
                                                    <td><input name="montant_mensuel_brut<?php echo $sufx_numemploi ?>" type="text" style="text-align:right" class="noircalibri10" id="montant_mensuel_brut<?php echo $sufx_numemploi ?>" value="<?php echo $row_rs_individuemploi['montant_mensuel_brut']; ?>" size="10" maxlength="10" <?php echo $disabled; ?>></td>
                                                    <td nowrap class="bleugrascalibri10">&nbsp;Montant mensuel net :&nbsp;</td>
                                                    <td><input name="montant_mensuel_net<?php echo $sufx_numemploi ?>" type="text" style="text-align:right" class="noircalibri10" id="montant_mensuel_net<?php echo $sufx_numemploi ?>" value="<?php echo $row_rs_individuemploi['montant_mensuel_net']; ?>" size="10" maxlength="10" <?php echo $disabled; ?>></td>
                                                  </tr>
                                                </table>
                                              </td>
                                            </tr>
                                            <?php 
                                            }?>
                                          </table>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                                <tr>
                                  <td nowrap >
                                    <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                      <tr>
                                        <td>
                                          <?php echo ligne_txt_upload_pj_individu($codeindividu,'emploi',$un_numemploi,'contrat_travail','Contrat travail',$form_fiche_dossier_pers,$editable);?>
                                        </td>
                                        <td>
                                          <?php echo ligne_txt_upload_pj_individu($codeindividu,'emploi',$un_numemploi,'avenant_contrat','Avenant contrat',$form_fiche_dossier_pers,$editable);?>
                                        </td>
                                      </tr>
                                    </table>
                                  </td>
                                </tr>
                              </table>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <?php 
                    }// fin boucle emplois
                    ?>
                  </table>
                  <?php 
                  }// fin non permanent ?>
                </td>
              </tr>
                <?php 
                }// fin non permanent non COLLEGE
                ?>
              <tr>
                <td><?php 
                  if($permanent=='oui')
                  {  ?>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="1" cellpadding="0">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>">
                          <tr>
                            <td nowrap><span class="bleugrascalibri10">Date de recrutement&nbsp;:</span></td>
                            <td nowrap><?php if(substr($row_rs_individu['datedeb_sejour'],8,2)!=''){ echo substr($row_rs_individu['datedeb_sejour'],8,2).'/'.substr($row_rs_individu['datedeb_sejour'],5,2).'/'.substr($row_rs_individu['datedeb_sejour'],0,4);} ?></td>
                            <td nowrap><span class="bleugrascalibri10">&nbsp;D&eacute;part :&nbsp;</span></td>
                            <td nowrap><?php if(substr($row_rs_individu['datefin_sejour'],8,2)!=''){ echo substr($row_rs_individu['datefin_sejour'],8,2).'/'.substr($row_rs_individu['datefin_sejour'],5,2).'/'.substr($row_rs_individu['datefin_sejour'],0,4);} ?></td>
                            <td nowrap><span class="bleugrascalibri10">&nbsp;Employeur :&nbsp;</span></td>
                            <td nowrap>
                              <select name="codeetab" class="noircalibri10" >
                              <?php
                                while ($row_rs_etab = mysql_fetch_assoc($rs_etab)) 
                                { ?>
                                <option value="<?php echo $row_rs_etab['codeetab']?>"<?php if ($row_rs_etab['codeetab']==$row_rs_individu['tab_emploi'][$numemploi]['codeetab']) {echo "SELECTED";} ?>><?php echo $row_rs_etab['libetab_fr']?></option>
                                <?php
                                }
                                ?>
                              </select>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0">
                          <tr>
                            <td nowrap><span class="mauvegrascalibri10"><?php echo htmlspecialchars($row_rs_individu['libcorps']); ?></span><span class="mauvegrascalibri11">&nbsp;</span></td>
                            <?php 
                            if(($codelibcat=='EC' || $codelibcat=='CHERCHEUR' || $codelibcat=='ITARF')  && mysql_num_rows($rs_corpsgrade)>0)
                            { ?>
                            <td nowrap><span class="bleugrascalibri10">Grade :&nbsp;</span></td>
                            <td nowrap><select name="codegrade" class="noircalibri10" id="codegrade" >
                              <?php
                              while ($row_rs_corpsgrade = mysql_fetch_assoc($rs_corpsgrade))
                              { ?>
                              <option value="<?php echo $row_rs_corpsgrade['codegrade']; ?>" <?php echo ($row_rs_corpsgrade['codegrade']==$row_rs_individu['codegrade']?'selected':'') ?>><?php echo $row_rs_corpsgrade['libgrade_fr'] ?></option>
                              <?php
                              } ?>
                              </select>
                            </td>
                            <?php 
                            }?>
                            <td nowrap>
                            <?php 
                            if($codelibcat=='ITARF')
                            { ?>
                            <td nowrap><span class="bleugrascalibri10">BAP</span></td>
                            <td> 
                            <select name="codebap" class="noircalibri10" id="codebap">
                              <?php
                              while ($row_rs_bap = mysql_fetch_assoc($rs_bap)) 
                              { ?>
                              <option value="<?php echo $row_rs_bap['codebap']?>"<?php echo ($row_rs_bap['codebap']==$row_rs_individu['codebap']?'selected':"") ?>><?php echo $row_rs_bap['libcourtbap']?></option>
                              <?php
                              }
                              ?>
                              </select>
                            </td>
                            <?php
                            }
                            else if($codelibcat=='EC' || $codelibcat=='CHERCHEUR')
                            { ?>
                            <td nowrap><span class="bleugrascalibri10">Section : </span>
                            <select name="codecommission" class="noircalibri10" onChange="affichesection(this)">
                              <?php
                              foreach ($tab_commission as $codecommission => $row_rs) 
                              { ?>
                              <option value="<?php echo $row_rs['codecommission']?>"<?php echo ($row_rs['codecommission']==$row_rs_individu['codecommission']?'selected':""); ?>><?php echo $row_rs['libcommission']?></option>
                              <?php
                              }
                              ?>
                              </select>
                            </td>
                            <td>
                            <select name="codesection" class="noircalibri10" >
                              <?php
                              foreach ($tab_commissionsection as $codesection => $row_rs)
                              { if($row_rs['codecommission']==$row_rs_individu['codecommission'])
																{?>
                              <option value="<?php echo $row_rs['codesection']?>" <?php echo ($row_rs['codesection']==$row_rs_individu['codesection']?'selected':""); ?>><?php echo $row_rs['numsection'].' '.$row_rs['libsection']?></option>
                              <?php
																}
                              } 
                              ?>
                            </select>
                            </td>
                            <?php
                            }?>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" width="50%"  cellpadding="0">
                          <tr>
                            <td width="11%" nowrap>
                            <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_quotite_admin">
                              <div class="tooltipContent_cadre" id="info_quotite_admin">
                                <span class="noircalibri10">
                                  Indiquer le taux de temps de travail total effectu&eacute; en quotit&eacute; administrative, le taux de temps de travail <?php echo $GLOBALS['acronymelabo'] ?> en ETPT*<br>
                                  Les personnels &agrave; temps plein ont en g&eacute;n&eacute;ral une quotit&eacute; administrative de 100 et un ETPT <?php echo $GLOBALS['acronymelabo'] ?> de 100<br>
                                  Si une personne travaille &agrave; 80% et consacre la moiti&eacute; de son temps au laboratoire, sa quotit&eacute; administrative est de 80, son ETPT de 40<br>
																	Un enseignant-chercheur &agrave; temps plein a une quotit&eacute; administrative de 100 et un ETPT <?php echo $GLOBALS['acronymelabo'] ?> de 50<br>
                                  *ETPT=Equivalent temps plein travaill&eacute;
                                </span>
                              </div>
                              <script type="text/javascript">
                              var sprytooltip_info_quotite_admin = new Spry.Widget.Tooltip("info_quotite_admin", "#sprytrigger_info_quotite_admin", {closeOnTooltipLeave:true, followMouse:true});
                              </script>
															<span class="bleugrascalibri10">&nbsp;Quotit&eacute; administrative % :&nbsp;</span>
                              <input name="quotite_admin" type="text" class="noircalibri10" id="quotite_admin" value="<?php echo $row_rs_individu['quotite_admin']; ?>" size="2" maxlength="3"></td>
                            <td width="11%" nowrap><span class="bleugrascalibri10">&nbsp;ETPT <?php echo $GLOBALS['acronymelabo'] ?> % :&nbsp;</span>
                              <input name="quotite_unite" type="text" class="noircalibri10" id="quotite_unite" value="<?php echo $row_rs_individu['quotite_unite']; ?>" size="2" maxlength="3"></td>
                            <td width="11%" nowrap><!--// 20170315 -->
                            <span class="bleugrascalibri10">Hors effectif :</span>
                            <input type="checkbox" name="hors_effectif" <?php echo $row_rs_individu['hors_effectif']=="oui"?'checked':'' ?>
                             onClick="if(!confirm('Voulez-vous r&eacute;ellement modifier le statut hors effectif de ce personnel ?'))
                                      { this.checked=!this.checked;
                                      }
                                      else if(this.checked==true && document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['quotite_unite'])
                                      { document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['quotite_unite'].value='';
                                      }
                                      "
                            >
                            <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_hors_effectif">
                            <div class="tooltipContent_cadre" id="info_hors_effectif">
                              <span class="noircalibri10">
                                Hors effectif : personnel non comptabilis&eacute; dans les effectifs du laboratoire (ETPT=0)<br>
                                Ce personnel peut faire partie des listes de diffusion, annuaire...
                              </span>
                              </div>
                            <script type="text/javascript">
                            var sprytooltip_info_hors_effectif = new Spry.Widget.Tooltip("info_hors_effectif", "#sprytrigger_info_hors_effectif", {closeOnTooltipLeave:true, followMouse:true});
                            </script>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <?php
                    if($codelibcat!='ITARF')
                    { ?>
                    <tr>
                      <td>
                        <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0">
                          <tr>
                            <td nowrap><span class="bleugrascalibri10">HDR : </span>
                              <input type="checkbox" name="hdr" id="hdr" <?php echo $row_rs_individu['hdr']=="oui"?'checked':'' ?> class="noircalibri10"
                               onClick="if(document.getElementById('date_hdr_aaaa').value!='')
                               					{ alert('La date de soutenance est renseign&eacute;e, vous ne pouvez pas d&eacute;cocher cette case')
                                          this.checked=true;
                                        }
                                        else
                                        { if(!confirm('Voulez-vous r&eacute;ellement modifier le statut HDR de ce personnel ?'))
                                        	{ this.checked=!this.checked;
                                          }
                                        }
                                        "
                               >
                            </td>
                            <td nowrap><span class="bleugrascalibri10">&nbsp;&nbsp;Date :</span>
                                <input name="date_hdr_jj" type="text" class="noircalibri10" id="date_hdr_jj" value="<?php echo substr($row_rs_individu['date_hdr'],8,2); ?>" size="2" maxlength="2">
                                <input name="date_hdr_mm" type="text" class="noircalibri10" id="date_hdr_mm" value="<?php echo substr($row_rs_individu['date_hdr'],5,2); ?>" size="2" maxlength="2">
                                <input name="date_hdr_aaaa" type="text" class="noircalibri10" id="date_hdr_aaaa" value="<?php echo substr($row_rs_individu['date_hdr'],0,4); ?>" size="4" maxlength="4"
                                onChange= "if(this.value!='')
                                					{ document.getElementById('hdr').checked=true;
                                          }
                                          "
                                >
                            </td>
                            <?php
                            if($codelibcat=='EC' || $codelibcat=='CHERCHEUR')
                            { ?>
                            <td nowrap>
                            <span class="bleugrascalibri10">Associ&eacute; :</span>
                            <input type="checkbox" name="associe" <?php echo $row_rs_individu['associe']=="oui"?'checked':'' ?>
                             onClick="if(!confirm('Voulez-vous r&eacute;ellement modifier le statut d&rsquo;associ&eacute; de ce personnel ?'))
                                      { this.checked=!this.checked;
                                      }
                                      "
                            >
                            </td>
															<?php // 20170315 
                              if(array_key_exists('srh',$tab_roleuser) || $est_admin)
                              {?>
                              <td>
                                <?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'contrat_associe','Contrat associ&eacute;',$form_fiche_dossier_pers,true);?>
                              </td>
                              <td>
                                <?php echo ligne_txt_upload_pj_individu($codeindividu,'sejour',$numsejour,'avenant_associe','Avenant contrat associ&eacute;',$form_fiche_dossier_pers,true);?>
                              </td>
                              <?php
                              }
                            }?>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <tr>
                    	<td>
                      	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                          <tr>
                            <td valign="top" ><img src="images/b_plus.png" name="image_suivi_hdr" id="image_suivi_hdr"
                                      onClick="javascript:
                                                suivi_hdr=document.getElementById('suivi_hdr');
                                                if(suivi_hdr.className=='affiche')
                                                { suivi_hdr.className='cache';
                                                  document.getElementById('image_suivi_hdr').src='images/b_plus.png';
                                                  document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['b_img_suivi_hdr_plus_ou_moins'].value='b_plus';
                                                }
                                                else 
                                                { suivi_hdr.className='affiche';
                                                  document.getElementById('image_suivi_hdr').src='images/b_moins.png';
                                                  document.forms['<?php echo $form_fiche_dossier_pers ?>'].elements['b_img_suivi_hdr_plus_ou_moins'].value='b_moins';
                                                }
                                              "
                                                >
                            </td>
                            <td>
                            	<div id='suivi_hdr' class="cache">
                            	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                              	<tr>
                                	<td>
                                  	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                    	<tr>	
                                        <td nowrap class="bleugrascalibri10">Heure :&nbsp;</td>
                                        <td nowrap><input type="text" class="noircalibri10" name="heure_hdr_hh" id="heure_hdr_hh" value="<?php echo substr($row_rs_individu['heure_hdr'],0,2); ?>" size="2" maxlength="2">
                                          <span class="bleugrascalibri10">&nbsp;H&nbsp;</span>
                                          <input type="text" class="noircalibri10" name="heure_hdr_mn" id="heure_hdr_mn" value="<?php echo substr($row_rs_individu['heure_hdr'],3,2); ?>" size="2" maxlength="2"></td>
                                        <td><span class="bleugrascalibri10">&nbsp;Lieu :</span>&nbsp;</td>
                                        <td><input type="text" class="noircalibri10" name="lieu_hdr" id="lieu_hdr" value="<?php echo htmlspecialchars($row_rs_individu['lieu_hdr']); ?>" size="45" maxlength="100"></td>
                                     </tr>
                                   </table>
                                  </td>
                                </tr>
                                <tr>
                                	<td>
                                  	<table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                                    	<tr>	
                                        <td nowrap class="bleugrascalibri10">Titre :&nbsp;</td>
                                        <td><input type="text" class="noircalibri10" name="titre_hdr" id="titre_hdr" value="<?php echo htmlspecialchars($row_rs_individu['titre_hdr']); ?>" size="100" maxlength="300"></td>
                                      </tr>
                                      <tr>
                                        <td valign="top" nowrap class="bleugrascalibri10">R&eacute;sum&eacute; :&nbsp;<br>
                                          <span class="bleucalibri9">(</span><span id="resume_hdr#nbcar_js" class="bleucalibri9"><?php echo strlen($row_rs_individu['resume_hdr']) ?></span><span class="bleucalibri9">/6000 car. max.)</span></td>
                                        <td><textarea name="resume_hdr" cols="80" rows="5" wrap="PHYSICAL" class="noircalibri10" id="resume_hdr" <?php echo affiche_longueur_js("this","6000","'resume_hdr#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['resume_hdr']; ?></textarea></td>
                                      </tr>
                                    </table>
                                  </td>
                          			</tr>
                              </table>
                              </div>
                            </td>
                          </tr>
                        </table>
                      </td>
                    </tr>
                    <?php
                    }?>
                  </table>
                  <?php 
                  }// fin estpermanent=oui ?>
                </td>
              </tr>
              <?php 
              if($codelibcat=='EC' || $codelibcat=='CHERCHEUR' || ($codelibcat=='ITARF' && ($codecorps=='13' || $codecorps=='19')))//ec, c ou ir
              {?>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" >
                    <tr>
                      <td><span class="bleugrascalibri10">Ecole doctorale :</span>
                      </td>
                      <td>
                        <select name="codeed" class="noircalibri10" >
                          <?php mysql_data_seek($rs_ed,0);
                          while ($row_rs_ed = mysql_fetch_assoc($rs_ed)) 
                          { ?>
                          <option value="<?php echo $row_rs_ed['codeed']?>"<?php echo ($row_rs_ed['codeed']==$row_rs_individu['codeed']?'selected':""); ?>><?php echo $row_rs_ed['libed']?></option>
                          <?php
                          }
                          ?>
                        </select>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              <?php 
              } 
							if(($est_admin || $estgesttheme || array_key_exists('srh',$tab_roleuser) || droit_acces($tab_contexte)))
							{?>
              <tr>
                <td>
                  <table  border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="0">
                    <tr>
                      <td valign="top" nowrap><span class="bleugrascalibri10">Notes partag&eacute;es :<br>
                        </span> <span id="note#nbcar_js" class="bleucalibri9">(<?php echo strlen($row_rs_individu['note']) ?></span><span class="bleucalibri9">/6000 car. max.)</span></td>
                      <td nowrap><textarea name="note" cols="70" rows="4" class="noircalibri10" <?php echo affiche_longueur_js("this","6000","'note#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individu['note']; ?></textarea></td>
                      <td valign="top" nowrap><span class="bleucalibri9">PostIt (<?php echo $row_rs_individupostit['prenom']; ?>)&nbsp;:</span><br>
                        <span id="postit#nbcar_js" class="bleucalibri9">(<?php echo strlen($row_rs_individupostit['postit']) ?></span><span class="bleucalibri9">/200 car. max.)</span>&nbsp; </td>
                      <td><textarea name="postit" cols="40" rows="5" class="noircalibri10" <?php echo affiche_longueur_js("this","1000","'postit#nbcar_js'","'bleucalibri9'","'rougecalibri9'") ?>><?php echo $row_rs_individupostit['postit']; ?></textarea></td>
                      <td align="left" valign="top">
                        <img src="images/b_info.png" width="16" height="16" id="sprytrigger_info_postit">
                        <div class="tooltipContent_cadre" id="info_postit">
                          <span class="noircalibri10">
                            Ce postit est personnel : il n'est affiché que pour vous.<br>
                            Chacun peut avoir son postit pour un dossier mais les autres ne le voient pas contrairement aux notes partagées.
                          </span>
                          </div>
                        <script type="text/javascript">
                        var sprytooltip_info_postit = new Spry.Widget.Tooltip("info_postit", "#sprytrigger_info_postit", {closeOnTooltipLeave:true, followMouse:true});
                        </script>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
							<?php 
							}?>
              <tr>
                <td><input name="submit_enregistrer" type="submit" class="noircalibri10" id="submit_enregistrer" value="Enregistrer" >
                      <!-- <input name="submit_valider" type="submit" class="noircalibri10" id="submit_valider" value="Enregistrer et demander validation">-->
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
	<?php 
  if($admin_bd)
  { ?>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
      <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellpadding="3" cellspacing="3" class="table_cadre_arrondi" width="100%">
        <tr>
          <td>
            <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="3" cellpadding="3">
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td><span class="bleugrascalibri10">Titre en fran&ccedil;ais :&nbsp;</span></td>
                      <td><input name="titrepers_fr" type="text" class="noircalibri10" id="titrepers_fr" value="<?php echo $row_rs_individu['titrepers_fr']; ?>" size="20" maxlength="20"></td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Titre en anglais :&nbsp;</span></td>
                      <td><input name="titrepers_en" type="text" class="noircalibri10" id="titrepers_en" value="<?php echo $row_rs_individu['titrepers_en']; ?>" size="20" maxlength="20"></td>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td><span class="bleugrascalibri10">Fonction en fran&ccedil;ais :&nbsp;</span></td>
                      <td><input name="fonction_fr" type="text" class="noircalibri10" id="fonction_fr" value="<?php echo $row_rs_individu['fonction_fr']; ?>" size="30" maxlength="30"></td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Fonction en anglais :&nbsp;</span></td>
                      <td><input name="fonction_en" type="text" class="noircalibri10" id="fonction_en" value="<?php echo $row_rs_individu['fonction_en']; ?>" size="30" maxlength="30"></td>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Lien page http :&nbsp;</span></td>
                      <td><input name="lienpghttp" type="text" class="noircalibri10" id="lienpghttp" value="<?php echo $row_rs_individu['lienpghttp']; ?>" size="40" maxlength="100"></td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Lien page http enseignement :&nbsp;</span></td>
                      <td><input name="lienhttpenseigne" type="text" class="noircalibri10" id="lienhttpenseigne" value="<?php echo $row_rs_individu['lienhttpenseigne']; ?>" size="40" maxlength="100"></td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Lien LinkedIn :&nbsp;</span></td>
                      <td><input name="lienlinkedin" type="text" class="noircalibri10" id="lienlinkedin" value="<?php echo $row_rs_individu['lienlinkedin']; ?>" size="40" maxlength="100"></td>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Afficher dans l'annuaire et listes diffusion :</span></td>
                      <td><input name="afficheannuaire" type="checkbox" class="noircalibri10" value="oui" <?php echo ($row_rs_individu['afficheannuaire']=="oui"?"checked":""); ?> ></td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Listes de diffusion :</span></td>
                      <td><input type="checkbox" name="liste_diff" id="liste_diff" <?php echo ($row_rs_individu['liste_diff']=="oui"?"checked":""); ?>></td>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Nom HAL :</span>&nbsp;</td>
                      <td><input name="nom_hal" type="text" class="noircalibri10" id="nom_hal" value="<?php echo $row_rs_individu['nom_hal']; ?>" size="30" maxlength="30"></td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;&nbsp;Pr&eacute;nom HAL :&nbsp;</span></td>
                      <td><input name="prenom_hal" type="text" class="noircalibri10" id="prenom_hal" value="<?php echo $row_rs_individu['prenom_hal']; ?>" size="30" maxlength="30"></td>
											<td align="center" nowrap><span class="bleugrascalibri10">liste_nomprenom_ascii_bib_hal :&nbsp;<br>nom, prenom suivi d'un CR</span></td>
                    	<td><textarea name="liste_nomprenom_ascii_bib_hal" rows="3" cols="40"><?php echo $row_rs_individu['liste_nomprenom_ascii_bib_hal'] ?></textarea>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Login intranet :&nbsp;</span>
                      </td>
                      <td>
                        <input name="login" type="text" class="noircalibri10" id="login" value="<?php echo $row_rs_individu['login']; ?>" size="30" maxlength="30">
                      </td>
                      <td nowrap><span class="bleugrascalibri10">&nbsp;Password :&nbsp;</span>
                      </td>
                      <td>
                        <input name="passwd" type="text" class="noircalibri10" id="passwd" value="<?php echo $row_rs_individu['passwd']; ?>" size="20" maxlength="20">
                      </td>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Login compte informatique <span class="mauvegrascalibri10">(&agrave; faire)</span> :&nbsp;</span>
                      </td>
                      <td><input name="compte_info" type="text" class="noircalibri10" id="compte_info" size="30" maxlength="30">
                      </td>
                    </tr>
                  </table>  
                </td>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>
                  <table border="<?php echo $GLOBALS['voir_bordure_tableau']?1:0; ?>" cellspacing="0" cellpadding="0">
                    <tr>
                      <td nowrap><span class="bleugrascalibri10">Proc&eacute;dure d'accueil :&nbsp;</span></td>
                      <td><input type="checkbox" name="proc_accueil" id="proc_accueil"<?php echo ($row_rs_individu['proc_accueil']=="oui"?"checked":""); ?>>
                      </td>
                      <td nowrap><span class="bleugrascalibri10">Proc&eacute;dure de d&eacute;part :&nbsp;</span></td>
                      <td><input type="checkbox" name="proc_depart" id="proc_depart" <?php echo ($row_rs_individu['proc_depart']=="oui"?"checked":""); ?>>
                      </td>
                    </tr>
                  </table>
                </td>
                <td>&nbsp;</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <?php
  } // fin if(admin_bd)
  ?>
  </tr>
</table>
</form>
</body>
</html>
<?php
}
/* if(isset($rs_objectifscientifique)) mysql_free_result($rs_objectifscientifique);*/
if(isset($rs_statutvisa)) mysql_free_result($rs_statutvisa);
if(isset($rs_zrr_sourcefinancement)) mysql_free_result($rs_zrr_sourcefinancement);
if(isset($rs_zrr_originefinancement)) mysql_free_result($rs_zrr_originefinancement); 
if(isset($rs_zrr_naturefinancement)) mysql_free_result($rs_zrr_naturefinancement);
if(isset($rs_zrr)) mysql_free_result($rs_zrr);
if(isset($zrr_physiquevirtuel)) mysql_free_result($zrr_physiquevirtuel);
if(isset($rs_pieceidentite)) mysql_free_result($rs_pieceidentite);
if(isset($rs_statutpers)) mysql_free_result($rs_statutpers);
if(isset($rs_statutperscorps)) mysql_free_result($rs_statutperscorps);
if(isset($rs_diplome)) mysql_free_result($rs_diplome);
if(isset($rs_ed)) mysql_free_result($rs_ed);
if(isset($rs_gesttheme)) mysql_free_result($rs_gesttheme);
if(isset($rs_individupostit)) mysql_free_result($rs_individupostit);
if(isset($rs_individutheme)) mysql_free_result($rs_individutheme);
if(isset($rs_bap)) mysql_free_result($rs_bap);
if(isset($rs_centrecout_pers)) mysql_free_result($rs_centrecout_pers);
if(isset($rs_sujet)) mysql_free_result($rs_sujet);
if(isset($rs_corps)) mysql_free_result($rs_corps);
if(isset($rs_corpsgrade)) mysql_free_result($rs_corpsgrade);
if(isset($rs_lieu)) mysql_free_result($rs_lieu);
if(isset($rs_etab)) mysql_free_result($rs_etab);
if(isset($rs_individu)) mysql_free_result($rs_individu);
if(isset($rs_individuemploi)) mysql_free_result($rs_individuemploi);
if(isset($rs_civilite)) mysql_free_result($rs_civilite);
if(isset($rs_referent)) mysql_free_result($rs_referent);
if(isset($rs_corpsmodefinancement)) mysql_free_result($rs_corpsmodefinancement);
if(isset($rs_typeprofession_postdoc)) mysql_free_result($rs_typeprofession_postdoc);
if(isset($rs_cat)) mysql_free_result($rs_cat);
if(isset($rs_pays)) mysql_free_result($rs_pays);
if(isset($rs_cotutelle_pays)) mysql_free_result($rs_cotutelle_pays);
if(isset($rs_pays_postdoc)) mysql_free_result($rs_pays_postdoc);
if(isset($rs_pays_etab_orig)) mysql_free_result($rs_pays_etab_orig);
if(isset($rs)) mysql_free_result($rs);
?>
