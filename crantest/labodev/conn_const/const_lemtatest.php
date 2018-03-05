<?php  include_once('const_defauttest.php');
//********************************** Pas de modif local/exploit
$GLOBALS['avecequipe']=true;
$GLOBALS['avecvisathemesujet']=false;
$GLOBALS['logolabo']='images/logo_lemta_160.jpg';
$GLOBALS['acronymelabo']='LEMTA';
$GLOBALS['liblonglabo']='Laboratoire d&rsquo;Energ&eacute;tique et de M&eacute;canique Th&eacute;orique et Appliqu&eacute;e';
$GLOBALS['adresselabo']='LEMTA - ENSEM - 2 Avenue de la For&ecirc;t de Haye BP 90161 54505 Vandoeuvre-l&egrave;s-Nancy cedex';
$GLOBALS['denominationlabo_attestationstage']='Universit&eacute; de Lorraine - '.$GLOBALS['liblonglabo'];
$GLOBALS['table_logo_attestationstage']='<table border="0"><tr><td><img src="images/logo_ul_rvb_80x180.jpg"></td><td><img src="'.$GLOBALS['logolabo'].'"></td></tr></table>';
$GLOBALS['acronymetutelle']=array('universite'=>array('ul'=>array('acronyme'=>'UL')),'recherche'=>array('cnrs'=>array('acronyme'=>'CNRS')));
$GLOBALS['num_umr']='7563';
$GLOBALS['racine_site_web_labo']="https://lemta.univ-lorraine.fr/";
// budget
$GLOBALS['gestioncommandes_passees']='';

// liens specifiques labos
// menuprincipal
$GLOBALS['menuprincipal_ligne_sous_entete']='';
// adresses mail exploit, modifiees si test
$GLOBALS['emailDU']='fabrice.lemoine@univ-lorraine.fr';
$GLOBALS['emailDIRECTION']='lemta-contact@univ-lorraine.fr';
$GLOBALS['emailACMO']='alain.chenu@univ-lorraine.fr';
$GLOBALS['emailRH']='edith.lang@univ-lorraine.fr';
$GLOBALS['emailSID']='ludovic.buhler@univ-lorraine.fr';
$GLOBALS['webMaster'] = array('nom'=>'WebMaster','email'=>'Pascal.Gend@univ-lorraine.fr');
$GLOBALS['emailTest'] = 'Pascal.Gend@univ-lorraine.fr';
$GLOBALS['Serveur12+'] = array('nom'=>'Serveur 12+','email'=>'fabrice.lemoine@univ-lorraine.fr');
$GLOBALS['Serveur12+commande'] = array('nom'=>'serveur 12+','lien'=>$GLOBALS['url_site12+'].$GLOBALS['rep_racine_site12+'].'/formintranetpasswd.php?db='.$database_labo,'emailexpediteur'=>'fabrice.lemoine@univ-lorraine.fr','emailretour'=>'celine.morville@univ-lorraine.fr');
$GLOBALS['fsd_contact'] = array('prenomnom'=>'fsd-acces-zrr','email'=>"fsd-acces-zrr@univ-lorraine.fr");
$GLOBALS['mode_avec_envoi_mail']=false;
if($GLOBALS['mode_exploit']=="test" || $GLOBALS['mode_exploit']=="demo")
{ $GLOBALS['emailDU']=$GLOBALS['emailTest'];
	$GLOBALS['emailDIRECTION']=$GLOBALS['emailTest'];
	$GLOBALS['emailACMO']=$GLOBALS['emailTest'];
	$GLOBALS['emailRH']=$GLOBALS['emailTest'];
	$GLOBALS['emailSID']=$GLOBALS['emailTest'];
	$GLOBALS['fsd_contact'] = array('prenomnom'=>$GLOBALS['admin_bd'],'email'=>$GLOBALS['emailTest']);
	$GLOBALS['mode_avec_envoi_mail']=false;
}
$GLOBALS['estzrr']=true;
$GLOBALS['libcourt_theme_fr']='Groupe';
$GLOBALS['liblong_theme_fr']='Groupe';
?>