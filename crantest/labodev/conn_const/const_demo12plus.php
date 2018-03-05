<?php include_once('const_defauttest.php');
//*********************************** A modifier sur site en exploitation/test
$GLOBALS['mode_exploit']="demo";
//$GLOBALS['mode_exploit']="test";
//$GLOBALS['mode_exploit']="normal";
//$GLOBALS['mode_exploit']="restreint";
$GLOBALS['siteouvert']=true;
//$GLOBALS['siteouvert']=false;
//$GLOBALS['display_errors']=true;
$GLOBALS['display_errors']=false;

//********************************** Pas de modif local/exploit
$GLOBALS['logolabo']='images/logodemo12plus.png';
$GLOBALS['acronymelabo']='DEMO';
$GLOBALS['liblonglabo']='Laboratoire DEMO';
$GLOBALS['adresselabo']='Adresse DEMO';
$GLOBALS['denominationlabo_attestationstage']='Universit&eacute; DEMO - '.$GLOBALS['liblonglabo'];
$GLOBALS['table_logo_attestationstage']='<table border="0"><tr><td></td><td><img src="'.$GLOBALS['logolabo'].'"></td></tr></table>';
$GLOBALS['num_umr']='9999';
$GLOBALS['racine_site_web_labo']="http://www.demo.fr/";
// liens specifiques labos
// menuprincipal
$GLOBALS['menuprincipal_ligne_sous_entete']='<a href="">Liens sp&eacute;fiques '.$GLOBALS['acronymelabo'].'</a>';
?>