<?php 
require_once('_const_fonc.php');

//include 'fonctions_base.php';

$bdd = "labodev";

$codeuser=deconnecte_ou_connecte();

// Par d�faut, codeindividu est initialise 
$codeindividu=isset($_GET['codeindividu'])?$_GET['codeindividu']:(isset($_POST['codeindividu'])?$_POST['codeindividu']:"");
// ROLES : $user a un ou plusieurs roles $tab_roleuser dans la liste de tous les roles $tab_statutvisa et est "titulaire de ce role" ou "suppl�ant"
// d�finis par $estreferent et $estresptheme
$tab_statutvisa=get_statutvisa();// liste de tous les visas (roles)
$estreferent=false;// user a le role referent mais n'est pas forc�ment referent
$estresptheme=false;// user a le role theme mais pas forc�ment r�f�rent : peut aussi etre le gesttheme
$tab_resp_roleuser=get_tab_roleuser($codeuser,'','',$tab_statutvisa,$estreferent,$estresptheme);//renvoie table des roles de $codeuser et modifie $estreferent+$estresptheme
$tab_roleuser=$tab_resp_roleuser['tab_roleuser'];
$estreferent=$tab_resp_roleuser['estreferent'];
$estresptheme=$tab_resp_roleuser['estresptheme'];
$tab_infouser=get_info_user($codeuser);// nom, prenom, gt(s)...

$erreur="";
$warning="";//warning qui n'empeche pas l'enregistrement mais avertit le user
$affiche_succes=false;//affichage de message_resultat_affiche (si pas d'erreur)
$message_resultat_affiche="";

$codification='';



$query_rs_typestage=" SELECT codetypestage,libcourttypestage as libtypestage".
										" FROM typestage ORDER BY numordre asc";
$rs_typestage = mysql_query($query_rs_typestage) or die(mysql_error());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; <?php echo $GLOBALS['charset'] ?>" />
<title>Nomenclatures et structures
</title>
<link href="styles/normal.css" rel="stylesheet" type="text/css" />
<link href="styles/tableau_bd.css" rel="stylesheet" type="text/css" />
</head>

<body>
<table border="0" align="center" cellpadding="0" cellspacing="1">
    <?php echo entete_page(array('image'=>'','titrepage'=>'Gestion des codifications','lienretour'=>'menuprincipal.php','texteretour'=>'Retour au menu principal',
                                'tab_infouser'=>$tab_infouser,'tab_roleuser'=>$tab_roleuser)) ?>
     <tr>
     <td align="center">
         <p class="bleugrascalibri11"><B>Tables 	&#224; modifier : </B></p>
<ul class="mauvegrascalibri11">
    <li><a href="gestionlieu.php">Lieu</a></li>
    <li><a href="gestionstructure.php">Structures</a></li>
    <li><a href="gestiontheme.php">Gestion des Th&#232;mes</a></li>
    <li><a href="gestionsecrsite.php">Secr&#233;tariat de site (lieu)</a></li>
    <li><a href="gestionstatutvisa.php">Visas - R&ocirc;les</a></li>
    <li><a href="gestioncentrecout.php">Centres de co&ucirc;t</a></li>
    <li><a href="gestioncat.php">Cat&eacute;gories</a></li>
    <li><a href="gestioncorps.php">Corps</a></li>
    <li><a href="gestioncorpsgrade.php">Corps et grades</a></li>
    <li><a href="gestioncommissionsection.php">Sections</a></li>
    <li><a href="gestioned.php">Ecole doctorales</a></li>
    <li><a href="gestiondomainescientifique.php">Domaines scientifiques</a></li>
    <li><a href="gestiondisciplinescientifique.php">Disciplines scientifiques</a></li>
    <li><a href="gestionobjectifscientifique.php">Activit&#233;s scientifiques</a></li>
    <li><a href="gestionmodefinancement.php">Modes de financement</a></li>
    <li><a href="gestioncorpsmodefinancement.php">Corps - Modes de financement</a></li>
    <li><a href="gestiontypecreditcentrefinanciercentrecout.php">Credit - Type credit - Enveloppe</a></li>
    <li><a href="gestioncontclassif.php">Contrat classification</a></li>
    <li><a href="gestionconttype.php">Contrat type</a></li>
    <li><a href="gestionconttypeconvention.php">Contrat type convention</a></li>
    <li><a href="gestionstatutpart.php">Contrat statut partenaire</a></li>
</ul>
<p>&nbsp;</p>
</td>
</tr> 
</table>
</body>
</html>

