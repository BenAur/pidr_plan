<?php  include_once('const_defauttest.php');
//********************************** Pas de modif local/exploit
$GLOBALS['avecvisathemesujet']=false;
$GLOBALS['gestionindividus_col_sujet']=false;
$GLOBALS['gestionindividus_col_theme']=true;
$GLOBALS['edit_sujet_dans_individu']=true;
$GLOBALS['logolabo']='images/logo_imopa_150.jpg';
$GLOBALS['acronymelabo']='IMoPA';
$GLOBALS['liblonglabo']='Ing&eacute;nierie Mol&eacute;culaire et Physiopathologie';
$GLOBALS['adresselabo']='UMR 7365 CNRS-UL IMoPA Biop&ocirc;le de l&rsquo;Universit&eacute; de Lorraine<br>Campus Biologie Sant&eacute;<br>9 Avenue de la For&ecirc;t de Haye<br>CS 50184<br>54505 Vandoeuvre Les Nancy';
$GLOBALS['denominationlabo_attestationstage']='Universit&eacute; de Lorraine - '.$GLOBALS['liblonglabo'];
$GLOBALS['table_logo_attestationstage']='<table border="0"><tr><td><img src="images/logo_ul_rvb_80x180.jpg"></td><td><img src="'.$GLOBALS['logolabo'].'"></td></tr></table>';
$GLOBALS['acronymetutelle']=array('universite'=>array('ul'=>array('acronyme'=>'UL')),'recherche'=>array('cnrs'=>array('acronyme'=>'CNRS')));
$GLOBALS['num_umr']='7365';
$GLOBALS['racine_site_web_labo']="http://www.imopa.cnrs.fr/";
// budget
$GLOBALS['gestioncommandes_passees']='';

// liens specifiques labos
// menuprincipal
$GLOBALS['menuprincipal_ligne_sous_entete']='<a href="https://mail.univ-lorraine.fr/#2" target="_blank">R&eacute;servation</a>';
// adresses mail exploit, modifiees si test
$GLOBALS['emailDU']='jean-yves.jouzeau@univ-lorraine.fr';
$GLOBALS['emailDIRECTION']='medecine-imopa-dir@univ-lorraine.fr';
$GLOBALS['emailACMO']='medecine-imopa-ap@univ-lorraine.fr';
$GLOBALS['emailRH']='karine.lorcin@univ-lorraine.fr';
$GLOBALS['emailSID']='laurent.grossin@univ-lorraine.fr';
$GLOBALS['webMaster'] = array('nom'=>'WebMaster','email'=>'Pascal.Gend@univ-lorraine.fr');
$GLOBALS['emailTest'] = 'Pascal.Gend@univ-lorraine.fr';
$GLOBALS['Serveur12+'] = array('nom'=>'Serveur 12+','email'=>'jean-yves.jouzeau@univ-lorraine.fr');
$GLOBALS['Serveur12+commande'] = array('nom'=>'serveur 12+','lien'=>$GLOBALS['url_site12+'].$GLOBALS['rep_racine_site12+'].'/formintranetpasswd.php?db='.$database_labo,'emailexpediteur'=>'jean-yves.jouzeau@univ-lorraine.fr','emailretour'=>''/* 'Carole.Courrier@univ-lorraine.fr' */);
$GLOBALS['fsd_contact'] = array('prenomnom'=>'fsd-acces-zrr','email'=>"fsd-acces-zrr@univ-lorraine.fr");
$GLOBALS['mode_avec_envoi_mail']=false;
if($GLOBALS['mode_exploit']=="test" || $GLOBALS['mode_exploit']=="demo")
{ $GLOBALS['emailDU']=$GLOBALS['emailTest'];
	$GLOBALS['emailDIRECTION']=$GLOBALS['emailTest'];
	$GLOBALS['emailACMO']="psclgnd@gmail.com";//$GLOBALS['emailTest'];
	$GLOBALS['emailRH']="pascal.gend@wanadoo.fr";//$GLOBALS['emailTest'];
	$GLOBALS['emailSID']=$GLOBALS['emailTest'];// service info. : devrait etre extrait de structureindividu 
	$GLOBALS['fsd_contact'] = array('prenomnom'=>$GLOBALS['admin_bd'],'email'=>$GLOBALS['emailTest']);
	$GLOBALS['mode_avec_envoi_mail']=false;
}
$GLOBALS['estzrr']=false;
$GLOBALS['libcourt_theme_fr']='Equipe';
$GLOBALS['liblong_theme_fr']='Equipe';
?>