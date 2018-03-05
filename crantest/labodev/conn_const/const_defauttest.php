<?php 
$GLOBALS['admin_bd']='cervellin';
$GLOBALS['charset']='charset=iso-8859-1';
// configuration messagerie pour envoi mail
$GLOBALS['smtpserver'] = "smtp-int.univ-lorraine.fr";//"mx-2.mail.uhp-nancy.fr";//"smtps.uhp-nancy.fr";
$GLOBALS['smtplocalhost']="VM-WEB-CRAN2.cran.univ-lorraine.fr";
$GLOBALS['smtpport'] = "25";//"587";"localhost"=>
// base de donnees
$hostname_labo = "localhost";
$database_labo = $_SESSION['database_labo'];
$username_labo = "root";
//------------------------------------
// local/serveur
$GLOBALS['local_serveur']="l:";
//$GLOBALS['local_serveur']="s:"; 
if($GLOBALS['local_serveur']=="l:")
{ $password_labo = "root";
	$GLOBALS['path_to_rep_upload']='c:/users/ocervell/Documents/htdocs_upload/'.$database_labo.'_upload/rep_upload';
	$GLOBALS['path_to_rep_upload_sauve']='c:/sauvebdcran-vm-web-cran/'.$database_labo.'_sauve_rep_upload';
	$GLOBALS['path_to_rep_sauve']='c:/sauvebdcran-vm-web-cran';
	$GLOBALS['url_site12+']= "http://localhost/";
}
else
{ $password_labo = "a*b1c*@";
	$GLOBALS['path_to_rep_upload']='e:/www_upload/'.$database_labo.'_upload/rep_upload';
	$GLOBALS['path_to_rep_upload_sauve']='e:/sauvebdcran-vm-web-cran/'.$database_labo.'_sauve_rep_upload';
	$GLOBALS['path_to_rep_sauve']='e:/sauvebdcran-vm-web-cran';
	$GLOBALS['url_site12+']= "https://12plus.cran.univ-lorraine.fr/";
} 

//------------------------------------
// mode exploitation
//$GLOBALS['mode_exploit']="normal";
$GLOBALS['mode_exploit']="test";
//$GLOBALS['mode_exploit']="demo";
if($GLOBALS['mode_exploit']=="normal")
{ $GLOBALS['rep_racine_site12+']='labo';//utilise dans les mails pour les styles
	$GLOBALS['display_errors']=false;
}
else if($GLOBALS['mode_exploit']=="demo")
{ $GLOBALS['rep_racine_site12+']='labodev';//utilise dans les mails pour les styles
	$GLOBALS['display_errors']=false;
}
else
{ $GLOBALS['rep_racine_site12+']='labodev';//utilise dans les mails pour les styles
	$GLOBALS['display_errors']=true;
}
//********************************** Pas de modif local/exploit
$GLOBALS['avecequipe']=false;//true
$GLOBALS['avecvisathemesujet']=true;//false
$GLOBALS['gestionindividus_col_sujet']=true;//false
$GLOBALS['gestionindividus_col_theme']=true;//false
$GLOBALS['edit_sujet_dans_individu']=false;// 20170322
$GLOBALS['logolabo']='images/logo_cran_160.png';
$GLOBALS['acronymelabo']='CRAN';
$GLOBALS['liblonglabo']='Centre de Recherche en Automatique de Nancy';
$GLOBALS['adresselabo']='CRAN - Campus Sciences - BP 70239 - 54506 VANDOEUVRE Cedex';
$GLOBALS['denominationlabo_attestationstage']='Universit&eacute; de Lorraine - '.$GLOBALS['liblonglabo'];
$GLOBALS['table_logo_attestationstage']='<table border="0"><tr><td><img src="images/logo_ul_rvb_80x180.jpg"></td><td><img src="'.$GLOBALS['logolabo'].'"></td></tr></table>';
$GLOBALS['acronymetutelle']=array('universite'=>array('ul'=>array('acronyme'=>'UL')),'recherche'=>array('cnrs'=>array('acronyme'=>'CNRS')));
$GLOBALS['num_umr']='7039';
$GLOBALS['racine_site_web_labo']="http://www.cran.univ-lorraine.fr/";
// budget
$GLOBALS['date_deb_12plus_budget']='2013/01/01';
$GLOBALS['date_deb_exercice_comptable']='2017/01/01';
$GLOBALS['date_fin_exercice_comptable']='2017/12/31';
$GLOBALS['date_ouverture_saisie_commandes_secr_site']='2017/01/01';
$GLOBALS['date_fermeture_saisie_commandes_secr_site']='2017/12/31';
$GLOBALS['suffixe_dotation_01']='';
$GLOBALS['suffixe_dotation_02']='RSA -- LDDIR';
$GLOBALS['prefixe_commande_etat_avec_frais']="OM#L0";
$GLOBALS['gestioncommandes_passees']='(<a href="/'.$database_labo.'2016_php/gestioncommandes.php" target="_self">2016</a>&nbsp;<a href="/'.$database_labo.'2015_php/gestioncommandes.php" target="_self">2015</a>&nbsp;<a href="/'.$database_labo.'2014_php/gestioncommandes.php" target="_self">2014</a>&nbsp;<a href="/'.$database_labo.'2013_php/gestioncommandes.php" target="_self">2013</a>)';

//message de bienvenue, images affichees
$GLOBALS['siteouvert']=true;
$GLOBALS['sitefermemotif']="Le site est ferm&eacute; jusqu&rsquo;au lundi 4 janvier 2016 - 9h pour raison de maintenance. Veuillez nous en excuser.";
$GLOBALS['sitebonjour']="<center><img src='images/galerie/affiche_12plus_bonjour.jpg'></center>";
$GLOBALS['siteaurevoir']="<center><img src='images/galerie/affiche_12plus_aurevoir.jpg'></center>";
$GLOBALS['siteloginerreur']="<center><img src='images/galerie/affiche_12plus_loginerreur.jpg'></center>";
$GLOBALS['bloque_saisie_these']=false;//true
$GLOBALS['bloque_saisie_these_motif']='Les sujets de th&egrave;se ne peuvent &ecirc;tre saisis du 10 janvier 0h au 13 janvier 12h';
// liens specifiques labos
// menuprincipal
$GLOBALS['menuprincipal_ligne_sous_entete']='<a href="http://gestion-materiel.cran.univ-lorraine.fr/" target="_blank">Gestion de mat&eacute;riel</a>&nbsp;&nbsp;&nbsp;<a href="http://reservation.cran.uhp-nancy.fr/index.html" target="_blank">Salles&nbsp;-Vid&eacute;oprojecteur</a>';
// adresses mail exploit, modifiees si test
$GLOBALS['emailDU']='didier.wolf@univ-lorraine.fr';
$GLOBALS['emailDIRECTION']='cran-direction@univ-lorraine.fr';
$GLOBALS['emailACMO']='cran-acmo@univ-lorraine.fr';
$GLOBALS['emailRH']='cran-rh@univ-lorraine.fr';
$GLOBALS['emailSID']='cran-serviceinfo@univ-lorraine.fr';
$GLOBALS['webMaster'] = array('nom'=>'WebMaster','email'=>'Pascal.Gend@univ-lorraine.fr');
$GLOBALS['emailTest'] = 'Pascal.Gend@univ-lorraine.fr';
$GLOBALS['Serveur12+'] = array('nom'=>'Serveur 12+','email'=>'Didier.Wolf@univ-lorraine.fr');
$GLOBALS['Serveur12+commande'] = array('nom'=>'serveur 12+','lien'=>$GLOBALS['url_site12+'].$GLOBALS['rep_racine_site12+'].'/formintranetpasswd.php?db='.$database_labo,'emailexpediteur'=>'Didier.Wolf@univ-lorraine.fr','emailretour'=>'Carole.Courrier@univ-lorraine.fr');
$GLOBALS['fsd_contact'] = array('prenomnom'=>'fsd-acces-zrr','email'=>"fsd-acces-zrr@univ-lorraine.fr");
$GLOBALS['mode_avec_envoi_mail']=false;//true;
if($GLOBALS['mode_exploit']=="test" || $GLOBALS['mode_exploit']=="demo")
{ $GLOBALS['emailDU']=$GLOBALS['emailTest'];
	$GLOBALS['emailDIRECTION']=$GLOBALS['emailTest'];
	$GLOBALS['emailACMO']=$GLOBALS['emailTest'];
	$GLOBALS['emailRH']=$GLOBALS['emailTest'];
	$GLOBALS['emailSID']=$GLOBALS['emailTest'];// service info. : devrait etre extrait de structureindividu 
	$GLOBALS['fsd_contact'] = array('prenomnom'=>$GLOBALS['admin_bd'],'email'=>$GLOBALS['emailTest']);
	$GLOBALS['mode_avec_envoi_mail']=false;//true
}
$GLOBALS['estzrr']=true;
$GLOBALS['date_zrr_obligatoire']='2014/09/15';// champs zrr obligatoires a partir de date_zrr_obligatoire
$GLOBALS['date_zrr_2017_obligatoire']='2017/01/01';// champs zrr obligatoires a partir de date_zrr_2017_obligatoire
$GLOBALS['date_zrr_t0']='2014/06/23';																	
$GLOBALS['date_limite_affiche_champ_prog_rech']='2012/09/01';// champ prog_rech plus affiche depuis date_limite_affiche_champ_prog_rech
$GLOBALS['date_bascule_gt_vers_dept']='2013/01/01';//utilise pour verification de l'affectation a un dept apres date_bascule_gt_vers_dept
// affichage de libelle avant et apres date_bascule_gt_vers_dept
if(date('Y/m/d')<$GLOBALS['date_bascule_gt_vers_dept'])
{ $GLOBALS['libcourt_theme_fr']='GT/Dept';
  $GLOBALS['liblong_theme_fr']='Groupe th�matique/D�partement';
}
else
{ $GLOBALS['libcourt_theme_fr']='Dept.';
  $GLOBALS['liblong_theme_fr']='D�partement';
}
$GLOBALS['date_changement_form_mission']='2015/03/15';
// pour calcul durees
$GLOBALS['nb_jours_du_mois']=array(1=>31,2=>28,3=>31,4=>30,5=>31,6=>30,7=>31,8=>31,9=>30,10=>31,11=>30,12=>31);
// pour affichage texte brut	
$GLOBALS['tab_car_accent_maj_transforme']=array("&Agrave;"=>"A","&Eacute;"=>"E","&Egrave;"=>"E","&Ecirc;"=>"E","&Icirc;"=>"I",
																					"&Ocirc;"=>"O","&Uacute;"=>"U","&Ugrave;"=>"U","&Ucirc;"=>"U");
//upload : maxi par fichier (verifier aussi limite dans php.ini)
$GLOBALS['max_file_size']=pow(2,23);//8  Mo total
$GLOBALS['max_file_size_Mo']=8;// 8 Mo total
// types de fichiers uploades : penser a reporter dans alert.js
$GLOBALS['file_types_array']=array('pdf','doc','docx','txt','xls','xlsx','pptx','csv','gif','jpeg','jpg','png','bib','zip');
$GLOBALS['file_types_mime_array']=array('pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','txt'=>'application/text','xls'=>'application/vnd.ms-excel',
																				'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
																				'csv'=>'application/vnd.ms-excel','gif'=>'image/gif','jpeg'=>'image/jpeg','jpg'=>'image/jpeg','png'=>'image/png','bib'=>'application/text','zip'=>'application/x-zip-compressed');
$GLOBALS['tab_erreur_upload']=array( UPLOAD_ERR_OK=>'',
																		 UPLOAD_ERR_INI_SIZE=>	'Le fichier t&eacute;l&eacute;charg&eacute; exc&egrave;de la taille maximale autoris&eacute;e par le syst&egrave;me.' ,
																		 UPLOAD_ERR_FORM_SIZE=>	'Le fichier t&eacute;l&eacute;charg&eacute; exc&egrave;de la taille maximale autoris&eacute;e ('.$GLOBALS['max_file_size_Mo'].' Mo).',
																		 UPLOAD_ERR_PARTIAL=>		'Le fichier n&rsquo;a &eacute;t&eacute; que partiellement t&eacute;l&eacute;charg&eacute;.', 
																		 UPLOAD_ERR_NO_FILE=>		'Aucun fichier n&rsquo;a &eacute;t&eacute; t&eacute;l&eacute;charg&eacute;.', 
																		 UPLOAD_ERR_NO_TMP_DIR=>'Un dossier temporaire est manquant.',
																		 UPLOAD_ERR_CANT_WRITE=>'Echec de l&rsquo;&eacute;criture du fichier sur le disque : erreur syst&eacute;.',
																		 UPLOAD_ERR_EXTENSION=>	'L&rsquo;extension n&rsquo;est pas autoris&eacute;e ('.implode(", ", $GLOBALS['file_types_array']).').'
																		);
// fin variables upload
// pour le developpeur
$GLOBALS['voir_bordure_tableau']=false;																
?>